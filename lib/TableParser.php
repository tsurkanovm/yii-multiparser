<?php
/**
 * Created by PhpStorm.
 * User: Tsurkanov
 * Date: 22.10.2015
 * Time: 15:53
 */

namespace yii\multiparser;


abstract class TableParser extends Parser {


    /**
     * @var array - текущий отпарсенный ряд
     */
    protected $row = [];

    /** @var int - первая строка с которой начинать парсить */
    public $first_line = 0;

    /** @var int - последняя строка до которой  парсить
     * если не указана, то парсинг происходит до конца файла*/
    public $last_line = 0;

    /** @var int - первая колонка файла с которой начнется парсинг */
    public $first_column = 0;


    /** @var bool
    нужно ли искать автоматически первоую значисмую строку (не пустая строка)
     * иначе первая строка будет взята из аттрибута $first_line */
    public $auto_detect_first_line = false;

    /** @var int - количество значимых колонок, что бы определить первую значимую строку
     * используется при автоопределении первой строки*/
    public $min_column_quantity = 5;
    /** @var int - количество пустых строк, что бы определить конец файла,
     такое количеество подряд пустых строк считается концом файла*/
    public $empty_lines_quantity = 3;


    /** @var int - номер текущей строки парсера */
    protected $current_row_number = 0;


    protected abstract function isEmptyRow();

    protected abstract function isEmptyColumn($column_value);

    protected abstract function readRow();

    protected abstract function setResult();


    public function read()
    {
        if ($this->auto_detect_first_line) {
            $this->shiftToFirstValuableLine();
        }

        // будем считать количество пустых строк подряд - при достижении $empty_lines_quantity - считаем что это конец файла и выходим
        $empty_lines = 0;
        while ( $empty_lines < $this->empty_lines_quantity ) {
            // прочтем строку из файла
            $this->readRow();

            // уберем пустые колонки из ряда
            $this->filterRow();

            if ( $this->isEmptyRow() ) {
                //счетчик пустых строк
                //CustomVarDamp::dump($this->current_row_number);
                $empty_lines++;
                continue;
            }

            $this->adjustRowToSettings(  );

            // строка не пустая, имеем прочитанный массив значений
            $this->current_row_number++;

            // для первой строки утановим ключи из заголовка
            if ( !$this->setKeysFromHeader() ) {
                $this->setResult();
            }


            // если у нас установлен лимит, при  его достижении прекращаем парсинг
            if ( $this->isLastLine() )
                break;

            // обнуляем счетчик, так как считаюся пустые строки ПОДРЯД
            $empty_lines = 0;



        }


    }
    /**
     * определяет первую значимую строку,
     * считывается файл пока в нем не встретится строка с непустыми колонками
     * в количестве указанном в атрибуте min_column_quantity
     * в результате выполнения $current_row_number будет находится на последней незначимой строке
     */
    protected function shiftToFirstValuableLine()
    {
        do  {

            $this->current_row_number ++;
            $this->readRow();

        } while( $this->isEmptyRow() );

        // @todo - сделать опционально
        // код для того что бы парсить первую строку, закомментировано как предполагается что первая значимая строка это заголовок
        //       $this->current_row_number --;
//        $this->file->seek( $this->current_row_number );
    }

    /**
     * @return array - одномерный массив результата парсинга строки
     */
    protected function adjustRowToSettings(  )
    {

            // если есть заголовок, то перед конвертацией его нужно назначить
            if ( $this->keys !== NULL ) {

                if (count($this->keys) !== count($this->row)) {
                    throw new \Exception("Ошибка парсинга файла в строке # {$this->current_row_number}. Не соответсвие числа ключевых колонок (заголовка) - числу колонок с данными");
                }

                $this->row = array_combine($this->keys, $this->row);
            }

            // попытаемся конвертировать прочитанные значения согласно конфигурации котнвертера значений
            $this->row = $this->convert($this->row);

            // обрежем массив к первой значимой колонке
            if ( $this->first_column ) {

                $this->row = array_slice($this->row, $this->first_column);

            }

    }

    protected  function setKeysFromHeader(){
        if ( $this->has_header_row ) {
            // в файле есть заголовок, но он еще не назначен - назначим
            if ($this->keys === NULL) {
                $this->keys = array_values( $this->row );
                return true;
            }
        }
        return false;
    }

    protected  function filterRow(){
        // если есть заголовок или ключи - все значения нужны, не фильтруем
        if ( $this->has_header_row || $this->keys !== NULL ) {
            return;
        }
       // CustomVarDamp::dump( $this->row);
        $this->row = array_filter( $this->row, function($val){
            return !$this->isEmptyColumn($val);
        });
        //CustomVarDamp::dump( $this->row);
    }

    protected  function isLastLine(){

        if ( ( $this->last_line ) && ( $this->current_row_number > $this->last_line ) ) {
            return true;
        }
        return false;
    }

}