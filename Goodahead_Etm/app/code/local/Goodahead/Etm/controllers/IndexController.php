<?php

class Goodahead_Etm_IndexController extends Mage_Core_Controller_Front_Action
{
    public function displayAction()
    {
        $entityTypeCode = $this->getRequest()->getParam('code');
        $this->loadLayout()->getLayout()->getBlock('etm')
            ->setEntityTypeCode($entityTypeCode);
        $this->renderLayout();
    }
}
