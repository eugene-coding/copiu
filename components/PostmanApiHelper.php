<?php

namespace app\components;

use app\models\Settings;
use Yii;


/**
 * @property string $server_url Адрес АПИ сервера
 * @property int $server_port Порт АПИ сервера
 * @property string $api_data Данные, полученнные по АПИ
 * @property string $request_url СТрока запроса без наименования метода
 */
class PostmanApiHelper
{
    private $server_url;
    private $server_port;
    private $request_url;
    private $api_data;

    public function __construct()
    {
        $this->server_url = Settings::getValueByKey('postman_server_url');
        $this->server_port = Settings::getValueByKey('postman_server_port');
        $this->request_url = $this->server_url . ':' . $this->server_port . '/resto/services/';
    }

    public function request($method)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->request_url . $method);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($ch);
        curl_close($ch);

//        Yii::info($response);

        return $response;
    }

    public function getAll()
    {
        Yii::info($this->server_url, 'test');
        if (!$this->server_url){
            $path = 'uploads/postman_response.xml';
            $str = file_get_contents($path);
            $xml = simplexml_load_string($str);
        } else {
            $xml = $this->request('update?methodName=waitEntitiesUpdate');
        }


        $json = json_encode($xml);
        $arr = json_decode($json, true);

        $arr_price_category = []; //Ценовые категории
        $arr_buyer = []; //Покупатели

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
     * Возвращает покупателей
     */
    public function getBuyers()
    {
        return $this->getAll()['buyer'];
    }

    /**
     * Возвращает ценовые категории
     */
    public function getPriceCategories()
    {
        return $this->getAll()['price_category'];
    }
}