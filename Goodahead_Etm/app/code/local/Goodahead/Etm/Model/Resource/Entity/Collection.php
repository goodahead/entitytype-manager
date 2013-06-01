<?php
class Goodahead_Etm_Model_Resource_Entity_Collection extends Mage_Eav_Model_Entity_Collection_Abstract
{
    protected $_entityType          = null;


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

    protected function _construct()
    {
        $this->_init('goodahead_etm/entity');
    }

    public function joinVisibleAttributes($entityTypeCode)
    {
        $visibleAttributes = $this->_getEtmHelper()->getVisibleAttributes($entityTypeCode);

        $this->addAttributeToSelect($visibleAttributes);

        return $this;
    }
}
