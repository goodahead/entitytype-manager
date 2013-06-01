<?php
class Goodahead_Etm_Block_Adminhtml_Entity_Types extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_blockGroup = 'goodahead_etm';
        $this->_controller = 'adminhtml_entity_types';
        $this->_headerText = Mage::helper('goodahead_etm')->__('Entity Types');

        parent::__construct();

        $this->_updateButton('add', 'label', Mage::helper('goodahead_etm')->__('Add New Entity Type'));
    }
}
