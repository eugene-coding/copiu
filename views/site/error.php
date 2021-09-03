<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $name string */
/* @var $message string */
/* @var $exception Exception */

$this->title = $name;
?>
<section class="content">

    <div class="error-page">
        <h2 class="headline text-info"><i class="fa fa-warning text-yellow"></i></h2>

        <div class="error-content">
            <h3><?= $name ?></h3>

            <p>
                <b>
                    <?= nl2br(Html::encode($message)) ?>
                </b>
            </p>

            <p>
                Вышеуказанная ошибка произошла во время обработки веб-сервером вашего запроса.
                Пожалуйста, свяжитесь с администрацией и сообщите о времени и действиях, которые привели к данной ошибке. Спасибо.<br><br>

                Вы также можете вернуться на <a href='<?= Yii::$app->homeUrl ?>'>Главную страницу</a>
            </p>

            <form class='search-form'>
                <div class='input-group'>
                    <input type="text" name="search" class='form-control' placeholder="Search"/>

                    <div class="input-group-btn">
                        <button type="submit" name="submit" class="btn btn-primary"><i class="fa fa-search"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

</section>
