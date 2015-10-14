<?php
/**
 * Created by PhpStorm.
 * User: Cibermag
 * Date: 07.09.2015
 * Time: 15:56
 */

namespace yii\multiparser;

use common\components\CustomVarDamp;
use yii\base\Component;




class YiiMultiparser extends Component{

public $configuration;

    public function getConfiguration($extension, $conf_parameter){

        if (!isset( $this->configuration[$extension] )){
            throw new \ErrorException( "Parser do not maintain file with extension  {$extension}");
        }
        if (!isset( $this->configuration[$extension][$conf_parameter] )){
            throw new \ErrorException( "Parser configurator do not have settings for {$conf_parameter} parameter");
        }

        return $this->configuration[$extension][$conf_parameter];

    }


    public function parse( $filePath, $options = [] ){

        $parser = new YiiParserHandler( $filePath, $options );
        return $parser->run();

    }


}