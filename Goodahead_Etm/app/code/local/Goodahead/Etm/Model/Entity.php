<?php

class Goodahead_Etm_Model_Entity extends Mage_Eav_Model_Entity
{
    const DEFAULT_ENTITY_MODEL      = 'eav/entity';
    const DEFAULT_ATTRIBUTE_MODEL   = 'eav/entity_attribute';
    const DEFAULT_BACKEND_MODEL     = 'eav/entity_attribute_backend_default';
    const DEFAULT_FRONTEND_MODEL    = 'eav/entity_attribute_frontend_default';
    const DEFAULT_SOURCE_MODEL      = 'eav/entity_attribute_source_config';

    const DEFAULT_ENTITY_TABLE      = 'eav/entity';
    const DEFAULT_ENTITY_ID_FIELD   = 'entity_id';
    const DEFAULT_VALUE_TABLE_PREFIX= 'eav/entity_attribute';

    /**
     * Resource initialization
     */
    public function __construct()
    {
        $resource = Mage::getSingleton('core/resource');
        $this->setConnection($resource->getConnection('eav_read'));
    }
}
