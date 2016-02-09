<?php
namespace yii\multiparser\module;

class MultiparserModule extends \yii\base\Module
{
    public $files_directory_path;
    public $configure_file;
    public $error_action;
	public function init()
	{

		parent::init();

        $this->setAliases([
            '@file_path' => $this->files_directory_path,
        ]);

		\Yii::configure( $this, require( $this->configure_file ) );

        //register custom error handler for module
        $handler = new \yii\web\ErrorHandler;
        $handler->errorAction = $this->error_action;
        \Yii::$app->set('errorHandler', $handler);
        $handler->register();
	}




}
