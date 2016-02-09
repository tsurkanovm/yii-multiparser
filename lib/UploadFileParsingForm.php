<?php
namespace yii\multiparser;

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
    // how many rows of readed array we showed to user
    public $read_line_end;
    // first row to write in DB
    public $write_line_begin;
    // last row to write in DB
    public $write_line_end;
    // int(0,1) for define specific write action - update or write
    public $update = 0;

    // attribute for parser extensions
    protected $extensions;
    // parser configuration, use for define parser extensions
    public $parser_config;

    const SCENARIO_READ = 'read';
    const SCENARIO_WRITE = 'write';

    public function __construct($config = [])
    {
        parent::__construct($config);
        if ( !empty( $this->parser_config ) ) {
            $this->extensions = array_keys( $this->parser_config );
        }

    }



    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios[self::SCENARIO_READ] = ['read_line_end', 'file'];
        $scenarios[self::SCENARIO_WRITE] = ['write_line_begin', 'write_line_end', 'update'];
        return $scenarios;
    }

    public function rules()
    {
        return [
            ['read_line_end', 'in', 'range' => [10, 100, 'все'], 'message' => 'Не верное значение поля \'Показать\'' ],
            ['file', 'required', 'message' => 'Не указан файл'],
            [['file'], 'file', 'extensions' =>  $this->extensions, 'checkExtensionByMimeType' => false ],
            ['write_line_begin' , 'integer', 'message' => 'Не верное значение поля \'Обрабатывать строки с\''],
            ['write_line_end' , 'integer', 'message' => 'Не верное значение поля \'Обрабатывать строки по\''],
            ['write_line_begin', 'compare', 'type' => 'number', 'compareAttribute' => 'write_line_end', 'operator' => '<=' ],//'message' => ' \'Обрабатывать строки с\' должно быть меньше значения поля \'Обрабатывать строки по\''],
        ];
    }

    public function attributeLabels()
    {
        return [
           // 'file' => Yii::t('app', 'Custom file'),
            'read_line_end' => Yii::t('app', 'Показать:'),
            'write_line_begin' => Yii::t('app', 'Обрабатывать строки с:'),
            'write_line_end' => Yii::t('app', 'по:'),
        ];
    }
}