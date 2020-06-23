$(document).ready(function() {

    $(".masked-phone").mask("+38(099) 999-99-99", {
        placeholder:"+38(0__) ___-__-__",
        autoclear: false,
        alias: "numeric"
    });

    $("#user_city").select2({
        language: {
            searching: function() {
                return "Something else...";
            }
        },
        matcher: function () {
            return 23;
        }
    });

    $(".select2-block").each(function() {
        $(this).select2({language: "ru"});
    });

    $("input[name='user_delivery']").change(function() {
        getOrderPaymentBlock();
        uncheckRadioPayment();

        let amount = $("input[name='user_delivery']").filter(':checked').length;
        if (amount>0) {
            $("#orders-payment").removeClass("none");
        }
    });

    $("input[type='radio']").change(function() {

        $(".orders-block-row-hidden").each(function () {
           $(this).removeClass("orders-block-row-display");
        });

        $("input[type='radio']").each(function () {
            if($(this).is(':checked')) $("#" + $(this).attr("data-tab-href")).addClass("orders-block-row-display");
        });

    });

});

/*==== MAIN ====*/
function setCityVal() {
    let data = $("#user_city").select2("data");
    let city_id = data[0].value;
    let city_name = data[0].text;
    setCityDepartments(city_id);
    $(".chosen-city").html(city_name);
}

/*==== /MAIN ====*/

/*==== DELIVERY + PAYMENT ====*/
function setCityDepartments(city_id) {
    JsHttpRequest.query(folder,{'w':'setCityDepartments', 'city_id':city_id},
        function (result, errors){ if (errors) {alert(errors);} if (result) {
            let select_np = $("#select_delivery_np");
            let select_up = $("#select_delivery_up");
            select_np.html(result.content[0]); select_np.select2();
            select_up.html(result.content[1]); select_up.select2();
        }}, true);
}

function getOrderDeliveryBlock() {
    $(".orders-block-row-delivery").each(function () {
        let delivery_id = $(this).attr("data-tab-delivery");
        let city_id = $("#user_city").select2("val");
        let block = $(this);
        block.removeClass("orders-block-row-hidden");
        JsHttpRequest.query(folder,{'w':'getOrderDeliveryBlock', 'delivery_id':delivery_id, 'city_id':city_id},
            function (result, errors){ if (errors) {alert(errors);} if (result){
                let status = result.content;
                if (status==0) block.addClass("orders-block-row-hidden");
            }}, true);
    });
}

function uncheckRadioDelivery() {
    $(".orders-block-row-delivery").each(function () {
        $(this).find("label").find("input[type='radio']").prop("checked", false);
        $(this).find("div").removeClass("orders-block-row-display");
    });
}

function uncheckRadioPayment() {
    $(".orders-block-row-payment").each(function () {
        $(this).find("label").find("input[type='radio']").prop("checked", false);
    });
}

function getOrderPaymentBlock() {
    $(".orders-block-row-payment").each(function () {
        let payment_id = $(this).attr("data-tab-payment");
        let delivery_id = $("input[name ='user_delivery']:checked").attr("data-id-delivery");
        let block = $(this);
        block.removeClass("orders-block-row-hidden");
        JsHttpRequest.query(folder,{'w':'getOrderPaymentBlock', 'payment_id':payment_id, 'delivery_id':delivery_id},
            function (result, errors){ if (errors) {alert(errors);} if (result){
                let status = result.content;
                if (status==0) block.addClass("orders-block-row-hidden");
            }}, true);
    });
}

/*==== /DELIVERY + PAYMENT ====*/

/*==== SAVE ====*/
function getPhone(str) {
    str = str.replace("_", "");str = str.replace("_", "");str = str.replace("_", "");str = str.replace("_", "");str = str.replace("_", "");str = str.replace("_", "");str = str.replace("_", "");str = str.replace("_", "");str = str.replace("_", "");
    str = str.replace("-", "");
    str = str.replace("-", "");
    str = str.replace("+", "");
    str = str.replace("(", "");
    str = str.replace(")", "");
    str = str.replace(" ", "");
    return str;
}

