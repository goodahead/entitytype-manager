<?php

class Goodahead_Etm_Model_Source_Entity_Type
{
    public function toOptionArray()
    {
        $collection = Mage::getResourceModel('goodahead_etm/entity_type_collection');
        return $collection->load()->toOptionArray('entity_type_id', 'entity_type_name');
    }
}