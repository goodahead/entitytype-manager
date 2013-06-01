<?php

/** @var $installer Goodahead_Etm_Model_Entity_Setup */
$installer = $this;
$installer->startSetup();

$installer->createEntityTables('goodahead_etm/eav');

$table = $installer->getConnection()
    ->newTable($installer->getTable('goodahead_etm/eav_entity_type'))
    ->addColumn('entity_type_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
    ), 'Entity Type ID')
    ->addColumn('entity_type_name', Varien_Db_Ddl_Table::TYPE_TEXT, 128, array(
        'nullable'  => false,
    ), 'Entity Type Name')
    ->addForeignKey($installer->getFkName('goodahead_etm/eav_entity_type', 'entity_type_id', 'eav/entity_type', 'entity_type_id'),
        'entity_type_id', $installer->getTable('eav/entity_type'), 'entity_type_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE
    )
    ->setComment('ETM Entity Type extended info');
$installer->getConnection()->createTable($table);

$installer->endSetup();
