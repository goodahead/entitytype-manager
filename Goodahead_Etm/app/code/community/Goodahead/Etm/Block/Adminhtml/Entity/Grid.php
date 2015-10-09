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

class Goodahead_Etm_Block_Adminhtml_Entity_Grid
    extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * @return Goodahead_Etm_Helper_Data
     */
    protected function _getEtmHelper()
    {
        return Mage::helper('goodahead_etm');
    }

    protected function _construct()
    {
        $this->setId('entityType');
        $this->_controller = 'adminhtml_entity';
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);

        $this->setDefaultSort('main_table.entity_id');
        $this->setDefaultDir('DESC');
    }

    protected function _getStore()
    {
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        return Mage::app()->getStore($storeId);
    }

    protected function _prepareCollection()
    {
        $entityType = $this->getEntityType();

        $collection = $this->_getEtmHelper()
            ->getEntityCollectionByEntityType($entityType)
            ->setStoreId($this->_getStore()->getId())
            ->joinVisibleAttributes($entityType->getId());

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('entity_id', array(
            'header'            => Mage::helper('catalog')->__('ID'),
            'width'             => '100',
            'index'             => 'entity_id',
            'type'              => 'number'
        ));

        $store = $this->_getStore();

        $entityType = $this->getEntityType();
        $visibleAttr = $this->_getEtmHelper()->getVisibleAttributes($entityType);
        /** @var $helper Goodahead_Etm_Helper_Data */
        $helper = Mage::helper('goodahead_etm');

        /** @var $attribute Goodahead_Etm_Model_Entity_Attribute */
        foreach($visibleAttr as $attributeCode => $attribute) {
            if ($attribute->getFrontendInput() == 'image') {
                continue;
            }

             // TODO: Additional analyze is needed here. Should we use double translations of the 'header' or use only attribute label for admin store?
            $attributeParams = array(
                'header'            => Mage::helper('goodahead_etm')->__($attribute->getFrontend()->getLabel()),
                'index'             => $attributeCode,
                'type'              => $attribute->getBackendType(),
                'attribute'         => $attribute,
            );

            if  ($attribute->getBackendType() == 'int' && $attribute->getFrontendInput() == 'boolean') {
                $attributeParams['type'] = 'options';
                $attributeParams['width'] = '80px';
                $attributeParams['options'] = array(
                    '1' => Mage::helper('adminhtml')->__('Yes'),
                    '0' => Mage::helper('adminhtml')->__('No')
                );
            }

            //multiselect
            if ($attribute->getFrontendInput() == 'multiselect') {
                $attributeParams['type']    = 'options';
                $attributeParams['options'] = $helper->getOptionsHash($attribute->getSource(), false);
                $attributeParams['filter']  = 'goodahead_etm/adminhtml_entity_grid_filter_multiselect';
                $attributeParams['renderer']  = 'goodahead_etm/adminhtml_entity_grid_renderer_options';
            }

            //select
            if ($attribute->getFrontendInput() == 'select') {
                $attributeParams['type']    = 'options';
                $attributeParams['options'] = $helper->getOptionsHash($attribute->getSource(), false);
            }

            //price
            if ($attribute->getFrontendInput() == 'price') {
                $attributeParams['type'] = 'price';
                $attributeParams['currency_code'] = $store->getBaseCurrency()->getCode();
            }

            if ($attribute->getFrontendInput() == 'datetime') {
                $attributeParams['type'] = 'datetime';
                $attributeParams['width'] = '160px';
            }

            if ($attribute->getFrontendInput() == 'textarea') {
                $attributeParams['escape'] = true;
                $attributeParams['nl2br'] = true;
            }

            $transport = new Varien_Object($attributeParams);
            Mage::dispatchEvent('goodahead_etm_entity_grid_prepare_column', array(
                'attribute' => $attribute,
                'column_params' => $transport,
            ));

            $this->addColumn($attributeCode, $transport->getData());
        }

        $this->addColumn('action',
            array(
                'header'    => Mage::helper('catalog')->__('Action'),
                'width'     => '100',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption' => Mage::helper('catalog')->__('Edit'),
                        'url'     => array(
                            'base' => '*/*/edit/entity_type_id/' . $entityType->getId(),
                        ),
                        'field'   => 'entity_id',
                    ),
                    array(
                        'caption' => Mage::helper('catalog')->__('Delete'),
                        'url'     => array(
                            'base' => '*/*/delete/entity_type_id/' . $entityType->getId(),
                        ),
                        'field'   => 'entity_id',
                        'confirm' => Mage::helper('goodahead_etm')->__('Are you sure you want to delete entity type?')
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'renderer'  => 'goodahead_etm/adminhtml_template_grid_renderer_actionLinks',
                'index'     => 'etm_attribute',
        ));

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $entityType = $this->getEntityType();

        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('entity_ids');
        $this->getMassactionBlock()->addItem('delete', array(
            'label'   => Mage::helper('catalog')->__('Delete'),
            'url'     => $this->getUrl('*/*/massDelete', array('entity_type_id' => $entityType->getId())),
            'confirm' => Mage::helper('goodahead_etm')->__('Are you sure you want to delete selected entity types?')
        ));
        return $this;
    }

    public function getEntityType()
    {
        return Mage::registry('etm_entity_type');
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array(
            'entity_id' => $row->getId(),
            'entity_type_id' => $row->getEntityTypeId(),
            'store' => $this->getRequest()->getParam('store'),
        ));
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current' => true));
    }
}
