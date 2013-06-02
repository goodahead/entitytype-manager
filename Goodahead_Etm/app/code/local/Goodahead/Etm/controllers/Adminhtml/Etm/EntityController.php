<?php

class Goodahead_Etm_Adminhtml_Etm_EntityController extends Goodahead_Etm_Controller_Adminhtml
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
        $entity = Mage::getModel('goodahead_etm/entity')->load($this->getRequest()->getParam('entity_id', null));

        if ($entity && $entity->getId()) {
            try {
                $entity->delete();
                $this->_getSession()->addSuccess($this->getEtmHelper()->__('Entity successfully deleted'));
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }

        $this->_redirectReferer();
    }

    public function massDeleteAction()
    {
        $etmEntitys = $this->getRequest()->getParam('entity_ids');
        if (!is_array($etmEntitys)) {
            $this->_getSession()->addError($this->__('Please select entity(s).'));
        } else {
            if (!empty($etmEntitys)) {
                try {
                    foreach ($etmEntitys as $entityId) {
                        Mage::getModel('goodahead_etm/entity')->load($entityId)->delete();
                    }
                    $this->_getSession()->addSuccess(
                        $this->__('Total of %d record(s) have been deleted.', count($etmEntitys))
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
        $this->_initAction($this->__('Create Entity'));
        $this->_initEntityType();

        $storeId = $this->getRequest()->getParam('store', 0);
        $entity = $this->_initEntity($storeId);
        if (!$entity->getId()) {
            $this->getLayout()->getBlock('content')->unsetChild('store_switcher');
        }

        // set entered data if was error when we do save
        $data = $this->_getSession()->getFormData();

        // restore data from SESSION
        if ($data) {
            $request = clone $this->getRequest();
            $request->setParams($data);
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
            $this->_initEntityType();
            $storeId = $this->getRequest()->getParam('store_id', 0);
            $entity = $this->_initEntity($storeId);
            if (!$entity->getId()) {
                $this->getLayout()->getBlock('content')->unsetChild('store_switcher');
            }

            $entity->addData($data);

            try {
                $hasError = false;

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
            } catch (Mage_Core_Exception $e) {
                $hasError = true;
                $this->_getSession()->addError($e->getMessage());
            } catch (Exception $e) {
                $hasError = true;
                $this->_getSession()->addException($e,
                    Mage::helper('goodahead_etm')->__('An error occurred while saving entity.')
                );
            }

            if ($hasError) {
                $this->_getSession()->setFormData($data);
                $redirectPath = '*/*/edit';
                if ($entity->getId()) {
                    $redirectParams['entity_id'] = $entity->getId();
                }
            }
        }

        $this->_redirect($redirectPath, $redirectParams);
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
                return Mage::getSingleton('admin/session')->isAllowed('goodahead_etm/manage_entities/save');
                break;
            case 'delete':
                return Mage::getSingleton('admin/session')->isAllowed('goodahead_etm/manage_entities/delete');
                break;
            case 'index':
            default:
                return Mage::getSingleton('admin/session')->isAllowed('goodahead_etm/manage_entities');
                break;
        }
    }
}
