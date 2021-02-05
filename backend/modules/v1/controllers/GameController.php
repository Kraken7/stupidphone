<?php

namespace backend\modules\v1\controllers;


use backend\modules\v1\models\Game;
use backend\modules\v1\models\Invite;
use common\models\User;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

/**
 * Контроллер для работы с играми
 *
 * @OA\Get(
 *     path="/games",
 *     summary="Получить список всех открытых игр",
 *     tags={"Игра"},
 *     security={{"BasicAuth":{}}},
 *     @OA\Response(
 *         response="200",
 *         description="Список всех открытых игр",
 *         @OA\JsonContent(
 *             default={{"id":7,"qty_phrase":50,"qty_user":5,"status":0,"private":false,"date_create":"22.01.2021 01:10:39","owner":0,"users":{1:777},"queue":{},"stop":{}},{"id":6,"qty_phrase":20,"qty_user":3,"status":0,"private":false,"date_create":"22.01.2021 01:30:39","owner":0,"users":{},"queue":{},"stop":{}}}
 *         )
 *     )
 * )
 * @OA\Get(
 *     path="/games/{id}",
 *     summary="Получить приватную игру (окно ожидания)",
 *     description="Пользователь должен состоять в этой игре",
 *     tags={"Игра"},
 *     security={{"BasicAuth":{}}},
 *     @OA\Parameter(
 *         in="path",
 *         name="id",
 *         description="ID приватной игры",
 *         required=true,
 *         @OA\Schema(
 *             type="integer"
 *         ),
 *         example=8
 *     ),
 *     @OA\Response(
 *         response="200",
 *         description="Список всех открытых игр",
 *         @OA\JsonContent(
 *             default={"id":12,"qty_phrase":20,"qty_user":3,"status":0,"private":true,"date_create":"23.01.2021 19:42:24","owner":7777,"users":{1:7777,3:156483708,4:391983031},"queue":{},"stop":{}}
 *         )
 *     )
 * )
 * @OA\Get(
 *     path="/games/current",
 *     summary="Получить текущую игру (активную)",
 *     description="Пользователь должен состоять в этой игре",
 *     tags={"Игра"},
 *     security={{"BasicAuth":{}}},
 *     @OA\Response(
 *         response="200",
 *         description="Данные о текущей игре",
 *         @OA\JsonContent(
 *             default={"id":12,"qty_phrase":20,"qty_user":3,"status":1,"private":true,"date_create":"23.01.2021 19:42:24","owner":7777,"users":{"2":7777,"3":156483708,"4":391983031},"queue":{4,2,3},"stop":{2,3},"current_qty_phrase":5,"current_phrases":{{"id":8,"user":391983031,"question":"зачем","answer":false,"date_create":"04.02.2021 05:29:32"},{"id":7,"user":156483708,"question":"что делали","answer":"гуляли","date_create":"04.02.2021 05:28:30"},{"id":6,"user":7777,"question":false,"answer":false,"date_create":"04.02.2021 05:27:41"},{"id":5,"user":391983031,"question":"с кем","answer":false,"date_create":"04.02.2021 03:52:52"},{"id":4,"user":156483708,"question":"Кто","answer":"super","date_create":"04.02.2021 02:55:13"}}}
 *         )
 *     )
 * )
 * @OA\Post(
 *     path="/games/entry/{id}",
 *     summary="Присоединиться к игре (в ожидании)",
 *     tags={"Игра"},
 *     security={{"BasicAuth":{}}},
 *     @OA\Parameter(
 *         in="path",
 *         name="id",
 *         description="ID игры",
 *         required=true,
 *         @OA\Schema(
 *             type="integer"
 *         ),
 *         example=8
 *     ),
 *     @OA\Response(
 *         description="Пустой ответ",
 *         response="204"
 *     )
 * )
 * @OA\Post(
 *     path="/games/exit/{id}",
 *     summary="Выйти из игры (в ожидании)",
 *     tags={"Игра"},
 *     security={{"BasicAuth":{}}},
 *     @OA\Parameter(
 *         in="path",
 *         name="id",
 *         description="ID игры",
 *         required=true,
 *         @OA\Schema(
 *             type="integer"
 *         ),
 *         example=8
 *     ),
 *     @OA\Response(
 *         description="Пустой ответ",
 *         response="204"
 *     )
 * )
 * @OA\Post(
 *     path="/games",
 *     summary="Создать игру",
 *     tags={"Игра"},
 *     security={{"BasicAuth":{}}},
 *     @OA\RequestBody(
 *         required=true,
 *         description="qty_phrase - кол-во фраз, qty_user - кол-во игроков, private (не обязат.) - приватная или публичная (0/1)",
 *         @OA\JsonContent(
 *             default={"qty_phrase": 20,"qty_user": 5,"private": 0}
 *         )
 *     ),
 *     @OA\Response(
 *         description="Пустой ответ",
 *         response="204"
 *     )
 * )
 * @OA\Post(
 *     path="/games/start/{id}",
 *     summary="Начать игру",
 *     tags={"Игра"},
 *     security={{"BasicAuth":{}}},
 *     @OA\Parameter(
 *         in="path",
 *         name="id",
 *         description="ID игры (приватной текущего пользователя)",
 *         required=true,
 *         @OA\Schema(
 *             type="integer"
 *         ),
 *         example=8
 *     ),
 *     @OA\Response(
 *         description="Пустой ответ",
 *         response="204"
 *     )
 * )
 * @OA\Post(
 *     path="/games/stop",
 *     summary="Добавляет/убирает (переключатель) текущего пользователя в список 'остановить игру'",
 *     tags={"Игра"},
 *     security={{"BasicAuth":{}}},
 *     @OA\Response(
 *         response="200",
 *         description="Статус игры",
 *         @OA\JsonContent(
 *             default={"status":1}
 *         )
 *     )
 * )
 */
