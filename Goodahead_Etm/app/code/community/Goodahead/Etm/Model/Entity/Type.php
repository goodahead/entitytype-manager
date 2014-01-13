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
 * @copyright  Copyright (C) 2014 Goodahead Ltd. (http://www.goodahead.com)
 * @license    http://www.gnu.org/licenses/lgpl-3.0-standalone.html
 */

/**
 * Class Goodahead_Etm_Model_Entity_Type
 *
 * @method string getEntityTypeName() getEntityTypeName()
 */
class Goodahead_Etm_Model_Entity_Type extends Mage_Eav_Model_Entity_Type
{
    protected function _construct()
    {
        $this->_cacheTag = array(
            Mage_Adminhtml_Block_Page_Menu::CACHE_TAGS
        );

        $this->_init('goodahead_etm/entity_type');
    }

    protected function _beforeSave()
    {
        parent::_beforeSave();
        if ($this->getData('default_attribute_id') == '') {
            $this->setData('default_attribute_id', null);
        }
    }

    protected function _afterSave()
    {
        parent::_afterSave();
        if ($this->getCreateAttributeSet() === true) {
            $this->setCreateAttributeSet(false);
            $this->isObjectNew(false);
            $this->save();
        }
        return $this;
    }

    public function getEntityAttributeCollection()
    {
        $collection = $this->_getData('entity_attribute_collection');
        if ($collection) {
            return $collection;
        }
        return 'goodahead_etm/entity_attribute_collection';
    }
}
