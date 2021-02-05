<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%message}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%game}}`
 * - `{{%user}}`
 */
class m210120_142324_create_message_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%message}}', [
            'id' => $this->primaryKey(),
            'game_id' => $this->integer()->notNull(),
            'user_id' => $this->integer()->comment("null - система"),
            'text' => $this->text()->notNull(),
            'date_create' => $this->timestamp()->notNull(),
        ]);

        // creates index for column `game_id`
        $this->createIndex(
            '{{%idx-message-game_id}}',
            '{{%message}}',
            'game_id'
        );

        // add foreign key for table `{{%game}}`
        $this->addForeignKey(
            '{{%fk-message-game_id}}',
            '{{%message}}',
            'game_id',
            '{{%game}}',
            'id',
            'CASCADE'
        );

        // creates index for column `user_id`
        $this->createIndex(
            '{{%idx-message-user_id}}',
            '{{%message}}',
            'user_id'
        );

        // add foreign key for table `{{%user}}`
        $this->addForeignKey(
            '{{%fk-message-user_id}}',
            '{{%message}}',
            'user_id',
            '{{%user}}',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // drops foreign key for table `{{%game}}`
        $this->dropForeignKey(
            '{{%fk-message-game_id}}',
            '{{%message}}'
        );

        // drops index for column `game_id`
        $this->dropIndex(
            '{{%idx-message-game_id}}',
            '{{%message}}'
        );

        // drops foreign key for table `{{%user}}`
        $this->dropForeignKey(
            '{{%fk-message-user_id}}',
            '{{%message}}'
        );

        // drops index for column `user_id`
        $this->dropIndex(
            '{{%idx-message-user_id}}',
            '{{%message}}'
        );

        $this->dropTable('{{%message}}');
    }
}
