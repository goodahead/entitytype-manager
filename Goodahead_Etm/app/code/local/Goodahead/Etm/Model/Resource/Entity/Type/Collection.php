<?php

class Goodahead_Etm_Model_Resource_Entity_Type_Collection extends Mage_Eav_Model_Resource_Entity_Type_Collection
{
    protected function _construct()
    {
        $this->_init('goodahead_etm/entity_type');
    }

    protected function _initSelect()
    {
        parent::_initSelect();
        $this->getSelect()->joinInner(
            array('etm_entity_type' => $this->getTable('etm_entity_type')),
            'main_table.entity_type_id = etm_entity_type.entity_type_id');
        return $this;
    }
}
