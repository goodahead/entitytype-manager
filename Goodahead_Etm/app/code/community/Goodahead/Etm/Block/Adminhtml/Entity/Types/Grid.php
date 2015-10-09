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

class Goodahead_Etm_Block_Adminhtml_Entity_Types_Grid
    extends Mage_Adminhtml_Block_Widget_Grid
{
    protected function _construct()
    {
        $this->setId('entityTypesGrid');
        $this->_controller = 'adminhtml_entity_types';
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);

        $this->setDefaultSort('main_table.entity_type_id');
        $this->setDefaultDir('DESC');
    }

    protected function _prepareCollection()
    {
        /** @var Goodahead_Etm_Model_Resource_Entity_Type_Collection $collection */
        $collection = Mage::getResourceModel('goodahead_etm/entity_type_collection');
        $collection->addFilterToMap('entity_type_id', 'main_table.entity_type_id');
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('entity_type_id', array(
            'header'            => Mage::helper('catalog')->__('ID'),
            'width'             => '100',
            'filter_index'      => 'entity_type_id',
            'index'             => 'entity_type_id',
            'type'              => 'number'
        ));

        $this->addColumn('entity_type_code', array(
            'header'            => Mage::helper('goodahead_etm')->__('Entity Type Code'),
            'filter_index'      => 'main_table.entity_type_code',
            'index'             => 'entity_type_code',
            'type'              => 'varchar'
        ));

        $this->addColumn('entity_type_name', array(
            'header'            => Mage::helper('goodahead_etm')->__('Entity Type Name'),
            'filter_index'      => 'entity_type_name',
            'index'             => 'entity_type_name',
            'type'              => 'varchar'
        ));

        $this->addColumn('action',
            array(
                'header'    => Mage::helper('catalog')->__('Action'),
                'width'     => '280px',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption' => Mage::helper('catalog')->__('Manage Attributes'),
                        'url'     => array(
                            'base' => '*/etm_attribute',
                        ),
                        'field'   => 'entity_type_id',
                    ),
                    array(
                        'caption' => Mage::helper('goodahead_etm')->__('Manage Entities'),
                        'url'     => array(
                            'base' => '*/etm_entity',
                        ),
                        'field'   => 'entity_type_id',
                    ),
                    array(
                        'caption' => Mage::helper('catalog')->__('Edit'),
                        'url'     => array(
                            'base' => '*/*/edit',
                        ),
                        'field'   => 'entity_type_id',
                    ),
                    array(
                        'caption' => Mage::helper('catalog')->__('Delete'),
                        'url'     => array(
                            'base' => '*/*/delete',
                        ),
                        'field'   => 'entity_type_id',
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
        $this->setMassactionIdField('entity_type_id');
        $this->getMassactionBlock()->setFormFieldName('entity_type_ids');
        $this->getMassactionBlock()->addItem('delete', array(
            'label'   => Mage::helper('catalog')->__('Delete'),
            'url'     => $this->getUrl('*/*/massDelete'),
            'confirm' => Mage::helper('goodahead_etm')->__('Are you sure you want to delete selected entity types?')
        ));
        return $this;
    }

    /**
     * @param Goodahead_Etm_Model_Resource_Entity_Type $entityType
     * @return string
     */
    public function getRowUrl($entityType)
    {
        return $this->getUrl('*/*/edit', array(
            'entity_type_id' => $entityType->getId(),
        ));
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current' => true));
    }
}
