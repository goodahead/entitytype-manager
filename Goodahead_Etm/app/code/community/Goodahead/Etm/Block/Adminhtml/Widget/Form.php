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

class Goodahead_Etm_Block_Adminhtml_Widget_Form extends Mage_Adminhtml_Block_Widget_Form
{
    public function getWysiwygConfig($data = array())
    {
        $wysiwygTextModeFlag = false;
        if (is_array($data) && array_key_exists('wysiwyg_text_mode', $data)
            && $data['wysiwyg_text_mode'] === true
        ) {
            $wysiwygTextModeFlag = true;
        }
        if ($wysiwygTextModeFlag || !$configModel = $this->getWysiwygModelClass()) {
            $configModel = 'goodahead_etm/wysiwyg_config';
        }
        return Mage::getSingleton($configModel)->getConfig($data);
    }
}