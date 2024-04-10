$(document).ready(function () {
    $(document).on('change', '.status-dropbox', function () {
        var val = $(this).val();
        var id = $(this).attr('data-id');
        $.post('/order/change-status', {id:id, status:val})
            .done(function (response) {
               if (response.success === false){
                   pjax.reload(true);
                   console.log('false');
                   alert(response.error)
               }
            })
            .fail(function (response) {
                alert(response.responseText);
            });
        console.log();
    });


});