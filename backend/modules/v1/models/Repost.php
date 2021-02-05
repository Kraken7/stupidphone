<?php

namespace backend\modules\v1\models;


use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * Модель для работы с таблицей репостов
 */
class Repost extends ActiveRecord
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
            [['game_id', 'user_ids'], 'required'],
            ['game_id', 'integer'],
            ['user_ids', 'string', 'max' => 1000],
            ['date_create', 'match', 'pattern' => '/^\d\d\d\d-\d\d-\d\d \d\d:\d\d:\d\d$/'],
            ['status', 'default', 'value' => 0],
        ];
    }

    /**
     * Связь с Game
     */
    public function getGame() {
        return $this->hasOne(Game::class, ['id' => 'game_id']);
    }
}