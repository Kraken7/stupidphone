<?php

namespace backend\modules\v1\models;


use common\models\User;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * Модель для работы с таблицей фраз
 */
class Phrase extends ActiveRecord
{
    const FIRST_PHRASES = ['Кто', 'Когда', 'Где'];

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
            [['game_id', 'user_id', 'question'], 'required'],
            [['parent_id', 'game_id', 'user_id'], 'integer'],
            [['question', 'answer'], 'string', 'min' => 1, 'max' => 255],
            ['date_create', 'match', 'pattern' => '/^\d\d\d\d-\d\d-\d\d \d\d:\d\d:\d\d$/'],
            [['parent_id', 'answer'], 'default', 'value' => null],
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
            /*'parent_id' => function() {
                return $this->parent_id ?? 0;
            },
            'game_id',*/
            'user' => function() {
                return $this->user->vk_id;
            },
            'question',
            'answer' => function() {
                return $this->answer ?? '';
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

    /**
     * Связь с собой
     */
    public function getParent() {
        return $this->hasOne(self::class, ['id' => 'parent_id']);
    }
}