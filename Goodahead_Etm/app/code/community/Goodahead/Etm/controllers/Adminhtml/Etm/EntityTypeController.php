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

class Goodahead_Etm_Adminhtml_Etm_EntityTypeController
    extends Goodahead_Etm_Controller_Adminhtml
{
    /**
     * Entity Type Manager index page
     */
    public function indexAction()
    {
        $this->_initAction($this->__('Manage Entity Types'));
        $this->renderLayout();
    }

    /**
     * Grid ajax action
     */
    public function gridAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    /* Deletes  entity types */
    public function deleteAction()
    {
        $entityType = Mage::getModel('eav/entity_type')->load($this->getRequest()->getParam('entity_type_id', null));

        if ($entityType && $entityType->getId()) {
            try {
                $entityType->delete();
                $this->_getSession()->addSuccess($this->__('Entity type successfully deleted'));
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }

        $this->_redirect('*/*/');
    }

    public function massDeleteAction()
    {
        $etmEntityTypes = $this->getRequest()->getParam('entity_type_ids');
        if (!is_array($etmEntityTypes)) {
            $this->_getSession()->addError($this->__('Please select entity type(s).'));
        } else {
            if (!empty($etmEntityTypes)) {
                try {
                    foreach ($etmEntityTypes as $entityTypeId) {
                        Mage::getModel('eav/entity_type')->setId($entityTypeId)->delete();
                    }
                    $this->_getSession()->addSuccess(
                        Mage::helper('adminhtml')->__('Total of %d record(s) have been deleted.', count($etmEntityTypes))
                    );
                } catch (Exception $e) {
                    $this->_getSession()->addError($e->getMessage());
                }
            }
        }
        $this->_redirectReferer();
    }



    /**
     * ACL check
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        switch ($this->getRequest()->getActionName()) {
            case 'edit':
            case 'save':
                return Mage::getSingleton('admin/session')->isAllowed('goodahead_etm/manage_entityTypes/save');
                break;
            case 'delete':
                return Mage::getSingleton('admin/session')->isAllowed('goodahead_etm/manage_entityTypes/delete');
                break;
            case 'index':
            default:
                return Mage::getSingleton('admin/session')->isAllowed('goodahead_etm/manage_entityTypes');
                break;
        }
    }

    public function newAction()
    {
        $this->_forward('edit');
    }

    public function editAction()
    {
        $this->_initAction($this->__('Create Entity Type'));
        try {
            $this->_initEntityType();
        } catch (Goodahead_Etm_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
            $this->_redirect('*/*/');
        }

        // set entered data if was error when we do save
        $data = Mage::getSingleton('adminhtml/session')->getEntityTypeData(true);

        // restore data from SESSION
        if ($data) {
            $request = clone $this->getRequest();
            $request->setParams($data);
        }
        $this->renderLayout();
    }

    public function saveAction()
    {
        $redirectPath   = '*/*';
        $redirectParams = array();

        $entityTypeId = $this->getRequest()->getPost('entity_type_id', null);

        /** @var Goodahead_Etm_Model_Entity_Type $entityTypeModel */
        $entityTypeModel = Mage::getModel('goodahead_etm/entity_type');

        if ($this->getRequest()->getPost()) {
            try {
                $hasError = false;
                $entityTypeModel->load($entityTypeId);

                $postFields = array(
//                    'entity_type_code',
                    'entity_type_name',
                    'entity_type_root_template',
                    'entity_type_layout_xml',
                    'entity_type_content',
                    'default_attribute_id'
                );
                $postData = array();
                foreach ($postFields as $_field) {
                    $value = $this->getRequest()->getPost($_field, null);
                    if (isset($value)) {
                        $postData[$_field] = $value;
                    }
                }
                if (!$entityTypeModel->getId()) {
                    $entityTypeCode = $this->getRequest()->getPost('entity_type_code', null);
                    if (!isset($entityTypeCode)) {
                        Mage::throwException($this->__('Entity Type Code required'));
                    }
                    /** @var $setup Goodahead_Etm_Model_Resource_Entity_Setup */
                    $setup = Mage::getResourceModel('goodahead_etm/entity_setup', 'core_setup');
                    if ($setup->getEntityType($entityTypeCode, 'entity_type_id')) {
                        Mage::throwException($this->__(
                            'Entity Type with Code \'%s\' already exists',
                            $entityTypeCode));
                    }

                    $setup->addEntityType(
                        $entityTypeCode,
                        array_merge(
                            $postData,
                            array(
                                'entity_model'             => sprintf('goodahead_etm/custom_%s_entity', $entityTypeCode),
                                'attribute_model'          => 'goodahead_etm/entity_attribute',
                                'table'                    => 'goodahead_etm/entity',
                                'create_system_attributes' => true,
                            )
                        )
                    );
                    $entityTypeId = $setup->getEntityType($entityTypeCode, 'entity_type_id');
                    $entityTypeModel->load($entityTypeId);
                    Mage::app()->cleanCache(array(Mage_Adminhtml_Block_Page_Menu::CACHE_TAGS));
                } else {
                    $entityTypeModel->addData($postData);
                    $entityTypeModel->save();
                }

                $this->_getSession()->addSuccess(
                    Mage::helper('goodahead_etm')->__('Entity type "%s" successfully saved', $entityTypeModel->getEntityTypeName())
                );

                // check if 'Save and Continue'
                if ($this->getRequest()->getParam('back')) {
                    $redirectPath   = '*/*/edit';
                    $redirectParams['entity_type_id'] = $entityTypeModel->getId();
                }
            } catch (Goodahead_Etm_Exception $e) {
                $hasError = true;
                $this->_getSession()->addException($e,
                    Mage::helper('goodahead_etm')->__('You are not allowed to edit non-custom entity type')
                );
            } catch (Mage_Core_Exception $e) {
                $hasError = true;
                $this->_getSession()->addError($e->getMessage());
            } catch (Exception $e) {
                $hasError = true;
                $this->_getSession()->addException($e,
                    Mage::helper('goodahead_etm')->__('An error occurred while saving entity type.')
                );
            }

            if ($hasError) {
                $this->_getSession()->setFormData($postData);
                $redirectPath   = '*/*/edit';
                if ($entityTypeModel->getId()) {
                    $redirectParams['entity_type_id'] = $entityTypeId;
                }
            }
        }

        $this->_redirect($redirectPath, $redirectParams);
    }
}
