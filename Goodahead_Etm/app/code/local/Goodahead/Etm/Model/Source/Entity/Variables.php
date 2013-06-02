<?php

class Goodahead_Etm_Model_Source_Entity_Variables
{
    protected function _getEtmHelper()
    {
        return Mage::helper('goodahead_etm');
    }


    /**
     * Assoc array of configuration variables
     *
     * @var array
     */
    protected $_entityVariables = array();

    /**
     * Constructor
     *
     */
    public function __construct()
    {
        $this->_entityVariables = array();

        //$visibleAttribute = $this->_getEtmHelper()->getVisibleAttributes($entityTypeId);

        /*array(
            'value' => Mage_Core_Model_Url::XML_PATH_UNSECURE_URL,
            'label' => Mage::helper('core')->__('Base Unsecure URL')
        );*/

    }

    /**
     * Retrieve variables option array
     *
     * @param boolean $withGroup
     * @return array
     */
    public function toOptionArray($withGroup = false)
    {
        $optionArray = array();
        foreach ($this->_entityVariables as $variable) {
            $optionArray[] = array(
                'value' => '{{var ' . $variable['value'] . '}}',
                'label' => Mage::helper('goodahead_etm')->__('%s', $variable['label'])
            );
        }
        if ($withGroup && $optionArray) {
            $optionArray = array(
                'label' => Mage::helper('core')->__('Entity Attributes'),
                'value' => $optionArray
            );
        }
        return $optionArray;
    }
}
