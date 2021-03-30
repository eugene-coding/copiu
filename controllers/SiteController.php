<?php

namespace app\controllers;

use app\components\IikoApiHelper;
use app\components\PostmanApiHelper;
use app\models\Account;
use app\models\Buyer;
use app\models\Department;
use app\models\Measure;
use app\models\NGroup;
use app\models\Nomenclature;
use app\models\PriceCategory;
use app\models\PriceCategoryToNomenclature;
use app\models\Settings;
use app\models\Store;
use Yii;
use yii\filters\AccessControl;
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
                        'actions' => ['sync-nomenclature', 'get-nomenclature'],
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
     * @return string
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
        set_time_limit(600);
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

        Yii::info($data, 'test');

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
        set_time_limit(600);

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
        Yii::info('Файл найден: ' . (int)is_file($path_json), 'test');

        if (!is_file($path_json)) {
            return 'Файл не найден';
        } else {
            $json = file_get_contents($path_json);

            $data = json_decode($json, true);
            Yii::info('Всего записей: ' . count($data), 'test');

            $next_chunk = (int)Settings::getValueByKey('sync_nomenclature_next_chunk');
            $chunk_data = array_chunk($data, 500);
            $count_chunk = count($chunk_data);
            Yii::info('Всего чанков: ' . $count_chunk, 'test');

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
                Yii::info('Чанк в наличии: ' . (int)isset($chunk_data[$next_chunk]), 'test');
                Yii::info($chunk_data[$next_chunk], 'test');
            }

            $result = Nomenclature::import($chunk_data[$next_chunk]);
            $chunk_num = $next_chunk + 1;
            $result['chunk_done'] = "{$chunk_num} из {$count_chunk}";
            Settings::setValueByKey('sync_nomenclature_sync_date', date('Y-m-d H:i:s', time()));

            if ($result['success']) {
                $next_chunk++;
                Settings::setValueByKey('sync_nomenclature_next_chunk', (string)$next_chunk);
            }
            Yii::warning('Всего памяти ' . memory_get_usage(true), 'test');
            VarDumper::dump($result, 10, true);
        }

    }

    public function actionSyncNomenclatureGroup()
    {
        set_time_limit(300);
//        ini_set("memory_limit", "128M");

        Yii::$app->response->format = Response::FORMAT_JSON;
        $iiko = new IikoApiHelper();

        $items = $iiko->getNomenclatureGroups();
        if (isset($items['success']) && !$items['success']) {
            return $items;
        }
//        Yii::info(isset($items[0]) ? $items[0] : 'Данные не получены', 'test');

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
            'data' => 'Создан файл с данными для синхронзиации. Цены будут синхронизированы в течении 10 минут',
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
        if ($diff_time < 110) {
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
//        Yii::info($data['returnValue']['v'], 'test');

        $next_chunk = (int)Settings::getValueByKey('sync_price_next_chunk');
        $chunk_data = array_chunk($data['returnValue']['v'], 500);
        $count_chunk = count($chunk_data);
        Yii::info('Всего чанков: ' . $count_chunk, 'test');

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
            Yii::info('Чанк в наличии: ' . (int)isset($chunk_data[$next_chunk]), 'test');
//            Yii::info($chunk_data[$next_chunk], 'test');
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
        $helper = new IikoApiHelper();
        $items = [
            '6a7c2975-86b9-4d81-b210-d9211f530d8f',
            'c9422351-9abf-4064-8703-8a60f256ac4d'
        ];
        $result = $helper->getItemsById($items);
        VarDumper::dump($result, 10, true);
    }
}
