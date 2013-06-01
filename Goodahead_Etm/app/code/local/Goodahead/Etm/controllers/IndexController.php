<?php

class Goodahead_Etm_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
        $entityType = Mage::getModel('goodahead_etm/entity_type');
        $entityType->load(33);
        var_dump($entityType->getId());
        return;
        $model = Mage::getModel('goodahead_etm/entity_type');
        $model->load(1);
        var_dump($model->getId());
        return;
        $collection = Mage::getModel('goodahead_etm/entity_type')->getCollection();
        $collection->load();
        echo get_class($collection);
        echo count($collection);
    }
}
