<?php
/**
 * Created by PhpStorm.
 * User: Cibermag
 * Date: 04.09.2015
 * Time: 18:25
 */

namespace artweb\yii_multiparser;

//@todo - заменить read на parse
//@todo - xml - убрать из названий функций xml и array - это и так понятно


use common\components\CustomVarDamp;

abstract class Parser
{
    public $converter_conf  = [];
    protected $converter = NULL;

    /**
     * @var array - результирующий массив с отпарсенными значениями
     */
    protected $result = [];

    /** @var array - массив с заголовком,
     * */
    public $keys = NULL;
    /** @var bool
    имеет ли файл заголовок который будет установлен ключами возвращемого массива*/
    public $has_header_row = false;

    /** @var экземляр SplFileObject читаемого файла */
    public $file;



    public function setup()
    {
        $this->setupConverter();
    }

    protected function setupConverter()
    {
        if ( $this->has_header_row || $this->keys !== NULL ) {
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

    public abstract function read();

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
}