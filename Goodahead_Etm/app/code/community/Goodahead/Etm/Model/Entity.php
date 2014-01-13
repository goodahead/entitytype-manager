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

abstract class Goodahead_Etm_Model_Entity extends Mage_Core_Model_Abstract
{
    protected $_entityTypeId = null;
    protected $_entityTypeInstance = null;

    protected $_storeId = null;

    protected $_entityTypeCode;

    /**
     * Attribute default values
     *
     * This array contain default values for attributes which have there values
     * redefined for store
     *
     * @var array
     */
    protected $_defaultValues = array();

    /**
     * This array contains codes of attributes which have value in current store
     *
     * @var array
     */
    protected $_storeValuesFlags = array();

    protected function _construct()
    {
        $this->_init(sprintf('goodahead_etm/custom_%s_entity', $this->_entityTypeCode));
    }

    protected function _getEntityTypeId($id = null, $field = null)
    {
        if ($id !== null) {
            return $this->getResource()->getEntityTypeId($id, $field);
        } else {
            return $this->getEntityTypeId();
        }
    }

    /**
     * @return Goodahead_Etm_Model_Entity_Type
     */
    public function getEntityTypeInstance()
    {
        if (!isset($this->_entityTypeInstance) && $this->getEntityTypeId()) {
            $this->_entityTypeInstance = Mage::getModel('goodahead_etm/entity_type')->load($this->getEntityTypeId());
        }
        return $this->_entityTypeInstance;
    }

    public function setStoreId($storeId)
    {
        $this->_storeId = $storeId;

        return $this;
    }

    /**
     * Returns store id with which attribute values loaded
     *
     * @return int
     */
    public function getStoreId()
    {
        if ($this->_storeId !== null) {
            return $this->_storeId;
        }
        return Mage::app()->getStore()->getId();
    }

    public function getEntityLabel()
    {
        $typeInstance = $this->getEntityTypeInstance();
        $defaultAttributeId = $typeInstance->getDefaultAttributeId();
        $defaultAttribute = Mage::getModel('goodahead_etm/entity_attribute')->load($defaultAttributeId);
        if (!$defaultAttribute->getId()) {
            return $this->getId();
        }
        return $this->getData($defaultAttribute->getAttributeCode());
    }

    /**
     * @return bool|Goodahead_Etm_Model_Entity
     */
    public function duplicate()
    {
        if (!$this->getId()) {
            return false;
        }

        /** @var Goodahead_Etm_Model_Entity $entity */
        $entity = Mage::getModel(sprintf('goodahead_etm/custom_%s_entity', $this->_entityTypeCode));
        $entity->setData($this->getData());

        $entity->setId(null);

        $entity->save();

        return $entity;
    }

    /**
     * Validate attribute values
     *
     * @throws Mage_Eav_Model_Entity_Attribute_Exception
     * @return bool|array
     */
    public function validate()
    {
        return $this->_getResource()->validate($this);
    }

    /**
     * Adding attribute code and value to default value registry
     *
     * Default value existing is flag for using store value in data
     *
     * @param   string $attributeCode
     * @param   mixed  $value
     * @return  Goodahead_Etm_Model_Entity
     */
    public function setAttributeDefaultValue($attributeCode, $value)
    {
        $this->_defaultValues[$attributeCode] = $value;
        return $this;
    }

    /**
     * Retrieve default value for attribute code
     *
     * @param   string $attributeCode
     * @return  mixed|boolean
     */
    public function getAttributeDefaultValue($attributeCode)
    {
        return array_key_exists($attributeCode, $this->_defaultValues) ? $this->_defaultValues[$attributeCode] : false;
    }

    /**
     * Set flag for attribute code if attribute has value in current store and
     * does not use value from default store
     *
     * @param   string $attributeCode
     * @return  Mage_Catalog_Model_Abstract
     */
    public function setExistsStoreValueFlag($attributeCode)
    {
        $this->_storeValuesFlags[$attributeCode] = true;
        return $this;
    }

    /**
     * Check if object attribute has value in current store
     *
     * @param   string $attributeCode
     * @return  bool
     */
    public function getExistsStoreValueFlag($attributeCode)
    {
        return array_key_exists($attributeCode, $this->_storeValuesFlags);
    }

}
