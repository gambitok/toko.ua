
function navigateTo(id) {
    $([document.documentElement, document.body]).animate({
        scrollTop: $("#" + id).offset().top
    }, 500);
}

// Main Search
function showArtSearch() {
    $("#PhoneArticle").modal("show");
    JsHttpRequest.query(folder,{'w':'showHistoryList'},
        function (result, errors){ if (errors) {alert(errors);} if (result){
            $("#modal-phone__history").html(result.content);
        }}, true);
    setTimeout(function() {
        $("#search_art3").focus();
    }, 2000);
}

// Modal `Region`
function showRegionForm() {
    let form = "region";
    JsHttpRequest.query(folder,{'w':'showModalForm', 'form':form},
        function (result, errors){ if (errors) {alert(errors);} if (result){
            $("#modals").append(result.content);
            $("#RegionForm").modal("show");
            $("#menu").css('left', '-100%');
        }}, true);
}

// Modal `Help` in Catalogs
function showPhoneForm() {
    let form = "help";
    JsHttpRequest.query(folder,{'w':'showModalForm', 'form':form},
        function (result, errors){ if (errors) {alert(errors);} if (result){
            $("#modals").append(result.content);
            $("#HelpForm2").modal("show");
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
    art = art.replace(/\s+/g,'');
    art = art.replace(/\.+/g,'');
    art = art.replace(/\-+/g,'');
    art = art.replace(/\//g,'');
    if (art === "" || art === undefined) {
        showNotify("{error_cap}:","{input_art_first}!","danger");
        $("#search_art").focus();
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
            selectRegionText(result.content);
            $("#RegionForm").modal("hide");
            location.reload(true);
        }}, true);
}

function selectRegionText(id) {
    JsHttpRequest.query(folder,{'w':'getRegionSelect', 'id':id},
        function (result, errors){ if (errors) {alert(errors);} if (result){
            $("#region_select_tpoint").html(result.content);
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
    JsHttpRequest.query(folder,{'w':'showPhotoForm', 'ref':ref},
        function (result, errors){ if (errors) {alert(errors);} if (result){
            $("#photo_gallery").html(result.content);
            $("#PhotoForm").modal("toggle");
        }}, true);
}

// SEARCH CATALOG FILTER
function catalogueFilter(order) {
    let art = $("#art_value").val();
    let brand = $("#brand_value").val();

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
    let bb = JSON.stringify(brands);

    let cur = parseInt($(".radio-group input[name=cur]:checked").attr("value"));
    if (isNaN(cur)) {
        cur = 1;
    }
    let cur_old = parseInt($("#cur_value").val());
    let price = $("#filter-price").val();
    let deliv = $("#filter-delivery").val();

    JsHttpRequest.query(folder,{'w':'getCatalogListFilter', 'art':art, 'brand':brand, 'bb':bb, 'cur':cur, 'price':price, 'deliv':deliv, 'order':order},
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

            var max_price = parseInt(result.content[3]);
            var value = input_price.data("slider").getValue();
            var value_dd = input_delivery.data("slider").getValue();

            $("#filter-max-price").html(max_price);
            input_price.attr("data-slider-max", max_price);
            input_price.slider("setAttribute", "max", max_price);
            input_price.slider("refresh");

            if (value[1] > max_price) {
                value[1] = max_price;
            }
            var max_min = value[0] + "," + value[1];
            input_price.attr("data-slider-value", max_min);
            $("#price_val").html(max_min);
            $("#price_val_min").val(value[0]);
            $("#price_val_max").val(value[1]);

            var max_min_dd = value_dd[0] + "," + value_dd[1];
            input_delivery.attr("data-slider-value", max_min_dd);
            $("#dd_val").html(max_min_dd);
            $("#dd_val_min").val(value_dd[0]);
            $("#dd_val_max").val(value_dd[1]);

            if (cur !== cur_old) {
                value[1] = max_price;
                value[0] = 0;
                input_price.slider("refresh");
                $("#filter-max-price").html(max_price);
                input_price.attr("data-slider-max", max_price);
                input_price.slider("setAttribute", "max", max_price);
                max_min = value[0] + "," + value[1];
                input_price.attr("data-slider-value", max_min);
                $("#price_val").html(max_min);
                $("#price_val_min").val(value[0]);
                $("#price_val_max").val(value[1]);
                $("#cur_value").val(cur);
                input_price.slider("setValue", value);
                catalogueFilter(order);
            }

            input_price.slider("setValue", value);
            $("#cur_value").val(cur);

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
    addToGarage(typ_id);
    location.href = group_link;
}

// ADD NEW CAR TO GARAGE
function addToGarage(typ_id = 0) {
    if (typ_id === 0) {
        typ_id = $("#typ_id").val();
    }
    if (typ_id !== undefined && typ_id !== 0 && typ_id !== "") {
        JsHttpRequest.query(folder,{'w':'addToGarage', 'typ_id':typ_id},
            function (result, errors){ if (errors) {alert(errors);} if (result) {
                if (result.content !== false) {
                    if (result.content === true) {
                        showNotify("{error_cap}:", "{garage_auto_exist}", "danger");
                    } else {
                        showNotify("{done_cap}:", result.content, "success");
                        showGarageStatus();
                    }
                } else {
                    showNotify("{error_cap}:", "{garage_is_full}", "danger");
                }
            }}, true);
    } else {
        showNotify("{error_cap}:", "{select_all_fields}!", "danger");
    }
}

// DELETE CAR FROM GARAGE
function deleteAutoGarage(auto_id) {
    JsHttpRequest.query(folder,{'w':'deleteAutoGarage', 'auto_id':auto_id},
        function (result, errors){ if (errors) {alert(errors);} if (result){
            showAutoGarage();
            showGarageStatus();
        }}, true);
}

// UPDATE GARAGE MODAL
function updateChosenAutoGarage(auto_id) {
    JsHttpRequest.query(folder,{'w':'updateChosenAutoGarage', 'auto_id':auto_id},
        function (result, errors){ if (errors) {alert(errors);} if (result){
            location.reload();
        }}, true);
}

// UPDATE GARAGE STATUS
function showGarageStatus() {
    let status1 = $("#garage_status");
    JsHttpRequest.query(folder,{'w':'updateGarageStatus'},
        function (result, errors){ if (errors) {alert(errors);} if (result){
            if (result.content[0] !== "") {
                status1.addClass("show");
                status1.removeClass("none");
                status1.html(result.content);
            } else {
                status1.addClass("none");
                status1.removeClass("show");
            }
        }}, true);
}

// SHOW GARAGE MODAL
function showAutoGarage() {
    $("#garage_form_dropdown").html("<div class=\"loader\"></div>");
    JsHttpRequest.query(folder,{'w':'showAutoGarage'},
        function (result, errors){ if (errors) {alert(errors);} if (result){
            $("#garage_form_dropdown").html(result.content);
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

/*==== /GARAGE =====*/

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
    let true_price = $("#true_price_" + i).val();
    let true_kours = $("#true_kours_" + i).val();
    let price = $("#price_" + i);
    if (parseInt(true_amount) >= parseInt(action_amount)) {
        price.text(action_price + " " + true_kours);
        price.prepend("<span id='price_out_" + i + "' class='span-outline'>" + true_price + " " + true_kours + "</span><br>");
    } else {
        price.text(true_price + " " + true_kours);
        $("#price_out_" + i).remove();
    }
}

// TOGGLE VIEW (CARD / TABLE)
function toggleProductView(ds) {
    JsHttpRequest.query(folder,{ 'w': 'toggleProductView', 'ds':ds},
        function (result, errors){ if (errors) {} if (result){
            let type_search = $("#type_search").val();
            if (type_search === "1") {
                catalogueFilter();
            }
            // if (type_search === "2") {
            //     tecModelsFilter();
            // }
        }}, true);
}

function setCatalogFilters() {
    let cat_filters = $("#catalog-filters");
    let filters = cat_filters.html();
    if (filters != "") {
        $("#menu-catalog-content").html(filters);
        cat_filters.html("");
    }
}

function setClientRequest() {
    let phone = $("#help-phone").val(); if ($("#help-phone").length === 0) phone = "";
    let vin = $("#help-vin").val(); if ($("#help-vin").length === 0) vin = "";
    let text = $("#help-text").val(); if ($("#help-text").length === 0) text = "";
    let status = 0;
    JsHttpRequest.query(folder,{'w':'setClientRequest', 'phone':phone, 'vin':vin, 'text':text, 'status':status},
        function (result, errors) { if (errors) {alert(errors);} if (result) {
            if (result.content === false) {
                showNotify("{error_cap}:", "{phone_number_input}", "danger");
            } else {
                showNotify("{done_cap}:", "{manager_call}!", "success");
            }
        }}, true);
}

function setClientRequestFaq() {
    let phone = $("#faq-phone").val(); if ($("#faq-phone").length === 0) phone = "";
    let vin = $("#faq-vin").val(); if ($("#faq-vin").length === 0) vin = "";
    let text = $("#faq-text").val(); if ($("#faq-text").length === 0) text = "";
    let status = 0;
    JsHttpRequest.query(folder,{'w':'setClientRequest', 'phone':phone, 'vin':vin, 'text':text, 'status':status},
        function (result, errors) { if (errors) {alert(errors);} if (result) {
            if (result.content === false) {
                showNotify("{error_cap}:", "{phone_number_input}", "danger");
            } else {
                showNotify("{done_cap}:", "{manager_call}!", "success");
            }
        }}, true);
}

function setClientRequestCard() {
    let phone = $("#help-phone-2").val(); if ($("#help-phone-2").length === 0) phone = "";
    let vin = $("#help-vin-2").val(); if ($("#help-vin-2").length === 0) vin = "";
    let text = $("#help-text-2").val(); if ($("#help-text-2").length === 0) text = "";
    let status = 1;
    JsHttpRequest.query(folder,{'w':'setClientRequest', 'phone':phone, 'vin':vin, 'text':text, 'status':status},
        function (result, errors){ if (errors) {alert(errors);} if (result) {
            if (result.content === false) {
                showNotify("{error_cap}:", "{phone_number_input}", "danger");
            } else {
                showNotify("{done_cap}:", "{manager_call}!", "success");
                setClientRequestDone();
            }
        }}, true);
}

function setClientRequest2() {
    let phone = $("#req-phone").val(); if ($("#req-phone").length === 0) phone = "";
    let vin = $("#req-vin").val(); if ($("#req-vin").length === 0) vin = "";
    let text = $("#req-text").val(); if ($("#req-text").length === 0) text = "";
    JsHttpRequest.query(folder,{'w':'setClientRequest', 'phone':phone, 'vin':vin, 'text':text},
        function (result, errors){ if (errors) {alert(errors);} if (result) {
            if (result.content === false) {
                showNotify("{error_cap}:", "{input_all_data}", "danger");
            } else {
                showNotify("{done_cap}:", "{manager_call}!", "success");
            }
        }}, true);
}

function setClientRequest3() {
    let phone = $("#req-phone-seo").val(); if ($("#req-phone-seo").length === 0) phone = "";
    let vin = $("#req-vin-seo").val(); if ($("#req-vin-seo").length === 0) vin = "";
    let text = $("#req-text-seo").val(); if ($("#req-text-seo").length === 0) text = "";
    JsHttpRequest.query(folder,{'w':'setClientRequest', 'phone':phone, 'vin':vin, 'text':text},
        function (result, errors){ if (errors) {alert(errors);} if (result) {
            if (result.content === false) {
                showNotify("{error_cap}:", "{input_all_data}", "danger");
            } else {
                showNotify("{done_cap}:", "{manager_call}!", "success");
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

