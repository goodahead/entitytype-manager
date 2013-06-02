<?php
class Goodahead_Etm_Block_Adminhtml_Attribute extends Mage_Adminhtml_Block_Widget_Grid_Container
{
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

    public function __construct()
    {
        $this->_blockGroup = 'goodahead_etm';
        $this->_controller = 'adminhtml_attribute';

        $typeName = $this->_getEntityTypeFromRegistry()->getEntityTypeName();
        $this->_headerText = Mage::helper('goodahead_etm')->__('Manage %s Attributes', $typeName);

        $this->_backButtonLabel = Mage::helper('goodahead_etm')->__('Back to Entity Types List');
        $this->_addBackButton();

        parent::__construct();

        $this->_updateButton('back', 'onclick', 'setLocation(\'' . $this->getUrl('*/etm_entityType') . '\')');

        $addUrl = $this->getUrl('*/*/new', array(
            'entity_type_id' => $this->_getEntityTypeFromRegistry()->getId(),
        ));
        $this->_updateButton('add', 'label', Mage::helper('goodahead_etm')->__('Add New Attribute'));
        $this->_updateButton('add', 'onclick', 'setLocation(\'' . $addUrl . '\')');
    }
}
