<?php
/**
 * Goodahead Ltd.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.goodahead.com/LICENSE.txt
 *
 * @category   Goodahead
 * @package    Goodahead_EtmPro
 * @copyright  Copyright (c) 2014 Goodahead Ltd. (http://www.goodahead.com)
 * @license    http://www.goodahead.com/LICENSE.txt
 */

/* @var $installer Goodahead_Etm_Model_Resource_Entity_Setup */

$installer = $this;
$installer->startSetup();

$installer->getConnection()->addColumn(
    $installer->getTable('goodahead_etm/eav_attribute'),
    'is_html_allowed_on_front',
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        'comment'   => 'Is HTML Allowed On Front'
    )
);
$installer->getConnection()->addColumn(
    $installer->getTable('goodahead_etm/eav_attribute'),
    'is_wysiwyg_enabled',
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        'comment'   => 'Is WYSIWYG Enabled'
    )
);

/**
 * URL Rewrite related fields for entity types
 */

$installer->getConnection()->addColumn(
    $installer->getTable('goodahead_etm/eav_entity_type'),
    'entity_type_url_key',
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'    => 255,
        'nullable'  => true,
        'comment'   => 'Entity Type URL Key',
    ));
$installer->getConnection()->addColumn(
    $installer->getTable('goodahead_etm/eav_entity_type'),
    'entity_type_use_rewrite',
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => 0,
        'comment'   => 'Entity Type Uses URL Rewrite',
    ));


$installer->getConnection()->addColumn(
    $installer->getTable('core/url_rewrite'),
    'etm_entity_type_id',
    array(
         'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
         'unsigned'  => true,
         'nullable'  => true,
         'comment'   => 'Goodahead ETM Entity Type Id'
    ));
$installer->getConnection()->addColumn(
    $installer->getTable('core/url_rewrite'),
    'etm_entity_id',
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
        'unsigned'  => true,
        'nullable'  => true,
        'comment'   => 'Goodahead ETM Entity Id'
    ));

$installer->getConnection()->addForeignKey(
    $installer->getFkName(
        'core/url_rewrite',
        'etm_entity_type_id',
        'goodahead_etm/eav_entity_type',
        'entity_type_id'),
    $installer->getTable('core/url_rewrite'),
    'etm_entity_type_id',
    $installer->getTable('goodahead_etm/eav_entity_type'),
    'entity_type_id');
$installer->getConnection()->addForeignKey(
    $installer->getFkName(
        'core/url_rewrite',
        'etm_entity_id',
        'goodahead_etm/entity',
        'entity_id'),
    $installer->getTable('core/url_rewrite'),
    'etm_entity_id',
    $installer->getTable('goodahead_etm/entity'),
    'entity_id');

$installer->endSetup();