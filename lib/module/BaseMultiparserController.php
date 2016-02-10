<?php
namespace yii\multiparser\module;

use yii\multiparser\UploadFileParsingForm;
use Yii;
use yii\multiparser\DynamicFormHelper;
use yii\web\Controller;
use yii\web\HttpException;
use yii\web\Response;
use yii\web\Session;

// @todo при выходе со страницы после чтения (без записи) - нужно удалять файл принудительно
/**
 * Class BaseMultiparserController
 * @package yii\multiparser\module
 * base controller for parsing and writing data to DB
 * this class should be inherited by custom controller class with specific scenario
 */
class BaseMultiparserController extends Controller
{
    /**
     * @var - string - name of current scenario. Should be set in child custom controller
     * this attribute has used in getScenarioParameter method for define current scenario parameters
     */
    protected $scenario = '';
    /**
     * @var - array - path to save file action (e.g. - ['details/save']). Should be set in child custom controller
     * this attribute has used in ParserView widget
     */
    protected $action_save = [];
    /**
     * @var - array - path to read (parse) file action (e.g. - ['details/read']). Should be set in child custom controller
     * this attribute has used in ParserView widget
     */
    protected $action_read = [];
    /**
     * @var - array - path to write data action to DB (e.g. - ['details/write']). Should be set in child custom controller
     * this attribute has used in ParserView widget
     */
    protected $action_write = [];

    //public $enableCsrfValidation = false;

    /**
     * checkup required attributes
     * @throws HttpException
     */
    public function init()
    {

        if ( empty( $this->action_save ) ){
            throw new HttpException(200, 'Ошибка контроллера. Не передано имя метода для сохранения файла (action).');
        }
        if ( empty( $this->action_read ) ){
            throw new HttpException(200, 'Ошибка виджета. Не передано имя метода для чтения данных (action).');
        }
        if ( empty( $this->action_write ) ){
            throw new HttpException(200, 'Ошибка виджета. Не передано имя метода записи данных (action).');
        }

        parent::init();
    }

    public function actionIndex()
    {
        $title = $this->getScenarioParameter('title');

        $upload_form = $this->getReadUploadForm();

        return $this->render('index', [
            'options' => ['model' => $upload_form,
                'title' => $title,
                'action_save' => $this->action_save,
                'action_read' => $this->action_read
            ],
        ]);
    }

    /**
     * @return bool
     * action save file to disc (files folder in parser module)
     */
    public function actionSave()
    {
        if (empty($post) && !empty($_FILES)) {
            $file_name = Yii::$aliases['@file_path'] . '/' . basename($_FILES[0]['name']);
            if (move_uploaded_file($_FILES[0]['tmp_name'], $file_name )) {
                // delete old file that was read in previous session
                $this->deleteParseFile();
                // safe file name in sessions params for further usage
                Yii::$app->session->setFlash( 'file_name', $file_name );
                return true;
            } else {
                return false;
            }
        }
    }

    /**
     * @return mixed
     * @throws HttpException
     * action that read file and rendering view with result (interact with ParserView widget)
     */
    public function actionRead()
    {
        // get form and validate user data
        $upload_form = $this->getReadUploadForm();
        $this->validateUploadForm( $upload_form );

        // collect params for parser
        $file_path = $upload_form->file;
        $basic_columns = $this->getScenarioParameter('basic_columns');
        $last_line = ($upload_form->read_line_end == 'все') ? 0 : $upload_form->read_line_end;
        $custom_settings = ['last_line' => $last_line, 'file_path' => $file_path];

        // run parser
        $data = $this->parseDataBySettings($custom_settings);

        $read_upload_form = $this->getReadUploadForm();
        return $this->renderAjax('index', [
            'options' => [
                'mode' => 'data',
                'data' => $data,
                'model' => $read_upload_form,
                'basic_columns' => $basic_columns,
                'action_write' => $this->action_write
            ]
        ]);

    }

