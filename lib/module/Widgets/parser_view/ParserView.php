<?php
/**
 * Created by PhpStorm.
 * User: Tsurkanov
 * Date: 13.01.2016
 * Time: 13:51
 */

namespace yii\multiparser\widgets;


use yii\base\Widget;
use yii\data\ArrayDataProvider;
use yii\multiparser\DynamicFormHelper;
use yii\web\HttpException;

/**
 * Class ParserView
 * @package yii\multiparser\widgets
 * widget that provides ajax interaction with inheritors of BaseMultiparserController
 *
 */
class ParserView extends Widget{
    public $options;

    public function init()
    {
        parent::init();
    }

    public function run()
    {
        if ( empty( $this->options['mode'] ) ) {
            $view = 'frame';
        } else{
            $view = $this->options['mode'];
        }

        if ( $view === 'data') {
            return $this->renderDataView();
        } else {
            return $this->renderSimpleView($view);
        }

    }

    protected function renderSimpleView($view)
    {
        return $this->render($view,
            [
                'params' => $this->options,
            ]);
    }

    /**
     * create and rendered view with parsed data
     * @return mixed
     * @throws HttpException
     */
    protected function renderDataView()
    {
        $data = [];
        if ( !empty( $this->options['data'] ) ) {
            $data = $this->options['data'];
        }
        $basic_columns = [];
        if ( !empty( $this->options['basic_columns'] ) ) {
            $basic_columns = $this->options['basic_columns'];
        }

        if ( empty( $this->options['model'] ) ) {
            throw new HttpException(200, 'Ошибка виджета. Не передана модель для валидации записываемых данных.');
        }
        $model = $this->options['model'];

        if ( empty( $this->options['action_write'] ) ) {
            throw new HttpException(200, 'Ошибка виджета. Не передано имя метода записи данных (action).');
        }
        $action_write = $this->options['action_write'];

        $provider = new ArrayDataProvider([
            'allModels' => $data,
        ]);

        if ( empty( $data[0] ) ) {
            // если нет первого ряда - это xml custom-файл с вложенными узлами, дерево,
            // такой массив нет возможности вывести с помощью GridView
            // просто выведем его как есть
            echo "<pre>";
            return print_r($data);
        }

        // создадим динамическую модель на столько реквизитов сколько колонок в отпарсенном файле
        // в ней пользователь произведет свой выбор
        $header_model = $this->createDynamicModel( $data[0] );

        return $this->render('data',
            ['model' => $data,
                'header_model' => $header_model,
                // список колонок для выбора
                'basic_column' => $basic_columns,
                'write_model' => $model,
                'action_write' => $action_write,
                'dataProvider' => $provider]);
    }

    /**
     * create a dynamic model by given header
     * @param array $header
     * @return \yii\base\DynamicModel
     */
    protected function createDynamicModel( array $header ){
        $last_index = end( array_flip( $header ) );
        $header_counts = $last_index + 1; // - количество колонок выбора формы предпросмотра
        $header_model = DynamicFormHelper::CreateDynamicModel( $header_counts );

        return $header_model;
    }
}