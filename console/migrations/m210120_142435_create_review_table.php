<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%review}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%game}}`
 * - `{{%user}}`
 */
class m210120_142435_create_review_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%review}}', [
            'id' => $this->primaryKey(),
            'game_id' => $this->integer()->notNull(),
            'user_id' => $this->integer()->notNull(),
            'text' => $this->text()->notNull(),
            'date_create' => $this->timestamp()->notNull(),
        ]);

        // creates index for column `game_id`
        $this->createIndex(
            '{{%idx-review-game_id}}',
            '{{%review}}',
            'game_id'
        );

        // add foreign key for table `{{%game}}`
        $this->addForeignKey(
            '{{%fk-review-game_id}}',
            '{{%review}}',
            'game_id',
            '{{%game}}',
            'id',
            'CASCADE'
        );

        // creates index for column `user_id`
        $this->createIndex(
            '{{%idx-review-user_id}}',
            '{{%review}}',
            'user_id'
        );

        // add foreign key for table `{{%user}}`
        $this->addForeignKey(
            '{{%fk-review-user_id}}',
            '{{%review}}',
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
            '{{%fk-review-game_id}}',
            '{{%review}}'
        );

        // drops index for column `game_id`
        $this->dropIndex(
            '{{%idx-review-game_id}}',
            '{{%review}}'
        );

        // drops foreign key for table `{{%user}}`
        $this->dropForeignKey(
            '{{%fk-review-user_id}}',
            '{{%review}}'
        );

        // drops index for column `user_id`
        $this->dropIndex(
            '{{%idx-review-user_id}}',
            '{{%review}}'
        );

        $this->dropTable('{{%review}}');
    }
}
