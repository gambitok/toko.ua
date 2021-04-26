
function goHome() {
    location.href = "/";
}

function stayInOrder() {
    showNotify("{error_cap}!","{basket_empty}!","danger");
}

function showNotify(title, text, type_text) {
    JsHttpRequest.query(folder,{ 'w': 'changeLangAlert', 'message':text, 'title':title },
        function (result, errors){ if (errors) {} if (result){
            let title_notify = "<b>" + result.content[1] + "</b>";
            let text_notify = result.content[0];
            $.notify({
                icon: "glyphicon glyphicon-star",
                title: title_notify,
                message: text_notify
            },{
                delay: 1000,
                z_index: 9999999999,
                placement: {
                    from: "bottom"
                },
                type: type_text
            });
        }}, true);
}

function moveBasket(id, art_id, brand_id, stock, storage_id, suppl_id) {
    let count_id = $("#count_" + id);
    let basket_count_id = $("#basket_count_" + id);
    let count = count_id.val();
    // for single product
    if (id == 'one') {
        count = 1;
    }

    if (parseInt(stock) < parseInt(count) || parseInt(count) === 0) {
        var secret = parseInt(stock) + 1;
        while (parseInt(secret) > parseInt(stock)) {
            secret = prompt("Выбранное количество продукта превышает доступное количество!", 1);
            if (parseInt(secret) === 0) {
                // count_id.val(stock);
                count_id.val(1);
                showNotify("{error_cap}!", "{wrong_count_cap}!", "danger");
                return;
            }
            if (parseInt(secret) < 0) {
                secret = 999999;
            } else if (isNaN(parseInt(secret))) {
                secret = 999999;
            }
        }
        if (secret === "") {
            secret = 0;
        }
        count_id.val(secret);
        count = secret;

        if (secret !== 0 && secret !== "") {
            JsHttpRequest.query(folder,{'w':'moveToBasket', 'art_id':art_id, 'brand_id':brand_id, 'count':count, 'stock':stock, 'storage_id':storage_id, 'suppl_id':suppl_id},
                function (result, errors){ if (errors) {alert(errors);} if (result){
                    let old_count = parseInt(result["old_amount"]);
                    let art_name = result["art_name"];
                    let all_count = old_count + parseInt(count);
                    let message = count + " {amount_abbr}. ";
                    let message_all = "";
                    if (old_count > 0) {
                        message_all = "<br><b>{total_basket_cap}:</b> " + all_count + " {amount_abbr}.";
                    }
                    showNotify("{done_cap}:", "{art_cap} '" + art_name + "' - " + message + " {added_to_basket}!" + message_all, "success");
                    showBasketStatus();
                    showBasketForm();
                    basket_count_id.html(result["basket_count"]);
                }}, true);
        }
    } else {
        JsHttpRequest.query(folder,{'w':'moveToBasket', 'art_id':art_id, 'brand_id':brand_id, 'count':count, 'stock':stock, 'storage_id':storage_id, 'suppl_id':suppl_id},
            function (result, errors){ if (errors) {alert(errors);} if (result){
                let old_count = parseInt(result["old_amount"]);
                let art_name = result["art_name"];
                let all_count = old_count + parseInt(count);
                let message = count + " {amount_abbr}. ";
                let message_all = "";
                if (old_count > 0) {
                    message_all = "<br><b>{total_basket_cap}:</b> " + all_count + " {amount_abbr}.";
                }
                showNotify("{done_cap}:", "{art_cap} '" + art_name + "' - " + message + " {added_to_basket}!" + message_all, "success");
                showBasketStatus();
                showBasketForm();
                basket_count_id.html(result["basket_count"]);
            }}, true);
    }
}

function showBasketMinForm() {
    $("#BasketForm").modal("show");
    $("#basket_block").html("");
    $(".bar").bigSlide();
    $(".fixed").addClass("hidden");
    JsHttpRequest.query(folder,{'w':'showBasketMinForm'},
        function (result, errors){ if (errors) {alert(errors);} if (result){
            $("#basket_block").html(result.content);
        }}, true);
}

