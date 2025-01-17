<?php

namespace app\controllers;

use app\models\Nomenclature;
use app\models\OrderBlank;
use app\models\OrderBlankToNomenclature;
use app\models\OrderLogging;
use app\models\OrderToNomenclature;
use app\models\Settings;
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
    protected $isBuyer;
    /**
     * @inheritdoc
     */
    public function behaviors(): array
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
    public function beforeAction($action): bool
    {
        $this->enableCsrfValidation = false;

        $this->isBuyer = !Yii::$app->user->can('admin');
        if ($this->isBuyer) {
            $this->layout = 'main-new';
        }
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
        Order::clean();

        $searchModel = new OrderSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        if (!Yii::$app->user->can('admin')) {
            $this->layout = 'main-new';
            return $this->render('/order-new/index', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            ]);
        } else {
            return $this->render('index', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            ]);
        }

    }

    /**
     * Displays a single Order model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionView(int $id)
    {
        $request = Yii::$app->request;
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
    public function actionDelete(int $id)
    {
        $model = $this->findModel($id);

        $model->log(OrderLogging::ACTION_ORDER_DELETE);
        $request = Yii::$app->request;
        $model->delete();

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
    protected function findModel(int $id): Order
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
    public function actionBulkAddProduct(int $order_id): array
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
    public function actionExcludeProduct(int $order_id, int $nomenclature_id): array
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
    public function actionIncludeProduct(int $order_id, int $nomenclature_id): array
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
    public function actionOrderProducts(int $id): string
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
        $user = Users::findOne(Yii::$app->user->id);
        $model->buyer_id = $user->buyer->id;
        //Yii::debug($model->attributes, 'test');

        if ($request->isPost) {
            $model->load($request->post());
            if (!$model->target_date) {
                Yii::$app->session->setFlash('warning', 'Не выбрана дата заказа');
            } else {
                $model->save();
                $this->redirect(['order-update', 'id' => $model->id]);
            }
        }

        $this->layout = 'main-new';
        return $this->render('/order-new/_form', [
            'model' => $model,
        ]);

    }

    /**
     * Редактирование заказа
     * @param int $id
     * @return string|Response
     * @throws \Exception
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionOrderUpdate(int $id)
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

        if ($this->isBuyer) {
            $this->layout = 'main-new';
        }
        $template = $this->isBuyer ? '/order-new/_form' : '_form';
        if ($request->isAjax) {
            return $this->render($template, [
                'model' => $model,
                'productsDataProvider' => $productsDataProvider,
            ]);
        }

        if ($request->isGet) {
            $model->step += 1;

            return $this->render($template, [
                'model' => $model,
                'productsDataProvider' => $productsDataProvider,
            ]);
        } else {
            $model->load($request->post());

            //Yii::debug($model->attributes, 'test');

            $model->orderProcessing();

            if ($model->step == 2) {
                $model->scenario = $model::SCENARIO_STEP_2;

                $total_count = $model->getTotalCountProducts();
                $comment_required = Settings::getValueByKey('comment_required');
                $model->comment = trim($model->comment);

                if ($comment_required == 1 && !$model->comment) {
                    $model->addError('comment', 'Необходимо заполнить комментарий');
                    Yii::$app->session->setFlash('warning', 'Необходимо заполнить комментарий');
                } elseif (!$model->validate('comment')) {
                    Yii::$app->session->setFlash('warning', $model->errors['comment']);
                }

                if (!$model->delivery_time_to || !$model->delivery_time_from) {
                    $model->addError('delivery_time_to', 'Не выбран период доставки');
                    Yii::$app->session->setFlash('warning', 'Не выбран период доставки');
                }

                if ($total_count == 0) {
                    $model->step = 2;
                    $model->addError('blanks', 'Не выбрано количество ни для одной позиции');
                    Yii::$app->session->setFlash('warning', 'Не выбрано количество ни для одной позиции');
                }

                $orderMinSum = Settings::getValueByKey('delivery_min_sum');
                $totalPrice = OrderToNomenclature::getTotalPrice($model->id);
                if ($totalPrice < $orderMinSum) {
                    $message = 'Минимальная сумма заказа ' . Yii::$app->formatter->asCurrency($orderMinSum);
                    $model->addError('total_price', $message);
                    Yii::$app->session->setFlash('warning', $message);
                }

                $addresses = $model->buyer->addresses ?? null;
                if ($addresses && !$model->delivery_address_id) {
                    $model->addError('delivery_address_id', 'Не указан адрес доставки');
                    Yii::$app->session->setFlash('warning', 'Не указан адрес доставки');
                }
                //Проверяем время доставки
                $model->checkDeliveryPeriod();
            }
            if ($model->errors) {
                Yii::error($model->errors, '_error');
            }
            if (!$model->hasErrors()) {
                $model->step++;
            }

            //Yii::debug('Шаг перед сохранением: ' . $model->step, 'test');
            //Yii::debug($model->attributes, 'test');
            if (!$model->hasErrors() && !$model->save()) {
                Yii::error($model->errors, '_error');
            }

            if ($model->step == 2) {
                //Обрабатываем заказ на основе кол-ва заказанных продуктов
                $model->orderProcessing();
                $productsDataProvider = $model->getProductDataProvider();
            }

            if ($model->step === 4) {
                //Yii::debug($model->attributes, 'test');
                //Формируем накладную
                $invoice_maked = $model->makeInvoice();
                if (!$invoice_maked) {
                    Yii::info('Ошибка формирования накладной', 'test');
                    $model->invoice_number = 'error';
                    $model->status = $model::STATUS_ERROR;
                    $model->log(OrderLogging::ACTION_ORDER_ERROR,
                        'Ошибка формирования накладной' . PHP_EOL);
                } else {
                    //Накладная сформировалась
                    Yii::info('Накладная успешно сформирована', 'test');
                    $model->status = $model::STATUS_WORK;
                }

                if ($model->deliveryCost && $invoice_maked) {
                    //Есть есть доставка (сумма доставки расчитана) и накладная сформирована
                    //Формируем акт оказания услуг (доставка)
                    Yii::info('Сумма доставки расчитана и накладная сформирована', 'test');
                    if (!$model->makeDeliveryAct()) {
                        Yii::info('Акт не сформирован', 'test');
                        $model->delivery_act_number = 'error';
                        $model->status = $model::STATUS_ERROR;
                    } else {
                        Yii::info('Акт сформирован', 'test');
                    }
                } else {
                    Yii::info('Нет доставки или сумма доставки 0 руб.', 'test');
                }
                //Yii::debug('Модель перед сохранением:', 'test');
                //Yii::debug($model->attributes, 'test');
                $model->save(false);
            }

            return $this->render($template, [
                'model' => $model,
                'productsDataProvider' => $productsDataProvider,
            ]);
        }
    }

    /**
     * Отменяет (удаляет) заказ
     * @param int $id Идентификатор заказа
     * @return Response
     * @throws \Throwable
     */
    public function actionCancel($id = null): Response
    {
        if ($id) {
            $model = Order::findOne($id);
            if ($model->status == $model::STATUS_IN_PROGRESS) {
                try {
                    if (!$model->delete()) {
                        Yii::error($model->errors, '_error');
                        $model->errlog();
                    }
                } catch (\Exception $e) {
                    Yii::error($e->getMessage(), '_error');
                    $model->errlog();

                }
            }
        }
        return $this->redirect('index');
    }

    public function actionChangeStatus(): array
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
     * @throws \yii\web\NotFoundHttpException
     */
    public function actionCopyOrder(int $basis_order_id)
    {
        $order_basis = $this->findModel($basis_order_id);

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

        $order->status = $order::STATUS_IN_PROGRESS;
        $order->blanks = implode(',', $blank_ids);
        $order->comment = $order_basis->comment;
        $order->delivery_time_from = $order_basis->delivery_time_from;
        $order->delivery_time_to = $order_basis->delivery_time_to;

        if (!$order->save()) {
            Yii::error($order->errors, '_error');
            Yii::$app->session->addFlash('error', 'Ошибка при копировании заказа. ' . json_encode($order->errors));
            return $this->redirect('index');
        }
        $order->log(OrderLogging::ACTION_ORDER_COPY, 'Базовый заказ: ' . $order_basis->id);

        //Добавляем продукты в новый заказ
        $rows = [];
        $query = OrderToNomenclature::find()
            ->andWhere(['order_id' => $order_basis->id]);

        /** @var OrderToNomenclature $item */
        foreach ($query->each() as $item) {
            /** OrderBlankToNomenclature $obtn */
            $obtn = $item->obtn;
            //Если в бланке уже нет продукта, который был раньше
            if (!$obtn) {
                continue;
            }

            $rows[] = [
                $order->id,
                $item->count,
                $obtn->id,
            ];

        }

        try {
            Yii::$app->db->createCommand()->batchInsert(OrderToNomenclature::tableName(), [
                'order_id',
                'count',
                'obtn_id',
            ], $rows)->execute();
        } catch (Exception $e) {
            Yii::error($order->errors, '_error');
            Yii::$app->session->addFlash('error',
                'Ошибка при сохранении нового заказа. ' . json_encode($e->getMessage()));
            return $this->redirect('index');
        }

        $products = [];
        /** @var OrderBlankToNomenclature $obtn */
        foreach ($order->getObtns() as $obtn) {
            array_push($products, $obtn->n->attributes);
        }

        $order->log(OrderLogging::ACTION_ORDER_COPY, $products);

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
    public function actionReMakeDocuments(int $id): Response
    {
        $model = $this->findModel($id);

        if ($model->invoice_number == 'error') {
            //Если ошибка формирования накладной
            if (!$model->makeInvoice()) {
                $model->invoice_number = 'error';
                $model->status = $model::STATUS_ERROR;
            } else {
                if ($model->deliveryCost && $model->delivery_act_number && $model->delivery_act_number != 'error') {
                    $model->status = $model::STATUS_WORK;
                }
            }
            $model->save();
        }

        if ($model->deliveryCost) {
            //Yii::debug('Есть доставка', 'test');
            if ($model->delivery_act_number == 'error') {
                //Yii::debug('Ошибка формирования Акта, формируем заново', 'test');
                //Если ошибка формирования Акта услуг
                //Формируем акт оказания услуг (доставка)
                if (!$model->makeDeliveryAct()) {
                    $model->delivery_act_number = 'error';
                    $model->status = $model::STATUS_ERROR;
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

    public function actionShowOrderErrorSettings(): array
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
    public function actionGetContent($order_id, $is_mobile = null): string
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = Order::findOne($order_id);

        $favoriteDataProvider = $model->getFavoriteDataProvider();
        $productsDataProvider = $model->getProductDataProvider(null, null, $favoriteDataProvider);

        Yii::$app->assetManager->bundles['yii\\web\\YiiAsset'] = false;
        Yii::$app->assetManager->bundles['yii\\web\\JqueryAsset'] = false;
        Yii::$app->assetManager->bundles['yii\\bootstrap\\BootstrapAsset'] = false;


        if ($is_mobile) {
            $template = $this->isBuyer ? '/order-new/_step_2_mobile' : '_step_2_mobile';
            return $this->renderAjax($template, [
                'model' => $model,
                'productsDataProvider' => $productsDataProvider,
                'favoriteDataProvider' => $favoriteDataProvider,
            ]);
        } else {
            $template = $this->isBuyer ? '/order-new/_step_2_desktop' : '_step_2_desktop';
            return $this->renderAjax($template, [
                'model' => $model,
                'productsDataProvider' => $productsDataProvider,
                'favoriteDataProvider' => $favoriteDataProvider,
            ]);
        }
    }

    /**
     * @param $order_id
     * @param $blank_id
     * @param $product_id
     * @param null $is_mobile
     * @return array
     */
    public function actionGetProductForTab($order_id, $blank_id, $product_id, $is_mobile = null): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = Order::findOne($order_id);

        $productsDataProvider = $model->getProductDataProvider($product_id, [$blank_id]);
        $model->search_product_id = $product_id;

        $blank_model = OrderBlank::findOne($blank_id);

        //Yii::debug($productsDataProvider->getModels()[$blank_model->number], 'test');
        if ($is_mobile) {
            //Yii::debug($model->attributes, 'test');
            return [
                'success' => true,
                'data' => $this->renderAjax('_nomenclature_mobile', [
                    'model' => $model,
                    'blank_id' => $blank_id,
                    'dataProvider' => $productsDataProvider->getModels()[$blank_model->number],
                ])
            ];
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
    public function actionAddProduct(): array
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

        if (!$count) {
            //Если выставлено кол-во продукта в ноль
            if ($model->id) {
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
        //Yii::debug($model->attributes, 'test');

        //Только для логирования
        $order = $model->order;
        $obtn = OrderBlankToNomenclature::findOne($obtn_id);
        $product = $obtn->n;

        $order->log(OrderLogging::ACTION_ORDER_ADD_PRODUCT,
            $product->num . ' ' . $product->name . ' кол-во: ' . $count);

        $total = OrderToNomenclature::find()
            ->select(['SUM(REPLACE(price,",",".") * count)'])
            ->andWhere(['order_id' => $order_id])
            ->scalar();

        return [
            'success' => true,
            'total' => $total ?: 0,
        ];
    }

    /**
     * Выставляет заказу статус Черновик
     * @param $id
     * @return array
     * @throws NotFoundHttpException
     */
    public function actionToDraft($id): array
    {
        $order = $this->findModel($id);
        $order->status = $order::STATUS_DRAFT;
        if (!$order->save()) {
            Yii::error($order->errors, '_error');
            $order->errlog();
        }
        $order->log(OrderLogging::ACTION_ORDER_CREATE_DRAFT);
        Yii::$app->response->format = Response::FORMAT_JSON;
        return [
            'success' => true,
        ];
    }

    /**
     * Удаляет черновик
     * @param $id
     * @return array|Response
     * @throws NotFoundHttpException
     * @throws \Exception
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionDeleteDraft($id)
    {
        $request = Yii::$app->request;

        $order = $this->findModel($id);

        if ($order->status == $order::STATUS_DRAFT) {
            $order->delete();
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
     * Переход к редактированию черновика
     * @param $id
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionUpdateDraft($id)
    {

        $request = Yii::$app->request;
        $order = $this->findModel($id);
        $order->log(OrderLogging::ACTION_ORDER_CREATE_FROM_DRAFT);

        if ($request->isPost) {
            $order->load($request->post());
            Yii::debug($order->attributes, 'update-draft');
            //Сохраняем, т.к. дата заказа скорее всего изменилась
            if (!$order->save()){
                Yii::error($order->errors, '_error');
            }
            $result = OrderBlank::getOrdersByDate($order->target_date);
            //Yii::debug($result, 'test');
            if ($result['success']) {
                return $this->redirect(['/order/order-update', 'id' => $order->id]);
            } else {
                //Если ошибка
                $order->addError('target_date', $result['error']);
            }
        }

        $template = $this->isBuyer ? '/order-new/_form' : '_form';
        return $this->render($template, [
            'model' => $order,
        ]);
    }


    public function actionDeliveryPeriod($start)
    {
        $delivery_period = (double)Settings::getValueByKey('delivery_period');


        $from = ((int)explode(':', $start)[0] + $delivery_period) . ':' . explode(':', $start)[1];
        $to_setting = Settings::getValueByKey('delivery_max_time');
        $to = (int)explode(':', $to_setting)[0];

        $intervals = $this->getTimeIntervals($from, $to);

        $result = '';
        if (empty($intervals)) {
            $result = "<option></option>";
        } else {
            foreach ($intervals as $key => $value) {
                $result .= "<option value='$key'>$value</option>";
            }
        }
        return $result;
    }

    private function getTimeIntervals($start, $end)
    {
        $result_arr = [];
        $startHour = (int)explode(':', $start)[0];
        $startMinute = explode(':', $start)[1];
        for ($i = $startHour; $i <= $end; $i++) {
            $val = str_pad($i, 2, '0', STR_PAD_LEFT) . ':' . $startMinute;
            $result_arr[$val . ':00'] = $val;
            if ($i == $startHour && $startMinute == '30') {

            } else {
                if ($i < $end) {
                    $val = str_pad($i, 2, '0', STR_PAD_LEFT) . ':30';
                    $result_arr[$val . ':00'] = $val;
                }
            }
        }

        return $result_arr;
    }
}

