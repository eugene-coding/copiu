$(document).ready(function () {
    var loader = $('.nomenclature-loader');
    var nom_block = $('.nomenclature');

    $(document).on('click', '#confirm-order-date', function () {
        nom_block.hide();
        var date = $('#order-target_date').val();
        var total_count = 0;

        loader.fadeIn(300);
        $.post("/order-blank/get-orders-by-date", {date: date})
            .done(function (response) {
                if (response.success) {
                    nom_block.html(response.data);
                    $('.count-products').each(function (index) {
                        total_count = total_count + Number($(this).text());
                    });
                    if (total_count > 0){
                        $('[type="submit"]').fadeIn(300);
                    } else {
                        $('[type="submit"]').fadeOut(300);
                    }
                } else {
                    nom_block.html('<p class="text-danger"><i class="fa fa-exclamation-circle"></i> ' + response.error + '</p>');
                }
            })
            .fail(function (error) {
                // nom_block.show();
                console.log(error);
                nom_block.html(error.responseText);
            })
            .always(function () {
                loader.fadeOut(300);
                nom_block.fadeIn(300);
            })

        // console.log(111);

    });

    $(document).on('change', '.count-product', function () {
        debugger;
        var count = $(this).val();
        console.log('Count: ' + count);
        var price = $(this).parents('tr').children('.product-price').html();
        console.log('Price: ' + price);
        $(this).parents('tr').children('.total-cost').html(count*price);
        var total = 0;
        $('.total-cost').each(function(index, value){
            total += Number(value.innerHTML);
        });
        console.log('Total: ' + total);
        $('.total').html(Number(total) + 'р.');
    });

    $(document).on('click', '[type="submit"]', function () {
        $(window).unbind('beforeunload');
    });

    $(window).bind('beforeunload', function() {
        var step = $('#order-step').val();
        if (step !== 'undefined' && step > 1 && step < 4){
            setTimeout(function() {
                setTimeout(function() {
                    console.log('Отмена');
                }, 1000);
                console.log('Закрываем');
            },1);
            return 'Данные заказа не будут сохранены. Вы уверены?';
        }
    });
});