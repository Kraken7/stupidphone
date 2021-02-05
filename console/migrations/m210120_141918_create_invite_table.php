<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%invite}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%game}}`
 */
class m210120_141918_create_invite_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%invite}}', [
            'id' => $this->primaryKey(),
            'game_id' => $this->integer()->notNull(),
            'vk_id' => $this->integer()->notNull(),
        ]);

        // creates index for column `game_id`
        $this->createIndex(
            '{{%idx-invite-game_id}}',
            '{{%invite}}',
            'game_id'
        );

        // add foreign key for table `{{%game}}`
        $this->addForeignKey(
            '{{%fk-invite-game_id}}',
            '{{%invite}}',
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
            '{{%fk-invite-game_id}}',
            '{{%invite}}'
        );

        // drops index for column `game_id`
        $this->dropIndex(
            '{{%idx-invite-game_id}}',
            '{{%invite}}'
        );

        $this->dropTable('{{%invite}}');
    }
}