function updateCountBasket(status, art_id, storage_id, stock, phone) {
    let prefix = "";
    if (phone > 0) {
        prefix = "_phone";
    }
    let count_id = $("#count_" + art_id + "_" + storage_id + prefix);
    let count = parseInt(count_id.val());
    if (status > 0) {
        count = count + 1;
        count_id.val(count);
    } else {
        if (count > 0) {
            count = count - 1;
            count_id.val(count);
        }
    }
    updateBasketForm(art_id, storage_id, stock, phone);
}

function updateBasketForm(art_id, storage_id, stock, phone) {
    let prefix = "";
    if (phone > 0) {
        prefix = "_phone";
    }
    var count_id = $("#count_" + art_id + "_" + storage_id + prefix);
    var count = count_id.val();
    if (parseInt(stock) < parseInt(count) || parseInt(count) === 0) {
        var secret = parseInt(stock) + 1;
        while (parseInt(secret) > parseInt(stock)) {
            if (secret === null) { count_id.val(1); return; }
            secret = prompt("Выбранное количество продукта превышает доступное количество!", 1);
            if (secret === null) { count_id.val(stock); return; }
            if (parseInt(secret) < 0) {
                secret = 999999;
            } else if (isNaN(parseInt(secret))) {
                secret = 999999;
            }
        }
        if (secret === "") {
            secret = 0;
        }
        count_id.val(secret);
        count = secret;
        if (secret !== 0 && secret !== "") {
            JsHttpRequest.query(folder,{'w':'updateBasketForm', 'art_id':art_id, 'count':count, 'storage_id':storage_id},
                function (result, errors){ if (errors) {alert(errors);} if (result){
                    showBasketForm();
                }}, true);
        }
    } else {
        JsHttpRequest.query(folder,{'w':'updateBasketForm', 'art_id':art_id, 'count':count, 'storage_id':storage_id},
            function (result, errors){ if (errors) {alert(errors);} if (result){
                showBasketForm();
            }}, true);
    }
}

// DELETE ITEM FROM BASKET
function deleteFromBasket(art_id, storage_id, art_name) {
    JsHttpRequest.query(folder,{'w':'deleteFromBasket', 'art_id':art_id, 'storage_id':storage_id},
        function (result, errors){ if (errors) {alert(errors);} if (result){
            showBasketForm();
            showNotify("{done_cap}:","{art_cap} '"+art_name+"' {removed_from_basket}!","danger")
        }}, true);
}

// CHECK/UNCHECK BASKET ITEM
function checkBasketItem(art_id, storage_id, a) {
    let status = $(a).attr("checked");
    if (status === undefined) status = 1; else status = 0;
    JsHttpRequest.query(folder,{'w':'checkBasketItem', 'art_id':art_id, 'storage_id':storage_id, 'status':status},
        function (result, errors){ if (errors) {alert(errors);} if (result){
            showBasketForm();
        }}, true);
}

// CHECK/UNCHECK BASKET ITEMS
function checkAllBasket() {
    let checked_basket = $(".check-brand");
    let btn = $("#check_all_box");
    if (btn.prop("checked") === true) {
        checked_basket.each(function () {
            checkBasketItem($(this).attr("id"),$(this).attr("name"),this);
            $(this).attr("checked","checked");
        });
        btn.attr("checked","checked");
    } else {
        checked_basket.each(function () {
            checkBasketItem($(this).attr("id"),$(this).attr("name"),this);
            $(this).removeAttr("checked");
        });
        btn.removeAttr("checked");
    }
}

function showBasketForm() {
    let cur = parseInt($(".radio-group input[name=cur]:checked").attr("value"));
    JsHttpRequest.query(folder,{'w':'showBasketForm', 'cur':cur},
        function (result, errors){ if (errors) {alert(errors);} if (result){
            $("#basket_block").html(result.content);
            loadInputNumber();
            showBasketStatus();
        }}, true);
}

