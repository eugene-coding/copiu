<?php

namespace app\controllers;

use app\models\Nomenclature;
use app\models\OrderBlank;
use app\models\OrderBlankToNomenclature;
use app\models\OrderToNomenclature;
use app\models\Users;
use Yii;
use app\models\Order;
use app\models\search\OrderSearch;
use yii\data\ActiveDataProvider;
use yii\db\Exception;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use \yii\web\Response;
use yii\helpers\Html;

/**
 * OrderController implements the CRUD actions for Order model.
 */
class OrderController extends Controller
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

    public function afterAction($action, $result)
    {
        Users::setActivity();
        return parent::afterAction($action, $result);
    }

    /**
     * Lists all Order models.
     * @return mixed
     */
    public function actionIndex()
    {
        $user = (new Users())->getUser();
        //Проверяем IP пользователя
        if (!$user->matchingIp()) {
            //Уже залогинен другой пользователь
            Yii::$app->user->logout();
            return $this->goHome();
        }
        $searchModel = new OrderSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        Order::clean();

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Order model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionView($id)
    {
        $request = Yii::$app->request;
        /** @var Order $model */
        $model = $this->findModel($id);

        if ($request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => "Заказ #" . $id,
                'content' => $this->renderAjax('view', [
                    'model' => $model,
                ]),
                'footer' => Html::button('Закрыть', ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"])
            ];
        } else {
            return $this->render('view', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Creates a new Order model.
     * For ajax request will return json object
     * and for non-ajax request if creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $request = Yii::$app->request;
        $model = new Order();
        $model->buyer_id = Yii::$app->user->identity->id;
        $model->status = $model::STATUS_DRAFT;


        if ($request->isAjax) {
            /*
            *   Process for ajax request
            */
            Yii::$app->response->format = Response::FORMAT_JSON;
            if ($request->isGet) {
                return [
                    'title' => "Добавление заказа",
                    'content' => $this->renderAjax('create', [
                        'model' => $model,
                        'step' => 1,
                    ]),
                    'footer' => Html::button('Закрыть',
                            ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
                        Html::button('Сохранить', ['class' => 'btn btn-primary', 'type' => "submit"])

                ];
            } else {
                if ($model->load($request->post()) && $model->save()) {
                    return [
                        'forceReload' => '#crud-datatable-pjax',
                        'title' => "Create new Order",
                        'content' => '<span class="text-success">Create Order success</span>',
                        'footer' => Html::button('Закрыть',
                                ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
                            Html::a('Create More', ['create'], ['class' => 'btn btn-primary', 'role' => 'modal-remote'])

                    ];
                } else {
                    return [
                        'title' => "Create new Order",
                        'content' => $this->renderAjax('create', [
                            'model' => $model,
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
            if ($model->load($request->post()) && $model->save()) {
                Yii::info($model->blanks, 'test');

                $productsDataProvider = new ActiveDataProvider([
                    'query' => Nomenclature::find()
                        ->joinWith(['orderBlanks'])
                        ->andWhere(['IN', 'order_blank.id', $model->blanks])
                ]);

                return $this->render('update', [
                    'model' => $model,
                    'step' => 2,
                    'productsDataProvider' => $productsDataProvider
                ]);
            } else {
                return $this->render('create', [
                    'model' => $model,
                    'step' => 1,
                ]);
            }
        }

    }

    /**
     * Updates an existing Order model.
     * For ajax request will return json object
     * and for non-ajax request if update is successful, the browser will be redirected to the 'view' page.
     * @param int $step Шаг заказа
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionUpdate($id, $step = null)
    {
        $request = Yii::$app->request;

        $model = $this->findModel($id);
        $blanks = explode(',', $model->blanks);
        $orderToNomenclatureDataProvider = new ActiveDataProvider([
            'query' => Nomenclature::find()
                ->joinWith(['orderToNomenclature'])
                ->andWhere(['order_to_nomenclature.order_id' => $model->id]),
        ]);
        $orderToNomenclatureDataProvider->pagination = false;
        $products_in_order = OrderToNomenclature::find()
            ->select(['nomenclature_id'])
            ->andWhere(['order_id' => $model->id])
            ->column();

        $productsDataProvider = new ActiveDataProvider([
            'query' => Nomenclature::find()
                ->joinWith(['orderBlanks'])
                ->andWhere(['IN', 'order_blank.id', $blanks])
                ->andWhere(['NOT IN', 'nomenclature.id', $products_in_order])
        ]);
        $productsDataProvider->pagination = false;

        if ($request->isAjax) {
            /*
            *   Process for ajax request
            */
            Yii::$app->response->format = Response::FORMAT_JSON;
            if ($request->isGet) {
                $_pjax = $request->get('_pjax');
                return [
                    'forceClose' => true,
                    'forceReload' => $_pjax
                ];
//                return [
//                    'title' => "Update Order #" . $id,
//                    'content' => $this->renderAjax('update', [
//                        'model' => $model,
//                    ]),
//                    'footer' => Html::button('Закрыть',
//                            ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
//                        Html::button('Сохранить', ['class' => 'btn btn-primary', 'type' => "submit"])
//                ];
            } else {
                if ($model->load($request->post()) && $model->save()) {
                    return [
                        'forceReload' => '#crud-datatable-pjax',
                        'title' => "Order #" . $id,
                        'content' => $this->renderAjax('view', [
                            'model' => $model,
                        ]),
                        'footer' => Html::button('Закрыть',
                                ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
                            Html::a('Редактировать', ['update', 'id' => $id],
                                ['class' => 'btn btn-primary', 'role' => 'modal-remote'])
                    ];
                } else {
                    return [
                        'title' => "Update Order #" . $id,
                        'content' => $this->renderAjax('update', [
                            'model' => $model,
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
            if ($model->load($request->post()) && $model->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
            } else {

                return $this->render('update', [
                    'model' => $model,
                    'step' => $step,
                    'productsDataProvider' => $productsDataProvider,
                    'orderToNomenclatureDataProvider' => $orderToNomenclatureDataProvider,
                ]);
            }
        }
    }

    /**
     * Delete an existing Order model.
     * For ajax request will return json object
     * and for non-ajax request if deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException
     * @throws \Exception
     * @throws \Throwable
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
     * Delete multiple existing Order model.
     * For ajax request will return json object
     * and for non-ajax request if deletion is successful, the browser will be redirected to the 'index' page.
     * @return mixed
     * @throws NotFoundHttpException
     * @throws \Exception
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionBulkDelete()
    {
        $request = Yii::$app->request;
        $pks = explode(',', $request->post('pks')); // Array or selected records primary keys
        foreach ($pks as $pk) {
            $model = $this->findModel($pk);
            $model->delete();
        }

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
     * Finds the Order model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Order the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Order::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    /**
     * Добавляет продукты в заказ
     * @param int $order_id Заказ
     * @return array
     */
    public function actionBulkAddProduct($order_id)
    {
        $request = Yii::$app->request;

        $selection = $request->post('selection');

        foreach ($selection as $item) {
            $nomenclature_model = Nomenclature::findOne($item);
            $price = $nomenclature_model->getPriceForBuyer();

            $product_exists = OrderToNomenclature::find()
                ->andWhere(['order_id' => $order_id, 'nomenclature_id' => $item])
                ->exists();
            if ($product_exists) {
                continue;
            }

            $model = new OrderToNomenclature([
                'order_id' => $order_id,
                'nomenclature_id' => $item,
                'price' => $price,
                'count' => 1,
            ]);

            if (!$model->save()) {
                Yii::error($model->errors, '_error');
            }
        }

        Yii::$app->response->format = Response::FORMAT_JSON;

        return [
            'forceReload' => '#selected-product-pjax',
            'forceClose' => true,
        ];
    }

    /**
     * Исключает продукт из заказа
     * @param $order_id
     * @param int $nomenclature_id Позиция номенклатуры
     * @return array
     * @throws \Exception
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionExcludeProduct($order_id, $nomenclature_id)
    {
        /** @var OrderToNomenclature $model */
        $model = OrderToNomenclature::find()
            ->andWhere(['nomenclature_id' => $nomenclature_id, 'order_id' => $order_id])
            ->one();
        if ($model && !$model->delete()) {
            Yii::error($model->errors, '_error');
        }
        Yii::$app->response->format = Response::FORMAT_JSON;
        return [
            'forceClose' => true,
            'forceReload' => '#order-pjax',
        ];

    }

    /**
     * Включает продукт в заказ
     * @param $order_id
     * @param int $nomenclature_id Позиция номенклатуры
     * @return array
     */
    public function actionIncludeProduct($order_id, $nomenclature_id)
    {
        $nomenclature_model = Nomenclature::findOne($nomenclature_id);

        $model = new OrderToNomenclature();
        $model->order_id = $order_id;
        $model->nomenclature_id = $nomenclature_id;
        $model->count = 0;
        $model->price = $nomenclature_model->getPriceForBuyer();

        if (!$model->save()) {
            Yii::error($model->errors, '_error');
        }

        Yii::$app->response->format = Response::FORMAT_JSON;
        return [
            'forceClose' => true,
            'forceReload' => '#order-pjax',
        ];
    }

    /**
     * @param int $id Идентификатор заказа
     * @return string
     */
    public function actionOrderProducts($id)
    {
        $model = Order::findOne($id);
        $dataProvider = new ActiveDataProvider([
            'query' => Nomenclature::find()
                ->joinWith(['orderToNomenclature'])
                ->andWhere(['order_to_nomenclature.order_id' => $model->id]),
        ]);
        return $this->renderAjax('_order_nomenclature', [
            'model' => $model,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionOrderCreate()
    {
        $user = (new Users())->getUser();
        //Проверяем IP пользователя
        if (!$user->matchingIp()) {
            //Уже залогинен другой пользователь
            Yii::$app->user->logout();
            return $this->goHome();
        }

        $request = Yii::$app->request;
        $model = new Order();
        $user = Users::findOne(Yii::$app->user->identity->id);
        $model->buyer_id = $user->buyer->id;
        Yii::info($model->attributes, 'test');

        if ($request->isPost) {
            $model->load($request->post());
            if (!$model->target_date) {
                Yii::$app->session->setFlash('warning', 'Не выбрана дата заказа');
            } else {
                $model->save();
                $this->redirect(['order-update', 'id' => $model->id]);
            }
        }

        return $this->render('_form', [
            'model' => $model,
        ]);

    }

    /**
     * Редактирование заказа
     * @param $id
     * @return string
     * @throws \Exception
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionOrderUpdate($id)
    {
        $user = (new Users())->getUser();
        //Проверяем IP пользователя
        if (!$user->matchingIp()) {
            //Уже залогинен другой пользователь
            Yii::$app->user->logout();
            return $this->goHome();
        }

        $request = Yii::$app->request;
        $model = Order::findOne($id);
        $productsDataProvider = $model->getProductDataProvider();

        if ($request->isAjax) {
            return $this->render('_form', [
                'model' => $model,
                'productsDataProvider' => $productsDataProvider,
            ]);
        }

        if ($request->isGet) {
            $model->step += 1;

            return $this->render('_form', [
                'model' => $model,
                'productsDataProvider' => $productsDataProvider,
            ]);
        } else {
            $model->load($request->post());

            Yii::info($model->attributes, 'test');


            $model->orderProcessing();

            if ($model->step == 2) {
                $total_count = $model->getTotalCountProducts();

                if ($total_count == 0) {
                    $model->step = 2;
                    $model->addError('blanks', 'Не выбрано количество ни для одной позиции');
                    Yii::$app->session->setFlash('warning', 'Не выбрано количество ни для одной позиции');
                }
            }

            //Проверяем время доставки
            if ($model->delivery_time_from) {
                $from = date('H', strtotime($model->delivery_time_from));
                $to = date('H', strtotime($model->delivery_time_to));
                if (!$to) {
                    $model->delivery_time_to = date('H:i', strtotime($from) + (60 * 60 * 2));
                    $to = date('H', strtotime($model->delivery_time_to));
                }
                if ($from > $to) {
                    Yii::$app->session->setFlash('warning', 'Конечное время доставки должно быть больше начального');
                    $model->addError('error_delivery_time', 'Конечное время должно быть больше начального');
                } elseif (($to - $from) < 2) {
                    Yii::$app->session->setFlash('warning', 'Увеличьте период доставки');
                    $model->addError('error_delivery_time', 'Увеличьте период доставки');
                }
            }

            if (!$model->hasErrors()) {
                $model->step++;
            }

            Yii::info('Шаг перед сохранением: ' . $model->step, 'test');
            if (!$model->hasErrors() && !$model->save()) {
                Yii::error($model->errors, '_error');

            }

            if ($model->step == 2) {
                //Обрабатываем заказ на основе кол-ва заказанных продуктов
                $model->orderProcessing();
                $productsDataProvider = $model->getProductDataProvider();
            }

            if ($model->step === 4) {
                //Формируем накладную
                $invoice_maked = $model->makeInvoice();
                if (!$invoice_maked) {
                    $model->invoice_number = 'error';
                    $model->status = $model::STATUS_DRAFT;
                    $model->save();
                }

                if ($model->deliveryCost && $invoice_maked) {
                    //Формируем акт оказания услуг (доставка)
                    if (!$model->makeDeliveryAct()) {
                        $model->delivery_act_number = 'error';
                        $model->status = $model::STATUS_DRAFT;
                        $model->save();
                    }
                }
            }

            return $this->render('_form', [
                'model' => $model,
                'productsDataProvider' => $productsDataProvider,
            ]);
        }
    }

    /**
     * Отменяет (удаляет) заказ
     * @param int $id Идентификатор заказа
     * @return Response
     * @throws \Exception
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionCancel($id = null)
    {
        if ($id) {
            $model = Order::findOne($id);

            try {
                if (!$model->delete()) {
                    \Yii::error($model->errors, '_error');
                }
            } catch (\Exception $e) {
                Yii::error($e->getMessage(), '_error');
            }
        }

        return $this->redirect('index');
    }

    public function actionChangeStatus()
    {
        $request = Yii::$app->request;
        Yii::$app->response->format = Response::FORMAT_JSON;

        $model = Order::findOne($request->post('id'));

        if (!$model) {
            return [
                'success' => false,
                'error' => 'Заказ не найден',
            ];
        }

        $model->status = $request->post('status');

        if (!$model->save()) {
            Yii::error($model->errors, '_error');
            return [
                'success' => false,
                'error' => json_encode($model->errors),
            ];
        }
        return [
            'success' => true,
            'forceReload' => '#crud-datatable-pjax',
        ];
    }

    /**
     * @param int $basis_order_id Идентификатор заказа, на основе которого будет сформирован новый заказ
     * @return string|Response
     */
    public function actionCopyOrder($basis_order_id)
    {
        $order_basis = Order::findOne($basis_order_id);
        $order = new Order();
        $order->buyer_id = $order_basis->buyer_id;


        //Бланки заказов
        $order_blanks = explode(',', $order_basis->blanks);
        $blank_ids = null;

        if ($order_blanks) {
            //Заново получаем id бланков, т.к. их может уже не быть
            $blank_ids = OrderBlank::find()->select(['id'])->andWhere(['IN', 'id', $order_blanks])->column();
        }

        if (!$blank_ids) {
            //Бланки заказов уже удалены из системы
            Yii::$app->session->addFlash('error',
                'Ошибка при копировании заказа. Бланки заказов, указанные в заказе-источнике, отсутствуют');
            return $this->redirect('index');
        }

        $order->status = 1;
        $order->blanks = implode(',', $blank_ids);
        $order->comment = $order_basis->comment;

        if (!$order->save()) {
            Yii::error($order->errors, '_error');
            Yii::$app->session->addFlash('error', 'Ошибка при копировании заказа. ' . json_encode($order->errors));
            return $this->redirect('index');
        }

//        //Получаем список ID продуктов из бланков заказа-источника (удаленные бланки не попадают в выдачу)
//        $basis_product_ids = OrderBlankToNomenclature::find()
//            ->select(['n_id'])
//            ->andWhere([
//                'IN',
//                'ob_id',
//                $blank_ids
//            ])->column();

        //Добавляем продукты в новый заказ
        $rows = [];
        $query = OrderToNomenclature::find()
            ->andWhere(['order_id' => $order_basis->id]);

        /** @var OrderToNomenclature $item */
        foreach ($query->each() as $item) {
            /** @var OrderBlankToNomenclature $obtn */
            $obtn = $item->obtn;
            //Если в бланке уже нет продукта, который был раньше
            if (!$obtn) {
                continue;
            }

            $rows[] = [
                $order->id,
                $obtn->getPriceForOrder($order->id), //Цену рассчитываем заново, т.к. цена может измениться
                $item->count,
                $obtn->id,
            ];

        }

        try {
            Yii::$app->db->createCommand()->batchInsert(OrderToNomenclature::tableName(), [
                'order_id',
                'price',
                'count',
                'obtn_id',
            ], $rows)->execute();
        } catch (Exception $e) {
            Yii::error($order->errors, '_error');
            Yii::$app->session->addFlash('error',
                'Ошибка при сохранении нового заказа. ' . json_encode($e->getMessage()));
            return $this->redirect('index');
        }

        $request = Yii::$app->request;
        if ($request->isPost) {
            $order->load($request->post());
            $order->save();
            return $this->redirect(['order-update', 'id' => $order->id]);
        } else {
            return $this->render('_form', [
                'model' => $order,
            ]);
        }
    }

    /**
     * Проверяет наличие накладной и акта услуг, при необходимости - формирует документ
     * @param int $id Идентификатор
     * @return Response
     * @throws NotFoundHttpException
     */
    public function actionReMakeDocuments($id)
    {
        $model = $this->findModel($id);

        if ($model->invoice_number == 'error') {
            //Если ошибка формирования накладной
            if (!$model->makeInvoice()) {
                $model->invoice_number = 'error';
                $model->status = $model::STATUS_DRAFT;
            } else {
                if ($model->deliveryCost && $model->delivery_act_number && $model->delivery_act_number != 'error') {
                    $model->status = $model::STATUS_WORK;
                }
            }
            $model->save();
        }

        if ($model->deliveryCost) {
            Yii::info('Есть доставка', 'test');
            if ($model->delivery_act_number == 'error') {
                Yii::info('Ошибка формирования Акта, формируем заново', 'test');
                //Если ошибка формирования Акта услуг
                //Формируем акт оказания услуг (доставка)
                if (!$model->makeDeliveryAct()) {
                    $model->delivery_act_number = 'error';
                    $model->status = $model::STATUS_DRAFT;
                } else {
                    if ($model->invoice_number && $model->invoice_number != 'error') {
                        $model->status = $model::STATUS_WORK;
                    }
                }
            }
            $model->save();
        }

        return $this->redirect('index');
    }

    public function actionShowOrderErrorSettings()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        return [
            'title' => 'Создание заказа',
            'content' =>
                '<b class="text-danger">Создание заказа невозможно, т.к. отсутствуют необходимые настройки. Обратитесь к администратору системы</b>',
            'footer' => Html::button('Закрыть',
                ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"])
        ];
    }

    /**
     * @param $order_id
     * @param $is_mobile
     * @return string
     */
    public function actionGetContent($order_id, $is_mobile = null)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = Order::findOne($order_id);
        $productsDataProvider = $model->getProductDataProvider();

        if ($is_mobile) {
            return $this->renderAjax('_step_2_mobile', [
                'model' => $model,
                'productsDataProvider' => $productsDataProvider,
            ]);
        } else {
            return $this->renderAjax('_step_2_desktop', [
                'model' => $model,
                'productsDataProvider' => $productsDataProvider,
            ]);
        }
    }

    /**
     * @param $order_id
     * @param $blank_id
     * @param $product_id
     * @param null $is_mobile
     * @return array|string
     */
    public function actionGetProductForTab($order_id, $blank_id, $product_id, $is_mobile = null)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = Order::findOne($order_id);

        $product =  Nomenclature::findOne($product_id);
        if ($product){
            $string = $product->name;
        } else {
            $string = null;
        }
        $productsDataProvider = $model->getProductDataProvider($string, [$blank_id]);
        $model->search_product_id =$product_id;

        $blank_model = OrderBlank::findOne($blank_id);
        if ($is_mobile) {
            return $this->renderAjax('_nomenclature', [
                'model' => $model,
                'dataProvider' => $productsDataProvider,
            ]);
        } else {
            return [
                'success' => true,
                'data' => $this->renderAjax('_nomenclature', [
                    'model' => $model,
                    'blank_id' => $blank_id,
                    'dataProvider' => $productsDataProvider->getModels()[$blank_model->number],
                ])
            ];
        }
    }

    /**
     * Добавляет продукт в заказ
     * @return array
     * @throws \Exception
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionAddProduct()
    {
        $request = Yii::$app->request->post();
        Yii::$app->response->format = Response::FORMAT_JSON;

        $order_id = $request['order_id'];
        $obtn_id = $request['obtn_id'];
        $count = $request['count'];
        $price = $request['price'];


        $model = OrderToNomenclature::find()
            ->andWhere(['order_id' => $order_id, 'obtn_id' => $obtn_id])
            ->one();

        if (!$model) {
            $model = new OrderToNomenclature([
                'order_id' => $order_id,
                'obtn_id' => $obtn_id
            ]);
        }
        Yii::info($model->attributes, 'test');

        if (!$count){
            //Если выставлено кол-во продукта в ноль
            if ($model->id){
                //Если запись есть в базе
                $model->delete();
            }
        } else {
            $model->count = $count;
            $model->price = $price;

            if (!$model->save()) {
                Yii::error($model->errors, '_error');
            }
        }

        $total = OrderToNomenclature::find()
            ->select(['SUM(REPLACE(price,",",".") * count)'])
            ->andWhere(['order_id' => $order_id])
            ->scalar();

        return [
            'success' => true,
            'total' => $total ?: 0,
        ];
    }
}

