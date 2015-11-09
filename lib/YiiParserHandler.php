<?php
/**
 * Created by PhpStorm.
 * User: Cibermag
 * Date: 07.09.2015
 * Time: 15:53
 */

namespace yii\multiparser;



class YiiParserHandler extends ParserHandler{


    /**
     * @param $filePath
     * @param array $options
     * проверяет читабельность переданного файла, а также наличие настроек парсера в конфигурационном файле для данного типа файла
     */
//    public function setup($file_path,  $options = [])
//    {
//        $this->file_path = $file_path;
//        if (isset($options['mode'])) {
//
//            $this->mode = $options['mode'];
//            unset($options['mode']);
//
//        } else {
//
//            $this->mode = self::DEFAULT_MODE;
//
//        }
//
//        $this->options = $options;
//
//        try {
//            $this->fileObject = new \SplFileObject($this->file_path, 'r');
//        } catch (\ErrorException $e) {
//            //  Yii::warning("Ошибка открытия файла {$this->file_path}");
//            echo "Ошибка открытия файла {$this->file_path}";
//            return [];
//        }
//
//        $options['file'] = $this->fileObject;
//        $this->extension = $this->fileObject->getExtension();
//
//        try {
//
//            $this->configuration = array_merge_recursive ($this->configuration, $options);
//
//        } catch (\ErrorException $e) {
//            echo $e->getMessage();
//            return [];
//        }
//
//    }
//
//    public function run()
//    {
//
//        $result = [];
//
//        // \common\components\CustomVarDamp::dumpAndDie($this);
//        if (count($this->configuration)) {
//            $parser = \Yii::createObject($this->configuration);
//
//            try {
//
//                $parser->setup();
//                $result = $parser->read();
//
//            } catch (\ErrorException $e) {
//
//                echo $e->getMessage();
//
//            }
//
//        }
//
//        return $result;
//    }
    protected function createObjectByConfiguration($configuration)
    {
        return \Yii::createObject($configuration);
    }


}