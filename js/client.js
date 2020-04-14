function showLangForm() { $("#LangForm").modal("toggle"); }

function selectLang(id) { "use strict";
    JsHttpRequest.query(folder,{'w':'selectLang', 'id':id},
        function (result, errors){ if (errors) {alert(errors);} if (result){
            let res = result.content;
            selectLangText(res);
            $("#LangForm").modal("hide");
            location.reload();
        }}, true);
}

function setSiteLang(id) { "use strict";
    JsHttpRequest.query(folder,{'w':'setSiteLang', 'id':id},
        function (result, errors){ if (errors) {alert(errors);} if (result){
            let res = result.content;
            location.href="https://toko.ua/"+res;
        }}, true);
}

function selectLangText(id) { "use strict";
    JsHttpRequest.query(folder,{'w':'selectLangText', 'id':id},
        function (result, errors){ if (errors) {alert(errors);} if (result){
            $("#lang_select").html(result.content);
        }}, true);
}

function showProfilePageOrders() { "use strict"; location.href="/profile/orders/"; }

function focusPhone() { "use strict"; $("#userlogin").focus(); }

function showLoginForm() { "use strict";
    let phone=$("#reg_phone").val();
    if (phone===undefined || phone==="") phone=$("#input_phone").val();
    if (phone===undefined || phone==="") phone=$("#input_phone2").val();
    $("#userpassword").val("");
    $("#myModal").modal("show");
    $("#userlogin").val(phone);
    document.getElementById("userpassword").select();
}

function setPriceList() { "use strict";
    JsHttpRequest.query(folder,{'w':'setPriceList'},
        function (result, errors){ if (errors) {alert(errors);} if (result){
            $("#profile_check_load").html(result.content);
			showProfilePrice();
        }}, true);
}

function saveProfileForm() { "use strict";
    let phone_input=$("#reg_phone"), phone=phone_input.val();
    let pass_input=$("#reg_password"), pass=pass_input.val();
    let name_input=$("#reg_name"), name=name_input.val();
    let email_input=$("#reg_email"), email=email_input.val();

    if (phone==="") phone_input.addClass("required_input"); else phone_input.removeClass("required_input");
    if (pass==="") pass_input.addClass("required_input"); else pass_input.removeClass("required_input");
    if (name==="") name_input.addClass("required_input"); else name_input.removeClass("required_input");
    if (email==="") email_input.addClass("required_input"); else email_input.removeClass("required_input");

    if ((phone!=="")&&(pass!=="")&&(name!=="")&&(email!=="")) {
        JsHttpRequest.query(folder,{'w':'check_reg_client', 'phone':phone, 'email':email},
            function (result, errors){ if (errors) {alert(errors);} if (result){
                if (result.content!==false) {
                    let text="{user_already_logged}!<br>{phone_cap}: "+result.content[0];
                    showAlertModal(text,"{error_cap}",0);
                } else {
                    showValidateModal(phone,validatePhone,saveProfile);
                }
            }}, true);
    }
}

function saveRegistrationForm() { "use strict";
    let phone_input=$("#reg_phone"), phone=phone_input.val();
    let pass_input=$("#reg_password"), pass=pass_input.val();
    let pass2_input=$("#reg_repassword"), pass2=pass2_input.val();
    let name_input=$("#reg_name"), name=name_input.val();
    let email_input=$("#reg_email"), email=email_input.val();
    let city_id=$("#reg_city option:selected").val();

    if (phone==="") phone_input.addClass("required_input"); else phone_input.removeClass("required_input");
    if (pass==="") pass_input.addClass("required_input"); else pass_input.removeClass("required_input");
    if (pass2!==pass || pass2==="") pass2_input.addClass("required_input"); else pass2_input.removeClass("required_input");
    if (name==="")  name_input.addClass("required_input"); else name_input.removeClass("required_input");
    if (city_id===undefined) $(".select2").addClass("required_input"); else $(".select2").removeClass("required_input");

    if ((phone!=="")&&(pass!=="")&&(pass===pass2)&&(name!=="")&&(city_id!==undefined)) {
        JsHttpRequest.query(folder,{'w':'check_reg_client', 'phone':phone, 'email':email},
            function (result, errors){ if (errors) {alert(errors);} if (result){
            if (result.content!==false) {
                let text="{user_already_logged}!<br>{client_login}: "+result.content[0];
                showAlertModal(text,"{error_cap}",0,showLoginForm);
            } else {
                showValidateModal(phone,validatePhone,saveRegistration);
            }
        }}, true);
    } else {
        showAlertModal("{input_all_data}!","{error_cap}",0);
    }
}

