<?php

class Goodahead_Etm_Model_Source_Entity_Type
{
    public function toOptionArray($addEmpty = false)
    {
        $collection = Mage::getModel('goodahead_etm/entity_type')->getCollection();
        $result = array();
        if ($addEmpty) {
            $result[] = array(
                'value' => '',
                'label' => '',
            );
        }
        $result = array_merge($result, $collection->load()->toOptionArray());
        return $result;
    }
}