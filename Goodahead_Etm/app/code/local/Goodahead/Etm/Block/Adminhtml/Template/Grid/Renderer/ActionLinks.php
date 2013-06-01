<?php

class Goodahead_Etm_Block_Adminhtml_Template_Grid_Renderer_ActionLinks
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Action
{
    /**
     * Renders column
     *
     * @param Varien_Object $row
     * @return string
     */
    public function render(Varien_Object $row)
    {
        $actions = $this->getColumn()->getActions();
        if (empty($actions) || !is_array($actions)) {
            return '&nbsp;';
        }

        $links = array();
        foreach ($actions as $action) {
            if (is_array($action)) {
                $links[] = $this->_toLinkHtml($action, $row);
            }
        }

        return implode(' | ', $links);
    }
}