function saveRegistration() { "use strict";
    let phone = $("#reg_phone").val();
    let pass = $("#reg_password").val();
    let email = $("#reg_email").val();
    let name = $("#reg_name").val();
    let client_category = $("#reg_category option:selected").val();
    let client_city = $("#reg_city option:selected").val();
    let client_tpoint = $("#reg_tpoint option:selected").val();
    let mailing = $("#reg_mailing").prop("checked");
    JsHttpRequest.query(folder,{'w':'saveRegistration', 'phone':phone, 'pass':pass, 'email':email, 'name':name, 'client_category':client_category, 'client_city':client_city, 'client_tpoint':client_tpoint, 'mailing':mailing},
        function (result, errors){ if (errors) {alert(errors);} if (result){
            let text="{success_registered}!<br>{phone_cap}:"+phone;
            showAlertModal(text,"{done_cap}",1,loginFormParams);
        }}, true);
}

function loginForm() { "use strict";
    let login = $("#userlogin").val();
    let password = $("#userpassword").val();
    if (login==="" || password==="") showAlertModal("{input_all_data}!","{error_cap}",0,focusPhone);
    else {
        JsHttpRequest.query(folder,{'w':'loginClient', 'login':login, 'password':password},
            function (result, errors){ if (errors) {alert(errors);} if (result){
                if (result.content===false) showAlertModal("{user_not_logged}!","{error_cap}",0);
                else location.reload();
            }}, true);
    }
}

function loginFormOrder() { "use strict";
    let login = $("#userlogin2").val();
    let password = $("#userpassword2").val();
    if (login==="" || password==="") showAlertModal("{input_all_data}!","{error_cap}",0);
    else {
        JsHttpRequest.query(folder,{'w':'loginClient', 'login':login, 'password':password},
            function (result, errors){ if (errors) {alert(errors);} if (result){
                if (result.content===false) showAlertModal("{user_not_logged}!","{error_cap}",0);
                else location.reload();
            }}, true);
    }
}

function signInForm() { "use strict";
    let login = $("#userlogin2").val();
    let password = $("#userpassword2").val();
    if (login==="" || password==="") showAlertModal("{input_all_data}!","{error_cap}",0);
    else {
        JsHttpRequest.query(folder,{'w':'loginClient', 'login':login, 'password':password},
            function (result, errors){ if (errors) {alert(errors);} if (result){
                if (result.content===false) showAlertModal("{user_not_logged}!","{error_cap}",0);
                else location.reload();
            }}, true);
    }
}

function loginFormParams() { "use strict";
    let login = $("#reg_phone").val();
    let password = $("#reg_password").val();
    if (login==="" || password==="") showAlertModal("{input_all_data}!","{error_cap}",0);
    else {
        JsHttpRequest.query(folder,{'w':'loginClient', 'login':login, 'password':password},
            function (result, errors){ if (errors) {alert(errors);} if (result){
                if (result.content===false) showAlertModal("{user_not_logged}!","{error_cap}",0);
                else location.href="/profile/";
            }}, true);
    }
}

function logoutForm() { "use strict";
    JsHttpRequest.query(folder,{'w':'logoutClient'},
        function (result, errors){ if (errors) {alert(errors);} if (result){
            location.href="/";
        }}, true);
}

