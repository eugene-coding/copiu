<?php

namespace app\controllers;

use app\models\Order;
use app\models\OrderBlank;
use app\models\OrderToNomenclature;
use app\models\Users;
use Throwable;
use Yii;
use app\models\OrderDraft;
use app\models\search\OrderDraftSearch;
use yii\base\Exception;
use yii\filters\AccessControl;
use yii\helpers\Json;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use \yii\web\Response;
use yii\helpers\Html;

/**
 * OrderDraftController implements the CRUD actions for OrderDraft model.
 */
class OrderDraftController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['post'],
                    'bulk-delete' => ['post'],
                ],
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
     * Lists all OrderDraft models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new OrderDraftSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single OrderDraft model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionView($id)
    {
        $request = Yii::$app->request;
        $model = $this->findModel($id);
        $order = $model->order;
        if ($request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $model->name,
                'content' => $this->renderAjax('view', [
                    'model' => $model,
                    'order' => $order,
                ]),
                'footer' => Html::button('Закрыть',
                        ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
                    Html::a('Редактировать', ['update', 'id' => $id],
                        ['class' => 'btn btn-primary', 'role' => 'modal-remote'])
            ];
        } else {
            return $this->render('view', [
                'model' => $model,
                'order' => $order,
            ]);
        }
    }

    /**
     * Creates a new OrderDraft model.
     * For ajax request will return json object
     * and for non-ajax request if creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     * @throws \Exception
     * @throws Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionCreate()
    {
        $request = Yii::$app->request;
        $model = new OrderDraft();
        $user = Users::getUser();
        $blanks = OrderBlank::find()->select('id')->column();
        $order = new Order([
            'buyer_id' => $user->buyer->id ?? null,
            'blanks' => implode(',', $blanks),
        ]);
        $productsDataProvider = $order->getProductDataProvider();


        if ($request->isAjax) {
            /*
            *   Process for ajax request
            */
            Yii::$app->response->format = Response::FORMAT_JSON;
            if ($request->isGet) {
                return [
                    'title' => "Добавление черновика",
                    'content' => $this->renderAjax('create', [
                        'draft' => $model,
                        'order' => $order,
                        'productsDataProvider' => $productsDataProvider,
                    ]),
                    'footer' => Html::button('Закрыть',
                            ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
                        Html::button('Сохранить', ['class' => 'btn btn-primary', 'type' => "submit"])

                ];
            } else {
                $order->scenario = $order::SCENARIO_DRAFT;
                if ($model->load($request->post()) && $model->save()) {
                    return [
                        'forceReload' => '#crud-datatable-pjax',
                        'forceClose' => true,
                    ];
                } else {
                    return [
                        'title' => "Добавление черновика",
                        'content' => $this->renderAjax('create', [
                            'draft' => $model,
                            'order' => $order,
                            'productsDataProvider' => $productsDataProvider,
                        ]),
                        'footer' => Html::button('Закрыть',
                                ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
                            Html::button('Сохранить', ['class' => 'btn btn-primary', 'type' => "submit"])

                    ];
                }
            }
        } else {
            /*
            *   Process for non-ajax request
            */
            if ($model->load($request->post()) && $order->load($request->post())) {
                if (!$order->save()) {
                    Yii::error($model->errors);
                }
                $order->orderProcessing();
                $order->total_price = OrderToNomenclature::getTotalPrice($order->id);
                $order->save(false);

                $model->order_id = $order->id;
                if (!$model->save()) {
                    Yii::error($model->errors);
                }
                return $this->redirect(['index']);
            } else {
                return $this->render('create', [
                    'draft' => $model,
                    'order' => $order,
                    'productsDataProvider' => $productsDataProvider,
                ]);
            }
        }

    }

    /**
     * Updates an existing OrderDraft model.
     * For ajax request will return json object
     * and for non-ajax request if update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException
     * @throws \Exception
     * @throws Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionUpdate($id)
    {
        $request = Yii::$app->request;
        $model = $this->findModel($id);
        $order = $model->order;
        if (!$model->order) {
            $user = Users::getUser();
            $blanks = OrderBlank::find()->select('id')->active()->column();
            $order = new Order([
                'buyer_id' => $user->buyer->id ?? null,
                'blanks' => implode(',', $blanks),
            ]);
        }
        $order->scenario = Order::SCENARIO_DRAFT;
        $productsDataProvider = $order->getProductDataProvider();

        if ($request->isAjax) {
            /*
            *   Process for ajax request
            */
            Yii::$app->response->format = Response::FORMAT_JSON;
            if ($request->isGet) {
                return [
                    'title' => "Редактирование " . $model->name,
                    'content' => $this->renderAjax('update', [
                        'draft' => $model,
                        'order' => $order,
                        'productsDataProvider' => $productsDataProvider,
                    ]),
                    'footer' => Html::button('Закрыть',
                            ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
                        Html::button('Сохранить', ['class' => 'btn btn-primary', 'type' => "submit"])
                ];
            } else {
                if ($model->load($request->post()) && $model->save()) {
                    return [
                        'forceReload' => '#crud-datatable-pjax',
                        'forceClose' => true,
                    ];
                } else {
                    return [
                        'title' => "Редактирование " . $model->name,
                        'content' => $this->renderAjax('update', [
                            'draft' => $model,
                            'order' => $order,
                            'productsDataProvider' => $productsDataProvider,
                        ]),
                        'footer' => Html::button('Закрыть',
                                ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
                            Html::button('Сохранить', ['class' => 'btn btn-primary', 'type' => "submit"])
                    ];
                }
            }
        } else {
            /*
            *   Process for non-ajax request
            */
            if ($model->load($request->post()) && $order->load($request->post())) {
                $order->orderProcessing();
                $order->total_price = OrderToNomenclature::getTotalPrice($order->id);
                if (!$order->save()) {
                    Yii::error($model->errors);
                }
                $model->order_id = $order->id;
                if (!$model->save()) {
                    Yii::error($model->errors);
                }
                return $this->redirect(['index']);
            } else {
                return $this->render('update', [
                    'draft' => $model,
                    'order' => $order,
                    'productsDataProvider' => $productsDataProvider,
                ]);
            }
        }
    }

    /**
     * Delete an existing OrderDraft model.
     * For ajax request will return json object
     * and for non-ajax request if deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException
     * @throws \Exception
     * @throws Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionDelete($id)
    {
        $request = Yii::$app->request;
        $this->findModel($id)->delete();

        if ($request->isAjax) {
            /*
            *   Process for ajax request
            */
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['forceClose' => true, 'forceReload' => '#crud-datatable-pjax'];
        } else {
            /*
            *   Process for non-ajax request
            */
            return $this->redirect(['index']);
        }
    }

    /**
     * Finds the OrderDraft model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return OrderDraft the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = OrderDraft::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    /**
     * @param $id
     * @return mixed|Response
     * @throws Throwable
     * @throws \Exception
     * @throws \yii\db\StaleObjectException
     */
    public function actionDeleteDraft($id)
    {
        $draft = OrderDraft::findOne($id);
        $order = Order::findOne($draft->order_id);
        $buyer = Users::getUser()->buyer;

        if ($buyer->id === $order->buyer_id) {
            if (!$order->invoice_number) {
                //Удаляем только в случае, если заказ не отправлен в айку
                $order->delete();
            } else {
                //Проставляем заказу признак "Удален"
                $order->deleted = 1;
                $order->save(false);
            }
        }

        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['forceClose' => true, 'forceReload' => '#crud-datatable-pjax'];
        }

        return $this->redirect(['index']);
    }

    /**
     * Сбрасывает дату на которую делается заказ, планируемую дату отправки и статус заказа (на STATUS_ORDER_DRAFT)
     * @param int $id Идентифкатор черновика
     * @return array
     */
    public function actionResetDraft($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        try {
            $draft = $this->findModel($id);
        } catch (NotFoundHttpException $e) {
            return [
                'title' => "Ошибка сброса черновика",
                'content' => '<span class="text-danger">Сброс не удался. ' . $e->getMessage() . '</span>',
                'footer' => Html::button('Закрыть',
                    ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"])
            ];
        }
        $draft->plan_send_date = null;
        $draft->send_at = null;

        $order = $draft->order;
        $order->target_date = null;
        $order->status = $order::STATUS_ORDER_DRAFT;
        $order->delivery_time_from = null;
        $order->delivery_time_to = null;
        $order->delivery_address_id = null;

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $draft->save(false);
            $order->save(false);
            $transaction->commit();
        } catch (Exception $e) {
            Yii::error($draft->errors, '_error');
            Yii::error($order->errors, '_error');
            $transaction->rollBack();
            $errors = $e->getMessage();
            $errors .= $draft->hasErrors() ? '<br>' . Json::encode($draft->errors) : '';
            $errors .= $order->hasErrors() ? '<br>' . Json::encode($order->errors) : '';
            return [
                'title' => "Ошибка сброса черновика",
                'content' => '<span class="text-danger">Сброс не удался. <br>' . $errors . '</span>',
                'footer' => Html::button('Закрыть',
                    ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"])
            ];
        }

        return ['forceClose' => true, 'forceReload' => '#crud-datatable-pjax'];

    }

    /**
     * Постановка заказа в очередь на отправку.
     * 1. Показ информации по заказу + установка даты на которую совершается заказ
     * 2. После подтверждения: установка даты на которую совершается заказ, расчет
     * и установка даты отправки заказа
     * @param int $id Идентификатор черновика
     * @return array
     * @throws NotFoundHttpException
     */
    public function actionToQueue($id)
    {
        $request = Yii::$app->request;
        Yii::$app->response->format = Response::FORMAT_JSON;

        $draft = $this->findModel($id);
        $order = $draft->order;
        $productsDataProvider = $order->getProductDataProvider();

        if ($request->isGet){
            return [
                'title' => "Добавление черновика в очередь",
                'content' => $this->renderAjax('_to_queue_form', [
                    'draft' => $draft,
                    'order' => $order,
                    'productsDataProvider' => $productsDataProvider,
                ]),
                'footer' => Html::button('Закрыть',
                        ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
                    Html::button('Подтвердить', ['class' => 'btn btn-primary', 'type' => "submit"])

            ];
        } else {
            if ($draft->load($request->post()) && $order->load($request->post())){
                //Yii::debug($draft->attributes, 'test');
                //Yii::debug($order->attributes, 'test');

                $order->scenario = $order::SCENARIO_TO_QUEUE;
                if (!$order->validate()){
                    $order->addError('target_date', 'Укажите дату заказа и период доставки');
                    return [
                        'title' => "Добавление черновика в очередь",
                        'content' => $this->renderAjax('_to_queue_form', [
                            'draft' => $draft,
                            'order' => $order,
                            'productsDataProvider' => $productsDataProvider,
                        ]),
                        'footer' => Html::button('Закрыть',
                                ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
                            Html::button('Подтвердить', ['class' => 'btn btn-primary', 'type' => "submit"])

                    ];
                }

                $result = $draft->toQueue($order);
                if ($result['success']){
                    if (!$draft->save()){
                        Yii::error($draft->errors, '_error');
                    }
                    $order->status = $order::STATUS_ORDER_WAITING;
                    if (!$order->save()){
                        Yii::error($order->errors, '_error');
                    }
                    return [
                        'forceReload' => '#crud-datatable-pjax',
                        'forceClose' => true,
                    ];
                } else {
                    $order->addError('target_date', $result['error']);
                    return [
                        'title' => "Добавление черновика в очередь",
                        'content' => $this->renderAjax('_to_queue_form', [
                            'draft' => $draft,
                            'order' => $order,
                            'productsDataProvider' => $productsDataProvider,
                        ]),
                        'footer' => Html::button('Закрыть',
                                ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
                            Html::button('Подтвердить', ['class' => 'btn btn-primary', 'type' => "submit"])

                    ];
                }
            } else {
                return [
                    'title' => "Добавление черновика в очередь",
                    'content' => 'Ошибка загрузки данных для постановки в очередь',
                    'footer' => Html::button('Закрыть',
                            ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"])

                ];
            }
        }
    }

    /**
     * Выводит тест помощи по разделу
     * @return array
     */
    public function actionHelp()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        return [
            'title' => "Работа с черновиками заказов",
            'content' => $this->renderAjax('help'),
            'footer' => Html::button('Закрыть',
                    ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"])
        ];
    }
}
