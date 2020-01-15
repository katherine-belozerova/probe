<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%employee}}`.
 */
class m200114_103934_add_access_token_column_to_employee_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%employee}}', 'access_token', $this->string());
        $this->update('{{%employee}}', array(
              'access_token' => Yii::$app->security->generateRandomString(24)), 
              'id=1'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%employee}}', 'access_token');
    }
}
