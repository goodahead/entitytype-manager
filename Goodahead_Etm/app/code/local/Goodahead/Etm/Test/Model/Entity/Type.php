<?php

class Goodahead_Etm_Test_Model_Entity_Type extends EcomDev_PHPUnit_Test_Case
{
    /**
     * @var Goodahead_Etm_Model_Entity_Type
     */
    protected $entityType = null;

    protected function setUp()
    {
        parent::setUp();
        $this->entityType = Mage::getModel('goodahead_etm/entity_type');
    }


    /**
     * @param string $name
     * @dataProvider dataProviderLoad
     */
    public function testSaveLoadDelete($name)
    {
        $this->entityType->setEntityTypeName($name);
        $this->entityType->save();

//        $this->assertEquals($name, $this->entityType->getEntityTypeName());
//
//        $this->entityType->delete();
//
//        $this->assertNotEquals($name, $this->entityType->getEntityTypeName());

    }

    public function dataProviderLoad()
    {
        $data = array(
            array('ololo'),
        );

        return $data;
    }

}
