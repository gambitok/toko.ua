
function navigateTo(id) {
    $([document.documentElement, document.body]).animate({
        scrollTop: $("#" + id).offset().top
    }, 500);
}

// Modal `Region`
function showRegionForm() {
    JsHttpRequest.query(folder,{'w':'showModalForm', 'form':"region"},
        function (result, errors){ if (errors) {alert(errors);} if (result){
            $("#modals").append(result.content);
            $("#RegionForm").modal("show");
            $("#menu").css('left', '-100%');

            $("#RegionForm").on('hidden.bs.modal', function () {
                setCookie('tpoint_id_status', 1);
            });
        }}, true);
}
// reg_form.on('hidden.bs.modal', function () {
//     setCookie('tpoint_id_status', 1);
// });

// Modal `Help` in Catalogs
function showPhoneForm() {
    JsHttpRequest.query(folder,{'w':'showModalForm', 'form':"help"},
        function (result, errors){ if (errors) {alert(errors);} if (result){
            $("#modals").append(result.content);
            $("#HelpForm2").modal("show");
            new LazyLoad({ elements_selector: ".lazy" });
        }}, true);
}

// SHOW OTHER STORAGES
function showStorage(art_id) {
    $("." + art_id + "-hide").toggleClass("none");
    $("#fa-" + art_id).toggleClass("none");
    $("#fas-" + art_id).toggleClass("none");
}

