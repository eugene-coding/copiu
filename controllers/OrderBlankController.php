<?php

namespace app\controllers;

use app\models\BuyerToOrderBlank;
use app\models\Users;
use Yii;
use app\models\OrderBlank;
use app\models\search\OrderBlankSearch;
use yii\base\InvalidConfigException;
use yii\db\Exception;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use \yii\web\Response;
use yii\helpers\Html;

/**
 * OrderBlankController implements the CRUD actions for OrderBlank model.
 */
class OrderBlankController extends Controller
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
                        'actions' => ['get-orders-by-date'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => function ($rule, $action) {
                            return Users::isAdmin();
                        }
                    ],

                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
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
     * Lists all OrderBlank models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new OrderBlankSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single OrderBlank model.
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
                'title' => "OrderBlank #" . $id,
                'content' => $this->renderAjax('view', [
                    'model' => $this->findModel($id),
                ]),
                'footer' => Html::button('Закрыть',
                        ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
                    Html::a('Редактировать', ['update', 'id' => $id],
                        ['class' => 'btn btn-primary', 'role' => 'modal-remote'])
            ];
        } else {
            return $this->render('view', [
                'model' => $this->findModel($id),
            ]);
        }
    }

    /**
     * Creates a new OrderBlank model.
     * For ajax request will return json object
     * and for non-ajax request if creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $request = Yii::$app->request;
        $model = new OrderBlank();

        if ($request->isAjax) {
            /*
            *   Process for ajax request
            */
            Yii::$app->response->format = Response::FORMAT_JSON;
            if ($request->isGet) {
                return [
                    'title' => "Добавление накладной",
                    'content' => $this->renderAjax('create', [
                        'model' => $model,
                    ]),
                    'footer' => Html::button('Закрыть',
                            ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
                        Html::button('Сохранить', ['class' => 'btn btn-primary', 'type' => "submit"])

                ];
            } else {
                if ($model->load($request->post())) {
                    $exists = $model->blankExistsInIiko();
                    Yii::info($exists, 'test');
                    if (!$exists) {
                        //Накладная не найдена
                        $model->addError('number', 'Накладная с указанным номером и датой не найдена');
                        return [
                            'title' => "Добавление накладной",
                            'content' => $this->renderAjax('create', [
                                'model' => $model,
                            ]),
                            'footer' => Html::button('Закрыть',
                                    ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
                                Html::button('Сохранить', ['class' => 'btn btn-primary', 'type' => "submit"])

                        ];
                    }

                    if (!$model->save()) {
                        Yii::error($model->errors, '_error');
                        //Ошибка сохранения
                        return [
                            'title' => "Добавление накладной",
                            'content' => $this->renderAjax('create', [
                                'model' => $model,
                            ]),
                            'footer' => Html::button('Закрыть',
                                    ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
                                Html::button('Сохранить', ['class' => 'btn btn-primary', 'type' => "submit"])

                        ];
                    }

                    return [
                        'forceReload' => '#crud-datatable-pjax',
                        'forceClose' => true,
                    ];
                } else {
                    return [
                        'title' => "Добавление накладной",
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
                return $this->redirect(['view', 'id' => $model->id]);
            } else {
                return $this->render('create', [
                    'model' => $model,
                ]);
            }
        }

    }

    /**
     * Updates an existing OrderBlank model.
     * For ajax request will return json object
     * and for non-ajax request if update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionUpdate($id)
    {
        $request = Yii::$app->request;
        $model = $this->findModel($id);
        $model->buyers = ArrayHelper::map($model->buyerToOrderBlanks, 'id', 'name');
        $model->buyers = array_keys($model->buyers);

        if ($request->isAjax) {
            /*
            *   Process for ajax request
            */
            Yii::$app->response->format = Response::FORMAT_JSON;
            if ($request->isGet) {
                return [
                    'title' => "Редактирование накладной #" . $model->number,
                    'content' => $this->renderAjax('update', [
                        'model' => $model,
                    ]),
                    'footer' => Html::button('Закрыть',
                            ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
                        Html::button('Сохранить', ['class' => 'btn btn-primary', 'type' => "submit"])
                ];
            } else {
                if ($model->load($request->post()) && $model->save()) {
                    if ($model->buyers) {
                        $rows = [];
                        foreach ($model->buyers as $buyer_id) {
                            $rows[] = [
                                $model->id,
                                $buyer_id
                            ];
                        }

                        try {
                            Yii::$app->db->createCommand()
                                ->batchInsert(BuyerToOrderBlank::tableName(), ['order_blank_id', 'buyer_id'], $rows)
                                ->execute();

                            $model->show_to_all = 0;
                            if (!$model->save()) {
                                Yii::error($model->errors, '_error');
                            }
                        } catch (Exception $e) {
                            Yii::error($e->getMessage(), '_error');
                        }


                    } else {
                        BuyerToOrderBlank::deleteAll(['order_blank_id' => $model->id]);
                    }
                    return [
                        'forceReload' => '#crud-datatable-pjax',
                        'forceClose' => true,
                    ];
                } else {
                    return [
                        'title' => "Редактирование накладной #" . $model->number,
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
                ]);
            }
        }
    }

    /**
     * Delete an existing OrderBlank model.
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
     * Delete multiple existing OrderBlank model.
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
     * Finds the OrderBlank model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return OrderBlank the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = OrderBlank::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    /**
     * Синхронизация накладных
     */
    public function actionSyncing()
    {
        set_time_limit(600);
        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            return OrderBlank::sync();
        } catch (InvalidConfigException $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Получает накладные на указзанную дату
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public function actionGetOrdersByDate()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = new OrderBlank();


        if (!$_POST['date']) {
            return [
                'success' => false,
                'error' => 'Не выбрана дата',
            ];
        }

        if (strtotime($_POST['date']) < time()) {
            $data = $model->getAllBlanksInfo();
            return [
                'success' => false,
                'error' => 'Дата заказа уже наступила. Заказ невозможен',
                'warning' => $data
            ];
        }

        $target_date = date('Y-m-d', strtotime($_POST['date']));

        $blanks = $model->getBlanksByDate($target_date);
        $data = $model->blanksToTable($blanks, $target_date);

        if (!$data) {
            $data = 'Бланки заказов отсуствуют';
        }

        return [
            'success' => true,
            'data' => $data,
        ];
    }
}
