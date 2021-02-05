<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%invite}}`.
 */
class m210125_201105_add_date_create_column_to_invite_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%invite}}', 'date_create', $this->timestamp()->notNull());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%invite}}', 'date_create');
    }
}
