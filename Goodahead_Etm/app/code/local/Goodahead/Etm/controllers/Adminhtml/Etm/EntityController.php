<?php

class Goodahead_Etm_Adminhtml_Etm_EntityController extends Goodahead_Etm_Controller_Adminhtml
{
    /**
     * Entity grid page
     */
    public function indexAction()
    {
        try {
            $this->_initEntityType();

            $this->_initAction($this->__('Manage Entities'));
            $this->renderLayout();
        // TODO: Catch only our exception
        } catch (Exception $e) {
            Mage::logException($e);
            $this->_forward('no_route');
        }
    }

    /* Deletes single entity */
    public function deleteAction()
    {
        $entity = Mage::getModel('goodahead_etm/entity')->load($this->getRequest()->getParam('entity_id', null));

        if ($entity && $entity->getId()) {
            try {
                $entity->delete();
                $this->_getSession()->addSuccess($this->getEtmHelper()->__('Entity successfully deleted'));
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }

        $this->_redirectReferer();
    }

    public function massDeleteAction()
    {
        $etmEntitys = $this->getRequest()->getParam('entity_ids');
        if (!is_array($etmEntitys)) {
            $this->_getSession()->addError($this->__('Please select entity(s).'));
        } else {
            if (!empty($etmEntitys)) {
                try {
                    foreach ($etmEntitys as $entityId) {
                        Mage::getModel('goodahead_etm/entity')->setId($entityId)->delete();
                    }
                    $this->_getSession()->addSuccess(
                        $this->__('Total of %d record(s) have been deleted.', count($etmEntitys))
                    );
                } catch (Exception $e) {
                    $this->_getSession()->addError($e->getMessage());
                }
            }
        }
        $this->_redirectReferer();
    }

    /**
     * ACL check
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        switch ($this->getRequest()->getActionName()) {
            case 'edit':
            case 'save':
                return Mage::getSingleton('admin/session')->isAllowed('goodahead_etm/manage_entities/save');
                break;
            case 'delete':
                return Mage::getSingleton('admin/session')->isAllowed('goodahead_etm/manage_entities/delete');
                break;
            case 'index':
            default:
                return Mage::getSingleton('admin/session')->isAllowed('goodahead_etm/manage_entities');
                break;
        }
    }
}
