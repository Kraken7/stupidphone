<?php

namespace backend\modules\v1\controllers;


use backend\modules\v1\models\Review;
use common\models\User;
use yii\helpers\Html;

/**
 * Контроллер для работы с отзывами
 *
 * @OA\Get(
 *     path="/reviews",
 *     summary="Получить все отзывы",
 *     tags={"Отзыв"},
 *     security={{"BasicAuth":{}}},
 *     @OA\Response(
 *         response="200",
 *         description="Список всех отзывов",
 *         @OA\JsonContent(
 *             default={{"id":1,"text":"super","game_id":12,"user":156483708,"date_create":"28.01.2021 04:38:11"},{"id":2,"text":"super2","game_id":14,"user":555,"date_create":"28.01.2021 04:38:11"}}
 *         )
 *     )
 * )
 * @OA\Post(
 *     path="/reviews",
 *     summary="Оставить отзыв к последней завершенной игре",
 *     tags={"Отзыв"},
 *     security={{"BasicAuth":{}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             default={"text": "super"}
 *         )
 *     ),
 *     @OA\Response(
 *         description="Пустой ответ",
 *         response="204"
 *     )
 * )
 */
class ReviewController extends AppController
{
    /**
     * @var Review restApi-model
     */
    public $modelClass = 'backend\modules\v1\models\Review';

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
     * Получить все отзывы
     *
     * @return Review[]
     */
    public function actionIndex() {
        return Review::find()->all();
    }

    /**
     * Оставить отзыв к последней завершенной игре
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
        $game = $user->gameEnd;
        if (!$game) {
            throw new \yii\web\BadRequestHttpException("Data error");
        }

        if (Review::find()->where(['game_id' => $game->id, 'user_id' => $user->id])->exists()) {
            throw new \yii\web\BadRequestHttpException("Reviewed already.");
        }

        $review = new Review();
        $review->text = $text;
        $review->game_id = $game->id;
        $review->user_id = $user->id;
        if (!$review->save()) {
            throw new \yii\web\BadRequestHttpException("Not validation.");
        }

        \Yii::$app->response->statusCode = 204;
    }

}