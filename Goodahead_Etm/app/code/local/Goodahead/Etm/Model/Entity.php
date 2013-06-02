<?php

class Goodahead_Etm_Model_Entity extends Mage_Core_Model_Abstract
{
    protected $_entityTypeId = null;

    protected function _construct()
    {
        $this->_init('goodahead_etm/entity');
    }

    public function getCollection($entityTypeId = null)
    {
        return $this->getResourceCollection($entityTypeId);
    }

    public function getResourceCollection($entityTypeId = null)
    {
        if (empty($this->_resourceCollectionName)) {
            Mage::throwException(Mage::helper('core')->__('Model collection resource name is not defined.'));
        }
        $collection = Mage::getResourceModel($this->_resourceCollectionName, $this->_getResource(array('entity_type_id' => $entityTypeId)));
        $collection->setEntityType($entityTypeId);
        return $collection;
    }

    /**
     * Get resource instance
     *
     * @return Mage_Core_Model_Mysql4_Abstract
     */
    protected function _getResource($entityTypeId = array())
    {
        if (empty($this->_resourceName)) {
            Mage::throwException(Mage::helper('core')->__('Resource is not set.'));
        }

        if ($this->_entityTypeId !== null) {
            $entityTypeId = array('entity_type_id' => $this->_entityTypeId);
        }

        return Mage::getResourceSingleton($this->_resourceName, $entityTypeId);
    }

    public function load($id, $field = null)
    {
        $this->_entityTypeId = $this->_getEntityTypeId($id, $field);
        return parent::load($id, $field);
    }

    protected function _getEntityTypeId($id, $field = null)
    {
        if ($id !== null) {
            $resource = Mage::getSingleton('core/resource');
            /** @var $connection Varien_Db_Adapter_Interface */
            $connection = $resource->getConnection('core_read');
            $select = $connection->select();
            $select->from($resource->getTableName('goodahead_etm/entity'));
            $field = $field ? $field : 'entity_id';
            $select->where($field . ' = ?', $id);
            $stmt = $select->query();
            $data = $stmt->fetch();
            return isset($data['entity_type_id']) ? (int)$data['entity_type_id'] : null;
        } else {
            return $this->getEntityTypeId();
        }
    }
}
