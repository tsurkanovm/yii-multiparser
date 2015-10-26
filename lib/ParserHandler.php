<?php

namespace artweb\yii_multiparser;

use common\components\CustomVarDamp;

class ParserHandler
{
    //@todo - добавить комменты на анг язе (ошибки выкидывать тоже на англ яз.)
    //@todo - сделать универсальную обработку ошибок
    //@todo - возможно отказаться от YiiParserHandler
    const DEFAULT_MODE = 'web';
    /** @var string */
    protected $filePath;

    /** @var string */
    protected $configuration = [];
    /** @var string */
    protected $custom_configuration = [];

    /** @var instance of SplFileObject */
    protected $fileObject;

    /** @var string - extension of file $filePath */
    protected $extension;

    /** @var string - */
    protected $mode;

    /** @var string - */
    protected $options;

    /**
     * @param string first line in file for parsing
     */
    public function setup($filePath, $options = [])
    {
        $this->filePath = $filePath;
        if (isset($options['mode'])) {

            $this->mode = $options['mode'];
            unset($options['mode']);

        } else {

            $this->mode = self::DEFAULT_MODE;

        }

        $this->options = $options;

        $this->fileObject = new \SplFileObject($this->filePath, 'r');

        $options['file'] = $this->fileObject;
        $this->extension = $this->fileObject->getExtension();

        $this->custom_configuration = $this->getCustomConfiguration($this->extension, $this->mode);
        $this->custom_configuration = array_merge_recursive($this->custom_configuration, $options);

    }

    public function run()
    {
        $result = [];
        if (count($this->custom_configuration)) {

            $parser = $this->createObjectByConfiguration($this->custom_configuration);

            try {

                $parser->setup();
                $result = $parser->read();

            } catch (\ErrorException $e) {

                echo $e->getMessage();

            }

        }

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


