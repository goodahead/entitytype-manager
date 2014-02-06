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
 * Class Goodahead_Etm_Block_Adminhtml_Entity_Edit_Form
 *
 * Entity edit form
 */

class Goodahead_Etm_Block_Adminhtml_Entity_Edit_Form
    extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        Varien_Data_Form::setFieldsetElementRenderer(
            $this->getLayout()->createBlock('goodahead_etm/adminhtml_form_renderer_fieldset_element')
        );
        return $this;
    }

    public function getEntity()
    {
        return Mage::registry('etm_entity');
    }

    public function getEntityType()
    {
        return Mage::registry('etm_entity_type');
    }

    protected function _initDefaultValues()
    {
        if (!$this->getEntity()->getId()) {
            foreach (
                Mage::helper('goodahead_etm')
                    ->getVisibleAttributes($this->getEntityType()) as $attribute
            ) {
                $default = $attribute->getDefaultValue();
                if ($default != '') {
                    $this->getEntity()->setData($attribute->getAttributeCode(), $default);
                }
            }
        }
        return $this;
    }

    /**
     * Preparing form elements for editing Entity
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

        $this->_initDefaultValues();
        $entityType = $this->getEntityType();
        $entity = $this->getEntity();

        $form->setDataObject($entity);

        $fieldSet = $form->addFieldset('entity_data', array(
            'legend' => Mage::helper('goodahead_etm')->__("Entity Attributes")
        ));

        $attributes = Mage::helper('goodahead_etm')->getVisibleAttributes($entityType);
        foreach ($attributes as $attribute) {
            /* @var $attribute Mage_Eav_Model_Entity_Attribute */
            $attribute->unsIsVisible();
            if ($attribute->isSystem()) {
                $attribute->setIsVisible(0);
            }
        }

        $this->_setFieldset($attributes, $fieldSet);

        if ($entity->getId()) {
            $form->addField('entity_id', 'hidden', array(
                'name' => 'entity_id',
            ));
        }
        $form->setValues($entity->getData());
        if ($entityType->getId()) {
            $form->addField('entity_type_id', 'hidden', array(
                'name'  => 'entity_type_id',
                'value' => $entityType->getId(),
            ));
        }
        $form->addField('store_id', 'hidden', array(
            'name'  => 'store_id',
            'value' => $this->getRequest()->getParam('store'),
        ));

        $this->setForm($form);
        return parent::_prepareForm();
    }

    // TODO: add media images and media gallery support
    protected function _getAdditionalElementTypes()
    {
        return array(
            'file'      => Mage::getConfig()->getBlockClassName('adminhtml/customer_form_element_file'),
//            'image'     => Mage::getConfig()->getBlockClassName('adminhtml/customer_form_element_image'),
            'image'     => Mage::getConfig()->getBlockClassName('goodahead_etm/adminhtml_form_renderer_fieldset_element_image'),
            'boolean'   => Mage::getConfig()->getBlockClassName('adminhtml/customer_form_element_boolean'),
            'price'     => Mage::getConfig()->getBlockClassName('goodahead_etm/adminhtml_form_renderer_fieldset_element_price'),
        );
    }
}
