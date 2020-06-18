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

    $("input[type='radio']").change(function() {

        $(".orders-block-row-hidden").each(function () {
           $(this).removeClass("orders-block-row-display");
        });

        $("input[type='radio']").each(function () {
            if($(this).is(':checked')) $("#" + $(this).attr("data-tab-href")).addClass("orders-block-row-display");
        });

    });

    // let select2_search = $("#user_city").data("select2").dropdown.$search.val();
    // console.log(select2_search);
    //
    // $("#user_city").change(function () {
    //    console.log("1");
    // });

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

/*==== DELIVERY ====*/
function setCityDepartments(city_id) {
    JsHttpRequest.query(folder,{'w':'setCityDepartments', 'city_id':city_id},
        function (result, errors){ if (errors) {alert(errors);} if (result) {
            let select_np = $("#select_delivery_np");
            let select_up = $("#select_delivery_up");
            select_np.html(result.content[0]); select_np.select2();
            select_up.html(result.content[1]); select_up.select2();
        }}, true);
}

// 1	Самовывоз из магазина (бесплатно)		Отображается если выбран город, который привязан к одной из ТТ (т.е. Киев или Хмельницкий)
// 2	Доставка курьером (бесплатно)		Отображается если выбран город, который привязан к одной из ТТ (т.е. Киев или Хмельницкий)
// 3	Доставка курьером на ваше СТО (бесплатно)		Отображается если выбран город, который привязан к одной из ТТ (т.е. Киев или Хмельницкий)
// 4	В отделение Новой Почты (?)		Отображается в любом случаи, но если в выбранном населённом пункте есть отделения
// 5	Курьерская доставка Новой Почты (?)		Отображается если выбран город, который не привязан ни к одной из ТТ (т.е. Киев или Хмельницкий)
// 6	Отделение Укрпочты (?)		Отображается в любом случаи, но если в выбранном населённом пункте есть отделения
// 7	Другие компании экспресс доставки (?)		Отображается если выбран город, который не привязан ни к одной из ТТ (т.е. Киев или Хмельницкий)

/*==== /DELIVERY ====*/

/*==== PAYMENT ====*/
// 1	Оплата наличными при получении заказа		Отображается если способ доставки - 1, 2, 3
// 2	Наложенный платёж		Отображается если способ доставки - 4, 5, 6, 7
// 3	Оплата на карту Приват Банка		Отображается при любом способе доставки, в будущем оплата картой на сайте

/*==== /PAYMENT ====*/

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
        $("#orders-info").removeClass("none");
    }
}

function editFields() {
    $("#valid_button").removeClass("none");
    $("#edit_button").addClass("none");
    $("#orders-info").addClass("none");
    $(".valid_field").each(function() {
        $(this).prop("disabled", false);
    });
}

/*==== /SAVE ====*/