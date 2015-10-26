<?php
/**

 */
namespace artweb\yii_multiparser;
use common\components\CustomVarDamp;


/**
 * Class CsvParser
 * @package artweb\yii_multiparser
 * @todo - перевести на анг. яз.
 */
class CsvParser extends TableParser
{
    /** @var string - разделитель csv */
    public $delimiter = ';';



    /**
     * метод устанвливает нужные настройки объекта SplFileObject, для работы с csv
     */
    public function setup()
    {

        $this->file->setCsvControl($this->delimiter);
        $this->file->setFlags(\SplFileObject::READ_CSV);
        $this->file->setFlags(\SplFileObject::SKIP_EMPTY);

        parent::setup();

    }

    public function read()
    {
        parent::read();

        return $this->result;
    }


    protected function readRow(  )
    {
        $this->row = $this->file->fgetcsv();
    }

    protected  function isEmptyRow(){

        $is_empty = false;

        if ($this->row === false || $this->row === NULL ) {
            return true;
        }

        $j = 0;
        for ($i = 1; $i <= count( $this->row ); $i++) {

            if ( $this->isEmptyColumn( $this->row[$i - 1] ) ) {
                $j++;
            }

            if ( $j >= $this->min_column_quantity ) {
                $is_empty = true;
                break;
            }
        }

        return $is_empty;
    }

    protected  function isEmptyColumn( $val ){
        return $val == '';
    }
}