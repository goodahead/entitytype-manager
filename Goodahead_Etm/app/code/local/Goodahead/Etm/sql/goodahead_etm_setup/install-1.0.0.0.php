<?php

/** @var $installer Goodahead_Etm_Model_Entity_Setup */
$installer = $this;
$installer->startSetup();

$installer->createEntityTables('goodahead_etm/eav');

$installer->endSetup();
