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
                console.log(response);
                if (response.success) {
                    nom_block.html(response.data);
                    $('.count-products').each(function (index) {
                        total_count = total_count + Number($(this).text());
                    });
                    if (total_count > 0) {
                        $('[type="submit"]').fadeIn(300);
                    } else {
                        $('[type="submit"]').fadeOut(300);
                    }
                } else {
                    $('[type="submit"]').fadeOut(300);
                    nom_block.html('<p class="text-danger"><i class="fa fa-exclamation-circle"></i> ' + response.error + '</p>');
                    nom_block.append(response.warning)
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

    $(document).on('click', '[type="submit"], .to-back', function () {
        $(window).unbind('beforeunload');
    });

    $(document).on('click', '#change-favorite-btn', function (e) {
        e.preventDefault();
        let link = $(this);
        let href = link.attr('data-href');
        let icon = link.html();
        let title = link.attr('title');
        $.get(href)
            .done(function (response) {
                if (response.success === true) {
                    icon = response.data;
                    link.html(icon);
                    link.attr('title', response.title);
                } else {
                    link.html('<i class="fa fa-fw fa-exclamation-circle text-danger"></i>');
                    link.attr('title', 'Ошибка работы с избранным');
                    setTimeout(function () {
                        link.html(icon);
                        link.attr('title', title);
                    }, 3000)
                }
            })
            .fail(function () {
                link.html('<i class="fa fa-fw fa-exclamation-circle text-danger"></i>');
                link.attr('title', 'Ошибка работы с избранным');
                setTimeout(function () {
                    link.html(icon);
                    link.attr('title', title);
                }, 3000)
            })
    });

    $(window).bind('beforeunload', function () {
        var step = $('#order-step').val();
        if (step !== 'undefined' && step > 1 && step < 4) {
            setTimeout(function () {
                setTimeout(function () {
                    console.log('Отмена');
                }, 1000);
                console.log('Закрываем');
            }, 1);
            return 'Данные заказа не будут сохранены. Вы уверены?';
        }
    });

    $(document).on('click', '.search-btn', function () {
        var btn = $(this);
        var block = btn.parents('.search-product');
        var product_id = block.find('select').val();
        var tab = btn.parents('.tab-pane').attr('id').split('-')[1];
        var order_id = $('#order-step').attr('data-id');

        $.get('/order/get-product-for-tab', {order_id: order_id, blank_id: tab, product_id: product_id, is_mobile: ''})
            .done(function (response) {
                console.log(btn.parents('.tab-pane').find('.tab-nomenclature-list'));
                btn.parents('.tab-pane').find('.table-products').html(response.data);
            })
            .fail(function (response) {
                btn.parents('.tab-pane').find('.table-products').html(response.responseText)
            })

    });

    $(document).on('click', '.search-mobile-btn', function () {
        var btn = $(this);
        var product_id = btn.parents('.search-product').find('select').val();
        var tab = btn.parents('.tab-pane').attr('id').split('-')[1];
        var order_id = $('#order-step').attr('data-id');

        $.get('/order/get-product-for-tab', {order_id: order_id, blank_id: tab, product_id: product_id, is_mobile: 1})
            .done(function (response) {
                btn.parents('.tab-pane').find('.product-cards').html(response.data);
            })
            .fail(function (response) {
                btn.parents('.tab-pane').find('.product-cards').html(response.responseText)
            })

    });

    $(document).on('change', '.count-product', function () {
        var count = $(this).val();
        var price = $(this).parents('.card').find('.product-price').html();
        var price_d = $(this).parents('tr').find('.product-price').html();
        $(this).parents('.card').find('.total-cost').html((count * price).toFixed(2));
        $(this).parents('tr').find('.total-cost').html((count * price_d).toFixed(2));
        if (typeof(price) === 'undefined') {
            price = price_d;
        }
        var order_id = $(this).attr('data-order-id');
        var obtn_id = $(this).attr('data-obtn-id');

        console.log('Order ID: ' + order_id);

        // var total = 0;
        // $('.total-cost').each(function (index, value) {
        //     total += Number(value.innerHTML);
        // });
        $.post('/order/add-product', {
            order_id: order_id,
            obtn_id: obtn_id,
            count: count,
            price: price
        })
            .done(function (response) {
                $('.total').html(Number(response.total).toFixed(2));
            });
    });

});