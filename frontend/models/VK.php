<?php

namespace frontend\models;


use yii\base\Model;

class VK extends Model
{
    /**
     * Проверка сигнатуры данных, поступивших из вк
     *
     * @param $data
     *
     * @return bool
     */
    public static function verifySign($data) {
        $client_secret = \Yii::$app->params['vk_client_secret']; // Защищённый ключ из настроек вашего приложения

        $sign_params = [];
        foreach ($data as $name => $value) {
            if (strpos($name, 'vk_') !== 0) { // Получаем только vk параметры из query
                continue;
            }
            $sign_params[$name] = $value;
        }

        ksort($sign_params); // Сортируем массив по ключам
        $sign_params_query = http_build_query($sign_params); // Формируем строку вида "param_name1=value&param_name2=value"
        $sign = rtrim(strtr(base64_encode(hash_hmac('sha256', $sign_params_query, $client_secret, true)), '+/', '-_'), '='); // Получаем хеш-код от строки, используя защищеный ключ приложения. Генерация на основе метода HMAC.

        return $sign === $data['sign']; // Сравниваем полученную подпись со значением параметра 'sign'
    }
}