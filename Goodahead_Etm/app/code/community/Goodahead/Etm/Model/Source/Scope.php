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

class Goodahead_Etm_Model_Source_Scope
{
    /**
     * Return scope filter as options array
     *
     * @return array
     */
    public function toOptionArray()
    {
        $optionsArray = array();

        foreach ($this->toArray() as $value => $label) {
            $optionsArray[] = array('value' => $value, 'label' => $label);
        }

        return $optionsArray;
    }

    /**
     * Return scope filter as array
     *
     * @return array
     */
    public function toArray()
    {
        return array(
            Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE   => Mage::helper('catalog')->__('Store View'),
            Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE => Mage::helper('catalog')->__('Website'),
            Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL  => Mage::helper('catalog')->__('Global'),
        );
    }
}
