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

class Goodahead_Etm_Block_Adminhtml_Entity_Types_Edit_Tab_Form
    extends Mage_Adminhtml_Block_Widget_Form
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
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
            'id' => 'edit_form',
            'action' => $this->getUrl('*/*/save'),
            'method' => 'post'
        ));

        $entityType = Mage::registry('etm_entity_type');

        $fieldSet = $form->addFieldset('entity_type_data', array(
            'class'     => 'fieldset-wide',
            'legend'    => Mage::helper('goodahead_etm')->__('General'),
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

        if ($entityType->getId()) {
//            $form->addField('entity_type_id', 'hidden', array(
//                'name' => 'entity_type_id',
//            ));

            // TODO: Rework
            $entityTypeAttributes = Mage::getModel('goodahead_etm/source_entity_attribute')
                ->toOptionsArrayWithoutExcludedTypes($entityType, true, array(
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

            Mage::dispatchEvent('goodahead_etm_entity_types_edit_prepare_form_main_section', array(
                'form'    => $form,
            ));
            $form->setValues($entityType->getData());
        } else {
            Mage::dispatchEvent('goodahead_etm_entity_types_edit_prepare_form_main_section', array(
                'form'    => $form,
            ));
        }

        $form->setFieldNameSuffix('data');

        $this->setForm($form);
        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return Mage::helper('catalog')->__('Properties');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return Mage::helper('catalog')->__('Properties');
    }

    /**
     * Returns status flag about this tab can be shown or not
     *
     * @return true
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Returns status flag about this tab hidden or not
     *
     * @return true
     */
    public function isHidden()
    {
        return false;
    }
}
