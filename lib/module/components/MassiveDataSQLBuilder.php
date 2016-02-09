<?php
/**
 * Created by PhpStorm.
 * User: Tsurkanov
 * Date: 22.01.2016
 * Time: 12:45
 */

namespace yii\multiparser;


use yii\base\Behavior;
use Yii;

class MassiveDataSQLBuilder extends Behavior{
    /**
     * int - размер пакета запроса
     */
    public $batch;

    public $keys;


    /**
     * вставка данных с апдейтом прямым запросом SQL
     * @param $data - массив вставляемых данный, вставка будет прозводится пакетами размером указанным в константе BATCH
     * @throws \yii\db\Exception
     */
    public function manualInsertWithUpdate( $data )
    {
        $owner = $this->owner;
        $table_name = $owner::tableName();
        $keys_arr = array_keys($data[0]);
        // найдем те поля которые не являются ключами. Их нужно будет при дубляже апдейтить
        $fields_arr_to_update = array_diff( $keys_arr, $this->keys );

        $query_update = ' on duplicate key update ';
        foreach ($fields_arr_to_update as $field) {
            $query_update .= "[[{$field}]] = values([[{$field}]]),";
        }
        // удалим последнюю запятую
        $query_update = substr($query_update, 0, strlen($query_update) - 1);

        // запросы будем выполнять пакетами
        // размер пакета установлен в константе
        // разобъем массив на пакеты и будем их проходить
        $data = array_chunk($data, $this->batch);
        foreach ($data as $current_batch_array) {

            //воспользуемся пакетной вставкой от фреймворка
            $query_insert = Yii::$app->db->createCommand()->batchInsert($table_name, $keys_arr, $current_batch_array)->sql;

            // добавим фрагмент с апдейтом при дубляже
            $query = "{$query_insert} {$query_update}";
            // \common\components\CustomVarDamp::dumpAndDie($query);
            Yii::$app->db->createCommand($query)->execute();

        }
    }
    /**
     * вставка данных прямым запросом SQL
     * @param $data - массив вставляемых данный, вставка будет прозводится пакетами размером указанным в константе BATCH
     * @throws \yii\db\Exception
     */
    public function manualInsert( $data )
    {
        $owner = $this->owner;
        $table_name = $owner::tableName();
        $keys_arr = array_keys($data[0]);

        // запросы будем выполнять пакетами
        // размер пакета установлен в константе
        // разобъем массив на пакеты и будем их проходить
        $data = array_chunk($data, $this->batch);
        foreach ($data as $current_batch_array) {

            //воспользуемся пакетной вставкой от фреймворка
            $query_insert = Yii::$app->db->createCommand()->batchInsert($table_name, $keys_arr, $current_batch_array)->sql;

            // \common\components\CustomVarDamp::dumpAndDie($query);
            Yii::$app->db->createCommand($query_insert)->execute();

        }
    }

    public function manualDelete( $conditions, $params = [] )
    {
        do {
            $query = Yii::$app->db->createCommand()->delete( self::tableName(), $conditions, $params )->sql . ' Limit ' . $this->batch;
            $res = Yii::$app->db->createCommand($query)->execute();
        } while ($res);

        return true;
    }
}