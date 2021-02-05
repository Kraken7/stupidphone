<?php

namespace backend\models;


use yii\base\Model;

/**
 * Класс для работы с API ВК
 */
class VK extends Model
{
    /**
     * @var string access_token
     */
    private $accessToken;

    /**
     * @var string version
     */
    private $version = "5.80";

    /**
     * @return mixed
     */
    public function getAccessToken() {
        return $this->accessToken;
    }

    /**
     * @param mixed $accessToken
     */
    public function setAccessToken($accessToken) {
        $this->accessToken = $accessToken;
    }

    /**
     * @return mixed
     */
    public function getVersion() {
        return $this->version;
    }

    /**
     * @param mixed $version
     */
    public function setVersion($version) {
        $this->version = $version;
    }

    /**
     * Базовый метод API запроса к ВК
     *
     * @param $method
     * @param array $params
     *
     * @throws \Exception
     * @return mixed
     */
    private function _vkApi_call($method, $params = []) {
        $params['access_token'] = $this->accessToken;
        $params['v'] = $this->version;

        $query = http_build_query($params);
        $url = "https://api.vk.com/method/".$method.'?'.$query;

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $json = curl_exec($curl);
        $error = curl_error($curl);

        if ($error) {
            throw new \Exception("Failed {$method} request");
        }

        curl_close($curl);

        $response = json_decode($json, true);

        if (!$response) {
            throw new \Exception("Invalid response for {$method} request");
        }

        return $response['response'];
    }

    /* for example */
    /**
     * @return mixed
     * @throws \Exception
     */
    public function statusGet() {
        return $this->_vkApi_call('status.get', [
            'user_id' => 156483708,
        ]);
    }
    /* for example */
}