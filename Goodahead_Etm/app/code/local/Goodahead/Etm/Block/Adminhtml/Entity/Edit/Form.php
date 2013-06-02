<?php

class Goodahead_Etm_Block_Adminhtml_Entity_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
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
        $entity = Mage::registry('etm_entity');

        $fieldSet = $form->addFieldset('entity_data', array(
            'legend' => Mage::helper('goodahead_etm')->__("Entity Attributes")
        ));

        $attributes = Mage::helper('goodahead_etm')->getVisibleAttributesCollection($entityType);
        foreach ($attributes as $attribute) {
            /* @var $attribute Mage_Eav_Model_Entity_Attribute */
            $attribute->setFrontendLabel(Mage::helper('goodahead_etm')->__($attribute->getAttributeName()));
            $attribute->unsIsVisible();
        }

        $this->_setFieldset($attributes, $fieldSet);

        if ($entity->getId()) {
            $form->addField('entity_id', 'hidden', array(
                'name' => 'entity_id',
            ));
            $form->setValues($entity->getData());
        }
        if ($entityType->getId()) {
            $form->addField('entity_type_id', 'hidden', array(
                'name'  => 'entity_type_id',
                'value' => $entityType->getId(),
            ));
        }

        $this->setForm($form);
        return parent::_prepareForm();
    }

    // TODO: change config path
    protected function _getAdditionalElementTypes()
    {
        return array(
            'file'      => Mage::getConfig()->getBlockClassName('adminhtml/customer_form_element_file'),
            'image'     => Mage::getConfig()->getBlockClassName('adminhtml/customer_form_element_image'),
            'boolean'   => Mage::getConfig()->getBlockClassName('adminhtml/customer_form_element_boolean'),
        );
    }
}
