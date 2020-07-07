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
        // uncheckRadioPayment();
        getBasketOrder();

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
    if (data.length!==0) {
        let city_id = data[0].value;
        let city_name = data[0].text;
        $(".chosen-city").html(city_name);
        JsHttpRequest.query(folder,{'w':'setCityNPVal', 'city_id':city_id},
            function (result, errors){ if (errors) {alert(errors);} if (result) {
                let user_city = $("#user_city_np");
                user_city.html(result.content);
            }}, true);
    }
}

function getCityVal() {
    let search_text = $(".select2-search__field").val();
    let len = search_text.length;
    if (len>2) {
        JsHttpRequest.query(folder,{'w':'getCityVal', 'search_text':search_text},
            function (result, errors){ if (errors) {alert(errors);} if (result) {
                let user_city = $("#user_city");
                user_city.append(result.content);
                var mas=result.content;
                var len=Object.keys(mas).length;
                for (var i=1; i<=len; i++) {
                    var id_city=Object.entries(mas[i])[0][1];
                    var value_city=Object.entries(mas[i])[1][1];
                    addOption(id_city,value_city);
                }
            }}, true);
    }
}

function addOption(id_city, value_city) {
    let select_city=$('#user_city');
    if (select_city.find("option[value='" + id_city + "']").length) {
        //select_city.val(null).trigger('change');
    } else {
        let newOption = new Option(value_city, id_city, false, false);
        select_city.append(newOption).val(null).trigger('change');
    }
}

/*==== /MAIN ====*/

/*==== DELIVERY + PAYMENT ====*/
function setCityDepartments() {
    let city_ref = $("#user_city_np option:selected").val();
    JsHttpRequest.query(folder,{'w':'setCityDepartments', 'city_ref':city_ref},
        function (result, errors){ if (errors) {alert(errors);} if (result) {
            let select_np = $("#select_delivery_np"); select_np.html("");
            let select_up = $("#select_delivery_up"); select_up.html("");
            select_np.html(result.content[0]); select_np.select2();
            select_up.html(result.content[1]); select_up.select2();
        }}, true);
}

function setCityAddress() {
    let city_id = $("#user_city").select2("val");
    JsHttpRequest.query(folder,{'w':'setCityAddress', 'city_id':city_id},
        function (result, errors){ if (errors) {alert(errors);} if (result) {
            $("#tpoint_address").html(result.content);
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
                if ($("#user_city_np option:selected").val()===undefined) {
                    $("div[data-tab-delivery='4']").addClass("orders-block-row-hidden");
                }
            }}, true);
    });
    setCityDepartments();
    setCityAddress();
}

function uncheckRadioDelivery() {
    $(".orders-block-row-delivery").each(function () {
        $(this).find("label").find("input[type='radio']").prop("checked", false);
        $(this).find("div").removeClass("orders-block-row-display");
    });
}

// function uncheckRadioPayment() {
//     $(".orders-block-row-payment").each(function () {
//         $(this).find("label").find("input[type='radio']").prop("checked", false);
//     });
// }

