<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%access}}`.
 */
class m200114_091742_create_access_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%access}}', [
            'id' => $this->primaryKey(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%access}}');
    }
}
