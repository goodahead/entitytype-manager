<?php

class Goodahead_Etm_Model_Resource_Entity_Type extends Mage_Eav_Model_Resource_Entity_Type
{
    protected function _getLoadSelect($field, $value, $object)
    {
        $select = parent::_getLoadSelect($field, $value, $object);
        $select->where('entity_table = \'goodahead_etm/eav\'');
        return $select;
    }
}