class GameController extends AppController
{
    /**
     * @var Game restApi-model
     */
    public $modelClass = 'backend\modules\v1\models\Game';

    /**
     * Предопределенные restApi-actions
     *
     * @return array Actions: options
     */
    public function actions() {
        $actions = parent::actions();
        unset($actions['index'], $actions['view'], $actions['delete'], $actions['create'], $actions['update']);
        return $actions;
    }

    /**
     * Получить список всех открытых игр
     *
     * @return Game[]
     */
    public function actionIndex() {
        return Game::find()->where(['status' => 0, 'private' => 0])->orderBy('date_create')->all();
    }

    /**
     * Получить игру в ожидании.
     *
     * Игрок должен быть в ней. Игра должна быть в ожидании.
     *
     * @param int $id ID игры
     *
     * @return Game
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionView(int $id) {
        $game = Game::findOne(['id' => $id]);
        if (!$game || $game->status || !$game->isRelationUser(\Yii::$app->user->identity->id)) {
            throw new \yii\web\BadRequestHttpException("Data error");
        }
        return $game;
    }

    /**
     * Войти в игру
     *
     * Игрок входит в открытую игру.
     * В приватную игру нельзя войти.
     * Из другой игры он выходит. Нельзя войти повторно. Нельзя войти, если запущена другая игра.
     * Если игроки все собраны - запуск игры.
     *
     * @param int $id ID игры
     *
     * @return void
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionEntry(int $id) {
        $game = Game::findOne(['id' => $id]);
        $user = User::findOne(\Yii::$app->user->identity->id);

        if (!$game || $game->status !== 0 || $game->private || $game->isRelationUser(\Yii::$app->user->identity->id) || $user->hasBeginGames()) {
            throw new \yii\web\BadRequestHttpException("Data error");
        }

        if ($gameOld = $user->getGames()->where(['status' => 0])->one()) {
            $gameOld->exit($user); // выйти из старой игры
        }
        $game->entry($user); // войти в новую игру

        if ($game->qty_user >= $game->getUsers()->count()) {
            if (!$game->start()) {
                throw new \yii\web\BadRequestHttpException("Not validation.");
            }
        }

        \Yii::$app->response->statusCode = 204;
    }

    /**
     * Выйти из игры
     *
     * Игрок выходит из открытой игры
     * Нельзя выйти из игры, в которой его нет.
     * Распространяется на приватную игру тоже.
     * Если игрок - создатель, то создателем становится другой игрок случайным выбором.
     * Если игроков не осталось и игра не по умолчанию, то она удаляется.
     *
     * @param int $id ID игры
     *
     * @return void
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionExit(int $id) {
        $game = Game::findOne(['id' => $id]);
        $user = User::findOne(\Yii::$app->user->identity->id);

        if (!$game || $game->status !== 0 || !$game->isRelationUser(\Yii::$app->user->identity->id)) {
            throw new \yii\web\BadRequestHttpException("Data error");
        }

        $game->exit($user); // выйти из игры
        \Yii::$app->response->statusCode = 204;
    }

    /**
     * Создать игру.
     *
     * При создании новой игры из других игрок выходит.
     *
     * @return void
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionCreate() {
        $qty_phrase = (int)\Yii::$app->request->post('qty_phrase');
        $qty_user = (int)\Yii::$app->request->post('qty_user');
        $private = (int)\Yii::$app->request->post('private');

        if (!$qty_phrase || !$qty_user) {
            throw new \yii\web\BadRequestHttpException("Data error");
        }

        $user = User::findOne(\Yii::$app->user->identity->id);

        // создать игру
        if (!$game = Game::create($qty_phrase, $qty_user, $private, $user)) {
            throw new \yii\web\BadRequestHttpException("Not validation.");
        }

        if ($gameOld = $user->getGames()->where(['status' => 0])->one()) {
            $gameOld->exit($user); // выйти из старой игры
        }
        $game->entry($user); // войти в новую игру

        \Yii::$app->response->statusCode = 204;
    }

    /**
     * Запустить свою игру.
     *
     * Запускает свою приватную игру, недожидаясь приглашений.
     *
     * @param int $id ID игры (текущего пользователя)
     *
     * @return void
     * @throws \yii\web\BadRequestHttpException
     * @throws \Exception
     */
    public function actionStart(int $id) {
        $game = Game::findOne(['id' => $id]);
        $userCount = $game->getUsers()->count();

        if (!$game->private || $game->owner_id != \Yii::$app->user->identity->id || $userCount < 2) {
            throw new \yii\web\BadRequestHttpException("Data error");
        }

        $game->qty_user = $userCount;
        $game->save();

        Invite::deleteAll(['game_id' => $id]);

        if (!$game->start()) {
            throw new \yii\web\BadRequestHttpException("Not validation.");
        }

        \Yii::$app->response->statusCode = 204;
    }

