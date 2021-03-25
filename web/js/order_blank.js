$(document).ready(function () {

   $(this).on('click', '#sync-order-blank-btn', function () {
       var message_block = $('#before-panel-message');
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
                   $('tbody').find('[data-col-seq="5"]').text(response.date)
               } else {
                   message_block.html('<i class="glyphicon glyphicon-alert text-danger"></i> ' + response.error).fadeIn(300);
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