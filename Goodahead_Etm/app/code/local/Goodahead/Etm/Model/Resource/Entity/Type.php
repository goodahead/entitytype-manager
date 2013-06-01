<?php

class Goodahead_Etm_Model_Resource_Entity_Type extends Mage_Eav_Model_Resource_Entity_Type
{
    protected function _construct()
    {
        parent::_construct();
        $this->_setResource(NULL, array(
            'etm_entity_type' => 'goodahead_etm/eav_entity_type',
        ));

    }
    protected function _getLoadSelect($field, $value, $object)
    {
        $select = parent::_getLoadSelect($field, $value, $object);
        $select->joinInner(
            array('etm_entity_type' => $this->getTable('etm_entity_type')),
            $this->getMainTable().'.entity_type_id = etm_entity_type.entity_type_id');
        return $select;
    }

    protected function _afterSave($object)
    {
        parent::_afterSave($object);
        $tableName = $this->getTable('etm_entity_type');
        $this->_getWriteAdapter()->insertOnDuplicate(
            $tableName, $this->_prepareDataForTable($object, $tableName));
    }
}
