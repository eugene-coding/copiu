<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%users}}`.
 */
class m200321_105939_create_users_table extends Migration
{
    /**
     * @return bool|void
     * @throws \yii\base\Exception
     */
    public function safeUp()
    {
        $this->createTable('users', [
            'id' => $this->primaryKey(),
            'fio' => $this->string(255)->comment("ФИО"),
            'login' => $this->string(255)->comment("Логин"),
            'password' => $this->string(255)->comment("Пароль"),
            'role' => $this->string()->comment("Роль"),
            'phone' => $this->string(255)->comment("Телефон номер"),
            'email' => $this->string(255)->comment("Email"),
            'avatar' => $this->string(255)->comment("Аватар пользователя"),
        ]);

        $this->insert('users',array(
            'fio' => 'Иванов И.И.',
            'login' => 'admin',
            'email' => 'eshturdiyevumidjon@gmail.com',
            'password' => Yii::$app->security->generatePasswordHash('admin'),
            'role' => 'admin',
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('users');
    }
}
