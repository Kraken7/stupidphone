<?php

namespace backend\models;


use yii\base\Model;

class FirstData extends Model
{
    /**
     * Первые системные 3 игры, которые доступны по умолчанию
     *
     * @throws \yii\db\Exception
     */
    public static function run() {
        /* game */
        $date = date("Y-m-d H:i:s");
        $data = [
            [
                'qty_phrase' => 10,
                'qty_user' => 2,
                'date_create' => $date,
                'queue' => "[]",
                'stop﻿' => "[]",
            ],
            [
                'qty_phrase' => 20,
                'qty_user' => 3,
                'date_create' => $date,
                'queue' => "[]",
                'stop' => "[]",
            ],
            [
                'qty_phrase' => 50,
                'qty_user' => 5,
                'date_create' => $date,
                'queue' => "[]",
                'stop' => "[]",
            ],
        ];
        \Yii::$app->db->createCommand()->batchInsert('game', ['qty_phrase', 'qty_user', 'date_create', 'queue', 'stop'], $data)->execute();
        /* game */
    }
}