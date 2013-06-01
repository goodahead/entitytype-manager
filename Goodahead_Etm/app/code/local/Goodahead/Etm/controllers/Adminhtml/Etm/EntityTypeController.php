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
}