// UPDATE BASKET STATUS, WHEN ARTICLE ADD TO BASKET
function showBasketStatus() {
    let status1 = $("#basket_status");
    let status3 = $("#basket_status3");
    let status5 = $("#tool-basket");
    JsHttpRequest.query(folder,{'w':'updateBasketStatus'},
        function (result, errors){ if (errors) {alert(errors);} if (result){
            if (result.content[0] !== "") {
                status1.addClass("show"); status1.removeClass("none"); status1.removeClass("tool-status-hidden"); status1.html(result.content[0]);
                status3.addClass("show"); status3.removeClass("none"); status3.html(result.content[0]);
                status5.removeClass("tool-status-hidden"); status5.text(result.content[0]);
            } else {
                status1.addClass("none"); status1.removeClass("show");
                status3.addClass("none"); status3.removeClass("show");
                status5.addClass("tool-status-hidden");
            }
        }}, true);
}

// FINISH FAST ORDER
function finishFastOrder(name) {
    $("#input_phone").val("");
    validateForm("phone", "input");
    let input_phone = $("#input_phone2");
    let phone = input_phone.val();
    if (!validationInput(name)) {
        input_phone.tooltip("show");
        setTimeout(function() {
            input_phone.tooltip("hide");
        }, 5000);
        return true;
    } else {
        JsHttpRequest.query(folder,{'w':'check_reg_client', 'phone':phone},
            function (result, errors){ if (errors) {alert(errors);} if (result){
                if (result.content !== false) {
                    let text = "{user_already_logged}!<br>{phone_cap}: " + result.content[0];
                    showAlertModal(text, "{error_cap}", 0, showLoginForm);
                } else {
                    validateOperator(phone);
                }
            }}, true);
    }
}
// VALIDATE PHONE NUMBER (by OPERATOR)
function validateOperator(phone) {
    JsHttpRequest.query(folder,{'w':'validateOperator', 'phone':phone},
        function (result, errors){ if (errors) {alert(errors);} if (result){
            if (result.content === false) {
                let text = "{check_phone_data}!";
                showAlertModal(text, "{error_cap}", 0);
            } else {
                showFastOrder();
                $("#BasketForm").modal("hide");
            }
        }}, true);
}

function showFastOrder() {
    let phone = $("#input_phone2").val();
    JsHttpRequest.query(folder,{'w':'finish_fast_order', 'phone':phone},
        function (result, errors){ if (errors) {alert(errors);} if (result){
            location.href = "https://toko.ua/order/?order_id=" + result.content[0] + "&user_id=" + result.content[1] + "&user_status=" + result.content[2];
        }}, true);
}

function closeOrderArtUpdate(dp_id, art_id, order_id) {
    JsHttpRequest.query(folder,{'w':'closeOrderArtUpdate', 'dp_id':dp_id, 'art_id':art_id, 'order_id':order_id},
        function (result, errors){ if (errors) {alert(errors);} if (result){
            showAlertModal(result.content, "{warning_cap}!", 2, updateOrderArt);
            return true;
        }}, true);
}

function updateOrderArt() {
    let order_id = $("#order_id").val();
    JsHttpRequest.query(folder,{'w':'updateOrderArt', 'order_id':order_id},
        function (result, errors){ if (errors) {alert(errors);} if (result){
            showProfileOrders();
        }}, true);
}

function validateForm(name, type) {
    let valid = $("#validate_input_" + name);
    let variable;
    if (type === "input") {
        variable = $("#input_" + name).val();
    } else {
        variable = $("#select2-select_" + name + "-container").text();
    }
    if (variable === "") {
        valid.removeClass("accept");
        valid.addClass("non_accept");
        valid.removeClass("fa-check-circle");
        valid.addClass("fa-times-circle");
    } else {
        valid.removeClass("non_accept");
        valid.addClass("accept");
        valid.removeClass("fa-times-circle");
        valid.addClass("fa-check-circle");
    }
    return variable;
}

function validationInput(name) {
    let id = $("#" + name).attr("id");
    let valid = $("#validate_" + id);
    let max_count = 16;
    let count = $("#" + id).val().replace(/[_-]/g, '').length;
    if (max_count === count) {
        valid.removeClass("non_accept");
        valid.addClass("accept");
        valid.removeClass("fa-times-circle");
        valid.addClass("fa-check-circle");
        return true;
    } else {
        valid.removeClass("accept");
        valid.addClass("non_accept");
        valid.removeClass("fa-check-circle");
        valid.addClass("fa-times-circle");
        return false;
    }
}
