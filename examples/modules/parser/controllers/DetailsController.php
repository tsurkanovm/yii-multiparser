<?php
/**
 * Created by PhpStorm.
 * User: Mihail
 * Date: 09.02.2016
 * Time: 10:13
 */
namespace common\modules\parser\controllers;

class DetailsController extends \yii\multiparser\module\BaseMultiparserController{
    // set scenario and action for current controller
    protected $scenario = 'details';
    protected $action_save = ['details/save'];
    protected $action_read = ['details/read'];
    protected $action_write = ['details/write'];
}