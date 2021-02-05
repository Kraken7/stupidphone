<?php
namespace common\models;

use backend\modules\v1\models\RateLimit;
use backend\modules\v1\models\Game;
use backend\modules\v1\models\Message;
use backend\modules\v1\models\Phrase;
use backend\modules\v1\models\Review;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\filters\RateLimitInterface;
use yii\web\IdentityInterface;

/**
 * User model
 *
 * @property integer $id
 * @property integer $vk_id
 */
class User extends ActiveRecord implements IdentityInterface, RateLimitInterface
{
    private $_user = false;

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
            ['vk_id', 'required'],
            ['vk_id', 'integer'],
            ['date_create', 'match', 'pattern' => '/^\d\d\d\d-\d\d-\d\d \d\d:\d\d:\d\d$/'],
            ['token', 'string', 'max' => 255],
            ['token', 'default', 'value' => ""],
        ];
    }

    /**
     * Связь с Game
     */
    public function getGames() {
        return $this->hasMany(Game::class, ['id' => 'game_id'])
            ->viaTable('user_game', ['user_id' => 'id'])->indexBy('id');
    }

    /**
     * Связь с Game (игры в ожидании)
     */
    public function getGamesWaite() {
        return $this->hasMany(Game::class, ['id' => 'game_id'])
            ->viaTable('user_game', ['user_id' => 'id'])->where(['status' => 0]);
    }

    /**
     * Связь с Game (текущая активная игра)
     */
    public function getGameCurrent() {
        return $this->hasMany(Game::class, ['id' => 'game_id'])
            ->viaTable('user_game', ['user_id' => 'id'])->where(['status' => 1])->one();
    }

    /**
     * Связь с Game (последняя завершенная игра)
     */
    public function getGameEnd() {
        return $this->hasMany(Game::class, ['id' => 'game_id'])
            ->viaTable('user_game', ['user_id' => 'id'])->where(['status' => 2])->orderBy('date_end DESC')->limit(1)->one();
    }

    /**
     * Связь с Game(Owner)
     */
    public function getOwnerGames() {
        return $this->hasMany(Game::class, ['owner_id' => 'id']);
    }

    /**
     * Связь с Message
     */
    public function getMessages() {
        return $this->hasMany(Message::class, ['user_id' => 'id']);
    }

    /**
     * Связь с Review
     */
    public function getReviews() {
        return $this->hasMany(Review::class, ['user_id' => 'id']);
    }

    /**
     * Связь с Phase
     */
    public function getPhases() {
        return $this->hasMany(Phrase::class, ['user_id' => 'id']);
    }

    /**
     * Проверка - есть ли хоть одна запущенная игра у пользователя
     *
     * @return bool
     */
    public function hasBeginGames() {
        return $this->getGames()->where(['status' => 1])->exists();
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentity($id) {
        return static::findOne(['id' => $id]);
    }

    /**
     * @param mixed $token
     * @param null $type
     *
     * @return void|IdentityInterface|null
     * @throws NotSupportedException
     */
    public static function findIdentityByAccessToken($token, $type = null) {
        throw new NotSupportedException('"findIdentityByAccessToken" is not implemented.');
    }

    /**
     * Finds user by vk_id
     *
     * @param string $vk_id
     *
     * @return static|null
     */
    public static function findByUsername($vk_id) {
        return static::findOne(['vk_id' => $vk_id]);
    }

    /**
     * @return int
     */
    public function getId() {
        return $this->getPrimaryKey();
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthKey() {
        // TODO: Implement getAuthKey() method.
    }

    /**
     * {@inheritdoc}
     */
    public function validateAuthKey($authKey) {
        // TODO: Implement validateAuthKey() method.
    }

    /**
     * Ограничения API - 100 запросов в минуту
     */
    public function getRateLimit($request, $action) {
        return [100, 60];
    }

    /**
     * Ограничения API - получение текущего состояния
     */
    public function loadAllowance($request, $action) {
        $rateLimit = RateLimit::findOne($this->id);
        if (!$rateLimit) {
            return [99, time()];
        }
        return [$rateLimit->allowance, $rateLimit->allowance_updated_at];
    }

    /**
     * Ограничения API - сохранение текущего состояния
     */
    public function saveAllowance($request, $action, $allowance, $timestamp) {
        $rateLimit = RateLimit::findOne($this->id);
        if (!$rateLimit) {
            $rateLimit = new RateLimit();
            $rateLimit->id = $this->id;
        }
        $rateLimit->allowance = $allowance;
        $rateLimit->allowance_updated_at = $timestamp;
        $rateLimit->save();
    }

    /**
     * Logs in a user using the provided username and password.
     * @return bool whether the user is logged in successfully
     */
    public function login($vk_id) {
        return \Yii::$app->user->login($this->getUser($vk_id));
    }

    /**
     * Finds user by [[vk_id]]
     *
     * @param $vk_id
     *
     * @return User
     */
    public function getUser($vk_id) {
        if ($this->_user === false) {
            $this->_user = self::findOne(['vk_id' => $vk_id]);
            if ($this->_user === null) {
                $this->_user = $this->registrationUser($vk_id);
            }
        }
        return $this->_user;
    }

    /**
     * Registration user by [[vk_id]]
     *
     * @param $vk_id
     *
     * @return User
     */
    public function registrationUser($vk_id) {
        $model = new self();
        $model->vk_id = $vk_id;
        $model->save();
        return self::findOne(['vk_id' => $vk_id]);
    }
}
