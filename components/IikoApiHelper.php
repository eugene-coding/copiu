<?php

namespace app\components;

use app\models\Settings;
use DOMDocument;
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
class IikoApiHelper
{
    protected $base_url;
    protected $request_string;
    protected $login;
    protected $password;
    protected $data;
    protected $token;
    protected $post_data;
    protected $headers;


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
        if ((time() - $time) > (60 * 60)) {
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

    /**
     * Получение токена доступа
     * @return bool
     */
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

    /**
     * Освобождение лицензии
     * @return mixed
     */
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
//        Yii::info('Headers: ' . json_encode($this->headers), 'test');

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->request_string);
        if ($type == "POST") {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $this->post_data);
        }
        if ($this->headers) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);
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

    /**
     * Номенклатура
     * @return mixed
     * @throws \Exception
     */
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

        $path_file = 'uploads/list_items.json';
        file_put_contents($path_file, $result);
        return $path_file;
//        return json_decode($result, 'true');


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

        if (!$result) {
            return [
                'success' => false,
                'error' => 'Данные не получены',
            ];
        }
        return json_decode($result, 'true');
    }

    /**
     * @param string||null $counteragent
     * @return mixed
     */
    public function getBalance($counteragent_outer_id = null)
    {
        $date = date('Y-m-d\TH:i:s', time());
        if (!$counteragent_outer_id) {
            $this->request_string = $this->base_url
                . "resto/api/v2/reports/balance/counteragents?timestamp={$date}&key={$this->token}";
        } else {
            $this->request_string = $this->base_url
                . "resto/api/v2/reports/balance/counteragents?timestamp={$date}&key={$this->token}&counteragent={$counteragent_outer_id}";
        }

        $result = $this->send();

        $info = json_decode($result, 'true');
        Yii::info($info, 'test');

        $sum = isset($info[0]['sum']) ? $info[0]['sum'] : 0;
        return $sum;
    }

    /**
     * Получение накладной по номеру
     * @param array $params Параметры
     * @return mixed
     */
    public function getOrderBlank($params)
    {
        if (!isset($params['from']) || !$params['from']) {
            Yii::error('Отсутствует параметр "from"', 'error');
            return false;
        }
        if (!isset($params['to']) || !$params['to']) {
            Yii::error('Отсутствует параметр "to"', 'error');
            return false;
        }
        if (!isset($params['number']) || !$params['number']) {
            Yii::error('Отсутствует параметр "number"', 'error');
            return false;
        }
        $params['key'] = $this->token;

        $params['currentYear'] = 'false';
        $query = http_build_query($params);
        $this->request_string = $this->base_url
            . 'resto/api/documents/export/outgoingInvoice/byNumber?' . $query;
        $result = $this->send();

        \Yii::info($result, 'test');

        return json_decode($result, 'true');
    }

    /**
     * Создание расходной накладной
     * @param array $params Параметры документа
     * @return string
     */
    public function makeExpenseInvoice($params)
    {
        $dom = new domDocument('1.0', 'utf-8');
        $root = $dom->createElement('document');
        $dom->appendChild($root);
        $number = $dom->createElement('documentNumber', $params['documentNumber']);
        $date_incoming = $dom->createElement('dateIncoming',$params['dateIncoming']);
        $useDefaultDocumentTime = $dom->createElement('useDefaultDocumentTime', 'true');
        $revenueAccountCode = $dom->createElement('revenueAccountCode', '4.01');
        $defaultStoreId = $dom->createElement('defaultStoreId', $params['defaultStoreId']);
        $counteragent_id = $dom->createElement('counteragentId', $params['counteragentId']);
        $comment = $dom
            ->createElement('comment', "Доставка с {$params['from']} по {$params['to']} + «{$params['comment']}»");

        $root->appendChild($number);
        $root->appendChild($date_incoming);
        $root->appendChild($useDefaultDocumentTime);
        $root->appendChild($revenueAccountCode);
        $root->appendChild($defaultStoreId);
        $root->appendChild($counteragent_id);
        $root->appendChild($comment);

        $items = $dom->createElement('items');
        foreach ($params['items'] as $item) {
            $item_element = $dom->createElement('item');
            $product_id = $dom->createElement('productId', $item['outer_id']);
            $num = $dom->createElement('productArticle', $item['num']);
            $price = $dom->createElement('price', $item['price']);
            $amount = $dom->createElement('amount', $item['count']);
            $sum = $dom->createElement('sum', $item['sum']);

            $item_element->appendChild($product_id);
            $item_element->appendChild($num);
            $item_element->appendChild($price);
            $item_element->appendChild($amount);
            $item_element->appendChild($sum);
            $items->appendChild($item_element);
        }
        $root->appendChild($items);

//        $path = "uploads/invoice.xml";
//        $dom->save($path);

        $this->post_data = $dom->saveXML();
        $this->headers = [
            'Content-Type: application/xml'
        ];
        \Yii::info($this->post_data, 'test');

        $this->request_string = $this->base_url . 'resto/api/documents/import/outgoingInvoice?key=' . $this->token;
        $result = $this->send('POST');
//        Yii::info($result, 'test');
        return $result;
    }
}