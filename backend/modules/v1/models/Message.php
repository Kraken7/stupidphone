<?php

namespace backend\modules\v1\models;


use common\models\User;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * Модель для работы с таблицей сообщений
 */
class Message extends ActiveRecord
{
    public function behaviors() {
        return [
            [
                'class' => TimestampBehavior::class,
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['date_create'],
                ],
                'value' => new Expression('NOW()'),
            ],
        ];
    }

    /**
     * Правила валидации полей
     *
     * @return array
     */
    public function rules() {
        return [
            [['game_id', 'text'], 'required'],
            [['game_id', 'user_id'], 'integer'],
            ['text', 'string', 'max' => 1000],
            ['date_create', 'match', 'pattern' => '/^\d\d\d\d-\d\d-\d\d \d\d:\d\d:\d\d$/'],
            ['user_id', 'default', 'value' => null],
        ];
    }

    /**
     * Связь с Game
     */
    public function getGame() {
        return $this->hasOne(Game::class, ['id' => 'game_id']);
    }

    /**
     * Связь с User
     */
    public function getUser() {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }
}