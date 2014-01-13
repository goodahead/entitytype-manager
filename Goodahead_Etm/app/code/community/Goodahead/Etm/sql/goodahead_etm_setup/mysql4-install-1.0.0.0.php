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

/** @var $installer Goodahead_Etm_Model_Resource_Entity_Setup */
$installer = $this;
$installer->startSetup();

$installer->createEntityTables('goodahead_etm/entity');

$table = $installer->getConnection()->newTable($installer->getTable('goodahead_etm/eav_entity_type'));
$table
    ->addColumn('entity_type_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
    ), 'Entity Type ID')
    ->addColumn('default_attribute_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'identity'  => false,
        'unsigned'  => true,
        'nullable'  => true,
    ), 'Entity Type ID')
    ->addColumn('entity_type_name', Varien_Db_Ddl_Table::TYPE_LONGVARCHAR, 128, array(
        'nullable'  => false,
    ), 'User Email')
    ->addColumn('entity_type_root_template', Varien_Db_Ddl_Table::TYPE_LONGVARCHAR, 128, array(), 'Root Template')
    ->addColumn('entity_type_layout_xml', Varien_Db_Ddl_Table::TYPE_LONGVARCHAR, null, array(), 'Layout XML')
    ->addColumn('entity_type_content', Varien_Db_Ddl_Table::TYPE_LONGVARCHAR, null, array(), 'Content')
    ->addForeignKey($installer->getFkName('goodahead_etm/eav_entity_type', 'entity_type_id', 'eav/entity_type', 'entity_type_id'),
        'entity_type_id', $installer->getTable('eav/entity_type'), 'entity_type_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE
    )
    ->addIndex($installer->getIdxName('goodahead_etm/eav_entity_type', array('default_attribute_id')),
        array('default_attribute_id'))
;

$comment = 'ETM Entity Type extended info';
if (method_exists($table, 'setComment')) {
    $table->setComment($comment);
} else {
    $table->setOption('comment', $comment);
}

$installer->getConnection()->createTable($table);

if ($this->isNewDdlModel()) {
    $columnDefinition = array(
        'type' => Varien_Db_Ddl_Table::TYPE_SMALLINT,
        'unsigned'  => true,
        'nullable'  => true,
        'comment' => 'Entity Type Manager Entity type Binding'
    );
} else {
    $columnDefinition = "SMALLINT UNSIGNED COMMENT 'Entity Type Manager Entity type Binding'";
}

// Add column and corresponding indexes to catalog/eav_attribute table, which will hold referenced entity type
$installer->getConnection()->addColumn(
    $installer->getTable('catalog/eav_attribute'),
    'goodahead_etm_entity_type_id',
    $columnDefinition);

$installer->getConnection()->addKey(
    $installer->getTable('catalog/eav_attribute'),
    $installer->getIdxName('catalog/eav_attribute', array('goodahead_etm_entity_type_id')),
    array('goodahead_etm_entity_type_id')
);

$installer->getConnection()->addConstraint(
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
    ->addColumn('attribute_name', Varien_Db_Ddl_Table::TYPE_LONGVARCHAR, 255, array(
        ), 'Attribute Name')
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
;

$comment = 'ETM EAV Attribute Table';
if (method_exists($table, 'setComment')) {
    $table->setComment($comment);
} else {
    $table->setOption('comment', $comment);
}

$installer->getConnection()->createTable($table);

/**
 * Add foreign key to entity type table
 */
$installer->getConnection()->addConstraint(
    $installer->getFkName('goodahead_etm/eav_entity_type', 'default_attribute_id', 'goodahead_etm/eav_attribute', 'attribute_id'),
    $installer->getTable('goodahead_etm/eav_entity_type'),
    'default_attribute_id',
    $installer->getTable('goodahead_etm/eav_attribute'),
    'attribute_id',
    Varien_Db_Ddl_Table::ACTION_SET_NULL, Varien_Db_Ddl_Table::ACTION_CASCADE
);

$installer->endSetup();
