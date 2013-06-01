<?php

class Goodahead_Etm_Model_Resource_Attribute_Collection extends Mage_Eav_Model_Resource_Entity_Attribute_Collection
{
    /**
     * @var Mage_Eav_Model_Entity_Type
     */
    protected $_entityType;

    /**
     * @return Mage_Eav_Model_Entity_Type
     */
    public function getEntityType()
    {
        if (!$this->_entityType) {
            $this->_entityType = $this->_getEntityTypeFromRegistry();
        }
        return $this->_entityType;
    }

    /**
     * @param Mage_Eav_Model_Entity_Type $entityType
     */
    public function setEntityType($entityType)
    {
        $this->_entityType = $entityType;
    }

    /**
     * Get entity type object from registry
     *
     * @return Mage_Eav_Model_Entity_Type
     * @throws Goodahead_Etm_Exception
     */
    protected function _getEntityTypeFromRegistry()
    {
        /** @var Mage_Eav_Model_Entity_Type $entityType */
        $entityType = Mage::registry('etm_entity_type');
        if ($entityType && $entityType->getId()) {
            return $entityType;
        }

        $helper = Mage::helper('goodahead_etm');
        throw new Goodahead_Etm_Exception($helper->__('Entity type object is absent in registry'));
    }

    /**
     * Initialize collection select
     *
     * @return $this
     */
    protected function _initSelect()
    {
        parent::_initSelect();

        $this->getSelect()->joinInner(
            array('etm_entity_type' => $this->getTable('goodahead_etm/eav_entity_type')),
            'main_table.entity_type_id = etm_entity_type.entity_type_id',
            array()
        );

        $this->addFieldToFilter('main_table.entity_type_id', $this->getEntityType()->getId());

        return $this;
    }
}
