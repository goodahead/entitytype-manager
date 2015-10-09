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

class Goodahead_Etm_Processor_Autoload
{

    static protected $_instance;

    protected $_includePath = '';

    protected $_isRegistered = false;

    protected $_dataDirSuffix = 'data';

    public function __construct()
    {
        $this->_includePath = Mage::getConfig()->getVarDir('goodahead' . DS . 'etm' . DS . 'includes' . DS);
    }

    /**
     * @return bool
     */
    public function getIsRegistered()
    {
        return $this->_isRegistered;
    }

    /**
     * @param bool $flag
     * @return Goodahead_Etm_Processor_Autoload
     */
    public function setIsRegistered($flag)
    {
        $this->_isRegistered = $flag;

        return $this;
    }

    /**
     * @return Goodahead_Etm_Processor_Autoload
     */
    public static function instance()
    {
        if (!self::$_instance) {
            self::$_instance = new Goodahead_Etm_Processor_Autoload();
        }

        return self::$_instance;
    }

    public static function register()
    {
        if (self::instance()->getIsRegistered()) {
            return;
        }

        $path = BP . DS . 'var' . DS . 'goodahead' . DS . 'etm' . DS . 'includes';
        set_include_path($path . PS . get_include_path());

        if (version_compare(PHP_VERSION, '5.3.0') >= 0) {
            spl_autoload_register(array(self::instance(), 'autoload'), false, true);
        } else {
            spl_autoload_register(array(self::instance(), 'autoload'), false);
            spl_autoload_unregister(array(Varien_Autoload::instance(), 'autoload'));
            Varien_Autoload::register();
        }

        self::instance()->setIsRegistered(true);
    }

    public function autoload($class)
    {
        if (
            preg_match('/^Goodahead_Etm_Model_(?:Resource_)?Custom/', $class)
        ) {
            $classFileName = $class . '.php';

            if (!file_exists($this->_includePath . $classFileName)) {
                if (!$this->_generateClass($class)) {
                    return false;
                }
            }

            include $classFileName;

            return true;
        }

        return false;
    }

    protected function _generateClass($class)
    {
        $entityTypeCode = $this->_getEntityTypeCodeFromClassName($class);

        if (!$entityTypeCode) {
            return false;
        }

        if (preg_match('/^Goodahead_Etm_Model_Resource_Custom/', $class)) {
            return $this->_generateEntityResourceClass($class, $entityTypeCode);
        }

        return $this->_generateEntityClass($class, $entityTypeCode);
    }

    protected function _generateEntityClass($class, $entityTypeCode)
    {
        $template = $this->_loadClassTemplate('ModelEntityTemplate.template');

        if (!$template) {
            return false;
        }

        $template = str_replace(
            array(
                '<class>',
                '<entity_type_code>'
            ),
            array(
                $class,
                $entityTypeCode
            ), $template
        );

        if (Mage::getConfig()->createDirIfNotExists($this->_includePath)) {
            file_put_contents($this->_includePath . $class . '.php', $template);

            return true;
        }

        return false;
    }

    protected function _generateEntityResourceClass($class, $entityTypeCode)
    {
        $templateName = 'ModelEntityResourceTemplate.template';

        if (preg_match('/Collection$/', $class)) {
            $templateName = 'ModelEntityCollectionTemplate.template';
        }

        $template = $this->_loadClassTemplate($templateName);

        if (!$template) {
            return false;
        }

        $template = str_replace(
            array(
                '<class>',
                '<entity_type_code>'
            ),
            array(
                $class,
                $entityTypeCode
            ), $template
        );

        if (Mage::getConfig()->createDirIfNotExists($this->_includePath)) {
            file_put_contents($this->_includePath . $class . '.php', $template);

            return true;
        }

        return false;
    }

    /**
     * @param string $name
     * @return bool|string
     */
    protected function _loadClassTemplate($name)
    {
        $dataPath = Mage::getModuleDir('', 'Goodahead_Etm') . DS;
        if ($this->_dataDirSuffix !== false) {
            $dataPath .= $this->_dataDirSuffix . DS;
        }

        if (file_exists($dataPath . $name)) {
            return file_get_contents($dataPath . $name);
        }

        return false;
    }

    /**
     * @param string $class
     * @return bool|string
     */
    protected function _getEntityTypeCodeFromClassName($class)
    {
        $matches = array();

        if (preg_match('/Custom_([a-zA-Z0-9_]+)_Entity/', $class, $matches)) {
            if (isset($matches[1])) {
                return strtolower($matches[1]);
            }
        }

        return false;
    }

}
