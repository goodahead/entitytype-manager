<?php

class Goodahead_Etm_Model_Entity extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('goodahead_etm/entity');
    }

    public function getCollection($entityTypeId = null)
    {
        return $this->getResourceCollection($entityTypeId);
    }

    public function getResourceCollection($entityTypeId = null)
    {
        if (empty($this->_resourceCollectionName)) {
            Mage::throwException(Mage::helper('core')->__('Model collection resource name is not defined.'));
        }
        $collection = Mage::getResourceModel($this->_resourceCollectionName, $this->_getResource(array($entityTypeId)));
        $collection->setEntityType($entityTypeId);
        return $collection;
    }

    /**
     * Get resource instance
     *
     * @return Mage_Core_Model_Mysql4_Abstract
     */
    protected function _getResource($entityTypeId = array())
    {
        if (empty($this->_resourceName)) {
            Mage::throwException(Mage::helper('core')->__('Resource is not set.'));
        }

        return Mage::getResourceSingleton($this->_resourceName, $entityTypeId);
    }
}
