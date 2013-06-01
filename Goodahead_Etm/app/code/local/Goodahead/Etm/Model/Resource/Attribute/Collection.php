<?php

class Goodahead_Etm_Model_Resource_Attribute_Collection extends Mage_Eav_Model_Resource_Entity_Attribute_Collection
{
    /**
     * Default attribute entity type code
     *
     * @var string
     */
    protected $_entityTypeCode   = 'goodahead_etm';

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
     * Init collection
     *
     * @param string $model
     * @param string|null $resourceModel
     * @return $this
     */
    protected function _init($model, $resourceModel = null)
    {
        $this->_entityType = $this->_getEntityTypeFromRegistry();

        parent::_init($model, $resourceModel);

        return $this;
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

        $this->addFieldToFilter('main_table.entity_type_id', $this->_getEntityTypeFromRegistry()->getId());

        return $this;
    }
}
