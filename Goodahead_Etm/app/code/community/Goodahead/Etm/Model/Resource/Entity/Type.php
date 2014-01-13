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

class Goodahead_Etm_Model_Resource_Entity_Type
    extends Mage_Eav_Model_Mysql4_Entity_Type // Compatibility with older versions
{
    protected function _construct()
    {
        parent::_construct();
        $this->_setResource(null, array('etm_entity_type' => 'goodahead_etm/eav_entity_type'));
    }

    /**
     * @param int $entityId
     * @return int|bool
     */
    public function getEntityTypeIdByEntityId($entityId)
    {
        $select = $this
            ->getReadConnection()
            ->select()
            ->from($this->getTable('goodahead_etm/entity'), array('entity_type_id'))
            ->where($this->getReadConnection()->quoteInto('entity_id =?', $entityId));

        return $this->getReadConnection()->fetchOne($select);
    }

    protected function _getLoadSelect($field, $value, $object)
    {
        $select = parent::_getLoadSelect($field, $value, $object);
        $select->joinInner(array('etm_entity_type' => $this->getTable('etm_entity_type')),
            $this->getMainTable() . '.entity_type_id = etm_entity_type.entity_type_id'
        );

        return $select;
    }

    protected function _beforeSave(Mage_Core_Model_Abstract $object)
    {
//        if ($object->isObjectNew()) {
//            $object->setCreateAttributeSet(true);
//        }
        return $this;
    }

    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
        parent::_afterSave($object);

        $tableName = $this->getTable('etm_entity_type');
        $this->_getWriteAdapter()->insertOnDuplicate($tableName, $this->_prepareDataForTable($object, $tableName));

        // create attribute set and group if new
//        if ($object->getCreateAttributeSet() === true) {
//            $setup = Mage::getResourceModel('goodahead_etm/entity_setup', 'core_setup');
//            $setup->addAttributeSet($object->getEntityTypeCode(), $setup->getDefaultAttributeSetName());
//            // set to entity type default attribute set id
//            $defaultAttributeSet = Mage::getModel('eav/entity_attribute_set')->load($object->getId(), 'entity_type_id');
//            $object->setDefaultAttributeSetId($defaultAttributeSet->getId());
//
//
//            $setup->addAttributeGroup($object->getEntityTypeCode(), $setup->getDefaultGroupName(), $setup->getGeneralGroupName());
//        }
    }
}
