<?php

namespace app\components;

use app\models\Settings;
use Yii;

/**
 * @property string $base_url Адрес АПИ сервера
 * @property string $request_string СТрока запроса
 * @property string $login Логин
 * @property string $password Пароль
 * @property string $data Данные, полученнные по АПИ
 * @property string $token Токен, полученный от сервера ikko
 * @property string $cookie Куки для отправки
 * @property string $headers заголовки
 * @property string $post_data Данные, отправляемые в POST запросе
 */
class IkkoApiHelper
{
    protected $base_url;
    protected $request_string;
    protected $login;
    protected $password;
    protected $data;
    protected $token;
    protected $post_data;

    public function __construct()
    {
        $this->base_url = Settings::getValueByKey(['ikko_server_url']);
        if (strpos($this->base_url, '/', strlen($this->base_url) - 2) === false) {
            $this->base_url .= '/';
        }
        $this->login = Settings::getValueByKey(['ikko_server_login']);
        $this->password = Settings::getValueByKey(['ikko_server_password']);
        $this->token = Settings::getValueByKey(['token']);
        $date = Settings::getValueByKey(['token_date']);
        $time = strtotime($date);
        if ((time() - $time) > (60 * 60)){
            $token_is_expired = true;
        } else {
            $token_is_expired = false;
        }
        Yii::info('Token expired: ' . (int)$token_is_expired, 'test');

        if (!$this->token || $token_is_expired) {
            $this->login();
        }
    }

    public function test()
    {
        $this->login();
    }

    protected function login()
    {
        $params = [
            'login' => $this->login,
            'pass' => sha1($this->password),
        ];
        $this->request_string = $this->base_url . 'resto/api/auth?' . http_build_query($params);
        $this->token = $this->send();
        if ($this->token) {
            Settings::setValueByKey('token', $this->token);
            Settings::setValueByKey('token_date', date('Y-m-d H:i:s'));
            return true;
        } else {
            return false;
        }
    }

    public function logout()
    {
        $this->request_string = $this->base_url . 'resto/api/logout?key=' . $this->token;
        Settings::setValueByKey('token', null);
        Settings::setValueByKey('token_date', null);
        return $this->send();
    }

    protected function send($type = 'GET')
    {
        Yii::info('Request string: ' . $this->request_string, 'test');

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->request_string);
        if ($type == "POST") {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $this->post_data);
        }
        curl_setopt($ch, CURLINFO_HEADER_OUT, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $response = curl_exec($ch);

        Yii::info(curl_getinfo($ch, CURLINFO_HEADER_OUT), 'test');

        curl_close($ch);
        if ($response === false) {
            Yii::error(curl_error($ch), '_error');
        }

//        Yii::info($response, 'test');
        return $response;
    }

    public function getItems()
    {
        $this->request_string = $this->base_url
            . 'resto/api/v2/entities/products/list?includeDeleted=false&key='
            . $this->token;
        $result = $this->send();

        if (strpos($result, 'Token is expired or invalid') !== false) {
            $this->login();
            $result = $this->send();
        }

        return json_decode($result, 'true');
    }





    /**
     * Получает номенклатурные группы
     * @return mixed
     */
    public function getNomenclatureGroups()
    {
        $this->request_string = $this->base_url
            . 'resto/api/v2/entities/products/group/list?includeDeleted=false&key='
            . $this->token;
        $result = $this->send();

        if (strpos($result, 'Token is expired or invalid') !== false) {
            $this->login();
            $result = $this->send();
        }

        if (!$result){
           return [
               'success' => false,
               'error' => 'Данные не получены',
           ];
        }
        return json_decode($result, 'true');
    }

}