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
    protected $cookie;
    protected $headers;
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

        if (!$this->token){
            $this->login();
        }

        $this->headers = [
            'X-Resto-LoginName: ' . $this->login,
            'X-Resto-PasswordHash: ' . sha1($this->password),
        ];
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
        if ($this->token){
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

    protected function send($to_postman = true, $type = 'GET')
    {
        Yii::info('Request string: ' . $this->request_string, 'test');

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->request_string);
        if ($to_postman){
            curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);
        }

        if ($type == "POST"){
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
        $this->request_string =  $this->base_url . 'resto/api/v2/entities/products/list?includeDeleted=false&key=' . $this->token;
        $result = $this->send();

        if (strpos($result, 'Token is expired or invalid') !== false){
           $this->login();
            $result = $this->send();
        }

        return json_decode($result, 'true');
    }

    /**
     * Синзронизация категорий пользоватлей
     */
    public function getUserCategories()
    {

    }

    /**
     * Синхронизируем покупатлей и ценовые категории
     * @return array
     */
    public function getAll()
    {
        if (!$this->base_url){
            $path = 'uploads/postman_response.xml';
            $str = file_get_contents($path);
            $xml = simplexml_load_string($str);
        } else {
            $this->request_string = $this->base_url . 'resto/services/update?methodName=waitEntitiesUpdate';
            $xml = $this->send(true);
        }

        if (strpos($xml, 'access is not allowed') > 0){
            return [
                'success' => false,
                'error' => 'Неавторизованные запросы запрещены'
            ];
        }

        $json = json_encode($xml);
        $arr = json_decode($json, true);

        $arr_price_category = []; //Ценовые категории
        $arr_buyer = []; //Покупатели

        Yii::info($arr, 'test');
        foreach ($arr['entitiesUpdate']['items']['i'] as $item) {
            if ($item['deleted'] == 'false') {
                switch ($item['type']) {
                    case 'User':
                        if ($item['r']['supplier'] == 'true') {
                            $arr_buyer[] = $item;
                        }
                        break;
                    case 'ClientPriceCategory':
                        $arr_price_category[] = $item;
                        break;
                }
            }
        }
        return [
            'buyer' => $arr_buyer,
            'price_category' => $arr_price_category,
        ];
    }

    /**
     * Цены для ценовых категорий
     * @return array
     */
    public function getPriceListItems()
    {

        if (!$this->base_url){
            $path = 'uploads/getPriceListItems.xml';
            $str = file_get_contents($path);
            $xml = simplexml_load_string($str);
        } else {
            $this->request_string = $this->base_url . 'resto/services/products?methodName=getPriceListItems';
            $xml = $this->send(true);
        }

        Yii::warning($xml);
        if ($xml){
            return [
                'success' => true,
                'data' => $xml
            ];
        }
        return [
            'success' => false,
            'error' => 'Данные не получены'
        ];

    }


}