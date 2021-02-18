<?php

namespace app\components;

use app\models\Settings;


/**
 * @property string $server_url Адрес АПИ сервера
 * @property int $server_port Порт АПИ сервера
 */
class PostmanApiHelper
{
    private $server_url;
    private $server_port;

    public function __construct()
    {
        $this->server_url = Settings::find()->andWhere(['key' => 'postman_server_url']);
        $this->server_port = Settings::find()->andWhere(['key' => 'postman_server_port']);
    }

    public function request()
    {

    }

    public function getAll()
    {
        $path = 'uploads/postman_response.xml';
        $str = file_get_contents($path);
        $xml = simplexml_load_string($str);

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
}