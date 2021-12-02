<?php

namespace app\controllers;

use app\components\IikoApiHelper;
use app\components\PostmanApiHelper;
use app\models\Account;
use app\models\Buyer;
use app\models\Container;
use app\models\Department;
use app\models\Measure;
use app\models\NGroup;
use app\models\Nomenclature;
use app\models\OrderBlank;
use app\models\PriceCategory;
use app\models\PriceCategoryToNomenclature;
use app\models\Settings;
use app\models\Store;
use app\models\Users;
use Yii;
use yii\db\Exception;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\helpers\VarDumper;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;

class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['sync-nomenclature', 'get-nomenclature', 'sync', 'offline', 'send-drafts'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],

                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * @param $action
     * @return bool
     * @throws ForbiddenHttpException
     * @throws \yii\web\BadRequestHttpException
     */
    public function beforeAction($action)
    {
        if (parent::beforeAction($action)) {
            //Проверяем доступ
            if (!Yii::$app->user->can($action->id)) {
                throw new ForbiddenHttpException('Доступ запрещен!');
            }
            return true;
        } else {
            return false;
        }
    }

    /**
     * Displays homepage.
     *
     * @return string|Response
     */
    public function actionIndex()
    {
        if (Yii::$app->user->isGuest) {
            return $this->redirect(['/site/login']);
        }
        return $this->render('index');

    }

    /**
     * Login action.
     *
     * @return Response|string
     */
    public function actionLogin()
    {
        $this->layout = '//main-login';

        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            //Проверяем доступ
            $access = $model->checkAccess();
            if (!$access['success']) {
                Yii::$app->user->logout();
                $model->addError('password', $access['error']);
                return $this->render('login', [
                    'model' => $model,
                ]);
            }

            return $this->goHome();
        }

        $model->password = '';
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout()
    {
        $user = Users::findOne(Yii::$app->user->id);
        $user->is_active = 0;
        $user->activity_ip = null;
        $user->last_activity = null;
        if (!$user->save()) {
            Yii::error($user->errors, '_error');
        }

        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Displays contact page.
     *
     * @return Response|string
     */
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
            Yii::$app->session->setFlash('contactFormSubmitted');

            return $this->refresh();
        }
        return $this->render('contact', [
            'model' => $model,
        ]);
    }

    /**
     * Displays about page.
     *
     * @return string
     */
    public function actionAbout()
    {
        return $this->render('about');
    }

    /**
     * Отображет форму с кнопками синхронизации
     * @return array
     */
    public function actionSyncing()
    {
        $request = Yii::$app->request;
        Yii::$app->response->format = Response::FORMAT_JSON;

        $syncing_methods = [
            '1. Синхронизация покупателей, ценовых категорий, отделов и пр.' => '/site/sync-all',
            '2. Синхронизация групп номенклатуры' => '/site/sync-nomenclature-group',
            '3. Синхронизация номенклатуры' => '/site/get-nomenclature?force=true',
            '4. Синхронизация цен для ценовых категорий' => '/site/get-price-for-price-category?force=true',
        ];

        if ($request->isGet) {
            return [
                'title' => 'Синхронизация данных',
                'content' => $this->renderAjax('sync', [
                    'syncing_methods' => $syncing_methods
                ]),
            ];
        }
        return [
            'title' => 'Синхронизация данных',
            'content' => 'Ок',
        ];
    }

    /**
     * Синхронизация покупателей, ценовых категорий, отделов, счетов, складов
     */
    public function actionSyncAll()
    {
        set_time_limit(1200);
//        ini_set("memory_limit", "128M");

        Yii::$app->response->format = Response::FORMAT_JSON;

        $helper = new PostmanApiHelper();
        $buyer_model = new Buyer();
        $pc_model = new PriceCategory();
        $department_model = new Department();
        $account_model = new Account();
        $store_model = new Store();
        $measure_model = new Measure();

        $data = $helper->getAll();

        Settings::setValueByKey('entities_version', $data['entities_version']);

        //Yii::debug($data, 'test');

        if (isset($data['success']) && $data['success'] === false) {
            return $data;
        }

        $sync_pc_result = $pc_model->sync($data['price_category']);

        if (!$sync_pc_result['success']) {
            return [
                'success' => false,
                'error' => 'Ошбика синхронизации ценовых категорий',
            ];
        }

        $sync_buyer_result = $buyer_model->sync($data['buyer']);
        if (!$sync_buyer_result['success']) {
            return [
                'success' => false,
                'error' => 'Ошбика синхронизации покупателей',
            ];
        }

        $sync_department_result = $department_model->sync($data['department']);
        if (!$sync_department_result['success']) {
            return [
                'success' => false,
                'error' => 'Ошбика синхронизации отделов',
            ];
        }

        $sync_account_result = $account_model->sync($data['account']);
        if (!$sync_account_result['success']) {
            return [
                'success' => false,
                'error' => 'Ошбика синхронизации аккаунтов',
            ];
        }

        $sync_store_result = $store_model->sync($data['store']);
        if (!$sync_store_result['success']) {
            return [
                'success' => false,
                'error' => 'Ошбика синхронизации складов',
            ];
        }

        $sync_measure_result = $measure_model->sync($data['measure']);
        if (!$sync_measure_result['success']) {
            return [
                'success' => false,
                'error' => 'Ошбика синхронизации единиц измерения',
            ];
        }

        if ($data['delivery']) {
            Settings::setValueByKey('delivery_eid', $data['delivery']['outer_id']);
            Settings::setValueByKey('delivery_main_unit', $data['delivery']['main_unit']);
        }

        if ($data['revenueDebitAccount']) {
            Settings::setValueByKey('revenue_debit_account', (string)$data['revenueDebitAccount']);
        }

        Yii::warning('Всего памяти ' . memory_get_usage(true), 'test');

        return [
            'success' => true,
            'data' => 'Синхронизация покупателей и ценовых категорий прошла успешно',
            'settings_check' => Settings::checkSettings()['success'],
        ];
    }

    /**
     * Получение и сохранение в файл номенклатуры
     * @param bool $force Принудительная синхронизация
     * @return array|mixed
     * @throws \Exception
     */
    public function actionGetNomenclature($force = false)
    {
        set_time_limit(1200);

        if (!$force) {
            //Проверяем период получения номенклатуры
            $last_time = strtotime(Settings::getValueByKey('get_nomenclature_date'));
            $diff_time = time() - $last_time;
            if ($diff_time < (60 * 60 * 12)) {
                return 'Ожидание синхронизации номенклатуры';
            }
        }


//        ini_set("memory_limit", "128M");

        Yii::$app->response->format = Response::FORMAT_JSON;
        $iiko = new IikoApiHelper();

        $iiko->getItems();

        Settings::setValueByKey('get_nomenclature_date', date('Y-m-d H:i:s', time()));

        return [
            'success' => true,
            'data' => 'Файл номенклатуры создан успешно. Номенклатура будет синхронизирована в течении получаса',
            'settings_check' => Settings::checkSettings()['success'],
        ];
    }

    /**
     * Синхронизация номенклатуры.
     * Производится частями по 500 позиций
     */
    public function actionSyncNomenclature()
    {
        //Проверяем период синхронизации номенклатуры
        $last_time = strtotime(Settings::getValueByKey('sync_nomenclature_sync_date'));
        $diff_time = time() - $last_time;
        if ($diff_time < 110) {
            return 'Ожидание синхронизации';
        }
        set_time_limit(600);

        $path_json = 'uploads/list_items.json';
        //Yii::debug('Файл найден: ' . (int)is_file($path_json), 'test');

        if (!is_file($path_json)) {
            return 'Файл не найден';
        } else {
            $json = file_get_contents($path_json);

            $data = json_decode($json, true);
            //Yii::debug('Всего записей: ' . count($data), 'test');

            $next_chunk = (int)Settings::getValueByKey('sync_nomenclature_next_chunk');
            $chunk_data = array_chunk($data, 500);
            $count_chunk = count($chunk_data);
            //Yii::debug('Всего чанков: ' . $count_chunk, 'test');

            if ($next_chunk === null) {
                $next_chunk = 0;
            }


            if (!isset($chunk_data[$next_chunk]) || !$chunk_data[$next_chunk]) {
                //Если нет чанка или он пустой - значит данные все импортированы, завршаем импорт
                Settings::setValueByKey('sync_nomenclature_next_chunk', null);
                try {
                    unlink($path_json);
                } catch (\Exception $e) {
                    Yii::error($e->getMessage(), '_error');
                }

                return 'Импорт номенклатуры завершен';
            } else {
                //Yii::debug('Чанк в наличии: ' . (int)isset($chunk_data[$next_chunk]), 'test');
                //Yii::debug($chunk_data[$next_chunk], 'test');
            }

            $result = Nomenclature::import($chunk_data[$next_chunk]);
            $chunk_num = $next_chunk + 1;
            $result['chunk_done'] = "{$chunk_num} из {$count_chunk}";
            Settings::setValueByKey('sync_nomenclature_sync_date', date('Y-m-d H:i:s', time()));

            if ($result['success']) {
                $next_chunk++;
                Settings::setValueByKey('sync_nomenclature_next_chunk', (string)$next_chunk);
            }
            Yii::warning('Всего памяти ' . (memory_get_usage(true) / 1048576) . 'M', 'test');
            VarDumper::dump($result, 10, true);
        }

    }

    public function actionSyncNomenclatureGroup()
    {
        set_time_limit(1200);
//        ini_set("memory_limit", "128M");

        Yii::$app->response->format = Response::FORMAT_JSON;
        $iiko = new IikoApiHelper();

        $items = $iiko->getNomenclatureGroups();
        if (isset($items['success']) && !$items['success']) {
            return $items;
        }
//        Yii::debug(isset($items[0]) ? $items[0] : 'Данные не получены', 'test');

        //Импортируем Группы номенклатуры
        $n_group = new NGroup();
        $result = $n_group->import($items);

        $result['settings_check'] = Settings::checkSettings()['success'];
        return $result;
    }

    /**
     * Сохраняет xml файл с ценовыми категориями
     * @param bool $force
     * @return array|string
     */
    public function actionGetPriceForPriceCategory($force = false)
    {
        set_time_limit(600);

        if (!$force) {
            //Проверяем период получения цен для категорий
            $last_time = strtotime(Settings::getValueByKey('get_prices_date'));
            $diff_time = time() - $last_time;
            if ($diff_time < (60 * 60 * 12)) {
                return 'Ожидание синхронизации цен';
            }
        }

        Yii::$app->response->format = Response::FORMAT_JSON;
        $postman = new PostmanApiHelper();
        $result = $postman->getPriceListItems();

        if ($result['success']) {
            file_put_contents('uploads/getPriceListItems.xml', $result['data']);
        } else {
            return $result;
        }

        Settings::setValueByKey('get_prices_date', date('Y-m-d H:i:s', time()));

        return [
            'success' => true,
            'data' => 'Создан файл с данными для синхронзиации. Цены будут синхронизированы в течении 30 минут',
        ];

    }

    public function actionSyncPriceForPriceCategory()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        //Проверяем период синхронизации номенклатуры
        $last_time = strtotime(Settings::getValueByKey('sync_price_date'));
        if (!$last_time) {
            $last_time = date('Y-m-d H:i:s', time());
        }
        $diff_time = time() - $last_time;
        if ($diff_time < 110 && Yii::$app->request->userIP != '127.0.0.1') {
            return 'Ожидание синхронизации цен';
        }
        set_time_limit(600);

        $path_xml = 'uploads/getPriceListItems.xml';

        if (!is_file($path_xml)) {
            return 'Файл не найден';
        }

        $xml = simplexml_load_file($path_xml, "SimpleXMLElement", LIBXML_NOCDATA);
        $json = json_encode($xml);
        $data = json_decode($json, true);
