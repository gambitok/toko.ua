function goHome() { location.href = "/"; }

function goBasket() { location.href = "/basket"; }

function stayInOrder() { showAlertModal("{basket_empty}!","{error_cap}",0); }

function toggleBasket() { $("#basket_toggle").slideToggle(); }

function showNotify(title,text,type_text) {
    JsHttpRequest.query(folder,{ 'w': 'changeLangAlert', 'message':text, 'title':title },
        function (result, errors){ if (errors) {} if (result){
            let title_notify = "<b>"+result.content[1]+"</b>";
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

function moveBasket(id,art_id,brand_id,stock,storage_id,suppl_id) { "use strict";
    let count_id = $("#count_"+id);
    let count = count_id.val();

    if (parseInt(stock)<parseInt(count) || parseInt(count)===0) {
        var secret = parseInt(stock)+1;
        while (parseInt(secret) > parseInt(stock)) {
            secret = prompt("Выбранное количество продукта превышает доступное количество!", 1);
            if (secret === null) { count_id.val(stock); return; }
            if (parseInt(secret) < 0) {
                secret=999999;
            } else if (isNaN(parseInt(secret))) {
                secret=999999;
            }
        }
        if (secret==="") secret=0;
        count_id.val(secret);
        count=secret;

        if (secret!==0 && secret!=="") {
            JsHttpRequest.query(folder,{'w':'moveToBasket', 'art_id':art_id, 'brand_id':brand_id, 'count':count, 'stock':stock, 'storage_id':storage_id, 'suppl_id':suppl_id},
                function (result, errors){ if (errors) {alert(errors);} if (result){
                    let old_count=parseInt(result["old_amount"]);
                    let art_name=result["art_name"];
                    let all_count=old_count+parseInt(count);
                    let message=count+" {amount_abbr}. ";
                    let message_all="";
                    if (old_count>0) message_all="<br><b>{total_basket_cap}:</b> "+all_count+" {amount_abbr}.";
                    showNotify("{done_cap}:","{art_cap} '"+art_name+"' - "+message+" {added_to_basket}!" + message_all,"success");
                    showBasketStatus();
                }}, true);
        }
    } else {
        JsHttpRequest.query(folder,{'w':'moveToBasket', 'art_id':art_id, 'brand_id':brand_id, 'count':count, 'stock':stock, 'storage_id':storage_id, 'suppl_id':suppl_id},
            function (result, errors){ if (errors) {alert(errors);} if (result){
                let old_count=parseInt(result["old_amount"]);
                let art_name=result["art_name"];
                let all_count=old_count+parseInt(count);
                let message=count+" {amount_abbr}. ";
                let message_all="";
                if (old_count>0) message_all="<br><b>{total_basket_cap}:</b> "+all_count+" {amount_abbr}.";
                showNotify("{done_cap}:","{art_cap} '"+art_name+"' - "+message+" {added_to_basket}!" + message_all,"success");
                showBasketStatus();
            }}, true);
    }
}

function showBasketMinForm() {
    $("#BasketForm").modal("show");
    $(".bar").bigSlide();
    JsHttpRequest.query(folder,{'w':'showBasketMinForm'},
        function (result, errors){ if (errors) {alert(errors);} if (result){
            $("#basket_block").html(result.content);
        }}, true);
}

function loadInputNumber() { "use strict";
    $(".show_count").keydown(function (e) {
        if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 110, 190]) !== -1 ||
            (e.keyCode === 65 && (e.ctrlKey === true || e.metaKey === true)) ||
            (e.keyCode === 67 && (e.ctrlKey === true || e.metaKey === true)) ||
            (e.keyCode === 88 && (e.ctrlKey === true || e.metaKey === true)) ||
            (e.keyCode >= 35 && e.keyCode <= 39)) {
            return;
        }
        if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
            e.preventDefault();
        }
    });
}

function updateCountBasket(status, art_id, storage_id, stock, phone) {
    let prefix="";
    if (phone>0) prefix="_phone";
    let count_id=$("#count_"+art_id+"_"+storage_id+prefix);
    let count=parseInt(count_id.val());
    if (status>0) {
        count=count+1;
        count_id.val(count);
    } else {
        if (count>0) {
            count=count-1;
            count_id.val(count);
        }
    }
    updateBasketForm(art_id,storage_id,stock,phone);
}

