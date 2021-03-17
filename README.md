Yii 2 Basic Project Template is a skeleton [Yii 2](http://www.yiiframework.com/) application best for
rapidly creating small projects.

[Установка](https://www.yiiframework.com/doc-2.0/guide-start-installation.html)

[Полное руководство](https://www.yiiframework.com/doc/guide/2.0)


Используется шаблон [Admin LTE](https://adminlte.io/)

Что имеем
-------------------
- Оформление Admin LTE
- Хранение пользователей в БД (Без CRUD)
- Интегрирован RBAC ([Настройка RBAC](https://habr.com/ru/post/235485/))

# RBAC
При добавлении/изменении роли правим:  
- `config/web` наименование ролей;
- `commands/RbacController` наименование ролей, правила, разрешения;
- `rbac/UserGroupRule.php` добавляем/меняем условия;
- в консоли выполняем команду `yii rbac/init`

## Пример использования
1. В контроллерах из метода `behaviors` убираем правило `access`
2. В контроллер добавляем метод:
```php
public function beforeAction($action)
{
    if (parent::beforeAction($action)) {
        if (!\Yii::$app->user->can($action->id)) {
            throw new ForbiddenHttpException('Доступ запрещён');
        }
        return true;
    } else {
        return false;
    }
}
 ```
 Или проверка доступа в методе контроллера:
 ```php
 public function actionUpdate($id)
 {
     if (!\Yii::$app->user->can('updateOwnProfile', ['profileId' => \Yii::$app->user->id])) {
         throw new ForbiddenHttpException('Доступ запрещен');
     }
     // ...
 } 
 ```
 
 #Подготовка проекта к работе
 ##Планировщик CRON
  ```
  Добавить на выполнение с периодичностью раз в сутки:
  https://mysite.ru/site/get-nomenclature
  
  Добавить на выполнение  с периодичностью каждые 2 минуты адрес:
   https://mysite.ru/site/sync-nomenclature
  ```
 