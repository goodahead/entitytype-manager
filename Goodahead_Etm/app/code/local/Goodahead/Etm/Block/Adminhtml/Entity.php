<?php
class Goodahead_Etm_Block_Adminhtml_Entity extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_blockGroup = 'goodahead_etm';
        $this->_controller = 'adminhtml_entity';
        $this->_headerText = Mage::helper('goodahead_etm')->__('Entities');

        parent::__construct();

        $this->_updateButton('add', 'label', Mage::helper('goodahead_etm')->__('Add New Entity'));
    }
}
