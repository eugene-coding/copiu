<?php

namespace app\components;

use app\models\Settings;
use BenMorel\XMLStreamer\XMLReaderException;
use BenMorel\XMLStreamer\XMLStreamer;
use DOMDocument;
use Yii;


/**
 * Класс назван от балды, т.к. когда он создавался было вообще не ясно что это такое
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
        $this->base_url = rtrim(Settings::getValueByKey('ikko_server_url'), '/');
        $this->login = Settings::getValueByKey(['ikko_server_login']);
        $this->password = Settings::getValueByKey(['ikko_server_password']);
        $server_info = $this->getServerInfo();

        $this->headers = [
            'X-Resto-ServerEdition' => $server_info['edition'],
            'X-Resto-BackVersion' => $server_info['back_version'],
            'X-Resto-AuthType' => 'BACK',
            'X-Resto-LoginName' => $this->login,
            'X-Resto-PasswordHash' => sha1($this->password),
            'Content-Type' => 'text/xml; charset=UTF8',
        ];
    }

    public function setParamsStreams($method = 'GET', $optionsParams = [])
    {
        Yii::debug($this->headers, 'test');
        Yii::debug('Ответ сервера', 'test');

        $params = [
            'http' => [
                'method' => $method,
                'header' => implode("\r\n", array_map(function ($value, $key){
                    return $key . ': ' . $value;
                }, $this->headers, array_keys($this->headers))),
            ],
        ];
        if (strlen($this->post_data) > 0) {
            $params['http']['content'] = $this->post_data;
        }

        \libxml_set_streams_context(\stream_context_create($params));
    }

    /**
     * Синхронизируем покупатлей и ценовые категории
     * @return array
     */
    public function getAll()
    {
        $streamerRev = new XMLStreamer('result', 'entitiesUpdate', 'revision');
        $streamer = new XMLStreamer('result', 'entitiesUpdate', 'items', 'i');
        if (!$this->base_url) {
            $path = 'uploads/postman_response.xml';
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
            $this->setParamsStreams('POST');
            $path = $this->base_url . '/resto/services/update?methodName=waitEntitiesUpdate';
        }

        try {
            $entities_version = (string)$streamerRev->stream($path)->current()->nodeValue;
        } catch (XMLReaderException $e) {
            if (strpos($e->getMessage(), 'HTTP request failed! HTTP/1.0 403 Forbidden') !== false) {
                return [
                    'success' => false,
                    'error' => 'Неавторизованные запросы запрещены',
                ];
            }
            throw $e;
        }

        $arr_price_category = []; //Ценовые категории
        $arr_buyer = []; //Покупатели
        $arr_department = []; //Департаменты
        $revenueDebitAccount = null;
        $arr_account = []; //Счета выручки;
        $arr_store = []; //Склады
        $arr_delivery = []; //Доставка
        $arr_measure = []; //Единицы измерения
        $delivery_article = Settings::getValueByKey('delivery_article');

        /** @var \DOMElement $i */
        foreach ($streamer->stream($path) as $i) {
            /** @var DOMElement $product */
            $document = new \DOMDocument();
            $document->appendChild($i);
            $item = simplexml_import_dom($i);
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
                        if ($item->r->name->customValue == 'Задолженность перед поставщиками') {
                            $revenueDebitAccount = $item->id;
                        } else {
                            $arr_account[] = [
                                'outer_id' => (string)$item->id,
                                'name' => (string)$item->r->name->customValue,
                                'type' => (string)$item->r->type,
                                'description' => (string)$item->r->description,
                            ];
                        }
                        break;
                    case 'Department':
                        $arr_department[] = [
                            'outer_id' => (string)$item->id,
                            'name' => (string)$item->r->name,
                            'deleted' => (string)$item->r->deleted == 'false',
                        ];
                        break;
                    case 'Store':
                        $arr_store[] = [
                            'outer_id' => (string)$item->id,
                            'name' => (string)$item->r->name->customValue,
                            'department_outer_id' => (string)$item->r->npeParent,
                            'description' => (string)$item->r->description,
                        ];
                        break;
                    case 'Product':
                        if ($item->r->num == $delivery_article) {
                            $arr_delivery['outer_id'] = (string)$item->id;
                            $arr_delivery['main_unit'] = (string)$item->r->mainUnit;
                        }
                        break;
                    case 'MeasureUnit':
                        $arr_measure[] = [
                                'outer_id' => (string)$item->id,
                                'name' => (string)$item->r->name->customValue,
                                'full_name' => (string)$item->r->fullName->customValue,
                            ];
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
            'delivery' => $arr_delivery,
            'measure' => $arr_measure,
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
        $streamerRev = new XMLStreamer('result');
        if (!$this->base_url) {
            $path = 'uploads/getPriceListItems.xml';
        } else {
            $entities_version = Settings::getValueByKey('entities_version');
            $date = date('Y-m-d\TH:i:s.000+03:00', time());
            $department = Settings::getValueByKey('department_outer_id');

            $this->post_data = <<<XML
<?xml version="1.0" encoding="utf-8"?><args>
<entities-version>$entities_version</entities-version>
<client-type>BACK</client-type>
<enable-warnings>false</enable-warnings>
<use-raw-entities>true</use-raw-entities>
<dateFrom>$date</dateFrom>
<dateTo>9999-12-31T23:59:59.999+03:00</dateTo>
<departments><i cls="Department">$department</i></departments>
<includeItemsWithSchedules>false</includeItemsWithSchedules></args>
XML;
            $path = $this->base_url . '/resto/services/products?methodName=getPriceListItems';
            $this->setParamsStreams('POST');
        }

        try {
            $dom = new DOMDocument('1.0', 'UTF-8');
            $result = $dom->appendChild($streamerRev->stream($path)->current());
        } catch (XMLReaderException $e) {
            if (strpos($e->getMessage(), 'HTTP request failed! HTTP/1.0 403 Forbidden') !== false) {
                return [
                    'success' => false,
                    'error' => 'Неавторизованные запросы запрещены',
                ];
            }
            throw $e;
        }

        return [
            'success' => true,
            'data' => $dom->saveXML(),
        ];
    }

    /**
     * Создание акта оказания услуг (доставка)
     * @param array $params
     * @return mixed
     */
    public function makeActOfServices(array $params)
    {
        Yii::info('Создание Акта услуг', 'test');
        $document_eid = $this->getGUID();
        $item_eid = $this->getGUID();
//        $invoice_eid = $this->getGUID();
        //Yii::debug($document_eid, 'test');
        //Yii::debug($item_eid, 'test');
//        Yii::debug($invoice_eid, 'test');


        $dom = new domDocument('1.0', 'utf-8');
        $root = $dom->createElement('args');
        $dom->appendChild($root);

//        $entities_version = $dom->createElement('entities-version', $params['entities_version']);
//        $root->appendChild($entities_version);

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

        $ndsPercent = $dom->createElement('ndsPercent', $params['delivery_nds']);
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
//        if (YII_ENV_DEV) {
            //Сохраняем в файл
            try {
                file_put_contents('uploads/out_act/' . $params['documentNumber'] . '.xml', $this->post_data);
            } catch (\Exception $e) {
                Yii::error($e->getMessage(), 'test');
            }
//        }

        //Yii::debug($this->post_data, 'test');

//        $this->request_string = $this->base_url . 'resto/services/document?methodName=saveOrUpdateDocumentWithValidation';
        $this->request_string = $this->base_url . 'resto/services/document?methodName=saveOrUpdateDocument';
        $response = $this->setParamsStreams('POST');
//        if (YII_ENV_DEV) {
            //Сохраняем в файл
            try {
                Yii::info('Сохранение файла ответа...', 'test');
                file_put_contents('uploads/out_act/' . $params['documentNumber'] . '_response.xml', $response);
                Yii::info('Сохранение файла ответа. Успешно. Файл: ' . $params['documentNumber'] . '_response.xml', 'test');
            } catch (\Exception $e) {
                Yii::info('Сохранение файла ответа. Ошибка: ' . $e->getMessage(), 'test');
                Yii::error($e->getMessage(), 'test');
            }
//        }

        Yii::info('Завершение создания Акта услуг.', 'test');

        return $response;
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

    /** Информация о сервере */
    public function getServerInfo(): array
    {
        $this->setParamsStreams();
        $streamer = new XMLStreamer('r');
        $result = $streamer->stream($this->base_url . '/resto/get_server_info.jsp?encoding=UTF-8');

        /** @var DOMDocument $xml */
        $xml = $result->current();
        Yii::debug(json_encode($xml), __METHOD__);
        if ($xml->getElementsByTagName('edition')->item(0)->textContent == 'chain'){
            $edition = 'IIKO_CHAIN';
        } else {
            $edition = 'IIKO_RMS';
        }
        unset($reader);
        return [
            'back_version' => $xml->getElementsByTagName('version')->item(0)->textContent,
            'edition' => $edition,
        ];
    }
}
