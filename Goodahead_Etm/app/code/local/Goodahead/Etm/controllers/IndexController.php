<?php

class Goodahead_Etm_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
        $entity = Mage::getModel('goodahead_etm/entity')->load($this->getRequest()->getParam('entity_id'));
        if ($entity->getId()) {
            $entityType = $entity->getEntityTypeInstance();
            Mage::register('goodahead_etm_entity', $entity);
            $this->getLayout()->getUpdate()
                ->addHandle('default')
                ->addHandle('goodahead_etm_entity')
                ->addHandle('ENTITY_TYPE_' . $entityType->getEntityTypeCode());
            $this->addActionLayoutHandles();
            if ($entityType->getEntityTypeRootTemplate()) {
                $this->getLayout()->helper('page/layout')->applyHandle($entityType->getEntityTypeRootTemplate());
            }

            $this->loadLayoutUpdates();
            $layoutUpdate = $entityType->getEntityTypeLayoutXml();
            $this->getLayout()->getUpdate()->addUpdate($layoutUpdate);
            $this->generateLayoutXml()->generateLayoutBlocks();

            if ($entityType->getEntityTypeRootTemplate()) {
                $this->getLayout()->helper('page/layout')
                    ->applyTemplate($entityType->getEntityTypeRootTemplate());
            }

            $this->renderLayout();
        } else {
            $this->_forward('no_route');
        }
    }

}