//        Yii::debug($data['returnValue']['v'], 'test');

        $next_chunk = (int)Settings::getValueByKey('sync_price_next_chunk');
        $chunk_data = array_chunk($data['returnValue']['v'], 500);
        $count_chunk = count($chunk_data);
        //Yii::debug('Всего чанков: ' . $count_chunk, 'test');

        if ($next_chunk === null) {
            $next_chunk = 0;
        }

        if (!isset($chunk_data[$next_chunk]) || !$chunk_data[$next_chunk]) {
            //Если нет чанка или он пустой - значит данные все импортированы, завршаем импорт
            Settings::setValueByKey('sync_price_next_chunk', null);
            try {
                unlink($path_xml);
            } catch (\Exception $e) {
                Yii::error($e->getMessage(), '_error');
            }

            return 'Импорт цен для ценовых категорий завершен';
        } else {
            //Yii::debug('Чанк в наличии: ' . (int)isset($chunk_data[$next_chunk]), 'test');
//            Yii::debug($chunk_data[$next_chunk], 'test');
        }

        $result = PriceCategoryToNomenclature::import($chunk_data[$next_chunk]);

        $chunk_num = $next_chunk + 1;
        $result['chunk_done'] = "{$chunk_num} из {$count_chunk}";

        Settings::setValueByKey('sync_price_date', date('Y-m-d H:i:s', time()));

        if ($result['success']) {
            $next_chunk++;
            Settings::setValueByKey('sync_price_next_chunk', (string)$next_chunk);
        }

        Yii::warning('Всего памяти ' . (memory_get_usage(true) / 1048576) . 'M', 'test');
        $result['settings_check'] = Settings::checkSettings()['success'];

        return $result;
    }

    /**
     * SyncPriceForPriceCategory v2
     */
    public function actionSyncPFPC()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $model = new PriceCategory();
        if (!$model->allowSync()) {
            return [
                'success' => 'false',
                'error' => 'Превышен лимит запросов, попробуйте позже'
            ];
        }
        set_time_limit(600);

        $path_xml = 'uploads/getPriceListItems.xml';
        if (!is_file($path_xml)) {
            return [
                'success' => 'false',
                'error' => 'Файл не найден',
            ];
        }
        //Yii::debug('Файл найден.', 'test');

        $xml = simplexml_load_file($path_xml, "SimpleXMLElement", LIBXML_NOCDATA);
        /** @var array $rows_to_add Массив для вставки одним запросом */
        $rows_to_add = [];

        foreach ($xml->returnValue->v as $item) {
            //Yii::debug('Продукт: ' . (string)$item->i->product, 'test');
            $product_in_response = (string)$item->i->product;
            $arr_prices = json_decode(json_encode($item->i->pricesForCategories), true);
//            Yii::debug($arr_prices, 'test');
            if ($arr_prices) {
                if (is_array($arr_prices['k'])) {
                    $cat_to_prices = array_combine($arr_prices['k'], $arr_prices['v']);
                } else {
                    //в массиве только один элемент
                    $cat_to_prices[$arr_prices['k']] = $arr_prices['v'];
                }
            } else {
                $cat_to_prices = [];
            }

            //Yii::debug($cat_to_prices, 'test');

            $product_in_db = ArrayHelper::map(Nomenclature::find()->all(), 'outer_id', 'id');
            $category_in_db = ArrayHelper::map(PriceCategory::find()->all(), 'outer_id', 'id');
            $product_outer_ids = array_keys($product_in_db);
            $category_outer_ids = array_keys($category_in_db);

            foreach ($cat_to_prices as $category => $price) {
                $price = round((double)$price, 2);
                if (!in_array($product_in_response, $product_outer_ids)) {
                    Yii::warning('Продукт: ' . $product_in_response . ' не найден. Пропускаем');
                    continue;
                }

                if (!in_array($category, $category_outer_ids)) {
                    Yii::warning('Категория: ' . $category . ' не найдена. Пропускаем');
                    continue;
                }
                $category_id = $category_in_db[$category];
                $product_id = $product_in_db[$product_in_response];

                /** @var PriceCategoryToNomenclature $pctn_model */
                $pctn_model = PriceCategoryToNomenclature::find()
                    ->andWhere([
                        'pc_id' => $category_id,
                        'n_id' => $product_id,
                    ])->one();

                if (!$pctn_model) {
                    $rows_to_add[] = [
                        $category_id,
                        $product_id,
                        $price,
                    ];
                } else {
                    if ($pctn_model->price != $price){
                        $pctn_model->price = $price;
                        if (!$pctn_model->save()) {
                            Yii::error($pctn_model->errors, '_error');
                        }
                    }
                }
            }
        }

        //Добавляем новые записи
        if ($rows_to_add) {
            try {
                Yii::$app->db->createCommand()->batchInsert(PriceCategoryToNomenclature::tableName(),
                    ['pc_id', 'n_id', 'price'], $rows_to_add)->execute();
            } catch (Exception $e) {
                Yii::error($e->getMessage(), '_error');
            }
        }

        //Удаление лишних не производим, т.к. при удалении ценовой категории удаляются все записи
        // с этой категорией из PriceCategoryToNomenclature

        return [
            'success' => true,
        ];
    }

    /**
     * Синхронизация цен для ценовых категорий
     */
    public function actionSyncPriceForPriceCategory_v1()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $path_xml = 'uploads/getPriceListItems.xml';

        if (!is_file($path_xml)) {
            return 'Файл не найден';
        }


        //Проверяем период синхронизации номенклатуры
        $last_time = strtotime(Settings::getValueByKey('sync_price_date'));
        if (!$last_time) {
            $last_time = date('Y-m-d H:i:s', time());
        }
        $diff_time = time() - $last_time;
        if ($diff_time < 110) {
            return 'Ожидание синхронизации цен';
        }
        set_time_limit(600);

        $path_xml = 'uploads/getPriceListItems.xml';

        if (!is_file($path_xml)) {
            return 'Файл не найден';
        }

        $str = file_get_contents($path_xml);

        $result = PriceCategoryToNomenclature::sync($str);

        Yii::warning('Всего памяти ' . (memory_get_usage(true) / 1048576) . 'M', 'test');
        $result['settings_check'] = Settings::checkSettings()['success'];

        try {
            unlink($path_xml);
        } catch (\Exception $e) {
            Yii::error($e->getMessage(), '_error');
        }

        return $result;
    }

    /**
     * Для тестов
     */
    public function actionTest()
    {
        $result = Container::find()->asArray()->all();
        VarDumper::dump($result, 10, true);
    }

    /**
     * Синхронизация покупателей, ценовых категорий, отделов, групп номенклатуры, бланков заказа
     * @return string
     * @throws \Exception
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\Exception
     */
    public function actionSync()
    {
        set_time_limit(1200);
        $this->actionSyncAll();
        $this->actionSyncNomenclatureGroup();
        $this->actionGetNomenclature();
        $this->actionGetPriceForPriceCategory();
        OrderBlank::sync();
        return 'Готово';
    }

    public function actionOffline()
    {
        $this->layout = '//main-login';
        return $this->render('offline');
    }

