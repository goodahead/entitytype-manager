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

class Goodahead_Etm_Model_Source_Entity_Attribute
{

    public function toOptionsArray($entityType, $emptyLine = false)
    {

        $result = array();
        if ($emptyLine) {
            $result = array(
                array(
                    'value' => '',
                    'label' => '',
                ),
            );
        }
        /** @var $attributesCollection Goodahead_Etm_Model_Resource_Entity_Attribute_Collection */
        $attributesCollection = Mage::getModel('goodahead_etm/entity_attribute')->getCollection();
        $attributesCollection->setEntityTypeFilter($entityType);
        return array_merge($result, $attributesCollection->load()->toOptionArray());
    }

    public function toOptionsArrayWithoutExcludedTypes($entityType, $emptyLine = false, $types = array())
    {
        $result = array();
        if ($emptyLine) {
            $result = array(
                array(
                    'value' => '',
                    'label' => '',
                ),
            );
        }
        /** @var $attributesCollection Goodahead_Etm_Model_Resource_Entity_Attribute_Collection */
        $attributesCollection = Mage::getModel('goodahead_etm/entity_attribute')->getCollection();
        $attributesCollection->setEntityTypeFilter($entityType);

        if (count($types)) {
            $attributesCollection->addFieldToFilter('frontend_input', array('nin' => $types));
            // TODO: Rework
            $attributesCollection->addFieldToFilter('backend_type', array('neq' => 'static'));
        }

        return array_merge($result, $attributesCollection->load()->toOptionArray());
    }

}