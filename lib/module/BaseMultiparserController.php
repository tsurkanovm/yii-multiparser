<?php
namespace yii\multiparser\module;

use yii\multiparser\UploadFileParsingForm;
use Yii;
use yii\multiparser\DynamicFormHelper;
use yii\web\Controller;
use yii\web\HttpException;
use yii\web\Response;
use yii\web\Session;


/**
 * Site controller
 */
class BaseMultiparserController extends Controller
{
    public $enableCsrfValidation = false;
    // @todo при выходе со страницы после чтения (без записи) - нужно удалять файл принудительно
    public function actionIndex()
    {
        $title = $this->getScenarioParameter('title');

        $upload_form = $this->getReadUploadForm();

        return $this->render('index', [
            'options' => ['model' => $upload_form,
                'title' => $title
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

                $this->deleteParseFile();

                Yii::$app->session->setFlash( 'file_name', $file_name );
                return true;
            } else {
                return false;
            }
        }
    }

    public function actionRead()
    {
        $upload_form = $this->getReadUploadForm();
        $this->validateUploadForm( $upload_form );

        $file_path = $upload_form->file;
        $basic_columns = $this->getScenarioParameter('basic_columns');
        $last_line = ($upload_form->read_line_end == 'все') ? 0 : $upload_form->read_line_end;
        $custom_settings = ['last_line' => $last_line, 'file_path' => $file_path];

        $data = $this->parseDataBySettings($custom_settings);

        $write_upload_form = $this->getReadUploadForm();
        return $this->renderAjax('index', [
            'options' => [
                'mode' => 'data',
                'data' => $data,
                'model' => $write_upload_form,
                'basic_columns' => $basic_columns,
            ]
        ]);

    }

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

        //CustomVarDamp::dumpAndDie($data);
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

            $response->data = $this->renderAjax('index', [
                'options' => ['title' => $msg,
                    'mode' => 'message']
            ]);

            return $response;
        }
    }

    protected function parseDataBySettings($custom_settings){
        if ( empty( $custom_settings['file_path'] ) ) {
            $file_path = Yii::$app->session->getFlash('file_name');
        } else {
            $file_path = $custom_settings['file_path'];
            unset( $custom_settings['file_path'] );
        }
        $parser_config = $this->getScenarioParameter('parser_config');
        array_merge( $custom_settings, ['has_header_row' => false] );

        $parser = Yii::$app->controller->module->multiparser;
        $parser->setConfiguration( $parser_config );
        $data = $parser->parse( $file_path, $custom_settings );

        return $data;
}

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

    protected function getScenarioParameter($parameter = '')
    {
        //$scenario = Yii::$app->request->get('scenario');
        $scenario = 'details';
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

    protected function getReadUploadForm(){

        $parser_config = $this->getScenarioParameter('parser_config');
        $upload_form = new UploadFileParsingForm(['parser_config' => $parser_config]);
        $upload_form->scenario = UploadFileParsingForm::SCENARIO_READ;

        return $upload_form;
    }

    protected function getDynamicUploadForm(){

        //получим колонки которые выбрал пользователь
        $arr_attributes = Yii::$app->request->post()['DynamicModel'];
        //соберем модель по полученным данным
        $dynamic_model = DynamicFormHelper::CreateDynamicModel($arr_attributes);

        return $dynamic_model;
    }

    protected function getWriteUploadForm(){

        $upload_form = new UploadFileParsingForm();
        $upload_form->scenario = UploadFileParsingForm::SCENARIO_WRITE;

        return $upload_form;
    }

    protected function generateValidateErrorException( $model ){

        $errors_str = 'Ошибка формы загрузки данных:';
        foreach ( $model->getErrors() as $error ) {
            $errors_str .= ' ' . implode( array_values( $error ) );
        }

        throw new HttpException( 200, $errors_str );

    }
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

