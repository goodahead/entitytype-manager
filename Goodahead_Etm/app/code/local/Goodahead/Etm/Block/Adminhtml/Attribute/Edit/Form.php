<?php

class Goodahead_Etm_Block_Adminhtml_Attribute_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{

    protected function _prepareForm()
    {
        $entityType = Mage::registry('etm_entity_type');
        $form = new Varien_Data_Form(array(
            'id' => 'edit_form',
            'action' => $this->getUrl('*/*/save', array('entity_type_id' => $entityType->getId())),
            'method' => 'post',
        ));
        
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

}
