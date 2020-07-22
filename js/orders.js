$(document).ready(function() {

    // INIT PHONE
    $(".masked-phone").mask("+38(099) 999-99-99", {
        placeholder:"+38(0__) ___-__-__",
        autoclear: false,
        alias: "numeric"
    });

    // INIT CITY
    let user_city = $("#user_city");

    if (user_city.length!==0) {
        user_city.select2({
            language: {
                searching: function() {
                    return "Something else...";
                }
            },
            matcher: function () {
                return 23;
            }
        });
        if (user_city.select2("val")>0) {
            setCityVal();
        }
    }

    // INIT SELECT FIELDS
    $(".select2-block").each(function() {
        $(this).select2({language: "ru"});
    });

    // ONCHANGE DELIVERY RADIO
    $("input[name='user_delivery']").change(function() {
        getOrderPaymentBlock();
        uncheckRadioPayment();
        getBasketOrder();
        $("#user_saved_info_list").hide();
        // SHOW PAYMENT (IF DELIVERY CHECKED)
        let amount = $("input[name='user_delivery']").filter(':checked').length;
        if (amount>0) {
            $("#orders-payment").removeClass("none");
        }
        // HIDE RADIO CHILD BLOCK
        $(".orders-block-row-hidden").each(function () {
            $(this).removeClass("orders-block-row-display");
        });
        // SHOW RADIO CHILD BLOCK (CHECKED)
        $("input[type='radio']").each(function () {
            if($(this).is(':checked')) $("#" + $(this).attr("data-tab-href")).addClass("orders-block-row-display");
        });
    });

    $($("input[name='user_recipient']")).each(function () {
        if($(this).is(':checked')) $("#" + $(this).attr("data-tab-href")).addClass("orders-block-row-display");
    });

    $("input[name='user_recipient']").change(function() {
        $($("input[name='user_recipient']")).each(function () {
            $("#" + $(this).attr("data-tab-href")).removeClass("orders-block-row-display");
        });
        $("#" + $(this).attr("data-tab-href")).addClass("orders-block-row-display");
    });

});

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

/*==== INFO BLOCK ====*/

// SET NOVA POSHTA DEPARTMENTS
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

// ADD CITY VALUES (by SEARCH)
function getCityVal() {
    let search_text = $(".select2-search__field").val();
    if ($("#select2-user_city-results").val()!==undefined) {
        if (search_text!==undefined) {
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
    }

}
function addOption(id_city, value_city) {
    let select_city = $("#user_city");
    if (select_city.find("option[value='" + id_city + "']").length) {
        //
    } else {
        let newOption = new Option(value_city, id_city, false, false);
        select_city.append(newOption).val(null).trigger('change');
    }
}

/*==== /INFO BLOCK ====*/

/*==== DELIVERY + PAYMENT ====*/

// SET NOVA POSHTA DEPARTMENTS
function setCityDepartments() {
    let city_ref = $("#user_city_np option:selected").val();
    if (city_ref!=="0" && city_ref!=="undefined") {
        JsHttpRequest.query(folder,{'w':'setCityDepartments', 'city_ref':city_ref},
            function (result, errors){ if (errors) {alert(errors);} if (result) {
                let select_np = $("#select_delivery_np"); select_np.html("");
                let select_up = $("#select_delivery_up"); select_up.html("");
                select_np.html(result.content[0]); select_np.select2();
                select_up.html(result.content[1]); select_up.select2();
            }}, true);
    }
}

// GET DELIVERY BLOCK
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

// GET PAYMENT BLOCK
function getOrderPaymentBlock() {
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
            }}, true);
    });
    $("#valid_payment_block").removeClass("not-valid");
}

// SET ADDRESS OF TPOINT (by CITY)
function setCityAddress() {
    let city_id = $("#user_city").select2("val");
    JsHttpRequest.query(folder,{'w':'setCityAddress', 'city_id':city_id},
        function (result, errors){ if (errors) {alert(errors);} if (result) {
            $("#tpoint_address").html(result.content);
        }}, true);
}

// UNCHECKED RADIO
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
    $("#valid_payment_block").removeClass("not-valid");
}

/*==== /DELIVERY + PAYMENT ====*/

/*==== SAVE ORDER ====*/

// SHOW INFO BLOCK, HIDE OTHERS BLOCKS
function editFields() {
    $("#valid_button").removeClass("none");
    $("#orders-delivery").addClass("none");
    $("#orders-payment").addClass("none");
    $(".valid_field").each(function() { $(this).prop("disabled", false); });
    uncheckRadioDelivery();
    uncheckRadioPayment();
    showOrderInfo();
    getBasketOrder();
}

// HIDE INFO BLOCK, SHOW DELIVERY BLOCK
function showOrderInfo() {
    $("#order_info_max").removeClass("none");
    $("#order_info_min_circle").removeClass("orders-header__round-fill");
    let text = "{order_contacts_cap}";
    JsHttpRequest.query(folder,{'w':'changeLangJs', 'text':text},
        function (result, errors){ if (errors) {alert(errors);} if (result) {
            $("#order_info_min").html(result.content);
        }}, true);
}

