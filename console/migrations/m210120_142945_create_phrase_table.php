<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%phrase}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%phrase}}`
 * - `{{%game}}`
 * - `{{%user}}`
 */
class m210120_142945_create_phrase_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%phrase}}', [
            'id' => $this->primaryKey(),
            'parent_id' => $this->integer()->comment("null - первая в игре"),
            'game_id' => $this->integer()->notNull(),
            'user_id' => $this->integer()->notNull(),
            'question' => $this->string()->notNull(),
            'answer' => $this->string()->notNull(),
            'date_create' => $this->timestamp()->notNull(),
        ]);

        // creates index for column `parent_id`
        $this->createIndex(
            '{{%idx-phrase-parent_id}}',
            '{{%phrase}}',
            'parent_id'
        );

        // add foreign key for table `{{%phrase}}`
        $this->addForeignKey(
            '{{%fk-phrase-parent_id}}',
            '{{%phrase}}',
            'parent_id',
            '{{%phrase}}',
            'id',
            'CASCADE'
        );

        // creates index for column `game_id`
        $this->createIndex(
            '{{%idx-phrase-game_id}}',
            '{{%phrase}}',
            'game_id'
        );

        // add foreign key for table `{{%game}}`
        $this->addForeignKey(
            '{{%fk-phrase-game_id}}',
            '{{%phrase}}',
            'game_id',
            '{{%game}}',
            'id',
            'CASCADE'
        );

        // creates index for column `user_id`
        $this->createIndex(
            '{{%idx-phrase-user_id}}',
            '{{%phrase}}',
            'user_id'
        );

        // add foreign key for table `{{%user}}`
        $this->addForeignKey(
            '{{%fk-phrase-user_id}}',
            '{{%phrase}}',
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
        // drops foreign key for table `{{%phrase}}`
        $this->dropForeignKey(
            '{{%fk-phrase-parent_id}}',
            '{{%phrase}}'
        );

        // drops index for column `parent_id`
        $this->dropIndex(
            '{{%idx-phrase-parent_id}}',
            '{{%phrase}}'
        );

        // drops foreign key for table `{{%game}}`
        $this->dropForeignKey(
            '{{%fk-phrase-game_id}}',
            '{{%phrase}}'
        );

        // drops index for column `game_id`
        $this->dropIndex(
            '{{%idx-phrase-game_id}}',
            '{{%phrase}}'
        );

        // drops foreign key for table `{{%user}}`
        $this->dropForeignKey(
            '{{%fk-phrase-user_id}}',
            '{{%phrase}}'
        );

        // drops index for column `user_id`
        $this->dropIndex(
            '{{%idx-phrase-user_id}}',
            '{{%phrase}}'
        );

        $this->dropTable('{{%phrase}}');
    }
}
