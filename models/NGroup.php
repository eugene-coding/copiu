<?php

namespace app\models;

use app\models\query\NGroupQuery;
use Yii;
use yii\db\ActiveRecord;
use yii\web\Response;

/**
 * This is the model class for table "n_group".
 *
 * @property int $id
 * @property string|null $name Наименование
 * @property string|null $outer_id Внешний идентификатор
 * @property string|null $description Описание
 * @property string|null $num Артикул
 * @property string|null $code Код
 * @property int|null $parent_id Родительская группа
 *
 * @property Nomenclature[] $nomenclatures
 */
class NGroup extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'n_group';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['description'], 'string'],
            [['parent_id'], 'integer'],
            [['name', 'outer_id', 'num', 'code'], 'string', 'max' => 255],
            [['outer_id'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Наименование',
            'outer_id' => 'Внешний идентификатор',
            'description' => 'Описание',
            'num' => 'Артикул',
            'code' => 'Код',
            'parent_id' => 'Родительская группа',
        ];
    }

    /**
     * Gets query for [[Nomenclatures]].
     *
     * @return \yii\db\ActiveQuery|\app\models\query\NomenclatureQuery
     */
    public function getNomenclatures()
    {
        return $this->hasMany(Nomenclature::class, ['n_group_id' => 'id']);
    }

    /**
     * Gets query for parent self.
     *
     * @return \yii\db\ActiveQuery|\app\models\query\NGroupQuery
     */
    public function getParentGroup()
    {
        return $this->hasOne(Nomenclature::class, ['id' => 'parent_id']);
    }


    /**
     * {@inheritdoc}
     * @return NGroupQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new NGroupQuery(get_called_class());
    }

    /**
     * @param array $data Импортируемые данные
     * @return array
     */
    public function import($data)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        /** @var array $parent_groups Внешние идентификаторы только родительских категорий (не имеющих предка) */
        $parent_groups = self::find()->select(['outer_id'])->parents()->column();
        $child_groups = [];
        $outer_id_to_id = [];

        //Сначала добавляем группы, у которых нет предков (нет родительских групп)
        foreach ($data as $group) {
            if (!in_array($group['id'], $parent_groups)) {
                //Если группы нет в базе
                if (!$group['parent']) {
                    //Если у грппы нет предка
                    $model = new NGroup([
                        'name' => $group['name'],
                        'description' => $group['description'] ? $group['description'] : null,
                        'num' => $group['num'],
                        'code' => $group['code'] ? $group['code'] : null,
                        'outer_id' => $group['id'],
                    ]);
                    if (!$model->save()) {
                        Yii::error($model->errors, '_error');
                    }
                    $outer_id_to_id[$group['id']] = $model->id;
                } else {
                    //Если есть предок
                    $child_groups[] = $group;
                }
            }
        }
        Yii::info($child_groups, '_error');
        Yii::info($outer_id_to_id, '_error');

        //Импортируем группы у которых есть предок
        foreach ($child_groups as $group){
            $model = new NGroup([
                'name' => $group['name'],
                'description' => $group['description'],
                'num' => $group['num'],
                'code' => $group['code'],
                'outer_id' => $group['id'],
                'parent_id' => $outer_id_to_id[$group['id']]
            ]);

            if (!$model->save()) {
                Yii::error($model->errors, '_error');
            }
        }

        return [
            'success' => true,
            'data' => 'Номенклатурные группы импортированы',
        ];
    }
}