    /**
     * @return string
     * @throws HttpException
     * write parsed data from file to DB by scenario
     * used writer class that was defined in 'writer' module param
     */
    public function actionWrite(){
        // validation static fields
        $model = $this->getWriteUploadForm();
        $this->validateUploadForm($model);

        // validation dynamic fields
        $dynamic_model = $this->getDynamicUploadForm();
        $this->validateDynamicUploadForm($dynamic_model);

       // prasing settings
        $first_line = (!$model->write_line_begin) ? 0 : $model->write_line_begin;
        $last_line = (!$model->write_line_end) ? 0 : $model->write_line_end + 1;
        $custom_settings = ['last_line' => $last_line,
                            'first_line' => $first_line,
        ];

        $data = $this->parseDataBySettings($custom_settings);

        // у нас есть соответсвие колонок, преобразуем в массив данное соответсвие для дальнейшей работы
        $arr = $dynamic_model->toArray();
        // соотнесем отпарсенные данные с соответсивем полученным от пользователя
        // для этого преобразуем массив отпарсенных данных - назначим ключи согласно соответствию
        $data = $this->createAssocArray( $data, $arr , 'attr_' );

        $this->deleteParseFile();

        $writer_class = $this->getScenarioParameter('writer');
        $writer = new $writer_class( $data );
        try {
            $writer->write( $model->update );
            $log = $writer->getValidatedMsg();
            $has_error = $writer->hasValidationError();
        } catch (\Exception $e) {
            $log = 'Ошибка записи данных в базу данных ' . $e->getMessage();
            $has_error = true;
        }

        $msg = $this->renderAjax('index', [
            'options' => [
                'mode' => 'message',
                'title' => $log,
            ]
        ]);
        $response_array = ['msg' => $msg, 'error' => $has_error];

        $response = json_encode( $response_array );

        return $response;
    }

    /**
     * @return Response
     * provide action for any error in the module
     * controller interact with view (ParserView widget) by ajax
     * and all data and errors shown on main page
     */
    public function actionError()
    {
        $exception = Yii::$app->errorHandler->exception;

        if ($exception !== null) {
            $msg = $exception->getMessage();

            if (Yii::$app->has('response')) {
                $response = Yii::$app->getResponse();
            } else {
                $response = new Response();
            }
            // all errors we show on main page that widget provides
            // so prepare data accordingly (like any other action response)
            $response->data = $this->renderAjax('index', [
                'options' => ['title' => $msg,
                    'mode' => 'message']
            ]);

            return $response;
        }
    }

    /**
     * @param array $custom_settings
     * @return array with parsed data
     * @throws HttpException
     */
    protected function parseDataBySettings( array $custom_settings ){
        if ( empty( $custom_settings['file_path'] ) ) {
            $file_path = Yii::$app->session->getFlash('file_name');
        } else {
            $file_path = $custom_settings['file_path'];
            unset( $custom_settings['file_path'] );
        }
        // read global parser setting from module param
        $parser_config = $this->getScenarioParameter('parser_config');
        // merge global and custom settings (first and last line settings) for parser
        array_merge( $custom_settings, ['has_header_row' => false] );

        // get parser component
        $parser = Yii::$app->controller->module->multiparser;
        // setup configuration
        $parser->setConfiguration( $parser_config );
        // run parser
        $data = $parser->parse( $file_path, $custom_settings );

        return $data;
}

    /**
     * @param $model
     * @throws HttpException
     * set file attribute from session and validate form
     */
    protected function validateUploadForm(&$model)
    {
        if ($model->load(Yii::$app->request->post())) {
            if( isset( $model->file ) ){
                $model->file = Yii::$app->session->getFlash('file_name');
                Yii::$app->session->setFlash( 'file_name', $model->file );
            }

            if (!$model->validate()) {
                // handle with error validation form
                $this->generateValidateErrorException($model);
            }
        } else {

            throw new HttpException(200, 'Ошибка загрузки данных в форму');
        }

    }


    /**
     * @param $dynamic_model
     * @throws HttpException
     * validate dynamic model
     */
    protected function validateDynamicUploadForm(&$dynamic_model){

        $arr_attributes = $dynamic_model->toArray();

        $require_columns = $this->getScenarioParameter('require_columns');
        $basic_columns = $this->getScenarioParameter('basic_columns');

        //добавим правила валидации (колонки должны быть те что указаны в конфиге)
        foreach ($arr_attributes as $key => $value) {
            $dynamic_model->addRule($key, 'in', ['range' => array_keys($basic_columns)]);
            // ищем наличие обязательных колонок
            $find_key = array_search( $value, $require_columns );
            if( $find_key !== false){
                unset( $require_columns[$find_key] );
            }
        }
        if( $require_columns ) {
            throw new HttpException( 200, implode(' - обязательное поле, укажите соответствие, ', $require_columns) . ' - обязательное поле, укажите соответствие' );
        }
        if (!$dynamic_model->validate()) {
            // handle with error validation form
            $this->generateValidateErrorException($dynamic_model);
        }
    }

