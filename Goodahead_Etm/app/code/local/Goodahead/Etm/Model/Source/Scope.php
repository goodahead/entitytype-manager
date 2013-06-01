<?php

class Goodahead_Etm_Model_Source_Scope
{
    /**
     * Return scope filter as options array
     *
     * @return array
     */
    public function toOptionArray()
    {
        $optionsArray = array();

        foreach ($this->toArray() as $value => $label) {
            $optionsArray[] = array('value' => $value, 'label' => $label);
        }

        return $optionsArray;
    }

    /**
     * Return scope filter as array
     *
     * @return array
     */
    public function toArray()
    {
        return array(
            Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE   => Mage::helper('goodahead_etm')->__('Store View'),
            Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE => Mage::helper('goodahead_etm')->__('Website'),
            Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL  => Mage::helper('goodahead_etm')->__('Global'),
        );
    }
}