function validFields() {
    let valid=0;
    $(".valid_field").each(function() {
        let data_attr = $(this).attr("data-attr");
        // INPUT TEXT FIELD
        if (data_attr==="text") {
            if ($(this).val()==="") {
                valid++;
                $(this).addClass("not-valid");
                $(this).removeClass("accept-valid");
            } else {
                $(this).addClass("accept-valid");
                $(this).removeClass("not-valid");
            }
        }
        // INPUT PHONE FIELD
        if (data_attr==="phone") {
            let phone = getPhone($(this).val());
            if (phone.length!==12) {
                valid++;
                $(this).addClass("not-valid");
                $(this).removeClass("accept-valid");
            } else {
                $(this).addClass("accept-valid");
                $(this).removeClass("not-valid");
            }
        }
        // SELECT FIELD
        if (data_attr==="select") {
            let data = $(this).select2("data");
            let data_id = data[0].value;

            if (data_id==="0") {
                valid++;
                $(this).next(".select2-container").find(".select2-selection--single").addClass("not-valid");
                $(this).next(".select2-container").find(".select2-selection--single").removeClass("accept-valid");
            } else {
                $(this).next(".select2-container").find(".select2-selection--single").addClass("accept-valid");
                $(this).next(".select2-container").find(".select2-selection--single").removeClass("not-valid");
            }
        }
    });
    if (valid===0) {
        $(".valid_field").each(function() {
            $(this).removeClass("not-valid accept-valid");
            $(this).prop("disabled", true);
            $(this).next(".select2-container").find(".select2-selection--single").removeClass("not-valid accept-valid");
        });
        $("#valid_button").addClass("none");
        $("#edit_button").removeClass("none");
        $("#orders-delivery").removeClass("none");
    }

    getOrderDeliveryBlock();
}

function editFields() {
    $("#valid_button").removeClass("none");
    $("#edit_button").addClass("none");
    $("#orders-delivery").addClass("none");
    $("#orders-payment").addClass("none");
    $(".valid_field").each(function() {
        $(this).prop("disabled", false);
    });

    uncheckRadioDelivery();
    uncheckRadioPayment();
}

function validOrder() {
    let name = $("#user_name").val();
    let phone = $("#user_phone").val();
    let city = $("#user_city").select2("val");
    let delivery = $("input[name ='user_delivery']:checked").attr("data-id-delivery");

    let delivery_type = getDeliveryTypeFields(delivery);

    let payment = $("input[name ='user_payment']:checked").attr("data-id-payment");
    let email = $("#user_email").val();
    let comment = $("#user_comment").val();

    if(delivery===undefined || payment===undefined) {
        alert("ERROR - check DELIVERY and PAYMENT first!")
    } else {
        JsHttpRequest.query(folder,{'w':'validOrder', 'name':name, 'phone':phone, 'city':city, 'delivery':delivery, 'delivery_type':delivery_type, 'payment': payment, 'email':email, 'comment':comment},
        function (result, errors){ if (errors) {alert(errors);} if (result) {
            $("#OrderModal").modal("show");
            $("#OrderModalContent").html(result.content);
        }}, true);
    }
}

function getDeliveryTypeFields(delivery_id) {
    let div = $("div[data-tab-delivery='" + delivery_id + "']");

    let street = div.find("div").find("input[name='street']").val();
    let house = div.find("div").find("input[name='house']").val();
    let porch = div.find("div").find("input[name='porch']").val();
    let department = div.find("select[name='department']").select2("val");
    let delivery_express = div.find("select[name='delivery_express']").select2("val");

    let arr = [];
    arr["street"] = street;
    arr["house"] = house;
    arr["porch"] = porch;
    arr["department"] = department;
    arr["delivery_express"] = delivery_express;

    return arr;
}

/*==== /SAVE ====*/