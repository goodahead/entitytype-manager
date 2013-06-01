<?php

/** @var $installer Goodahead_Etm_Model_Entity_Setup */
$installer = $this;
$installer->startSetup();

$installer->run("
DROP TABLE IF EXISTS `{$this->getTable('goodahead_etm/eav')}`;
DROP TABLE IF EXISTS `{$this->getTable('goodahead_etm/eav')}_datetime`;
DROP TABLE IF EXISTS `{$this->getTable('goodahead_etm/eav')}_decimal`;
DROP TABLE IF EXISTS `{$this->getTable('goodahead_etm/eav')}_int`;
DROP TABLE IF EXISTS `{$this->getTable('goodahead_etm/eav')}_text`;
DROP TABLE IF EXISTS `{$this->getTable('goodahead_etm/eav')}_varchar`;
DROP TABLE IF EXISTS `{$this->getTable('goodahead_etm/eav')}_char`;
");

$installer->createEntityTables('goodahead_etm/eav');

$installer->endSetup();
