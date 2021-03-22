<?php

namespace app\components;

use app\models\Nomenclature;
use app\models\PriceCategory;
use app\models\PriceCategoryToNomenclature;
use app\models\Settings;
use DOMDocument;
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
    private $headers = [];
    private $post_data;

    public function __construct()
    {
        $this->base_url = Settings::getValueByKey('ikko_server_url');
        if (strpos($this->base_url, '/', strlen($this->base_url) - 2) === false) {
            $this->base_url .= '/';
        }

        $this->login = Settings::getValueByKey(['ikko_server_login']);
        $this->password = Settings::getValueByKey(['ikko_server_password']);
        $server_back_version = $this->getServerBackVersion();

        $this->headers = [
            'X-Resto-ServerEdition: IIKO_CHAIN',
            'X-Resto-BackVersion: ' . $server_back_version,
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
            $xml = simplexml_load_string($result);
        }

        if (strpos($xml, 'access is not allowed') > 0) {
            return [
                'success' => false,
                'error' => 'Неавторизованные запросы запрещены'
            ];
        }

        $arr_price_category = []; //Ценовые категории
        $arr_buyer = []; //Покупатели
        $arr_department = []; //Департаменты
        $revenueDebitAccount = null;
        $entities_version = (string)$xml->entitiesUpdate->revision;
        $arr_account = []; //Счета выручки;
        $arr_store = []; //Склады

        foreach ($xml->entitiesUpdate->items->i as $item) {
            if ($item->deleted == 'false') {
                switch ($item->type) {
                    case 'User':
                        if ($item->r->supplier == 'true') {
                            $arr_buyer[] = [
                                'id' => $item->id,
                                'name' => $item->r->name->customValue,
                                'price_category' => $item->r->priceCategory,
                            ];
                        }
                        break;
                    case 'ClientPriceCategory':
                        $arr_price_category[] = [
                            'id' => $item->id,
                            'name' => $item->r->name->customValue,
                        ];
                        break;
                    case 'Account':
                        if ($item->r->name->customValue == 'Задолженность перед поставщиками'){
                            $revenueDebitAccount = $item->id;
                        } elseif($item->deleted == 'false') {
                            $arr_account[] = [
                                'outer_id' => (string)$item->id,
                                'name' => (string)$item->r->name->customValue,
                                'type' => (string)$item->r->type,
                                'description' => (string)$item->r->description,
                            ];
                        }
                        break;
                    case 'Department':
                       if ($item->deleted == 'false'){
                           $arr_department[] = [
                               'outer_id' => $item->id,
                               'name' => $item->r->name
                           ];
                       }
                        break;
                    case 'Store':
                        if ($item->deleted == 'false'){
                            $arr_store[] = [
                                'outer_id' => (string)$item->id,
                                'name' => (string)$item->r->name->customValue,
                                'department_outer_id' => (string)$item->r->npeParent,
                                'description' => (string)$item->r->description
                            ];
                        }
                        break;
                }
            }
        }

        return [
            'buyer' => $arr_buyer,
            'price_category' => $arr_price_category,
            'revenueDebitAccount' => $revenueDebitAccount,
            'department' => $arr_department,
            'account' => $arr_account,
            'entities_version' => $entities_version,
            'store' => $arr_store,
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
            $this->post_data = <<<XML
<?xml version="1.0" encoding="utf-8"?><args><entities-version>547512</entities-version><client-type>BACK</client-type><enable-warnings>false</enable-warnings><use-raw-entities>true</use-raw-entities><dateFrom>2021-02-08T13:26:43.762+03:00</dateFrom><dateTo>9999-12-31T23:59:59.999+03:00</dateTo><departments><i cls="Department">f5460b95-c588-b515-0164-21bb6182000d</i></departments><includeItemsWithSchedules>false</includeItemsWithSchedules></args>
XML;

            $this->request_string = $this->base_url . 'resto/services/products?methodName=getPriceListItems';
            $str = $this->send('POST');
            $xml = simplexml_load_string($str);
        }

        if (strpos($xml, 'access is not allowed')) {
            return [
                'success' => false,
                'error' => 'Неавторизированные запросы запрещены',
            ];
        }

//        $json = json_encode($xml);
//        $arr = json_decode($json, true);

        $items = $xml->returnValue->v;
        Yii::info($items, 'test');

        if (!$items) {
            return [
                'success' => false,
                'error' => 'Нет данных'
            ];
        }

        foreach ($items as $item) {
            $product_outer_id = (string)$item->i['eid'];
            if (!$product_outer_id) {
                Yii::info('Нет ID продукта. Пропускаем', 'test');
                $skipped++;
                continue;
            }


            Yii::info($product_outer_id, 'test');
            $categories_and_prices = [];
            if ($item->i->pricesForCategories) {
                $categories_and_prices = json_decode(json_encode($item->i->pricesForCategories), true);
            }
//            $categories = isset($item['i']['pricesForCategories']['k']) ? $item['i']['pricesForCategories']['k'] : null;
            $categories = isset($categories_and_prices['k']) ? $categories_and_prices['k'] : null;
            if (!$categories) {
                Yii::info('Нет категорий. Пропускаем', 'test');
                $skipped++;
                continue;
            }

//            $prices = $item['i']['pricesForCategories']['v'];
            $prices = isset($categories_and_prices['v']) ? $categories_and_prices['v'] : null;;

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

    /**
     * Создание акта оказания услуг (доставка)
     * @param array $params
     * @return mixed
     */
    public function makeActOfServices($params)
    {
        $document_eid = $this->getGUID();
        $item_eid = $this->getGUID();
//        $invoice_eid = $this->getGUID();
        Yii::info($document_eid, 'test');
        Yii::info($item_eid, 'test');
//        Yii::info($invoice_eid, 'test');


        $dom = new domDocument('1.0', 'utf-8');
        $root = $dom->createElement('args');
        $dom->appendChild($root);

        $entities_version = $dom->createElement('entities-version', $params['entities_version']);
        $root->appendChild($entities_version);

        $client_type = $dom->createElement('client-type', 'BACK');
        $root->appendChild($client_type);

        $use_raw_entities = $dom->createElement('use-raw-entities', 'true');
        $root->appendChild($use_raw_entities);

        $document = $dom->createElement('document');
        $doc_cls_attr = $dom->createAttribute('cls');
        $doc_eid_attr = $dom->createAttribute('eid');
        $doc_cls_attr->value = 'OutgoingService';
        $doc_eid_attr->value = $document_eid;
        $document->appendChild($doc_cls_attr);
        $document->appendChild($doc_eid_attr);

        $revenueDebitAccount = $dom->createElement('revenueDebitAccount', $params['revenueDebitAccount']);
        $document->appendChild($revenueDebitAccount);

        $isAutomatic = $dom->createElement('isAutomatic', 'false');
        $document->appendChild($isAutomatic);

        $editable = $dom->createElement('editable', 'true');
        $document->appendChild($editable);

        $department = $dom->createElement('department', $params['department']);
        $department_cls_attr = $dom->createAttribute('cls');
        $department_cls_attr->value = 'Department';
        $department->appendChild($department_cls_attr);
        $document->appendChild($department);

        $revenueAccount = $dom->createElement('revenueAccount', $params['revenueAccount']);
        $document->appendChild($revenueAccount);

        $dateIncoming = $dom->createElement('incomingDate', $params['incomingDate']);
        $document->appendChild($dateIncoming);

        $supplier = $dom->createElement('supplier', $params['buyer_outer_id']);
        $document->appendChild($supplier);

        $items = $dom->createElement('items');

        $i = $dom->createElement('i');
        $i_cls_attr = $dom->createAttribute('cls');
        $i_cls_attr->value = 'OutgoingServiceItem';
        $i->appendChild($i_cls_attr);
        $i_eid_attr = $dom->createAttribute('eid');
        $i_eid_attr->value = $item_eid;
        $i->appendChild($i_eid_attr);

        $invoice = $dom->createElement('invoice');
        $invoice_cls_attr = $dom->createAttribute('cls');
        $invoice_cls_attr->value = 'OutgoingService';
        $invoice->appendChild($invoice_cls_attr);
        $invoice_eid_attr = $dom->createAttribute('eid');
        $invoice_eid_attr->value = $document_eid;
        $invoice->appendChild($invoice_eid_attr);
        $i->appendChild($invoice);

        $code = $dom->createElement('code', $params['code']);
        $i->appendChild($code);

        $price = $dom->createElement('price', $params['sum']);
        $i->appendChild($price);

        $priceWithoutNds = $dom->createElement('priceWithoutNds', $params['sum']);
        $i->appendChild($priceWithoutNds);

        $sum = $dom->createElement('sum', $params['sum']);
        $i->appendChild($sum);

        $ndsPercent = $dom->createElement('ndsPercent', 0);
        $i->appendChild($ndsPercent);

        $sumWithoutNds = $dom->createElement('sumWithoutNds', $params['sum']);
        $i->appendChild($sumWithoutNds);

        $discountSum = $dom->createElement('discountSum', 0);
        $i->appendChild($discountSum);

        $amountUnit = $dom->createElement('amountUnit', $params['amountUnit']);
        $i->appendChild($amountUnit);

        $containerId = $dom->createElement('containerId', '00000000-0000-0000-0000-000000000000');
        $i->appendChild($containerId);

        $num = $dom->createElement('num', 1);
        $i->appendChild($num);

        $product = $dom->createElement('product', $params['product']);
        $i->appendChild($product);

        $amount = $dom->createElement('amount', 1);
        $i->appendChild($amount);

        $id = $dom->createElement('id', $item_eid);
        $i->appendChild($id);

        $items->appendChild($i);
        $document->appendChild($items);

        $doc_invoice = $dom->createElement('invoice');
        $document->appendChild($doc_invoice);

        $dateIncoming = $dom->createElement('dateIncoming', $params['incomingDate']);
        $document->appendChild($dateIncoming);

        $documentNumber = $dom->createElement('documentNumber', $params['documentNumber']);
        $document->appendChild($documentNumber);

        $status = $dom->createElement('status', $params['status']);
        $document->appendChild($status);

        $revision = $dom->createElement('revision', 0);
        $document->appendChild($revision);

        $doc_id = $dom->createElement('id', $document_eid);
        $document->appendChild($doc_id);

        $root->appendChild($document);

        $suppressWarnings = $dom->createElement('suppressWarnings');
        $suppressWarnings_cls_attr = $dom->createAttribute('cls');
        $suppressWarnings_cls_attr->value = 'java.util.ArrayList';
        $suppressWarnings->appendChild($suppressWarnings_cls_attr);

        $i2 = $dom->createElement('i', 'SUPPLIER_PRICE_DEVIATION_LIMIT_EXCEEDED');
        $suppressWarnings->appendChild($i2);

        $root->appendChild($suppressWarnings);

        $this->post_data = $dom->saveXML();
        Yii::info($this->post_data, 'test');

        $this->request_string = $this->base_url . 'resto/services/document?methodName=saveOrUpdateDocumentWithValidation';
        return $this->send('POST');
    }

    /**
     * Генерирует идентификатор по шаблону хххххххх(8символов)-хххх-хххх-хххххххххххх(12символов)
     */
    function getGUID()
    {
        if (function_exists('com_create_guid')) {
            return com_create_guid();
        } else {
            mt_srand((double)microtime() * 10000);//optional for php 4.2.0 and up.
            $charid = strtoupper(md5(uniqid(rand(), true)));
            $hyphen = chr(45);// "-"
            $uuid = substr($charid, 0, 8) . $hyphen
                . substr($charid, 8, 4) . $hyphen
                . substr($charid, 12, 4) . $hyphen
                . substr($charid, 16, 4) . $hyphen
                . substr($charid, 20, 12);
            return strtolower($uuid);
        }
    }

    public function getServerInfo()
    {
        $this->request_string = $this->base_url . 'resto/get_server_info.jsp?encoding=UTF-8';
        return $this->send();
    }

    public function getServerBackVersion()
    {
       $info = $this->getServerInfo();
       $info = '<?xml version="1.0" encoding="utf-8"?>' . $info;
       $xml = simplexml_load_string($info);
        Yii::info($xml->version, 'test');

        return (string)$xml->version;
    }
}