function updateBasketForm(art_id,storage_id,stock,phone) { "use strict";
    let prefix="";
    if (phone>0) prefix="_phone";
    var count_id=$("#count_"+art_id+"_"+storage_id+prefix);
    var count=count_id.val();
    if (parseInt(stock)<parseInt(count) || parseInt(count)===0) {
        var secret = parseInt(stock) + 1;
        while (parseInt(secret) > parseInt(stock)) {
            if (secret === null) { count_id.val(1); return; }
            secret = prompt("Выбранное количество продукта превышает доступное количество!", 1);
            if (secret === null) { count_id.val(stock); return; }
            if (parseInt(secret) < 0) {
                secret=999999;
            } else if (isNaN(parseInt(secret))) {
                secret=999999;
            }
        }
        if (secret==="") secret = 0;
        count_id.val(secret);
        count=secret;
        if (secret!==0 && secret!=="") {
            //showLoader();
            JsHttpRequest.query(folder,{'w':'updateBasketForm', 'art_id':art_id, 'count':count, 'storage_id':storage_id},
                function (result, errors){ if (errors) {alert(errors);} if (result){
                    showBasketForm();
                    //setTimeout(hideLoader, 500);
                }}, true);
        }
    } else {
        //showLoader();
        JsHttpRequest.query(folder,{'w':'updateBasketForm', 'art_id':art_id, 'count':count, 'storage_id':storage_id},
            function (result, errors){ if (errors) {alert(errors);} if (result){
                showBasketForm();
                //setTimeout(hideLoader, 500);
            }}, true);
    }
}

function deleteFromBasket(art_id,storage_id,art_name) {
    JsHttpRequest.query(folder,{'w':'deleteFromBasket', 'art_id':art_id, 'storage_id':storage_id},
        function (result, errors){ if (errors) {alert(errors);} if (result){
            showBasketForm();
            showNotify("{done_cap}:","{art_cap} '"+art_name+"' {removed_from_basket}!","danger")
        }}, true);
}

function checkBasketItem(art_id,storage_id,a) {
    let status=$(a).attr("checked");
    if (status===undefined) status=1; else status=0;
    JsHttpRequest.query(folder,{'w':'checkBasketItem', 'art_id':art_id, 'storage_id':storage_id, 'status':status},
        function (result, errors){ if (errors) {alert(errors);} if (result){
            showBasketForm();
        }}, true);
}

