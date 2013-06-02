<?php
class Goodahead_Etm_Block_Adminhtml_Entity_Types_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    protected function _construct()
    {
        $this->setId('entityTypesGrid');
        $this->_controller = 'adminhtml_entity_types';
        $this->setUseAjax(true);

        $this->setDefaultSort('main_table.entity_type_id');
        $this->setDefaultDir('DESC');
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('goodahead_etm/entity_type_collection');

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('entity_type_id', array(
            'header'            => Mage::helper('goodahead_etm')->__('Entity Type ID'),
            'width'             => '100',
            'filter_index'      => 'main_table.entity_type_id',
            'index'             => 'entity_type_id',
            'type'              => 'number'
        ));

        $this->addColumn('entity_type_code', array(
            'header'            => Mage::helper('goodahead_etm')->__('Entity Type Code'),
            'filter_index'      => 'main_table.entity_type_code',
            'index'             => 'entity_type_code',
            'type'              => 'text'
        ));

        $this->addColumn('entity_type_name', array(
            'header'            => Mage::helper('goodahead_etm')->__('Entity Type Name'),
            'filter_index'      => 'entity_type_name',
            'index'             => 'entity_type_name',
            'type'              => 'text'
        ));

        $this->addColumn('action',
            array(
                'header'    => Mage::helper('goodahead_etm')->__('Actions'),
                'width'     => '280px',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption' => Mage::helper('goodahead_etm')->__('Manage Entities'),
                        'url'     => array(
                            'base' => '*/etm_entity',
                        ),
                        'field'   => 'entity_type_id',
                    ),
                    array(
                        'caption' => Mage::helper('goodahead_etm')->__('Manage Attributes'),
                        'url'     => array(
                            'base' => '*/etm_attribute',
                        ),
                        'field'   => 'entity_type_id',
                    ),
                    array(
                        'caption' => Mage::helper('goodahead_etm')->__('Edit'),
                        'url'     => array(
                            'base' => '*/*/edit',
                        ),
                        'field'   => 'entity_type_id',
                    ),
                    array(
                        'caption' => Mage::helper('goodahead_etm')->__('Delete'),
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
            'label'   => Mage::helper('goodahead_etm')->__('Delete'),
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
