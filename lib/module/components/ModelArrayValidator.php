<?php
/**
 * Created by PhpStorm.
 * User: Tsurkanov
 * Date: 26.11.2015
 * Time: 9:04
 */

namespace yii\multiparser;

/**
 * Class ModelArrayValidator
 * @package common\components
 * Валидирует переданный массив, сохраняет отдельно ошибки, и возвращает отвалидированные данные
 * также формирует сообщение на форму с количеством обработанных записей и количеством ошибок
 * использовать когда нужно сохранить только отвалидированные данные,
 * а ошибочные откинуть и показать пользователю в сообщении
 */
class ModelArrayValidator implements MassiveDataValidatorInterface
{
    protected $arr_errors = [];
    protected $model;
    protected $valid_data = [];
    protected $total_rows = 0;

    public function setModel( Model $model )
    {
        $this->model = $model;
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->arr_errors;
    }

    /**
     * @return mixed
     */
    public function getValidData()
    {
        return $this->valid_data;
    }

    /**
     * @return string - сообщение на форму с результатми обработки
     */
    public function getMassage ()
    {
        $total_count = $this->total_rows;
        $success_count = $this->total_rows - count($this->arr_errors);
        $error_count = count($this->arr_errors);

        $msg = "Обработано - {$total_count} строк.</br>
        Успешно загрузились - {$success_count} строк.</br>
        Найдено строк с ошибками - {$error_count}.</br>";

        foreach ($this->arr_errors as $row => $error) {
            $msg .= "Ошибка в строке {$row} </br>
            Текст ошибки: {$error} </br>";
        }

        return $msg;
    }

    /**
     * @param $data
     * @return array
     * метод регистрирует ошибки, регистрирует "чистые данные" и возвращает их
     */
    public function validate( $data )
    {
        foreach ( $data as $row ) {
            $this->total_rows++;

             if ( $this->validateRow( $row ) ) {
            // everything OK, registred row to valid data
                $this->valid_data[] = $row;
             } else{
                 // we have errors
                 $this->registredError( $this->total_rows );
             }

        }

        return $this->valid_data;
    }
    public function validateRow( $row )
    {
            $validate_row[$this->model->formName()] = $row;
            // clear previous loading
            $this->clearModelAttributes();
             if ( $this->model->load( $validate_row ) && $this->model->validate() ) {

                 return true;
             } else{

                 return false;
             }

    }

    protected function registredError ($index)
    {
        $errors_str = '';
        foreach ($this->model->getErrors() as $error) {
            $errors_str .= implode(array_values($error));
        }

        $this->arr_errors[$index] = $errors_str;
    }


    public function hasError ()
    {
        return (bool) count($this->arr_errors);
    }

    protected function clearModelAttributes()
    {
    $attributes = $this->model->safeAttributes();

        foreach ( $attributes as $key => $value ) {
            $this->model->$value = '';
        }

}

    public function clearErrors(){

      $this->arr_errors = [];

    }

    public function close(){

        $this->valid_data = [];
        $this->clearErrors();
        $this->total_rows = 0;

    }
}