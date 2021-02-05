<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%user_game}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%user}}`
 * - `{{%game}}`
 */
class m210120_142102_create_junction_table_for_user_and_game_tables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%user_game}}', [
            'user_id' => $this->integer(),
            'game_id' => $this->integer(),
            'PRIMARY KEY(user_id, game_id)',
        ]);

        // creates index for column `user_id`
        $this->createIndex(
            '{{%idx-user_game-user_id}}',
            '{{%user_game}}',
            'user_id'
        );

        // add foreign key for table `{{%user}}`
        $this->addForeignKey(
            '{{%fk-user_game-user_id}}',
            '{{%user_game}}',
            'user_id',
            '{{%user}}',
            'id',
            'CASCADE'
        );

        // creates index for column `game_id`
        $this->createIndex(
            '{{%idx-user_game-game_id}}',
            '{{%user_game}}',
            'game_id'
        );

        // add foreign key for table `{{%game}}`
        $this->addForeignKey(
            '{{%fk-user_game-game_id}}',
            '{{%user_game}}',
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
        // drops foreign key for table `{{%user}}`
        $this->dropForeignKey(
            '{{%fk-user_game-user_id}}',
            '{{%user_game}}'
        );

        // drops index for column `user_id`
        $this->dropIndex(
            '{{%idx-user_game-user_id}}',
            '{{%user_game}}'
        );

        // drops foreign key for table `{{%game}}`
        $this->dropForeignKey(
            '{{%fk-user_game-game_id}}',
            '{{%user_game}}'
        );

        // drops index for column `game_id`
        $this->dropIndex(
            '{{%idx-user_game-game_id}}',
            '{{%user_game}}'
        );

        $this->dropTable('{{%user_game}}');
    }
}
