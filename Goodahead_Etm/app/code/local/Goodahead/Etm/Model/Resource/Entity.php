<?php
class Goodahead_Etm_Model_Resource_Entity extends Mage_Eav_Model_Entity_Abstract
{
    /**
     * Initialize resource
     */
    public function __construct($entityTypeCode)
    {
        parent::__construct();
        $this->setType($entityTypeCode['entity_type_id']);
        $this->setConnection('eav_read', 'eav_write');
    }
}
