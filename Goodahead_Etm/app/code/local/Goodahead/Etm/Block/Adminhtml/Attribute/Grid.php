<?php
class Goodahead_Etm_Block_Adminhtml_Attribute_Grid extends Mage_Eav_Block_Adminhtml_Attribute_Grid_Abstract
{
    protected function _construct()
    {
        $this->setId('attributeGrid');
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
        /** @var Goodahead_Etm_Model_Resource_Attribute_Collection $collection */
        $collection = Mage::getResourceModel('goodahead_etm/attribute_collection');
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('attribute_id', array(
            'header'   => Mage::helper('goodahead_etm')->__('Attribute ID'),
            'width'    => '100',
            'sortable' => true,
            'index'    => 'attribute_id',
            'type'     => 'number'
        ));

        parent::_prepareColumns();

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
                            'base' => '*/*/delete/entity_type_id/' . $this->_getEntityTypeFromRegistry()->getId(),
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

    protected function _prepareMassaction()
    {
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

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current' => true));
    }
}
