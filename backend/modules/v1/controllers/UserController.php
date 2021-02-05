<?php

namespace backend\modules\v1\controllers;


use common\models\User;
use yii\helpers\Html;

/**
 * Контроллер для работы с пользователями
 *
 * @OA\Get(
 *     path="/users/my",
 *     summary="Получить информацию о себе (токен вк)",
 *     tags={"Пользователь"},
 *     security={{"BasicAuth":{}}},
 *     @OA\Response(
 *         response="200",
 *         description="Информация о себе",
 *         @OA\JsonContent(
 *             default={"id":3,"vk_id":156483708,"token":"www","date_create":"2021-01-21 23:23:20"}
 *         )
 *     )
 * )
 * @OA\Put(
 *     path="/users/my",
 *     summary="Сохранить токен вк",
 *     tags={"Пользователь"},
 *     security={{"BasicAuth":{}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             default={"token":"www"}
 *         )
 *     ),
 *     @OA\Response(
 *         description="Пустой ответ",
 *         response="204"
 *     )
 * )
 */
class UserController extends AppController
{
    /**
     * @var User restApi-model
     */
    public $modelClass = 'backend\modules\v1\models\User';

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
     * Получить информацию о себе
     *
     * @return User
     */
    public function actionMy() {
        return User::findOne(['id' => \Yii::$app->user->identity->id]);
    }

    /**
     * Изменить/добавить вк токен
     *
     * @return void
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionEditToken() {
        $token = Html::decode(\Yii::$app->request->post('token'));
        if (!$token) {
            throw new \yii\web\BadRequestHttpException("Data error");
        }

        $user = User::findOne(['id' => \Yii::$app->user->identity->id]);
        $user->token = $token;
        if (!$user->save()) {
            throw new \yii\web\BadRequestHttpException("Not validation.");
        }

        \Yii::$app->response->statusCode = 204;
    }
}