    /**
     * Получить текущую активную игру текущего пользователя.
     *
     * Игрок должен быть в ней. Игра должна быть запущена.
     * Показывает сколько уже написано фраз.
     * Показывает список фраз по порядку. Видны только фразы текущего игрока.
     * Показывает очередь игроков. Первый в очереди - ход данного игрока.
     *
     * Если текущей игры не найдено, показывается последняя завершенная игра со всеми открытыми фразами.
     * Это создает эффект "раскрытия фраз" одним и тем же методом.
     * В интерфейсе должны появиться кнопки "оставить отзыв" и "сделать репост".
     *
     * @return array
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionCurrent() {
        $user = User::findOne(['id' => \Yii::$app->user->identity->id]);
        $game = $user->gameCurrent;
        if (!$game) {
            $game = $user->gameEnd;
            if (!$game) {
                throw new \yii\web\BadRequestHttpException("Data error");
            }
        }

        $phrasesModel = $game->getPhrases();
        $phrasesCount = (int)$phrasesModel->count();

        $phrases = $phrasesModel->orderBy('date_create DESC')->all();

        if ($game->status == 1) {
            // скрыть чужие фразы
            foreach ($phrases as $phrase) {
                if ($phrase->user_id != $user->id) {
                    $phrase->answer = false;
                    if ($phrase->parent->user_id != $user->id) {
                        $phrase->question = false;
                    }
                }
            }
        }


        $res = ArrayHelper::toArray($game);
        $res['current_qty_phrase'] = $phrasesCount;
        $res['current_phrases'] = $phrases;

        return $res;
    }

    /**
     * Добавляет/убирает (переключатель) текущего пользователя в список "остановить игру".
     *
     * Когда все игроки будут согласны остановить игру (добавлены в этот список), игра заканчивается.
     *
     * @return array status: 1 - игра продолжается, 2 - игра завершена
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionStop() {
        $user = User::findOne(['id' => \Yii::$app->user->identity->id]);
        $game = $user->gameCurrent;
        if (!$game) {
            throw new \yii\web\BadRequestHttpException("Data error");
        }

        $stop = Json::decode($game->stop);
        if (empty($stop) || !in_array($user->id, $stop)) {
            $stop[] = $user->id; // добавить
        } else {
            unset($stop[array_search($user->id, $stop)]); // убрать
            $stop = array_values($stop);
        }

        $game->stop = Json::encode($stop);
        if (!$game->save()) {
            throw new \yii\web\BadRequestHttpException("Not validation.");
        }

        $status = 1; // игра продолжается

        // если все нажали "стоп", завершить игру
        if (count($stop) == $game->qty_user) {
            if (!$game->stop()) {
                throw new \yii\web\BadRequestHttpException("Not validation.");
            }
            $status = 2; // игра завершена
        }

        return [
            'status' => $status,
        ];
    }
}