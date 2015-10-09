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

/**
 * entity_type_title
 */
$columnComment = 'Entity Page Title';
if ($this->isNewDdlModel()) {
    $columnDefinition = array(
        'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'    => 255,
        'nullable'  => true,
        'comment'   => $columnComment,
    );
} else {
    $columnDefinition = "VARCHAR(255) COMMENT '{$columnComment}'";
}
$installer->getConnection()->addColumn(
    $installer->getTable('goodahead_etm/eav_entity_type'),
    'entity_type_title',
    $columnDefinition);

/**
 * entity_type_list_title
 */
$columnComment = 'Entity List Page Title';
if ($this->isNewDdlModel()) {
    $columnDefinition = array(
        'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'    => 255,
        'nullable'  => true,
        'comment'   => $columnComment,
    );
} else {
    $columnDefinition = "VARCHAR(255) COMMENT '{$columnComment}'";
}
$installer->getConnection()->addColumn(
    $installer->getTable('goodahead_etm/eav_entity_type'),
    'entity_type_list_title',
    $columnDefinition);


/**
 * entity_type_list_root_template
 */
$columnComment = 'Entities List Root Template';
if ($this->isNewDdlModel()) {
    $columnDefinition = array(
        'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'    => 128,
        'nullable'  => true,
        'comment'   => $columnComment,
    );
} else {
    $columnDefinition = "VARCHAR(128) COMMENT '{$columnComment}'";
}
$installer->getConnection()->addColumn(
    $installer->getTable('goodahead_etm/eav_entity_type'),
    'entity_type_list_root_template',
    $columnDefinition);

/**
 * entity_type_list_layout_xml
 */
$columnComment = 'Entities List Layout XML';
if ($this->isNewDdlModel()) {
    $columnDefinition = array(
        'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'    => null,
        'nullable'  => true,
        'comment'   => $columnComment,
    );
} else {
    $columnDefinition = "TEXT COMMENT '{$columnComment}'";
}
$installer->getConnection()->addColumn(
    $installer->getTable('goodahead_etm/eav_entity_type'),
    'entity_type_list_layout_xml',
    $columnDefinition);

/**
 * entity_type_list_content
 */
$columnComment = 'Entities List Template';
if ($this->isNewDdlModel()) {
    $columnDefinition = array(
        'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'    => null,
        'nullable'  => true,
        'comment'   => $columnComment,
    );
} else {
    $columnDefinition = "TEXT COMMENT '{$columnComment}'";
}
$installer->getConnection()->addColumn(
    $installer->getTable('goodahead_etm/eav_entity_type'),
    'entity_type_list_content',
    $columnDefinition);

/**
 * entity_type_item_content
 */
$columnComment = 'Entities List Item Template';
if ($this->isNewDdlModel()) {
    $columnDefinition = array(
        'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'    => null,
        'nullable'  => true,
        'comment'   => $columnComment,
    );
} else {
    $columnDefinition = "TEXT COMMENT '{$columnComment}'";
}
$installer->getConnection()->addColumn(
    $installer->getTable('goodahead_etm/eav_entity_type'),
    'entity_type_item_content',
    $columnDefinition);


$installer->endSetup();
