<?php
namespace backend\models;

use yii\base\ErrorException;
use yii\base\Model;
use yii\web\UploadedFile;
use Yii;
use common\components\CustomVarDamp;

/**
 * UploadForm is the model behind the upload form.
 */
class UploadFileParsingForm extends Model
{
    /**
     * @var UploadedFile file attribute
     */
    // chosen file
    public $file;
    // file path after save
    public $file_path;
    // 0 - custom file (user should choose in $file field)
    // 1 - csv template file from data/template.csv
    // 2 - xml template file from data/template.xml
    // 3 - xlsx template file from data/template.xlsx
    // 4 - xls template file from data/template.xls
    // 5 - txt template file from data/template.txt
    public $file_type = 0;


    public function rules()
    {
        $client_func = <<< JS
        function(attribute, value) {
            return $('input[name=UploadFileParsingForm[file_type]]').val() == '0';
        }
JS;
        return [
            ['file_type', 'in', 'range' => range( 0, 5 ) ],
            ['file', 'required', 'when' => function(){
                return !$this->file_type;
            } , 'whenClient' => $client_func],
            [['file'], 'file', 'extensions' => ['csv', 'xlsx', 'xml', 'xls', 'txt'], 'checkExtensionByMimeType' => false ],
            ['file_path', 'safe'],

        ];
    }

    public function attributeLabels()
    {
        return [
            'file' => Yii::t('app', 'Custom file'),
        ];
    }

    public function readFile( $options = [] ){

        $data = Yii::$app->multiparser->parse( $this->file_path, $options );

        if( !is_array( $data ) || count($data) == 0 ){
            throw new ErrorException("Parser return empty array. Check file and configuration settings (config.php)");
        }

        if(  !$this->file_path  && file_exists( $this->file_path ) )
            unlink( $this->file_path );

        return $data;
    }




}