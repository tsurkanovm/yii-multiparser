<?php
/**
 * Created by PhpStorm.
 * User: Tsurkanov
 * Date: 20.10.2015
 * Time: 16:24
 */

namespace yii\multiparser;


class ObjectCreator {
    public static function build( array $configuration ){
        if ( isset( $configuration['class'] ) ) {
            $class =  trim( $configuration['class'] );
            unset( $configuration['class'] );
        } else{
            return null;
        }

        $object = new $class();
        foreach ($configuration as $name => $value) {
            $object->$name = $value;
        }

        return $object;
    }
}