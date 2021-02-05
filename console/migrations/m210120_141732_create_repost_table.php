<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%repost}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%game}}`
 */
class m210120_141732_create_repost_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%repost}}', [
            'id' => $this->primaryKey(),
            'game_id' => $this->integer()->notNull(),
            'user_ids' => $this->json()->notNull()->comment("user_ids[]"),
            'status' => $this->boolean()->notNull()->defaultValue(0)->comment("0/1 - опубликован?"),
            'date_create' => $this->timestamp()->notNull(),
        ]);

        // creates index for column `game_id`
        $this->createIndex(
            '{{%idx-repost-game_id}}',
            '{{%repost}}',
            'game_id'
        );

        // add foreign key for table `{{%game}}`
        $this->addForeignKey(
            '{{%fk-repost-game_id}}',
            '{{%repost}}',
            'game_id',
            '{{%game}}',
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
            '{{%fk-repost-game_id}}',
            '{{%repost}}'
        );

        // drops index for column `game_id`
        $this->dropIndex(
            '{{%idx-repost-game_id}}',
            '{{%repost}}'
        );

        $this->dropTable('{{%repost}}');
    }
}
