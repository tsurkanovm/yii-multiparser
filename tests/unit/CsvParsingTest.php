<?php
namespace tests\unit;

use Codeception\Util\Stub;
use Yii;
use yii\multiparser\YiiMultiparser;
use yii\multiparser\YiiParserHandler;

class CsvParsingTest extends \Codeception\TestCase\Test
{
    /**
     * @var \UnitTester
     */

    protected static $data;
    protected static $parser_handler;
    protected static $file_path;
    protected static $options;

    public static function setUpBeforeClass()
    {

        self::$file_path = Yii::getAlias('@tests') . '\template.csv';
        self::$parser_handler = new YiiParserHandler();

    }

    public function parseFileByOptions( $options ){

        self::$parser_handler->setConfiguration( $options );
        $csv_parser = Stub::make( new YiiMultiparser(), ['parserHandler' => self::$parser_handler] );

        if (!$csv_parser)
            self::markTestSkipped('Parser handler do not initialized.');

        self::$data = $csv_parser->parse( self::$file_path, ['mode' => 'template'] );
    }


    public function testWithKeys( ){

        self::$options =
            ['csv' =>
                ['template' =>
                    ['class' => 'yii\multiparser\CsvParser',
                        'keys' => [
                            0 => 'Description',
                            1 => 'Article',
                            2 => 'Price',
                            3 => 'Brand',
                            4 => 'Count',
                        ],
                        'converter_conf' => [
                            'class' => 'yii\multiparser\Converter',
                            'configuration' => ["encode" => 'Description',
                                "string" => ['Description', 'Brand'],
                                "float" => 'Price',
                                "integer" => 'Count'
                            ],
                        ],
                    ],
                ],
            ];

        $this->parseFileByOptions( self::$options );
        $this->assertNotEmpty( self::$data , 'Output array is empty' );

    }

    /**
     * @depends testWithKeys
     */
    public function testWithKeysOnFullness( ){

        $this->assertArrayHasKey( 'Article', self::$data[0], 'Output array does`t have key - Article'  );
        $this->assertArrayHasKey( 'Count', self::$data[1], 'Output array does`t have key - Count'  );
        $this->assertArrayHasKey( 'Description', self::$data[2], 'Output array does`t have key - Description'  );
        $this->assertArrayHasKey( 'Price', self::$data[3], 'Output array does`t have key - Price'  );
        $this->assertArrayHasKey( 'Price', self::$data[13], 'Output array does`t have key - Brand'  );
        $this->assertEquals( 16, count( self::$data ), 'Output array does`t have 16 rows'  );

    }

    public function setOptionDataProvider(){

        $opt = [
            [7, ['csv' => ['template' => ['first_line' => 10],],]],
            [8, ['csv' => ['template' => ['first_line' => 10,  'has_header_row' => false],],]],
            [17, ['csv' => ['template' => ['has_header_row' => false],],]],
            [3, ['csv' => ['template' => ['last_line' => 3, 'has_header_row' => false],],]],
            [2, ['csv' => ['template' => ['last_line' => 3],],]]
        ];

        return $opt;
    }

    /**
     * @depends testWithKeys
     * @dataProvider setOptionDataProvider
     */
    public function testWithKeysOnTruncate( $expectedResult,  $sub_options ){

        $options = array_merge_recursive( self::$options, $sub_options );
        $this->parseFileByOptions( $options );
        $this->assertEquals( $expectedResult, count( self::$data ), "Output (extended) array does`t have {$expectedResult} rows"  );

    }

    /**
     * @depends testWithKeys
     */
    public function testWithoutKeys( ){

        self::$options =
            ['csv' =>
                ['template' =>
                    ['class' => 'yii\multiparser\CsvParser',

                    ],
                ],
            ];

        $this->parseFileByOptions(  self::$options );
        $this->assertNotEmpty( self::$data , 'Output array is empty' );

    }

    /**
     * @depends testWithoutKeys
     */
    public function testWithoutKeysOnFullness( ){

        $this->assertEquals( 16, count( self::$data ), 'Output array does`t have 16 rows'  );
        $this->assertEquals( 5, count( self::$data[0] ), 'Output array does`t have 5 columns'  );

    }








}