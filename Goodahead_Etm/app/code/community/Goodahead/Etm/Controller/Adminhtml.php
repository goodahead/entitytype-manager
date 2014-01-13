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

class Goodahead_Etm_Controller_Adminhtml
    extends Mage_Adminhtml_Controller_Action
{
    protected $_usedModuleName = 'Goodahead_Etm';

    /**
     * Init entity type object based on passed entity_type_id parameter
     *
     * @throws Goodahead_Etm_Exception
     * @return Goodahead_Etm_Model_Entity_Type
     */
    protected function _initEntityType()
    {
        $entityTypeId = $this->getRequest()->getParam('entity_type_id', null);
        $entityType = Mage::getModel('goodahead_etm/entity_type')->load($entityTypeId);
        if ($entityType->getId() || $entityTypeId === null) {
            Mage::register('etm_entity_type', $entityType);
            return $entityType;
        }
        throw new Goodahead_Etm_Exception(Mage::helper('goodahead_etm')->__('Entity type not found'));
    }

    /**
     * Init entity object based on passed entity_id parameter
     *
     * @param int
     * @throws Goodahead_Etm_Exception
     * @return Goodahead_Etm_Model_Entity
     */
    protected function _initEntity($storeId = null)
    {
        $entityId = $this->getRequest()->getParam('entity_id', null);

        $entity = $this->getEtmHelper()->getEntityModelByEntityType(Mage::registry('etm_entity_type'));

        $entity->setStoreId($storeId);
        $entity->load($entityId);

        if ($entity->getId() || $entityId === null) {
            Mage::register('etm_entity', $entity);
            return $entity;
        }
        throw new Goodahead_Etm_Exception(Mage::helper('goodahead_etm')->__('Entity not found'));
    }

    /**
     * Init action
     *
     * @param string $title
     * @return $this
     */
    protected function _initAction($title)
    {
        $helper = Mage::helper('goodahead_etm');
        // load layout, set active menu and breadcrumbs
        $this->loadLayout()
            ->_setActiveMenu('goodahead_etm/manage_entities')
            ->_addBreadcrumb($helper->__('Entity Type Manager'), $helper->__('Entity Type Manager'))
            ->_addBreadcrumb($title, $title);

        // set title
        $this->_title($this->__('Entity Type Manager'))
            ->_title($title);

        return $this;
    }


    /* @return Goodahead_Etm_Helper_Data */
    protected function getEtmHelper()
    {
        return Mage::helper('goodahead_etm');
    }
}