// HIDE INFO BLOCK, SHOW DELIVERY BLOCK
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

// GET DELIVERY INFO FIELDS
function getDeliveryTypeFields(delivery_id) {
    let div = $("div[data-tab-delivery='" + delivery_id + "']");
    let street = div.find("div").find("input[name='street']").val();
    let house = div.find("div").find("input[name='house']").val();
    let porch = div.find("div").find("input[name='porch']").val();
    let data_department = div.find("select[name='department']").select2("data");
    let data_express = div.find("select[name='delivery_express']").select2("data");
    let delivery_express_department = div.find("div").find("input[name='delivery_express_department']").val();
    let department = "0";
    let department_id = "0";
    let delivery_express = "0";
    if (data_department!==undefined) {
        department = data_department[0].text;
        department_id = data_department[0].value;
    }
    if (data_express!==undefined) {
        delivery_express = data_express[0].value;
    }
    let arr = [];
    arr["street"] = street;
    arr["house"] = house;
    arr["porch"] = porch;
    arr["department"] = department;
    arr["department_id"] = department_id;
    arr["delivery_express"] = delivery_express;
    arr["delivery_express_department"] = delivery_express_department;
    return arr;
}

// SET DELIVERY EXPRESS CAPTION
function setDeliveryExpressDepartment() {
    let delivery_express = $("#delivery_express option:selected").val();
    JsHttpRequest.query(folder,{'w':'setDeliveryExpressDepartment', 'delivery_express':delivery_express},
        function (result, errors){ if (errors) {alert(errors);} if (result) {
            $("#delivery_express_department").html(result.content);
        }}, true);
}

// GET BASKET ORDER FORM
function getBasketOrder() {
    $("#orders-basket").html("");
    let delivery_id = $("input[name ='user_delivery']:checked").attr("data-id-delivery");
    JsHttpRequest.query(folder,{'w':'getBasketOrder', 'delivery_id':delivery_id},
        function (result, errors){ if (errors) {alert(errors);} if (result) {
            $("#orders-basket").html(result.content);
        }}, true);
}

/*==== /SAVE ====*/

/*==== VALIDATION ====*/

