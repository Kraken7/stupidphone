<?php

namespace console\controllers;


use backend\modules\v1\models\Invite;
use \yii\console\Controller;

/**
 * Консольная утилита для работы с приглашениями
 */
class InviteController extends Controller
{
    /**
     * @var int время устаревания приглашения (1 день)
     */
    public $time = 60*60*24;

    /**
     * Сборщик устаревших приглашений (крон)
     *
     * Каждый день удаляет приглашения из БД, которые хранятся больше указанного времени.
     *
     * @return void
     */
    public function actionGarbageCollector() {
        Invite::deleteAll(['<', 'date_create', date("Y-m-d H:i:s", time() - $this->time)]);
    }
}