function saveProfile() { "use strict";
    let phone = $("#reg_phone").val();
    let pass = $("#reg_password").val();
    let email = $("#reg_email").val();
    let name = $("#reg_name").val();
    JsHttpRequest.query(folder,{'w':'saveProfile', 'phone':phone, 'pass':pass, 'email':email, 'name':name},
        function (result, errors){ if (errors) {alert(errors);} if (result){
            showNotify("{done_cap}:","{data_saved}!","success");
            showProfileAccount();
        }}, true);
}

function showProfileAccount() {
    window.history.pushState("account", "Profile", "/profile/account/");
    $("#profile_account").html("<div class=\"loader\"></div>");
    JsHttpRequest.query(folder,{'w':'showProfileAccount'},
        function (result, errors){ if (errors) {alert(errors);} if (result){
            $("#profile_account").html(result.content);
        }}, true);
}

function showProfileOrders() { "use strict";
    window.history.pushState("orders", "Profile", "/profile/orders/");
    $("#radio_orders").prop("checked", true);
    $("#profile_orders").html("<div class=\"loader\"></div>");
    JsHttpRequest.query(folder,{'w':'showProfileOrders'},
        function (result, errors){ if (errors) {alert(errors);} if (result){
            $("#profile_orders").html(result.content);
        }}, true);
}

function showProfileOrdersArts(dp_id,order_id) { "use strict";
    $("#radio_orders_arts").prop("checked", true);
    $("#profile_orders").html("<div class=\"loader\"></div>");
    JsHttpRequest.query(folder,{'w':'showProfileOrdersArts', 'dp_id':dp_id, 'order_id':order_id},
        function (result, errors){ if (errors) {alert(errors);} if (result){
            $("#profile_orders").html(result.content);
        }}, true);
}

function showProfileBasketForm() { "use strict";
    window.history.pushState("basket", "Profile", "/profile/basket/");
    $("#profile_basket_form").html("<div class=\"loader\"></div>");
    JsHttpRequest.query(folder,{'w':'showProfileBasketForm'},
        function (result, errors){ if (errors) {alert(errors);} if (result){
            $("#profile_basket_form").html(result.content);
        }}, true);
}

function showProfileCheckForm() { "use strict";
    window.history.pushState("check", "Profile", "/profile/check/");
    let data_start = $("#saldo_data_start").val(); if (data_start===undefined) data_start=0;
    let data_end = $("#saldo_data_end").val(); if (data_end===undefined) data_end=0;
    $("#check_block").html("<div class=\"loader\"></div>");
    JsHttpRequest.query(folder,{'w':'showProfileCheckForm', 'data_start':data_start, 'data_end':data_end},
        function (result, errors){ if (errors) {alert(errors);} if (result){
            $("#check_block").html(result.content);
        }}, true);
}

function showProfilePrice() { "use strict";
    window.history.pushState("price", "Profile", "/profile/price/");
    $("#profile_file_list").html("<div class=\"loader\"></div>");
    JsHttpRequest.query(folder,{'w':'showProfilePrice'},
        function (result, errors){ if (errors) {alert(errors);} if (result){
            $("#profile_file_list").html(result.content);
        }}, true);
}

function recoverPassword() { "use strict";
    let phone=$("#recover_phone").val();
    JsHttpRequest.query(folder,{'w':'check_reg_client', 'phone':phone},
        function (result, errors){ if (errors) {alert(errors);} if (result){
            if (result.content!==false) {
                recoverPasswordNext();
            } else {
                let text="{user_not_logged}!";
                showAlertModal(text,"{error_cap}",0);
            }
        }
    });
}

function recoverPasswordNext() { "use strict";
    let phone=$("#recover_phone").val();
    JsHttpRequest.query(folder,{'w':'recoverPassword', 'phone':phone},
        function (result, errors){ if (errors) {alert(errors);} if (result){
            $("#recover_block").html(result.content);
        }}, true);
}

