<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%game}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%user}}`
 */
class m210120_141322_create_game_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%game}}', [
            'id' => $this->primaryKey(),
            'owner_id' => $this->integer()->comment("Создатель, null - система"),
            'qty_phrase' => $this->integer()->notNull(),
            'qty_user' => $this->smallInteger()->notNull(),
            'status' => $this->tinyInteger()->notNull()->defaultValue(0)->comment("0 - создана, 1 - начата, 2 - закончена"),
            'private' => $this->boolean()->notNull()->defaultValue(0)->comment("0 - публичная, 1 - приватная"),
            'date_create' => $this->timestamp()->notNull(),
            'date_start' => $this->timestamp(),
            'date_end' => $this->timestamp(),
            'queue' => $this->json()->notNull()->comment("user_ids[]"),
            'stop' => $this->json()->notNull()->comment("user_ids[]"),
        ]);

        // creates index for column `owner_id`
        $this->createIndex(
            '{{%idx-game-owner_id}}',
            '{{%game}}',
            'owner_id'
        );

        // add foreign key for table `{{%user}}`
        $this->addForeignKey(
            '{{%fk-game-owner_id}}',
            '{{%game}}',
            'owner_id',
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
        // drops foreign key for table `{{%user}}`
        $this->dropForeignKey(
            '{{%fk-game-owner_id}}',
            '{{%game}}'
        );

        // drops index for column `owner_id`
        $this->dropIndex(
            '{{%idx-game-owner_id}}',
            '{{%game}}'
        );

        $this->dropTable('{{%game}}');
    }
}
