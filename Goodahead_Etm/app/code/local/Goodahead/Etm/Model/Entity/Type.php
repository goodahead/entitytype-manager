<?php

/**
 * Class Goodahead_Etm_Model_Entity_Type
 *
 * @method string getEntityTypeName() getEntityTypeName()
 */
class Goodahead_Etm_Model_Entity_Type extends Mage_Eav_Model_Entity_Type
{
    protected function _construct()
    {
        $this->_init('goodahead_etm/entity_type');
    }

    protected function _afterSave()
    {
        parent::_afterSave();
        if ($this->getCreateAttributeSet() === true) {
            $this->setCreateAttributeSet(false);
            $this->isObjectNew(false);
            $this->save();
        }
        return $this;
    }
}
