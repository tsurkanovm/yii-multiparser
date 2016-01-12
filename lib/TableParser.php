<?php
/**
 * Created by PhpStorm.
 * User: Tsurkanov
 * Date: 22.10.2015
 * Time: 15:53
 */

namespace yii\multiparser;


use common\components\CustomVarDamp;

abstract class TableParser extends Parser
{
    /**
     * @var array - текущий отпарсенный ряд
     *если есть ключи, то колонки с пустыми значениями будут пропускаться (из ряда такие значения будут удаляться),
     * например если в файле вторая колонка пустая то она будет удалена
     * в остальных случаях парсятся все колонки (не проверяется - пустая ли колонка) и попадёт в итоговый массив
     */
    protected $row = [];

    /** @var int - первая строка с которой начинать парсить
     * эта строка будет считаться первой значимой строкой
     * если установлен аттрибут $has_header_row,
     * тогда следующая строка будет считаться заголовком и будет пропущена
     */
    public $first_line = 0;

    /** @var int - последняя строка до которой  парсить
     * если не указана, то парсинг происходит до конца файла*/
    public $last_line = 0;

    /** @var int - первая колонка файла с которой начнется парсинг */
    public $first_column = 0;


    /** @var bool
     * имеет ли файл заголовок в первой значимой строке
     * true - первая значимая строка будет пропущена
     */
    public $has_header_row = true;


    /** @var int - количество значимых колонок, что бы определить первую значимую строку
     * используется при автоопределении первой строки*/
    public $min_column_quantity = 5;
    /** @var int - количество пустых строк, что бы определить конец файла,
     * такое количеество подряд пустых строк считается концом файла*/
    public $empty_lines_quantity = 3;


    /** @var int - номер текущей строки парсера */
    protected $current_row_number = 0;


    protected abstract function isEmptyColumn($column_value);

    protected abstract function readRow();

    protected abstract function setResult();


    public function read()
    {
        // первый проход
        $first_circle = true;
        $this->current_row_number = 0;

        // будем считать количество пустых строк подряд - при достижении $empty_lines_quantity - считаем что это конец файла и выходим
        $empty_lines = 0;
        while ($empty_lines < $this->empty_lines_quantity) {

            $this->readRow();
            $this->current_row_number++;

            // уберем пустые колонки из ряда
            if ( $this->keys === NULL ) {
                $this->filterRow();
            }

            if ( $this->isEmptyRow() ) {
                //счетчик пустых строк
                $empty_lines++;
                continue;
            }

            if ( $first_circle ) {
                // при первом проходе нужно учесть настройки по поиску первой строки
                // такие как first_line и  has_header_row
                $this->shiftToFirstValuableLine();
                $first_circle = false;
            }

            // запустим конвертирование
            $this->adjustRowToSettings();

            // установим отпарсенную строку в итоговый массив результата
            $this->setResult();

            // если у нас установлен лимит, при  его достижении прекращаем парсинг
            if ($this->isLastLine())
                break;

            // обнуляем счетчик, так как считаюся пустые строки ПОДРЯД
            $empty_lines = 0;
        }
    }

    /**
     * определяет первую значимую строку согласно first_line и has_header_row,
     * считывается пока не дойдет до first_line
     * пропускает заголовок если он указан
     */
    protected function shiftToFirstValuableLine()
    {
        // читаем пока не дойдем до first_line
        while ( $this->first_line > $this->current_row_number ) {
            $this->readRow();
            $this->current_row_number++;
        }
        // если указан заголовок, то его мы тоже пропускаем (читаем далее)
        if( $this->has_header_row ) {
            $this->readRow();
            $this->current_row_number++;
        }
    }

    /**
     * @return array - одномерный массив результата парсинга строки
     */
    protected function adjustRowToSettings()
    {
        // если есть заголовок, то перед конвертацией его нужно назначить
        if ($this->keys !== NULL) {
            // adjust row to keys
            $this->adjustRowToKeys();
            // назначим заголовок
            $this->row = array_combine($this->keys, $this->row);
        }

        // попытаемся конвертировать прочитанные значения согласно конфигурации котнвертера значений
        $this->row = $this->convert($this->row);

        // обрежем массив к первой значимой колонке
        if ($this->first_column) {

            $this->row = array_slice($this->row, $this->first_column);

        }

    }

    protected  function isEmptyRow(){

        $is_empty = false;

        if ( empty( $this->row ) ) {
            return true;
        }
        if (  count( $this->row ) < $this->min_column_quantity ) {
            return true;
        }

        $j = 0;
        for ($i = 1; $i <= count( $this->row ); $i++) {

            if ( !isset( $this->row[ $i - 1 ] ) ) {
                continue;
            }

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



    protected function filterRow()
    {
        // нет строки - нет фильтрации
        if ( empty( $this->row ) ) {
            return;
        }
        $this->row = array_filter($this->row, function ($val) {
            return !$this->isEmptyColumn($val);
        });
    }

    protected function isLastLine()
    {

        if (($this->last_line) && ($this->current_row_number >= $this->last_line)) {
            return true;
        }
        return false;
    }

    protected function adjustRowToKeys()
    {
        //уберем из ряда те колонки которых нет в ключах
        $this->row = array_intersect_key($this->row, $this->keys);

        $keys_count = count($this->keys);
        $column_count = count($this->row);
        if ($keys_count != $column_count) {
            // найдем колонки которых нет в ряде но есть ключах
            $arr_diff = array_diff_key($this->keys, $this->row);
            foreach ($arr_diff as $key => $value) {
                // колонки которых нет в ряде но есть ключах, добавим их с пустым значением
                $this->row[$key] = '';
            }
        }
    }

}