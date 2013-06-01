<?php
class Goodahead_Etm_Test_Model_Entity_Setup extends EcomDev_PHPUnit_Test_Case
{
    /**
     * @var Goodahead_Etm_Model_Entity_Setup
     */
    protected $setup = null;

    protected function setUp()
    {
        parent::setUp();
        $this->setup = new Goodahead_Etm_Model_Entity_Setup('goodahead_etm_setup');
    }

    /**
     * @param $array
     * @param $key
     * @param null $default
     * @dataProvider dataProviderGetValue
     */
    public function testGetValue($array, $key, $default = null)
    {
        $result = EcomDev_Utils_Reflection::invokeRestrictedMethod(
            $this->setup,
            '_getValue',
            array($array, $key, $default)
        );

        $this->assertInternalType('string', $result);
    }

    public  function dataProviderGetValue()
    {
        $data = array(
            array(array('bababa'=> 'cococo', 1=> 7), 'ololo', null),
        );
        return $data;
    }
}
