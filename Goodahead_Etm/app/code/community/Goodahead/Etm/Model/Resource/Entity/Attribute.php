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

class Goodahead_Etm_Model_Resource_Entity_Attribute
//    extends Mage_Eav_Model_Resource_Entity_Attribute
    extends Mage_Eav_Model_Mysql4_Entity_Attribute // Compatibility with older versions
{
    protected function _getLoadSelect($field, $value, $object)
    {
        $select = parent::_getLoadSelect($field, $value, $object);
//        $select->joinInner(
//            array('etm_entity_type' => $this->getTable('goodahead_etm/eav_entity_type')),
//            $this->getMainTable() . '.entity_type_id = etm_entity_type.entity_type_id'
//        );
        $select->joinInner(
            array('etm_attribute' => $this->getTable('goodahead_etm/eav_attribute')),
            $this->getMainTable() . '.attribute_id = etm_attribute.attribute_id'
        );

        return $select;
    }

    /**
     * @param Goodahead_Etm_Model_Attribute|Mage_Core_Model_Abstract $object
     * @return Mage_Eav_Model_Resource_Entity_Attribute
     */
    protected function _beforeSave(Mage_Core_Model_Abstract $object)
    {
        if ($object->isObjectNew()) {
            /* @var $helper Mage_Catalog_Helper_Product */
            $helper = Mage::helper('catalog/product');

            $object->setIsUserDefined(1);
            $object->setSourceModel(Mage::helper('goodahead_etm')->getAttributeSourceModelByInputType($object->getFrontendInput()));
            $object->setBackendModel($helper->getAttributeBackendModelByInputType($object->getFrontendInput()));
            $object->setBackendType($object->getBackendTypeByInput($object->getFrontendInput()));
        }

        return parent::_beforeSave($object);
    }

    /**
     * @param Goodahead_Etm_Model_Attribute|Mage_Core_Model_Abstract $object
     * @return Mage_Eav_Model_Resource_Entity_Attribute|void
     */
    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
        parent::_afterSave($object);

        $tableName = $this->getTable('goodahead_etm/eav_attribute');
        $this->_getWriteAdapter()->insertOnDuplicate($tableName, $this->_prepareDataForTable($object, $tableName));
    }

    /**
     * Delete attributes with given ids
     *
     * @param array $attributeIds
     * @return $this
     */
    public function deleteAttributes(array $attributeIds)
    {
        /** @var Goodahead_Etm_Model_Resource_Entity_Attribute_Collection $collection */
        $collection = Mage::getResourceModel('goodahead_etm/entity_attribute_collection');
        $collection->addFieldToFilter('main_table.attribute_id', $attributeIds);

        // we should delete only attributes which belong to our custom entity types
        $attributeIdsToDelete = $collection->getAllIds();

        $this->_getWriteAdapter()->delete($this->getMainTable(),
            array('attribute_id IN(?)' => $attributeIdsToDelete)
        );

        return $this;
    }
}
