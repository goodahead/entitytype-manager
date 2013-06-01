<?php
class Goodahead_Etm_Block_Adminhtml_Entity_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    protected function _getEtmHelper()
    {
        return Mage::helper('goodahead_etm');
    }


    protected function _construct()
    {
        $this->setId('entityType');
        $this->_controller = 'adminhtml_entity';
        $this->setUseAjax(true);

        //$this->setDefaultSort('main_table.entity_type_id');
        //$this->setDefaultDir('DESC');
    }

    protected function _prepareCollection()
    {
        $entityType = Mage::registry('etm_entity_type');
        $collection = Mage::getModel('goodahead_etm/entity')
            ->getCollection($entityType->getEntityTypeCode())
            ->joinVisibleAttributes($entityType->getEntityTypeCode());

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {

        //$this->getVisibleAttributes()

        $this->addColumn('entity_id', array(
            'header'            => Mage::helper('goodahead_etm')->__('Entity ID'),
            'width'             => '100',
            'filter_index'      => 'main_table.entity_id',
            'index'             => 'entity_id',
            'type'              => 'number'
        ));

        $this->addColumn('entity_type_id', array(
            'header'            => Mage::helper('goodahead_etm')->__('Entity Type ID'),
            'width'             => '100',
            'filter_index'      => 'main_table.entity_type_id',
            'index'             => 'entity_type_id',
            'type'              => 'number'
        ));


        $entityType = Mage::registry('etm_entity_type');
        $visibleAttr = $this->_getEtmHelper()->getVisibleAttributes($entityType->getEntityTypeCode());
        foreach($visibleAttr as $attributeCode => $attrTitle) {
            $this->addColumn($attributeCode, array(
                'header'            => Mage::helper('goodahead_etm')->__($attrTitle),
                'index'             => $attributeCode,
                'type'              => 'text'
            ));
        }



        $this->addColumn('action', array(
            'header'            => Mage::helper('goodahead_etm')->__('Action'),
            'width'             => '100',
            'type'              => 'action',
            'getter'            => 'getId',
            'actions'           => array(
                array(
                    'caption' => Mage::helper('goodahead_etm')->__('Delete'),
                    'url'     => array(
                        'base' => '*/*/delete',
                    ),
                    'field'   => 'entity_type_id',
                    'confirm' => Mage::helper('goodahead_etm')->__('Are you sure?')
                )
            ),
            'filter'            => false,
            'sortable'          => false,
            'index'             => 'entity_type_id',
        ));

        $this->addColumn('action',
            array(
                'header'    => Mage::helper('goodahead_etm')->__('Actions'),
                'width'     => '200px',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
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

    public function getRowUrl($template)
    {
        return $this->getUrl('*/*/edit', array(
            'entity_type_id' => $template->getId(),
        ));
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current' => true));
    }
}
