<?php

namespace backend\modules\v1\models;


use common\models\User;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\helpers\Json;

/**
 * Модель для работы с таблицей игр
 */
class Game extends ActiveRecord
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
            [['qty_phrase', 'qty_user'], 'required'],
            ['qty_phrase', 'integer', 'min' => 10, 'max' => 100],
            ['qty_user', 'integer', 'min' => 2, 'max' => 10],
            ['status', 'integer', 'min' => 0, 'max' => 2],
            ['owner_id', 'integer'],
            ['private', 'integer', 'min' => 0, 'max' => 1],
            [['date_create', 'date_start', 'date_end'], 'match', 'pattern' => '/^\d\d\d\d-\d\d-\d\d \d\d:\d\d:\d\d$/'],
            ['status', 'default', 'value' => 0],
            ['private', 'default', 'value' => 0],
            [['queue', 'stop'], 'default', 'value' => "[]"],
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
            'qty_phrase',
            'qty_user',
            'status',
            'private' => function() {
                return (bool)$this->private;
            },
            'date_create' => function() {
                return \Yii::$app->formatter->asDatetime($this->date_create);
            },
            'owner' => function() {
                return $this->owner->vk_id ?? 0;
            },
            'users' => function() {
                $data = [];
                $users = $this->getUsers()->all();
                foreach ($users as $user) {
                    $data[$user['id']] = $user['vk_id'];
                }
                return $data;
            },
            'queue' => function() {
                return Json::decode($this->queue);
            },
            'stop' => function() {
                return Json::decode($this->stop);
            },
        ];
    }

    /**
     * Связь с User
     */
    public function getUsers() {
        return $this->hasMany(User::class, ['id' => 'user_id'])
            ->viaTable('user_game', ['game_id' => 'id'])->indexBy('id');
    }

    /**
     * Связь с Owner(User)
     */
    public function getOwner() {
        return $this->hasOne(User::class, ['id' => 'owner_id']);
    }

    /**
     * Связь с Invite
     */
    public function getInvites() {
        return $this->hasMany(Invite::class, ['game_id' => 'id']);
    }

    /**
     * Связь с Repost
     */
    public function getRepost() {
        return $this->hasOne(Repost::class, ['game_id' => 'id']);
    }

    /**
     * Связь с Message
     */
    public function getMessages() {
        return $this->hasMany(Message::class, ['game_id' => 'id'])->orderBy("date_create DESC");
    }

    /**
     * Связь с Review
     */
    public function getReviews() {
        return $this->hasMany(Review::class, ['game_id' => 'id']);
    }

    /**
     * Связь с Phase
     */
    public function getPhrases() {
        return $this->hasMany(Phrase::class, ['game_id' => 'id']);
    }

    /**
     * Последняя фраза игры
     */
    public function getLastPhrase() {
        return $this->hasMany(Phrase::class, ['game_id' => 'id'])->orderBy('date_create DESC')->limit(1)->one();
    }

    /**
     * Проверка - есть ли связь этой игры с запрашиваемым пользователем
     *
     * @param int $user_id
     *
     * @return bool
     */
    public function isRelationUser($user_id) {
        return array_key_exists($user_id, $this->users);
    }

    /**
     * Выйти из игры
     *
     * @param User $user
     *
     * @return void
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function exit(User $user) {
        $gameUsers = $this->users;

        // убрать из игры
        $user->unlink('games', $this, true);
        unset($gameUsers[(string)\Yii::$app->user->identity->id]);

        // если он был создателем игры - назначить нового
        if (!empty($gameUsers) && $this->owner_id === \Yii::$app->user->identity->id) {
            $this->link('owner', $gameUsers[array_rand($gameUsers)]);
        }

        // если игроков не осталось и это не игра по умолчанию - удалить игру и приглашения в нее
        if (empty($gameUsers) && $this->owner_id) {
            Invite::deleteAll(['game_id' => $this->id]);
            $this->delete();
        }
    }

    /**
     * Войти в игру
     *
     * @param User $user
     *
     * @return void
     */
    public function entry(User $user) {
        $this->link('users', $user); // добавить в игру
    }

    /**
     * Создать игру
     *
     * @param int $qty_phrase
     * @param int $qty_user
     * @param int $private
     * @param User|null $user
     *
     * @return Game|bool
     */
    public static function create(int $qty_phrase, int $qty_user, int $private = 0, User $user = null) {
        $game = new self;
        $game->qty_phrase = $qty_phrase;
        $game->qty_user = $qty_user;
        $game->private = $private;
        if ($user) $game->owner_id = \Yii::$app->user->identity->id;

        if (!$game->save()) {
            return false;
        }
        return $game;
    }

    /**
     * Запуск игры.
     *
     * Изменение статуса игры и создание случайной очереди ходов игроков.
     * Создание первой фразы-вопроса.
     *
     * @return bool
     */
    public function start() {
        $this->status = 1;
        $this->date_start = date("Y-m-d H:i:s");

        // формирование очереди
        $users = array_keys($this->users);
        shuffle($users);
        $this->queue = Json::encode($users);

        if ($this->save()) {
            // создание первой фразы
            $phrase = new Phrase();
            $phrase->game_id = $this->id;
            $phrase->user_id = $users[0];
            $phrase->question = Phrase::FIRST_PHRASES[array_rand(Phrase::FIRST_PHRASES)];
            return $phrase->save();
        }
        return false;
    }

    /**
     * Завершение игры.
     *
     * Изменение статуса и добавление врмени окончания игры.
     *
     * @return bool
     */
    public function stop() {
        $this->status = 2;
        $this->date_end = date("Y-m-d H:i:s");
        return $this->save();
    }

    /**
     * Выход из игры текущего игрока.
     *
     * Игрок исключается из очереди.
     * Если он должен ответить на вопрос, ход передается следующему игроку.
     * Если он должен задать вопрос, вопрос задается автоматически следующему игроку.
     *
     * @return bool
     */
    public function exitCurrent() {
        // TODO: выход из игры, если он не отвечает 5 минут, и если он вышел из сети (обрыв сессии или как-то определить)

        $user = User::findOne(['id' => \Yii::$app->user->identity->id]);
        $game = $user->gameCurrent;
        if (!$game) {
            return false;
        }

        // убрать из очереди
        $queue = Json::decode($game->queue); // очередь игры
        if (!empty($queue) && in_array($user->id, $queue)) {
            unset($queue[array_search($user->id, $queue)]); // убрать
            $queue = array_values($queue);
        }
        $game->queue = Json::encode($queue);
        if (!$game->save()) {
            return false;
        }

        if (count( $queue) < 2) {
            // завершить игру, дальше фразы не нужно корректировать
            return $this->stop();
        }

        $phrase = $game->lastPhrase;
        if ($phrase->user_id == $user->id) {
            if ($phrase->answer === null) {
                // если у него неотвеченная фраза, передать ее следующему игроку
                $phrase->user_id = $queue[0];
                if (!$phrase->save()) {
                    return false;
                }
            } else {
                // если он должен задать вопрос, вопрос задается автоматически на следующего игрока
                $phraseNew = new Phrase();
                $phraseNew->game_id = $this->id;
                $phraseNew->user_id = $queue[0];
                $phraseNew->question = Phrase::FIRST_PHRASES[array_rand(Phrase::FIRST_PHRASES)];
                if (!$phraseNew->save()) {
                    return false;
                }
            }
        }
        return true;
    }
}