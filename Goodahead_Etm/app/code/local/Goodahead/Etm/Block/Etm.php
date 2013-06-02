<?php
class Goodahead_Etm_Block_Etm extends Mage_Core_Block_Template
{
    public function _toHtml()
    {
        /*
         * @var $entity Goodahead_Etm_Model_Entity
         * @var $textProcessor Mage_Core_Model_Email_Template
         */
        $entity = Mage::registry('goodahead_etm_entity');
        $content = $entity->getEntityTypeInstance()->getEntityTypeContent();

        $textProcessor = Mage::getModel('core/email_template');
        $textProcessor->setTemplateText($content);
        return nl2br($textProcessor->getProcessedTemplate($entity->getData(), true));
    }
}