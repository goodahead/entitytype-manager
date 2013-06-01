<?php

class Goodahead_Etm_Block_Adminhtml_Entity_Types_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        $this->_objectId   = 'entity_type_id';
        $this->_blockGroup = 'goodahead_etm';
        $this->_controller = 'adminhtml_entity_types';

        parent::__construct();

        if ($this->_isAllowedAction('save')) {
            $this->_updateButton('save', 'label', Mage::helper('goodahead_etm')->__('Save Entity Type'));
            $this->_addButton('saveandcontinue', array(
                'label'     => Mage::helper('goodahead_etm')->__('Save and Continue Edit'),
                'onclick'   => 'saveAndContinueEdit()',
                'class'     => 'save',
            ), -100);
        } else {
            $this->_removeButton('save');
        }

        if ($this->_isAllowedAction('delete')) {
            $this->_updateButton('delete', 'label', Mage::helper('goodahead_etm')->__('Delete Entity Type'));
        } else {
            $this->_removeButton('delete');
        }

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
        /** @var Goodahead_Etm_Model_Entity_Type $entityType */
        $entityType = Mage::registry('etm_entity_type');
        if ($entityType->getId()) {
            return Mage::helper('goodahead_etm')->__("Edit Entity Type '%s'",
                 $this->escapeHtml($entityType->getEntityTypeName())
            );
        } else {
            return Mage::helper('goodahead_etm')->__('New Entity Type');
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
                return Mage::getSingleton('admin/session')->isAllowed('goodahead_etm/manage_entityTypes/save');
                break;
            case 'delete':
                return Mage::getSingleton('admin/session')->isAllowed('goodahead_etm/manage_entityTypes/delete');
                break;
            case 'index':
            default:
                return Mage::getSingleton('admin/session')->isAllowed('goodahead_etm/manage_entityTypes');
                break;
        }
    }
}
