<?php
class Goodahead_Etm_Block_Adminhtml_Entity_Grid_Renderer_Multiselect
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $values= $this->_getValue($row);
        $values = explode(',', $values);

        $attribute = $this->getColumn()->getAttribute();
        $result = '';
        if (!empty($values)) {
            $result = '<ul>';
            foreach ($values as $value) {
                $result .= '<li>' . $attribute->getSource()->getOptionText($value) . '</li>';
            }
            $result .= '</ul>';
        }

        return $result;
    }

}