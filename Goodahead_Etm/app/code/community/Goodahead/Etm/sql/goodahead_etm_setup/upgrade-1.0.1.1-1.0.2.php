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
$installer->getConnection()->addColumn(
    $installer->getTable('goodahead_etm/eav_entity_type'),
    'entity_type_title',
    array(
         'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
         'length'    => 255,
         'nullable'  => true,
         'comment'   => 'Entity Page Title',
    ));

/**
 * entity_type_list_title
 */
$installer->getConnection()->addColumn(
    $installer->getTable('goodahead_etm/eav_entity_type'),
    'entity_type_list_title',
    array(
         'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
         'length'    => 255,
         'nullable'  => true,
         'comment'   => 'Entity List Page Title',
    ));

/**
 * entity_type_list_root_template
 */
$installer->getConnection()->addColumn(
    $installer->getTable('goodahead_etm/eav_entity_type'),
    'entity_type_list_root_template',
    array(
         'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
         'length'    => 128,
         'nullable'  => true,
         'comment'   => 'Entities List Root Template',
    ));

/**
 * entity_type_list_layout_xml
 */
$installer->getConnection()->addColumn(
    $installer->getTable('goodahead_etm/eav_entity_type'),
    'entity_type_list_layout_xml',
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'    => null,
        'nullable'  => true,
        'comment'   => 'Entities List Layout XML',
    ));

/**
 * entity_type_list_content
 */
$installer->getConnection()->addColumn(
    $installer->getTable('goodahead_etm/eav_entity_type'),
    'entity_type_list_content',
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'    => null,
        'nullable'  => true,
        'comment'   => 'Entities List Template',
    ));

/**
 * entity_type_item_content
 */
$installer->getConnection()->addColumn(
    $installer->getTable('goodahead_etm/eav_entity_type'),
    'entity_type_item_content',
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'    => null,
        'nullable'  => true,
        'comment'   => 'Entities List Item Template',
    ));

$installer->endSetup();