function getOrderPaymentBlock() {
    let first = 0;
    let status = "1";
    $(".orders-block-row-payment").each(function () {
        let block = $(this);
        let payment_id = block.attr("data-tab-payment");
        let delivery_id = $("input[name ='user_delivery']:checked").attr("data-id-delivery");
        block.removeClass("orders-block-row-hidden");
        block.find("label").find("input[type='radio']").prop("checked", false);
        JsHttpRequest.query(folder,{'w':'getOrderPaymentBlock', 'payment_id':payment_id, 'delivery_id':delivery_id},
            function (result, errors){ if (errors) {alert(errors);} if (result){
                status = result.content;
                if (status==="0") block.addClass("orders-block-row-hidden");
                if (status==="1" && first===0) {
                    block.find("label").find("input").prop("checked", true);
                    first++;
                }
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

function showOrderInfo() {
    $("#order_info_max").removeClass("none");
    $("#order_info_min_circle").removeClass("orders-header__round-fill");
    let text = "{order_contacts_cap}";
    JsHttpRequest.query(folder,{'w':'changeLangJs', 'text':text},
        function (result, errors){ if (errors) {alert(errors);} if (result) {
            $("#order_info_min").html(result.content);
        }}, true);
}

function hideOrderInfo() {
    $("#order_info_max").addClass("none");
    $("#order_info_min_circle").addClass("orders-header__round-fill");
    let name = $("#user_name").val();
    let phone = $("#user_phone").val();
    let city = $("#user_city").select2("data")[0].text;
    JsHttpRequest.query(folder,{'w':'hideOrderInfo', 'name':name, 'phone':phone, 'city':city},
        function (result, errors){ if (errors) {alert(errors);} if (result) {
            $("#order_info_min").html(result.content);
        }}, true);
}

function editFields() {
    $("#valid_button").removeClass("none");
    $("#orders-delivery").addClass("none");
    $("#orders-payment").addClass("none");
    $(".valid_field").each(function() {
        $(this).prop("disabled", false);
    });
    uncheckRadioDelivery();
    // uncheckRadioPayment();
    showOrderInfo();
    $("#valid_info").val(0);
}

function getDeliveryTypeFields(delivery_id) {
    let div = $("div[data-tab-delivery='" + delivery_id + "']");
    let street = div.find("div").find("input[name='street']").val();
    let house = div.find("div").find("input[name='house']").val();
    let porch = div.find("div").find("input[name='porch']").val();
    // let department = div.find("select[name='department']").select2("val"); //select department name
    let data = div.find("select[name='department']").select2("data"); //select department name
    let department = "0";
    if (data!==undefined) {
        department = data[0].text;
    }
    let delivery_express = div.find("select[name='delivery_express']").select2("val");
    let delivery_express_department = div.find("div").find("input[name='delivery_express_department']").val();
    let arr = [];
    arr["street"] = street;
    arr["house"] = house;
    arr["porch"] = porch;
    arr["department"] = department;
    arr["delivery_express"] = delivery_express;
    arr["delivery_express_department"] = delivery_express_department;
    return arr;
}

/*==== /SAVE ====*/

/*==== BASKET ====*/
function getBasketOrder() {
    $("#orders-basket").html("");
    let delivery_id = $("input[name ='user_delivery']:checked").attr("data-id-delivery");
    JsHttpRequest.query(folder,{'w':'getBasketOrder', 'delivery_id':delivery_id},
        function (result, errors){ if (errors) {alert(errors);} if (result) {
            $("#orders-basket").html(result.content);
        }}, true);
}

/*==== /BASKET ====*/

/*==== VALID FIELDS ====*/
function validInfoFields() {

    let valid = 0;
    $(".valid_field").each(function() {
        let data_attr = $(this).attr("data-attr");
        // INPUT TEXT FIELD
        if (data_attr==="text") {
            let name = $(this).val();
            if (name==="" || !(name.includes(" "))) {
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
            let data_id = $(this).select2("data")[0].value;
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
        $("#orders-delivery").removeClass("none");
        hideOrderInfo();
        $("#valid_info").val(1);
    } else {
        $("#valid_info").val(0);
    }

    getOrderDeliveryBlock();
}

/*==== /VALID FIELDS ====*/

/*==== VALID ORDER ====*/
function validOrder() {
    let delivery = $("input[name ='user_delivery']:checked").attr("data-id-delivery");
    let delivery_type = getDeliveryTypeFields(delivery);
    let payment = $("input[name ='user_payment']:checked").attr("data-id-payment");

    let div = $("div[data-tab-delivery='" + delivery + "']");
    div.find("div").find("input").each(function () {
        $(this).removeClass("not-valid");
    });
    div.find("div").find("select").each(function () {
        $(this).next(".select2-container").find(".select2-selection--single").removeClass("not-valid");
    });

    JsHttpRequest.query(folder,{'w':'validDeliveryFields', 'delivery':delivery, 'delivery_type':delivery_type},
        function (result, errors){ if (errors) {alert(errors);} if (result) {
            let valid_status = result.content[0];
            if (valid_status) {
                if (payment!==undefined) {
                    validFullOrder();
                }
            } else {
                let arr = result.content[1];
                arr.forEach(function(element){
                    div.find("div").find("input[name='" + element + "']").addClass("not-valid");
                    div.find("select[name='" + element + "']").next(".select2-container").find(".select2-selection--single").addClass("not-valid");
                });
            }
        }}, true);
}

function validFullOrder() {
    let name = $("#user_name").val();
    let phone = $("#user_phone").val();
    let city = $("#user_city").select2("val");
    let delivery = $("input[name ='user_delivery']:checked").attr("data-id-delivery");
    let delivery_type = getDeliveryTypeFields(delivery);
    let payment = $("input[name ='user_payment']:checked").attr("data-id-payment");
    let email = $("#user_email").val();
    let comment = $("#user_comment").val();

    JsHttpRequest.query(folder,{'w':'validOrder', 'name':name, 'phone':phone, 'city':city, 'delivery':delivery, 'delivery_type':delivery_type, 'payment': payment, 'email':email, 'comment':comment},
        function (result, errors){ if (errors) {alert(errors);} if (result) {
            $("#OrderModal").modal("show");
            $("#OrderModalContent").html(result.content);
        }}, true);
}

/*==== /VALID ORDER ====*/
