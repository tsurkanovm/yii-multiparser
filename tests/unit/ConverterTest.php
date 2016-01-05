<?php
namespace tests\unit;


use Yii;
use yii\multiparser\Converter;

class ConverterTest extends \Codeception\TestCase\Test
{
    /**
     * @var \UnitTester
     */

    private $converter;
    private $configuration;
    private $wrong_configuration;
    private $data_in;
    private $data_out;


    public function _before()
    {
        $this->converter = new Converter();

        $this->configuration = ['hasKey' => true,
            'configuration' =>
            ['encode'   => 'encode',
            'string'   => ['string1', 'string2' ],
            'float'   => 'float',
            'integer'   => ['integer1', 'integer2' ],
         ]];

        $this->wrong_configuration = ['config' =>
            ['encode'   => 'encode',
            'string'   => 'string',
            'float'   => 'float',
            'integer'   => 'integer',
         ]];

        $this->data_in = [
            "encode"   => iconv( 'UTF-8', 'windows-1251', 'test encode string' ),
            "string1"   => 43,
            "string2"   => 45.45,
            "float"   => '100.67',
            "integer1"   => '43.5sd',
            "integer2"   =>  45.45,
        ];

    }

    public function testConvertByConfig(){

        $data_out = $this->converter->convertByConfiguration( $this->data_in, $this->configuration );
        $this->assertNotEmpty( $data_out, 'Output array is empty' );
        $this->assertArrayHasKey( 'encode', $data_out, 'Output array don`t have key - encode'  );
        $this->assertArrayHasKey( 'string1', $data_out, 'Output array don`t have key - string1'  );
        $this->assertArrayHasKey( 'string2', $data_out, 'Output array don`t have key - string2'  );
        $this->assertArrayHasKey( 'float', $data_out, 'Output array don`t have key - float'  );
        $this->assertArrayHasKey( 'integer1', $data_out, 'Output array don`t have key - integer1'  );
        $this->assertArrayHasKey( 'integer2', $data_out, 'Output array don`t have key - integer2'  );

        return $data_out;

    }
    /**
     * @depends testConvertByConfig
     */
    public function testConvertToFloat( $data_out ){

        $this->assertInternalType( 'float', $data_out['float'], 'Convert to float is failed' );

    }
    /**
     * @depends testConvertByConfig
     */
    public function testConvertToInteger( $data_out ){

        $this->assertInternalType( 'integer', $data_out['integer1'], 'Convert to integer is failed (field integer1)' );
        $this->assertInternalType( 'integer', $data_out['integer2'], 'Convert to integer is failed (field integer2)' );

    }
    /**
     * @depends testConvertByConfig
     */
    public function testConvertToEncoder( $data_out ){

        $this->assertEquals( $data_out['encode'], iconv( 'windows-1251', 'UTF-8', 'test encode string' ), 'Encoding failed' );

    }

    public function testConvertToException(){

        $this->setExpectedException('\Exception');
        $this->data_out = $this->converter->convertByConfiguration( $this->data_in, $this->wrong_configuration );

    }



}