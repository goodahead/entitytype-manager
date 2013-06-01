<?php

/**
 * Class Goodahead_Etm_Model_Attribute
 *
 * @method Goodahead_Etm_Model_Resource_Attribute getResource() getResource()
 */
class Goodahead_Etm_Model_Attribute extends Mage_Eav_Model_Entity_Attribute
{
    public function _construct()
    {
        $this->_init('goodahead_etm/attribute');
    }

    /**
     * Delete attributes with given ids
     *
     * @param array $attributeIds
     * @return $this
     */
    public function deleteAttributes(array $attributeIds)
    {
        $this->getResource()->deleteAttributes($attributeIds);
    }
}
