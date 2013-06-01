<?php

class Goodahead_Etm_Model_Resource_Entity_Type extends Mage_Eav_Model_Resource_Entity_Type
{
    protected function _construct()
    {
        parent::_construct();
        $this->_setResource(NULL, array(
            'eav_entity_type_extra' => 'goodahead_etm/eav_entity_type',
        ));

    }
    protected function _getLoadSelect($field, $value, $object)
    {
        $select = parent::_getLoadSelect($field, $value, $object);
        $select->where('entity_table = \'goodahead_etm/eav\'');
        return $select;
    }

    protected function _afterSave($object)
    {
        parent::_afterSave($object);
        $tableName = $this->getTable('eav_entity_type_extra');
        $this->_getWriteAdapter()->insertOnDuplicate(
            $tableName, $this->_prepareDataForTable($object, $tableName));
    }
}
