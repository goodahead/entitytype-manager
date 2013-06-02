<?php

class Goodahead_Etm_Block_Product_Attribute_Output_Handler extends Mage_Core_Block_Template
{
    /**
     * @var string
     */
    protected $_attributeValue;

    /**
     * @var int
     */
    protected $_entityId;

    /**
     * Path to template file in theme
     *
     * @var string
     */
    protected $_template = 'goodahead_etm/product/attribute/output/handler.phtml';

    /**
     * @param Mage_Catalog_Helper_Output $outputHelper
     * @param string $attributeValue
     * @param array $params
     * @return string
     */
    public function productAttribute(Mage_Catalog_Helper_Output $outputHelper, $attributeValue, $params)
    {
        $attribute = Mage::getSingleton('eav/config')
            ->getAttribute(Mage_Catalog_Model_Product::ENTITY, $params['attribute']);

        if ($attribute->getData('goodahead_etm_entity_type_id')) {
            $this->_attributeValue = $attributeValue;
            $this->_entityId       = $params['product']->getData($params['attribute']);

            return $this->toHtml();
        } else {
            return $attributeValue;
        }
    }

    /**
     * @return string
     */
    public function getAttributeValue()
    {
        return $this->_attributeValue;
    }

    /**
     * @return int
     */
    public function getEntityId()
    {
        return $this->_entityId;
    }
}
