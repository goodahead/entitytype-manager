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
 * Class Goodahead_Etm_Model_Entity_Attribute
 *
 * @method Goodahead_Etm_Model_Resource_Entity_Attribute getResource() getResource()
 */
class Goodahead_Etm_Model_Entity_Attribute extends Mage_Eav_Model_Entity_Attribute
{

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix                         = 'goodahead_etm_entity_attribute';

    const SCOPE_STORE                           = 0;
    const SCOPE_GLOBAL                          = 1;
    const SCOPE_WEBSITE                         = 2;

    public function _construct()
    {
        $this->_init('goodahead_etm/entity_attribute');
    }

    /**
     * Return is attribute global
     *
     * @return integer
     */
    public function getIsGlobal()
    {
        return $this->_getData('is_global');
    }

    /**
     * Retrieve attribute is global scope flag
     *
     * @return bool
     */
    public function isScopeGlobal()
    {
        return $this->getIsGlobal() == self::SCOPE_GLOBAL;
    }

    /**
     * Retrieve attribute is website scope website
     *
     * @return bool
     */
    public function isScopeWebsite()
    {
        return $this->getIsGlobal() == self::SCOPE_WEBSITE;
    }

    /**
     * Retrieve attribute is store scope flag
     *
     * @return bool
     */
    public function isScopeStore()
    {
        return !$this->isScopeGlobal() && !$this->isScopeWebsite();
    }

    public function isSystem()
    {
        return $this->getIsUserDefined() == 0;
    }

    /**
     * Delete attributes with given ids
     *
     * @param array $attributeIds
     * @return $this
     */
    public function deleteAttributes(array $attributeIds)
    {
        $this->getResource()->deleteAttributes($attributeIds);
    }

    /**
     * Retrieve entity type
     *
     * @return string
     */
    public function getEntityType()
    {
        return Mage::getModel('goodahead_etm/entity_type')->load($this->getEntityTypeId());
    }

    public function isValueEmpty($value)
    {
        $attrType = $this->getBackend()->getType();
        $isEmpty =  //is_array($value) WTF??? Maybe was needed. For now fixed like that.
            $value === null
            || $value === false && $attrType != 'int'
            || $value === '' && ($attrType == 'int' || $attrType == 'decimal' || $attrType == 'datetime');

        return $isEmpty;
    }

}
