<?php

class Goodahead_Etm_Model_Resource_Entity_Type extends Mage_Eav_Model_Resource_Entity_Type
{
    protected function _getLoadSelect($field, $value, $object)
    {
        $select = parent::_getLoadSelect($field, $value, $object);
        $select->joinInner(
            array('etm_entity_type' => $this->getTable('goodahead_etm/eav_entity_type')),
            $this->getMainTable().'.entity_type_id = etm_entity_type.entity_type_id');
        return $select;
    }
}
