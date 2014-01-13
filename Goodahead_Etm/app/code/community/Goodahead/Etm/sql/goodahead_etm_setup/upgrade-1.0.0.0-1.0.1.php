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

$select = $installer->getConnection()->select()
    ->from(
        array('goodahead_etm_attribute' => $installer->getTable('goodahead_etm/eav_attribute')),
        array(
            'frontend_label' => 'attribute_name',
        )
    )
    ->where('main.attribute_id = goodahead_etm_attribute.attribute_id');

$updateQuery = $installer->getConnection()->updateFromSelect(
    $select,
    array('main' => $installer->getTable('eav/attribute'))
);

$installer->getConnection()->query($updateQuery);

$installer->getConnection()->dropColumn($installer->getTable('goodahead_etm/eav_attribute'), 'attribute_name');

$installer->getConnection()->addColumn($installer->getTable('goodahead_etm/eav_attribute'), 'sort_order', 'INT(4) UNSIGNED NOT NULL DEFAULT 0');

$select = $installer->getConnection()->select();
$select->from(array(
        'main_table' => $installer->getTable('eav/entity_type')
    ), array(
        'entity_type_id',
        'entity_type_code',
    )
);

$select->joinInner(array(
        'g' => $installer->getTable('goodahead_etm/eav_entity_type')
    ),
    'main_table.entity_type_id = g.entity_type_id',
    array()
);

$items = $installer->getConnection()->fetchAll($select);
if ($items && is_array($items)) {
    foreach ($items as $item) {
        $installer->getConnection()->update(
            $installer->getTable('eav/entity_type'),
            array(
                'entity_model'    => sprintf('goodahead_etm/custom_%s_entity', $item['entity_type_code']),
                'attribute_model' => 'goodahead_etm/entity_attribute',
            ),
            $installer->getConnection()->quoteInto('entity_type_id = ?', $item['entity_type_id'])
        );
    }
}

$tableTypes = array(
    'int',
    'varchar',
    'char',
    'text',
    'decimal',
    'datetime',
);

foreach ($tableTypes as $type) {
    $installer->getConnection()->addIndex(
        $installer->getTable('goodahead_etm/entity') . '_' . $type,
        $installer->getIdxName($installer->getTable('goodahead_etm/entity') . '_' . $type, array(
            'entity_type_id',
            'attribute_id',
            'store_id',
            'entity_id'
        )),
        array(
            'entity_type_id',
            'attribute_id',
            'store_id',
            'entity_id'
        ),
        Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
    );
}

$installer->endSetup();
