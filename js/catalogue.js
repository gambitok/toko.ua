
function showRegionForm() {
    let form="region";
    JsHttpRequest.query(folder,{'w':'showModalForm', 'form':form},
        function (result, errors){ if (errors) {alert(errors);} if (result){
            $("#modals").append(result.content);
            $("#RegionForm").modal("show");
            $("#menu").css('left','-100%');
        }}, true);
}

function showActionForm() {
    let form="action";
    JsHttpRequest.query(folder,{'w':'showModalForm', 'form':form},
        function (result, errors){ if (errors) {alert(errors);} if (result){
            $("#modals").append(result.content);
            $("#ActionForm").modal("show");
        }}, true);
}

function catalogueFilterClear() { "use strict"; location.reload(true); }

function showArtSearch() { "use strict";
    $("#PhoneArticle").modal("show");
    JsHttpRequest.query(folder,{'w':'showHistoryList'},
        function (result, errors){ if (errors) {alert(errors);} if (result){
            $("#modal-phone__history").html(result.content);
        }}, true);
}

// CATALOG BRAND SEARCH
function searchBrandInput() {
    var input, filter, ul, li, a, i, txtValue;
    input = document.getElementById("brandSearchInput");
    filter = input.value.toUpperCase();
    ul = document.getElementById("brandSearchList");
    li = ul.getElementsByTagName("li");
    for (i = 0; i < li.length; i++) {
        a = li[i].getElementsByTagName("a")[0];
        txtValue = a.textContent || a.innerText;
        if (txtValue.toUpperCase().indexOf(filter) > -1) {
            li[i].style.display = "";
        } else {
            li[i].style.display = "none";
        }
    }
}

function showSearchParameters() {
    let str_id=$("#details_str_id").val();
    let page=$("#details_page").val();
    let active_filters=$("#details_active_filters").val();
    let type=$("#details_str_type").val();
    $("#details_pagination").html("<div class=\"spinner-border\"></div>");
    JsHttpRequest.query(folder,{'w':'showSearchParameters', 'str_id':str_id, 'page':page, 'active_filters':active_filters, 'type':type},
        function (result, errors){ if (errors) {alert(errors);} if (result){
            $("#details_count").html(result.content[0]);
            $("#details_pagination").html(result.content[1]);
        }}, true);
}

function navigateTo(id) {
    $([document.documentElement, document.body]).animate({
        scrollTop: $("#"+id).offset().top
    }, 500);
}

function toggleListParams(a, param_id) { "use strict";
    $("#param-"+param_id).toggleClass("list-hide");
    $(a).find("span").each(function() {
        if ($(this).attr("class")==="show") {
            $(this).addClass("none");
            $(this).removeClass("show");
            return true;
        }
        if ($(this).attr("class")==="none") {
            $(this).addClass("show");
            $(this).removeClass("none");
            return true;
        }
    });
}

function showStorage(art_id) { "use strict";
    $("."+art_id+"-hide").toggleClass("none");
    $("#fa-"+art_id).toggleClass("none");
    $("#fas-"+art_id).toggleClass("none");
}

