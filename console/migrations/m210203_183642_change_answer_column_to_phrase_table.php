<?php

use yii\db\Migration;

/**
 * Class m210203_183642_change_answer_column_to_phrase_table
 */
class m210203_183642_change_answer_column_to_phrase_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn('{{%phrase}}', 'answer', $this->string());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->alterColumn('{{%phrase}}', 'answer', $this->string()->notNull());
    }
}
