<?php

class Goodahead_Etm_Model_Resource_Entity_Type_Collection extends Mage_Eav_Model_Resource_Entity_Type_Collection
{
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->getSelect()->where('entity_table = \'goodahead_etm/eav\'');
        return $this;
    }
}
