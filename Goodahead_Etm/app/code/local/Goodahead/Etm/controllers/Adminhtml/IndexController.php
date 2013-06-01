<?php

class Goodahead_Etm_Adminhtml_IndexController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Entity Type Manager index page
     */
    public function indexAction()
    {
        $this->loadLayout();
        $this->_setActiveMenu('goodahead_etm');
        $this->_addBreadcrumb(
            Mage::helper('goodahead_etm')->__('Entity Type Manager'),
            Mage::helper('goodahead_etm')->__('Entity Type Manager')
        );

        $this->renderLayout();
    }

    /**
     * ACL check
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        switch ($this->getRequest()->getActionName()) {
            case 'index':
            default:
                return Mage::getSingleton('admin/session')->isAllowed('goodahead_etm/manage');
                break;
        }
    }
}
