<?php

class Goodahead_Etm_Controllers_Adminhtml extends Mage_Adminhtml_Controller_Action
{
    /**
     * Init entity type object based on passed entity_type_id parameter
     *
     * @return $this
     */
    protected function _initEntityType()
    {
        $entityTypeId = $this->getRequest()->getParam('entity_type_id', null);
        // TODO: Use our own model instead of eav/entity_type model
        $entityType = Mage::getModel('eav/entity_type')->load($entityTypeId);
        if ($entityType->getId()) {
            Mage::register('etm_entity_type', $entityType);
            return $this;
        }
        // TODO: Use our own exception type
        Mage::throwException('Entity type not found');
    }
}
