<?php

namespace backend\modules\v1;


/**
 * Rest API
 *
 * @author Kraken7 <aleksey.saraev.97@mail.ru>
 * @since 1.0
 *
 * @OA\Info(title="Stupid Phone API", version="1.0.0")
 * @OA\Server(url="https://api.stupidphone.loc/v1")
 */
class Module extends \yii\base\Module
{
    /**
     * Пространство имен контроллеров
     */
    public $controllerNamespace = 'backend\modules\v1\controllers';

    /**
     * Конфигурация identityClass
     *
     * @return void
     */
    public function init()
    {
        parent::init();
        /*\Yii::$app->user->enableSession = false;
        \Yii::$app->user->identityClass = 'app\modules\v1\models\User';
        \Yii::$app->user->loginUrl = '/v1/main/index';*/
    }
}