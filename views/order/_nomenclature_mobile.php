<?php

/* @var $model app\models\Order */
/* @var $blank_id integer */
/* @var $products array */

$counter = 1;

?>

<?php foreach ($products as $product): ?>
    <div class="card">
        <div class="row">
            <div class="col-md-12 card-name">
                <?php if ($product['description']): ?>
                    <i class="glyphicon glyphicon-info-sign showDescription"></i>
                <?php endif; ?>
                <b> <?= $product['name'] ?></b>
            </div>
        </div>
        <div class="row" style="display: none">
            <div class="col-md-12 card-description">
                <span><?= $product['description'] ?> </span>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-6 card-count">
                <span><b>Кол-во (<?= $product['measure'] ?>):</b><br>
                      <div class="input-group">
                          <input type="text" class="form-control count-product"
                                 name="Order[count][<?= $product['obtn_id']?>]"
                                 aria-describedby="basic-addon2">
                          <span class="input-group-btn">
                              <button class="btn btn-default count-inc"
                                      type="button">+</button>
                              <button class="btn btn-default count-dec"
                                      type="button">-</button>
                          </span>
                      </div>
                </span>
            </div>
            <div class="col-xs-3 card-cena">
                <span><b>Цена:</b><br><span class="product-price"><?= $product['price'] ?></span></span>
            </div>
            <div class="col-xs-3 card-amount">
                <span><b>Сумма:</b><br><span class="product-total-price">0.00</span> р.</span>
            </div>
        </div>
    </div>
<?php endforeach; ?>

