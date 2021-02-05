<?php

namespace backend\modules\v1\models;


use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * Модель для работы с таблицей приглашений
 */
class Invite extends ActiveRecord
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
            [['game_id', 'vk_id'], 'required'],
            [['game_id', 'vk_id'], 'integer'],
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
            'game'=> function() {
                return [
                    'qty_phrase' => $this->game->qty_phrase,
                    'qty_user' => $this->game->qty_user,
                ];
            },
            'owner' => function() {
                return $this->game->owner->vk_id ?? 0;
            },
            'users' => function() {
                $data = [];
                $users = $this->game->users;
                foreach ($users as $user) {
                    $data[] = $user['vk_id'];
                }
                return $data;
            },
            'date_create' => function() {
                return \Yii::$app->formatter->asDatetime($this->game->date_create);
            },
        ];
    }

    /**
     * Связь с Game
     */
    public function getGame() {
        return $this->hasOne(Game::class, ['id' => 'game_id']);
    }
}