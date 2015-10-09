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

class Goodahead_Etm_Adminhtml_Etm_EntityController
    extends Goodahead_Etm_Controller_Adminhtml
{
    /**
     * Entity grid page
     */
    public function indexAction()
    {
        try {
            $this->_initEntityType();

            $this->_initAction($this->__('Manage Entities'));
            $this->renderLayout();
        // TODO: Catch only our exception
        } catch (Exception $e) {
            Mage::logException($e);
            Mage::throwException($e);
            $this->_forward('no_route');
        }
    }

    /* Deletes single entity */
    public function deleteAction()
    {
        $this->_initEntityType();

        try {
            $entity = $this->getEtmHelper()->getEntityByEntityId(
                $this->getRequest()->getParam('entity_id', null));

            $entity->delete();
            $this->_getSession()->addSuccess($this->__('Entity successfully deleted'));
        } catch (Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        }

        $this->_redirect('*/*/', array(
            'entity_type_id'    => $this->getRequest()->getParam('entity_type_id'),
            'store'             => $this->getRequest()->getParam('store'),
        ));
    }

    public function massDeleteAction()
    {
        $etmEntitys = $this->getRequest()->getParam('entity_ids');
        if (!is_array($etmEntitys)) {
            $this->_getSession()->addError($this->__('Please select entity(s).'));
        } else {
            if (!empty($etmEntitys)) {
                $this->_initEntityType();
                try {
                    $collection = $this->getEtmHelper()
                        ->getEntityCollectionByEntityType(
                            Mage::registry('etm_entity_type'));
                    $collection->addFieldToFilter('entity_id',
                        array('IN' => $etmEntitys));
                    $collection->delete();

                    $this->_getSession()->addSuccess(
                        Mage::helper('adminhtml')->__('Total of %d record(s) have been deleted.',
                            count($etmEntitys))
                    );
                } catch (Exception $e) {
                    $this->_getSession()->addError($e->getMessage());
                }
            }
        }
        $this->_redirectReferer();
    }

    /**
     * Grid ajax action
     */
    public function gridAction()
    {
        $this->_initEntityType();
        $this->_initEntity();
        $this->loadLayout();
        $this->renderLayout();
    }

    public function newAction()
    {
        $this->_forward('edit');
    }

    public function editAction()
    {
        $entityId = $this->getRequest()->getParam('entity_id', null);

        $entityType = $this->_initEntityType();
        $this->_initAction($this->__(
            $entityId ? 'Edit %s' : 'Create %s',
            $this->getEtmHelper()->escapeHtml($entityType->getEntityTypeName())
        ));

        $storeId = $this->getRequest()->getParam('store', 0);
        $entity = $this->_initEntity($storeId);
        if (!$entity->getId()) {
            $this->getLayout()->getBlock('content')->unsetChild('store_switcher');
        }

        // set entered data if was error when we do save
        $data = $this->_getSession()->getFormData();

        // restore data from SESSION
        if ($data) {
            $this->_getSession()->unsFormData();
            $entity->addData($data);
        }
        $block = $this->getLayout()->getBlock('catalog.wysiwyg.js');
        if ($block) {
            $block->setStoreId($storeId);
        }
        $this->renderLayout();
    }

    /**
     * Save action
     */
    public function saveAction()
    {
        $redirectPath   = '*/*';
        $redirectParams = array();
        // check if data sent
        $data         = $this->getRequest()->getPost();
        $entityTypeId = $this->getRequest()->getParam('entity_type_id');
        if (!empty($entityTypeId)) {
            $redirectParams = array('entity_type_id' => $entityTypeId);
            $data['entity_type_id'] = $entityTypeId;
        }
        if ($data) {
            $entityType = $this->_initEntityType();
            $storeId = $this->getRequest()->getPost('store_id', 0);
            if ($storeId) {
                $redirectParams['store'] = $storeId;
            }
            $entity = $this->_initEntity($storeId);

            $entity->addData($data);
            $entity->setStoreId($storeId);
            /**
             * Check "Use Default Value" checkboxes values
             */
            if ($useDefaults = $this->getRequest()->getPost('use_default')) {
                foreach ($useDefaults as $attributeCode) {
                    $entity->setData($attributeCode, false);
                }
            }

            try {
                $hasError = false;
                $validate = $entity->validate();
                if ($validate !== true) {
                    foreach ($validate as $code => $error) {
                        if ($error === true) {
                            Mage::throwException(
                                Mage::helper('catalog')->__(
                                    'Attribute "%s" is invalid.',
                                    $entity->getResource()->getAttribute($code)
                                        ->getFrontend()->getLabel()));
                        } else {
                            Mage::throwException($error);
                        }
                    }
                }

                $entity->save();
                $this->_getSession()->addSuccess(
                    Mage::helper('goodahead_etm')->__('Entity has been saved.')
                );

                // check if 'Save and Continue'
                if ($this->getRequest()->getParam('back')) {
                    $redirectPath   = '*/*/edit';
                    $redirectParams['entity_id'] = $entity->getId();
                }
            } catch (Goodahead_Etm_Exception $e) {
                $hasError = true;
                $this->_getSession()->addException($e,
                    Mage::helper('goodahead_etm')->__('You are not allowed to edit non-custom entity')
                );
            } catch (Exception $e) {
                $hasError = true;
                $this->_getSession()->addError($e->getMessage());
            }

            if ($hasError) {
                $attributes = Mage::helper('goodahead_etm')
                    ->getVisibleAttributes($entityType);
                $this->_getSession()->setFormData(
                    array_intersect_key($data, $attributes));
                $redirectPath = '*/*/edit';
                if ($entity->getId()) {
                    $redirectParams['entity_id'] = $entity->getId();
                }
            }
        }

        $this->_redirect($redirectPath, $redirectParams);
    }

    public function duplicateAction()
    {
        // TODO: Add Validation
        $redirectPath   = '*/*';
        $redirectParams = array();

        $entityTypeId = $this->getRequest()->getParam('entity_type_id');

        if ($entityTypeId) {
            $this->_initEntityType();
            $storeId = $this->getRequest()->getParam('store_id', 0);
            $entity  = $this->_initEntity($storeId);
            if ($storeId) {
                $redirectParams['store'] = $storeId;
            }

            try {
                $hasError = false;

                $newEntity = $entity->duplicate();

                $this->_getSession()->addSuccess(
                    Mage::helper('goodahead_etm')->__('Entity has been duplicated.')
                );

                $redirectPath   = '*/*/edit';
                $redirectParams['entity_id']      = $newEntity->getId();
                $redirectParams['entity_type_id'] = $entityTypeId;
            } catch (Goodahead_Etm_Exception $e) {
                $hasError = true;
                $this->_getSession()->addException($e,
                    Mage::helper('goodahead_etm')->__('You are not allowed to edit non-custom entity')
                );
            } catch (Mage_Core_Exception $e) {
                $hasError = true;
                $this->_getSession()->addError($e->getMessage());
            } catch (Exception $e) {
                $hasError = true;
                $this->_getSession()->addException($e,
                    Mage::helper('goodahead_etm')->__('An error occurred while duplicating entity.')
                );
            }

            if ($hasError) {
                $redirectPath = '*/*/edit';
                if ($entity->getId()) {
                    $redirectParams['entity_id'] = $entity->getId();
                    $redirectParams['entity_type_id'] = $entityTypeId;
                }
            }
        }

        $this->_redirect($redirectPath, $redirectParams);
    }

    /**
     * WYSIWYG editor action for ajax request
     *
     */
    public function wysiwygAction()
    {
        $elementId = $this->getRequest()->getParam('element_id', md5(microtime()));
        $storeId = $this->getRequest()->getParam('store_id', 0);
        $storeMediaUrl = Mage::app()->getStore($storeId)->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA);

        $content = $this->getLayout()->createBlock('adminhtml/catalog_helper_form_wysiwyg_content', '', array(
            'editor_element_id' => $elementId,
            'store_id'          => $storeId,
            'store_media_url'   => $storeMediaUrl,
        ));
        $this->getResponse()->setBody($content->toHtml());
    }

    /**
     * ACL check
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        switch ($this->getRequest()->getActionName()) {
            case 'wysiwyg':
            case 'edit':
            case 'save':
                return Mage::getSingleton('admin/session')->isAllowed('goodahead_etm/manage_entities/save');
            case 'delete':
                return Mage::getSingleton('admin/session')->isAllowed('goodahead_etm/manage_entities/delete');
            case 'index':
            default:
                return Mage::getSingleton('admin/session')->isAllowed('goodahead_etm/manage_entities');
        }
    }
}
