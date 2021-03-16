$(document).ready(function () {
    var loader = $('.nomenclature-loader');
    var nom_block = $('.nomenclature');

    $(document).on('click', '#confirm-order-date', function () {
        nom_block.hide();
        var date = $('#order-target_date').val();

        loader.fadeIn(300);
        $.post("/order-blank/get-orders-by-date", {date: date})
            .done(function (response) {
                if (response.success) {
                    nom_block.html(response.data);
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
        var count = $(this).val();
        var price = $(this).parents('tr').children('.product-price').html();
        $(this).parents('tr').children('.total-cost').html(count*price);
        var total = 0;
        $('.total-cost').each(function(index, value){
            total += Number(value.innerHTML);
        });
        $('.total').html(Number(total) + 'р.');
    });

});