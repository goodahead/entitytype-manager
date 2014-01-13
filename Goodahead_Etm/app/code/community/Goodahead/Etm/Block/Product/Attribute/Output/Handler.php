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

class Goodahead_Etm_Block_Product_Attribute_Output_Handler
    extends Mage_Core_Block_Template
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
    protected $_template = 'goodahead/etm/product/attribute/output/handler.phtml';

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
