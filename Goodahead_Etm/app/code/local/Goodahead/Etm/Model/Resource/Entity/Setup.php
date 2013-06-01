<?php
class Goodahead_Etm_Model_Resource_Entity_Setup extends Mage_Eav_Model_Entity_Setup
{
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
        return $this->_defaultGroupName;
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
                ->addColumn('increment_id', Varien_Db_Ddl_Table::TYPE_TEXT, 50, array(
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
                ), 'Created At')
                ->addColumn('updated_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
                    'nullable'  => false,
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
                ->setComment('Eav Entity Main Table');

            $tables[$this->getTable($baseTableName)] = $mainTable;
        }

        $types = array();
        if (!$isNoDefaultTypes) {
            $types = array(
                'datetime'  => array(Varien_Db_Ddl_Table::TYPE_DATETIME, null),
                'decimal'   => array(Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4'),
                'int'       => array(Varien_Db_Ddl_Table::TYPE_INTEGER, null),
                'text'      => array(Varien_Db_Ddl_Table::TYPE_TEXT, '64k'),
                'varchar'   => array(Varien_Db_Ddl_Table::TYPE_TEXT, '255'),
                'char'   => array(Varien_Db_Ddl_Table::TYPE_TEXT, '255')
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

        /**
         * Create table array($baseTableName, $type)
         */
        foreach ($types as $type => $fieldType) {
            $eavTableName = array($baseTableName, $type);

            $eavTable = $connection->newTable($this->getTable($eavTableName));
            $eavTable
                ->addColumn('value_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
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
                ->setComment('Eav Entity Value Table');

            $tables[$this->getTable($eavTableName)] = $eavTable;
        }

        //$connection->beginTransaction();
        try {
            foreach ($tables as $tableName => $table) {
                $connection->createTable($table);
            }
            //$connection->commit();
        } catch (Exception $e) {
           //$connection->rollBack();
           throw Mage::exception('Mage_Eav', Mage::helper('eav')->__('Can\'t create table: %s', $tableName));
        }

        return $this;
    }
}
