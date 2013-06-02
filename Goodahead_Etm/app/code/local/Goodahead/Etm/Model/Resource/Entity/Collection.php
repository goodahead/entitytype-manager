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
     * @return Goodahead_Etm_Model_Entity_Type
     */
    public function getEntityType()
    {
        return $this->_entityType;
    }

    /**
     * @param Goodahead_Etm_Model_Entity_Type $storeId
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

    /**
     * @param string $valueField
     * @param string $labelField
     * @param array $additional
     * @return array
     */
    protected function _toOptionArray($valueField = 'entity_id', $labelField = 'name', $additional = array())
    {
        $defaultAttributeId = $this->getEntityType()->getDefaultAttributeId();
        /** @var Goodahead_Etm_Model_Attribute $defaultAttribute */
        $defaultAttribute = Mage::getModel('goodahead_etm/attribute')->load($defaultAttributeId);

        $this->addAttributeToSelect($defaultAttribute->getAttributeCode());

        return parent::_toOptionArray($valueField, $defaultAttribute->getAttributeCode(), $additional);
    }
}
