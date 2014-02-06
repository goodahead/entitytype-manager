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

class Goodahead_Etm_Block_Adminhtml_Attribute
    extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    /**
     * Get entity type object from registry
     *
     * @return Mage_Eav_Model_Entity_Type
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

    public function __construct()
    {
        $this->_blockGroup = 'goodahead_etm';
        $this->_controller = 'adminhtml_attribute';

        $typeName = $this->_getEntityTypeFromRegistry()->getEntityTypeName();
        $this->_headerText = Mage::helper('goodahead_etm')->__("Manage '%s' Attributes", $typeName);

        $this->_backButtonLabel = Mage::helper('goodahead_etm')->__('Back to Entity Types List');
        $this->_addBackButton();

        parent::__construct();

        $this->_updateButton('back', 'onclick', 'setLocation(\'' . $this->getUrl('*/etm_entityType') . '\')');

        $addUrl = $this->getUrl('*/*/new', array(
            'entity_type_id' => $this->_getEntityTypeFromRegistry()->getId(),
        ));
        $this->_updateButton('add', 'label', Mage::helper('catalog')->__('Add New Attribute'));
        $this->_updateButton('add', 'onclick', 'setLocation(\'' . $addUrl . '\')');
    }
}
