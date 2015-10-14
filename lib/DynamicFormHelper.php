<?php
/**
 * Created by PhpStorm.
 * User: Cibermag
 * Date: 08.09.2015
 * Time: 14:50
 */

namespace yii\multiparser;

use yii\base\DynamicModel;
use yii\grid\GridView;
use yii\grid\SerialColumn;
use yii\helpers\ArrayHelper;

/**
 * Class DynamicFormHelper
 * @package backend\components\parsers
 * Содержит процедуры генерации компонентов с динамическим количеством аттрибутов
 */
class DynamicFormHelper
{

    const KEY_PREFIX = 'attr_';

    /**
     * @param $source - int or array
     * если передан массив, то создается модель с атрибутами переданными в массиве,
     * ключ - имя, значение - значение аттрибута
     * если передано число, то создается переданное количество аттрибутов с именами - attr_0, attr_1...
     */
    public static function CreateDynamicModel( $source )
    {
        $arr_keys = [];
        if (is_array($source)) {
            $arr_keys = $source;
        } elseif (is_int($source)) {

            $i = 0;
            while ($source > $i) {
                $arr_keys[] = self::KEY_PREFIX . $i;
                $i++;
            }
            array_flip($arr_keys);

        }

        $model = new DynamicModel($arr_keys);

        return $model;
    }

    // @todo add comments
    public static function CreateGridWithDropDownListHeader( $dataProvider, $form, $header_model, $arr_header_values )
    {
        $columns_config = [['class' => SerialColumn::className()]];
        $i = 0;
        foreach( $header_model as $key => $value ) {

            $columns_config[] = ['header' => $form->field($header_model, $key, ['inputOptions' => ['label' => '']])->dropDownList($arr_header_values), 'attribute' => $i];
            $i++;
        }
        $dynamic_grid_view = GridView::widget( ['dataProvider' => $dataProvider,
                                                'columns' => $columns_config ] );

        return $dynamic_grid_view;

    }

}