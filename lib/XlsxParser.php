<?php
/**
 * Created by PhpStorm.
 * User: Tsurkanov
 * Date: 21.10.2015
 * Time: 15:44
 */

namespace yii\multiparser;

use common\components\CustomVarDamp;


/**
 * Class XlsxParser
 * @package yii\multiparser
 */
class XlsxParser extends TableParser
{

    /**
     * @var string - путь куда будут распаковываться файлы, если не указанно - во временный каталог сервера
     */
    public $path_for_extract_files = '';


    /**
     * @var int - если указано то считывание будет производиться с этого листа, иначе со всех листов
     * при чтении со всех листов - выходной массив будет иметь номера листов первыми элементами
     */
    public $active_sheet = 0;

    protected $strings_arr = [];
    protected $sheets_arr = [];

    protected $current_node;
    protected $current_sheet;

    // глубина округления для флоата
    // @todo - перенести в родительский класс и применить в дочерних классах
    protected $float_precision = 6;

    public function setup()
    {

        parent::setup();

        if ($this->path_for_extract_files == '') {
            $this->path_for_extract_files = sys_get_temp_dir();
        }
    }


    public function read()
    {
        $this->extractFiles();
        $this->readSheets();
        $this->readStrings();
        foreach ($this->sheets_arr as $sheet) {
            //проходим по всем файлам из директории /xl/worksheets/
            $this->current_sheet = $sheet;
            $sheet_path = $this->path_for_extract_files . '/xl/worksheets/' . $sheet . '.xml';
            if (file_exists($sheet_path) && is_readable($sheet_path)) {
                $xml = simplexml_load_file($sheet_path, "SimpleXMLIterator");
                $this->current_node = $xml->sheetData->row;
                $this->current_node->rewind();
                if ($this->current_node->valid()) {
                    parent::read();
                }
            }
        }

        $this->cleanUp();

        if ($this->active_sheet) {
            // в настройках указан конкретный лист с которого будем производить чтение, поэтому и возвращаем подмассив
            return $this->result[$this->current_sheet];
        } else {
            return $this->result;
        }

    }

    protected function extractFiles()
    {
        $this->path_for_extract_files = $this->path_for_extract_files . session_id();
        if (!file_exists($this->path_for_extract_files)) {
            if (!mkdir($this->path_for_extract_files)) {
                throw new \Exception('Ошибка создания временного каталога - ' . $this->path_for_extract_files);
            }
        }

        $zip = new \ZipArchive;
        if ($zip->open($this->file_path) === TRUE) {
            $zip->extractTo($this->path_for_extract_files . '/');
            $zip->close();
        } else {

            throw new \Exception('Ошибка чтения xlsx файла');
        }
        unset($zip);
    }

    protected function readSheets()
    {
        if ($this->active_sheet) {
            $this->sheets_arr[] = 'sheet' . $this->active_sheet;
            return;
        }

        $xml = simplexml_load_file($this->path_for_extract_files . '/xl/workbook.xml');
        foreach ($xml->sheets->children() as $sheet) {
            $sheet_name = '';
            $sheet_id = 0;
            $attr = $sheet->attributes();
            foreach ($attr as $name => $value) {
                if ($name == 'name')
                    $sheet_name = (string)$value;

                if ($name == 'sheetId')
                    $sheet_id = $value;

            }
            if ($sheet_name && $sheet_id) {
                $this->sheets_arr[$sheet_name] = 'Sheet' . $sheet_id;
            }
//
        }
    }

    protected function readStrings()
    {
        $file_with_strings = $this->path_for_extract_files . '/xl/sharedStrings.xml';
        if ( file_exists( $file_with_strings ) ) {
            $xml = simplexml_load_file($file_with_strings);
            foreach ($xml->children() as $item) {
                $this->strings_arr[] = (string)$item->t;
            }
        }

    }


    protected function readRow()
    {
        $this->row = [];
        $node = $this->current_node->getChildren();
        if ($node === NULL) {
            return;
        }

        for ($node->rewind(), $i = 0; $node->valid(); $node->next(), $i++) {
            $child = $node->current();
            $attr = $child->attributes();

            // define the index of result array
            // $attr['r'] - contain the address of cells - A1, B1 ...
            if (isset($attr['r'])) {
                // override index
                $i = $this->convertCellToIndex( $attr['r'] );

                if ( $this->keys !== Null ){
                    if( isset( $this->keys[$i] ) ){
                        //$i = $this->keys[$i];
                    } else {
                        // we have a keys, but this one we didn't find, so skip it
                        continue;
                    }
                }
            }
            // define the value of result array
            if (isset($child->v)) {
                $value = (string)$child->v;

                if ( isset($attr['t']) ){
                    // it's not a value it's a string, so fetch it from string array
                    if( empty( $this->strings_arr[$value] ) ){
                        $value = '';
                    } else {
                        $value = $this->strings_arr[$value];
                    }

                } else {
                    $value = (string)round( $value, $this->float_precision );
                }

            } else {
                $value = '';
            }
            // set
            $this->row[$i] = $value;
        }

        ksort( $this->row );
        $this->current_node->next();
    }


    protected function isEmptyColumn($val)
    {
        return $val == '' || $val === null;
    }

    protected function setResult()
    {
        $this->result[$this->current_sheet][] = $this->row;
    }

    protected function deleteExtractFiles()
    {
        $this->removeDir($this->path_for_extract_files);

    }

    protected function removeDir($dir)
    {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (filetype($dir . "/" . $object) == "dir")
                        $this->removeDir($dir . "/" . $object);
                    else
                        unlink($dir . "/" . $object);
                }
            }
            reset($objects);
            rmdir($dir);
        }
    }

    /**
     * @param $cell_address - string with address like A1, B1 ...
     * @return int - integer index
     * this method has a constraint - 'Z' - it's a last column to convert,
     * column with 'AA..' address and bigger - return index = 0
     */
    protected function convertCellToIndex($cell_address)
    {
        $index = 0;

        $address_letter = substr($cell_address, 0, 1);
        $address_arr = range('A', 'Z');

        if ( $search_value =  array_search( $address_letter, $address_arr ) )
            $index = $search_value;

        return $index;

    }

    protected function cleanUp()
    {
        parent::cleanUp();
        unset($this->strings_arr);
        unset($this->sheets_arr);
        unset($this->current_node);


    }

    function __destruct()
    {
        $this->deleteExtractFiles();
    }


}