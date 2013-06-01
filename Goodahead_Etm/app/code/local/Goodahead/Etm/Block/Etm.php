<?php
class Goodahead_Etm_Block_Etm extends Mage_Core_Block_Template
{
    protected $_entityTypeObject = null;

    protected $_entityTypeCode = null;

    public function getEntityTypeObject()
    {
        if ($this->_entityTypeObject === null) {
            if ($this->_entityTypeCode !== null) {
                $this->_entityTypeObject = Mage::getModel('goodahead_etm/entity_type')
                    ->load($this->_entityTypeCode, 'entity_type_code');
            }
        }
        return $this->_entityTypeObject;
    }

    public function setEntityTypeCode($entityTypeCode)
    {
        $this->_entityTypeCode = $entityTypeCode;
    }
}