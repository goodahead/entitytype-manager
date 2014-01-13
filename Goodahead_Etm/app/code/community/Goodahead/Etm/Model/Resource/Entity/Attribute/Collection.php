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

class Goodahead_Etm_Model_Resource_Entity_Attribute_Collection
//    extends Mage_Eav_Model_Resource_Entity_Attribute_Collection
    extends Mage_Eav_Model_Mysql4_Entity_Attribute_Collection // Compatibility with older versions
{
    /**
     * @var Goodahead_Etm_Model_Entity_Type
     */
    protected $_entityType;

    /**
     * @return Goodahead_Etm_Model_Entity_Type
     */
    public function getEntityType()
    {
        if (!$this->_entityType) {
            $this->_entityType = $this->_getEntityTypeFromRegistry();
        }
        return $this->_entityType;
    }

    /**
     *
     */
    public function setEntityType($entityType)
    {
        if ($entityType instanceof Goodahead_Etm_Model_Entity_Type) {
            $this->_entityType = $entityType;
        } else {
            $this->_entityType = Mage::getModel('goodahead_etm/entity_type')->load($entityType);
        }
        if (!$this->_entityType->getId()) {
            $helper = Mage::helper('goodahead_etm');
            throw new Goodahead_Etm_Exception($helper->__('Entity type not found'));
        }
        $this->addFieldToFilter('main_table.entity_type_id', $this->getEntityType()->getId());

        return $this;
    }

    public function toOptionArray()
    {
        return $this->_toOptionArray('attribute_id', 'frontend_label');
    }

    /**
     * Get entity type object from registry
     *
     * @return Goodahead_Etm_Model_Entity_Type
     * @throws Goodahead_Etm_Exception
     */
    protected function _getEntityTypeFromRegistry()
    {
        /** @var Mage_Eav_Model_Entity_Type $entityType */
        $entityType = Mage::registry('etm_entity_type');
        if ($entityType && $entityType->getId()) {
            return $entityType;
        }

        $helper = Mage::helper('goodahead_etm');
        throw new Goodahead_Etm_Exception($helper->__('Entity type object is absent in registry'));
    }

    /**
     * Initialize collection select
     *
     * @return $this
     */
    protected function _initSelect()
    {
        parent::_initSelect();

        $this->getSelect()->joinInner(
            array('etm_entity_type' => $this->getTable('goodahead_etm/eav_entity_type')),
            'main_table.entity_type_id = etm_entity_type.entity_type_id',
            array()
        );
        $this->getSelect()->joinInner(
            array('etm_attribute' => $this->getTable('goodahead_etm/eav_attribute')),
            'main_table.attribute_id = etm_attribute.attribute_id'
        );

        return $this;
    }
}