// VALID INFO FIELDS
function validInfoFields() {
    let valid = 0;
    let valid_field = $(".valid_field");
    valid_field.each(function() {
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
            let data_id = "0";
            let data = $(this).select2("data");
            if (data.length!==0) {
                data_id = data[0].value;
            }
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
    // ALL OK
    if (valid===0) {
        let order_user_id = $("#order_user_id").val();
        // CHECK IF USER LOGIN
        if (order_user_id==0 || order_user_id==undefined) {
            // CHECK LOGIN USERS
            let phone = $("#user_phone").val();
            JsHttpRequest.query(folder,{'w':'getAuthorizedUser', 'phone':phone},
                function (result, errors){ if (errors) {alert(errors);} if (result) {
                    let status = result.content[0];
                    let user_id = result.content[1];
                    if (status) {
                        showLoginForm();
                    } else {
                        valid_field.each(function() {
                            $(this).removeClass("not-valid accept-valid");
                            $(this).prop("disabled", true);
                            $(this).next(".select2-container").find(".select2-selection--single").removeClass("not-valid accept-valid");
                        });
                        $("#valid_button").addClass("none");
                        $("#orders-delivery").removeClass("none");
                        hideOrderInfo();
                        getUserSavedData(user_id);
                    }
                    $("#order_user_id").val(user_id);
                }}, true);
        } else {
            valid_field.each(function() {
                $(this).removeClass("not-valid accept-valid");
                $(this).prop("disabled", true);
                $(this).next(".select2-container").find(".select2-selection--single").removeClass("not-valid accept-valid");
            });
            $("#valid_button").addClass("none");
            $("#orders-delivery").removeClass("none");
            hideOrderInfo();
            getUserSavedData(order_user_id);
        }
    }
    getOrderDeliveryBlock();
}

// VALID DELIVERY & PAYMENT FIELDS
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
    if (payment===undefined){
        $("#valid_payment_block").addClass("not-valid");
    } else {
        $("#valid_payment_block").removeClass("not-valid");
    }

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

// SHOW ORDER DATA
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

// FINISH ORDER
function saveOrder() {
    let user_id = $("#order_user_id").val();
    let name = $("#user_name").val();
    let phone = $("#user_phone").val();
    let city = $("#user_city").select2("val");
    let delivery = $("input[name ='user_delivery']:checked").attr("data-id-delivery");
    let delivery_type = getDeliveryTypeFields(delivery);
    let payment = $("input[name ='user_payment']:checked").attr("data-id-payment");
    let email = $("#user_email").val();
    let comment = $("#user_comment").val();
    let recipient_name = $("#user_recipient_name").val();
    let recipient_phone = $("#user_recipient_phone").val();

    JsHttpRequest.query(folder,{'w':'saveOrder', 'user_id':user_id, 'name':name, 'phone':phone, 'city':city, 'delivery':delivery, 'delivery_type':delivery_type, 'payment': payment, 'email':email, 'comment':comment, 'recipient_name':recipient_name, 'recipient_phone':recipient_phone},
        function (result, errors){ if (errors) {alert(errors);} if (result) {
            let order_id = result.content[0];
            let user_id = result.content[1];
            let user_status = result.content[2];
            location.href = window.location.href  + "/?order_id=" + order_id + "&user_id=" + user_id + "&user_status=" + user_status;
        }}, true);
}

/*==== /VALIDATION ====*/

/*==== USER DATA ====*/

function dropClientOrderInfo(id) {
    JsHttpRequest.query(folder,{'w':'dropClientOrderInfo', 'id':id},
        function (result, errors){ if (errors) {alert(errors);} if (result) {
            let user_id = $("#order_user_id").val();
            getUserSavedData(user_id);
        }}, true);
}

// INIT USER SAVED DATA
function setClientOrderInfo(id) {
    JsHttpRequest.query(folder,{'w':'setClientOrderInfo', 'id':id},
        function (result, errors){ if (errors) {alert(errors);} if (result) {
            let arr = result.content;
            let city_id = arr["city_id"];
            let delivery_id = arr["delivery_id"];
            let payment_id = arr["payment_id"];
            let delivery_info = arr["delivery_info"];
            let recipient_name = arr["recipient_name"];
            let recipient_phone = arr["recipient_phone"];

            if (recipient_name==="" && recipient_phone==="") {
                $("input[data-id-recipient='1']").prop("checked", true);
                $("input[data-id-recipient='2']").prop("checked", false);
            } else {
                $("input[data-id-recipient='2']").prop("checked", true);
                $("input[data-id-recipient='1']").prop("checked", false);
            }

            $("#user_recipient_name").val(recipient_name);
            $("#user_recipient_phone").val(recipient_phone);
            $("#user_recipient_phone").mask("+38(099) 999-99-99", {
                placeholder:"+38(0__) ___-__-__",
                autoclear: false,
                alias: "numeric"
            });

            // CITY
            let user_city = $("#user_city");
            user_city.val(city_id); user_city.select2();

            // DELIVERY
            $("input[data-id-delivery='" + delivery_id + "']").prop('checked', true);
            $(".orders-block-row-hidden").each(function () {
                $(this).removeClass("orders-block-row-display");
            });
            $("input[type='radio']").each(function () {
                if($(this).is(':checked')) $("#" + $(this).attr("data-tab-href")).addClass("orders-block-row-display");
            });

            // DELIVERY INFO FIELDS
            let div = $("div[data-tab-delivery='" + delivery_id + "']");
            div.find("div").find("input[name='street']").val(delivery_info["street"]);
            div.find("div").find("input[name='house']").val(delivery_info["house"]);
            div.find("div").find("input[name='porch']").val(delivery_info["porch"]);
            div.find("select[name='department']").val(delivery_info["department"]); div.find("select[name='department']").select2();
            div.find("select[name='delivery_express']").val(delivery_info["express"]); div.find("select[name='delivery_express']").select2();
            div.find("div").find("input[name='delivery_express_department']").val(delivery_info["express_info"]);

            // PAYMENT
            let amount = $("input[name='user_delivery']").filter(':checked').length;
            if (amount>0) {
                $("#orders-payment").removeClass("none");
            }
            getOrderPaymentBlock();
            $("input[data-id-payment='" + payment_id + "']").prop('checked', true);

            // BASKET
            getBasketOrder();

        }}, true);

    $("#user_saved_info_list").hide();
}

// GET USER SAVED DATA (by CITY)
function getUserSavedData(user_id) {
    let city = $("#user_city").select2("val");
    JsHttpRequest.query(folder,{'w':'getUserSavedData', 'user_id':user_id, 'city':city},
        function (result, errors){ if (errors) {alert(errors);} if (result) {
            if (result.status==1) {
                setClientOrderInfo(result.info_id);
                $("#user_saved_info").html("");
            } else {
                $("#user_saved_info").html(result.list);
            }
        }}, true)
}

function ordersUserToggle() {
    $("#user_saved_info_list").toggle();
}

/*==== /USER DATA ====*/

/*==== ORDER DONE ====*/
function saveOrderClient() {
    let user_id = $("#order_user_id").val();
    let name = $("#user_name").val();
    let email = $("#user_email").val();
    let pass = $("#user_pass").val();
    JsHttpRequest.query(folder,{'w':'saveOrderClient', 'user_id':user_id, 'name':name, 'email':email, 'pass':pass},
        function (result, errors){ if (errors) {alert(errors);} if (result) {
            // зберегти дані користувача
            // авторизуватись
            location.href = "https://toko.ua/profile/orders/";
        }}, true);
}

function loginOrderClient() {
    let user_id = $("#order_user_id").val();
    JsHttpRequest.query(folder,{'w':'loginOrderClient', 'user_id':user_id},
        function (result, errors){ if (errors) {alert(errors);} if (result) {
            // авторизуватисьsad
            location.href = "https://toko.ua/profile/orders/";
        }}, true);
}

/*==== /ORDER DONE ====*/
