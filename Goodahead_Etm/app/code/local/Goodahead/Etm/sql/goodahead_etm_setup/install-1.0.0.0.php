<?php

/** @var $installer Goodahead_Etm_Model_Resource_Entity_Setup */
$installer = $this;
$installer->startSetup();

$installer->createEntityTables('goodahead_etm/entity');

$table = $installer->getConnection()->newTable($installer->getTable('goodahead_etm/eav_entity_type'));
$table
    ->addColumn('entity_type_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
    ), 'Entity Type ID')
    ->addColumn('entity_type_name', Varien_Db_Ddl_Table::TYPE_TEXT, 128, array(
        'nullable'  => false,
    ), 'User Email')
    ->addColumn('entity_type_root_template', Varien_Db_Ddl_Table::TYPE_TEXT, 128, array(), 'Root Template')
    ->addColumn('entity_type_layout_xml', Varien_Db_Ddl_Table::TYPE_TEXT, null, array(), 'Layout XML')
    ->addColumn('entity_type_content', Varien_Db_Ddl_Table::TYPE_TEXT, null, array(), 'Content')
    ->addForeignKey($installer->getFkName('goodahead_etm/eav_entity_type', 'entity_type_id', 'eav/entity_type', 'entity_type_id'),
        'entity_type_id', $installer->getTable('eav/entity_type'), 'entity_type_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE
    )
    ->setComment('ETM Entity Type extended info');
$installer->getConnection()->createTable($table);

// Add column and corresponding indexes to catalog/eav_attribute table, which will hold referenced entity type
$installer->getConnection()->addColumn($installer->getTable('catalog/eav_attribute'), 'goodahead_etm_entity_type_id', array(
    'type' => Varien_Db_Ddl_Table::TYPE_SMALLINT,
    'unsigned'  => true,
    'nullable'  => true,
    'comment' => 'Entity Type Manager Entity type Binding'
));

$installer->getConnection()->addIndex(
    $installer->getTable('catalog/eav_attribute'),
    $installer->getIdxName('catalog/eav_attribute', array('goodahead_etm_entity_type_id')),
    array('goodahead_etm_entity_type_id')
);

$installer->getConnection()->addForeignKey(
    $installer->getFkName('catalog/eav_attribute', 'goodahead_etm_entity_type_id', 'goodahead_etm/eav_entity_type', 'entity_type_id'),
    $installer->getTable('catalog/eav_attribute'),
    'goodahead_etm_entity_type_id',
    $installer->getTable('goodahead_etm/eav_entity_type'),
    'entity_type_id'
);

/**
 * Create table 'goodahead_etm/eav_attribute'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('goodahead_etm/eav_attribute'))
    ->addColumn('attribute_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Attribute ID')
    ->addColumn('attribute_name', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Attribute Name')
    ->addColumn('frontend_input_renderer', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Frontend Input Renderer')
    ->addColumn('is_global', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '1',
        ), 'Is Global')
    ->addColumn('is_visible', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '1',
        ), 'Is Visible')
    ->addForeignKey(
        $installer->getFkName('goodahead_etm/eav_attribute', 'attribute_id', 'eav/attribute', 'attribute_id'),
        'attribute_id', $installer->getTable('eav/attribute'), 'attribute_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('ETM EAV Attribute Table');
$installer->getConnection()->createTable($table);

$installer->endSetup();
