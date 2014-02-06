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

class Goodahead_Etm_Model_Resource_Entity
    extends Mage_Eav_Model_Entity_Abstract
{
    /**
     * Identifier of default store
     * used for loading default data for entity
     */
    const DEFAULT_STORE_ID = 0;

    const SCOPE_STORE   = 0;
    const SCOPE_WEBSITE = 2;

    /**
     * Store firstly set attributes to filter selected attributes when used specific store_id
     *
     * @var array
     */
    protected $_attributes   = array();

    protected $_entityTypeCode;

    protected function _getDefaultAttributeModel()
    {
        return 'goodahead_etm/entity_attribute';
    }

    /**
     * Initialize resource
     */
    public function __construct()
    {
        parent::__construct();
        $this->setConnection('eav_read', 'eav_write');

        /** @var Goodahead_Etm_Model_Entity_Type $entityType */
        $entityType = Mage::getModel('goodahead_etm/entity_type');
        $entityType->loadByCode($this->_entityTypeCode);

        $this->setType($entityType);
    }

    public function isScopeWebsite($attribute)
    {
        return $attribute->getIsGlobal() == self::SCOPE_WEBSITE;
    }

    public function isScopeStore($attribute)
    {
        return $attribute->getIsGlobal() == self::SCOPE_STORE;
    }

    /**
     * Default attributes
     *
     * @return array
     */
    protected function _getDefaultAttributes()
    {
        return array('entity_id', 'entity_type_id', 'increment_id');
    }

    /**
     * Returns default Store ID
     *
     * @return int
     */
    public function getDefaultStoreId()
    {
        return self::DEFAULT_STORE_ID;
    }


    /**
     * Check whether the attribute is Applicable to the object
     *
     * @param Varien_Object $object
     * @param Mage_Catalog_Model_Resource_Eav_Attribute $attribute
     * @return boolean
     */
/*    protected function _isApplicableAttribute($object, $attribute)
    {
        $applyTo = $attribute->getApplyTo();
        return count($applyTo) == 0 || in_array($object->getTypeId(), $applyTo);
    }*/

    /**
     * Walk through the attributes and run method with optional arguments
     *
     * Returns array with results for each attribute
     *
     * if $method is in format "part/method" will run method on specified part
     * for example: $this->walkAttributes('backend/validate');
     *
     * @param string $method
     * @param array $args
     * @param array $part attribute, backend, frontend, source
     * @return array
     */
    public function walkAttributes($partMethod, array $args = array())
    {
        $methodArr = explode('/', $partMethod);
        switch (sizeof($methodArr)) {
            case 1:
                $part   = 'attribute';
                $method = $methodArr[0];
                break;

            case 2:
                $part   = $methodArr[0];
                $method = $methodArr[1];
                break;
        }
        $results = array();
        foreach ($this->getAttributesByCode() as $attrCode => $attribute) {

            if (isset($args[0]) && is_object($args[0]) && !$this->_isApplicableAttribute($args[0], $attribute)) {
                continue;
            }

            switch ($part) {
                case 'attribute':
                    $instance = $attribute;
                    break;

                case 'backend':
                    $instance = $attribute->getBackend();
                    break;

                case 'frontend':
                    $instance = $attribute->getFrontend();
                    break;

                case 'source':
                    $instance = $attribute->getSource();
                    break;
            }

            if (!$this->_isCallableAttributeInstance($instance, $method, $args)) {
                continue;
            }

            try {
                $results[$attrCode] = call_user_func_array(array($instance, $method), $args);
            } catch (Mage_Eav_Model_Entity_Attribute_Exception $e) {
                throw $e;
            } catch (Exception $e) {
                $e = Mage::getModel('eav/entity_attribute_exception', $e->getMessage());
                $e->setAttributeCode($attrCode)->setPart($part);
                throw $e;
            }
        }

        return $results;
    }


    /**
     * Check whether attribute instance (attribute, backend, frontend or source) has method and applicable
     *
     * @param Mage_Eav_Model_Entity_Attribute_Abstract|Mage_Eav_Model_Entity_Attribute_Backend_Abstract|Mage_Eav_Model_Entity_Attribute_Frontend_Abstract|Mage_Eav_Model_Entity_Attribute_Source_Abstract $instance
     * @param string $method
     * @param array $args array of arguments
     * @return boolean
     */
    protected function _isCallableAttributeInstance($instance, $method, $args)
    {
        if ($instance instanceof Mage_Eav_Model_Entity_Attribute_Backend_Abstract
            && ($method == 'beforeSave' || $method = 'afterSave')
        ) {
            $attributeCode = $instance->getAttribute()->getAttributeCode();
            if (isset($args[0]) && $args[0] instanceof Varien_Object && $args[0]->getData($attributeCode) === false) {
                return false;
            }
        }

        if (!is_object($instance) || !method_exists($instance, $method)) {
            return false;
        }

        if (method_exists(get_parent_class(__CLASS__), '_isCallableAttributeInstance')) {
            return parent::_isCallableAttributeInstance($instance, $method, $args);
        } else {
            return true;
        }
    }



    /**
     * Retrieve select object for loading entity attributes values
     * Join attribute store value
     *
     * @param Varien_Object $object
     * @param string $table
     * @return Varien_Db_Select
     */
    protected function _getLoadAttributesSelect($object, $table)
    {
        $select = parent::_getLoadAttributesSelect($object, $table);
        /**
         * This condition is applicable for all cases when we was work in not single
         * store mode, customize some value per specific store view and than back
         * to single store mode. We should load correct values
         */
        if (Mage::app()->isSingleStoreMode()) {
            $storeId = (int)Mage::app()->getStore(true)->getId();
        } else {
            $storeId = (int)$object->getStoreId();
        }

        $setId  = $object->getAttributeSetId();
        $storeIds = array($this->getDefaultStoreId());
        if ($storeId != $this->getDefaultStoreId()) {
            $storeIds[] = $storeId;
        }

        $select->where('store_id IN (?)', $storeIds);
        if ($setId) {
            $select->join(
                array('set_table' => $this->getTable('eav/entity_attribute')),
                $this->_getReadAdapter()->quoteInto("{$table}.attribute_id = set_table.attribute_id" .
                ' AND set_table.attribute_set_id = ?', $setId),
                array()
            );
        }
        return $select;
    }

    /**
     * Adds Columns prepared for union
     *
     * @param Varien_Db_Select $select
     * @param string $table
     * @param string $type
     * @return Varien_Db_Select
     */
    protected function _addLoadAttributesSelectFields($select, $table, $type)
    {
        $select->columns(
            Mage::getResourceHelper('catalog')->attributeSelectFields('attr_table', $type)
        );
        return $select;
    }

    /**
     * Prepare select object for loading entity attributes values
     *
     * @param array $selects
     * @return Varien_Db_Select
     */
    protected function _prepareLoadSelect(array $selects)
    {
        $select = parent::_prepareLoadSelect($selects);
        $select->order('store_id', 'desc');
        return $select;
    }

    /**
     * Initialize attribute value for object
     *
     * @param Mage_Catalog_Model_Abstract $object
     * @param array $valueRow
     * @return Mage_Catalog_Model_Resource_Abstract
     */
    protected function _setAttributeValue($object, $valueRow)
    {
        $attribute = $this->getAttribute($valueRow['attribute_id']);
        if ($attribute) {
            $attributeCode = $attribute->getAttributeCode();
            $isDefaultStore = $valueRow['store_id'] == $this->getDefaultStoreId();
            if (isset($this->_attributes[$valueRow['attribute_id']])) {
                if ($isDefaultStore) {
                    $object->setAttributeDefaultValue($attributeCode, $valueRow['value']);
                } else {
                    $object->setAttributeDefaultValue(
                        $attributeCode,
                        $this->_attributes[$valueRow['attribute_id']]['value']
                    );
                }
            } else {
                $this->_attributes[$valueRow['attribute_id']] = $valueRow;
            }

            $value   = $valueRow['value'];
            $valueId = $valueRow['value_id'];

            $object->setData($attributeCode, $value);
            if (!$isDefaultStore) {
                $object->setExistsStoreValueFlag($attributeCode);
            }
//            $attribute->getBackend()->setEntityValueId($object, $valueId);
            $attribute->getBackend()->setValueId($valueId);
        }

        return $this;
    }

    /**
     * Initialize attribute value for object
     *
     * @deprecated after 1.5.1.0 - mistake in method name
     *
     * @param   Varien_Object $object
     * @param   array $valueRow
     * @return  Mage_Eav_Model_Entity_Abstract
     */
    protected function _setAttribteValue($object, $valueRow)
    {
        return $this->_setAttributeValue($object, $valueRow);
    }


    /**
     * Insert or Update attribute data
     *
     * @param Goodahead_Etm_Model_Entity $object
     * @param Mage_Eav_Model_Entity_Attribute_Abstract $attribute
     * @param mixed $value
     * @return Mage_Catalog_Model_Resource_Abstract
     */
    protected function _saveAttributeValue($object, $attribute, $value)
    {
        $write   = $this->_getWriteAdapter();
        $storeId = (int)Mage::app()->getStore($object->getStoreId())->getId();
        $table   = $attribute->getBackend()->getTable();

        /**
         * If we work in single store mode all values should be saved just
         * for default store id
         * In this case we clear all not default values
         */
        if (Mage::app()->isSingleStoreMode()) {
            $storeId = $this->getDefaultStoreId();
            $write->delete($table, array(
                'attribute_id = ?' => $attribute->getAttributeId(),
                'entity_id = ?'    => $object->getEntityId(),
                'store_id <> ?'    => $storeId
            ));
        }

        $data = new Varien_Object(array(
            'entity_type_id'    => $attribute->getEntityTypeId(),
            'attribute_id'      => $attribute->getAttributeId(),
            'store_id'          => $storeId,
            'entity_id'         => $object->getEntityId(),
            'value'             => $this->_prepareValueForSave($value, $attribute)
        ));
        $bind = $this->_prepareDataForTable($data, $table);

        if ($this->isScopeStore($attribute)) {
            /**
             * Update attribute value for store
             */
            $this->_attributeValuesToSave[$table][] = $bind;
        } else if ($this->isScopeWebsite($attribute) && $storeId != $this->getDefaultStoreId()) {
            /**
             * Update attribute value for website
             */
            $storeIds = Mage::app()->getStore($storeId)->getWebsite()->getStoreIds(true);
            foreach ($storeIds as $storeId) {
                $bind['store_id'] = (int)$storeId;
                $this->_attributeValuesToSave[$table][] = $bind;
            }
        } else {
            /**
             * Update global attribute value
             */
            $bind['store_id'] = $this->getDefaultStoreId();
            $this->_attributeValuesToSave[$table][] = $bind;
        }

        return $this;
    }

    /**
     * Insert entity attribute value
     *
     * @param Varien_Object $object
     * @param Mage_Eav_Model_Entity_Attribute_Abstract $attribute
     * @param mixed $value
     * @return Mage_Catalog_Model_Resource_Abstract
     */
    protected function _insertAttribute($object, $attribute, $value)
    {
        /**
         * save required attributes in global scope every time if store id different from default
         */
        $storeId = (int)Mage::app()->getStore($object->getStoreId())->getId();
        if ($attribute->getIsRequired() && $this->getDefaultStoreId() != $storeId) {
            $table = $attribute->getBackend()->getTable();

            $select = $this->_getReadAdapter()->select()
                ->from($table)
                ->where('entity_type_id = ?', $attribute->getEntityTypeId())
                ->where('attribute_id = ?', $attribute->getAttributeId())
                ->where('store_id = ?', $this->getDefaultStoreId())
                ->where('entity_id = ?',  $object->getEntityId());
            $row = $this->_getReadAdapter()->fetchOne($select);

            if (!$row) {
                $data  = new Varien_Object(array(
                    'entity_type_id'    => $attribute->getEntityTypeId(),
                    'attribute_id'      => $attribute->getAttributeId(),
                    'store_id'          => $this->getDefaultStoreId(),
                    'entity_id'         => $object->getEntityId(),
                    'value'             => $this->_prepareValueForSave($value, $attribute)
                ));
                $bind  = $this->_prepareDataForTable($data, $table);
                $this->_getWriteAdapter()->insertOnDuplicate($table, $bind, array('value'));
            }
        }

        return $this->_saveAttributeValue($object, $attribute, $value);
    }

    /**
     * Update entity attribute value
     *
     * @deprecated after 1.5.1.0
     * @see Mage_Catalog_Model_Resource_Abstract::_saveAttributeValue()
     *
     * @param Varien_Object $object
     * @param Mage_Eav_Model_Entity_Attribute_Abstract $attribute
     * @param mixed $valueId
     * @param mixed $value
     * @return Mage_Catalog_Model_Resource_Abstract
     */
    protected function _updateAttribute($object, $attribute, $valueId, $value)
    {
        return $this->_saveAttributeValue($object, $attribute, $value);
    }

//    /**
//     * Update attribute value for specific store
//     *
//     * @param Mage_Catalog_Model_Abstract $object
//     * @param object $attribute
//     * @param mixed $value
//     * @param int $storeId
//     * @return Mage_Catalog_Model_Resource_Abstract
//     */
//    protected function _updateAttributeForStore($object, $attribute, $value, $storeId)
//    {
//        $adapter = $this->_getWriteAdapter();
//        $table   = $attribute->getBackend()->getTable();
//        $entityIdField = $attribute->getBackend()->getEntityIdField();
//        $select  = $adapter->select()
//            ->from($table, 'value_id')
//            ->where('entity_type_id = :entity_type_id')
//            ->where("$entityIdField = :entity_field_id")
//            ->where('store_id = :store_id')
//            ->where('attribute_id = :attribute_id');
//        $bind = array(
//            'entity_type_id'  => $object->getEntityTypeId(),
//            'entity_field_id' => $object->getId(),
//            'store_id'        => $storeId,
//            'attribute_id'    => $attribute->getId()
//        );
//        $valueId = $adapter->fetchOne($select, $bind);
//        /**
//         * When value for store exist
//         */
//        if ($valueId) {
//            $bind  = array('value' => $this->_prepareValueForSave($value, $attribute));
//            $where = array('value_id = ?' => (int)$valueId);
//
//            $adapter->update($table, $bind, $where);
//        } else {
//            $bind  = array(
//                $idField            => (int)$object->getId(),
//                'entity_type_id'    => (int)$object->getEntityTypeId(),
//                'attribute_id'      => (int)$attribute->getId(),
//                'value'             => $this->_prepareValueForSave($value, $attribute),
//                'store_id'          => (int)$storeId
//            );
//
//            $adapter->insert($table, $bind);
//        }
//
//        return $this;
//    }

    /**
     * Delete entity attribute values
     *
     * @param Varien_Object $object
     * @param string $table
     * @param array $info
     * @return Mage_Catalog_Model_Resource_Abstract
     */
    protected function _deleteAttributes($object, $table, $info)
    {
        $adapter            = $this->_getWriteAdapter();
        $entityIdField      = $this->getEntityIdField();
        $globalValues       = array();
        $websiteAttributes  = array();
        $storeAttributes    = array();

        /**
         * Separate attributes by scope
         */
        foreach ($info as $itemData) {
            $attribute = $this->getAttribute($itemData['attribute_id']);
            if ($this->isScopeStore($attribute)) {
                $storeAttributes[] = (int)$itemData['attribute_id'];
            } elseif ($this->isScopeWebsite($attribute)) {
                $websiteAttributes[] = (int)$itemData['attribute_id'];
            } else {
                $globalValues[] = (int)$itemData['value_id'];
            }
        }

        /**
         * Delete global scope attributes
         */
        if (!empty($globalValues)) {
            $adapter->delete($table, array('value_id IN (?)' => $globalValues));
        }

        $condition = array(
            $entityIdField . ' = ?' => $object->getId(),
            'entity_type_id = ?'  => $object->getEntityTypeId()
        );

        /**
         * Delete website scope attributes
         */
        if (!empty($websiteAttributes)) {
            $storeIds = $object->getWebsiteStoreIds();
            if (!empty($storeIds)) {
                $delCondition = $condition;
                $delCondition['attribute_id IN(?)'] = $websiteAttributes;
                $delCondition['store_id IN(?)'] = $storeIds;

                $adapter->delete($table, $delCondition);
            }
        }

        /**
         * Delete store scope attributes
         */
        if (!empty($storeAttributes)) {
            $delCondition = $condition;
            $delCondition['attribute_id IN(?)'] = $storeAttributes;
            $delCondition['store_id = ?']       = (int)$object->getStoreId();

            $adapter->delete($table, $delCondition);
        }

        return $this;
    }

    /**
     * Collect original data
     *
     * @deprecated after 1.5.1.0
     *
     * @param Varien_Object $object
     * @return array
     */
    protected function _collectOrigData($object)
    {
        $this->loadAllAttributes($object);

        if ($this->getUseDataSharing()) {
            $storeId = $object->getStoreId();
        } else {
            $storeId = $this->getStoreId();
        }

        $data = array();
        foreach ($this->getAttributesByTable() as $table=>$attributes) {
            $select = $this->_getReadAdapter()->select()
                ->from($table)
                ->where($this->getEntityIdField() . '=?', $object->getId());

            $where = $this->_getReadAdapter()->quoteInto('store_id=?', $storeId);

            $globalAttributeIds = array();
            foreach ($attributes as $attr) {
                if ($attr->getIsGlobal()) {
                    $globalAttributeIds[] = $attr->getId();
                }
            }
            if (!empty($globalAttributeIds)) {
                $where .= ' or '.$this->_getReadAdapter()->quoteInto('attribute_id in (?)', $globalAttributeIds);
            }
            $select->where($where);

            $values = $this->_getReadAdapter()->fetchAll($select);

            if (empty($values)) {
                continue;
            }

            foreach ($values as $row) {
                $data[$this->getAttribute($row['attribute_id'])->getName()][$row['store_id']] = $row;
            }
        }
        return $data;
    }

    /**
     * Check is attribute value empty
     *
     * @param Mage_Eav_Model_Entity_Attribute_Abstract $attribute
     * @param mixed $value
     * @return bool
     */
    protected function _isAttributeValueEmpty(Mage_Eav_Model_Entity_Attribute_Abstract $attribute, $value)
    {
        return $value === false;
    }

    /**
     * Return if attribute exists in original data array.
     * Checks also attribute's store scope:
     * We should insert on duplicate key update values if we unchecked 'STORE VIEW' checkbox in store view.
     *
     * @param Mage_Eav_Model_Entity_Attribute_Abstract $attribute
     * @param mixed $value New value of the attribute.
     * @param array $origData
     * @return bool
     */
    protected function _canUpdateAttribute(
        Mage_Eav_Model_Entity_Attribute_Abstract $attribute,
        $value,
        array &$origData)
    {
        $result = parent::_canUpdateAttribute($attribute, $value, $origData);
        if ($result &&
            ($this->isScopeStore($attribute) || $this->isScopeWebsite($attribute)) &&
            !$this->_isAttributeValueEmpty($attribute, $value) &&
            $value == $origData[$attribute->getAttributeCode()] &&
            isset($origData['store_id']) && $origData['store_id'] != $this->getDefaultStoreId()
        ) {
            return false;
        }

        return $result;
    }

    /**
     * Prepare value for save
     *
     * @param mixed $value
     * @param Mage_Eav_Model_Entity_Attribute_Abstract $attribute
     * @return mixed
     */
    protected function _prepareValueForSave($value, Mage_Eav_Model_Entity_Attribute_Abstract $attribute)
    {
        $type = $attribute->getBackendType();
        if (($type == 'int' || $type == 'decimal' || $type == 'datetime') && $value === '') {
            $value = null;
        }

        return parent::_prepareValueForSave($value, $attribute);
    }

    /**
     * Prepare data for passed table
     *
     * @param Varien_Object $object
     * @param string $table
     * @return array
     */
    protected function _prepareDataForTable(Varien_Object $object, $table)
    {
        if (method_exists(get_parent_class(__CLASS__), '_prepareDataForTable')) {
            return parent::_prepareDataForTable($object, $table);
        } else {
            /**
             * Compatibility part 1.4 - 1.5
             */
            $data = array();
            $fields = $this->_getWriteAdapter()->describeTable($table);
            foreach (array_keys($fields) as $field) {
                if ($object->hasData($field)) {
                    $fieldValue = $object->getData($field);
                    if ($fieldValue instanceof Zend_Db_Expr) {
                        $data[$field] = $fieldValue;
                    } else {
                        if (null !== $fieldValue) {
                            $fieldValue   = $this->_prepareTableValueForSave($fieldValue, $fields[$field]['DATA_TYPE']);
                            // Method not available in old magento
                            //$fieldValue = $this->_getWriteAdapter()->prepareColumnValue($fields[$field], $fieldValue);
                            $data[$field] = $fieldValue;
                        } else if (!empty($fields[$field]['NULLABLE'])) {
                            $data[$field] = null;
                        }
                    }
                }
            }
            return $data;
        }
    }

    /**
     * Prepare value for save
     *
     * @param mixed $value
     * @param string $type
     * @return mixed
     */
    protected function _prepareTableValueForSave($value, $type)
    {
        if (method_exists(get_parent_class(__CLASS__), '_prepareTableValueForSave')) {
            return parent::_prepareTableValueForSave($value, $type);
        } else {
            $type = strtolower($type);
            if ($type == 'decimal' || $type == 'numeric' || $type == 'float') {
                $value = Mage::app()->getLocale()->getNumber($value);
            }
            return $value;
        }
    }

    /**
     * Retrieve attribute's raw value from DB.
     *
     * @param int $entityId
     * @param int|string|array $attribute atrribute's ids or codes
     * @param int|Mage_Core_Model_Store $store
     * @return bool|string|array
     */
    public function getAttributeRawValue($entityId, $attribute, $store)
    {
        if (!$entityId || empty($attribute)) {
            return false;
        }
        if (!is_array($attribute)) {
            $attribute = array($attribute);
        }

        $attributesData     = array();
        $staticAttributes   = array();
        $typedAttributes    = array();
        $staticTable        = null;
        $adapter            = $this->_getReadAdapter();

        foreach ($attribute as $_attribute) {
            /* @var $attribute Mage_Catalog_Model_Entity_Attribute */
            $_attribute = $this->getAttribute($_attribute);
            if (!$_attribute) {
                continue;
            }
            $attributeCode = $_attribute->getAttributeCode();
            $attrTable     = $_attribute->getBackend()->getTable();
            $isStatic      = $_attribute->getBackend()->isStatic();

            if ($isStatic) {
                $staticAttributes[] = $attributeCode;
                $staticTable = $attrTable;
            } else {
                /**
                 * That structure needed to avoid farther sql joins for getting attribute's code by id
                 */
                $typedAttributes[$attrTable][$_attribute->getId()] = $attributeCode;
            }
        }

        /**
         * Collecting static attributes
         */
        if ($staticAttributes) {
            $select = $adapter->select()->from($staticTable, $staticAttributes)
                ->where($this->getEntityIdField() . ' = :entity_id');
            $attributesData = $adapter->fetchRow($select, array('entity_id' => $entityId));
        }

        /**
         * Collecting typed attributes, performing separate SQL query for each attribute type table
         */
        if ($store instanceof Mage_Core_Model_Store) {
            $store = $store->getId();
        }

        $store = (int)$store;
        if ($typedAttributes) {
            foreach ($typedAttributes as $table => $_attributes) {
                $select = $adapter->select()
                    ->from(array('default_value' => $table), array('attribute_id'))
                    ->where('default_value.attribute_id IN (?)', array_keys($_attributes))
                    ->where('default_value.entity_type_id = :entity_type_id')
                    ->where('default_value.entity_id = :entity_id')
                    ->where('default_value.store_id = ?', 0);
                $bind = array(
                    'entity_type_id' => $this->getTypeId(),
                    'entity_id'      => $entityId,
                );

                if ($store != $this->getDefaultStoreId()) {
                    $valueExpr = $adapter->getCheckSql('store_value.value IS NULL',
                        'default_value.value', 'store_value.value');
                    $joinCondition = array(
                        $adapter->quoteInto('store_value.attribute_id IN (?)', array_keys($_attributes)),
                        'store_value.entity_type_id = :entity_type_id',
                        'store_value.entity_id = :entity_id',
                        'store_value.store_id = :store_id',
                    );

                    $select->joinLeft(
                        array('store_value' => $table),
                        implode(' AND ', $joinCondition),
                        array('attr_value' => $valueExpr)
                    );

                    $bind['store_id'] = $store;

                } else {
                    $select->columns(array('attr_value' => 'value'), 'default_value');
                }

                $result = $adapter->fetchPairs($select, $bind);
                foreach ($result as $attrId => $value) {
                    $attrCode = $typedAttributes[$table][$attrId];
                    $attributesData[$attrCode] = $value;
                }
            }
        }

        if (sizeof($attributesData) == 1) {
            $_data = each($attributesData);
            $attributesData = $_data[1];
        }

        return $attributesData ? $attributesData : false;
    }

    /**
     * Reset firstly loaded attributes
     *
     * @param Varien_Object $object
     * @param integer $entityId
     * @param array|null $attributes
     * @return Mage_Catalog_Model_Resource_Abstract
     */
    public function load($object, $entityId, $attributes = array())
    {
        $this->_attributes = array();
        return parent::load($object, $entityId, $attributes);
    }

    public function getEntityTypeId($entity, $field = null)
    {
        if ($entity instanceof Goodahead_Etm_Model_Entity) {
            $entityId = $entity->getId();
        } else {
            $entityId = $entity;
        }

        $select = $this->getReadConnection()->select();
        $select->from($this->getEntityTable(), array('entity_type_id'));

        $field = $field ? $field : 'entity_id';

        $select->where($this->getReadConnection()->quoteIdentifier($field) . ' = ?', $entityId);

        return  $this->getReadConnection()->fetchOne($select);
    }

}
