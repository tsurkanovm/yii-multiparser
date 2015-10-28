<?php
/**
 * Created by PhpStorm.
 * User: Cibermag
 * Date: 07.09.2015
 * Time: 15:56
 */

namespace yii\multiparser;

use yii\base\Component;
use yii\base\ErrorException;


class YiiConverter extends Component{

public $configuration;
public $converter;

    public function init()
    {
        parent::init();
        $converter = \Yii::createObject( $this->configuration );
        if ( $converter instanceof ConverterInterface ) {

            $this->converter = $converter;
        }else{
            throw new ErrorException('Wrong type of converter');
        }


    }

    public function convertTo( $method, $value, $attributes = [] ){

        if ( $attributes ) {
            $this->converter->setAttributes($attributes);
        }
        return $this->converter->$method( $value );

    }

    public function convertByConfiguration( $value, $configuration ){

        return $this->converter->convertByConfiguration( $value, $configuration );

    }


}