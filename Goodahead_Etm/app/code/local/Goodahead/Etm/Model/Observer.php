<?php


class Goodahead_Etm_Model_Observer {

    public function renderMenu($observer)
    {
        /** @var $menu Varien_Simplexml_Element */
        $menu = Mage::getSingleton('admin/config')->getAdminhtmlConfig()->getNode('menu');
        foreach ($menu->xpath('//*[@update]') as $node) {
            $helperName = explode('/', (string)$node->getAttribute('update'));
            $helperMethod = array_pop($helperName);
            $helperName = implode('/', $helperName);
            $helper = Mage::helper($helperName);
            if ($helper && method_exists($helper, $helperMethod)) {
                $helper->$helperMethod($node);
            }
        }
    }

    public function updateMenuBlockCacheId($observer)
    {
        $block = $observer->getEvent()->getBlock();
        if ($block instanceof Mage_Adminhtml_Block_Page_Menu) {
            /** @var $menu Varien_Simplexml_Element */
            $menu = Mage::getSingleton('admin/config')->getAdminhtmlConfig()->getNode('menu');
            $additionalCacheKeyInfo = $block->getAdditionalCacheKeyInfo();
            if (!is_array($additionalCacheKeyInfo)) {
                $additionalCacheKeyInfo = array();
            }
            $additionalCacheKeyInfo['goodahead_etm_cache_key_info'] = md5($menu->asXML());
            $block->setAdditionalCacheKeyInfo($additionalCacheKeyInfo);
        }
    }

}