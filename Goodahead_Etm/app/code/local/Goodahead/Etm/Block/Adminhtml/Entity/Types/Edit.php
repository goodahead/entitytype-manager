<?php

class Goodahead_Etm_Block_Adminhtml_Entity_Types_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        $this->_objectId = 'entity_type_id';
        $this->_controller = 'adminhtml_entity_types';

        parent::__construct();

        $this->_updateButton('save', 'label', Mage::helper('goodahead_etm')->__('Save Entity Type'));
        $this->_updateButton('delete', 'label', Mage::helper('goodahead_etm')->__('Delete Entity Type'));
    }

    public function getEntityTypeId()
    {
        return Mage::registry('etm_entity_type')->getId();
    }

    public function getHeaderText()
    {
        if ($this->getEntityTypeId()) {
            return $this->htmlEscape(Mage::registry('etm_entity_type')->getEntityTypeName());
        } else {
            return Mage::helper('customer')->__('New Entity Type');
        }
    }
}
