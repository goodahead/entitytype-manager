<?php

class Goodahead_Etm_Block_Adminhtml_Attribute_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
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

    /**
     * Initialize edit form container
     */
    public function __construct()
    {
        $this->_objectId   = 'attribute_id';
        $this->_blockGroup = 'goodahead_etm';
        $this->_controller = 'adminhtml_attribute';

        parent::__construct();

        if ($this->_isAllowedAction('save')) {
            $this->_updateButton('save', 'label', Mage::helper('goodahead_etm')->__('Save Attribute'));
            $this->_addButton('saveandcontinue', array(
                'label'     => Mage::helper('goodahead_etm')->__('Save and Continue Edit'),
                'onclick'   => 'saveAndContinueEdit()',
                'class'     => 'save',
            ), -100);
        } else {
            $this->_removeButton('save');
        }

        if ($this->_isAllowedAction('delete')) {
            $this->_updateButton('delete', 'label', Mage::helper('goodahead_etm')->__('Delete Attribute'));
        } else {
            $this->_removeButton('delete');
        }

        $backUrl = $this->getUrl('*/*/index', array(
            'entity_type_id' => $this->_getEntityTypeFromRegistry()->getId(),
        ));
        $this->_updateButton('back', 'onclick', 'setLocation(\'' . $backUrl . '\')');

        $this->_formScripts[] = "
            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    /**
     * Retrieve text for header element depending on loaded page
     *
     * @return string
     */
    public function getHeaderText()
    {
        /** @var Goodahead_Etm_Model_Attribute $attribute */
        $attribute = Mage::registry('etm_attribute');
        if ($attribute->getId()) {
            return Mage::helper('goodahead_etm')->__("Edit Attribute with Code '%s'",
                 $this->escapeHtml($attribute->getAttributeCode())
            );
        } else {
            return Mage::helper('goodahead_etm')->__('New Attribute');
        }
    }

    /**
     * Check permission for passed action
     *
     * @param string $action
     * @return bool
     */
    protected function _isAllowedAction($action)
    {
        switch ($action) {
            case 'edit':
            case 'save':
                return Mage::getSingleton('admin/session')->isAllowed('goodahead_etm/manage_attributes/save');
                break;
            case 'delete':
                return Mage::getSingleton('admin/session')->isAllowed('goodahead_etm/manage_attributes/delete');
                break;
            case 'index':
            default:
                return Mage::getSingleton('admin/session')->isAllowed('goodahead_etm/manage_attributes');
                break;
        }
    }
}
