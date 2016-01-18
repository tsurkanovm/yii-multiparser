<?php
/**
 * Created by PhpStorm.
 * User: Cibermag
 * Date: 07.09.2015
 * Time: 15:56
 */

namespace yii\multiparser;

use yii\base\Component;




class YiiMultiparser extends Component{

public $configuration;
public $parserHandler;
//public $file_path;

    public function init()
    {
        parent::init();
        $this->parserHandler = new YiiParserHandler( );
        if ( !empty( $this->configuration ) ) {
            $this->setConfiguration( $this->configuration );
        }
    }


    public function parse( $filePath, $options = [] ){

        $this->parserHandler->setup( $filePath, $options );

        return $this->parserHandler->run();

    }

    public function getConfigurationByExtension( $extension, $parameter ){

        return $this->parserHandler->getCustomConfiguration( $extension, $parameter );

    }

    public function setConfiguration( $config ){

        return  $this->parserHandler->setConfiguration( $config );

    }

}