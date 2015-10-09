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

class Goodahead_Etm_Model_Observer
{
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
            'note' => $_helper->__('Can be used only with catalog input type Dropdown or Multiple Select.'
                . ' Bind this product attribute to custom Entity Type.'),
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
                        $attribute->setData(
                            'default_value',
                            $attribute->getData('goodahead_etm_entity_type_default_value')
                        );
                    } elseif ($attribute->getFrontendInput() == 'multiselect') {
                        if (is_array($attribute->getData('goodahead_etm_entity_type_default_value'))) {
                            $attribute->setData(
                                'default_value',
                                implode(',', $attribute->getData('goodahead_etm_entity_type_default_value'))
                            );
                        } else {
                            $attribute->setData(
                                'default_value',
                                $attribute->getData('goodahead_etm_entity_type_default_value')
                            );
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
                    $attribute->setData(
                        'goodahead_etm_entity_type_default_value',
                        explode(',', $attribute->getData('default_value'))
                    );
                } else {
                    $attribute->setData(
                        'goodahead_etm_entity_type_default_value',
                        $attribute->getData('default_value')
                    );
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

    public function wysiwygPluginActionResponse(Varien_Event_Observer $observer)
    {
        $event = $observer->getEvent();
        $variablesObject = $event->getVariables();
        $variables = $variablesObject->getData();
        /* @var $action Goodahead_Core_Adminhtml_Goodahead_Core_System_VariableController */
        $action = $event->getAction();
        $etmParam = $action->getRequest()->getParam('etm', null);
        if (isset($etmParam)) {
            $requestVariableParams = json_decode(base64_decode($etmParam), true);
            $variableNameList = array(
                'entity_type_id', 'entity_id', 'store_id',
                'etm_entity_attributes', 'etm_entity_type_toolbar', 'etm_attributes',
            );
            $variableParams = array();
            foreach ($variableNameList as $key) {
                $variableParams[$key] = (array_key_exists($key, $requestVariableParams) && !empty($requestVariableParams[$key]))
                    ? $requestVariableParams[$key]
                    : null;
            }
            if (isset($variableParams['entity_type_id'])) {
                $entityType = Mage::getModel('goodahead_etm/entity_type')
                    ->load($variableParams['entity_type_id']);
                Mage::register('etm_entity_type', $entityType);
                if ($entityType->getId()) {
                    if (isset($variableParams['etm_entity_attributes'])) {
                        $variables[] = $this->getEtmEntityAttributeVariables();
                    }
                    if (isset($variableParams['etm_attributes']) && isset($variableParams['entity_id'])) {
                        $entity = $this->getEtmHelper()
                            ->getEntityModelByEntityType(Mage::registry('etm_entity_type'));
                        $entity
                            ->setStoreId($variableParams['store_id'])
                            ->load($variableParams['entity_id']);
                        if ($entity->getId()) {
                            $variables[] = array(); // TODO result against array
                        }
                    }
                }
            }
            if (isset($variableParams['etm_entity_type_toolbar'])) {
                $variables[] = $this->getEtmEntitesListVariables();
            }
            $variablesObject->setData($variables);
        }
        return $this;
    }

    public function prepareWysiwygPluginVariables(Varien_Event_Observer $observer)
    {
        $event = $observer->getEvent();
        $config = $event->getConfig();

        if ($config->getData('add_variables') && $config->getData('etm_variables')) {
            $etmVariables = base64_encode(json_encode($config->getData('etm_variables')));
            $plugins = $config->getData('plugins');
            $variablePluginId = null;
            foreach ($plugins as $pluginId => $pluginData) {
                if (array_key_exists('name', $pluginData) && $pluginData['name'] == 'magentovariable') {
                    $variablePluginId = $pluginId;
                    break;
                }
            }
            if (isset($variablePluginId)) {
                $variablesUrl = $this->_getVariablesWysiwygActionUrl(array('etm' => $etmVariables));
                if (isset($plugins[$variablePluginId]['options']['onclick']['subject'])) {
                    $plugins[$variablePluginId]['options']['onclick']['subject'] = 
                        'GoodaheadvariablePlugin.loadChooser(\''.$variablesUrl.'\', \'{{html_id}}\');';
                    $config->setData('plugins', $plugins);
                }
            }
        }
        return $this;
    }

    /* @return Goodahead_Etm_Helper_Data */
    protected function getEtmHelper()
    {
        return Mage::helper('goodahead_etm');
    }

    protected function getEtmEntityAttributeVariables()
    {
        $entityType = Mage::registry('etm_entity_type');

        $visibleAttribute = $this->getEtmHelper()->getVisibleAttributes($entityType);
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
                'label' => $this->getEtmHelper()->__('%s', $variable['label'])
            );
        }
        if ($optionArray) {
            $optionArray = array(
                'label' => Mage::helper('core')->__('Entity Attributes'),
                'value' => $optionArray
            );
        }

        return $optionArray;
    }

    public function getEtmEntitesListVariables()
    {
        return array(
            'label' => $this->getEtmHelper()->__('Template'),
            'value' => array(
                array(
                    'label' => $this->getEtmHelper()->__('Pager (with sample configurations)'),
                    'value' => '{{pager limits=25|50|75 frame_length=2 page_var_name=page}}',
                ),
                array(
                    'label' => $this->getEtmHelper()->__('Pager'),
                    'value' => '{{pager}}',
                ),
                array(
                    'label' => $this->getEtmHelper()->__('Entity Type List (list of entities)'),
                    'value' => '{{items}}',
                )
            ),
        );
    }

    /**
     * Return url of action to get variables
     *
     * @return string
     */
    protected function _getVariablesWysiwygActionUrl($params = array())
    {
        return Mage::getSingleton('adminhtml/url')
            ->getUrl('*/goodahead_core_system_variable/wysiwygPlugin', $params);
    }
}
