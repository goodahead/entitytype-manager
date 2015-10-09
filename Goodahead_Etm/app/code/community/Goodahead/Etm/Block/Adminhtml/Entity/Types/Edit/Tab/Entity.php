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

class Goodahead_Etm_Block_Adminhtml_Entity_Types_Edit_Tab_Entity
    extends Goodahead_Etm_Block_Adminhtml_Widget_Form
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
            'legend'    => Mage::helper('goodahead_etm')->__('Entity Page Layout'),
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

        $this->addWysiwygTextFieldToFieldset($fieldSet);

        $wysiwygVariables = array('etm_variables' => array(
            'entity_type_id' => $entityType->getId(),
            'etm_entity_attributes' => true,
        ));
        $fieldSet->addField('entity_type_content', 'editor',
            array(
                'label'     => Mage::helper('cms')->__('Content'),
                'name'      => 'entity_type_content',
                'style'     => 'height:24em',
                'required'  => false,
                'config'    => $this->getWysiwygConfig($wysiwygVariables),
            )
        );

        Mage::dispatchEvent('goodahead_etm_entity_types_edit_prepare_form_page_layout_section', array(
            'form'    => $form,
        ));

        if ($entityType->getId()) {
            $form->setValues($entityType->getData());
        }
        $form->setFieldNameSuffix('data');
        $this->setForm($form);
        return parent::_prepareForm();
    }

    public function addWysiwygTextFieldToFieldset($fieldSet = null)
    {
        if (!is_null($fieldSet)) {
            $entityType = Mage::registry('etm_entity_type');
            $wysiwygVariables = array(
                'etm_variables' => array(
                    'entity_type_id'        => $entityType->getId(),
                    'etm_entity_attributes' => true,
                ),
                'wysiwyg_text_mode'     => true,
            );

            $wysiwygTextButtonSettings = array(
                'label'     => Mage::helper('goodahead_etm')->__('Page Title'),
                'name'      => 'entity_type_title',
                'required'  => false,
                'config'    => $this->getWysiwygConfig($wysiwygVariables),
            );

            $entityTitle = $fieldSet->addField('entity_type_title', 'text', $wysiwygTextButtonSettings);
            $entityTitle = clone $entityTitle;
            $fieldSet->removeField('entity_type_title');
            $fakeEditorField = $fieldSet->addField('entity_type_title', 'editor', $wysiwygTextButtonSettings);
            $fakeEditorField->setRenderer(
                Mage::getBlockSingleton('goodahead_etm/adminhtml_widget_form_element_wysiwyg_fake')
            );
            $fakeEditorField = clone $fakeEditorField;
            $fieldSet->removeField('entity_type_title');
            $textWysiwygField = $fieldSet->addField('entity_type_title', 'text', array_merge(
                $wysiwygTextButtonSettings,
                array(
                    'emt_temp_text'    => $entityTitle,
                    'emt_temp_editor'  => $fakeEditorField
                )
            ));
            $textWysiwygField->setRenderer(
                Mage::getBlockSingleton('goodahead_etm/adminhtml_widget_form_element_wysiwyg_text')
            );
            return $textWysiwygField;
        }
        return false;
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return Mage::helper('goodahead_etm')->__('Entity Page Layout');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return Mage::helper('goodahead_etm')->__('Entity Page Layout');
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