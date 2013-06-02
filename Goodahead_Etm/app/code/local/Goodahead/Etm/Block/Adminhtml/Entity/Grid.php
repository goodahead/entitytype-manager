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

        $this->setDefaultSort('main_table.entity_id');
        $this->setDefaultDir('DESC');
    }

    protected function _prepareCollection()
    {
        $entityType = Mage::registry('etm_entity_type');
        $collection = Mage::getModel('goodahead_etm/entity')
            ->getCollection($entityType->getEntityTypeCode())
            ->joinVisibleAttributes($entityType->getId());

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }


    protected function _prepareColumns()
    {

        //$this->getVisibleAttributes()

        $this->addColumn('entity_id', array(
            'header'            => Mage::helper('goodahead_etm')->__('Entity ID'),
            'width'             => '100',
            'index'             => 'entity_id',
            'type'              => 'number'
        ));




        $entityType = Mage::registry('etm_entity_type');
        $visibleAttr = $this->_getEtmHelper()->getVisibleAttributes($entityType->getId());
        foreach($visibleAttr as $attributeCode => $attribute) {
            $attributeParams = array(
                'header'            => Mage::helper('goodahead_etm')->__($attribute->getAttributeName()),
                'index'             => $attributeCode,
                'type'              => $attribute->getBackendType(),
            );
            if  ($attribute->getBackendType() == 'int' && $attribute->getFrontendInput() == 'boolean') {
                $attributeParams['type'] = 'options';
                $attributeParams['width'] = '80px';
                $attributeParams['options'] = array(
                    '1' => Mage::helper('goodahead_etm')->__('Yes'),
                    '0' => Mage::helper('goodahead_etm')->__('No')
                );
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
                'header'    => Mage::helper('goodahead_etm')->__('Actions'),
                'width'     => '100',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption' => Mage::helper('goodahead_etm')->__('Edit'),
                        'url'     => array(
                            'base' => '*/*/edit/entity_type_id/' . $entityType->getId(),
                        ),
                        'field'   => 'entity_id',
                    ),
                    array(
                        'caption' => Mage::helper('goodahead_etm')->__('Delete'),
                        'url'     => array(
                            'base' => '*/*/delete',
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
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('entity_ids');
        $this->getMassactionBlock()->addItem('delete', array(
            'label'   => Mage::helper('goodahead_etm')->__('Delete'),
            'url'     => $this->getUrl('*/*/massDelete'),
            'confirm' => Mage::helper('goodahead_etm')->__('Are you sure you want to delete selected entity types?')
        ));
        return $this;
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array(
            'entity_id' => $row->getId(),
            'entity_type_id' => $row->getEntityTypeId(),
        ));
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current' => true));
    }
}
