<?php
class Goodahead_Etm_Model_Resource_Entity_Collection extends Mage_Eav_Model_Entity_Collection_Abstract
{
    protected $_entityType  = null;
    protected $_storeId     = null;

    protected function _getEtmHelper()
    {
        return Mage::helper('goodahead_etm');
    }

    /**
     * @param null $entityType
     */
    public function setEntityType($entityType)
    {
        $this->_entityType = $entityType;
    }

    /**
     * @return null
     */
    public function getEntityType()
    {
        return $this->_entityType;
    }

    /**
     * @param null $storeId
     */
    public function setStoreId($storeId)
    {
        $this->_storeId = $storeId;
    }

    /**
     * @return null
     */
    public function getStoreId()
    {
        return $this->_storeId;
    }

    protected function _construct()
    {
        $this->_init('goodahead_etm/entity');
    }

    public function joinVisibleAttributes($entityTypeId)
    {
        $visibleAttributes = $this->_getEtmHelper()->getVisibleAttributes($entityTypeId);

        $this->addAttributeToSelect(array_keys($visibleAttributes));

        return $this;
    }


    public function _loadAttributes($printQuery = false, $logQuery = false)
    {
        $this->_storeId = 1;
        if (empty($this->_items) || empty($this->_itemsById) || empty($this->_selectAttributes)) {
            return $this;
        }

        $entity = $this->getEntity();

        $tableAttributes = array();
        $attributeTypes  = array();
        foreach ($this->_selectAttributes as $attributeCode => $attributeId) {
            if (!$attributeId) {
                continue;
            }
            $attribute = Mage::getSingleton('eav/config')->getCollectionAttribute($entity->getType(), $attributeCode);
            if ($attribute && !$attribute->isStatic()) {
                $tableAttributes[$attribute->getBackendTable()][] = $attributeId;
                if (!isset($attributeTypes[$attribute->getBackendTable()])) {
                    $attributeTypes[$attribute->getBackendTable()] = $attribute->getBackendType();
                }
            }
        }

        $selects = array();
        foreach ($tableAttributes as $table=>$attributes) {
            $select = $this->_getLoadAttributesSelect($table, $attributes);
            $selects[$attributeTypes[$table]][] = $this->_addLoadAttributesSelectValues(
                $select,
                $table,
                $attributeTypes[$table]
            );
        }
        $selectGroups = Mage::getResourceHelper('eav')->getLoadAttributesSelectGroups($selects);
        foreach ($selectGroups as $selects) {
            if (!empty($selects)) {
                try {
                    $select = implode(' UNION ALL ', $selects);
                    $values = $this->getConnection()->fetchAll($select);
                } catch (Exception $e) {
                    Mage::printException($e, $select);
                    $this->printLogQuery(true, true, $select);
                    throw $e;
                }

                if ($this->getStoreId()) {
                    $values = $this->_compactValues($values);
                }
                foreach ($values as $value) {
                    $this->_setItemAttributeValue($value);
                }
            }
        }

        return $this;
    }

    protected function _getLoadAttributesSelect($table, $attributeIds = array())
    {
        $select = parent::_getLoadAttributesSelect($table, $attributeIds);
        if ($this->getStoreId()) {
            $select->where('store_id IN (?)', array(0, $this->getStoreId()));
        } else {
            $select->where('store_id = 0');
        }
        return $select;
    }

    protected function _addLoadAttributesSelectValues($select, $table, $type)
    {
        parent::_addLoadAttributesSelectValues($select, $table, $type);
        $select->columns(array(
            'store_id' => $table. '.store_id',
        ));
        return $select;
    }

    protected function _compactValues($values)
    {
        $result = array();
        foreach ($values as $value) {
            $key = $value['entity_id'] . '-' . $value['attribute_id'];
            $result[$key]['entity_id'] = $value['entity_id'];
            $result[$key]['attribute_id'] = $value['attribute_id'];
            if ($value['store_id']) {
                $result[$key]['value'] = $value['value'];
            } else {
                $result[$key]['default_value'] = $value['value'];
            }
        }
        return $result;
    }

    protected function _setItemAttributeValue($valueInfo)
    {
        $entityIdField  = $this->getEntity()->getEntityIdField();
        $entityId       = $valueInfo[$entityIdField];
        if (!isset($this->_itemsById[$entityId])) {
            throw Mage::exception('Mage_Eav',
                Mage::helper('eav')->__('Data integrity: No header row found for attribute')
            );
        }
        $attributeCode = array_search($valueInfo['attribute_id'], $this->_selectAttributes);
        if (!$attributeCode) {
            $attribute = Mage::getSingleton('eav/config')->getCollectionAttribute(
                $this->getEntity()->getType(),
                $valueInfo['attribute_id']
            );
            $attributeCode = $attribute->getAttributeCode();
        }

        foreach ($this->_itemsById[$entityId] as $object) {
            $object->setData($attributeCode, array_key_exists('value', $valueInfo) ? $valueInfo['value'] : $valueInfo['default_value']);
        }

        return $this;
    }


}
