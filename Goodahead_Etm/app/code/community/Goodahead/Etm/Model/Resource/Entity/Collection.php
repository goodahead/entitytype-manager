<?php
/**
 * This file is part of Goodahead_Etm extension
 *
 * This extension allows to create and manage custom EAV entity types
 * and EAV entities
 *
 * Copyright (C) 2014 Goodahead Ltd. (http://www.goodahead.com)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * and GNU General Public License along with this program.
 * If not, see <http://www.gnu.org/licenses/>.
 *
 * @category   Goodahead
 * @package    Goodahead_Etm
 * @copyright  Copyright (c) 2014 Goodahead Ltd. (http://www.goodahead.com)
 * @license    http://www.gnu.org/licenses/lgpl-3.0-standalone.html GNU Lesser General Public License
 */

abstract class Goodahead_Etm_Model_Resource_Entity_Collection
    extends Mage_Eav_Model_Entity_Collection_Abstract
{
    protected $_storeId     = null;

    protected $_entityTypeCode;

    protected function _construct()
    {
        $this->_init(sprintf('goodahead_etm/custom_%s_entity', $this->_entityTypeCode));
    }

    protected function _getEtmHelper()
    {
        return Mage::helper('goodahead_etm');
    }

    /**
     * @param int $storeId
     * @return Goodahead_Etm_Model_Resource_Entity_Collection
     */
    public function setStoreId($storeId)
    {
        $this->_storeId = $storeId;

        return $this;
    }

    /**
     * @return null|int
     */
    public function getStoreId()
    {
        return $this->_storeId;
    }

    public function getDefaultStoreId()
    {
        return 0;
    }

    public function joinVisibleAttributes($entityTypeId)
    {
        $visibleAttributes = $this->_getEtmHelper()->getVisibleAttributes($entityTypeId);

        $this->addAttributeToSelect(array_keys($visibleAttributes));

        return $this;
    }

    /**
     * @param string $valueField
     * @param string $labelField
     * @param array $additional
     * @return array
     */
    protected function _toOptionArray($valueField = 'entity_id', $labelField = 'name', $additional = array())
    {
        $defaultAttributeId = $this->getEntity()->getEntityType()->getDefaultAttributeId();
        /** @var Goodahead_Etm_Model_Entity_Attribute $defaultAttribute */
        $defaultAttribute = Mage::getModel('goodahead_etm/entity_attribute')->load($defaultAttributeId);

        $this->addAttributeToSelect($defaultAttribute->getAttributeCode());

        return parent::_toOptionArray($valueField, $defaultAttribute->getAttributeCode(), $additional);
    }

    public function _loadAttributes($printQuery = false, $logQuery = false)
    {
        if (empty($this->_items) || empty($this->_itemsById) || empty($this->_selectAttributes)) {
            return $this;
        }

        $entity = $this->getEntity();

        $tableAttributes = array();
        $attributeTypes  = array();
        foreach ($this->_selectAttributes as $attributeCode => $attributeId) {
            if (!$attributeId) {
                continue;
            }
            $attribute = Mage::getSingleton('eav/config')->getCollectionAttribute($entity->getType(), $attributeCode);
            if ($attribute && !$attribute->isStatic()) {
                $tableAttributes[$attribute->getBackendTable()][] = $attributeId;
                if (!isset($attributeTypes[$attribute->getBackendTable()])) {
                    $attributeTypes[$attribute->getBackendTable()] = $attribute->getBackendType();
                }
            }
        }

        $selects = array();
        foreach ($tableAttributes as $table=>$attributes) {
            $select = $this->_getLoadAttributesSelect($table, $attributes);
            $selects[$attributeTypes[$table]][] = $this->_addLoadAttributesSelectValues(
                $select,
                $table,
                $attributeTypes[$table]
            );
        }
        $selectGroups = Mage::getResourceHelper('eav')->getLoadAttributesSelectGroups($selects);
        foreach ($selectGroups as $selects) {
            if (!empty($selects)) {
                try {
                    $select = implode(' UNION ALL ', $selects);
                    $values = $this->getConnection()->fetchAll($select);
                } catch (Exception $e) {
                    Mage::printException($e, $select);
                    $this->printLogQuery(true, true, $select);
                    throw $e;
                }

                if ($this->getStoreId()) {
                    $values = $this->_compactValues($values);
                }
                foreach ($values as $value) {
                    $this->_setItemAttributeValue($value);
                }
            }
        }

        return $this;
    }

    /**
     * Retrieve attributes load select
     *
     * @param   string $table
     * @param   array  $attributeIds
     * @return  Mage_Eav_Model_Entity_Collection_Abstract
     */
    protected function _getLoadAttributesSelect($table, $attributeIds = array())
    {
        $select = parent::_getLoadAttributesSelect($table, $attributeIds);
        if ($this->getStoreId()) {
            $select->where('store_id IN (?)', array(0, $this->getStoreId()));
        } else {
            $select->where('store_id = 0');
        }
        return $select;
    }

    protected function _addLoadAttributesSelectValues($select, $table, $type)
    {
        parent::_addLoadAttributesSelectValues($select, $table, $type);
        $select->columns(array(
            'store_id' => $table. '.store_id',
        ));
        return $select;
    }

    protected function _compactValues($values)
    {
        $result = array();
        foreach ($values as $value) {
            $key = $value['entity_id'] . '-' . $value['attribute_id'];
            $result[$key]['entity_id'] = $value['entity_id'];
            $result[$key]['attribute_id'] = $value['attribute_id'];
            if ($value['store_id']) {
                $result[$key]['value'] = $value['value'];
            } else {
                $result[$key]['default_value'] = $value['value'];
            }
        }
        return $result;
    }

    protected function _setItemAttributeValue($valueInfo)
    {
        $entityIdField  = $this->getEntity()->getEntityIdField();
        $entityId       = $valueInfo[$entityIdField];
        if (!isset($this->_itemsById[$entityId])) {
            throw Mage::exception('Mage_Eav',
                Mage::helper('eav')->__('Data integrity: No header row found for attribute')
            );
        }
        $attributeCode = array_search($valueInfo['attribute_id'], $this->_selectAttributes);
        if (!$attributeCode) {
            $attribute = Mage::getSingleton('eav/config')->getCollectionAttribute(
                $this->getEntity()->getType(),
                $valueInfo['attribute_id']
            );
            $attributeCode = $attribute->getAttributeCode();
        }

        foreach ($this->_itemsById[$entityId] as $object) {
            $object->setData($attributeCode, array_key_exists('value', $valueInfo) ? $valueInfo['value'] : $valueInfo['default_value']);
        }

        return $this;
    }

    protected function _joinAttributeToSelect($method, $attribute, $tableAlias, $condition, $fieldCode, $fieldAlias)
    {
        if (isset($this->_joinAttributes[$fieldCode]['store_id'])) {
            $store_id = $this->_joinAttributes[$fieldCode]['store_id'];
        } else {
            $store_id = $this->getStoreId();
        }

        $adapter = $this->getConnection();

        if ($store_id != $this->getDefaultStoreId() && !$attribute->isScopeGlobal()) {
            /**
             * Add joining default value for not default store
             * if value for store is null - we use default value
             */
            $defCondition = '('.implode(') AND (', $condition).')';
            $defAlias     = $tableAlias . '_default';
            $defAlias     = $this->getConnection()->getTableName($defAlias);
            $defFieldAlias= str_replace($tableAlias, $defAlias, $fieldAlias);
            $tableAlias   = $this->getConnection()->getTableName($tableAlias);

            $defCondition = str_replace($tableAlias, $defAlias, $defCondition);
            $defCondition.= $adapter->quoteInto(
                " AND " . $adapter->quoteColumnAs("$defAlias.store_id", null) . " = ?",
                $this->getDefaultStoreId());

            $this->getSelect()->$method(
                array($defAlias => $attribute->getBackend()->getTable()),
                $defCondition,
                array()
            );

            $method = 'joinLeft';
            $fieldAlias = $this->getConnection()->getCheckSql("{$tableAlias}.value_id > 0",
                $fieldAlias, $defFieldAlias);
            $this->_joinAttributes[$fieldCode]['condition_alias'] = $fieldAlias;
            $this->_joinAttributes[$fieldCode]['attribute']       = $attribute;
        } else {
            $store_id = $this->getDefaultStoreId();
        }
        $condition[] = $adapter->quoteInto(
            $adapter->quoteColumnAs("$tableAlias.store_id", null) . ' = ?', $store_id);
        return parent::_joinAttributeToSelect($method, $attribute, $tableAlias, $condition, $fieldCode, $fieldAlias);
    }

    protected function _beforeLoad()
    {
        $attribute = Mage::getSingleton('eav/config')->getCollectionAttribute(
            $this->getEntity()->getEntityType(),
            $this->getEntity()->getEntityType()->getDefaultAttributeId()
        );
        $attributeCode = $attribute->getAttributeCode();
        $this->addAttributeToSort($attributeCode);
    }

}
