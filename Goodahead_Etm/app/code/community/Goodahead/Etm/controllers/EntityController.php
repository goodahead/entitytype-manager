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

class Goodahead_Etm_EntityController extends Mage_Core_Controller_Front_Action
{
    public function viewAction()
    {
        /** @var Goodahead_Etm_Helper_Data $helper */
        $helper = Mage::helper('goodahead_etm');

        try {
            $entity = $helper->getEntityByEntityId(
                $this->getRequest()->getParam('entity_id'),
                Mage::app()->getStore()->getId()
            );

            if ($entity->getId()) {
                $entityType = $entity->getEntityTypeInstance();
                Mage::register('goodahead_etm_entity', $entity);
                Mage::register('goodahead_etm_entity_type', $entityType);
                $this->getLayout()->getUpdate()
                    ->addHandle('default')
                    ->addHandle('goodahead_etm_entity_view')
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
        } catch (Exception $e) {
            $this->_forward('no_route');
        }
    }

    public function listAction()
    {
        /** @var Goodahead_Etm_Helper_Data $helper */
        $helper = Mage::helper('goodahead_etm');

        try {
            $entityCollection = $helper->getEntityCollectionByEntityType(
                $this->getRequest()->getParam('entity_type_code')
            );
            $entityType = $entityCollection->getEntityType();
            $entityCollection->setStoreId(Mage::app()->getStore()->getId());
            $entityCollection->joinVisibleAttributes($entityType->getId());

            Mage::register('goodahead_etm_entity_collection', $entityCollection);
            Mage::register('goodahead_etm_entity_type', $entityType);
            $this->getLayout()->getUpdate()
                ->addHandle('default')
                ->addHandle('goodahead_etm_entity_list')
                ->addHandle('ENTITY_TYPE_LIST_' . $entityType->getEntityTypeCode());
            $this->addActionLayoutHandles();
            if ($entityType->getEntityTypeListRootTemplate()) {
                $this->getLayout()->helper('page/layout')->applyHandle(
                    $entityType->getEntityTypeListRootTemplate());
            }

            $this->loadLayoutUpdates();
            $layoutUpdate = $entityType->getEntityTypeListLayoutXml();
            $this->getLayout()->getUpdate()->addUpdate($layoutUpdate);
            $this->generateLayoutXml()->generateLayoutBlocks();

            if ($entityType->getEntityTypeListRootTemplate()) {
                $this->getLayout()->helper('page/layout')
                    ->applyTemplate($entityType->getEntityTypeListRootTemplate());
            }

            $this->renderLayout();
        } catch (Exception $e) {
            $this->_forward('no_route');
        }
    }

}
