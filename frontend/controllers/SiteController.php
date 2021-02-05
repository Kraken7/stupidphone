<?php

namespace frontend\controllers;


use common\models\User;
use frontend\models\VK;
use yii\web\Controller;

/**
 * Site controller
 */
class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        /* demo data */
        $_GET = array (
            'vk_access_token_settings' => 'notify',
            'vk_app_id' => '7552303',
            'vk_are_notifications_enabled' => '0',
            'vk_is_app_user' => '1',
            'vk_is_favorite' => '0',
            'vk_language' => 'ru',
            'vk_platform' => 'desktop_web',
            'vk_ref' => 'other',
            'vk_ts' => '1611253219',
            'vk_user_id' => '156483708',
            'sign' => '9Uxtsx1DZnJ-6ip_68CdV2aJQ5m8IYJTns7AqOyuhOw',
        );
        /*$_GET = array (
            'vk_access_token_settings' => '',
            'vk_app_id' => '7552303',
            'vk_are_notifications_enabled' => '0',
            'vk_is_app_user' => '1',
            'vk_is_favorite' => '0',
            'vk_language' => 'ru',
            'vk_platform' => 'desktop_web',
            'vk_ref' => 'other',
            'vk_ts' => '1611256979',
            'vk_user_id' => '391983031',
            'sign' => 'oG-9LS3_jaZdEkzRALWz2WFrId22b_xZssswvKS6tlQ',
        );*/
        /* demo data */

        $this->layout = false;

        // получение данных с вк
        $data = \Yii::$app->request->get();

        // проверка сигнатуры
        if (VK::verifySign($data)) {
            $vk_id = (int)$data['vk_user_id'];

            // логин/регистрация
            $model = new User();
            $model->login($vk_id);

            // \Yii::$app->user->logout();

            if ($data['vk_platform'] == 'desktop_web') {
                return $this->render('desktop');
            } else {
                return $this->render('mobile');
            }
        }
        return false;
    }
}
