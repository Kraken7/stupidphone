<?php

namespace backend\modules\v1\controllers;


use yii\filters\AccessControl;
use yii\filters\auth\HttpBasicAuth;
use yii\rest\ActiveController;

/**
 * Базовый контроллер rest-api
 *
 * @OA\SecurityScheme(
 *     securityScheme="BasicAuth",
 *     type="http",
 *     scheme="basic"
 * )
 */
class AppController extends ActiveController
{
    /**
     * Аутентификация Session
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['access'] = [
            'class' => AccessControl::class,
            'rules' => [
                [
                    'allow' => true,
                    'roles' => ['@'],
                ],
            ],
        ];
        return $behaviors;
    }
}