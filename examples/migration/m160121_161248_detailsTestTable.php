<?php

use yii\db\Schema;
use yii\db\Migration;

class m160121_161248_detailsTestTable extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%details_test}}', [
            'id_details_test' => $this->primaryKey(),
            'article' => $this->string()->notNull()->unique(),
            'brand' => $this->string(32)->notNull(),
            'price' => $this->float(10,2),
            'count' => $this->integer(10,2),
            'name' => $this->string(200),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ], $tableOptions);
    }

    public function safeDown()
    {
        $this->dropTable('{{%details_test}}');
    }

    /*
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
    }

    public function safeDown()
    {
    }
    */
}
