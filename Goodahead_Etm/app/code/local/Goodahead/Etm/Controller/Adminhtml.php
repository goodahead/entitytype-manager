<?php

class Goodahead_Etm_Controller_Adminhtml extends Mage_Adminhtml_Controller_Action
{
    /**
     * Init entity type object based on passed entity_type_id parameter
     *
     * @throws Goodahead_Etm_Exception
     * @return $this
     */
    protected function _initEntityType()
    {
        $entityTypeId = $this->getRequest()->getParam('entity_type_id', null);
        $entityType = Mage::getModel('goodahead_etm/entity_type')->load($entityTypeId);
        if ($entityType->getId() || $entityTypeId === null) {
            Mage::register('etm_entity_type', $entityType);
            return $this;
        }
        throw new Goodahead_Etm_Exception(Mage::helper('goodahead_etm')->__('Entity type not found'));
    }

    /**
     * Init action
     *
     * @param string $title
     * @return $this
     */
    protected function _initAction($title)
    {
        $helper = Mage::helper('goodahead_etm');
        // load layout, set active menu and breadcrumbs
        $this->loadLayout()
            ->_setActiveMenu('goodahead_etm/manage_entities')
            ->_addBreadcrumb($helper->__('Entity Type Manager'), $helper->__('Entity Type Manager'))
            ->_addBreadcrumb($title, $title);

        // set title
        $this->_title($this->__('Entity Type Manager'))
            ->_title($title);

        return $this;
    }


    /* @return Goodahead_Etm_Helper_Data */
    protected function getEtmHelper()
    {
        return Mage::helper('goodahead_etm');
    }
}