function checkAllBasket() {
    let checked_basket=$(".check-brand");
    let btn=$("#check_all_box");
    if (btn.prop("checked")===true) {
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

function detectmob() {
    if (navigator.userAgent.match(/Android/i)
        || navigator.userAgent.match(/webOS/i)
        || navigator.userAgent.match(/iPhone/i)
        || navigator.userAgent.match(/iPad/i)
        || navigator.userAgent.match(/iPod/i)
        || navigator.userAgent.match(/BlackBerry/i)
        || navigator.userAgent.match(/Windows Phone/i)) {
        return true;
    } else {
        return false;
    }
}

function showBasketForm() {
    showLoader();
    let cur = parseInt($(".radio-group input[name=cur]:checked").attr("value"));
    //$("#basket_block").html("<div class='content'></div>");
    JsHttpRequest.query(folder,{'w':'showBasketForm', 'cur':cur},
        function (result, errors){ if (errors) {alert(errors);} if (result){
            $("#basket_block").html(result.content);
            loadInputNumber();
            showBasketStatus();
            setTimeout(hideLoader, 500);
        }}, true);
}

function showBasketStatus() {
    let status1=$("#basket_status");
    let status2=$("#basket_status2");
    let status3=$("#basket_status3");
    let status4=$("#basket_status4");
    JsHttpRequest.query(folder,{'w':'updateBasketStatus'},
        function (result, errors){ if (errors) {alert(errors);} if (result){
            if (result.content[0]!=="") {
                status1.addClass("show"); status1.removeClass("none"); status1.html(result.content[0]);
                status2.addClass("show"); status2.removeClass("none"); status2.html(result.content[0]);
                status3.addClass("show"); status3.removeClass("none"); status3.html(result.content[0]);
                status4.addClass("show"); status4.removeClass("none"); status4.html(result.content[0]);
            } else {
                status1.addClass("none"); status1.removeClass("show");
                status2.addClass("none"); status2.removeClass("show");
                status3.addClass("none"); status3.removeClass("show");
                status4.addClass("none"); status4.removeClass("show");
            }
        }}, true);
}


// function closeBasketUpdate(art_id,storage_id) {
//     JsHttpRequest.query(folder,{'w':'close_basket_update', 'art_id':art_id, 'storage_id':storage_id},
//         function (result, errors){ if (errors) {alert(errors);} if (result){
//             showBasketStatus();
//             showBasketForm();
//         }}, true);
//     showUpdateData(art_id,storage_id);
// }

// function showUpdateData(art_id,storage_id) {
//     JsHttpRequest.query(folder,{'w':'show_update_data', 'art_id':art_id, 'storage_id':storage_id},
//         function (result, errors){ if (errors) {alert(errors);} if (result){
//             showAlertModal(result.content[0],result.content[1]);
//         }}, true);
// }

function finishOrder() { "use strict";
    let name=$("#input_name").val();
    let phone=$("#input_phone").val();
    let email=$("#input_email").val(); if (email===undefined) email="";
    let user=$("#input_user").val();
    let region=$("#select2-select_city-container").attr("name");
    if (name==="" || phone==="" || region==="0") {
        showAlertModal("{input_all_data}!","{error_cap}!",0);
        return true;
    } else {
        JsHttpRequest.query(folder,{'w':'check_reg_client', 'phone':phone, 'email':email},
            function (result, errors){ if (errors) {alert(errors);} if (result){
                if (result.content!==false && user==="0") {
                    let text="{user_already_logged}!<br>{phone_cap}: "+result.content[0];
                    showAlertModal(text,"{error_cap}",0,showLoginForm);
                } else {
                    showFinishOrderForm();
                }
            }}, true);
    }
}

function finishFastOrder() { "use strict";
    $("#input_phone").val(""); validateForm("phone","input");
    let phone=$("#input_phone2").val();
    if (phone==="") {
        showAlertModal("{input_all_data}!","{error_cap}!",0);
        return true;
    } else {
        JsHttpRequest.query(folder,{'w':'check_reg_client', 'phone':phone},
            function (result, errors){ if (errors) {alert(errors);} if (result){
                if (result.content!==false) {
                    let text="{user_already_logged}!<br>{phone_cap}: "+result.content[0];
                    showAlertModal(text,"{error_cap}",0,showLoginForm);
                } else {
                    showValidateModal(phone,validatePhone,showFastOrder);
                    $("#BasketForm").modal("hide");
                }
            }}, true);
    }
}

function showFinishOrderForm() {
    let client=$("#input_client").val();
    let client_user_id=$("#input_user").val();
    let tpoint=$("#input_tpoint").val();
    let name=$("#input_name").val();
    let phone=$("#input_phone").val();
    let email=$("#input_email").val();
    let region=$("#select2-select_city-container").attr("name");
    let delivery=$("#select_delivery option:selected").val();
    let delivery_info=$("#select_delivery_info option:selected").text();
    let carrier_id=$("#select_carrier_id option:selected").val();
    let payment=$("#select_payment option:selected").val();
    let payment_info=$("#input_payment_info").val();
    JsHttpRequest.query(folder,{'w':'finish_order', 'client_id':client, 'client_user_id':client_user_id, 'tpoint_user_id':tpoint, 'name':name, 'phone':phone, 'region':region, 'email':email, 'delivery':delivery, 'delivery_info':delivery_info, 'payment':payment, 'payment_info':payment_info, 'carrier_id':carrier_id},
        function (result, errors){ if (errors) {alert(errors);} if (result){
            location.href = "https://toko.ua/order/?order_id="+result.content[0]+"&client_id="+result.content[1];
        }}, true);
}

function showFastOrder() {
    let phone=$("#input_phone2").val();
    let client=$("#input_client").val();
    let user=$("#input_user").val();
    let tpoint=$("#input_tpoint").val();
    JsHttpRequest.query(folder,{'w':'finish_order', 'client_id':client, 'client_user_id':user, 'tpoint_user_id':tpoint, 'phone':phone},
        function (result, errors){ if (errors) {alert(errors);} if (result){
            location.href = "https://toko.ua/order/?order_id="+result.content[0]+"&client_id="+result.content[1];
        }}, true);
}

function saveClientRetail() {
    let pass=$("#reg_password").val();
    let client_id=$("#reg_client_id").val();
    let order_id=$("#reg_order_id").val();
    let name=$("#reg_name").val();
    let phone=$("#reg_phone").val();
    let email=$("#reg_email").val();
    if (pass==="") {
        showAlertModal("{input_all_data}!","{error_cap}!",0);
        return true;
    } else {
        JsHttpRequest.query(folder,{'w':'finish_order_success', 'client_id':client_id, 'pass':pass, 'order_id':order_id, 'name':name, 'phone':phone, 'email':email},
            function (result, errors){ if (errors) {alert(errors);} if (result){
                let text="{success_registered}!<br>{phone_cap}: "+result.content[0]+"<br>{client_password_cap}: "+result.content[1];
                showAlertModal(text,"{done_cap}!",1,loginFormParams); return true;
            }}, true);
    }
}

function deleteClientRetail() {
    let pass="";
    let client_id=$("#reg_client_id").val();
    let order_id=$("#reg_order_id").val();
    JsHttpRequest.query(folder,{'w':'finish_order_success', 'client_id':client_id, 'pass':pass, 'order_id':order_id},
        function (result, errors){ if (errors) {alert(errors);} if (result){
            showAlertModal("{order_completed}!","{done_cap}!",1,goHome); return true;
        }}, true);
}

function closeOrderUpdate(dp_id,order_id) { "use strict";
    showProfileOrdersArts(dp_id,order_id);
}

function closeOrderArtUpdate(dp_id,art_id,order_id) { "use strict";
    JsHttpRequest.query(folder,{'w':'closeOrderArtUpdate', 'dp_id':dp_id, 'art_id':art_id, 'order_id':order_id},
        function (result, errors){ if (errors) {alert(errors);} if (result){
            showAlertModal(result.content,"{warning_cap}!",2,updateOrderArt);
            return true;
        }}, true);
}

function updateOrderArt() { "use strict";
    let order_id=$("#order_id").val();
    JsHttpRequest.query(folder,{'w':'updateOrderArt', 'order_id':order_id},
        function (result, errors){ if (errors) {alert(errors);} if (result){
            showProfileOrders();
        }}, true);
}

function getTypeDeliveryId() { "use strict";
    let delivery=$("#select_delivery option:selected").val();
    if (delivery==="60") $("#carrier_id_row").css("display","flex");
    else $("#carrier_id_row").css("display","none");
}

function setDeliveryInfoInput() {
    let delivery_select=$("#select_delivery_info option:selected").text();
    $("#input_delivery_info").val(delivery_select);
}

function showNewAdressForm() {
    $("#AddressForm").modal("show");
}

function addNewAddressForm() {
    let client_id=$("#input_client").val();
    let address=$("#new_client_address").val();
    JsHttpRequest.query(folder,{'w':'addNewAddressForm', 'client_id':client_id, 'address':address},
        function (result, errors){ if (errors) {alert(errors);} if (result){
            if (result.content==1) {
                $("#AddressForm").modal("hide");
                let max_option = $("#select_delivery_info option:last").val();
                $("#select_delivery_info").append(new Option(address, max_option));
            } else {
                showAlertModal(result.content,"{error_cap}",0);
            }
        }}, true);
}

function validateForm(name, type) { "use strict";
    var valid=$("#validate_input_"+name);
    var variable="";
    if (type==="input") variable=$("#input_"+name).val();
    else variable=$("#select2-select_"+name+"-container").text();
    if (variable==="") {
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

