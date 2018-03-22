function ddeliveryWidgetInit() {
    DDeliveryModule.init({
        id: 74290,
        width: 500,
        height: 350,
        url: "/index.php?route=module/ddelivery/gettoken&action=module"
    }, {
        price: function (data) {
            $.ajax({
                url: "/index.php?route=module/ddelivery/setconfig",
                data: "price=" + data.price,
                type: "post",
                success: function(result){
                    console.log('Parametrs send to store (on price)');
                    if ($('[name=shipping_method]:checked').val() == "ddelivery.ddelivery") {
                        $.get('index.php?route=checkout/simplecheckout_cart', function(data){
                            $('.simplecheckout-cart-total[id^=total_]').each(function(){
                                $(this).html($(data).find('#'+$(this).attr('id')).html());
                            });
                        });
                    }
                    if (DDeliveryModule.validate()) {
                        $('#shipping_field28').val('true');
                    } else {
                        $('#shipping_field28').val('');
                    }

                }
            });
        },
        change: function (data) {
            $.ajax({
                url: "/index.php?route=module/ddelivery/setconfig",
                data: "price=" + data.client_price + "&order_id=" + data.id + "&token=" + data.client_token,
                type: "post",
                success: function(result){
                    console.log('Parametrs send to store (on change)');
                    $("#ddelivery-widget-error").fadeOut();
                    $('#shipping_field28').val('');
                    if (DDeliveryModule.validate()) {
                        $('#shipping_field28').val('true');
                    } else {
                        $('#shipping_field28').val('');
                    }
                }
            });
        },
        open: function () {
            return true;
        }
    }, 'ddelivery-widget');
}

function simpleValidate() {
    if ($('[name=shipping_method]:checked').val() == "ddelivery.ddelivery") {
        if (DDeliveryModule.validate()) {
            $('#shipping_field28').val('true');
            return true;
        } else {
            $("#ddelivery-widget-error").html(DDeliveryModule.getErrorMsg());
            $("#ddelivery-widget-error").fadeIn();
            $('#shipping_field28').val('');
            return false;
        }
    } else {
        return true;
    }
}