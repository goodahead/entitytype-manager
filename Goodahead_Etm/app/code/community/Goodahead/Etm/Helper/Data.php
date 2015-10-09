<?php
/**
 * This file is part of Goodahead_Etm extension
 *
 * This extension allows to create and manage custom EAV entity types
 * and EAV entities
 *
 * Copyright (C) 2014 Goodahead Ltd. (http://www.goodahead.com)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * and GNU General Public License along with this program.
 * If not, see <http://www.gnu.org/licenses/>.
 *
 * @category   Goodahead
 * @package    Goodahead_Etm
 * @copyright  Copyright (c) 2014 Goodahead Ltd. (http://www.goodahead.com)
 * @license    http://www.gnu.org/licenses/lgpl-3.0-standalone.html GNU Lesser General Public License
 */

class Goodahead_Etm_Helper_Data extends Mage_Core_Helper_Abstract
{
    const XML_PATH_INPUT_TYPE_MAP   = 'global/goodahead/etm/entity/attribute/input_type_map';

    protected $_visibleAttributes   = array();

    protected $_entityTypesCode = array();

    protected $_entityTypes = array();

    /**
     * @var Goodahead_Etm_Model_Resource_Entity_Type_Collection
     */
    protected $_entityTypesCollection;

    protected $_inputTypesMap;

    /**
     * Initialize helper instance
     *
     * @param array $args
     */
    public function __construct(array $args = array())
    {
        if (@class_exists('Mage_Shell_Abstract')) {
            $this->runEtmAutoloader();
        }
    }

    public function runEtmAutoloader()
    {
        Goodahead_Etm_Processor_Autoload::register();
        return $this;
    }

    protected function _getEntityTypesCollection()
    {
        if (!isset($this->_entityTypesCollection)) {
            $this->_entityTypesCollection = Mage::getModel('goodahead_etm/entity_type')
                ->getCollection()
                ->setOrder('entity_type_name');
        }
        return $this->_entityTypesCollection;
    }

    public function updateMenu(Varien_Simplexml_Element $node)
    {
        $entityTypesCollection = $this->_getEntityTypesCollection();
        if ($entityTypesCollection->getSize()) {
            $children = $node->addChild('children');
            $index = 0;
            foreach ($entityTypesCollection as $entityType) {
                $index += 10;
                $menuItem = $children->addChild(sprintf('goodahead_etm_entity_type_%d', $entityType->getId()));
                $menuItem->addChild('title',
                    strlen($entityType->getEntityTypeName())
                        ? $entityType->getEntityTypeName()
                        : $entityType->getEntityTypeCode());
                $menuItem->addChild('sort_order', $index);
                $menuItem->addChild('action', sprintf((string)$node->base_link, $entityType->getId()));
            }
        } else {
            $nodeName = $node->getName();
            unset($node->getParent()->$nodeName);
        }
    }

    /**
     * Returns associative array of visible attributes for Entity Type
     *
     * @param  int|Goodahead_Etm_Model_Entity_Type $entityType
     * @return array Entity Type visible attributes array
     */
    public function getVisibleAttributes($entityType)
    {
        // TODO: Use eav/config instead
        if ($entityType instanceof Goodahead_Etm_Model_Entity_Type) {
            $entityTypeId = $entityType->getId();
        } else {
            $entityTypeId = $entityType;
        }
        if (!array_key_exists($entityTypeId, $this->_visibleAttributes)) {
            $collection = $this->getVisibleAttributesCollection($entityType);

            $this->_visibleAttributes[$entityTypeId] = array();

            foreach ($collection as $attribute) {
                $this->_visibleAttributes[$entityTypeId][$attribute->getAttributeCode()] = $attribute;
            }
        }

        return $this->_visibleAttributes[$entityTypeId];
    }

    public function getVisibleAttributesCollection($entityType)
    {
        // TODO: Use eav/config instead
        /** @var Goodahead_Etm_Model_Resource_Entity_Attribute_Collection $collection */
        $collection = Mage::getResourceModel('goodahead_etm/entity_attribute_collection');
        $collection->setEntityTypeFilter($entityType);
        $collection->addFieldToFilter('is_visible', 1);
        $collection->setOrder('sort_order', Zend_Db_Select::SQL_ASC);

        return $collection;
    }

    protected function _initInputTypesMap()
    {
        if (!isset($this->_inputTypesMap)) {
            $node = Mage::getConfig()->getNode(self::XML_PATH_INPUT_TYPE_MAP);
            $this->_inputTypesMap = $node->asArray();
        }
        return $this->_inputTypesMap;
    }

    public function getAttributeSourceModelByInputType($inputType)
    {
        $this->_initInputTypesMap();
        if (!empty($this->_inputTypesMap[$inputType]['source_model'])) {
            return $this->_inputTypesMap[$inputType]['source_model'];
        }
        return null;
    }

    public function getAttributeBackendModelByInputType($inputType)
    {
        $this->_initInputTypesMap();
        if (!empty($this->_inputTypesMap[$inputType]['backend_model'])) {
            return $this->_inputTypesMap[$inputType]['backend_model'];
        }
        return null;
    }

