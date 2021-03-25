<?php

/* @var $this yii\web\View */
/* @var array $data Массив с ошибками */
?>
<div class="settings-errors">
    <table class="table table-hover">
        <tbody>
        <?php foreach ($data as $key => $item): ?>
            <tr>
                <td><?= $item ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
