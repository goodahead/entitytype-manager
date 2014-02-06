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

class Goodahead_Etm_Block_Adminhtml_Entity_Edit
    extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        $this->_objectId   = 'entity_id';
        $this->_blockGroup = 'goodahead_etm';
        $this->_controller = 'adminhtml_entity';

        parent::__construct();

        if ($this->_isAllowedAction('save')) {
            $this->_updateButton('save', 'label', Mage::helper('goodahead_etm')->__('Save Entity'));
            $this->_addButton('saveandcontinue', array(
                'label'     => Mage::helper('catalog')->__('Save and Continue Edit'),
                'onclick'   => 'saveAndContinueEdit()',
                'class'     => 'save',
            ), -100);
        } else {
            $this->_removeButton('save');
        }

        if ($this->_isAllowedAction('delete')) {
            $this->_updateButton('delete', 'label', Mage::helper('goodahead_etm')->__('Delete Entity'));
        } else {
            $this->_removeButton('delete');
        }

        $entityTypeId = $this->getRequest()->getParam('entity_type_id');
        $this->_updateButton('back', 'onclick', 'setLocation(\'' .
            $this->getUrl('*/etm_entity', array(
                'entity_type_id' => $entityTypeId,
                'store' => $this->getRequest()->getParam('store'),
            )) . '\')'
        );

        $entityId = $this->getRequest()->getParam('entity_id');
        if ($entityId) {
            $this->_addButton('duplicate', array(
                'label'     => Mage::helper('catalog')->__('Duplicate'),
                'onclick'   => 'setLocation(\'' . $this->_getDuplicateUrl($entityId, $entityTypeId) . '\')',
            ), 0);
        }

        $this->_formScripts[] = "
            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    protected function _getDuplicateUrl($entityId, $entityTypeId)
    {
        return $this->getUrl('*/etm_entity/duplicate', array(
            'entity_type_id' => $entityTypeId,
            'entity_id'      => $entityId,
            'store' => $this->getRequest()->getParam('store'),
        ));
    }

    public function getDeleteUrl()
    {
        $entityTypeId = $this->getRequest()->getParam('entity_type_id');

        return $this->getUrl('*/*/delete', array(
            $this->_objectId => $this->getRequest()->getParam($this->_objectId),
            'entity_type_id' => $entityTypeId,
            'store' => $this->getRequest()->getParam('store'),
        ));
    }

    /**
     * Retrieve text for header element depending on loaded page
     *
     * @return string
     */
    public function getHeaderText()
    {
        /** @var Goodahead_Etm_Model_Entity $entity */
        $entity = Mage::registry('etm_entity');
        if ($entity->getId()) {
            return $this->__('Edit %1$s \'%2$s\'',
                $this->escapeHtml($entity->getEntityTypeInstance()->getEntityTypeName()),
                $this->escapeHtml($entity->getEntitylabel())
            );
        } else {
            $entityType = Mage::registry('etm_entity_type');
            return $this->__('New %s', $this->escapeHtml(
                    $entityType->getEntityTypeName()));
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
                return Mage::getSingleton('admin/session')->isAllowed('goodahead_etm/manage_entity/save');
                break;
            case 'delete':
                return Mage::getSingleton('admin/session')->isAllowed('goodahead_etm/manage_entity/delete');
                break;
            case 'index':
            default:
                return Mage::getSingleton('admin/session')->isAllowed('goodahead_etm/manage_entity');
                break;
        }
    }
}
