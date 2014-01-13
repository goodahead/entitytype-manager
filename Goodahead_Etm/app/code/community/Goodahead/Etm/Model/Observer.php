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

class Goodahead_Etm_Model_Observer {

    public function renderMenu($observer)
    {
        /** @var $menu Varien_Simplexml_Element */
        $menu = Mage::getSingleton('admin/config')->getAdminhtmlConfig()->getNode('menu');
        foreach ($menu->xpath('//*[@update]') as $node) {
            $helperName = explode('/', (string)$node->getAttribute('update'));
            $helperMethod = array_pop($helperName);
            $helperName = implode('/', $helperName);
            $helper = Mage::helper($helperName);
            if ($helper && method_exists($helper, $helperMethod)) {
                $helper->$helperMethod($node);
            }
        }
    }

    public function updateMenuBlockCacheId($observer)
    {
        $block = $observer->getEvent()->getBlock();
        if ($block instanceof Mage_Adminhtml_Block_Page_Menu) {
            /** @var $menu Varien_Simplexml_Element */
            $menu = Mage::getSingleton('admin/config')->getAdminhtmlConfig()->getNode('menu');
            $additionalCacheKeyInfo = $block->getAdditionalCacheKeyInfo();
            if (!is_array($additionalCacheKeyInfo)) {
                $additionalCacheKeyInfo = array();
            }
            $additionalCacheKeyInfo['goodahead_etm_cache_key_info'] = md5($menu->asXML());
            $block->setAdditionalCacheKeyInfo($additionalCacheKeyInfo);
        }
    }

    public function adminhtmlCatalogProductAttributeEditPrepareForm($observer)
    {
        /* @var $form Varien_Data_Form */
        $form = $observer->getEvent()->getForm();
        /* @var $fieldset Varien_Data_Form_Element_Fieldset */
        $fieldset = $form->getElement('base_fieldset');

        $attribute = $observer->getEvent()->getAttribute();
        $_helper = Mage::helper('goodahead_etm');

        $fieldset->addField('goodahead_etm_entity_type_id', 'select', array(
            'name' => 'goodahead_etm_entity_type_id',
            'label' => $_helper->__("Bind to Custom Entity Type"),
            'title' => $_helper->__('Can be used only with catalog input type Dropdown or Multiple Select'),
            'note' => $_helper->__('Can be used only with catalog input type Dropdown or Multiple Select. Bind this product attribute to custom Entity Type.'),
            'values' => Mage::getModel('goodahead_etm/source_entity_type')->toOptionArray(true),
        ), 'frontend_input');

        if ($attribute->getId()) {
            $form->getElement('goodahead_etm_entity_type_id')->setDisabled(1);
        }

        if ($attribute->getData('goodahead_etm_entity_type_id')) {
            $fieldset->addField('goodahead_etm_entity_type_default_value', $attribute->getFrontendInput(), array(
                'name'   => 'goodahead_etm_entity_type_default_value',
                'label'  => $_helper->__("Default Value for Custom Entity Type"),
                'values' => $attribute->getSource()->getAllOptions(true, true),
            ), 'goodahead_etm_entity_type_id');
        }
    }

    public function catalogProductAttributeSavePredispatch($observer)
    {
        /** @var  $controller Mage_Adminhtml_Controller_Action */
        $controller = $observer->getEvent()->getControllerAction();
        $request = $controller->getRequest();
        if ($entityTypeId = $request->getParam('goodahead_etm_entity_type_id')) {
            Mage::register('goodahead_etm_attribute_entity_type', $entityTypeId);
        }
    }

    public function catalogEntityAttributeSaveBefore($observer)
    {
        $attribute = $observer->getEvent()->getAttribute();
        if ($attribute->isObjectNew()) {
            if (($entityTypeId = Mage::registry('goodahead_etm_attribute_entity_type'))) {
                $attribute->setData('source_model', 'goodahead_etm/source_entity');
                $attribute->setData('goodahead_etm_entity_type_id', $entityTypeId);
            } else {
                $attribute->unsetData('goodahead_etm_entity_type_id');
            }
            if ($attribute->getFrontendInput() == 'select' && !$attribute->hasData('_update_binding')) {
                $attribute->setData('_update_binding', true);
            }
        } else {
            if ($attribute->hasData('goodahead_etm_entity_type_id')) {
                if ($attribute->hasData('goodahead_etm_entity_type_default_value')) {
                    if ($attribute->getFrontendInput() == 'select') {
                        $attribute->setData('default_value', $attribute->getData('goodahead_etm_entity_type_default_value'));
                    } elseif ($attribute->getFrontendInput() == 'multiselect') {
                        if (is_array($attribute->getData('goodahead_etm_entity_type_default_value'))) {
                            $attribute->setData('default_value', implode(',', $attribute->getData('goodahead_etm_entity_type_default_value')));
                        } else {
                            $attribute->setData('default_value', $attribute->getData('goodahead_etm_entity_type_default_value'));
                        }
                    }
                }
            }

            $attribute->unsetData('goodahead_etm_entity_type_id');
        }
    }

    public function catalogEntityAttributeSaveAfter($observer)
    {
        $attribute = $observer->getEvent()->getAttribute();
        if ($attribute->getData('_update_binding')) {
            $attribute->setData('_update_binding', 0);
            if (($entityTypeId = Mage::registry('goodahead_etm_attribute_entity_type'))) {
                $attribute->setData('source_model', 'goodahead_etm/source_entity');
                $attribute->setData('goodahead_etm_entity_type_id', $entityTypeId);
                $attribute->save();
            }
        }
    }

    public function catalogEntityAttributeLoadAfter($observer)
    {
        $attribute = $observer->getEvent()->getAttribute();

        if ($attribute->hasData('goodahead_etm_entity_type_id')) {
            if ($attribute->hasData('default_value')) {
                if ($attribute->getFrontendInput() == 'multiselect') {
                    $attribute->setData('goodahead_etm_entity_type_default_value', explode(',', $attribute->getData('default_value')));
                } else {
                    $attribute->setData('goodahead_etm_entity_type_default_value', $attribute->getData('default_value'));
                }
            }
        }
    }

    public function addHandlerToCatalogOutputHelper($observer)
    {
        /** @var Mage_Catalog_Helper_Output $outputHelper */
        $outputHelper = $observer->getEvent()->getHelper();
        $outputHandlerBlock
            = Mage::app()->getLayout()->createBlock('goodahead_etm/product_attribute_output_handler');
        $outputHelper->addHandler('productAttribute', $outputHandlerBlock);
    }

    public function cronDispatch()
    {
        Goodahead_Etm_Processor_Autoload::register();
    }

}
