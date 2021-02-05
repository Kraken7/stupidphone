<?php

namespace backend\modules\v1\controllers;


use backend\models\FirstData;
use backend\models\VK;
use yii\rest\Controller;
use yii\web\UnauthorizedHttpException;

/**
 * Контроллер-заглушка
 */
class MainController extends Controller
{
    /**
     * Action-заглушка
     *
     * @return string
     */
    public function actionIndex() {
//        $vk = new VK();
//        $vk->accessToken = "";
//        $vk->version = "5.126";
//        print_r($vk->statusGet());

//        FirstData::run();

        return 'it is main controller - welcome page';
    }

    /**
     * @throws UnauthorizedHttpException
     */
    public function actionUnauthorized() {
        throw new UnauthorizedHttpException();
    }
}