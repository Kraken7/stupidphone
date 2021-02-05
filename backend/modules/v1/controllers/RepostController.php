<?php

namespace backend\modules\v1\controllers;


use backend\modules\v1\models\Repost;
use common\models\User;
use yii\helpers\Json;

/**
 * Контроллер для работы с репостами
 *
 * @OA\Post(
 *     path="/invites/reposts",
 *     summary="Сделать репост последней завершенной игры",
 *     tags={"Репост"},
 *     security={{"BasicAuth":{}}},
 *     @OA\Response(
 *         description="Пустой ответ",
 *         response="204"
 *     )
 * )
 */
class RepostController extends AppController
{
    /**
     * @var Repost restApi-model
     */
    public $modelClass = 'backend\modules\v1\models\Repost';

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
     * Сделать репост последней завершенной игры.
     *
     * @return void
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionCreate() {
        $user = User::findOne(\Yii::$app->user->identity->id);
        $game = $user->gameEnd;
        if (!$game) {
            throw new \yii\web\BadRequestHttpException("Data error");
        }

        $users = [];
        if ($repost = Repost::find()->where(['game_id' => $game->id])->one()) {
            $users = Json::decode($repost->user_ids);
        } else {
            $repost = new Repost();
            $repost->game_id = $game->id;
        }
        if (!in_array($user->id, $users)) {
            $users[] = $user->id;
        }
        $repost->user_ids = Json::encode($users);
        if (!$repost->save()) {
            throw new \yii\web\BadRequestHttpException("Not validation.");
        }

        \Yii::$app->response->statusCode = 204;
    }
}