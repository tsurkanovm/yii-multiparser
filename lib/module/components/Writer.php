<?php
/**
 * Created by PhpStorm.
 * User: Cibermag
 * Date: 30.09.2015
 * Time: 9:34
 */

namespace yii\multiparser;



abstract class Writer
{
    /**
     * @var - массив с данными которые нужно записать
     */
    protected $data;

    /**
     * @var - сообщение валидатора об ошибках
     */
    protected $validated_msg;
    /**
     * @var - bool - есть ли ошибки валидации
     */
    protected $hasValidationError;
    /**
     * @var - array - list of models (active records) - when we write to
     */
    protected $models = [];
    /**
     * @var - object that implements MassiveDataValidatorInterface for validation data
     */
    protected $validator;


    public function __construct( $data )
    {
        set_time_limit(600);
        $this->data = $data;
        $this->setModels();
        $this->setValidator();
    }

   abstract protected function setModels();

   abstract protected function setValidator();

   abstract protected function writeToDB( $with_update );


    /**
     * @return mixed
     */
    public function getValidatedMsg()
    {
        return $this->validated_msg;
    }

    /**
     * @return mixed
     */
    public function hasValidationError()
    {
        return $this->hasValidationError;
    }

    public function checkValidator()
    {
        if ( !($this->validator instanceof MassiveDataValidatorInterface) ) {
            throw new \Exception( 'Set incorrect validator' );
        }
    }


    public function write( $update = false )
    {
        //3. провалидируем полученные данные моделью - Details
        $this->validateByModels();
        if ( empty($this->data) ) {
            // после валидации не осталось валидных данных для записи
            return false;
        }
            //5. запишем данные в связанные таблицы
            $this->writeToDB( $update );

        return true;
    }

    protected function validateByModels(){

        foreach ( $this->models as $model ) {

            $this->validator->setModel( $model );
            $this->data = $this->validator->validate( $this->data );
            $this->validated_msg = $this->validated_msg . $this->validator->getMassage();
            $this->hasValidationError =  $this->validator->hasError();

        }

        $this->validator->close();

    }
}