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

class Goodahead_Etm_Block_Entity_list extends Mage_Core_Block_Template
{
    /**
     * Default toolbar block name
     *
     * @var string
     */
    protected $_defaultPagerBlock = 'core/html_pager';

    /**
     * Entity Collection
     *
     * @var Mage_Eav_Model_Entity_Collection_Abstract
     */
    protected $_entityCollection;

    /**
     * Template Filter
     *
     * @var Goodahead_Etm_Model_Template_Filter
     */
    protected $_templateFilter;

    /**
     * Pager Block
     *
     * @var Mage_Page_Block_Html_Pager
     */
    protected $_pagerBlock;


    protected function _construct()
    {
        parent::_construct();
        $this->setPagerBlockName('entity_list_pager');
    }

    /**
     * Get pager block
     *
     * @return Mage_Page_Block_Html_Pager
     */
    public function getPagerBlock()
    {
        if (!isset($this->_pagerBlock)) {
            $pagerBlock = $this->getChild($this->getPagerBlockName());
            if (!$pagerBlock) {
                $pagerBlock = $this->getLayout()->createBlock(
                    $this->_defaultPagerBlock, microtime());
            }
            $this->_pagerBlock = $pagerBlock;
        }
        return $this->_pagerBlock;
    }

    public function setEntityCollection($entityCollection)
    {
        if ($entityCollection instanceof Goodahead_Etm_Model_Resource_Entity_Collection) {
            $this->_entityCollection = $entityCollection;
            $this->_templateFilter = null;
        }
        return $this;
    }

    /**
     * @return Goodahead_Etm_Model_Resource_Entity_Collection
     */
    public function getEntityCollection()
    {
        if (isset($this->_entityCollection)) {
            return $this->_entityCollection;
        }
        return Mage::registry('goodahead_etm_entity_collection');
    }

//    protected function _beforeToHtml()
//    {
//        parent::_beforeToHtml();
//        $this->getPagerBlock()->setCollection($this->getEntityCollection());
//        return $this;
//    }

    protected function _toHtml()
    {
        /**
         * @var $entity Goodahead_Etm_Model_Entity
         * @var $textProcessor Mage_Core_Model_Email_Template
         */
        $entityCollection = $this->getEntityCollection();
        $entityType = $entityCollection->getEntityType();
        $content = $entityType->getEntityTypeListContent();

//        $textProcessor = Mage::getModel('core/email_template');
//        $data = $entity->getResource()->walkAttributes(
//            'frontend/getValue', array($entity));

        /** @var $templateFilter Goodahead_Etm_Model_Template_Filter */
        $templateFilter = Mage::getModel('goodahead_etm/template_filter');
        $templateFilter
            ->setPagerBlock($this->getPagerBlock())
            ->setCollection($entityCollection)
            ->setEntityType($entityType)
            ->setStoreId(Mage::app()->getStore()->getId())
            ->setUseAbsoluteLinks(true)
            ->setPlainTemplateMode(false)
            ->setItemTemplate($entityType->getEntityTypeItemContent());
//        $textProcessor->setTemplateFilter($this->get);
//        $textProcessor->setTemplateText($content);


        return $templateFilter->filter($content);
    }
}