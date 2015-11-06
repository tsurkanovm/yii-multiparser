<?php
/**
 * Created by PhpStorm.
 * User: Cibermag
 * Date: 04.09.2015
 * Time: 18:25
 */

namespace yii\multiparser;

//@todo - заменить read на parse
//@todo - xml - убрать из названий функций xml и array - это и так понятно


abstract class Parser
{
    public $converter_conf  = [];
    protected $converter = NULL;

    /** @var экземляр SplFileObject читаемого файла */
    public $file;

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
    /*
     *если есть ключи, то колонки с пустыми значениями будут пропускаться (из ряда такие значения будут удаляться),
     * например если в файле вторая колонка пустая то она будет удалена
     * если есть $has_header_row - то первая значимая строка становится ключами, но пустые колонки не удаляются из ряда
     * например если в файле вторая колонка пустая то ей будет назначен соответсвующий ключ (второй) из первой строки
     * все описаное выше реализуется в дочернем семействе классов TableParser в методе filterRow()
     * для xml происходит просто сопоставление переданных ключей с прочитанными
    */




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

    protected function cleanUp(  )
    {
        unset( $this->file );
        unset( $this->converter );
        unset( $this->converter_conf );

    }


}