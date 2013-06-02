<?php

class Goodahead_Etm_Model_Source_Entity extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    /**
     * Default values for option cache
     *
     * @var array
     */
    protected $_optionsDefault = array();

    /**
     * Retrieve Full Option values array
     *
     * @param bool $withEmpty       Add empty option to array
     * @param bool $defaultValues
     * @return array
     */
    public function getAllOptions($withEmpty = true, $defaultValues = false)
    {
        $result = array();

        $attribute = $this->getAttribute();
        $entityTypeId = $attribute->getData('goodahead_etm_entity_type_id');

        /** @var Goodahead_Etm_Model_Entity_Type $entityType */
        $entityType = Mage::getModel('goodahead_etm/entity_type')->load($entityTypeId);
        if ($entityType->getId()) {
            /** @var Goodahead_Etm_Model_Resource_Entity_Collection $collection */
            $collection = Mage::getModel('goodahead_etm/entity')->getCollection($entityType->getEntityTypeCode());
            $collection->setEntityType($entityType);
            if ($withEmpty) {
                $result[] = array(
                    'value' => '',
                    'label' => '',
                );
            }
            $result = array_merge($result, $collection->toOptionArray());

        }
        return $result;
    }
}
