<?php

namespace app\models;

use app\components\IikoApiHelper;
use Yii;
use yii\base\Model;

/**
 * LoginForm is the model behind the login form.
 * @property string $username
 * @property string $password
 * @property int $rememberMe
 * @property bool $_user
 * @property-read User|null $user This property is read-only.
 *
 */
class LoginForm extends Model
{
    public $username;
    public $password;
    public $rememberMe = true;

    private $_user = false;


    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            // username and password are both required
            [['username', 'password'], 'required'],
            // rememberMe must be a boolean value
            ['rememberMe', 'boolean'],
            // password is validated by validatePassword()
            ['password', 'validatePassword'],
        ];
    }

    /**
     * @return array customized attribute labels
     */
    public function attributeLabels()
    {
        return [
            'username' => 'Логин',
            'password' => 'Пароль',
            'rememberMe' => 'Запомнить меня',
        ];
    }

    /**
     * Validates the password.
     * This method serves as the inline validation for password.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validatePassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();
//            Yii::info($user->attributes, 'test');

            if (!$user || !$user->validatePassword($this->password)) {
                $this->addError($attribute, 'Incorrect username or password.');
            }
        }
    }

    /**
     * Logs in a user using the provided username and password.
     * @return bool whether the user is logged in successfully
     */
    public function login()
    {
        if ($this->validate()) {
            return Yii::$app->user->login($this->getUser(), $this->rememberMe ? 3600*24*30 : 0);
        }

        return false;
    }

    /**
     * Finds user by [[username]]
     *
     * @return User|null
     */
    public function getUser()
    {
        if ($this->_user === false) {
            $this->_user = User::findByUsername($this->username);
        }
        return $this->_user;
    }

    /**
     * Проверяет баланс покупателя
     * @param Buyer $buyer
     * @return bool
     */
    public function checkBalance($buyer)
    {
        if ($buyer && $buyer->work_mode === Buyer::WORK_MODE_BALANCE_LIMIT){
            $helper = new IikoApiHelper();
            $buyer->balance = $helper->getBalance($buyer->outer_id);
            if (!$buyer->save()){
                Yii::error($buyer->errors, '_error');
            }

            if ($buyer->balance < $buyer->min_balance){
                return false;
            } else {
                return true;
            }
        } else {
            return true;
        }
    }

    public function checkAccess()
    {
        if (Users::isAdmin()) return ['success' => true];

        $user = $this->getUser();
        Yii::info($user->attributes, 'test');

        $buyer = $user->buyer;
        Yii::info($buyer->attributes, 'test');

        //Проверяем деактивацию
        if ($buyer->work_mode === $buyer::WORK_MODE_DEACTIVATED){
            return [
                'success' => false,
                'error' => 'Ваша учетная запись заблокирована.'
            ];
        }

        //Проверяем баланс
        if (!$this->checkBalance($buyer) ){
            return [
                'success' => false,
                'error' => 'Для совершения заказа недостаточен баланс.'
            ];
        }

        //Проверяем занятость сессии (уже есть кто-то в системе под этим пользователем или нет)
        if ($user->is_active){
            //Сессия активна
            //Проверяем время сессии
            Yii::info('Сессия активна', 'test');
            if (!$user->sessionIsActiveByTime()){
                Yii::info('Время сессии вышло', 'test');
                //У сессии вышло время
                //Переписываем своими данными
                $user::setActivity();
            } else {
                Yii::info('Время сессии НЕ вышло', 'test');
                //Время сессии не вышло
                //Проверяем IP
                Yii::info("IP в базе: " . $user->activity_ip, 'test');
                Yii::info("IP: " . $_SERVER['REMOTE_ADDR'], 'test');
                if ($user->activity_ip != $_SERVER['REMOTE_ADDR']){
                    Yii::info('IP не совпадают, сессия занята другим человеком', 'test');
                    //IP не совпадают, сессия занята другим человеком
                    return [
                        'success' => false,
                        'error' => 'Сессия занята, попробуйте позже'
                    ];
                } else {
                    Yii::info('IP совпадают, сессия занята этим же человеком', 'test');
                }
            }
        } else {
            Yii::info('Сессия не активна. Устанвливаем активность', 'test');
            //Сессия не активна
            $user::setActivity();
        }

        return [
            'success' => true,
        ];
    }
}
