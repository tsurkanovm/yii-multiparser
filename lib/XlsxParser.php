<?php
/**
 * Created by PhpStorm.
 * User: Tsurkanov
 * Date: 21.10.2015
 * Time: 15:44
 */

namespace yii\multiparser;



/**
 * Class XlsxParser
 * @package yii\multiparser
 */
class XlsxParser extends TableParser {

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

    public function setup()
    {

        parent::setup();

        if ( $this->path_for_extract_files == '' ) {
            $this->path_for_extract_files = sys_get_temp_dir();
        }
    }


    public function read()
    {
        $this->extractFiles();

        $this->readSheets();
        $this->readStrings();

        foreach ( $this->sheets_arr  as $sheet ) {
            //проходим по всем файлам из директории /xl/worksheets/
            $this->current_sheet = $sheet;
            $sheet_path = $this->path_for_extract_files . '/xl/worksheets/' . $sheet . '.xml';
            if ( file_exists( $sheet_path ) && is_readable( $sheet_path ) ) {

                $xml = simplexml_load_file( $sheet_path, "SimpleXMLIterator" );
                $this->current_node = $xml->sheetData->row;
                $this->current_node->rewind();
                if ( $this->current_node->valid() ) {
                    parent::read();
                }
            }
        }

        $this->cleanUp();
        if ( $this->active_sheet ) {
            // в настройках указан конкретный лист с которогшо будем производить чтение, поэтому и возвращаем подмассив
            return $this->result[ $this->current_sheet ];
        }else{
            return $this->result;
        }

    }

    protected function extractFiles ()
    {
        $this->path_for_extract_files = $this->path_for_extract_files . session_id();
        if ( !mkdir($this->path_for_extract_files) )
        {
            throw new \Exception( 'Ошибка создания временного каталога - ' . $this->path_for_extract_files );
            }

        $zip = new \ZipArchive;
        if ( $zip->open( $this->file->getPathname() ) === TRUE ) {
            $zip->extractTo( $this->path_for_extract_files . '/' );
            $zip->close();
        } else {
            throw new \Exception( 'Ошибка чтения xlsx файла' );
        }
    }

    protected function readSheets ()
    {
        if ( $this->active_sheet ) {
            $this->sheets_arr[ ] = 'Sheet' . $this->active_sheet;
            return;
        }

        $xml = simplexml_load_file( $this->path_for_extract_files . '/xl/workbook.xml' );
        foreach ( $xml->sheets->children() as $sheet ) {
            $sheet_name = '';
            $sheet_id = 0;
            $attr = $sheet->attributes();
            foreach ( $attr as $name => $value ) {
                if ($name == 'name')
                    $sheet_name = (string)$value;

                if ($name == 'sheetId')
                    $sheet_id = $value;

            }
            if ( $sheet_name && $sheet_id ) {
                $this->sheets_arr[$sheet_name] = 'Sheet' . $sheet_id;
            }
//
        }
    }

    protected function readStrings ()
    {
        $xml = simplexml_load_file( $this->path_for_extract_files . '/xl/sharedStrings.xml' );
        foreach ( $xml->children() as $item ) {
            $this->strings_arr[] = (string)$item->t;
        }
    }



   // protected function readRow ( $item, $sheet , $current_row )
    protected function readRow ( )
    {
        $this->row = [];
        $node = $this->current_node->getChildren();
        if ($node === NULL) {
            return;
        }
        //foreach ( $node as $child ) {
        for ( $node->rewind(); $node->valid(); $node->next() ) {
            $child = $node->current();
            $attr = $child->attributes();

            if( isset($child->v) ) {
                $value = (string)$child->v;
            }else{
                $value = '';
            }
            if ( isset( $attr['t'] ) ) {
                $this->row[] =  $this->strings_arr[ $value ];
            }else{
                $this->row[] =  $value;
            }

        }
        // дополним ряд пустыми значениями если у нас ключей больше чем значений
        if ( $this->has_header_row && ( count( $this->keys ) > count( $this->row ) ) ) {
            $extra_coloumn = count( $this->keys ) - count( $this->row );
            for ( $i = 1; $i <= $extra_coloumn; $i++ ) {
                $this->row[] =  '';
            }
        }
        $this->current_node->next();
    }

    protected  function isEmptyRow(){

        $is_empty = false;

        if ( !count( $this->row ) || !$this->current_node->valid() ) {
            return true;
        }

        $j = 0;
        for ($i = 1; $i <= count( $this->row ); $i++) {

            if ( isset($this->row[$i - 1]) && $this->isEmptyColumn( $this->row[$i - 1] ) ) {
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

    protected  function setResult(  ){
        $this->result[ $this->current_sheet ][] = $this->row;
    }

    protected function deleteExtractFiles ()
    {
            $this->removeDir( $this->path_for_extract_files );

    }

    protected function removeDir($dir) {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (filetype($dir."/".$object) == "dir")
                        $this->removeDir($dir."/".$object);
                    else
                        unlink($dir."/".$object);
                }
            }
            reset($objects);
            rmdir($dir);
        }
    }


    protected function cleanUp()
    {
        parent::cleanUp();
        unset($this->strings_arr);
        unset($this->sheets_arr);
        unset($this->current_node);

        $this->deleteExtractFiles();

    }


}