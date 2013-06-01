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
