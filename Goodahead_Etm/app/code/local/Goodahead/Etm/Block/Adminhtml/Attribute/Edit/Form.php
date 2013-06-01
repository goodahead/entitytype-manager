<?php

class Goodahead_Etm_Block_Adminhtml_Attribute_Edit_Form extends Mage_Eav_Block_Adminhtml_Attribute_Edit_Main_Abstract
{
    public function getAttributeObject()
    {
        if (null === $this->_attribute) {
            return Mage::registry('etm_attribute');
        }
        return $this->_attribute;
    }

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array(
            'id'      => 'edit_form',
            'action'  => $this->getUrl('*/*/save', array(
                'entity_type_id' => $this->getRequest()->getParam('entity_type_id')
            )),
            'method'  => 'post',
            'enctype' => 'multipart/form-data'
        ));

        $attribute = $this->getAttributeObject();

        if ($attribute->getId()) {
            $form->addField('attribute_id', 'hidden', array(
                'name' => 'attribute_id',
            ));
            $form->setValues($attribute->getData());
        }

        $fieldSet = $form->addFieldset('attribute_data', array());
        $validateClass = sprintf('required-entry validate-code validate-length maximum-length-%d',
            Mage_Eav_Model_Entity_Attribute::ATTRIBUTE_CODE_MAX_LENGTH
        );
        $fieldSet->addField('attribute_code', 'text', array(
            'name'  => 'attribute_code',
            'label' => Mage::helper('goodahead_etm')->__('Attribute Code'),
            'title' => Mage::helper('goodahead_etm')->__('Attribute Code'),
            'note'  => Mage::helper('goodahead_etm')->__('For internal use. Must be unique with no spaces. Maximum length of attribute code must be less then %s symbols', Mage_Eav_Model_Entity_Attribute::ATTRIBUTE_CODE_MAX_LENGTH),
            'class' => $validateClass,
            'required' => true,
        ));

        $inputTypes = Mage::getModel('eav/adminhtml_system_config_source_inputtype')->toOptionArray();
        $yesNo = Mage::getModel('adminhtml/system_config_source_yesno')->toOptionArray();

        $fieldSet->addField('frontend_input', 'select', array(
            'name' => 'frontend_input',
            'label' => Mage::helper('goodahead_etm')->__('Catalog Input Type for Store Owner'),
            'title' => Mage::helper('goodahead_etm')->__('Catalog Input Type for Store Owner'),
            'value' => 'text',
            'values'=> $inputTypes
        ));

        //TODO: add default value field
//        $fieldSet->addField('default_value_text', 'text', array(
//            'name'  => 'default_value_text',
//            'label' => Mage::helper('eav')->__('Default Value'),
//            'title' => Mage::helper('eav')->__('Default Value'),
//            'value' => $attribute->getDefaultValue(),
//        ));
//
//        $fieldSet->addField('default_value_yesno', 'select', array(
//            'name'  => 'default_value_yesno',
//            'label'  => Mage::helper('eav')->__('Default Value'),
//            'title'  => Mage::helper('eav')->__('Default Value'),
//            'values' => $yesNo,
//            'value'  => $attribute->getDefaultValue(),
//        ));
//
//        $dateFormatIso = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
//        $fieldSet->addField('default_value_date', 'date', array(
//            'name'   => 'default_value_date',
//            'label'  => Mage::helper('eav')->__('Default Value'),
//            'title'  => Mage::helper('eav')->__('Default Value'),
//            'image'  => $this->getSkinUrl('images/grid-cal.gif'),
//            'value'  => $attribute->getDefaultValue(),
//            'format' => $dateFormatIso
//        ));
//
//        $fieldSet->addField('default_value_textarea', 'textarea', array(
//            'name'  => 'default_value_textarea',
//            'label' => Mage::helper('eav')->__('Default Value'),
//            'title' => Mage::helper('eav')->__('Default Value'),
//            'value' => $attribute->getDefaultValue(),
//        ));

        $fieldSet->addField('is_unique', 'select', array(
            'name'   => 'is_unique',
            'label'  => Mage::helper('eav')->__('Unique Value'),
            'title'  => Mage::helper('eav')->__('Unique Value (not shared with other products)'),
            'note'   => Mage::helper('eav')->__('Not shared with other products'),
            'values' => $yesNo,
        ));

        $fieldSet->addField('is_required', 'select', array(
            'name'   => 'is_required',
            'label'  => Mage::helper('eav')->__('Values Required'),
            'title'  => Mage::helper('eav')->__('Values Required'),
            'values' => $yesNo,
        ));

        $fieldSet->addField('frontend_class', 'select', array(
            'name'  => 'frontend_class',
            'label' => Mage::helper('eav')->__('Input Validation for Store Owner'),
            'title' => Mage::helper('eav')->__('Input Validation for Store Owner'),
            'values'=> Mage::helper('eav')->getFrontendClasses($attribute->getEntityType()->getEntityTypeCode())
        ));

        if ($attribute->getId()) {
            $form->getElement('attribute_code')->setDisabled(1);
            $form->getElement('frontend_input')->setDisabled(1);
            if (!$attribute->getIsUserDefined()) {
                $form->getElement('is_unique')->setDisabled(1);
            }
        }

        $form->setUseContainer(true);
        $this->setForm($form);
        return Mage_Adminhtml_Block_Widget_Form::_prepareForm();
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
}
