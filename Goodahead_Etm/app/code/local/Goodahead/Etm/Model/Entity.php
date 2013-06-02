<?php

class Goodahead_Etm_Model_Entity extends Mage_Core_Model_Abstract
{
    protected $_entityTypeId = null;
    protected $_entityTypeInstance = null;

    protected $_storeId = null;

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
        $collection->setStoreId($this->getStoreId());
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
        $_entityTypeId = $this->_getEntityTypeId($id, $field);
        if ($_entityTypeId !== $this->_entityTypeId) {
            $this->_entityTypeId = $_entityTypeId;
            $this->_entityTypeInstance = null;
        }
        return parent::load($id, $field);
    }

    protected function _getEntityTypeId($id = null, $field = null)
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

    /**
     * @return Goodahead_Etm_Model_Entity_Type
     */
    public function getEntityTypeInstance()
    {
        if (!isset($this->_entityTypeInstance) && $this->_getEntityTypeId()) {
            $this->_entityTypeInstance = Mage::getModel('goodahead_etm/entity_type')->load($this->_getEntityTypeId());
        }
        return $this->_entityTypeInstance;
    }

    public function setStoreId($storeId)
    {
        $this->_storeId = $storeId;
    }

    /**
     * Returns store id with which attribute values loaded
     *
     * @return int
     */
    public function getStoreId()
    {
        if ($this->_storeId !== null) {
            return $this->_storeId;
        }
        return Mage::app()->getStore()->getId();
    }

    public function getEntityLabel()
    {
        $typeInstance = $this->getEntityTypeInstance();
        $defaultAttributeId = $typeInstance->getDefaultAttributeId();
        $defaultAttribute = Mage::getModel('goodahead_etm/attribute')->load($defaultAttributeId);
        if (!$defaultAttribute->getId()) {
            return $this->getId();
        }
        return $this->getData($defaultAttribute->getAttributeCode());
    }
}