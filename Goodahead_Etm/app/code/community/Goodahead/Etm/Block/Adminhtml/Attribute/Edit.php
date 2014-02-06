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

class Goodahead_Etm_Block_Adminhtml_Attribute_Edit
    extends Mage_Adminhtml_Block_Widget_Form_Container
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

    /**
     * Initialize edit form container
     */
    public function __construct()
    {
        $this->_objectId   = 'attribute_id';
        $this->_blockGroup = 'goodahead_etm';
        $this->_controller = 'adminhtml_attribute';

        parent::__construct();

        if ($this->_isAllowedAction('save')) {
            $this->_updateButton('save', 'label', Mage::helper('catalog')->__('Save Attribute'));
            $this->_addButton('saveandcontinue', array(
                'label'     => Mage::helper('catalog')->__('Save and Continue Edit'),
                'onclick'   => 'saveAndContinueEdit()',
                'class'     => 'save',
            ), -100);
        } else {
            $this->_removeButton('save');
        }

        if ($this->_isAllowedAction('delete')) {
            $this->_updateButton('delete', 'label', Mage::helper('catalog')->__('Delete Attribute'));
        } else {
            $this->_removeButton('delete');
        }

        $deleteUrl = $this->getUrl('*/*/delete', array(
            'entity_type_id' => $this->_getEntityTypeFromRegistry()->getId(),
            'attribute_id'   => $this->getRequest()->getParam('attribute_id'),
        ));
        $this->_updateButton('delete', 'onclick', 'deleteConfirm(\''
            . Mage::helper('goodahead_etm')->__('Are you sure you want to delete attribute?'). '\', \''
            . $deleteUrl . '\')'
        );
        $backUrl = $this->getUrl('*/*/index', array(
            'entity_type_id' => $this->_getEntityTypeFromRegistry()->getId(),
        ));
        $this->_updateButton('back', 'onclick', 'setLocation(\'' . $backUrl . '\')');

        $this->_formScripts[] = "
            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    /**
     * Retrieve text for header element depending on loaded page
     *
     * @return string
     */
    public function getHeaderText()
    {
        /** @var Goodahead_Etm_Model_Entity_Attribute $attribute */
        $attribute = Mage::registry('etm_attribute');
        if ($attribute->getId()) {
            return Mage::helper('goodahead_etm')->__("Edit Attribute with Code '%s'",
                 $this->escapeHtml($attribute->getAttributeCode())
            );
        } else {
            return Mage::helper('adminhtml')->__('New Attribute');
        }
    }

    /**
     * Check permission for passed action
     *
     * @param string $action
     * @return bool
     */
    protected function _isAllowedAction($action)
    {
        switch ($action) {
            case 'edit':
            case 'save':
                return Mage::getSingleton('admin/session')->isAllowed('goodahead_etm/manage_attributes/save');
                break;
            case 'delete':
                return Mage::getSingleton('admin/session')->isAllowed('goodahead_etm/manage_attributes/delete');
                break;
            case 'index':
            default:
                return Mage::getSingleton('admin/session')->isAllowed('goodahead_etm/manage_attributes');
                break;
        }
    }
}
