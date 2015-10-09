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

class Goodahead_Etm_Adminhtml_Etm_AttributeController
    extends Goodahead_Etm_Controller_Adminhtml
{
    /**
     * Entity Type Manager index page
     */
    public function indexAction()
    {
        try {
            $this->_initEntityType();

            $this->_initAction(Mage::helper('catalog')->__('Manage Attributes'));
            $this->renderLayout();
        } catch (Goodahead_Etm_Exception $e) {
            Mage::logException($e);
            $this->_forward('no_route');
        }
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

    /**
     * Grid ajax action
     */
    public function gridAction()
    {
        try {
            $this->_initEntityType();

            $this->loadLayout();
            $this->renderLayout();
        } catch (Goodahead_Etm_Exception $e) {
            Mage::logException($e);
            $this->_forward('no_route');
        }
    }

    /**
     * Delete attribute action
     *
     * @return void
     */
    public function deleteAction()
    {
        $attributeId = $this->getRequest()->getParam('attribute_id');
        if ($attributeId) {
            try {
                $this->_initEntityType();

                /** @var Goodahead_Etm_Model_Entity_Attribute $attributeModel */
                $attributeModel = Mage::getSingleton('goodahead_etm/entity_attribute');
                $attributeModel->load($attributeId);
                if (!$attributeModel->getId()) {
                    Mage::throwException(Mage::helper('goodahead_etm')->__('Unable to find attribute.'));
                }
                $attributeModel->delete();

                $this->_getSession()->addSuccess(
                    Mage::helper('goodahead_etm')->__(
                        "Attribute with code '%s' has been deleted.",
                        $attributeModel->getAttributeCode()
                    )
                );
            } catch (Goodahead_Etm_Exception $e) {
                $this->_getSession()->addException($e,
                    Mage::helper('goodahead_etm')->__('You are not allowed to delete non-custom attribute')
                );
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            } catch (Exception $e) {
                $this->_getSession()->addException($e,
                    Mage::helper('goodahead_etm')->__('An error occurred while deleting attribute.')
                );
            }
        }

        // go to grid
        $arguments    = array();
        $entityTypeId = $this->getRequest()->getParam('entity_type_id');
        if (!empty($entityTypeId)) {
            $arguments = array('entity_type_id' => $entityTypeId);
        }
        $this->_redirect('*/*/', $arguments);
    }

    /**
     * Mass delete attributes action
     *
     * @return void
     */
    public function massDeleteAction()
    {
        $attributeIds = $this->getRequest()
            ->getParam('attribute_ids');

        if (!is_array($attributeIds)) {
            Mage::getSingleton('adminhtml/session')->addError(
                $this->__('Please select attribute(s) to delete.')
            );
        } else {
            try {
                $this->_initEntityType();

                /** @var Goodahead_Etm_Model_Entity_Attribute $attributeModel */
                $attributeModel = Mage::getSingleton('goodahead_etm/entity_attribute');
                $attributeModel->deleteAttributes($attributeIds);

                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('goodahead_etm')->__(
                        '%d attribute(s) were deleted.', count($attributeIds)
                    )
                );
            } catch (Goodahead_Etm_Exception $e) {
                $this->_getSession()->addException($e,
                    Mage::helper('goodahead_etm')->__('You are not allowed to delete non-custom attributes')
                );
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            } catch (Exception $e) {
                $this->_getSession()->addException($e,
                    Mage::helper('goodahead_etm')->__('An error occurred while deleting attributes.')
                );
            }
        }

        // go to grid
        $arguments = array();
        $entityTypeId = $this->getRequest()->getParam('entity_type_id');
        if (!empty($entityTypeId)) {
            $arguments = array('entity_type_id' => $this->getRequest()->getParam('entity_type_id'));
        }
        $this->_redirect('*/*/', $arguments);
    }

    /**
     * Create new attribute
     *
     * @return void
     */
    public function newAction()
    {
        // the same form is used to create and edit
        $this->_forward('edit');
    }

    /**
     * Edit attribute
     *
     * @return void
     */
    public function editAction()
    {
        try {
            $this->_initEntityType();

            /** @var Goodahead_Etm_Model_Entity_Attribute $attributeModel */
            $attributeModel = Mage::getSingleton('goodahead_etm/entity_attribute');

            $attributeId = $this->getRequest()->getParam('attribute_id');
            if ($attributeId) {
                $attributeModel->load($attributeId);

                if (!$attributeModel->getId()) {
                    Mage::throwException(Mage::helper('goodahead_etm')->__('Unable to find attribute.'));
                }

                if ($attributeModel->isSystem()) {
                    Mage::throwException(Mage::helper('goodahead_etm')->__('You cannot edit System attributes.'));
                }

                $this->_initAction($this->__("Edit Attribute with Code '%s'", $attributeModel->getAttributeCode()));
            } else {
                $this->_initAction(Mage::helper('adminhtml')->__('New Attribute'));
            }

            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
            if (!empty($data)) {
                $attributeModel->addData($data);
            }

            Mage::register('etm_attribute', $attributeModel);

            $block = $this->getLayout()->getBlock('catalog.wysiwyg.js');
            if ($block) {
                $block->setStoreId(Mage_Core_Model_App::ADMIN_STORE_ID);
            }
            $this->renderLayout();
            return;
        } catch (Goodahead_Etm_Exception $e) {
            $this->_getSession()->addException($e,
                Mage::helper('goodahead_etm')->__('You are not allowed to edit non-custom attribute')
            );
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            $this->_getSession()->addException($e,
                Mage::helper('goodahead_etm')->__('An error occurred while opening attribute.')
            );
        }

        // go to grid
        $arguments    = array();
        $entityTypeId = $this->getRequest()->getParam('entity_type_id');
        if (!empty($entityTypeId)) {
            $arguments = array('entity_type_id' => $entityTypeId);
        }
        $this->_redirect('*/*/', $arguments);
    }

    /**
     * Save action
     */
    public function saveAction()
    {
        $redirectPath   = '*/*/';
        $redirectParams = array();

        // check if data sent
        $data         = $this->getRequest()->getPost();
        $entityTypeId = $this->getRequest()->getParam('entity_type_id');
        if (!empty($entityTypeId)) {
            $redirectParams = array('entity_type_id' => $entityTypeId);
            $data['entity_type_id'] = $entityTypeId;
        }
        if ($data) {
            try {
                $hasError = false;
                $this->_initEntityType();

                /** @var Goodahead_Etm_Model_Entity_Attribute $attributeModel */
                $attributeModel = Mage::getModel('goodahead_etm/entity_attribute');

                // if attribute exists, try to load it
                $frontendInput = $this->getRequest()->getParam('frontend_input');
                $attributeId = $this->getRequest()->getParam('attribute_id');
                if ($attributeId) {
                    $attributeModel->load($attributeId);
                    if ($attributeModel->hasData('frontend_input')) {
                        $frontendInput = $attributeModel->getData('frontend_input');
                    }
                } else {
                    // TODO: Rework to use setup model addAttribute method
                    $attributeModel->setIsUserDefined(1);
                }

                $defaultValueField = $attributeModel->getDefaultValueByInput($frontendInput);
                if ($defaultValueField) {
                    $data['default_value'] = $this->getRequest()->getParam($defaultValueField);
                }


                $attributeModel->addData($data);
                $attributeModel->save();
                $this->_getSession()->addSuccess(
                    Mage::helper('goodahead_etm')->__('Attribute has been saved.')
                );

                // check if 'Save and Continue'
                if ($this->getRequest()->getParam('back')) {
                    $redirectPath   = '*/*/edit';
                    $redirectParams['attribute_id'] = $attributeModel->getId();
                }
            } catch (Goodahead_Etm_Exception $e) {
                $hasError = true;
                $this->_getSession()->addException($e,
                    Mage::helper('goodahead_etm')->__('You are not allowed to edit non-custom attribute')
                );
            } catch (Mage_Eav_Exception $e) {
                $hasError = true;
                $this->_getSession()->addError($e->getMessage());
            } catch (Mage_Core_Exception $e) {
                $hasError = true;
                $this->_getSession()->addError($e->getMessage());
            } catch (Exception $e) {
                $hasError = true;
                $this->_getSession()->addException($e,
                    Mage::helper('goodahead_etm')->__('An error occurred while saving attribute.')
                );
            }

            if ($hasError) {
                $this->_getSession()->setFormData($data);
                $redirectPath = '*/*/edit';
                if ($attributeModel->getId()) {
                    $redirectParams['attribute_id'] = $attributeId;
                }
            }
        }

        $this->_redirect($redirectPath, $redirectParams);
    }
}
