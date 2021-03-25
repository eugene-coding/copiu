<?php

use yii\helpers\Html;

/** @var array $syncing_methods Методы синхронизации */
?>
    <div class="syncing">
        <div class="row">
            <?php foreach ($syncing_methods as $method_name => $url):?>
                <div class="col-xs-12">
                    <div class="panel panel-info">
                        <div class="panel-heading">
                            <?= $method_name ?>
                        </div>
                        <div class="panel-body text-center">
                            <div class="sync-buyer-btn">
                                <?= Html::button('Начать синхронизацию', [
                                    'class' => 'btn btn-primary btn-block sync-start-btn',
                                    'data-url' => $url
                                ]) ?>
                            </div>
                            <div class="sync-progress" style="display: none;">
                                <div class="progress">
                                    <div class="progress-bar progress-bar-striped active" role="progressbar"
                                         aria-valuenow="100"
                                         aria-valuemin="0" aria-valuemax="100" style="width: 100%">
                                    </div>
                                </div>
                                <div>Идёт синхронизация. Ожидайте</div>
                            </div>
                            <div class="sync-result" style="display: none;">
                                <div class="sync-result-message" style="margin-bottom: 5px;"></div>
                                <?= Html::button('Ок', [
                                    'class' => 'btn btn-success btn-block sync-result-btn',
                                ]) ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

<?php
$js = <<<JS
$(document).ready(function() {
    
  $(document).on('click', '.sync-start-btn', function() {
        var url = $(this).attr('data-url');
        
        var start_block = $(this).parents('.sync-buyer-btn');
        var progress_block = start_block.siblings('.sync-progress');
        var result_block = start_block.siblings('.sync-result');
        
        start_block.slideUp(300);
        progress_block.slideDown(300);
        
        $.get(url)
        .done(function(res) {
            console.log(res);
            if (res.success){
                result_block.find('.sync-result-message').html(res.data);
            } else {
                result_block.find('.sync-result-message').html('<p class="text-danger">' + res.error + '</p>');
            }
             if (res['settings_check'] === true){
                 $('.settings-warning').hide()
             } else {
                 $('.settings-warning').show()
             }
        })
        .fail(function() {
            result_block.find('.sync-result-message').html('<p class="text-danger">При импорте возникла ошибка.</p>');
        })
        .always(function(res) {
             progress_block.slideUp(300);
             result_block.slideDown(300);
            
            //  setTimeout(function() {
            //     $('.sync-result-btn').trigger('click');
            // }, 10000);
        });
      
  });
    $(document).on('click', '.sync-result-btn', function() {
            var start_block = $(this).parents('.panel-body').find('.sync-buyer-btn');
            var result_block = $(this).parents('.sync-result');
        
            result_block.slideUp(300);
            start_block.slideDown(300);
            setTimeout(function() {
               result_block.find('.sync-result-message').html('');
            }, 2000);
    });
})
JS;
$this->registerJs($js);
