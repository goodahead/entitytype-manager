<?php

class Goodahead_Etm_Model_Resource_Entity_Type extends Mage_Eav_Model_Resource_Entity_Type
{
    protected function _construct()
    {
        parent::_construct();
        $this->_setResource(null, array('etm_entity_type' => 'goodahead_etm/eav_entity_type'));
    }

    protected function _getLoadSelect($field, $value, $object)
    {
        $select = parent::_getLoadSelect($field, $value, $object);
        $select->joinInner(array('etm_entity_type' => $this->getTable('etm_entity_type')),
            $this->getMainTable() . '.entity_type_id = etm_entity_type.entity_type_id'
        );

        return $select;
    }

    protected function _beforeSave(Mage_Core_Model_Abstract $object)
    {
        if ($object->isObjectNew()) {
            $object->setCreateAttributeSet(true);
        }
        return $this;
    }

    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
        parent::_afterSave($object);

        $tableName = $this->getTable('etm_entity_type');
        $this->_getWriteAdapter()->insertOnDuplicate($tableName, $this->_prepareDataForTable($object, $tableName));

        // create attribute set and group if new
        if ($object->getCreateAttributeSet() === true) {
            $setup = Mage::getResourceModel('goodahead_etm/entity_setup', 'core_setup');
            $setup->addAttributeSet($object->getEntityTypeCode(), $setup->getDefaultAttributeSetName());
            // set to entity type default attribute set id
            $defaultAttributeSet = Mage::getModel('eav/entity_attribute_set')->load($object->getId(), 'entity_type_id');
            $object->setDefaultAttributeSetId($defaultAttributeSet->getId());
            $setup->addAttributeGroup($object->getEntityTypeCode(), $setup->getDefaultGroupName(), $setup->getGeneralGroupName());
        }
    }
}
