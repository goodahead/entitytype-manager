<?php

class Goodahead_Etm_Block_Adminhtml_Entity_Types_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array(
            'id'            => 'edit_form',
            'action'        => $this->getUrl('*/*/save'),
            'method'        => 'post',
            'enctype'       => 'multipart/form-data',
            'use_container' => true
        ));

        $entityType = Mage::registry('etm_entity_type');

        $fieldSet = $form->addFieldset('entity_type_data', array());
        $validateClass = sprintf('required-entry validate-code validate-length maximum-length-%d',
            Mage_Eav_Model_Entity_Attribute::ATTRIBUTE_CODE_MAX_LENGTH
        );

        $fieldSet->addField('entity_type_code', 'text', array(
            'label'     => Mage::helper('goodahead_etm')->__("Entity Type Code"),
            'name'      => 'entity_type_code',
            'class'     => $validateClass,
            'required'  => true,
        ));

        $fieldSet->addField('entity_type_name', 'text', array(
            'label'     => Mage::helper('goodahead_etm')->__("Entity Type Name"),
            'name'      => 'entity_type_name',
            'class'     => 'required-entry',
            'required'  => true,
        ));

        if ($entityType->getId()) {
            $form->addField('entity_type_id', 'hidden', array(
                'name' => 'entity_type_id',
            ));

            $entityTypeAttributes = Mage::getModel('goodahead_etm/source_attribute')->toOptionsArray($entityType, true);

            $fieldSet->addField('default_attribute_id', 'select', array(
                'label'     => Mage::helper('goodahead_etm')->__("Default Attribute"),
                'name'      => 'default_attribute_id',
                'required'  => false,
                'values'    => $entityTypeAttributes,
                'note'      => Mage::helper('goodahead_etm')->__("This attribute is used to display entity label"),
            ));

            $form->getElement('entity_type_code')->setReadonly('readonly');
            $form->getElement('entity_type_code')->setDisabled(1);
            $form->setValues($entityType->getData());
        }

        $this->setForm($form);
        return parent::_prepareForm();
    }
}
