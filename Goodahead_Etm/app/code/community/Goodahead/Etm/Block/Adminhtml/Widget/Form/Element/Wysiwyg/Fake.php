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

class Goodahead_Etm_Block_Adminhtml_Widget_Form_Element_Wysiwyg_Fake
    extends Varien_Data_Form_Element_Editor
    implements Varien_Data_Form_Element_Renderer_Interface
{
    protected $_element;

    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $this->_element = $element;
        return $this->getElementHtml();
    }

    public function getElement()
    {
        return $this->_element;
    }

    public function getElementHtml()
    {
        $element = $this->getElement();
        if ($element->getConfig('widget_window_url')) {
            $html = $element->_getButtonsHtml() . $element->getTextInputHtml();
            $html = $element->_wrapIntoContainer($html);
            return $html;
        }
        return $element->getTextInputHtml();
    }
}