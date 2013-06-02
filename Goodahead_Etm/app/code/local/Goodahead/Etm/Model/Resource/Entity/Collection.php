<?php
class Goodahead_Etm_Model_Resource_Entity_Collection extends Mage_Eav_Model_Entity_Collection_Abstract
{
    protected $_entityType  = null;
    protected $_storeId     = null;

    protected function _getEtmHelper()
    {
        return Mage::helper('goodahead_etm');
    }

    /**
     * @param null $entityType
     */
    public function setEntityType($entityType)
    {
        $this->_entityType = $entityType;
    }

    /**
     * @return null
     */
    public function getEntityType()
    {
        return $this->_entityType;
    }

    /**
     * @param null $storeId
     */
    public function setStoreId($storeId)
    {
        $this->_storeId = $storeId;
    }

    /**
     * @return null
     */
    public function getStoreId()
    {
        return $this->_storeId;
    }

    protected function _construct()
    {
        $this->_init('goodahead_etm/entity');
    }

    public function joinVisibleAttributes($entityTypeId)
    {
        $visibleAttributes = $this->_getEtmHelper()->getVisibleAttributes($entityTypeId);

        $this->addAttributeToSelect(array_keys($visibleAttributes));

        return $this;
    }
}
