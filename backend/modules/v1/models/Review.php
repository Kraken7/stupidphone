<?php

namespace backend\modules\v1\models;


use common\models\User;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * Модель для работы с таблицей отзывов
 */
class Review extends ActiveRecord
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
            [['game_id', 'user_id', 'text'], 'required'],
            [['game_id', 'user_id'], 'integer'],
            ['text', 'string', 'max' => 5000],
            ['date_create', 'match', 'pattern' => '/^\d\d\d\d-\d\d-\d\d \d\d:\d\d:\d\d$/'],
        ];
    }

    /**
     * Определение формата полей rest-ресурса
     *
     * @return array
     */
    public function fields() {
        return [
            'id',
            'text',
            'game_id',
            'user' => function() {
                return $this->user->vk_id;
            },
            'date_create' => function() {
                return \Yii::$app->formatter->asDatetime($this->date_create);
            },
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