//    /**
//     * Отправляет заказ, если дата настала.
//     */
//    public function actionSendDrafts()
//    {
//        set_time_limit(60 * 60);
//        //Получаем актуальные черновики (у которых задана плаируемая дата отправки и нет даты отправки)
//        $drafts = OrderDraft::find()
//            ->andWhere(['IS NOT', 'plan_send_date', null])
//            ->andWhere(['IS', 'send_at', null])
//            ->all();
//
//        /** @var OrderDraft $draft */
//        foreach ($drafts as $draft) {
//            Yii::debug($draft->attributes, 'test');
//            if ($draft->plan_send_date == date('Y-m-d', time())) {
//                //Копируем заказ
//                $order = Order::copy($draft->order_id);
//                $draft_order = $draft->order;
//                if ($order) {
//                    $order->target_date = $draft_order->target_date;
//                    $order->total_price = $draft_order->total_price;
//                    //Формируем накладную
//                    if (!$order->makeInvoice()) {
//                        //Если накладная не создалась
//                        $order->invoice_number = 'error';
//                        $order->status = $order::STATUS_ERROR;
//                    } else {
//                        $order->status = $order::STATUS_WORK;
//                    }
//                    if (!$order->save()){
//                        Yii::error($order->errors, '_error');
//                    }
//                    $draft->send_at = date('Y-m-d H:i:s', time());
//                    if (!$draft->save()){
//                        Yii::error($draft->errors, '_error');
//                    }
//                } else {
//                    return Yii::$app->session['error'];
//                }
//            }
//        }
//        return 'Готово';
//    }
}
