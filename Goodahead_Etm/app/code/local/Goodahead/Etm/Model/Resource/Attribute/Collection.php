<?php

class Goodahead_Etm_Model_Resource_Attribute_Collection extends Mage_Eav_Model_Resource_Entity_Attribute_Collection
{
    /**
     * @var Goodahead_Etm_Model_Entity_Type
     */
    protected $_entityType;

    /**
     * @return Goodahead_Etm_Model_Entity_Type
     */
    public function getEntityType()
    {
        if (!$this->_entityType) {
            $this->_entityType = $this->_getEntityTypeFromRegistry();
        }
        return $this->_entityType;
    }

    /**
     *
     */
    public function setEntityType($entityType)
    {
        if ($entityType instanceof Goodahead_Etm_Model_Entity_Type) {
            $this->_entityType = $entityType;
        } else {
            $this->_entityType = Mage::getModel('goodahead_etm/entity_type')->load($entityType);
        }
        if (!$this->_entityType->getId()) {
            $helper = Mage::helper('goodahead_etm');
            throw new Goodahead_Etm_Exception($helper->__('Entity type not found'));
        }
    }

    public function toOptionArray()
    {
        return $this->_toOptionArray('attribute_id', 'attribute_name');
    }

    /**
     * Get entity type object from registry
     *
     * @return Goodahead_Etm_Model_Entity_Type
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
        $this->getSelect()->joinInner(
            array('etm_attribute' => $this->getTable('goodahead_etm/eav_attribute')),
            'main_table.attribute_id = etm_attribute.attribute_id'
        );

        $this->addFieldToFilter('main_table.entity_type_id', $this->getEntityType()->getId());

        return $this;
    }
}
