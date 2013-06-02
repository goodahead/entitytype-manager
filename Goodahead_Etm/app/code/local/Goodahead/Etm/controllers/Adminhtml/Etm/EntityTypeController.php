<?php

class Goodahead_Etm_Adminhtml_Etm_EntityTypeController extends Goodahead_Etm_Controller_Adminhtml
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
                $this->_getSession()->addSuccess($this->getEtmHelper()->__('Entity type successfully deleted'));
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }

        $this->_redirectReferer();
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
                        $this->__('Total of %d record(s) have been deleted.', count($etmEntityTypes))
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
                    'entity_type_code',
                    'entity_type_name',
                    'entity_type_root_template',
                    'entity_type_layout_xml',
                    'entity_type_content',
                    'default_attribute_id'
                );
                $postData = array();
                foreach ($postFields as $_field) {
                    $postData[$_field] = $this->getRequest()->getPost($_field, null);
                }
                $entityTypeModel->addData($postData);
                if (!$entityTypeModel->getId()) {
                    $entityTypeModel->addData(array(
                        'entity_model'         => 'goodahead_etm/entity',
                        'entity_table'         => 'goodahead_etm/entity',
                        'increment_per_store'  => 0,
                        'increment_pad_length' => 8,
                        'increment_pad_char'   => 0,
                    ));
                }
                $entityTypeModel->save();

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
