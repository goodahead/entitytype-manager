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

class Goodahead_Etm_Model_Source_Entity
    extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    /**
     * Default values for option cache
     *
     * @var array
     */
    protected $_optionsDefault = array();

    /**
     * Retrieve Full Option values array
     *
     * @param bool $withEmpty       Add empty option to array
     * @param bool $defaultValues
     * @return array
     */
    public function getAllOptions($withEmpty = true, $defaultValues = false)
    {
        $result = array();

        $attribute = $this->getAttribute();
        $storeId = $this->getAttribute()->getStoreId();

        if (!is_array($this->_options)) {
            $this->_options = array();
        }
        if (!is_array($this->_optionsDefault)) {
            $this->_optionsDefault = array();
        }

        $entityTypeId = $attribute->getData('goodahead_etm_entity_type_id');

        /** @var Goodahead_Etm_Model_Entity_Type $entityType */
        $entityType = Mage::getModel('goodahead_etm/entity_type')->load($entityTypeId);
        if ($entityTypeId = $entityType->getId()) {
            if (!array_key_exists($entityTypeId, $this->_options)) {
                $this->_options[$entityTypeId] = array();
            }
            if (!isset($this->_options[$entityTypeId][$storeId])) {
                /** @var Goodahead_Etm_Model_Resource_Entity_Collection $collection */
                $collection = Mage::helper('goodahead_etm')
                    ->getEntityCollectionByEntityType($entityType);
                $collection->setStoreId($storeId);
                $etmDefaultAttribute = $entityType->getDefaultAttribute();
                if (isset($etmDefaultAttribute)) {
                    $collection->addAttributeToSort($etmDefaultAttribute->getAttributeCode());
                }

                $this->_options[$entityTypeId][$storeId]        = $collection->toOptionArray();
                // TODO: Implement default values for collection
//                $this->_optionsDefault[$entityTypeId][$storeId] = $collection->toOptionArray(true);
            }
            $result =
                /*$defaultValues
                    ? $this->_optionsDefault[$entityTypeId][$storeId]
                    :*/ $this->_options[$entityTypeId][$storeId];
            if ($withEmpty) {
                array_unshift($result, array('label' => '', 'value' => ''));
            }
        }

        return $result;
    }
}
