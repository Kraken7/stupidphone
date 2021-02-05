<?php

namespace backend\modules\v1\controllers;


use backend\modules\v1\models\Game;
use backend\modules\v1\models\Invite;
use common\models\User;

/**
 * Контроллер для работы с приглашениями
 *
 * @OA\Get(
 *     path="/invites",
 *     summary="Получить список приглашений",
 *     tags={"Приглашение"},
 *     security={{"BasicAuth":{}}},
 *     @OA\Response(
 *         response="200",
 *         description="Список всех приглашений в приватную игру",
 *         @OA\JsonContent(
 *             default={{"id":2,"game":{"qty_phrase":20,"qty_user":5},"owner":7777,"users":{},"date_create":"23.01.2021 21:19:13"},{"id":7,"game":{"qty_phrase":20,"qty_user":3},"owner":7777,"users":{7777,156483708,391983031},"date_create":"23.01.2021 19:42:24"}}
 *         )
 *     )
 * )
 * @OA\Post(
 *     path="/invites/ok/{id}",
 *     summary="Принять приглашение (присоединиться к игре)",
 *     tags={"Приглашение"},
 *     security={{"BasicAuth":{}}},
 *     @OA\Parameter(
 *         in="path",
 *         name="id",
 *         description="ID приглашения",
 *         required=true,
 *         @OA\Schema(
 *             type="integer"
 *         ),
 *         example=2
 *     ),
 *     @OA\Response(
 *         description="Пустой ответ",
 *         response="204"
 *     )
 * )
 * @OA\Post(
 *     path="/invites/cancel/{id}",
 *     summary="Отклонить приглашение",
 *     tags={"Приглашение"},
 *     security={{"BasicAuth":{}}},
 *     @OA\Parameter(
 *         in="path",
 *         name="id",
 *         description="ID приглашения",
 *         required=true,
 *         @OA\Schema(
 *             type="integer"
 *         ),
 *         example=2
 *     ),
 *     @OA\Response(
 *         description="Пустой ответ",
 *         response="204"
 *     )
 * )
 * @OA\Post(
 *     path="/invites",
 *     summary="Пригласить друга в приватную игру",
 *     description="Пригласить можно только в свою приватную игру, которая находится в ожидании",
 *     tags={"Приглашение"},
 *     security={{"BasicAuth":{}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             default={"game_id": 16,"vk_id": 555}
 *         )
 *     ),
 *     @OA\Response(
 *         description="Пустой ответ",
 *         response="204"
 *     )
 * )
 */
class InviteController extends AppController
{
    /**
     * @var Invite restApi-model
     */
    public $modelClass = 'backend\modules\v1\models\Invite';

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

    public function actionIndex() {
        return Invite::find()->where(['vk_id' => \Yii::$app->user->identity->vk_id])->all();
    }

    /**
     * Пригласить друга в приватную игру.
     *
     * Пригласить можно только в свою приватную игру, которая находится в ожидании.
     *
     * @return void
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionCreate() {
        $game_id = (int)\Yii::$app->request->post('game_id');
        $vk_id = (int)\Yii::$app->request->post('vk_id');
        if (!$game_id || !$vk_id) {
            throw new \yii\web\BadRequestHttpException("Data error");
        }

        $game = Game::findOne(['id' => $game_id]);
        $is_double = Invite::find()->where(['game_id' => $game_id, 'vk_id' => $vk_id])->exists();
        if (!$game->private || $game->status || $game->owner_id != \Yii::$app->user->identity->id || $is_double) {
            throw new \yii\web\BadRequestHttpException("Data error");
        }

        $invite = new Invite();
        $invite->game_id = $game_id;
        $invite->vk_id = $vk_id;
        if (!$invite->save()) {
            throw new \yii\web\BadRequestHttpException("Not validation.");
        }

        \Yii::$app->response->statusCode = 204;
    }

    /**
     * Принять приглашение в приватную игру.
     *
     * Игрок входит в приватную игру (аналогично методу входа в игру).
     * Приглашение удаляется.
     *
     * @param int $id ID приглашения
     *
     * @return void
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionOk(int $id) {
        $invite = Invite::findOne(['id' => $id]);
        if ($invite->vk_id != \Yii::$app->user->identity->vk_id) {
            throw new \yii\web\BadRequestHttpException("Data error");
        }

        $game = Game::findOne(['id' => $invite->game_id]);
        $user = User::findOne(\Yii::$app->user->identity->id);

        if (!$game || $game->status !== 0 || $game->isRelationUser(\Yii::$app->user->identity->id) || $user->hasBeginGames()) {
            throw new \yii\web\BadRequestHttpException("Data error");
        }

        if ($gameOld = $user->getGames()->where(['status' => 0])->one()) {
            $gameOld->exit($user); // выйти из старой игры
        }
        $game->entry($user); // войти в новую игру

        if ($game->qty_user >= $game->getUsers()->count()) {
            $game->start();
        }

        $invite->delete();

        \Yii::$app->response->statusCode = 204;
    }

    /**
     * Отклонить приглашение в приватную игру.
     *
     * @param int $id ID приглашения
     *
     * @return void
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionCancel(int $id) {
        $invite = Invite::findOne(['id' => $id]);
        if ($invite->vk_id != \Yii::$app->user->identity->vk_id) {
            throw new \yii\web\BadRequestHttpException("Data error");
        }
        $invite->delete();

        \Yii::$app->response->statusCode = 204;
    }
}