<?php

class Goodahead_Etm_Block_Adminhtml_Attribute_Edit_Tab_Form extends Mage_Eav_Block_Adminhtml_Attribute_Edit_Main_Abstract
{
    public function getAttributeObject()
    {
        if (null === $this->_attribute) {
            return Mage::registry('etm_attribute');
        }
        return $this->_attribute;
    }

    /**
     * Initialize form fields values
     *
     * @return $this
     */
    protected function _initFormValues()
    {
        $attribute = $this->getAttributeObject();

        if ($attribute->getId()) {
            $this->getForm()->setValues($attribute->getData());
        }

        return Mage_Adminhtml_Block_Widget_Form::_initFormValues();
    }

    protected function _prepareForm()
    {
        parent::_prepareForm();
        $attributeObject = $this->getAttributeObject();
        /* @var $form Varien_Data_Form */
        $form = $this->getForm();
        /* @var $fieldset Varien_Data_Form_Element_Fieldset */
        $fieldset = $form->getElement('base_fieldset');

        $fieldset->addField('attribute_name', 'text', array(
            'name'      => 'attribute_name',
            'label'     => Mage::helper('goodahead_etm')->__('Attribute name'),
            'required'  => true,
        ), '^');

        $frontendInputElm = $form->getElement('frontend_input');
        $additionalTypes = array(
            array(
                'value' => 'price',
                'label' => Mage::helper('goodahead_etm')->__('Price')
            ),
            array(
                'value' => 'media_image',
                'label' => Mage::helper('goodahead_etm')->__('Media Image')
            )
        );
        if ($attributeObject->getFrontendInput() == 'gallery') {
            $additionalTypes[] = array(
                'value' => 'gallery',
                'label' => Mage::helper('goodahead_etm')->__('Gallery')
            );
        }

        $response = new Varien_Object();
        $response->setTypes(array());
        Mage::dispatchEvent('adminhtml_product_attribute_types', array('response'=>$response));
        $_disabledTypes = array();
        $_hiddenFields = array();
        foreach ($response->getTypes() as $type) {
            $additionalTypes[] = $type;
            if (isset($type['hide_fields'])) {
                $_hiddenFields[$type['value']] = $type['hide_fields'];
            }
            if (isset($type['disabled_types'])) {
                $_disabledTypes[$type['value']] = $type['disabled_types'];
            }
        }
        Mage::register('attribute_type_hidden_fields', $_hiddenFields);
        Mage::register('attribute_type_disabled_types', $_disabledTypes);

        $frontendInputValues = array_merge($frontendInputElm->getValues(), $additionalTypes);
        $frontendInputElm->setValues($frontendInputValues);

//        $yesnoSource = Mage::getModel('adminhtml/system_config_source_yesno')->toOptionArray();

        $scopes = array(
            Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE =>Mage::helper('goodahead_etm')->__('Store View'),
            Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE =>Mage::helper('goodahead_etm')->__('Website'),
            Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL =>Mage::helper('goodahead_etm')->__('Global'),
        );

        if ($attributeObject->getAttributeCode() == 'status' || $attributeObject->getAttributeCode() == 'tax_class_id') {
            unset($scopes[Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE]);
        }

        $fieldset->addField('is_global', 'select', array(
            'name'  => 'is_global',
            'label' => Mage::helper('goodahead_etm')->__('Scope'),
            'title' => Mage::helper('goodahead_etm')->__('Scope'),
            'note'  => Mage::helper('goodahead_etm')->__('Declare attribute value saving scope'),
            'values'=> $scopes
        ), 'attribute_code');

        Mage::dispatchEvent('goodahead_etm_catalog_product_attribute_edit_prepare_form', array(
            'form'      => $form,
            'attribute' => $attributeObject
        ));

        return $this;
    }

    /**
     * Retrieve additional element types for product attributes
     *
     * @return array
     */
    protected function _getAdditionalElementTypes()
    {
        return array(
            'apply'         => Mage::getConfig()->getBlockClassName('adminhtml/catalog_product_helper_form_apply'),
        );
    }
}
