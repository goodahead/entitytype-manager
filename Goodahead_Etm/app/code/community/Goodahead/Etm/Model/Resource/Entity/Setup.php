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

class Goodahead_Etm_Model_Resource_Entity_Setup
    extends Mage_Eav_Model_Entity_Setup
{

    /**
     * Default attribute set name
     *
     * @var string
     */
    protected $_defaultAttributeSetName  = 'Default';

    protected $_callAfterApplyAllUpdates = true;

    public function __construct($resourceName)
    {
        parent::__construct($resourceName);

        Goodahead_Etm_Processor_Autoload::register();
    }

    /**
     * @return string
     */
    public function getDefaultAttributeSetName()
    {
        return $this->_defaultAttributeSetName;
    }

    /**
     * @return string
     */
    public function getDefaultGroupName()
    {
        if (isset($this->_defaultGroupName)) {
            return $this->_defaultGroupName;
        } elseif (isset($this->_generalGroupName)) {
            return $this->_generalGroupName;
        }

        return null;
    }

    /**
     * @return string
     */
    public function getGeneralGroupName()
    {
        return $this->_generalGroupName;
    }

    /**
     * Retrieve value from array by key or return default value
     *
     * @param array $array
     * @param string $key
     * @param string $default
     * @return string
     */
    protected function _getValue($array, $key, $default = null)
    {
        if (isset($array[$key]) && is_bool($array[$key])) {
            $array[$key] = (int) $array[$key];
        }
        return isset($array[$key]) ? $array[$key] : $default;
    }

    /**
     * Create entity tables
     *
     * @param $baseTableName
     * @param array $options
     * - no-main
     * - no-default-types
     * - types
     * @throws Mage_Core_Exception
     * @return Goodahead_Etm_Model_Entity_Setup
     */
    public function createEntityTables($baseTableName, array $options = array())
    {
        $isNoCreateMainTable = $this->_getValue($options, 'no-main', false);
        $isNoDefaultTypes    = $this->_getValue($options, 'no-default-types', false);
        $customTypes         = $this->_getValue($options, 'types', array());
        $tables              = array();

        if (!$isNoCreateMainTable) {
            /**
             * Create table main eav table
             */
            $connection = $this->getConnection();
            $mainTable = $connection
                ->newTable($this->getTable($baseTableName))
                ->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
                    'unsigned'  => true,
                    'identity'  => true,
                    'nullable'  => false,
                    'primary'   => true,
                 ), 'Entity Id')
                ->addColumn('entity_type_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
                    'unsigned'  => true,
                    'nullable'  => false,
                    'default'   => '0',
                ), 'Entity Type Id')
                ->addColumn('attribute_set_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
                    'unsigned'  => true,
                    'nullable'  => false,
                    'default'   => '0',
                ), 'Attribute Set Id')
                ->addColumn('increment_id', Varien_Db_Ddl_Table::TYPE_VARCHAR, 50, array(
                    'nullable'  => false,
                    'default'   => '',
                ), 'Increment Id')
                ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
                    'unsigned'  => true,
                    'nullable'  => false,
                    'default'   => '0',
                ), 'Store Id')
                ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
                    'nullable'  => false,
                    'default'   => 0,
                ), 'Created At')
                ->addColumn('updated_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
                    'nullable'  => false,
                    'default'   => Goodahead_Core_Model_Resource_Setup_Compatibility::getTimestampColumnDefaultValue(
                        Goodahead_Core_Model_Resource_Setup_Compatibility::TIMESTAMP_INIT_UPDATE
                    ),
                ), 'Updated At')
                ->addColumn('is_active', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
                    'unsigned'  => true,
                    'nullable'  => false,
                    'default'   => '1',
                ), 'Defines Is Entity Active')
                ->addIndex($this->getIdxName($baseTableName, array('entity_type_id')),
                    array('entity_type_id'))
                ->addIndex($this->getIdxName($baseTableName, array('store_id')),
                    array('store_id'))
                ->addForeignKey($this->getFkName($baseTableName, 'entity_type_id', 'eav/entity_type', 'entity_type_id'),
                    'entity_type_id', $this->getTable('eav/entity_type'), 'entity_type_id',
                    Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
                ->addForeignKey($this->getFkName($baseTableName, 'store_id', 'core/store', 'store_id'),
                    'store_id', $this->getTable('core/store'), 'store_id',
                    Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
            ;

            $comment = 'Eav Entity Main Table';
            if (method_exists($mainTable, 'setComment')) {
                $mainTable->setComment($comment);
            } else {
                $mainTable->setOption('comment', $comment);
            }

            $tables[$this->getTable($baseTableName)] = $mainTable;
        }

        $types = array();
        if (!$isNoDefaultTypes) {
            $types = array(
                'datetime'  => array(
                    Goodahead_Core_Helper_Data::getConstValue(
                        'Varien_Db_Ddl_Table::TYPE_DATETIME',
                        Varien_Db_Ddl_Table::TYPE_TIMESTAMP
                    ), null),
                'decimal'   => array(Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4'),
                'int'       => array(Varien_Db_Ddl_Table::TYPE_INTEGER, null),
                'text'      => array(Varien_Db_Ddl_Table::TYPE_LONGVARCHAR, '64k'),
                'varchar'   => array(Varien_Db_Ddl_Table::TYPE_VARCHAR, '255'),
                'char'   => array(Varien_Db_Ddl_Table::TYPE_VARCHAR, '255')
            );
        }

        if (!empty($customTypes)) {
            foreach ($customTypes as $type => $fieldType) {
                if (count($fieldType) != 2) {
                    throw Mage::exception('Mage_Eav', Mage::helper('eav')->__('Wrong type definition for %s', $type));
                }
                $types[$type] = $fieldType;
            }
        }

        $_updateTableName = null;

        /**
         * Create table array($baseTableName, $type)
         */
        foreach ($types as $type => $fieldType) {
            $eavTableName = array($baseTableName, $type);

            $eavTable = $connection->newTable($this->getTable($eavTableName));
            $eavTable
                ->addColumn('value_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
                    'unsigned'  => true,
                    'identity'  => true,
                    'nullable'  => false,
                    'primary'   => true,
                    ), 'Value Id')
                ->addColumn('entity_type_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
                    'unsigned'  => true,
                    'nullable'  => false,
                    'default'   => '0',
                    ), 'Entity Type Id')
                ->addColumn('attribute_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
                    'unsigned'  => true,
                    'nullable'  => false,
                    'default'   => '0',
                    ), 'Attribute Id')
                ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
                    'unsigned'  => true,
                    'nullable'  => false,
                    'default'   => '0',
                    ), 'Store Id')
                ->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
                    'unsigned'  => true,
                    'nullable'  => false,
                    'default'   => '0',
                    ), 'Entity Id')
                ->addColumn('value', $fieldType[0], $fieldType[1], array(
                    'nullable'  => false,
                    ), 'Attribute Value')
                ->addIndex($this->getIdxName($eavTableName, array('entity_type_id')),
                    array('entity_type_id'))
                ->addIndex($this->getIdxName($eavTableName, array('attribute_id')),
                    array('attribute_id'))
                ->addIndex($this->getIdxName($eavTableName, array('store_id')),
                    array('store_id'))
                ->addIndex($this->getIdxName($eavTableName, array('entity_id')),
                    array('entity_id'));
            if ($type !== 'text') {
                $eavTable->addIndex($this->getIdxName($eavTableName, array('attribute_id', 'value')),
                    array('attribute_id', 'value'));
                $eavTable->addIndex($this->getIdxName($eavTableName, array('entity_type_id', 'value')),
                    array('entity_type_id', 'value'));
            }

            if ($fieldType[0] == Varien_Db_Ddl_Table::TYPE_TIMESTAMP) {
                $_updateTableName = $this->getTable($eavTableName);
            }

            $eavTable
                ->addForeignKey($this->getFkName($eavTableName, 'entity_id', $baseTableName, 'entity_id'),
                    'entity_id', $this->getTable($baseTableName), 'entity_id',
                    Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
                ->addForeignKey($this->getFkName($eavTableName, 'entity_type_id', 'eav/entity_type', 'entity_type_id'),
                    'entity_type_id', $this->getTable('eav/entity_type'), 'entity_type_id',
                    Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
                ->addForeignKey($this->getFkName($eavTableName, 'store_id', 'core/store', 'store_id'),
                    'store_id', $this->getTable('core/store'), 'store_id',
                    Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
            ;

            $comment = 'Eav Entity Value Table';
            if (method_exists($eavTable, 'setComment')) {
                $eavTable->setComment($comment);
            } else {
                $eavTable->setOption('comment', $comment);
            }

            $tables[$this->getTable($eavTableName)] = $eavTable;
        }

        //$connection->beginTransaction();
        try {
            foreach ($tables as $tableName => $table) {
                $connection->createTable($table);
                if (isset($_updateTableName) && $_updateTableName == $tableName) {
                    $connection->changeColumn(
                        $tableName,
                        'value',
                        'value',
                        'DATETIME NOT NULL'
                    );
                }
            }
            //$connection->commit();
        } catch (Exception $e) {
           //$connection->rollBack();
           throw Mage::exception('Mage_Eav', Mage::helper('eav')->__('Can\'t create table: %s', $tableName));
        }

        return $this;
    }

    /**
     * Add an entity type
     *
     * If already exists updates the entity type with params data
     *
     * @param string $code
     * @param array $params
     * @return Mage_Eav_Model_Entity_Setup
     */
    public function addEntityType($code, array $params)
    {
        $_updateMode = false;
        if ($this->getEntityType($code, 'entity_type_id')) {
            $_updateMode = true;
        }
        $params['entity_model'] = $this->_getValue($params, 'entity_model',
            sprintf('goodahead_etm/custom_%s_entity', $code));
        parent::addEntityType($code, $params);

        $entityTypeId = $this->getEntityType($code, 'entity_type_id');
        if ($entityTypeId) {
            $data = array(
                'entity_type_id'            => $entityTypeId,
                'entity_type_name'          => $this->_getValue($params, 'entity_type_name', $code),
                'default_attribute_id'      => $this->_getValue($params, 'default_attribute_id'),
                'entity_type_root_template' => $this->_getValue($params, 'entity_type_root_template'),
                'entity_type_layout_xml'    => $this->_getValue($params, 'entity_type_layout_xml'),
                'entity_type_content'       => $this->_getValue($params, 'entity_type_content'),
            );
            if ($_updateMode) {
                unset($data['entity_type_id']);
                $this->updateEntityTypeExtra($code, $data);
            } else {
                $this->_conn->insert($this->getTable('goodahead_etm/eav_entity_type'), $data);
            }
        }
        return $this;
    }

    public function updateEntityTypeExtra($code, $field, $value=null)
    {
        $this->updateTableRow('goodahead_etm/etm_entity_type',
            'entity_type_id', $this->getEntityTypeId($code),
            $field, $value
        );
        return $this;
    }


    public function isNewDdlModel()
    {
        $coreVersion = Goodahead_Core_Helper_Data::getMagentoCoreVersion();
        return version_compare($coreVersion, '1.6', '>=');
    }

    public function getTable($tableName)
    {
        if ($this->isNewDdlModel()) {
            return parent::getTable($tableName);
        }

        if (is_array($tableName)) {
            list($tableName, $suffix) = $tableName;
            $cacheName = $tableName . '_' . $suffix;
        } else {
            $cacheName = $tableName;
        }
        if (!isset($this->_tables[$cacheName])) {
            $this->_tables[$cacheName] = Mage::getSingleton('core/resource')->getTableName($tableName);
            if (isset($suffix)) {
                $this->_tables[$cacheName] = $this->_tables[$cacheName] . '_' . $suffix;
            }
        }
        return $this->_tables[$cacheName];
    }

    /**
     * Redeclared for backwards compatibility with Magento versions prior to
     * 1.6.x Community and 1.11.x Enterprise
     *
     * @param string       $tableName
     * @param array|string $fields
     * @param string       $indexType
     *
     * @return mixed|string
     */
    public function getIdxName($tableName, $fields, $indexType = '')
    {
        if (method_exists(get_parent_class(__CLASS__), 'getIdxName')) {
            return parent::getIdxName($tableName, $fields, $indexType);
        } else {
            return Goodahead_Core_Model_Resource_Setup_Compatibility::getIndexName(
                $this->getTable($tableName),
                $fields,
                $indexType);
        }
    }

    /**
     * Redeclared for backwards compatibility with Magento versions prior to
     * 1.6.x Community and 1.11.x Enterprise
     *
     * @param string $priTableName
     * @param string $priColumnName
     * @param string $refTableName
     * @param string $refColumnName
     *
     * @return mixed|string
     */
    public function getFkName($priTableName, $priColumnName, $refTableName, $refColumnName)
    {
        if (method_exists(get_parent_class(__CLASS__), 'getFkName')) {
            return parent::getFkName($priTableName, $priColumnName, $refTableName, $refColumnName);
        } else {
            return Goodahead_Core_Model_Resource_Setup_Compatibility::getIndexName(
                $this->getTable($priTableName),
                $priColumnName,
                $this->getTable($refTableName),
                $refColumnName);
        }
    }

}
