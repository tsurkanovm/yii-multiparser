<?php
/**
 * Created by PhpStorm.
 * User: Cibermag
 * Date: 04.09.2015
 * Time: 18:25
 */

namespace yii\multiparser;

//@todo - заменить read на parse

use common\components\CustomVarDamp;

abstract class Parser
{
    public $converter_conf  = [];
    protected $converter = NULL;

    /** @var file-resource читаемого файла */
    public $file;
    /** @var string путь читаемого файла */
    public $file_path;

    /**
     * @var array - результирующий массив с отпарсенными значениями
     */
    protected $result = [];

    /** @var array - массив с заголовком,
     * */
    public $keys = NULL;

    public abstract function read();

    public function setup()
    {
        $this->setupConverter();
    }

    protected function setupConverter()
    {
        if ( !empty( $this->keys ) ) {
            // если у файла есть заголовок, то в результате имеем ассоциативный массив
            $this->converter_conf['hasKey'] = 1;
        }

        if ( $this->converter_conf ) {
            $converter = ObjectCreator::build( $this->converter_conf );
            if ( $converter instanceof ConverterInterface ) {

                $this->converter = $converter;

            }
        }
    }

    /**
     * @param $arr
     * @return mixed
     * преобразовует значения прочитанного массива в нужные типы, согласно конфигурации конвертера
     */
    protected function convert( $arr )
    {
        if ($this->converter !== NULL) {

            $arr = $this->converter->convertByConfiguration( $arr, $this->converter_conf );

        }
        return $arr;
    }

    public final static function supportedExtension()
    {
        return ['csv','xml','xlsx','txt','xls'];
    }

    protected function cleanUp(  )
    {
        unset( $this->file );
        unset( $this->converter );
        unset( $this->converter_conf );
    }

}