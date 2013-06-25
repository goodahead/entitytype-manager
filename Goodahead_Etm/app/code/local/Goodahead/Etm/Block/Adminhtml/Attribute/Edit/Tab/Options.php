<?php
class Goodahead_Etm_Block_Adminhtml_Attribute_Edit_Tab_Options
    extends Mage_Eav_Block_Adminhtml_Attribute_Edit_Options_Abstract
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('catalog/product/attribute/options.phtml');
    }

    /**
     * Retrieve attribute object from registry
     *
     * @return Mage_Eav_Model_Entity_Attribute_Abstract
     */
    public function getAttributeObject()
    {
        return Mage::registry('etm_attribute');
    }
}
