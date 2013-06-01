<?php

class Goodahead_Etm_Adminhtml_Etm_AttributeController extends Goodahead_Etm_Controller_Adminhtml
{
    /**
     * Entity Type Manager index page
     */
    public function indexAction()
    {
        try {
            $this->_initEntityType();

            $this->_initAction($this->__('Manage Attributes'));
            $this->renderLayout();
        } catch (Goodahead_Etm_Exception $e) {
            Mage::logException($e);
            $this->_forward('no_route');
        }
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

    /**
     * Grid ajax action
     */
    public function gridAction()
    {
        try {
            $this->_initEntityType();

            $this->loadLayout();
            $this->renderLayout();
        } catch (Goodahead_Etm_Exception $e) {
            Mage::logException($e);
            $this->_forward('no_route');
        }
    }

    /**
     * Delete attribute action
     *
     * @return void
     */
    public function deleteAction()
    {
        $attributeId = $this->getRequest()->getParam('attribute_id');
        if ($attributeId) {
            try {
                $this->_initEntityType();

                /** @var Goodahead_Etm_Model_Attribute $attributeModel */
                $attributeModel = Mage::getSingleton('goodahead_etm/attribute');
                $attributeModel->load($attributeId);
                if (!$attributeModel->getId()) {
                    Mage::throwException(Mage::helper('goodahead_etm')->__('Unable to find attribute.'));
                }
                $attributeModel->delete();

                $this->_getSession()->addSuccess(
                    Mage::helper('goodahead_etm')->__(
                        "Attribute with code '%s' has been deleted.",
                        $attributeModel->getAttributeCode()
                    )
                );
            } catch (Goodahead_Etm_Exception $e) {
                $this->_getSession()->addException($e,
                    Mage::helper('goodahead_etm')->__('You are not allowed to delete non-custom attribute')
                );
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            } catch (Exception $e) {
                $this->_getSession()->addException($e,
                    Mage::helper('goodahead_etm')->__('An error occurred while deleting attribute.')
                );
            }
        }

        // go to grid
        $this->_redirect('*/*/', array('entity_type_id' => $this->getRequest()->getParam('entity_type_id')));
    }

    /**
     * Mass delete attributes action
     *
     * @return void
     */
    public function massDeleteAction()
    {
        $attributeIds = $this->getRequest()
            ->getParam('attribute_ids');

        if (!is_array($attributeIds)) {
            Mage::getSingleton('adminhtml/session')->addError(
                $this->__('Please select attribute(s) to delete.')
            );
        } else {
            try {
                $this->_initEntityType();

                /** @var Goodahead_Etm_Model_Attribute $attributeModel */
                $attributeModel = Mage::getSingleton('goodahead_etm/attribute');
                $attributeModel->deleteAttributes($attributeIds);

                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('goodahead_etm')->__(
                        '%d attribute(s) were deleted.', count($attributeIds)
                    )
                );
            } catch (Goodahead_Etm_Exception $e) {
                $this->_getSession()->addException($e,
                    Mage::helper('goodahead_etm')->__('You are not allowed to delete non-custom attributes')
                );
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            } catch (Exception $e) {
                $this->_getSession()->addException($e,
                    Mage::helper('goodahead_etm')->__('An error occurred while deleting attributes.')
                );
            }
        }

        // go to grid
        $this->_redirect('*/*/', array('entity_type_id' => $this->getRequest()->getParam('entity_type_id')));
    }
}
