<?php
/**
 * Created by PhpStorm.
 * User: Cibermag
 * Date: 07.09.2015
 * Time: 15:53
 */

namespace yii\multiparser;



class YiiParserHandler {


    const DEFAULT_MODE = 'web';


    /** @var string */
    protected $configuration = [];
    /** @var string */
    protected $custom_configuration = [];

    /** @var file handle */
    protected $file;

    /** @var string - extension of file $file_path */
    protected $extension;

    /** @var string - */
    protected $options;

    /**
     * @param string first line in file for parsing
     */
    public function setup($file_path, $options = [])
    {

        $this->options = $options;
        $this->file = fopen($file_path, 'r');
        $options['file'] = $this->file;
        $options['file_path'] = $file_path;
        $this->extension = pathinfo( $file_path, PATHINFO_EXTENSION );
        $this->custom_configuration = $this->getCustomConfiguration($this->extension);
        $this->custom_configuration = array_merge_recursive($this->custom_configuration, $options);

    }

    public function run()
    {
        $parser = $this->createObjectByConfiguration( $this->custom_configuration );

        $parser->setup();
        $result = $parser->read();

        unset($parser);
        fclose( $this->file );

        return $result;
    }

    public function getCustomConfiguration( $extension, $parameter = '' )
    {
        if ( empty( $this->configuration[$extension] ) ) {
            throw new \ErrorException("Parser do not maintain file with extension  {$extension}");
        }
        if ( $parameter && empty( $this->configuration[$extension][$parameter])) {
            throw new \ErrorException("Parser configurator do not have settings for {$parameter} parameter");
        }

        if ($parameter) {
            return $this->configuration[$extension][$parameter];
        } else {
            return $this->configuration[$extension];
        }

    }

    public function setConfiguration($configuration)
    {
        $this->configuration = $configuration;
    }

    protected function createObjectByConfiguration($configuration)
    {
        return \Yii::createObject($configuration);
    }


}