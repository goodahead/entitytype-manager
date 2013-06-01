<?php

class Goodahead_Etm_Helper_Data extends Mage_Core_Helper_Abstract
{
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
                $menuItem->addChild('title', $entityType->getEntityTypeCode());
                $menuItem->addChild('sort_order', $index);
                $menuItem->addChild('action', sprintf((string)$node->base_link, $entityType->getId()));
            }
        } else {
            $nodeName = $node->getName();
            unset($node->getParent()->$nodeName);
        }
    }
}