// SEARCH (by ARTICLE_DISPLAY / ARTICLE_SEARCH)
function artSearch(input_name) {
    let art = $("#" + input_name).val();

    art = art.replace(/\s+/g, '');
    art = art.replace(/\.+/g, '');
    art = art.replace(/\-+/g, '');
    art = art.replace(/\//g, '');

    if (art === "" || art === undefined) {
        showNotify("{error_cap}:", "{input_art_first}!", "danger");
        $("#" + input_name).focus();
    } else {
        JsHttpRequest.query(folder,{'w':'getCatalogueLink', 'article_nr_search':art},
            function (result, errors){ if (errors) {alert(errors);} if (result){
                location.href = result.content;
            }}, true);
    }
}

function selectRegion(id) {
    JsHttpRequest.query(folder,{'w':'setTpoint', 'id':id},
        function (result, errors){ if (errors) {alert(errors);} if (result){
            location.reload(true);
        }}, true);
}

function shortSearchList() {
    let art_id = $("#art_id").val();

    JsHttpRequest.query(folder,{'w':'shortSearchList', 'art_id':art_id},
        function (result, errors){ if (errors) {alert(errors);} if (result){
            $("#analogs_form").html(result.content);
            new LazyLoad({ elements_selector: ".lazy" });
        }}, true);
}

function shortArticleOE() {
    let art_id = $("#art_id").val();

    JsHttpRequest.query(folder,{'w':'getOriginalNumbers', 'art_id':art_id},
        function (result, errors){ if (errors) {alert(errors);} if (result){
            $("#oe_form").html(result.content);
        }}, true);
}

function shortArticleApplicable() {
    let art_id = $("#art_id").val();

    JsHttpRequest.query(folder,{'w':'getArticleApplicableForm', 'art_id':art_id},
        function (result, errors){ if (errors) {alert(errors);} if (result){
            $("#applicable_form").html(result.content);
        }}, true);
}

// Modal `Brands`
function showBrandForm(brand) {
    JsHttpRequest.query(folder,{'w':'showBrandForm', 'brand':brand},
        function (result, errors){ if (errors) {alert(errors);} if (result){
            $("#info_brand").html(result.content);
        }}, true);
    $("#BrandForm").modal("show");
}

// Modal `Information`
function showInfoForm(art_id) {
    JsHttpRequest.query(folder,{'w':'showInfoForm', 'art_id':art_id},
        function (result, errors){ if (errors) {alert(errors);} if (result){
            $("#info_content").html(result.content[0]);
            $("#info_title").html(result.content[1]);
            $("#InfoForm").modal("toggle");
        }}, true);
}

// Modal `Photo Gallery`
function showPhotoGallery(ref) {
    JsHttpRequest.query(folder,{'w':'showPhotoGallery', 'ref':ref},
        function (result, errors){ if (errors) {alert(errors);} if (result){
            $("#photo_gallery").html(result.content);
            $("#PhotoForm").modal("toggle");
        }}, true);
}

// SEARCH CATALOG FILTER
function catalogueFilter() {
    let art     = $("#art_value").val();
    let brand   = $("#brand_value").val();

    $(".check-brand").each(function () {
        if ($(this).hasClass("main-brand") === false) {
            $(this).attr("disabled", true);
        }
    });

    let brands = [];
    $("input[type=checkbox]").each(function () {
        if (this.checked) {
            brands.push($(this).attr("name"));
        }
    });
    let bb      = JSON.stringify(brands);
    let price   = $("#filter-price").val();
    let deliv   = $("#filter-delivery").val();

    JsHttpRequest.query(folder,{'w':'getCatalogListFilter', 'art':art, 'brand':brand, 'bb':bb, 'price':price, 'deliv':deliv},
        function (result, errors){ if (errors) {alert(errors);} if (result){

            $("#cat_search_main").html(result.content[0]);
            $("#cat_search_filters").html(result.content[1]);
            $("#cat_search_brands").html(result.content[2]);

            loadInputNumber();

            var input_price = $("#filter-price"), input_delivery = $("#filter-delivery");
            input_price.slider();
            input_price.on("slide", function(e) {
                let evalue = e.value;
                $("#price_val").text(evalue);
                $("#price_val_min").val(evalue[0]);
                $("#price_val_max").val(evalue[1]);
            });
            input_delivery.slider();
            input_delivery.on("slide", function(e) {
                let evalue = e.value;
                $("#dd_val").text(evalue);
                $("#dd_val_min").val(evalue[0]);
                $("#dd_val_max").val(evalue[1]);
            });

            $(".js-example-basic-single").select2();

            let max_price   = parseInt(result.content[3]);
            let value       = input_price.data("slider").getValue();
            let value_dd    = input_delivery.data("slider").getValue();

            $("#filter-max-price").html(max_price);
            input_price.attr("data-slider-max", max_price);
            input_price.slider("setAttribute", "max", max_price);
            input_price.slider("refresh");

            if (value[1] > max_price) {
                value[1] = max_price;
            }
            let max_min = value[0] + "," + value[1];
            input_price.attr("data-slider-value", max_min);
            $("#price_val").html(max_min);
            $("#price_val_min").val(value[0]);
            $("#price_val_max").val(value[1]);

            let max_min_dd = value_dd[0] + "," + value_dd[1];
            input_delivery.attr("data-slider-value", max_min_dd);
            $("#dd_val").html(max_min_dd);
            $("#dd_val_min").val(value_dd[0]);
            $("#dd_val_max").val(value_dd[1]);

            input_price.slider("setValue", value);

            $(".check-brand").each(function () {
                if ($(this).hasClass("main-brand") === false) {
                    $(this).removeAttr("disabled");
                }
            });
            new LazyLoad({ elements_selector: ".lazy" });

            $(".tooltips").tooltip();
            navigateTo("result_target");

        }}, true);
}

function getArticleApplModelForm(art_id, mfa_id, a) {
    $(".info__applicability-checked").each(function () {
        $(this).removeClass("span-red");
    });
    $(a).addClass("span-red");
    JsHttpRequest.query(folder,{ 'w': 'getArticleApplModelForm', 'art_id':art_id, 'mfa_id':mfa_id},
        function (result, errors){ if (errors) {} if (result){
            $("#info2_more").html(result.content);
            $("#info3_more").html(result.content);
        }}, true);
}

function getArticleApplModelInfoForm(art_id, typ_id) {
    let er = 0;
    if (document.getElementById("AMI" + typ_id).innerHTML === "") {
        JsHttpRequest.query(folder,{ 'w': 'getArticleApplModelInfoForm', 'art_id':art_id, 'typ_id':typ_id},
            function (result, errors){ if (errors) {} if (result){
                document.getElementById("AMI" + typ_id).innerHTML = result.content;
            }}, true);
        er = 1;
    }
    if (document.getElementById("AMI" + typ_id).innerHTML !== "" && er === 0) {
        $("#AMI" + typ_id).html("");
    }
}

// COPY ARTICLE BUTTON
function copyToClipboard(element, art_name) {
    let $temp = $("<input>");
    $("body").append($temp);
    $temp.val($("#" + element).next().val()).select();
    document.execCommand("copy");
    $temp.remove();
    showNotify("{done_cap}:", "{art_cap} '" + art_name + "' {copied_to_clipboard}!", "success");
}

/*
* finish add car to garage
* */
function finishGarage(typ_id, group_link) {
    setCookie('auto_typ_id', typ_id);
    //addToGarage(typ_id);
    addToGarageHistory(typ_id);
    location.href = group_link;
}

function addToGarageHistory(sel_typ_id = 0) {
    JsHttpRequest.query(folder,{'w':'addGarageHistory', 'sel_typ_id':sel_typ_id},
        function (result, errors){ if (errors) {alert(errors);} if (result){
            console.log(result.content);
        }}, true);
}

// ADD NEW CAR TO GARAGE
function addToGarage(typ_id = 0, a) {
    if (typ_id === 0) {
        typ_id = $("#typ_id").val();
    }
    if (typ_id !== undefined && typ_id !== 0 && typ_id !== "") {
        JsHttpRequest.query(folder,{'w':'addToGarage', 'typ_id':typ_id},
            function (result, errors){ if (errors) {alert(errors);} if (result) {
                // if (result.content !== false) {
                    if (result.content[0] === false) {
                        showNotify("{error_cap}:", "{garage_auto_exist}", "danger");
                    } else {
                        showNotify("{done_cap}:", result.content[1], "success");
                        $(a).addClass("btn-img-disabled");
                        showGarageStatus();
                    }
                // } else {
                //     showNotify("{error_cap}:", "{garage_is_full}", "danger");
                // }
            }}, true);
    } else {
        showNotify("{error_cap}:", "{select_all_fields}!", "danger");
    }
}

// DELETE CAR FROM GARAGE
function deleteAutoGarage(auto_id) {
    JsHttpRequest.query(folder,{'w':'deleteAutoGarage', 'auto_id':auto_id},
        function (result, errors){ if (errors) {alert(errors);} if (result){
            showGarageForm();
            showGarageStatus();
        }}, true);
}

// UPDATE GARAGE STATUS
function showGarageStatus() {
    let status1 = $("#garage_status");
    JsHttpRequest.query(folder,{'w':'getGarageAutoCount'},
        function (result, errors){ if (errors) {alert(errors);} if (result){
            if (result.content[0] !== "") {
                status1.addClass("show").removeClass("none").html(result.content);
            } else {
                status1.addClass("none").removeClass("show");
            }
        }}, true);
}

// SHOW GARAGE HISTORY FORM
function showAutoHistory() {
    let div_id = $("#car_form-history");
    if (div_id.is(':visible')) {
        div_id.hide("slow");
    } else {
        JsHttpRequest.query(folder,{'w':'showAutoHistory'},
            function (result, errors){ if (errors) {alert(errors);} if (result){
                div_id.html(result.content);
                div_id.show("slow");
            }}, true);
    }
}

// DROP GARAGE HISTORY FORM
function dropAutoHistory(history_id) {
    JsHttpRequest.query(folder,{'w':'dropAutoHistory', 'history_id':history_id},
        function (result, errors){ if (errors) {alert(errors);} if (result){
            showAutoHistory();
        }}, true);
}

function changeBasketCount(status, id) {
    let input_id = $("#" + id);
    let count = parseInt(input_id.val());
    if (status > 0) {
        count = count + 1;
        input_id.val(count);
    } else {
        if (count > 1) {
            count = count - 1;
            input_id.val(count);
        }
    }
}

function changeActionCount(i, action_price, action_amount) {
    let true_amount = $("#count_" + i).val();
    let true_price  = $("#true_price_" + i).val();
    let true_kours  = $("#true_kours_" + i).val();
    let price       = $("#price_" + i);

    if (parseInt(true_amount) >= parseInt(action_amount)) {
        price.text(action_price + " " + true_kours);
        price.prepend("<span id='price_out_" + i + "' class='span-outline'>" + true_price + " " + true_kours + "</span><br>");
    } else {
        price.text(true_price + " " + true_kours);
        $("#price_out_" + i).remove();
    }
}

// TOGGLE VIEW (CARD / TABLE)
// function toggleProductView(ds) {
//     JsHttpRequest.query(folder,{ 'w': 'toggleProductView', 'ds':ds},
//         function (result, errors){ if (errors) {} if (result){
//             let type_search = $("#type_search").val();
//             if (type_search === "1") {
//                 catalogueFilter();
//             }
//         }}, true);
// }

function setCatalogFilters() {
    let cat_filters = $("#catalog-filters");
    let filters     = cat_filters.html();

    if (filters != "") {
        $("#menu-catalog-content").html(filters);
        cat_filters.html("");
    }
}

function setClientRequest() {
    let phone   = $("#help-phone").val(); if ($("#help-phone").length === 0) phone = "";
    let vin     = $("#help-vin").val(); if ($("#help-vin").length === 0) vin = "";
    let text    = $("#help-text").val(); if ($("#help-text").length === 0) text = "";
    let status  = 0;

    JsHttpRequest.query(folder,{'w':'setClientRequest', 'phone':phone, 'vin':vin, 'text':text, 'status':status},
        function (result, errors) { if (errors) {alert(errors);} if (result) {
            if (result["answer"] === false) {
                showNotify("{error_cap}:", result["err"], "danger");
            } else {
                showNotify("{done_cap}:", "{manager_call}!", "success");
                $("#btn_set_client_request").attr("disabled", true);
            }
        }}, true);
}

function setClientRequestFaq() {
    let phone   = $("#faq-phone").val(); if ($("#faq-phone").length === 0) phone = "";
    let vin     = $("#faq-vin").val(); if ($("#faq-vin").length === 0) vin = "";
    let text    = $("#faq-text").val(); if ($("#faq-text").length === 0) text = "";
    let status  = 0;

    JsHttpRequest.query(folder,{'w':'setClientRequest', 'phone':phone, 'vin':vin, 'text':text, 'status':status},
        function (result, errors) { if (errors) {alert(errors);} if (result) {
            if (result["answer"] === false) {
                showNotify("{error_cap}:", result["err"], "danger");
            } else {
                showNotify("{done_cap}:", "{manager_call}!", "success");
                $("#btn_set_client_request_faq").attr("disabled", true);
            }
        }}, true);
}

function setClientRequestCard() {
    let phone   = $("#help-phone-2").val(); if ($("#help-phone-2").length === 0) phone = "";
    let vin     = $("#help-vin-2").val(); if ($("#help-vin-2").length === 0) vin = "";
    let text    = $("#help-text-2").val(); if ($("#help-text-2").length === 0) text = "";
    let status  = 1;

    JsHttpRequest.query(folder,{'w':'setClientRequest', 'phone':phone, 'vin':vin, 'text':text, 'status':status},
        function (result, errors){ if (errors) {alert(errors);} if (result) {
            if (result["answer"] === false) {
                console.log(result["err"]);
                showNotify("{error_cap}:", result["err"], "danger");
            } else {
                showNotify("{done_cap}:", "{manager_call}!", "success");
                setClientRequestDone();
                $("#btn_set_client_request_card").attr("disabled", true);
            }
        }}, true);
}

function setClientRequest2() {
    let phone   = $("#req-phone").val(); if ($("#req-phone").length === 0) phone = "";
    let vin     = $("#req-vin").val(); if ($("#req-vin").length === 0) vin = "";
    let text    = $("#req-text").val(); if ($("#req-text").length === 0) text = "";

    JsHttpRequest.query(folder,{'w':'setClientRequest', 'phone':phone, 'vin':vin, 'text':text},
        function (result, errors){ if (errors) {alert(errors);} if (result) {
            if (result["answer"] === false) {
                showNotify("{error_cap}:", result["err"], "danger");
            } else {
                showNotify("{done_cap}:", "{manager_call}!", "success");
                $("#btn_set_client_request2").attr("disabled", true);
            }
        }}, true);
}

function setClientRequest3() {
    let phone   = $("#req-phone-seo").val(); if ($("#req-phone-seo").length === 0) phone = "";
    let vin     = $("#req-vin-seo").val(); if ($("#req-vin-seo").length === 0) vin = "";
    let text    = $("#req-text-seo").val(); if ($("#req-text-seo").length === 0) text = "";

    JsHttpRequest.query(folder,{'w':'setClientRequest', 'phone':phone, 'vin':vin, 'text':text},
        function (result, errors){ if (errors) {alert(errors);} if (result) {
            if (result["answer"] === false) {
                showNotify("{error_cap}:", result["err"], "danger");
            } else {
                showNotify("{done_cap}:", "{manager_call}!", "success");
                $("#btn_set_client_request3").attr("disabled", true);
            }
        }}, true);
}

function setClientRequestDone() {
    $("#request-card").html("");

    JsHttpRequest.query(folder,{'w':'setClientRequestDone'},
        function (result, errors){ if (errors) {alert(errors);} if (result){
            $("#request-card").html(result.content);
        }}, true);
}

function updateBasketCount(status) {
    getBasketId();

    let basket_id = parseInt($("#basket_id").val());

    JsHttpRequest.query(folder,{'w':'updateBasketCount', 'basket_id':basket_id, 'status':status},
        function (result, errors){ if (errors) {alert(errors);} if (result){
            let answer = result["answer"];
            let err = result["err"];
            let new_amount = result["new_amount"];
            if (err == 1) {
                showNotify("{error_cap}!", answer, "danger");
                $("#count_1").val(new_amount);
            }
            if (err == 2) {
                $("#basket_id").val(0);
                $(".buy-form__button").removeClass("buy-form__button-hidden");
                $(".buy-form__input").addClass("buy-form__input-hidden");
            }
        }}, true);
}

function moveBasketButton() {
    $(".btn-buy").parent(".buy-form__button").toggleClass("buy-form__button-hidden").next(".buy-form__input").toggleClass("buy-form__input-hidden");

    let id          = "one";
    let art_id      = $("#art_id").val();
    let brand_id    = $("#brand_id").val();
    let stock       = $("#stock").val();
    let storage_id  = $("#storage_id").val();
    let suppl_id    = $("#suppl_id").val();

    moveBasket(id, art_id, brand_id, stock, storage_id, suppl_id);
    getBasketId();
}

function getBasketId() {
    let art_id      = $("#art_id").val();
    let storage_id  = $("#storage_id").val();
    let suppl_id    = $("#suppl_id").val();

    JsHttpRequest.query(folder,{'w':'getBasketId', 'art_id':art_id, 'suppl_id':suppl_id, 'storage_id':storage_id},
        function (result, errors){ if (errors) {alert(errors);} if (result){
            let basket_id = result.content;
            $("#basket_id").val(basket_id);
        }}, true);
}

function updateBasketCountChange() {
    getBasketId();

    let basket_id   = parseInt($("#basket_id").val());
    let amount      = $("#count_1").val();

    JsHttpRequest.query(folder,{'w':'updateBasketCountChange', 'basket_id':basket_id, 'amount':amount},
        function (result, errors){ if (errors) {alert(errors);} if (result){
            let answer = result["answer"];
            let err = result["err"];
            let new_amount = result["new_amount"];
            if (err == 1) {
                showNotify("{error_cap}!", answer, "danger");
                $("#count_1").val(new_amount);
            }
            if (err == 2) {
                $("#basket_id").val(0);
                $(".buy-form__button").removeClass("buy-form__button-hidden");
                $(".buy-form__input").addClass("buy-form__input-hidden");
            }
        }}, true);
}

function showSearchDropdown() {
    let text = $("#search_input").val();

    JsHttpRequest.query(folder,{'w':'showSearchDropdown', 'text':text},
        function (result, errors){ if (errors) {alert(errors);} if (result){
            $("#search_input_dropdown").html(result.content);
        }}, true);
}

function showSearchDropdown2() {
    let text_input = $("#search-mobile").val();

    JsHttpRequest.query(folder,{'w':'showSearchDropdown2', 'text_input':text_input},
        function (result, errors){ if (errors) {alert(errors);} if (result){
            $("#search-dropdown").html(result.content);
        }}, true);
}

function getPartsSortForm(link) {
    let redirect_link = "";
    let sort_id = $("#cat-products-sort option:selected").val();

    if (sort_id == "0") {
        redirect_link = link;
    }
    if (sort_id == "1") {
        redirect_link = link + "?sort=asc";
    }
    if (sort_id == "2") {
        redirect_link = link + "?sort=desc";
    }
    location.href = redirect_link;
}

function appendCatalog() {
    let cur_page = parseInt($("#cur_page").val());
    let max_page = parseInt($("#max_page").val());

    if (cur_page < max_page) {
        let new_page = cur_page + 1;
        $("#cur_page").val(new_page);
        if (new_page === max_page) {
            $("#append_btn").hide();
        }
        $(".cat-products-list").last().append("<div class='cat-products-list'>CONTENT HERE => PAGE " + new_page + "</div>");
    } else {
        $("#append_btn").hide();
    }
}