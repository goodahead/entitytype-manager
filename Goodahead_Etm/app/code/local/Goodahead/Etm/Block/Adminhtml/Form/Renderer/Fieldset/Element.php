<?php

class Goodahead_Etm_Block_Adminhtml_Form_Renderer_Fieldset_Element
    extends Mage_Adminhtml_Block_Widget_Form_Renderer_Fieldset_Element
{
    const SCOPE_STORE                           = 0;
    const SCOPE_GLOBAL                          = 1;
    const SCOPE_WEBSITE                         = 2;

    /**
     * Initialize block template
     */
    protected function _construct()
    {
        $this->setTemplate('goodahead/etm/form/renderer/fieldset/element.phtml');
    }

    public function isScopeGlobal($attribute)
    {
        return $attribute->getIsGlobal() == self::SCOPE_GLOBAL;
    }

    public function isScopeWebsite($attribute)
    {
        return $attribute->getIsGlobal() == self::SCOPE_WEBSITE;
    }

    public function isScopeStore($attribute)
    {
        return $attribute->getIsGlobal() == self::SCOPE_STORE;
    }

    /**
     * Retrieve data object related with form
     *
     * @return Mage_Catalog_Model_Product || Mage_Catalog_Model_Category
     */
    public function getDataObject()
    {
        return $this->getElement()->getForm()->getDataObject();
    }

    /**
     * Retireve associated with element attribute object
     *
     * @return Mage_Catalog_Model_Resource_Eav_Attribute
     */
    public function getAttribute()
    {
        return $this->getElement()->getEntityAttribute();
    }

    /**
     * Retrieve associated attribute code
     *
     * @return string
     */
    public function getAttributeCode()
    {
        return $this->getAttribute()->getAttributeCode();
    }

    /**
     * Check "Use default" checkbox display availability
     *
     * @return bool
     */
    public function canDisplayUseDefault()
    {
        if ($attribute = $this->getAttribute()) {
            if (!$this->isScopeGlobal($attribute)
                && $this->getDataObject()
                && $this->getDataObject()->getId()
                && $this->getDataObject()->getStoreId()) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check default value usage fact
     *
     * @return bool
     */
    public function usedDefault()
    {
        $attributeCode = $this->getAttribute()->getAttributeCode();
        $defaultValue = $this->getDataObject()->getAttributeDefaultValue($attributeCode);

        if (!$this->getDataObject()->getExistsStoreValueFlag($attributeCode)) {
            return true;
        } else if ($this->getElement()->getValue() == $defaultValue &&
            $this->getDataObject()->getStoreId() != $this->_getDefaultStoreId()
        ) {
            return false;
        }
        if ($defaultValue === false && !$this->getAttribute()->getIsRequired() && $this->getElement()->getValue()) {
            return false;
        }
        return $defaultValue === false;
    }

    /**
     * Disable field in default value using case
     *
     * @return Mage_Adminhtml_Block_Catalog_Form_Renderer_Fieldset_Element
     */
    public function checkFieldDisable()
    {
        if ($this->canDisplayUseDefault() && $this->usedDefault()) {
            $this->getElement()->setDisabled(true);
        }
        return $this;
    }

    /**
     * Retrieve label of attribute scope
     *
     * GLOBAL | WEBSITE | STORE
     *
     * @return string
     */
    public function getScopeLabel()
    {
        $html = '';
        $attribute = $this->getElement()->getEntityAttribute();
        if (!$attribute || Mage::app()->isSingleStoreMode() || $attribute->getFrontendInput()=='gallery') {
            return $html;
        }
        if ($this->isScopeGlobal($attribute)) {
            $html .= Mage::helper('adminhtml')->__('[GLOBAL]');
        } elseif ($this->isScopeWebsite($attribute)) {
            $html .= Mage::helper('adminhtml')->__('[WEBSITE]');
        } elseif ($this->isScopeStore($attribute)) {
            $html .= Mage::helper('adminhtml')->__('[STORE VIEW]');
        }

        return $html;
    }

    /**
     * Retrieve element label html
     *
     * @return string
     */
    public function getElementLabelHtml()
    {
        $element = $this->getElement();
        $label = $element->getLabel();
        if (!empty($label)) {
            $element->setLabel($this->__($label));
        }
        return $element->getLabelHtml();
    }

    /**
     * Retrieve element html
     *
     * @return string
     */
    public function getElementHtml()
    {
        return $this->getElement()->getElementHtml();
    }

    /**
     * Default sore ID getter
     *
     * @return integer
     */
    protected function _getDefaultStoreId()
    {
        return Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID;
    }
}
