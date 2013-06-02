<?php

class Goodahead_Etm_Block_Adminhtml_Entity_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        $this->_objectId   = 'entity_id';
        $this->_blockGroup = 'goodahead_etm';
        $this->_controller = 'adminhtml_entity';

        parent::__construct();

        if ($this->_isAllowedAction('save')) {
            $this->_updateButton('save', 'label', Mage::helper('goodahead_etm')->__('Save Entity'));
            $this->_addButton('saveandcontinue', array(
                'label'     => Mage::helper('goodahead_etm')->__('Save and Continue Edit'),
                'onclick'   => 'saveAndContinueEdit()',
                'class'     => 'save',
            ), -100);
        } else {
            $this->_removeButton('save');
        }

        if ($this->_isAllowedAction('delete')) {
            $this->_updateButton('delete', 'label', Mage::helper('goodahead_etm')->__('Delete Entity'));
        } else {
            $this->_removeButton('delete');
        }

        $entityTypeId = $this->getRequest()->getParam('entity_type_id');
        $this->_updateButton('back', 'onclick', 'setLocation(\'' . $this->getUrl('*/etm_entity', array(
        'entity_type_id' => $entityTypeId)) . '\')'
        );

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
        /** @var Goodahead_Etm_Model_Entity $entity */
        $entity = Mage::registry('etm_entity');
        if ($entity->getId()) {
            return Mage::helper('goodahead_etm')->__("Edit Entity '%s'",
                $this->escapeHtml($entity->getEntitylabel())
            );
        } else {
            return Mage::helper('goodahead_etm')->__('New Entity');
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
                return Mage::getSingleton('admin/session')->isAllowed('goodahead_etm/manage_entity/save');
                break;
            case 'delete':
                return Mage::getSingleton('admin/session')->isAllowed('goodahead_etm/manage_entity/delete');
                break;
            case 'index':
            default:
                return Mage::getSingleton('admin/session')->isAllowed('goodahead_etm/manage_entity');
                break;
        }
    }
}