function artSearch(input_name) { "use strict";
    let art = $("#"+input_name).val();
    art=art.replace(/\s+/g,'');
    art=art.replace(/\.+/g,'');
    art=art.replace(/\-+/g,'');
    art=art.replace(/\//g,'');
    if (art==="" || art===undefined) {
        showNotify("{error_cap}:","{input_art_first}!","danger");
        $("#search_art").focus();
    } else {
        JsHttpRequest.query(folder,{'w':'getCatalogueLink', 'article_nr_search':art},
            function (result, errors){ if (errors) {alert(errors);} if (result){
                location.href = result.content;
            }}, true);
    }
}

// function selectModel() { "use strict";
//     let auto = $("#select_auto option:selected").val();
//     $("#select_model_descr").empty();
//     $("#select_number_descr").empty();
//     $("#select_group_descr").empty();
//     $("#select_model").empty(); $("#select_modelid").empty(); $("#select_group").empty();
//     JsHttpRequest.query(folder,{'w':'select_model', 'auto':auto},
//         function (result, errors){ if (errors) {alert(errors);} if (result){
//             let select_model=$("#select_model");
//             select_model.html(result.content);
//             select_model.select2("open");
//         }}, true);
// }
//
// function selectModelID() { "use strict";
//     let model = $("#select_model option:selected").val();
//     $("#select_number_descr").empty();
//     $("#select_group_descr").empty();
//     $("#select_modelid").empty(); $("#select_group").empty();
//     JsHttpRequest.query(folder,{'w':'select_modelid', 'model':model},
//         function (result, errors){ if (errors) {alert(errors);} if (result){
//             let select_modelid=$("#select_modelid");
//             select_modelid.html(result.content);
//             select_modelid.select2("open");
//         }}, true);
// }
//
// function selectGroup() { "use strict";
//     let modelid = $("#select_modelid option:selected").val();
//     $("#select_group").empty();
//     $("#select_group_descr").empty();
//     JsHttpRequest.query(folder,{'w':'select_group', 'modelid':modelid},
//         function (result, errors){ if (errors) {alert(errors);} if (result){
//             let select_group=$("#select_group");
//             select_group.html(result.content);
//             select_group.select2("open");
//         }}, true);
// }
//
// function selectEnd() { "use strict";
//     let group = $("#select_group option:selected").val();
//     JsHttpRequest.query(folder,{'w':'select_end', 'group':group},
//         function (result, errors){ if (errors) {alert(errors);} if (result){
//             $("#select_group_descr").html(result.content);
//         }}, true);
// }

function selectRegion(id) { "use strict";
    JsHttpRequest.query(folder,{'w':'setTpoint', 'id':id},
        function (result, errors){ if (errors) {alert(errors);} if (result){
            let res = result.content;
            selectRegionText(res);
            $("#RegionForm").modal("hide");
            location.reload(true);
        }}, true);
}

function checkCookieTpoint() { "use strict";
    let tpoint_id = getCookie("tpoint_id");
    if (tpoint_id==="") showRegionForm();
    let action_status = getCookie("action_status");
    let user = getCookie("user");
    JsHttpRequest.query(folder,{'w':'checkActionClients'},
        function (result, errors){ if (errors) {alert(errors);} if (result){
            if (action_status==="" && user!=="" && result.content>0) {
                setCookie("action_status","1");
                showActionForm();
            }
        }}, true);
    return true;
}

function selectRegionText(id) { "use strict";
    JsHttpRequest.query(folder,{'w':'getRegionSelect', 'id':id},
        function (result, errors){ if (errors) {alert(errors);} if (result){
            $("#region_select_tpoint").html(result.content);
        }}, true);
}

function showBrandForm(brand) { "use strict";
    JsHttpRequest.query(folder,{'w':'showBrandForm', 'brand':brand},
        function (result, errors){ if (errors) {alert(errors);} if (result){
            $("#info_brand").html(result.content);
        }}, true);
    $("#BrandForm").modal("show");
}

// function tecSearch() { "use strict";
//     let auto = $("#select_auto option:selected").val();
//     let model = $("#select_model option:selected").val();
//     let modelid = $("#select_modelid option:selected").val();
//     let group = $("#select_group option:selected").val();
//     if ((auto==="0")||(model==="0")||(modelid==="0")||(group==="0")||(auto===undefined)||(model===undefined)||(modelid===undefined)||(group===undefined)) {
//         showNotify("{error_cap}:","{select_all_fields}!","danger");
//     } else {
//         JsHttpRequest.query(folder,{'w':'getCatalogueLink'},
//             function (result, errors){ if (errors) {alert(errors);} if (result){
//                 location.href = result.content + "findmodel/"+auto+"/"+model+"/"+modelid+"/"+group+"/";
//             }}, true);
//     }
// }

// function tecSearchFindDetail() { "use strict";
//     let auto = $("#select_auto option:selected").val();
//     let model = $("#select_model option:selected").val();
//     let modelid = $("#select_modelid option:selected").val();
//     let group = $("#select_group option:selected").val();
//     let str_id = $("#str_id").val();
//     let str_level = $("#str_level").val();
//     let str_id_parrent = $("#str_id_parrent").val();
//     if ((auto==="0")||(model==="0")||(modelid==="0")||(group==="0")||(auto===undefined)||(model===undefined)||(modelid===undefined)||(group===undefined)) {
//         showNotify("{error_cap}:","{select_all_fields}!","danger");
//     } else {
//         JsHttpRequest.query(folder,{'w':'getCatalogueLink'},
//             function (result, errors){ if (errors) {alert(errors);} if (result){
//                 location.href = result.content + "findmodel/"+auto+"/"+model+"/"+modelid+"/"+group+"/"+str_id+"/"+str_level+"/"+str_id_parrent+"/";
//             }}, true);
//     }
// }

function showInfoForm(art_id) { "use strict";
    JsHttpRequest.query(folder,{'w':'showInfoForm', 'art_id':art_id},
        function (result, errors){ if (errors) {alert(errors);} if (result){
            $("#info_content").html(result.content[0]);
            $("#info_title").html(result.content[1]);
            $("#InfoForm").modal("toggle");
            new LazyLoad({ elements_selector: ".lazy" });
        }}, true);
}

function showPhotoGallery(ref) { "use strict";
    JsHttpRequest.query(folder,{'w':'showPhotoForm', 'ref':ref},
        function (result, errors){ if (errors) {alert(errors);} if (result){
            $("#photo_gallery").html(result.content);
            $("#PhotoForm").modal("toggle");
            new LazyLoad({ elements_selector: ".lazy" });
        }}, true);
}

function catalogueFilter(order) { "use strict";
    let art = $("#art_value").val();
    let brand = $("#brand_value").val();
    let text = $("#text_filter").val();

    $(".check-brand").each(function () { if ($(this).hasClass("main-brand")===false) $(this).attr("disabled", true); });
    let brands = [];
    $("input[type=checkbox]").each(function () { if (this.checked) {brands.push($(this).attr("name"));} });
    let bb = JSON.stringify(brands);

    let cur = parseInt($(".radio-group input[name=cur]:checked").attr("value")); if (isNaN(cur)) cur=1;
    let cur_old = parseInt($("#cur_value").val());
    let price = $("#ex1").val();
    let deliv = $("#ex3").val();

    JsHttpRequest.query(folder,{'w':'show_catalogue_filter_all', 'art':art, 'brand':brand, 'bb':bb, 'text':text, 'cur':cur, 'price':price, 'deliv':deliv, 'order':order},
        function (result, errors){ if (errors) {alert(errors);} if (result){

            $("#cat_search_main").html(result.content[0]);
            $("#cat_search_filters").html(result.content[1]);
            $("#cat_search_brands").html(result.content[2]);
            $("#text_filter").val(result.content[4]);

            loadInputNumber();

            var ex1=$("#ex1"), ex3=$("#ex3");
            ex1.slider();
            ex1.on("slide", function(slideEvt) { $("#price_val").text(slideEvt.value); });
            ex3.slider();
            ex3.on("slide", function(slideEvt) { $("#dd_val").text(slideEvt.value); });

            $(".js-example-basic-single").select2();

            var max_price = parseInt(result.content[3]);
            var value = ex1.data("slider").getValue();

            $("#filter-max-price").html(max_price);
            ex1.attr("data-slider-max", max_price);
            ex1.slider("setAttribute", "max", max_price);
            ex1.slider("refresh");

            if (value[1]>max_price) value[1]=max_price;
            var max_min=value[0]+","+value[1];
            ex1.attr("data-slider-value",max_min);
            $("#price_val").html(max_min);

            if (cur!==cur_old) {
                value[1]=max_price;
                value[0]=0;
                ex1.slider("refresh");
                $("#filter-max-price").html(max_price);
                ex1.attr("data-slider-max",max_price);
                ex1.slider("setAttribute", "max", max_price);
                max_min=value[0]+","+value[1];
                ex1.attr("data-slider-value",max_min);
                $("#price_val").html(max_min);
                $("#cur_value").val(cur);
                ex1.slider("setValue", value);
                catalogueFilter(order);
            }

            ex1.slider("setValue", value);
            $("#cur_value").val(cur);

            $(".check-brand").each(function () { if ($(this).hasClass("main-brand")===false) $(this).removeAttr("disabled"); });
            new LazyLoad({ elements_selector: ".lazy" });

            $(".tooltips").tooltip();
            navigateTo("result_target");

        }}, true);
}

function tecModelsFilter(order) {

    let art = $("#art_value").val();
    let brand = $("#brand_value").val();
    let text = $("#text_filter").val();

    let brands = [];
    $("input[type=checkbox]").each(function () { if (this.checked) {brands.push($(this).attr("name"));} });
    let bb = JSON.stringify(brands);

    let cur = parseInt($(".radio-group input[name=cur]:checked").attr("value")); if (isNaN(cur)) cur=1;
    let cur_old = parseInt($("#cur_value").val());
    let price = $("#ex1").val();
    let deliv = $("#ex3").val();

    JsHttpRequest.query(folder,{'w':'show_model_filter_all', 'art':art, 'brand':brand, 'bb':bb, 'text':text, 'cur':cur, 'price':price, 'deliv':deliv, 'order':order},
        function (result, errors){ if (errors) {alert(errors);} if (result){

            $("#cat_search_main").html(result.content[0]);
            $("#cat_search_filters").html(result.content[1]);
            $("#cat_search_brands").html(result.content[2]);
            $("#text_filter").val(result.content[4]);

            loadInputNumber();

            var ex1=$("#ex1"), ex3=$("#ex3");
            ex1.slider();
            ex1.on("slide", function(slideEvt) { $("#price_val").text(slideEvt.value); });
            ex3.slider();
            ex3.on("slide", function(slideEvt) { $("#dd_val").text(slideEvt.value); });

            $(".js-example-basic-single").select2();

            var max_price = parseInt(result.content[3]);
            var value = ex1.data("slider").getValue();

            $("#filter-max-price").html(max_price);
            ex1.attr("data-slider-max",max_price);
            ex1.slider("setAttribute", "max", max_price);
            ex1.slider("refresh");

            if (value[1]>max_price) value[1]=max_price;
            var max_min=value[0]+","+value[1];
            ex1.attr("data-slider-value",max_min);
            $("#price_val").html(max_min);

            if (cur!==cur_old) {
                value[1]=max_price;
                value[0]=0;
                ex1.slider("refresh");
                $("#filter-max-price").html(max_price);
                ex1.attr("data-slider-max",max_price);
                ex1.slider("setAttribute", "max", max_price);
                max_min=value[0]+","+value[1];
                ex1.attr("data-slider-value",max_min);
                $("#price_val").html(max_min);
                $("#cur_value").val(cur);
                ex1.slider("setValue", value);
                tecModelsFilter(order);
            }

            ex1.slider("setValue", value);
            $("#cur_value").val(cur);
            $(".tooltips").tooltip();
            new LazyLoad({ elements_selector: ".lazy" });

            navigateTo("result_target");

        }}, true);
}

// function tecModelsTreeStr(manuf, mod, modid, gr, str, lvl, par, a) { "use strict";
//     $(".details_class").each(function () {$(this).removeClass("detail-red");}); $(a).addClass("detail-red");
//
//     JsHttpRequest.query(folder,{'w':'tecModelsTreeStr', 'manuf':manuf, 'mod':mod, 'modid':modid, 'gr':gr, 'str':str, 'lvl':lvl, 'par':par},
//         function (result, errors){ if (errors) {alert(errors);} if (result){
//
//             $("#cat_search_main").html(result.content[0]);
//             $("#cat_search_filters").html(result.content[1]);
//             $("#cat_search_brands").html(result.content[2]);
//
//             window.history.pushState("catalogue", "Product", "/catalogue/findmodel/"+manuf+"/"+mod+"/"+modid+"/"+gr+"/"+str+"/"+lvl+"/"+par+"/"+result.content[4]+"/");
//
//             document.title = result.content[3];
//
//             loadInputNumber();
//
//             var ex1=$("#ex1"), ex3=$("#ex3");
//             ex1.slider();
//             ex1.on("slide", function(slideEvt) { $("#price_val").text(slideEvt.value); });
//             ex3.slider();
//             ex3.on("slide", function(slideEvt) { $("#dd_val").text(slideEvt.value); });
//
//             $(".js-example-basic-single").select2();
//             $(".tooltips").tooltip();
//             navigateTo("result_target");
//         }}, true);
// }

// function tecModelsFilterClear() { "use strict";
//     location.reload(true);
// }

function loadApplicModels2(art_id_tcd, manufacture, a) { "use strict";
    $(".load_app").each(function () {$(this).removeClass("span-red");});
    $(a).addClass("span-red");
    JsHttpRequest.query(folder,{ 'w': 'loadApplicModels2', 'art_id_tcd':art_id_tcd, 'manufacture':manufacture},
        function (result, errors){ if (errors) {} if (result){
            $("#info2_more").html(result.content);
            $("#info3_more").html(result.content);
        }}, true);
}

function loadApplicModelsInfo2(art_id, typ_id){ "use strict";
    let er=0;
    if (document.getElementById("AMI"+typ_id).innerHTML===""){
        JsHttpRequest.query(folder,{ 'w': 'loadApplicModelsInfo2', 'art_id':art_id, 'typ_id':typ_id},
            function (result, errors){ if (errors) {} if (result){
                document.getElementById("AMI"+typ_id).innerHTML=result.content;
            }}, true);er=1;
    }
    if (document.getElementById("AMI"+typ_id).innerHTML!=="" && er===0){ $("#AMI"+typ_id).html(""); }
}

// function checkTypeAnalog(n) { "use strict";
//     let art_id=document.getElementById(n).getAttribute("name");
//     let art_dspl=document.getElementById(n).innerHTML;
//     let brand_id=document.getElementById("brand"+n).getAttribute("name");
//     JsHttpRequest.query(folder,{ 'w': 'check_type_analog', 'art_id':art_id, 'brand_id':brand_id, 'art_dspl':art_dspl},
//         function (result, errors){ if (errors) {alert(errors);} if (result){
//             if (result.content>0) $("#analog"+n).css("color","#4b4e4f");
//         }}, true);
// }

// function checkAnalog() { "use strict";
//     var max=0;
//     var element=document.getElementsByClassName("artclass");
//     if (typeof(element)!=="undefined" && element!=null && document.getElementsByClassName("artclass").length!==0) {
//         var str=document.getElementsByClassName("artclass")[0].getAttribute("id");
//         var res=str.replace("art", "");
//         res=parseInt(res);
//     }
//     $(".artclass").each(function() {
//         max=Math.max(this.id, max);
//     });
//     for (var i=res; i<=max; i++) {
//         if (document.getElementById(i)!=null) checkTypeAnalog(i);
//     }
// }

function copyToClipboard(element,art_name) { "use strict";
    let $temp = $("<input>");
    $("body").append($temp);
    $temp.val($("#"+element).next().val()).select();
    document.execCommand("copy");
    $temp.remove();
    showNotify("{done_cap}:","{art_cap} '"+art_name+"' {copied_to_clipboard}!","success");
}

/*==== Garage ========================================================================================================*/

// ADD NEW CAR TO GARAGE
function addToGarage(typ_id=0) {
    if (typ_id===0) typ_id=$("#typ_id").val();
    if (typ_id!==undefined && typ_id!==0 && typ_id!=="") {
        JsHttpRequest.query(folder,{'w':'addToGarage', 'typ_id':typ_id},
            function (result, errors){ if (errors) {alert(errors);} if (result){
                if (result.content!==false) {
                    if (result.content===true) {
                        showNotify("{error_cap}:","{garage_auto_exist}","danger");
                    } else {
                        showNotify("{done_cap}:",result.content,"success");
                        showGarageStatus();
                        updateGarageForm();
                    }
                } else {
                    showNotify("{error_cap}:","{garage_is_full}","danger");
                }
            }}, true);
    } else {
        showNotify("{error_cap}:","{select_all_fields}!","danger");
    }
}

// UPDATE SELECTED CAR FORM
function updateGarageForm() {
    $("#car_content").html();
    JsHttpRequest.query(folder,{'w':'showCarsSelectedForm'},
        function (result, errors){ if (errors) {alert(errors);} if (result){
            $("#car_content").html(result.content);
        }}, true);
}

// DELETE CAR FROM GARAGE
function deleteAutoGarage(auto_id) {
    JsHttpRequest.query(folder,{'w':'deleteAutoGarage', 'auto_id':auto_id},
        function (result, errors){ if (errors) {alert(errors);} if (result){
            showAutoGarage();
            showGarageStatus();
            if (result.content===false) {
                if (getCookie("auto_typ_id")==="") {
                    showCarsSelectMin(1);
                } else {
                }
            } else {
                updateGarageForm();
            }
        }}, true);
}

// UPDATE GARAGE MODAL
function updateChosenAutoGarage(auto_id) {
    JsHttpRequest.query(folder,{'w':'updateChosenAutoGarage', 'auto_id':auto_id},
        function (result, errors){ if (errors) {alert(errors);} if (result){
            showAutoGarage();
            updateGarageForm();
        }}, true);
}

// UPDATE GARAGE STATUS
function showGarageStatus() {
    let status1=$("#garage_status");
    JsHttpRequest.query(folder,{'w':'updateGarageStatus'},
        function (result, errors){ if (errors) {alert(errors);} if (result){
            if (result.content[0]!=="") {
                status1.addClass("show"); status1.removeClass("none"); status1.html(result.content[0]);
            } else {
                status1.addClass("none"); status1.removeClass("show");
            }
        }}, true);
}

// SHOW GARAGE MODAL
function showAutoGarage() { "use strict";
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

/*==== /Garage =====*/

function changeBasketCount(status, id) {
    let input_id=$("#"+id);
    let count=parseInt(input_id.val());
    if (status>0) {
        count=count+1;
        input_id.val(count);
    } else {
        if (count>1) {
            count=count-1;
            input_id.val(count);
        }
    }
}

function changeActionCount(i, action_price, action_amount) {
    let true_amount = $("#count_"+i).val();
    let true_price = $("#true_price_"+i).val();
    let true_kours = $("#true_kours_"+i).val();
    let price = $("#price_"+i);
    if (parseInt(true_amount)>=parseInt(action_amount)) {
        price.text(action_price+" "+true_kours);
        price.prepend("<span id='price_out_"+i+"' class='span-outline'>"+true_price+" "+true_kours+"</span><br>");
    } else {
        price.text(true_price+" "+true_kours);
        $("#price_out_"+i).remove();
    }
}

function showManufactureDetails(head_id, str_id_str) {
    let request_link = window.location.href;
    let class_name = $("#manufacture_head"+head_id).attr("class");
    if (class_name==="tree-list dnone") {
        JsHttpRequest.query(folder,{ 'w': 'showManufactureDetails', 'head_id':head_id, 'request_link':request_link, 'str_id_str':str_id_str},
            function (result, errors){ if (errors) {} if (result){
                $("#tree_head-"+head_id).toggleClass("check-head");
                let manuf_head = $("#manufacture_head"+head_id);
                manuf_head.removeClass("dnone");
                manuf_head.addClass("dblock");
                manuf_head.html(result.content);
            }}, true);
    } else {
        $("#tree_head-"+head_id).toggleClass("check-head");
        let manuf_head = $("#manufacture_head"+head_id);
        manuf_head.removeClass("dblock");
        manuf_head.addClass("dnone");
        manuf_head.html("");
    }
}

/*==== NEW STR CAR DETAILS ====*/
function showCarDetailsStr(head_id) {
    let str_id_str=$("#tree_str_ids").val();
    let class_name=$("#manufacture_head"+head_id).attr("class");
    if (class_name==="tree-list dnone") {
        JsHttpRequest.query(folder,{ 'w': 'showCarDetailsStr', 'head_id':head_id, 'str_id_str':str_id_str},
            function (result, errors){ if (errors) {} if (result){
                $("#tree_head-"+head_id).toggleClass("check-head");
                let manuf_head = $("#manufacture_head"+head_id);
                manuf_head.removeClass("dnone");
                manuf_head.addClass("dblock");
                manuf_head.html(result.content);
            }}, true);
    } else {
        $("#tree_head-"+head_id).toggleClass("check-head");
        let manuf_head = $("#manufacture_head"+head_id);
        manuf_head.removeClass("dblock");
        manuf_head.addClass("dnone");
        manuf_head.html("");
    }
}

function showCarDetailsStrMin(head_id) {
    let typ_id=$("#typ_id").val();
    let str_id_str=$("#tree_str_ids").val();
    let class_name=$("#manufacture_head"+head_id).attr("class");
    if (class_name==="tree-list_min dnone") {
        JsHttpRequest.query(folder,{ 'w': 'showCarDetailsStr', 'head_id':head_id, 'str_id_str':str_id_str, 'typ_id':typ_id},
            function (result, errors){ if (errors) {} if (result){
                $("#tree_head-"+head_id).toggleClass("check-head");
                let manuf_head = $("#manufacture_head"+head_id);
                manuf_head.removeClass("dnone");
                manuf_head.addClass("dblock");
                manuf_head.html(result.content);
            }}, true);
    } else {
        $("#tree_head-"+head_id).toggleClass("check-head");
        let manuf_head = $("#manufacture_head"+head_id);
        manuf_head.removeClass("dblock");
        manuf_head.addClass("dnone");
        manuf_head.html("");
    }
}

/*==== /NEW STR CAR DETAILS ====*/

function triggerDetailCar(type_id, value_id) {
    $("#toggle_manuf_list").show();

    let str_id = $("#str_id").val();
    let str_level = $("#str_level").val();
    let str_id_parrent = $("#str_id_parrent").val();

    let s_yr = $("#select_year");
    let s_mf = $("#select_manufacture");
    let s_md = $("#select_model");
    let s_mi = $("#select_model_id");
    let s_gr = $("#select_group");

    let r_yr = $("#str_year");
    let r_mf = $("#str_manufacture");
    let r_md = $("#str_model");
    let r_mi = $("#str_model_id");
    let r_gr = $("#str_group");

    let r_ti = $("#str_type");

    $(".btn-select").each(function () {$(this).removeClass("btn-active");});

    if (r_ti.val()!=="0") {
        if (r_yr.val()==="") {
            type_id=parseInt(r_ti.val());
            r_yr.val(value_id);
            switch (type_id) {
                case 1: {value_id=parseInt(r_yr.val());break;}
                case 2: {value_id=parseInt(r_mf.val());break;}
                case 3: {value_id=r_md.val();break;}
                case 4: {value_id=parseInt(r_mi.val());break;}
            }
        }
        if (r_yr.val()==="all") {
            type_id=0;
            r_yr.val("");
        }
    }

    if (value_id===undefined) {
        switch (type_id) {
            case 0: {s_yr.addClass("btn-active");break;}
            case 1: {s_mf.addClass("btn-active");break;}
            case 2: {s_md.addClass("btn-active");break;}
            case 3: {s_mi.addClass("btn-active");break;}
            case 4: {s_gr.addClass("btn-active");break;}
        }
    } else {
        switch (type_id) {
            case 0: {s_yr.addClass("btn-active");break;}
            case 1: {r_yr.val(value_id);s_mf.addClass("btn-active");break;}
            case 2: {r_mf.val(value_id);s_md.addClass("btn-active");break;}
            case 3: {r_md.val(value_id);s_mi.addClass("btn-active");break;}
            case 4: {r_mi.val(value_id);s_gr.addClass("btn-active");break;}
        }
    }

    if (value_id!==undefined) {
        switch (type_id) {
            case 0: {s_mf.attr("disabled", 'disabled');s_md.attr("disabled", 'disabled');s_mi.attr("disabled", 'disabled');s_gr.attr("disabled", 'disabled');break;}
            case 1: {s_mf.removeAttr("disabled");s_md.attr("disabled", 'disabled');s_mi.attr("disabled", 'disabled');s_gr.attr("disabled", 'disabled');break;}
            case 2: {s_mf.removeAttr("disabled");s_md.removeAttr("disabled");s_mi.attr("disabled", 'disabled');s_gr.attr("disabled", 'disabled');break;}
            case 3: {s_mf.removeAttr("disabled");s_md.removeAttr("disabled");s_mi.removeAttr("disabled");s_gr.attr("disabled", 'disabled');break;}
            case 4: {s_mf.removeAttr("disabled");s_md.removeAttr("disabled");s_mi.removeAttr("disabled");s_gr.removeAttr("disabled");break;}
        }
    } else {
        switch (type_id) {
            case 0: {s_yr.addClass("btn-active");s_mf.removeAttr("disabled");break;}
            case 1: {s_mf.addClass("btn-active");s_mf.removeAttr("disabled");break;}
            case 2: {s_md.addClass("btn-active");s_mf.removeAttr("disabled");s_md.removeAttr("disabled");break;}
            case 3: {s_mi.addClass("btn-active");s_mf.removeAttr("disabled");s_md.removeAttr("disabled");s_mi.removeAttr("disabled");break;}
            case 4: {s_gr.addClass("btn-active");s_mf.removeAttr("disabled");s_md.removeAttr("disabled");s_mi.removeAttr("disabled");s_gr.removeAttr("disabled");break;}
        }
    }

    let year = r_yr.val();
    let manufacture = r_mf.val();
    let model = r_md.val();
    let model_id = r_mi.val();
    let group = r_gr.val();

    switch (type_id) {
        case 1: {manufacture="";model="";model_id="";group="";break;}
        case 2: {model="";model_id="";group="";break;}
        case 3: {model_id="";group="";break;}
        case 4: {group="";break;}
    }

    JsHttpRequest.query(folder,{'w':'triggerDetailCar', 'type_id':type_id, 'value_id':value_id, 'year':year, 'manufacture':manufacture, 'model':model, 'model_id':model_id, 'group':group, 'str_id':str_id},
        function (result, errors){ if (errors) {alert(errors);} if (result){

            $("#select_form_car").html(result.content);
            $("#toggle_manuf_list").html(result.content);
            $("#str_str_text").val(result.header);
            $("#breadcrumb_auto").text(result.format);

            if (result.skip_id>0) {
                model_id=result.skip_id;
                if (model_id>0) {
                    r_mi.val(model_id);
                    s_mi.removeClass("btn-active");
                    s_gr.addClass("btn-active");
                }
            }

            if (value_id!==undefined) {
                let car_code="/";
                if (year!=="") car_code+=year+"/";
                if (year==="") car_code+="all/";
                if (manufacture!=="") car_code+=manufacture+"/";
                if (model!=="") car_code+=model+"/";
                if (model_id!=="") car_code+=model_id+"/";
                if (group!=="") car_code+=group+"/";

                if (str_id==="") {
                    window.history.pushState("catalogue", "Auto", "/catalogue/findmodel/"+car_code+"/"+result.header+"/");
                    document.title = result.title;
                } else {
                    window.history.pushState("catalogue", "Auto", "/catalogue/finddetail/"+result.header+""+"/"+str_id+"/"+str_level+"/"+str_id_parrent+car_code);
                    document.title = result.title;
                }
            }

        }}, true);
}

function showTabCatalogueAuto() {
    JsHttpRequest.query(folder,{ 'w': 'showTabCatalogueAuto'},
        function (result, errors){ if (errors) {} if (result){
            $("#cat_tab_search").html(result.content);
            let link = $("#link_auto"); link.removeClass("disabled"); link.trigger("click");
            $("#link_model").addClass("disabled");
            $("#link_modelid").addClass("disabled");
            $("#link_group").addClass("disabled");
            $(".bootstrap-switch-label").text($(".bootstrap-switch-handle-off").text());
        }}, true);
}

function showCarsSelectLink() {
    let str_text = $("#str_text_select").val();
    location.href = "https://toko.ua/details/"+str_text+"/";
}

function showCarsSelectMin(param_id, value_id=0, fuel_id=0) {
    let mfa=$("#mfa_select").val();
    let model=$("#model_select").val();
    let year=$("#year_select").val();
    let modelid=$("#modelid_select").val();
    let typ_id=$("#typ_id_select").val();
    let fuel_id_selected=$("#fuel_id_select").val();
    let str_id=$("#str_text_select").val();

    if (param_id==1) {mfa=""; model=""; year=""; modelid=""; typ_id="";}

    if (param_id==2 && value_id>0)   {mfa=value_id; model="";}
    if (param_id==2 && value_id!="") {mfa=value_id; model="";}
    if (param_id==2 && value_id==0)  {model="";}

    if (param_id==3 && value_id>0)   {model=value_id; year="";}
    if (param_id==3 && value_id!="") {model=value_id; year="";}
    if (param_id==3 && value_id==0)  {year="";}

    if (param_id==4 && value_id>0)   {year=value_id; modelid="";}
    if (param_id==4 && value_id!="") {year=value_id; modelid="";}
    if (param_id==4 && value_id==0)  {modelid="";}

    if (param_id==5 && value_id>0)   {modelid=value_id;typ_id="";}
    if (param_id==5 && value_id==0)  {typ_id="";$(".car_form-select_card").toggle();}

    if (param_id==6 && value_id>0)   {typ_id=value_id;}
    if (param_id==6 && value_id!="") {typ_id=value_id;}
    if (param_id==6 && value_id==0)  {fuel_id=fuel_id_selected;}

    console.log("typ_id: "+typ_id+"; fuel_id: "+fuel_id);

    JsHttpRequest.query(folder,{ 'w': 'showCarsSelectMin', 'str_id':str_id, 'mfa':mfa, 'model':model, 'year':year, 'modelid':modelid, 'typ_id':typ_id, 'fuel_id':fuel_id},
        function (result, errors){ if (errors) {} if (result){
            $("#car_content").html(result.content);
            navigateTo("catalogue");
        }}, true);
}

function showCarsSelected(param_id, value_id=0) {
    let mfa=$("#mfa_select").val();
    let model=$("#model_select").val();
    let year=$("#year_select").val();
    let modelid=$("#modelid_select").val();
    let typ_id=$("#typ_id_select").val();

    if (param_id==1) {mfa=""; model=""; year=""; modelid=""; typ_id="";}

    if (param_id==2 && value_id>0)   {mfa=value_id; model="";}
    if (param_id==2 && value_id!="") {mfa=value_id; model="";}
    if (param_id==2 && value_id==0)  {model="";}

    if (param_id==3 && value_id>0)   {model=value_id; year="";}
    if (param_id==3 && value_id!="") {model=value_id; year="";}
    if (param_id==3 && value_id==0)  {year="";}

    if (param_id==4 && value_id>0)   {year=value_id; modelid="";}
    if (param_id==4 && value_id!="") {year=value_id; modelid="";}
    if (param_id==4 && value_id==0)  {modelid="";}

    if (param_id==5 && value_id>0)   {modelid=value_id;typ_id="";}
    if (param_id==5 && value_id==0)  {typ_id="";$(".car_form-select_card").toggle();}

    if (param_id==6 && value_id>0)   {typ_id=value_id;}

    JsHttpRequest.query(folder,{ 'w': 'showCarsSelected', 'mfa':mfa, 'model':model, 'year':year, 'modelid':modelid, 'typ_id':typ_id},
        function (result, errors){ if (errors) {} if (result){
            $("#car_content").html(result.content);
        }}, true);
}

function techCarModelsFilter() {
    let typ_id=$("#search_typ_id").val();
    let str_id=$("#search_str_id").val();

    JsHttpRequest.query(folder,{ 'w': 'techCarModelsFilter', 'typ_id':typ_id, 'str_id':str_id},
        function (result, errors){ if (errors) {} if (result){

            $("#search_new_tree").html(result.content[0]); $("#search_new_tree").fadeIn(3000);
            $("#search_tree").html(result.content[1]); $("#search_tree").fadeIn(3000);
            $("#search_filters").html(result.content[2]); $("#search_filters").fadeIn(3000);
            $("#search_brands").html(result.content[3]); $("#search_brands").fadeIn(3000);

            new treefilter($("#my-tree"), { searcher : $("input#my-search") });

            var ex1=$("#ex1"), ex3=$("#ex3");
            if (ex1.length) {
                ex1.slider();
                ex1.on("slide", function(slideEvt) {
                    $("#price_val").text(slideEvt.value);
                });
            }
            if (ex3.length) {
                ex3.slider();
                ex3.on("slide", function (slideEvt) {
                    $("#dd_val").text(slideEvt.value);
                });
            }

        }}, true);
}

function techCarModels(typ_id, str_id) {

    showCarsSelectMin(6, typ_id);
    $(".car_form-select_card").toggle();

    JsHttpRequest.query(folder,{ 'w': 'techCarModels', 'typ_id':typ_id, 'str_id':str_id},
        function (result, errors){ if (errors) {} if (result){

            $("#catalogue-main").html(result.content);

            new treefilter($("#my-tree"), { searcher : $("input#my-search") });

            var ex1=$("#ex1"), ex3=$("#ex3");
            if (ex1.length) {
                ex1.slider();
                ex1.on("slide", function(slideEvt) {
                    $("#price_val").text(slideEvt.value);
                });
            }
            if (ex3.length) {
                ex3.slider();
                ex3.on("slide", function (slideEvt) {
                    $("#dd_val").text(slideEvt.value);
                });
            }

            $(".tooltips").tooltip();
            new LazyLoad({ elements_selector: ".lazy" });

        }}, true);
}

function toggleProductView(ds) {
    JsHttpRequest.query(folder,{ 'w': 'toggleProductView', 'ds':ds},
        function (result, errors){ if (errors) {} if (result){
            let type_search=$("#type_search").val();
            if (type_search==="1") catalogueFilter();
            if (type_search==="2") tecModelsFilter();
        }}, true);
}

function showFiltersForm() {
    $("#template_filters").html("<div class=\"spinner-border\"></div>");

    let template_id = $("#template_id").val();
    let active_filters = JSON.parse($("#template_active_filters").val());

    JsHttpRequest.query(folder,{'w':'showFiltersForm', 'template_id':template_id, 'active_filters':active_filters},
        function (result, errors){ if (errors) {alert(errors);} if (result){
            $("#template_filters").html(result.content[0]);
        }}, true);
}

function showFilterOptionsForm(page) {
    $("#template_pagination").html("<div class=\"spinner-border\"></div>");
    $("#template_count").html();
    $("#template_checked").html();

    let template_id = $("#template_id").val();
    let active_filters = JSON.parse($("#template_active_filters").val());

    JsHttpRequest.query(folder,{'w':'showFilterOptionsForm', 'template_id':template_id, 'page':page, 'active_filters':active_filters},
        function (result, errors){ if (errors) {alert(errors);} if (result){
            $("#template_count").html(result.content[0]);
            $("#template_pagination").html(result.content[1]);
            $("#template_checked").html(result.content[2]);
        }}, true);
}

function toggleAutoBlock(block,slide) { "use strict";
    $("#"+slide).slideToggle("slow");
    $("."+block).find("i").toggleClass("icon-rotate");
    if ($("#year_select").val()==="") { $("#select_year").addClass("car_form-selected"); console.log('toggle year');}
    else if ($("#modelid_select").val()==="") { $("#select_modelid").addClass("car_form-selected"); console.log('toggle modelid');}
    else if ($("#typ_id_select").val()==="") { $("#select_typid").addClass("car_form-selected"); console.log('toggle typid');}
    else if (($("#typ_id_select").val()!=="" && $("#fuel_id_select").val()==="")) { $("#select_modification").addClass("car_form-selected"); console.log('toggle fuelid');}
}