    public function getAttributeFrontendModelByInputType($inputType)
    {
        $this->_initInputTypesMap();
        if (!empty($this->_inputTypesMap[$inputType]['frontend_model'])) {
            return $this->_inputTypesMap[$inputType]['frontend_model'];
        }
        return null;
    }

    /**
     * @param int $entityTypeId
     * @return string|null
     */
    public function getEntityTypeCodeById($entityTypeId)
    {
        if (!array_key_exists($entityTypeId, $this->_entityTypesCode)) {

            /** @var Goodahead_Etm_Model_Entity_Type $entityType */
            $entityType = Mage::getModel('goodahead_etm/entity_type');
            $entityType->load($entityTypeId);

            $this->_entityTypesCode[$entityTypeId] = $entityType->getEntityTypeCode();
        }

        return $this->_entityTypesCode[$entityTypeId];
    }

    /**
     * @param int $entityId
     * @return bool|int
     * @throws Goodahead_Etm_Exception
     */
    public function getEntityTypeIdByEntityId($entityId)
    {
        /** @var Goodahead_Etm_Model_Resource_Entity_Type $resource */
        $resource = Mage::getResourceModel('goodahead_etm/entity_type');
        $entityTypeId = $resource->getEntityTypeIdByEntityId($entityId);

        if (!isset($entityTypeId)) {
            throw new Goodahead_Etm_Exception(
                Mage::helper('goodahead_etm')->__('Entity type for this entity not found')
            );
        }

        return $entityTypeId;
    }

    /**
     * @param int $entityTypeId
     * @return Goodahead_Etm_Model_Entity_Type
     * @throws Goodahead_Etm_Exception
     */
    protected function _getEntityTypeByEntityTypeId($entityTypeId)
    {
        if (!array_key_exists($entityTypeId, $this->_entityTypes)) {
            /** @var Goodahead_Etm_Model_Entity_Type $entityType */
            $entityType = Mage::getModel('goodahead_etm/entity_type');
            $entityType->load($entityTypeId);

            if (!$entityType->getId()) {
                throw new Goodahead_Etm_Exception(
                    Mage::helper('goodahead_etm')->__('Entity type not found')
                );
            }

            $this->_entityTypes[$entityType->getId()] = $entityType;
        }

        return $this->_entityTypes[$entityTypeId];
    }

    /**
     * @param int $entityId
     * @return string
     * @throws Goodahead_Etm_Exception
     */
    public function getEntityTypeCodeByEntityId($entityId)
    {
        return $this->getEntityTypeByEntityId($entityId)->getEntityTypeCode();
    }

    /**
     * @param int $entityId
     * @return Goodahead_Etm_Model_Entity_Type
     * @throws Goodahead_Etm_Exception
     */
    public function getEntityTypeByEntityId($entityId)
    {
        return $this->_getEntityTypeByEntityTypeId($this->getEntityTypeIdByEntityId($entityId));
    }

    /**
     * @param int $entityId
     * @param int $storeId
     * @return Goodahead_Etm_Model_Entity
     */
    public function getEntityByEntityId($entityId, $storeId = 0)
    {
        $entity = Mage::getModel(sprintf(
            'goodahead_etm/custom_%s_entity',
            $this->getEntityTypeCodeByEntityId($entityId)
        ))
            ->setStoreId($storeId)
            ->load($entityId);

        return $entity;
    }

    /**
     * @param int|string|Goodahead_Etm_Model_Entity_Type $type
     * @return Goodahead_Etm_Model_Resource_Entity_Collection
     */
    public function getEntityCollectionByEntityType($type)
    {
        if ($type instanceof Goodahead_Etm_Model_Entity_Type) {
            $entityTypeCode = $type->getEntityTypeCode();
        } elseif (is_numeric($type)) {
            $entityTypeCode = $this->getEntityTypeCodeById($type);
        } else {
            $entityTypeCode = $type;
        }

        return Mage::getSingleton(sprintf('goodahead_etm/custom_%s_entity', $entityTypeCode))->getCollection();
    }

    /**
     * @param int|string|Goodahead_Etm_Model_Entity_Type $type
     * @return Goodahead_Etm_Model_Entity
     */
    public function getEntityModelByEntityType($type)
    {
        if ($type instanceof Goodahead_Etm_Model_Entity_Type) {
            $entityTypeCode = $type->getEntityTypeCode();
        } elseif (is_numeric($type)) {
            $entityTypeCode = $this->getEntityTypeCodeById($type);
        } else {
            $entityTypeCode = $type;
        }

        return Mage::getModel(sprintf('goodahead_etm/custom_%s_entity', $entityTypeCode));
    }

    /**
     * Return Options Hash for grid column from attribute source model
     *
     * @param Mage_Eav_Model_Entity_Attribute_Source_Interface $source
     * @param bool                                             $withEmpty
     * @param bool                                             $defaultValues
     *
     * @return array
     */
    public function getOptionsHash(
        Mage_Eav_Model_Entity_Attribute_Source_Interface $source,
        $withEmpty = true,
        $defaultValues = false
    )
    {
        $options = array();
        foreach ($source->getAllOptions($withEmpty, $defaultValues) as $valueArray) {
            $options[$valueArray['value']] = $valueArray['label'];
        }
        return $options;
    }

}
