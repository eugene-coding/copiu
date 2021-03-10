<?php

namespace app\components;

use app\models\Nomenclature;
use app\models\PriceCategory;
use app\models\PriceCategoryToNomenclature;
use app\models\Settings;
use Yii;


/**
 * @property string $base_url Адрес АПИ сервера
 * @property string $request_string СТрока запроса без наименования метода
 * @property string $login
 * @property string $password
 * @property string $headers Заголовки
 * @property string $post_data Данные, отправляемые в POST запросе
 */
class PostmanApiHelper
{
    private $base_url;
    private $request_string;
    private $login;
    private $password;
    private $headers;
    private $post_data;

    public function __construct()
    {
        $this->base_url = Settings::getValueByKey('ikko_server_url');
        if (strpos($this->base_url, '/', strlen($this->base_url) - 2) === false) {
            $this->base_url .= '/';
        }

        $this->login = Settings::getValueByKey(['ikko_server_login']);
        $this->password = Settings::getValueByKey(['ikko_server_password']);

        $this->headers = [
            'X-Resto-ServerEdition: IIKO_CHAIN',
            'X-Resto-BackVersion: 7.5.6019.0',
            'X-Resto-AuthType: BACK',
            'X-Resto-LoginName: ' . $this->login,
            'X-Resto-PasswordHash: ' . sha1($this->password),
            'Content-Type: raw',
        ];
    }

    public function send($type = 'GET')
    {
        Yii::info('Request string: ' . $this->request_string, 'test');
        Yii::info($this->headers, 'test');

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->request_string);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);
        curl_setopt($ch, CURLINFO_HEADER_OUT, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        if ($type != "GET") {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $this->post_data);
        }
        $response = curl_exec($ch);
        Yii::info(curl_getinfo($ch, CURLINFO_HEADER_OUT), 'test');
        curl_close($ch);

//        Yii::info($response);

        return $response;
    }

    /**
     * Синхронизируем покупатлей и ценовые категории
     * @return array
     */
    public function getAll()
    {
        if (!$this->base_url) {
            $path = 'uploads/postman_response.xml';
            $str = file_get_contents($path);
            $xml = simplexml_load_string($str);
        } else {
            $body = <<<XML
<?xml version="1.0" encoding="utf-8"?>
<args>
    <entities-version>-1</entities-version>
    <client-type>BACK</client-type>
    <use-raw-entities>true</use-raw-entities>
    <fromRevision>-1</fromRevision>
    <timeoutMillis>30000</timeoutMillis>
    <useRawEntities>true</useRawEntities>
</args>
XML;

            $this->post_data = $body;
            $this->request_string = $this->base_url . 'resto/services/update?methodName=waitEntitiesUpdate';
            $result = $this->send('POST');
//            file_put_contents('waitEntitiesUpdate.xml', $result);
            $xml = simplexml_load_string($result);
        }

        if (strpos($xml, 'access is not allowed') > 0) {
            return [
                'success' => false,
                'error' => 'Неавторизованные запросы запрещены'
            ];
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
//        Yii::info($arr_buyer, 'test');
//        Yii::info($arr_price_category, 'test');

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

    /**
     * Цены для ценовых категорий
     * @return array
     */
    public function getPriceListItems()
    {

        $skipped = 0;
        $added = 0;
        $errors = 0;

        if (!$this->base_url) {
            $path = 'uploads/getPriceListItems.xml';
            $str = file_get_contents($path);
            $xml = simplexml_load_string($str);
        } else {
            $this->request_string = $this->base_url . 'resto/services/products?methodName=getPriceListItems';
            $xml = $this->send(true, 'POST');
        }

        if (strpos($xml, 'access is not allowed')) {
            return [
                'success' => false,
                'error' => 'Неавторизированные запросы запрещены',
            ];
        }

        $json = json_encode($xml);
        $arr = json_decode($json, true);

        $items = $arr['returnValue']['v'];
        Yii::info($items, 'test');

        if (!$items) {
            return [
                'success' => false,
                'error' => 'Нет данных'
            ];
        }

        foreach ($items as $item) {
            $product_outer_id = isset($item['i']['@attributes']['eid']) ? $item['i']['@attributes']['eid'] : null;
            if (!$product_outer_id) {
                Yii::info('Нет ID продукта. Пропускаем', 'test');
                $skipped++;
                continue;
            }

            Yii::info($product_outer_id, 'test');
            Yii::info($item['i']['pricesForCategories'], 'test');

            $categories = isset($item['i']['pricesForCategories']['k']) ? $item['i']['pricesForCategories']['k'] : null;
            if (!$categories) {
                Yii::info('Нет категорий. Пропускаем', 'test');
                $skipped++;
                continue;
            }

            $prices = $item['i']['pricesForCategories']['v'];

            for ($i = 0; $i < count($categories); $i++) {
                $category = PriceCategory::findOne(['outer_id' => $categories[$i]]);

                if (!$category) {
                    Yii::info('Категория не найдена. Пропускаем', 'test');
                    continue;
                }

                $price = $prices[$i];
                $product = Nomenclature::findOne(['outer_id' => $product_outer_id]);

                if (!$product) {
                    Yii::info('Продукт не найден. Пропускаем', 'test');
                    continue;
                }

                $model = new PriceCategoryToNomenclature([
                    'pc_id' => $category->id,
                    'n_id' => $product->id,
                    'price' => $price,
                ]);

                if (!$model->validate('pc_id')) {
                    Yii::info($model->errors, 'test');
                    continue;
                }

                if (!$model->save()) {
                    Yii::error($model->errors, '_error');
                    $errors++;
                } else {
                    $added++;
                }
            }

        }

        $data = 'Синхронизация прошла успешно<br>';
//
//        if ($errors){
//            $data .= 'Ошибок: ' . $errors . '<br>';
//        }
//        if ($skipped){
//            $data .= 'Пропущено: ' . $skipped . '<br>';
//        }
//
//        if ($added){
//            $data .= 'Добавлено: ' . $added . '<br>';
//        }

        return [
            'success' => true,
            'data' => $data
        ];

    }
}