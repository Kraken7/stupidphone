<?php

namespace backend\modules\v1\models;


use yii\redis\ActiveRecord;

/**
 * Модель для работы с Redis таблицей ограничений API
 */
class RateLimit extends ActiveRecord
{

    /**
     * Атрибуты таблицы
     *
     * @return array
     */
    public function attributes() {
        return ['id', 'allowance', 'allowance_updated_at'];
    }

}