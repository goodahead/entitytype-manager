<?php

class Goodahead_Etm_Helper_Data extends Mage_Core_Helper_Abstract
{
    protected $_visibleAttributes   = null;

    /**
     * @var Goodahead_Etm_Model_Resource_Entity_Type_Collection
     */
    protected $_entityTypesCollection;

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


    public function getVisibleAttributes($entityTypeId)
    {
        $collection = Mage::getResourceModel('goodahead_etm/attribute_collection');
        $collection->setEntityType($entityTypeId);

        if (is_null($this->_visibleAttributes)) {
            $this->_visibleAttributes = array();

            foreach($collection as $attribute) {
                $this->_visibleAttributes[$attribute->getAttributeCode()] = $attribute->getAttributeName();
            }
        }

        return $this->_visibleAttributes;
    }

    public function getVisibleAttributesCollection($entityType)
    {
        $collection = Mage::getResourceModel('goodahead_etm/attribute_collection');
        $collection->setEntityType($entityType);
        return $collection;
    }
}
