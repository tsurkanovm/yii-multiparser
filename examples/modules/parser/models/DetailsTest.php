<?php

namespace common\modules\parser\models;

use yii\multiparser\module\MassiveDataSQLBuilder;
use Yii;

/**
 * This is the model class for table "details_test".
 *
 * @property integer $id_details_test
 * @property string $article
 * @property string $brand
 * @property double $price
 * @property integer $count
 * @property string $name
 * @property integer $created_at
 * @property integer $updated_at
 */
class DetailsTest extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */

    public function behaviors()
    {
        return [

            'SQLBuilder' => [
                'class' => MassiveDataSQLBuilder::className(),
                'batch' => 500,
                'keys' =>  [
                    'article',
                   // 'brand',
                ],
            ]
        ];
    }

    public static function tableName()
    {
        return 'details_test';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['article', 'brand'], 'required'],
            [['price'], 'number'],
            [['count', 'created_at', 'updated_at'], 'integer'],
            [['article'], 'string', 'max' => 255],
            [['brand'], 'string', 'max' => 32],
            [['name'], 'string', 'max' => 200],
        //    [['article'], 'unique']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'articul' => 'Articul',
            'brand' => 'Brand',
            'price' => 'Price',
            'quantity' => 'Quantity',
            'name' => 'Name',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
}
