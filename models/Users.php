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
 * @property string $password Пароль
 * @property int $role Роль
 * @property string $phone Телефон номер
 * @property string $email Email
 * @property string $avatar Путь к аватару пользователя
 *
 * @property string $roleDescription Описание роли
 *
 */
class Users extends ActiveRecord
{
    public $new_password;
    public $image;


    public static function tableName()
    {
        return 'users';
    }

    public function rules()
    {
        return [
            [['fio', 'login', 'password', 'role', 'email'], 'required'],
            [['login'], 'unique'],
            [['email'], 'email'],
            [['avatar'], 'string'],
            [['fio', 'login', 'password', 'new_password', 'phone', 'email', 'role'], 'string', 'max' => 255],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'fio' => 'ФИО',
            'login' => 'Логин',
            'password' => 'Пароль',
            'role' => 'Роль',
            'phone' => 'Телефон',
            'email' => 'Email',
            'avatar' => 'Фото',
            'image' => 'Фото',
            'new_password' => 'Новый пароль',

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
            $this->password = Yii::$app->security->generatePasswordHash($this->password);
        }

        if ($this->new_password != null) {
            $this->password = Yii::$app->security->generatePasswordHash($this->new_password);
        }
        return parent::beforeSave($insert);
    }

    public function beforeDelete()
    {
        if ($this->id == 1){
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
        if (!$role_name){
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
        return ArrayHelper::map(Yii::$app->authManager->roles,'name','description');

    }

}
