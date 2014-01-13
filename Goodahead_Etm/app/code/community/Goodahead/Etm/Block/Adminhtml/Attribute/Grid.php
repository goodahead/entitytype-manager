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

class Goodahead_Etm_Block_Adminhtml_Attribute_Grid
    extends Mage_Eav_Block_Adminhtml_Attribute_Grid_Abstract
{
    protected function _construct()
    {
        $entityType = $this->_getEntityTypeFromRegistry();

        $this->setId('attributeGrid' . $entityType->getId());
        $this->_controller = 'adminhtml_attribute';
        $this->setUseAjax(true);

        $this->setDefaultSort('attribute_code');
        $this->setDefaultDir('ASC');
    }

    /**
     * Get entity type object from registry
     *
     * @return Mage_Eav_Model_Entity_Type
     * @throws Goodahead_Etm_Exception
     */
    protected function _getEntityTypeFromRegistry()
    {
        /** @var Mage_Eav_Model_Entity_Type $entityType */
        $entityType = Mage::registry('etm_entity_type');
        if ($entityType && $entityType->getId()) {
            return $entityType;
        }

        $helper = Mage::helper('goodahead_etm');
        throw new Goodahead_Etm_Exception($helper->__('Entity type object is absent in registry'));
    }

    /**
     * Prepare attributes grid collection object
     *
     * @return $this
     */
    protected function _prepareCollection()
    {
        /** @var Goodahead_Etm_Model_Resource_Entity_Attribute_Collection $collection */
        $collection = Mage::getResourceModel('goodahead_etm/entity_attribute_collection');
        $collection->setEntityType($this->_getEntityTypeFromRegistry());
        $collection->addFilterToMap('attribute_id', 'main_table.attribute_id');
        $collection->addFilterToMap('entity_type_id', 'main_table.entity_type_id');
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * Remove existing column. Compatibility with 1.4, 1.5 Magento versions
     *
     * @param string $columnId
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    public function removeColumn($columnId)
    {
        if (method_exists(get_parent_class(__CLASS__), 'removeColumn')) {
            return parent::removeColumn($columnId);
        } else {
            if (isset($this->_columns[$columnId])) {
                unset($this->_columns[$columnId]);
                if ($this->_lastColumnId == $columnId) {
                    $this->_lastColumnId = key($this->_columns);
                }
            }
            return $this;
        }
    }

    /**
     * Prepare columns for grid
     *
     * @return $this
     */
    protected function _prepareColumns()
    {
        $this->addColumn('attribute_id', array(
            'header'   => Mage::helper('goodahead_etm')->__('Attribute ID'),
            'width'    => '100',
            'sortable' => true,
            'index'    => 'attribute_id',
            'type'     => 'number',
        ));

        parent::_prepareColumns();

        $this->removeColumn('attribute_label');

        $scopes = Mage::getModel('goodahead_etm/source_scope')->toArray();
        $this->addColumn('is_global', array(
            'header'   => Mage::helper('goodahead_etm')->__('Scope'),
            'sortable' => true,
            'index'    => 'is_global',
            'type'     => 'options',
            'options'  => $scopes
        ));
        $yesNo = Mage::getModel('goodahead_etm/source_yesno')->toArray();
        $this->addColumn('is_visible', array(
            'header'   => Mage::helper('goodahead_etm')->__('Is Visible'),
            'sortable' => true,
            'index'    => 'is_visible',
            'type'     => 'options',
            'options'  => $yesNo
        ));

        // TODO: Add permission check for actions
        $this->addColumn('action',
            array(
                'header'    => Mage::helper('goodahead_etm')->__('Actions'),
                'width'     => '80px',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption' => Mage::helper('goodahead_etm')->__('Edit'),
                        'url'     => array(
                            'base' => '*/*/edit/entity_type_id/' . $this->_getEntityTypeFromRegistry()->getId(),
                        ),
                        'field'   => 'attribute_id',
                    ),
                    array(
                        'caption' => Mage::helper('goodahead_etm')->__('Delete'),
                        'url'     => array(
                            'base' => '*/*/delete/entity_type_id/' . $this->_getEntityTypeFromRegistry()->getId(),
                        ),
                        'field'   => 'attribute_id',
                        'confirm' => Mage::helper('goodahead_etm')->__('Are you sure you want to delete attribute?')
                    ),
                ),
                'filter'    => false,
                'sortable'  => false,
                'renderer'  => 'goodahead_etm/adminhtml_template_grid_renderer_actionLinks',
                'index'     => 'etm_attribute',
            ));

        return $this;
    }

    /**
     * Prepare mass action for grid
     *
     * @return $this
     */
    protected function _prepareMassaction()
    {
        // TODO: Add permissions check for massaction
        $this->setMassactionIdField('entity_type_id');
        $this->getMassactionBlock()->setFormFieldName('attribute_ids');
        $this->getMassactionBlock()->addItem('delete', array(
            'label'   => Mage::helper('goodahead_etm')->__('Delete'),
            'url'     => $this->getUrl('*/*/massDelete', array(
                    'entity_type_id' => $this->_getEntityTypeFromRegistry()->getId(),
                )),
            'confirm' => Mage::helper('goodahead_etm')->__('Are you sure you want to delete selected attributes?')
        ));
        return $this;
    }

    /**
     * Return row (edit Attribute) URL that will be used by grid object
     *
     * @param Goodahead_Etm_Block_Adminhtml_Attribute $attribute
     * @return string
     */
    public function getRowUrl($attribute)
    {
        return $this->getUrl('*/*/edit', array(
            'entity_type_id' => $this->_getEntityTypeFromRegistry()->getId(),
            'attribute_id'   => $attribute->getId(),
        ));
    }

    /**
     * Return grid URL that will be used by grid object
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current' => true));
    }
}
