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
