<?php


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
        /* @var $frontendInput Varien_Data_Form_Element_Select */
        $frontendInput = $form->getElement('frontend_input');
//        $frontendInput->get

        $attribute = $observer->getEvent()->getAttribute();
//        $hiddenFields = Mage::registry('attribute_type_hidden_fields');

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

//        $allowedInputs = array (
//            'select' => true,
//            'multiselect' =>true,
//        );

//        foreach ($frontendInput->getValues() as $value) {
//            if (!array_key_exists($value['value'], $allowedInputs)) {
//                if (array_key_exists($value['value'], $hiddenFields)) {
//                    $hiddenFields[$value['value']][] = 'goodahead_etm_entity_type';
//                } else {
//                    $hiddenFields[$value['value']] = array('goodahead_etm_entity_type');
//                }
//            }
//        }

//        Mage::unregister('attribute_type_hidden_fields');
//        Mage::register('attribute_type_hidden_fields', $hiddenFields);
//        $fieldset
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
            $attribute->unsetData('goodahead_etm_entity_type_id');
        }
    }

    public function catalogEntityAttributeSaveAfter($observer)
    {
        $attribute = $observer->getEvent()->getAttribute();
        if ($attribute->getData('_update_binding')) {
            $attribute->setData('_update_binding', 0);
            if (($entityTypeId = Mage::registry('goodahead_etm_attribute_entity_type')) !== false) {
                $attribute->setData('source_model', 'goodahead_etm/source_entity');
                $attribute->setData('goodahead_etm_entity_type_id', $entityTypeId);
                $attribute->save();
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
}
