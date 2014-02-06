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
 * Class Goodahead_Etm_Block_Adminhtml_Attribute_Edit_Tab_Form
 *
 * Main tab for Entity Attribute Edit form
 */

class Goodahead_Etm_Block_Adminhtml_Attribute_Edit_Tab_Form
    extends Mage_Eav_Block_Adminhtml_Attribute_Edit_Main_Abstract
{
    /**
     * Retrieve attribute object from registry
     *
     * @return Goodahead_Etm_Model_Attribute current attribute model
     */
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
/*    protected function _initFormValues()
    {
        $attribute = $this->getAttributeObject();

        if ($attribute->getId()) {
            $this->getForm()->setValues($attribute->getData());
        }

        parent::_initFormValues()

        return Mage_Adminhtml_Block_Widget_Form::_initFormValues();
    }*/

    /**
     * Preparing form elements for editing Entity Type Attribute
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        parent::_prepareForm();

        /** @var Goodahead_Etm_Model_Entity_Type $entityType */
        $entityType = Mage::registry('etm_entity_type');

        $attributeObject = $this->getAttributeObject();
        /* @var $form Varien_Data_Form */
        $form = $this->getForm();
        /* @var $fieldset Varien_Data_Form_Element_Fieldset */
        $fieldset = $form->getElement('base_fieldset');

        $frontendInputElm = $form->getElement('frontend_input');
        $additionalTypes = array(
            array(
                'value' => 'price',
                'label' => Mage::helper('catalog')->__('Price')
            ),
            array(
                'value' => 'image',
                'label' => Mage::helper('catalog')->__('Media Image')
            )
        );
        if ($attributeObject->getFrontendInput() == 'gallery') {
            $additionalTypes[] = array(
                'value' => 'gallery',
                'label' => Mage::helper('catalog')->__('Gallery')
            );
        }

        $response = new Varien_Object();
        $response->setTypes(array());

        Mage::dispatchEvent(
            'goodahead_etm_entity_attribute_types',
            array('response'=>$response));
        Mage::dispatchEvent(
            sprintf(
                'goodahead_etm_%s_entity_attribute_types',
                $entityType->getEntityTypeCode()),
            array('response'=>$response));

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

        $scopes = array(
            Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE      => Mage::helper('catalog')->__('Store View'),
            Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE    => Mage::helper('catalog')->__('Website'),
            Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL     => Mage::helper('catalog')->__('Global'),
        );

        $fieldset->addField('is_global', 'select', array(
            'name'  => 'is_global',
            'label' => Mage::helper('catalog')->__('Scope'),
            'title' => Mage::helper('catalog')->__('Scope'),
            'note'  => Mage::helper('catalog')->__('Declare attribute value saving scope'),
            'values'=> $scopes
        ), 'attribute_code');

        $fieldset->addField('sort_order', 'text', array(
            'name'  => 'sort_order',
            'label' => Mage::helper('core')->__('Sort Order'),
            'title' => Mage::helper('core')->__('Sort Order'),
        ));

        Mage::dispatchEvent('goodahead_etm_entity_attribute_edit_prepare_form', array(
            'form'      => $form,
            'attribute' => $attributeObject
        ));

        return $this;
    }

    /**
     * Retrieve additional element types for Entity Type attributes
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
