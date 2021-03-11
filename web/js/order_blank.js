$(document).ready(function () {
    var message_block = $('#before-panel-message');
   $(document).on('click', '#sync-order-blank-btn', function () {
       message_block.fadeOut();
       var button = $(this);
       var url = button.attr('data-url');
       button.html('<i class="fa fa-spinner fa-spin fa-fw"></i> Синхронизация. Не перезагружайте страницу');
       button.attr('disabled', true);
       $.get(url)
           .done(function (response) {
               if (response.success){
                   message_block.html('<i class="glyphicon glyphicon-ok text-success"></i> '
                       + response.data);
               } else {
                   message_block.html('<i class="glyphicon glyphicon-alert text-danger"></i> ' + response.error);
               }
           })
           .fail(function (response) {
               message_block.html('<i class="glyphicon glyphicon-alert text-danger"></i> Ошибка синхронизации: '
                   + response.responseText);
           })
           .always(function () {
               message_block.fadeIn(300);
               button.attr('disabled', false);
               button.html('<i class="glyphicon glyphicon-sort"></i> Синхронизировать');
           });
   });
});