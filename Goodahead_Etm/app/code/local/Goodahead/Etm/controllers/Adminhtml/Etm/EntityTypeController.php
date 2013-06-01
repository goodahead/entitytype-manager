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



    public function gridAction()
    {
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('goodahead_etm/adminhtml_entity_types')->toHtml()
        );
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
        $this->_initEntityType();

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
        $data = $this->getRequest()->getPost();
        if ($data) {
            $entityTypeId = $this->getRequest()->getPost('entity_type_id', null);
            $entityType = Mage::getModel('goodahead_etm/entity_type')->load($entityTypeId);
            $code = $this->getRequest()->getPost('entity_type_code', null);
            $name = $this->getRequest()->getPost('entity_type_name', null);
            if ($entityType->getId()) {
                $entityType->setEntityTypeName($name);
                $entityType->save();
            } else {
                $data = array(
                    'entity_type_code'              => $code,
                    'entity_model'                  => 'goodahead_etm/entity',
                    'entity_table'                  => 'goodahead_etm/eav',
                    'increment_per_store'           => 0,
                    'increment_pad_length'          => 8,
                    'increment_pad_char'            => 0,
                    'entity_type_name'              => $name,
                );
                $entityType = Mage::getModel('goodahead_etm/entity_type');
                $entityType->setData($data);
                $entityType->save();

                $setup = Mage::getResourceModel('goodahead_etm/entity_setup', 'core_setup');
                $setup->addAttributeSet($code, $setup->getDefaultAttributeSetName());
                $setup->addAttributeGroup($code, $setup->getDefaultGroupName(), $setup->getGeneralGroupName());
            }
        }
        $this->getResponse()->setRedirect($this->getUrl('*/*/'));
    }
}
