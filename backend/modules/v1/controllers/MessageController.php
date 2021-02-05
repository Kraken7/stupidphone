<?php

namespace backend\modules\v1\controllers;


use backend\modules\v1\models\Message;
use common\models\User;
use yii\helpers\Html;

/**
 * Контроллер для работы с сообщениями
 *
 * @OA\Get(
 *     path="/messages",
 *     summary="Получить сообщения чата текущей игры",
 *     tags={"Чат"},
 *     security={{"BasicAuth":{}}},
 *     @OA\Response(
 *         response="200",
 *         description="Список всех сообщений чата текущей игры",
 *         @OA\JsonContent(
 *             default={{"id":6,"game_id":12,"user_id":3,"text":"test1","date_create":"2021-01-27 01:58:37"},{"id":5,"game_id":12,"user_id":3,"text":"test2","date_create":"2021-01-27 01:57:36"},{"id":4,"game_id":12,"user_id":3,"text":"test3","date_create":"2021-01-27 01:57:12"}}
 *         )
 *     )
 * )
 * @OA\Get(
 *     path="/messages/{id}",
 *     summary="Получить сообщение",
 *     description="Пользователь должен состоять в этой игре",
 *     tags={"Чат"},
 *     security={{"BasicAuth":{}}},
 *     @OA\Parameter(
 *         in="path",
 *         name="id",
 *         description="ID сообщения",
 *         required=true,
 *         @OA\Schema(
 *             type="integer"
 *         ),
 *         example=6
 *     ),
 *     @OA\Response(
 *         response="200",
 *         description="Сообщение",
 *         @OA\JsonContent(
 *             default={"id":6,"game_id":12,"user_id":3,"text":"test","date_create":"2021-01-27 01:58:37"}
 *         )
 *     )
 * )
 * @OA\Post(
 *     path="/messages",
 *     summary="Написать сообщение в чат текущей игры",
 *     tags={"Чат"},
 *     security={{"BasicAuth":{}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             default={"text": "test"}
 *         )
 *     ),
 *     @OA\Response(
 *         description="Пустой ответ",
 *         response="204"
 *     )
 * )
 */
class MessageController extends AppController
{
    /**
     * @var Message restApi-model
     */
    public $modelClass = 'backend\modules\v1\models\Message';

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
     * Получить все сообщения текущей активной игры пользователя
     *
     * @return Message[]
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionIndex() {
        $user = User::findOne(\Yii::$app->user->identity->id);
        $game = $user->gameCurrent;
        if (!$game) {
            throw new \yii\web\BadRequestHttpException("Data error");
        }
        return $game->messages;
    }

    /**
     * Получить сообщение.
     *
     * Сообщение можно получить только принадлежащее текущей игре пользователя.
     *
     * @param int $id ID сообщения
     *
     * @return Message
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionView(int $id) {
        $user = User::findOne(\Yii::$app->user->identity->id);
        $game = $user->gameCurrent;
        if (!$game) {
            throw new \yii\web\BadRequestHttpException("Data error");
        }

        $message = Message::findOne(['id' => $id]);
        if ($message->game_id != $game->id) {
            throw new \yii\web\BadRequestHttpException("Data error");
        }
        return Message::findOne(['id' => $id]);
    }

    /**
     * Создать сообщение в текущей активной игре пользователя (диалоги).
     *
     * @return void
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionCreate() {
        $text = Html::decode(\Yii::$app->request->post('text'));
        if (!$text) {
            throw new \yii\web\BadRequestHttpException("Data error");
        }

        $user = User::findOne(\Yii::$app->user->identity->id);
        $game = $user->gameCurrent;
        if (!$game) {
            throw new \yii\web\BadRequestHttpException("Data error");
        }

        $message = new Message();
        $message->text = $text;
        $message->game_id = $game->id;
        $message->user_id = $user->id;
        if (!$message->save()) {
            throw new \yii\web\BadRequestHttpException("Not validation.");
        }

        \Yii::$app->response->statusCode = 204;
    }
}