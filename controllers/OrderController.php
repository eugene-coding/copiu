<?php

namespace app\controllers;

use app\models\Nomenclature;
use app\models\OrderToNomenclature;
use app\models\Users;
use Yii;
use app\models\Order;
use app\models\search\OrderSearch;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\web\Controller;
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
     * Lists all Order models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new OrderSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

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
        if ($request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => "Order #" . $id,
                'content' => $this->renderAjax('view', [
                    'model' => $this->findModel($id),
                ]),
                'footer' => Html::button('Close', ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
                    Html::a('Edit', ['update', 'id' => $id], ['class' => 'btn btn-primary', 'role' => 'modal-remote'])
            ];
        } else {
            return $this->render('view', [
                'model' => $this->findModel($id),
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
                    'footer' => Html::button('Close',
                            ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
                        Html::button('Save', ['class' => 'btn btn-primary', 'type' => "submit"])

                ];
            } else {
                if ($model->load($request->post()) && $model->save()) {
                    return [
                        'forceReload' => '#crud-datatable-pjax',
                        'title' => "Create new Order",
                        'content' => '<span class="text-success">Create Order success</span>',
                        'footer' => Html::button('Close',
                                ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
                            Html::a('Create More', ['create'], ['class' => 'btn btn-primary', 'role' => 'modal-remote'])

                    ];
                } else {
                    return [
                        'title' => "Create new Order",
                        'content' => $this->renderAjax('create', [
                            'model' => $model,
                        ]),
                        'footer' => Html::button('Close',
                                ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
                            Html::button('Save', ['class' => 'btn btn-primary', 'type' => "submit"])

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
//                    'footer' => Html::button('Close',
//                            ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
//                        Html::button('Save', ['class' => 'btn btn-primary', 'type' => "submit"])
//                ];
            } else {
                if ($model->load($request->post()) && $model->save()) {
                    return [
                        'forceReload' => '#crud-datatable-pjax',
                        'title' => "Order #" . $id,
                        'content' => $this->renderAjax('view', [
                            'model' => $model,
                        ]),
                        'footer' => Html::button('Close',
                                ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
                            Html::a('Edit', ['update', 'id' => $id],
                                ['class' => 'btn btn-primary', 'role' => 'modal-remote'])
                    ];
                } else {
                    return [
                        'title' => "Update Order #" . $id,
                        'content' => $this->renderAjax('update', [
                            'model' => $model,
                        ]),
                        'footer' => Html::button('Close',
                                ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
                            Html::button('Save', ['class' => 'btn btn-primary', 'type' => "submit"])
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
        $request = Yii::$app->request;
        $model = new Order();
        $user = Users::findOne(Yii::$app->user->identity->id);
        $model->buyer_id = $user->buyer->id;
        Yii::info($model->attributes, 'test');

        if ($request->isPost) {
            $model->load($request->post());
            $model->save();
            $this->redirect(['order-update', 'id' => $model->id]);
        } else {
            return $this->render('_form', [
                'model' => $model,
            ]);
        }

    }

    /**
     * Редактирование заказа
     * @param $id
     * @return string
     */
    public function actionOrderUpdate($id)
    {
        $request = Yii::$app->request;
        $model = Order::findOne($id);

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
            return $this->render('_form', [
                'model' => $model,
                'orderToNomenclatureDataProvider' => $orderToNomenclatureDataProvider,
                'productsDataProvider' => $productsDataProvider,
            ]);
        }

        if ($request->isGet) {
            $model->step += 1;

            return $this->render('_form', [
                'model' => $model,
                'orderToNomenclatureDataProvider' => $orderToNomenclatureDataProvider,
                'productsDataProvider' => $productsDataProvider,
            ]);
        } else {
            $model->load($request->post());
            if ($model->count) {
                foreach ($model->count as $nomenclature_id => $count) {
                    $n = Nomenclature::findOne($nomenclature_id);

                    $otn = OrderToNomenclature::find()
                        ->andWhere(['order_id' => $model->id, 'nomenclature_id' => $nomenclature_id])->one();
                    if (!$otn) {
                        $otn = new OrderToNomenclature();
                        $otn->order_id = $model->id;
                        $otn->nomenclature_id = $nomenclature_id;
                    }
                    $otn->price = $n->getPriceForBuyer();
                    $otn->count = $count;

                    if (!$otn->save()) {
                        Yii::error($otn->errors, '_error');
                        $model->step--;
                    }
                }
            }
            if ($model->delivery_time_from) {
                $from = date('H', strtotime($model->delivery_time_from));
                $to = date('H', strtotime($model->delivery_time_to));
                if (!$to) {
                    $model->delivery_time_to = date('H:i', strtotime($from) + (60 * 60 * 2));
                    $to = date('H', strtotime($model->delivery_time_to));
                }
                Yii::info('FROM: ' . $from, 'test');
                Yii::info('TO: ' . $to, 'test');
                if ($from > $to) {
                    $model->addError('error_delivery_time', 'Конечное время должно быть больше начального');
                    $model->step--;
                } elseif(($to - $from) < 2) {
                    $model->addError('error_delivery_time', 'Уведичте период доставки');
                    $model->step--;
                }
            }
            $model->step++;
            if (!$model->hasErrors() && !$model->save()) {
                Yii::error($model->errors, '_error');
            }

            return $this->render('_form', [
                'model' => $model,
                'orderToNomenclatureDataProvider' => $orderToNomenclatureDataProvider,
                'productsDataProvider' => $productsDataProvider,
            ]);
        }
    }


    /**
     * Отссеняет (удаляет) заказ
     * @param int $id Идентификатор заказа
     * @return Response
     * @throws \Exception
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionCancel($id)
    {
        $model = Order::findOne($id);

        if (!$model->delete()) {
            \Yii::error($model->errors, '_error');
        }

        return $this->redirect('index');
    }
}