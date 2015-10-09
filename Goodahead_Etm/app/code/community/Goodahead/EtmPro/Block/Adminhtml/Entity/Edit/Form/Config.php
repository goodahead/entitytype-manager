<?php
class Goodahead_Etmpro_Block_Adminhtml_Entity_Edit_Form_Config extends Mage_Adminhtml_Block_Template
{
    public function getAdditionalElementTypes()
    {
        return array(
            'textarea'    => Mage::getConfig()->getBlockClassName('adminhtml/catalog_helper_form_wysiwyg'),
        );
    }
}