<?php
namespace common\modules\parser;

class Module extends \yii\multiparser\module\MultiparserModule
{
    public function init(){

        $this->files_directory_path = dirname(__DIR__). '/parser/files';
        $this->configure_file       = __DIR__ . '/config.php';
        $this->error_action         = 'parser/details/error';

        parent::init();

    }
}
