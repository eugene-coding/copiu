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
 */
class IkkoApiHelper
{
    protected $base_url;
    protected $request_string;
    protected $login;
    protected $password;
    protected $data;
    protected $token;
    protected $cookie;

    public function __construct()
    {
        $this->base_url = Settings::getValueByKey(['ikko_server_url']);
        if (strpos($this->base_url, '/', strlen($this->base_url) - 2) === false) {
            $this->base_url .= '/';
        }
        $this->login = Settings::getValueByKey(['ikko_server_login']);
        $this->password = Settings::getValueByKey(['ikko_server_password']);

        $this->login();
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
        $this->request_string = $this->base_url . 'api/auth?' . http_build_query($params);
        $this->token = $this->send();
    }

    protected function logout()
    {
        $this->request_string = $this->base_url . 'api/logout';
        $this->send();
    }

    protected function send()
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->request_string);
        curl_setopt($ch, CURLOPT_COOKIE, 'key=' . $this->token);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($ch);
        curl_close($ch);

        Yii::warning($response);

        $this->logout();

        return $response;
    }

    public function getItems()
    {
        $this->request_string =  $this->base_url . 'entities/products/list';
    }

    public function getUserCategories()
    {

    }

}