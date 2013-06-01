<?php
class Goodahead_Etm_Test_Model_Resource_Entity_Type_Collection extends EcomDev_PHPUnit_Test_Case
{
    /**
     * @var Goodahead_Etm_Model_Resource_Entity_Type_Collection
     */
    protected $collection = null;

    protected function setUp()
    {
        parent::setUp();
        $this->collection = Mage::getModel('goodahead_etm/entity_type')->getCollection();
    }

    public function testInitSelect()
    {
//        $collection = EcomDev_Utils_Reflection::invokeRestrictedMethod($this->collection, '_initSelect');

        $from = $this->collection->getSelect()->getPart(Zend_Db_Select::SQL_FROM);

        $this->assertArrayHasKey('etm_entity_type', $from);

    }
}