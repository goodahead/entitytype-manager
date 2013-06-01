<?php

class Goodahead_Etm_Test_Model_Resource_Entity_Type extends EcomDev_PHPUnit_Test_Case
{
    /**
     * @var Goodahead_Etm_Model_Resource_Entity_Type
     */
    protected $model = null;

    protected function setUp()
    {
        parent::setUp();
        $this->model = Mage::getResourceModel('goodahead_etm/entity_type');
    }

    /**
     * @param $field
     * @param $value
     * @param $object
     * @return mixed
     * @dataProvider dataProviderLoadSelect
     */
    public  function testGetLoadSelect($field, $value, $object)
    {
         $select = EcomDev_Utils_Reflection::invokeRestrictedMethod(
             $this->model, '_getLoadSelect', array($field, $value, $object)
         );

        $from = $select->getPart(Zend_Db_Select::FROM);

        $this->assertArrayHasKey('etm_entity_type', $from);
    }

    public function dataProviderLoadSelect()
    {
        $data = array(
            array('field', 'value', new Varien_Object()),
        );
        return $data;
    }
}