    /**
     * @param string $parameter - that we want to read
     * @return mixed - value of parameter for current scenario
     * @throws HttpException
     */
    protected function getScenarioParameter($parameter = '')
    {
        // get current scenario
        $scenario = $this->scenario;
        // get current module params
        $configuration = Yii::$app->controller->module->params;
        if (empty($configuration['scenarios_config'])) {
            throw new HttpException(200, 'В модуле не определены настройки сценариев - module->params[\'scenarios_config\']');
        }
        $configuration = $configuration['scenarios_config'];
        if (empty($configuration[$scenario])) {
            throw new HttpException(200, "Модуль не поддерживает указанный сценарий  {$scenario}");
        }
        if ($parameter && empty($configuration[$scenario][$parameter])) {
            throw new HttpException(200, "Сценарий {$scenario} не содержит настройку {$parameter}");
        }

        if ($parameter) {
            return $configuration[$scenario][$parameter];
        } else {
            return $configuration[$scenario];
        }

    }

    /**
     * @return UploadFileParsingForm in a read mode
     * @throws HttpException - if 'parser_config' don't set for current scenario
     */
    protected function getReadUploadForm(){

        $parser_config = $this->getScenarioParameter('parser_config');
        $upload_form = new UploadFileParsingForm(['parser_config' => $parser_config]);
        $upload_form->scenario = UploadFileParsingForm::SCENARIO_READ;

        return $upload_form;
    }

    /**
     * @return UploadFileParsingForm in a write mode
     */
    protected function getWriteUploadForm(){

        $upload_form = new UploadFileParsingForm();
        $upload_form->scenario = UploadFileParsingForm::SCENARIO_WRITE;

        return $upload_form;
    }

    /**
     * @return \yii\base\DynamicModel - with filled attributes from user (post)
     */
    protected function getDynamicUploadForm(){

        //получим колонки которые выбрал пользователь
        $arr_attributes = Yii::$app->request->post()['DynamicModel'];
        //соберем модель по полученным данным
        $dynamic_model = DynamicFormHelper::CreateDynamicModel($arr_attributes);

        return $dynamic_model;
    }

    /**
     * @param $model
     * @throws HttpException
     * if a given model has errors - this errors converting into string and threw by HttpException
     */
    protected function generateValidateErrorException( $model ){

        $errors_str = 'Ошибка формы загрузки данных:';
        foreach ( $model->getErrors() as $error ) {
            $errors_str .= ' ' . implode( array_values( $error ) );
        }

        throw new HttpException( 200, $errors_str );

    }

    /**
     * if in current session file was already has read - clear session flash and delete the file
     */
    protected function deleteParseFile(){

        if (Yii::$app->session->hasFlash('file_name')) {
            $file_path = Yii::$app->session->getFlash('file_name');
            if(file_exists($file_path))
                unlink($file_path);
        }
    }

    /**
     * @param $value_arr - двумерный массив значений, которому нужно присвоить ключи
     * @param $key_array - ключи для вложенного массива
     * @return array - таблица с проименованными колонками
     */
    protected function createAssocArray(array $value_arr, array $key_array, $key_prefix = '')
    {
        // очистка служебного префикса в массиве заголовков
        if ($key_prefix) {
            // @todo оптимизировать - два переворота массива - избыточно
            $key_array = array_flip($key_array);

            array_walk($key_array, function (&$value, $key, $key_prefix) {
                $value = str_replace($key_prefix, '', $value);
            }, $key_prefix);

            $key_array = array_flip($key_array);
            //уберем пустые элементы
            $key_array = array_filter($key_array, function ($value) {
                return $value !== '';
            });
        }
        array_walk( $value_arr,

            function (&$value, $key, $key_array) {
                $res = $value;
                foreach ($res as $sub_key => $sub_value) {
                    if (isset($key_array[$sub_key])) {
                        // если такой ключ в базовом массиве (массиве ключей) есть, то заменим новым, иначе просто удалим
                        $new_key = $key_array[$sub_key];
                        if (!array_key_exists($new_key, $res)) {
                            $res[$new_key] = $value[$sub_key];
                        }
                    }
                    unset($res[$sub_key]);
                    $value = $res;
                }

            },

            $key_array );

        return $value_arr;
    }


}

