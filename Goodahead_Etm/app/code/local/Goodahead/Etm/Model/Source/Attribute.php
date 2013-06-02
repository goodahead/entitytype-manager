<?php
/**
 * Created by JetBrains PhpStorm.
 * User: test
 * Date: 02.06.13
 * Time: 9:00
 * To change this template use File | Settings | File Templates.
 */

class Goodahead_Etm_Model_Source_Attribute {

    public function toOptionsArray($entityType, $emptyLine = false)
    {

        $result = array();
        if ($emptyLine) {
            $result = array(
                array(
                    'value' => '',
                    'label' => '',
                ),
            );
        }
        /** @var $attributesCollection Goodahead_Etm_Model_Resource_Attribute_Collection */
        $attributesCollection = Mage::getModel('goodahead_etm/attribute')->getCollection();
        $attributesCollection->setEntityType($entityType);
        return array_merge($result, $attributesCollection->load()->toOptionArray());
    }

}