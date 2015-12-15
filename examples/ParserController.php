<?php
namespace backend\controllers;

use backend\models\UploadFileParsingForm;
use Yii;
use yii\base\ErrorException;
use yii\data\ArrayDataProvider;
use yii\helpers\VarDumper;
use yii\multiparser\DynamicFormHelper;
use yii\web\Controller;
use yii\web\UploadedFile;


/**
 * Site controller
 */
class ParserController extends Controller
{
    /**
     * @var - string
     * file parsing extension
     */
    protected $file_extension;

    public function actionIndex($mode = 0)
    {
        $model = new UploadFileParsingForm();
        return $this->render('index', ['model' => $model]);
    }

    public function actionRead()
    {
        $model = new UploadFileParsingForm();
        $data = [];
        $mode = '';
        if ($model->load(\Yii::$app->request->post())) {
            if (!$model->file_type) {
                $model->file = UploadedFile::getInstance($model, 'file');
            }
            if ($model->validate()) {
                // get the extension of user chosen file
                $this->file_extension = $this->getFileExtensionFromModel($model);

                if ($model->file_type) {
                    $model->file_path = dirname(dirname(__DIR__)) . '/tests/_data/template.' . $this->file_extension;
                    $mode = 'template';
                } else {
                    $mode = 'custom';
                    $model->file_path = dirname(dirname(__DIR__)) . '/tests/_data/custom_template.' . $this->file_extension;
                    $model->file->saveAs($model->file_path);
                }

                // run parsing
                $data = $model->readFile(['mode' => $mode]);

                if ($mode == 'custom' && file_exists($model->file_path)) {
                    unlink($model->file_path);
                }
                // safe parse data to cache
                Yii::$app->getCache()->set('parser_data', json_encode($data), 300);

            } else {
                // handle with error validation form
                $errors_str = 'Error upload form';
                foreach ($model->getErrors() as $error) {
                    $errors_str .= ' ' . implode(array_values($error));
                }

                throw new ErrorException($errors_str);
            }

        } elseif (Yii::$app->getCache()->get('parser_data')) {
            // it's  a get request, so retrive data from cache
            $data = json_decode(Yii::$app->getCache()->get('parser_data'), true);
        }

        return $this->renderResultView($data, $mode);
    }

    public function getFileExtensionFromModel($model)
    {
        switch ($model->file_type) {
            case 0:
                return $model->file->extension;
            case 1:
                return 'csv';
            case 2:
                return 'xml';
            case 3:
                return 'xlsx';
            default:
                return 'csv';
        }

    }

    public function renderResultView($data )
    {
        $provider = new ArrayDataProvider([
            'allModels' => $data,
            'pagination' => [
                'pageSize' => 10,
            ],
        ]);

        if ( empty( $data[0] ) ) {
            // если нет первого ряла - это xml custom-файл с вложенными узлами, массив ассоциативный (дерево),
            // такой массив нет возможности вывести с помощью GridView
            // просто выведем его как есть
            echo "<pre>";
            return print_r($data);
        }
        // если отпарсенные данные - ассоциативный массив, то пользователю нечего выбирать
        // но выведем его в GridView
        $assoc_data_arr = $this->is_assoc($data[0]);

        if ( $assoc_data_arr ) {

            // $mode == 'template' or xml file
            // парсинг с файла по шаблону
            // согласно конфигурационного файла у нас колонкам назначены ключи
            // то есть результат - ассоциативный массив, у пользователя нечего спрашивать
            // данные отконвертированы согласно настройкам и готовы к записи в БД (или к дальнейшей обработке)

            return $this->render('results',
                ['model' => $data,
                    // список колонок для выбора
                    'dataProvider' => $provider]);

        } else {
            // $mode == 'custom' and not xml
            // для произвольного файла создадим страницу предпросмотра
            // с возможностью выбора соответсвий колонок с отпарсенными данными
            //колонки для выбора возьмем из конфигурационного файла - опция - 'basic_column'

            // создадим динамическую модель на столько реквизитов сколько колонок в отпарсенном файле
            // в ней пользователь произведет свой выбор
            $last_index = end(array_flip($data[0]));
            $header_counts = $last_index + 1; // - количество колонок выбора формы предпросмотра
            $header_model = DynamicFormHelper::CreateDynamicModel($header_counts);

            // колонки для выбора возьмем из конфигурационного файла
            $basicColumns = Yii::$app->multiparser->getConfiguration($this->file_extension, 'basic_column');;

            return $this->render('results',
                ['model' => $data,
                    'header_model' => $header_model,
                    // список колонок для выбора
                    'basic_column' => $basicColumns,
                    'dataProvider' => $provider]);
        }

    }

    private function is_assoc(array $array)
    {
        // Keys of the array
        $keys = array_keys($array);

        // If the array keys of the keys match the keys, then the array must
        // not be associative (e.g. the keys array looked like {0:0, 1:1...}).
        return array_keys($keys) !== $keys;
    }

}
