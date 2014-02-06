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

class Goodahead_Etm_Block_Adminhtml_Form_Renderer_Fieldset_Element_Image
    extends Varien_Data_Form_Element_Image
{

    /**
     * @return Goodahead_Etm_Model_Entity_Type
     */
    public function getEntityType()
    {
        return Mage::registry('etm_entity_type');
    }

    public function getValue()
    {
        $value = $this->getData('value');
        if (is_array($value)) {
            if (!empty($value['value'])) {
                $value = $value['value'];
            } else {
                $value = null;
            }
        }
        return $value;
    }

    protected function _getUrl()
    {
        $url = false;
        if ($this->getValue()) {
            $url = sprintf('%sgoodahead/etm/images/%s/%s/%s',
                Mage::getBaseUrl('media'),
                $this->getEntityType()->getEntityTypeCode(),
                $this->getEntityAttribute()->getAttributeCode(),
                $this->getValue()
            );
        }
        return $url;
    }

}
