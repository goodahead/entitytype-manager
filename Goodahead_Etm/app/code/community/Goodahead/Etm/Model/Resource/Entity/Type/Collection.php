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

class Goodahead_Etm_Model_Resource_Entity_Type_Collection
//    extends Mage_Eav_Model_Resource_Entity_Type_Collection
    extends Mage_Eav_Model_Mysql4_Entity_Type_Collection // Compatibility with older versions
{
    public function _construct()
    {
        $this->_init('goodahead_etm/entity_type');
    }

    protected function _initSelect()
    {
        parent::_initSelect();
        $this->getSelect()->joinInner(
            array('etm_entity_type' => $this->getTable('etm_entity_type')),
            'main_table.entity_type_id = etm_entity_type.entity_type_id');
        return $this;
    }

    public function toOptionArray()
    {
        return $this->_toOptionArray('entity_type_id', 'entity_type_name');
    }
}
