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

class Goodahead_Etm_Block_Etm extends Mage_Core_Block_Template
{
    protected $_entity;
    protected $_templateFilter;

    public function setEntity($entity)
    {
        if ($entity instanceof Goodahead_Etm_Model_Entity) {
            $this->_entity = $entity;
            $this->_templateFilter = null;
        } else {
            $helper = Mage::helper('goodahead_etm');

            try {
                $entity = $helper->getEntityByEntityId(
                    $this->getRequest()->getParam('entity_id'),
                    Mage::app()->getStore()->getId()
                );
                $this->_entity = $entity;
                $this->_templateFilter = null;
            } catch (Exception $e) {
                Mage::logException($e);
            }
        }
        return $this;
    }

    /**
     * @return Goodahead_Etm_Model_Entity
     */
    public function getEntity()
    {
        if (isset($this->_entity)) {
            return $this->_entity;
        }
        return Mage::registry('goodahead_etm_entity');
    }

    public function getEntityType()
    {
        $entity = $this->getEntity();
        if ($entity instanceof Goodahead_Etm_Model_Entity) {
            return $entity->getEntityTypeInstance();
        }
        return Mage::registry('goodahead_etm_entity_type');
    }

    /**
     * @return Goodahead_Etm_Model_Template_Filter
     */
    protected function _getTemplateFilter()
    {
        if (!isset($this->_templateFilter)) {
            /**
             * @var $entity Goodahead_Etm_Model_Entity
             */
            $entity = $this->getEntity();
            $data = $entity->getResource()->walkAttributes(
                'frontend/getValue', array($entity));

            $templateFilter = Mage::getModel('goodahead_etm/template_filter');
            $templateFilter
                ->setStoreId(Mage::app()->getStore()->getId())
                ->setUseAbsoluteLinks(true)
                ->setPlainTemplateMode(false)
                ->setVariables($data)
                ->setEntity($entity)
                ->setEntityType($entity->getEntityTypeInstance());
            $this->_templateFilter = $templateFilter;
        }
        return $this->_templateFilter;
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $entityType = $this->getEntityType();
        if (
            $entityType instanceof Goodahead_Etm_Model_Entity_Type
            && $entityType->getEntityTypeTitle()
        ) {
            $titleBlock = Mage::app()->getLayout()->getBlock('head');
            if ($titleBlock) {
                $titleBlock->setTitle(
                    $this->_getTemplateFilter()
                        ->filter($entityType->getEntityTypeTitle()));
            }
        }
        return $this;
    }

    public function _toHtml()
    {
        return $this->_getTemplateFilter()->filter(
            $this->getEntity()->getEntityTypeInstance()->getEntityTypeContent()
        );
    }
}