<?php
/**
 * This file is part of Goodahead_Etm extension
 *
 * This extension allows to create and manage custom EAV entity types
 * and EAV entities
 *
 * Copyright (C) 2014 Goodahead Ltd. (http://www.goodahead.com)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * and GNU General Public License along with this program.
 * If not, see <http://www.gnu.org/licenses/>.
 *
 * @category   Goodahead
 * @package    Goodahead_Etm
 * @copyright  Copyright (c) 2014 Goodahead Ltd. (http://www.goodahead.com)
 * @license    http://www.gnu.org/licenses/lgpl-3.0-standalone.html GNU Lesser General Public License
 */

/**
 * Class Goodahead_Etm_Block_Adminhtml_Entity_Types_Edit_Form
 *
 * Entity Type edit form
 */

class Goodahead_Etm_Block_Adminhtml_Entity_Types_Edit_Form
    extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * Prepare layout.
     * Add files to use dialog windows
     *
     * @return Mage_Adminhtml_Block_System_Email_Template_Edit_Form
     */
    protected function _prepareLayout()
    {
        return parent::_prepareLayout();
    }

    /**
     * Preparing form elements for editing Entity Type
     *
     * @return $this
     */
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

        $fieldSet = $form->addFieldset('entity_type_data', array(
            'class' => 'fieldset-wide',
        ));
        $validateClass = sprintf('required-entry validate-code validate-length maximum-length-%d',
            Goodahead_Core_Helper_Data::getConstValue(
                'Mage_Eav_Model_Entity_Attribute::ATTRIBUTE_CODE_MAX_LENGTH',
                50
            )
        );

        $fieldSet->addField('entity_type_code', 'text', array(
            'label'     => Mage::helper('goodahead_etm')->__('Entity Type Code'),
            'name'      => 'entity_type_code',
            'class'     => $validateClass,
            'required'  => true,
        ));

        $fieldSet->addField('entity_type_name', 'text', array(
            'label'     => Mage::helper('goodahead_etm')->__('Entity Type Name'),
            'name'      => 'entity_type_name',
            'class'     => 'required-entry',
            'required'  => true,
        ));

        $fieldSet->addField('entity_type_root_template', 'select', array(
            'label'     => Mage::helper('goodahead_etm')->__('Entity Type Root Template'),
            'name'      => 'entity_type_root_template',
            'values'    => Mage::getSingleton('page/source_layout')->toOptionArray(),
            'class'     => 'required-entry',
            'required'  => true,
        ));

        $fieldSet->addField('entity_type_layout_xml', 'textarea', array(
            'label'     => Mage::helper('goodahead_etm')->__('Layout XML'),
            'name'      => 'entity_type_layout_xml',
            'style'     => 'height:7em',
            'required'  => false,
        ));



        $fieldSet->addField('variables', 'hidden', array(
            'name' => 'variables',
        ));

        $insertVariableButton = $this->getLayout()->createBlock('adminhtml/widget_button', '', array(
            'type' => 'button',
            'label' => Mage::helper('adminhtml')->__('Insert Variable...'),
            'onclick' => 'openVariablesWindow();return false;'
        ));
        $fieldSet->addField('insert_variable', 'note', array(
            'text' => $insertVariableButton->toHtml()
        ));

        $fieldSet->addField('entity_type_content', 'textarea', array(
            'label'     => Mage::helper('cms')->__('Content'),
            'name'      => 'entity_type_content',
            'style'     => 'height:24em',
            'required'  => false,
        ));

        if ($entityType->getId()) {
            $form->addField('entity_type_id', 'hidden', array(
                'name' => 'entity_type_id',
            ));

            // TODO: Rework
            $entityTypeAttributes = Mage::getModel('goodahead_etm/source_entity_attribute')->toOptionsArrayWithoutExcludedTypes($entityType, true, array(
                'boolean',
                'multiselect',
                'select',
                'image',
                'static',
            ));

            $fieldSet->addField('default_attribute_id', 'select', array(
                'label'     => Mage::helper('goodahead_etm')->__('Default Attribute'),
                'name'      => 'default_attribute_id',
                'required'  => false,
                'values'    => $entityTypeAttributes,
                'note'      => Mage::helper('goodahead_etm')->__('This attribute is used to display entity label'),
            ), 'entity_type_name');

            $form->getElement('entity_type_code')->setReadonly('readonly');
            $form->getElement('entity_type_code')->setDisabled(1);
            $form->setValues($entityType->getData());
            $form->getElement('variables')->setValue(Zend_Json::encode($this->getVariables()));
        } else {
            $form->getElement('variables')->setValue(Zend_Json::encode(array()));
        }

        $this->setForm($form);
        return parent::_prepareForm();
    }

    /**
     * Retrieve variables to insert into entity type content template field
     *
     * @return array
     */
    public function getVariables()
    {
        $entityType = Mage::registry('etm_entity_type');

        $visibleAttribute = Mage::helper('goodahead_etm')->getVisibleAttributes($entityType);
        $variables = array();

        foreach($visibleAttribute as $attributeCode => $attribute) {
            $variables[] = array(
                'value' => $attributeCode,
                'label' => $attribute->getFrontend()->getLabel()
            );
        }

        $optionArray = array();
        foreach ($variables as $variable) {
            $optionArray[] = array(
                'value' => '{{var ' . $variable['value'] . '}}',
                'label' => Mage::helper('goodahead_etm')->__('%s', $variable['label'])
            );
        }
        if ($optionArray) {
            $optionArray = array(array(
                'label' => Mage::helper('core')->__('Entity Attributes'),
                'value' => $optionArray
            ));
        }

        return $optionArray;
    }

}
