DELETE FROM `core_resource` WHERE code = 'goodahead_etm_setup';
ALTER TABLE catalog_eav_attribute DROP FOREIGN KEY FK_4C54C2F21602E7FD345B67E0BDCF6658;
ALTER TABLE goodahead_etm_eav_entity_type DROP FOREIGN KEY FK_C8CAB03607BAABD021C66B39D864549C;
ALTER TABLE catalog_eav_attribute DROP `goodahead_etm_entity_type_id`;

DROP TABLE IF EXISTS `goodahead_etm_eav_datetime`;
DROP TABLE IF EXISTS `goodahead_etm_eav_decimal`;
DROP TABLE IF EXISTS `goodahead_etm_eav_int`;
DROP TABLE IF EXISTS `goodahead_etm_eav_text`;
DROP TABLE IF EXISTS `goodahead_etm_eav_varchar`;
DROP TABLE IF EXISTS `goodahead_etm_eav_char`;
DROP TABLE IF EXISTS `goodahead_etm_eav_entity_type`;
DROP TABLE IF EXISTS `goodahead_etm_eav`;

DROP TABLE IF EXISTS `goodahead_etm_entity_datetime`;
DROP TABLE IF EXISTS `goodahead_etm_entity_decimal`;
DROP TABLE IF EXISTS `goodahead_etm_entity_int`;
DROP TABLE IF EXISTS `goodahead_etm_entity_text`;
DROP TABLE IF EXISTS `goodahead_etm_entity_varchar`;
DROP TABLE IF EXISTS `goodahead_etm_entity_char`;
DROP TABLE IF EXISTS `goodahead_etm_entity_entity_type`;
DROP TABLE IF EXISTS `goodahead_etm_entity`;
DROP TABLE IF EXISTS `goodahead_etm_eav_attribute`;

DELETE FROM `eav_entity_type` WHERE `entity_model` = 'goodahead_etm/entity';
