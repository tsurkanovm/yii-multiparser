<?php

namespace yii\multiparser;


use common\components\CustomVarDamp;

class ParserHandler
{
    //@todo - добавить комменты на анг язе (ошибки выкидывать тоже на англ яз.)
    //@todo - сделать универсальную обработку ошибок
    //@todo - возможно отказаться от YiiParserHandler
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
    protected $mode;

    /** @var string - */
    protected $options;

    /**
     * @param string first line in file for parsing
     */
    public function setup($file_path, $options = [])
    {
       //$this->file_path = $file_path;
        if (isset($options['mode'])) {

            $this->mode = $options['mode'];
            unset($options['mode']);

        } else {

            $this->mode = self::DEFAULT_MODE;

        }

        $this->options = $options;
        $this->file = fopen($file_path, 'r');
        $options['file'] = $this->file;
        $options['file_path'] = $file_path;
        $this->extension = pathinfo( $file_path, PATHINFO_EXTENSION );
        $this->custom_configuration = $this->getCustomConfiguration($this->extension, $this->mode);
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

    public function getCustomConfiguration($extension, $parameter)
    {
        if (!count($this->configuration)) {
            $this->setConfiguration(require(__DIR__ . '/config.php'));
        }

        if (!isset($this->configuration[$extension])) {
            throw new \ErrorException("Parser do not maintain file with extension  {$extension}");
        }
        if (!isset($this->configuration[$extension][$parameter])) {
            throw new \ErrorException("Parser configurator do not have settings for {$parameter} parameter");
        }

        return $this->configuration[$extension][$parameter];
    }

    public function setConfiguration($configuration)
    {
        $this->configuration = $configuration;
    }

    protected function createObjectByConfiguration($configuration)
    {
        return ObjectCreator::build($configuration);
    }




}


