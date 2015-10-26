<?php
/**
 * Created by PhpStorm.
 * User: Tsurkanov
 * Date: 20.10.2015
 * Time: 13:38
 */

namespace yii\multiparser;


interface ConverterInterface {

    public static function convertByConfiguration( $arr_values_to_convert, $configuration );

}