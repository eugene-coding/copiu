<?php

namespace app\controllers;

use app\components\PostmanApiHelper;
use app\models\Buyer;
use app\models\PriceCategory;
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
                'class' => AccessControl::className(),
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],

                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
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
            return $this->goBack();
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
            'Синхронизация покупателей и ценовых категорий' => '/site/sync-all',
//            'Синхронизация покупателей' => '/site/sync-buyer',
//            'Синхронизация ценовых категорий' => '/site/sync-price-category',
        ];

        if ($request->isGet) {
            return [
                'title' => 'Синхронизация данных',
                'content' => $this->renderAjax('sync', [
                    'syncing_methods' => $syncing_methods
                ]),
            ];
        }

    }

    /**
     * Синхронизация покупателей
     */
    public function actionSyncBuyer()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $helper = new PostmanApiHelper();
        $buyer_model = new Buyer();
        return $buyer_model->sync($helper->getBuyers());

//        return [
//            'success' => false,
//            'data' => 'Синхронизация покупателей прошла успешно',
//            'error' => 'Ошбика Синхронизации покупателей категорий',
//        ];
    }

    /**
     * Синхронизация ценовых категорий
     */
    public function actionSyncPriceCategory()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        return [
            'success' => false,
            'data' => 'Синхронизация ценовых категорий прошла успешно',
            'error' => 'Ошбика Синхронизации ценовых категорий',
        ];
    }

    /**
     * Синхронизация ценовых категорий ипокупателей
     */
    public function actionSyncAll()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $helper = new PostmanApiHelper();
        $buyer_model = new Buyer();
        $pc_model = new PriceCategory();

        $data = $helper->getAll();

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

        return [
            'success' => true,
            'data' => 'Синхронизация покупателей и ценовых категорий прошла успешно',
        ];
    }

    public function actionTest()
    {
        $helper = new PostmanApiHelper();

        $result = $helper->getAll();

        VarDumper::dump($result, 10, true);
    }


}
