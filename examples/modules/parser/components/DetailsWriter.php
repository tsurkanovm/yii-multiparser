<?php
/**
 * Created by PhpStorm.
 * User: Cibermag
 * Date: 30.09.2015
 * Time: 9:34
 */

namespace common\modules\parser\components;


use common\modules\parser\models\DetailsTest;
use yii\multiparser\module\ModelArrayValidator;
use yii\multiparser\module\Writer;

/**
 * Class PriceWriter
 * @package common\components
 * записывает в БД отпарсенные данные
 * запись происходит в несколько таблиц
 */
class DetailsWriter extends Writer
{

    public function writeToDB( $update )
    {
        $model = $this->models[0];
        if ($update) {
            $model->manualInsertWithUpdate( $this->data );
        } else {
            $model->manualInsert( $this->data );
        }

    }

    protected function setModels(){

        array_unshift( $this->models, new DetailsTest() );

    }

    protected function setValidator(){

       $this->validator = new ModelArrayValidator();

    }
}