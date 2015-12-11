<?php
/**

 */
namespace yii\multiparser;

/**
 * Class XlsParser
 * @package yii\multiparser
 * @todo - перевести на анг. яз.
 */


class XlsParser extends TableParser
{
    // экземпляр класса Spreadsheet_Excel_Reader - для чтения листов Excel
  protected $reader;
   // строка кодировки
  protected $encoding = 'CP1251';

    /**
     * @var int - номер листа с которого будет происходить чтение
     */
    public $active_sheet = 0;

    /**
     * метод устанавливает настройки конвертера и ридера
     */
    public function setup()
    {
        parent::setup();
        $this->setupReader();
    }


    /**
     * устанавливает ридер и его параметры
     */
    protected function setupReader()
    {
        require_once 'ExcelReader/reader.php';
        $this->reader = new \Spreadsheet_Excel_Reader();
        $this->reader->setOutputEncoding( $this->encoding );
        $this->reader->read( $this->file_path );

    }

    public function read()
    {
        parent::read();

        $this->cleanUp();

        return $this->result;
    }


    protected function readRow(  )
    {
        $this->row = [];
        $current_sheet = $this->reader->sheets[ $this->active_sheet ];

        for ( $j = 1; $j <= $current_sheet['numCols']; $j++ ) {
            if ( isset( $current_sheet['cells'][ $this->current_row_number ][$j]) )
                $this->row[] = $current_sheet['cells'][ $this->current_row_number ][$j];
        }
    }


    protected  function isEmptyColumn( $val ){
        return $val == '';
    }

    protected  function setResult(  ){
        $this->result[] = $this->row;
    }
}