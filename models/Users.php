<?php

namespace app\models;


use Yii;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;


/**
 * This is the model class for table "users".
 *
 * @property int $id
 * @property string $fio ФИО
 * @property string $login Логин
 * @property string $password Хеш пароля
 * @property string $new_password Новый пароль
 * @property int $role Роль
 * @property string $phone Телефон номер
 * @property string $email Email
 * @property string $avatar Путь к аватару пользователя
 * @property string $open_pass Пароль
 * @property string $last_activity Последняя активность
 * @property string $is_active Активен ли пользователь
 * @property string $activity_ip IP активного пользоватлея
 *
 * @property string $roleDescription Описание роли
 * @property Buyer $buyer Покупатель
 *
 */
class Users extends ActiveRecord
{
    const ROLE_ADMIN = 'admin';
    const ROLE_BUYER = 'buyer';

    public $new_password;
    public $image;

    public static function tableName()
    {
        return 'users';
    }

    public function rules()
    {
        return [
            [['fio', 'login', 'role'], 'required'],
            [['login'], 'unique'],
            [['email'], 'email'],
            [['avatar', 'open_pass'], 'string'],
            [['fio', 'login', 'password', 'new_password', 'phone', 'email', 'role'], 'string', 'max' => 255],
            [['open_pass'], 'string'],
            [['last_activity', 'activity_ip'], 'safe'],
            [['is_active'], 'integer'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'fio' => 'ФИО/Наименование',
            'login' => 'Логин',
            'password' => 'Пароль',
            'role' => 'Роль',
            'phone' => 'Телефон',
            'email' => 'Email',
            'avatar' => 'Фото',
            'image' => 'Фото',
            'new_password' => 'Новый пароль',
            'open_pass' => 'Пароль',
        ];
    }

    /**
     * @param bool $insert
     * @return bool
     * @throws \yii\base\Exception
     */
    public function beforeSave($insert)
    {
        if ($this->isNewRecord) {
            $this->open_pass = $this->password;
            $this->password = Yii::$app->security->generatePasswordHash($this->password);
        }

        if ($this->new_password != null) {
            $this->open_pass = $this->new_password;
            $this->password = Yii::$app->security->generatePasswordHash($this->new_password);
        }
        return parent::beforeSave($insert);
    }

    public function beforeDelete()
    {
        if ($this->id == 1) {
            return false;
        }
        return parent::beforeDelete();
    }

    /**
     * @throws \yii\base\Exception
     * @throws \yii\db\Exception
     */
    public function upload()
    {
        $fileName = $this->id . '-' . Yii::$app->security->generateRandomString() . '.' . $this->image->extension;
        if (!empty($this->image)) {
            if (file_exists('uploads/avatars/' . $this->avatar) && $this->avatar != null) {
                unlink('uploads/avatars/' . $this->avatar);
            }

            $this->image->saveAs('uploads/avatars/' . $fileName);
            Yii::$app->db->createCommand()->update('users', ['avatar' => $fileName], ['id' => $this->id])->execute();
        }
    }

    /**
     * @return bool
     */
    public static function isAdmin()
    {
        return Yii::$app->user->identity->role == 'admin';
    }

    /**
     * @return bool
     */
    public static function isBuyer()
    {
        return Yii::$app->user->identity->role == 'buyer';
    }

    /**
     * @return mixed
     */
    public function getAvatar()
    {
        if (!file_exists($this->avatar) || $this->avatar == '') {
            $path = Url::to(['@web/img/nouser.png']);
        } else {
            $path = Url::to(['@web/' . $this->avatar]);
        }
        return $path;
    }

    public function getRoleDescription($role_name = null)
    {
        if (!$role_name) {
            return $this->getRoles()[Yii::$app->user->identity->role];
        } else {
            return $this->getRoles()[$role_name];
        }
    }

    /**
     * Получает список ролей системы
     * @return array
     */
    public function getRoles()
    {
        return ArrayHelper::map(Yii::$app->authManager->roles, 'name', 'description');

    }

    public function getBuyer()
    {
        return $this->hasOne(Buyer::class, ['user_id' => 'id']);
    }

    /**
     * Записывает последнее время активности пользователя
     * @return void
     */
    public static function setActivity()
    {
        $model = Users::findOne(Yii::$app->user->identity->id);

        if ($model){
            $model->last_activity = date('Y-m-d H:i:s', time());
            $model->activity_ip = $_SERVER['REMOTE_ADDR'];
            $model->is_active = 1;
            if (!$model->save()) {
                Yii::error($model->errors, '_error');
            }
        }
    }

    /**
     * Проверяем сессию пользователя на активность
     * @return int
     */
    public static function sessionIsActive()
    {
        Yii::info('sessionIsActive()', 'test');
       $user = Users::findOne(Yii::$app->user->identity->id);
       return $user->is_active;
    }

    /**
     * Активна ли сессия по времени
     * @return bool
     */
    public function sessionIsActiveByTime()
    {
        Yii::info('sessionIsActiveByTime()', 'test');
        /** @var int $max_non_activity Максимальное время неактивности, секунд */
        $max_non_activity = 10 * 60;
        Yii::info($this->attributes, 'test');
        Yii::info("Максимальное время неактивности: 10 минут", 'test');
        Yii::info("С момента полседней активности прошло: " . (time() - strtotime($this->last_activity))/60 . ' мин.', 'test');

        if ((time() - strtotime($this->last_activity)) > $max_non_activity) {
            //Прошло больше максимально возможного времени неактивности
           return false;
        } else {
            return true;
        }
    }

    /**
     * Сверяет IP в базе с текущим IP
     * @return bool
     */
    public function matchingIp()
    {
        if (Users::isAdmin()){
            return true;
        }
        $ip = $this->getUser()->activity_ip;
        Yii::info("IP в базе: " . $ip, 'test');
        Yii::info("IP: " . $_SERVER['REMOTE_ADDR'], 'test');


        return $ip == $_SERVER['REMOTE_ADDR'];
    }

    public function getUser()
    {
        return Users::findOne(Yii::$app->user->identity->id);
    }


}
