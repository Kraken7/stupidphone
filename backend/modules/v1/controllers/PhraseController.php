<?php

namespace backend\modules\v1\controllers;


use backend\modules\v1\models\Phrase;
use common\models\User;
use yii\helpers\Html;
use yii\helpers\Json;

/**
 * Контроллер для работы с фразами
 *
 * @OA\Get(
 *     path="/phrases",
 *     summary="Получить последнюю фразу текущей игры текущего пользователя",
 *     tags={"Фраза"},
 *     security={{"BasicAuth":{}}},
 *     @OA\Response(
 *         response="200",
 *         description="Фраза",
 *         @OA\JsonContent(
 *             default={"id":5,"user":391983031,"question":"с кем","answer":"","date_create":"04.02.2021 03:52:52"}
 *         )
 *     )
 * )
 * @OA\Post(
 *     path="/phrases/answer",
 *     summary="Добавить ответ к фразе",
 *     tags={"Фраза"},
 *     security={{"BasicAuth":{}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             default={"text": "test"}
 *         )
 *     ),
 *     @OA\Response(
 *         response="200",
 *         description="Статус игры",
 *         @OA\JsonContent(
 *             default={"status":1}
 *         )
 *     )
 * )
 * @OA\Post(
 *     path="/phrases/question",
 *     summary="Создать новую фразу с вопросом",
 *     tags={"Фраза"},
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
class PhraseController extends AppController
{
    /**
     * @var Phrase restApi-model
     */
    public $modelClass = 'backend\modules\v1\models\Phrase';

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
     * Получить последнюю фразу текущей игры текущего пользователя.
     *
     * @return Phrase
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionIndex() {
        $user = User::findOne(\Yii::$app->user->identity->id);
        $game = $user->gameCurrent;
        if (!$game) {
            throw new \yii\web\BadRequestHttpException("Data error");
        }

        return $game->lastPhrase;
    }

    /**
     * Написать ответ
     *
     * Находит последнюю фразу текущей игры текущего пользователя.
     * Фраза должна принадлежать текущему пользователю.
     * Добавляет к этой фразе ответ.
     *
     * Определяет по количеству текущих фраз продолжать или закончить игру.
     *
     * @return array status: 1 - игра продолжается, 2 - игра завершена
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionAnswer() {
        $text = Html::decode(\Yii::$app->request->post('text'));
        if (!$text) {
            throw new \yii\web\BadRequestHttpException("Data error");
        }

        $user = User::findOne(\Yii::$app->user->identity->id);
        $game = $user->gameCurrent;
        if (!$game) {
            throw new \yii\web\BadRequestHttpException("Data error");
        }

        $phrase = $game->lastPhrase;
        if ($phrase->user_id != $user->id) {
            throw new \yii\web\BadRequestHttpException("Access is denied");
        }
        if ($phrase->answer !== null) {
            throw new \yii\web\BadRequestHttpException("Already filled");
        }

        $phrase->answer = $text;
        if (!$phrase->save()) {
            throw new \yii\web\BadRequestHttpException("Not validation.");
        }

        $status = 1; // игра продолжается

        // если фразы закончились, завершить игру
        if ($game->qty_phrase == (int)$game->getPhrases()->count()) {
            if (!$game->stop()) {
                throw new \yii\web\BadRequestHttpException("Not validation.");
            }
            $status = 2; // игра завершена
        }

        return [
            'status' => $status,
        ];
    }

    /**
     * Написать вопрос
     *
     * Находит последнюю фразу текущей игры текущего пользователя.
     * Фраза должна принадлежать текущему пользователю и на нее должен быть ответ.
     * Создается новая фраза-вопрос с указанием на предыдущую фразу с user_id следующего по очереди игрока.
     * Очередь игры смещается вперед по кругу.
     *
     * @return void
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionQuestion() {
        $text = Html::decode(\Yii::$app->request->post('text'));
        if (!$text) {
            throw new \yii\web\BadRequestHttpException("Data error");
        }

        $user = User::findOne(\Yii::$app->user->identity->id);
        $game = $user->gameCurrent;
        if (!$game) {
            throw new \yii\web\BadRequestHttpException("Data error");
        }

        $phrase = $game->lastPhrase;
        if ($phrase->user_id != $user->id) {
            throw new \yii\web\BadRequestHttpException("Access is denied");
        }
        if ($phrase->answer === null) {
            throw new \yii\web\BadRequestHttpException("The answer is required");
        }

        $queue = Json::decode($game->queue); // очередь игры

        $newPhrase = new Phrase();
        $newPhrase->parent_id = $phrase->id;
        $newPhrase->game_id = $game->id;
        $newPhrase->user_id = $queue[1]; // следующий игрок в очереди
        $newPhrase->question = $text;
        if (!$newPhrase->save()) {
            throw new \yii\web\BadRequestHttpException("Not validation.");
        }

        // обновление очереди по кругу
        array_push($queue, array_shift($queue));
        $game->queue = Json::encode($queue);
        if (!$game->save()) {
            throw new \yii\web\BadRequestHttpException("Not validation.");
        }

        \Yii::$app->response->statusCode = 204;
    }
}