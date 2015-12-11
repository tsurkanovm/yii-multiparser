<?php
/**

 */
namespace yii\multiparser;

/**
 * Class CsvParser
 * @package yii\multiparser
 * @todo - перевести на анг. яз.
 */
class CsvParser extends TableParser
{
    /** @var string - разделитель csv */
    public $delimiter = ';';



    /**
     * метод устанавливает настройки конвертера
     */
    public function setup()
    {
        parent::setup();

    }

    public function read()
    {
        parent::read();

        $this->cleanUp();

        return $this->result;
    }


    protected function readRow(  )
    {
        $this->row = fgetcsv( $this->file, 0, $this->delimiter );
    }


    protected  function isEmptyColumn( $val ){
        return $val == '';
    }

    protected  function setResult(  ){
        $this->result[] = $this->row;
    }
}