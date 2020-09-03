// function showLoader() {
    // $("#LoaderForm").modal("show");
    // $(".modal-backdrop").css("background-color","white");
// }

// function hideLoader() {
    // $("#LoaderForm").modal("hide");
    // $(".modal-backdrop").css("background-color","black");
// }

// DROPZONE FILE UPLOAD
function showUploadForm() {
    let myDropzone = new Dropzone("#myDropzone",{ dictDefaultMessage: "Press to choose file!" });
    myDropzone.removeAllFiles(true);
    myDropzone.on("queuecomplete", function() {
        this.removeAllFiles();
        JsHttpRequest.query(folder,{ 'w': 'getSellerImage'},
            function (result, errors){ if (errors) {} if (result){
                $("#upload_image").text(result.content);
            }}, true);
        $("#filePhotoUploadForm").modal("hide");
        $("#upload_btn").removeClass("required_input");
    });
}

// ALERT MODAL
function showAlertModal(message, title, status, callback) {
    JsHttpRequest.query(folder,{ 'w': 'changeLangAlert', 'message':message, 'title':title },
        function (result, errors){ if (errors) {} if (result){
            $("#choose_message").html("<span>"+result.content[0]+"</span>");
            if (result.content[1]==="" || result.content[1]===undefined) $("#choose_title").html("");
            else $("#choose_title").html("<h4>"+result.content[1]+"</h4>");
            if (status===0) $("#alert-modal-header").addClass("bg-danger");
            if (status===1) $("#alert-modal-header").addClass("bg-success");
            if (status===2) $("#alert-modal-header").addClass("bg-info");
            if (typeof callback!=="undefined") { $('#alert_btn_ok').click(function(){callback();}); }
            $("#AlertForm").modal("toggle");
        }}, true);
}

// function validate(evt) {
//     var theEvent = evt || window.event;
//     if (theEvent.type === "paste") {
//         key = event.clipboardData.getData("text/plain");
//     } else {
//         var key = theEvent.keyCode || theEvent.which;
//         key = String.fromCharCode(key);
//     }
//     var regex = /[0-9]|\./;
//     if (!regex.test(key)) {
//         theEvent.returnValue = false;
//         if(theEvent.preventDefault) theEvent.preventDefault();
//     }
// }

// REGISTRATION VALIDATE
function showValidateModal(phone, callback, callback2) {
    JsHttpRequest.query(folder,{ 'w': 'validatePhone', 'phone':phone},
        function (result, errors){ if (errors) {} if (result){
            $("#ValidateForm").modal("show");
            $("#validate_btn_ok").click(function(){callback(callback2);});
        }}, true);
}

function validatePhone(callback) {
    let phone = $("#reg_phone").val();
    if (phone===undefined) phone = $("#input_phone2").val();
    let password = $("#validate_code").val();
    JsHttpRequest.query(folder,{ 'w': 'endValidation', 'phone':phone, 'password':password},
        function (result, errors){ if (errors) {} if (result){
            if (result.content===true) {
                callback();
                $("#ValidateForm").modal("hide");
            } else {
                $("#validate_label").css("display","block");
            }
        }}, true);
}

function togglePass(a) {
    if($(a).attr("checked") !== "checked") {
        $(a).attr("checked","checked");
        $("#reg_password").attr("type","password");
    } else {
        $(a).removeAttr("checked");
        $("#reg_password").attr("type","text");
    }
}

function showGarageForm() {
    $("#GarageForm").modal("show");
    $("#garage_block").html("");
    JsHttpRequest.query(folder,{ 'w': 'showGarageForm'},
        function (result, errors){ if (errors) {} if (result){
            $("#garage_block").html(result.content);
        }}, true);
    if ($("#car_content").length==0) {
        JsHttpRequest.query(folder,{'w':'showCarsForm2'},
            function (result, errors){ if (errors) {alert(errors);} if (result){
                $("#garage_404_select").html(result.content[0]);
                if (result.content[1]==1) {
                    toggleCarsNavigation($("div[data-type='manuf']"));
                }
            }}, true);
    }
}

function dropHistoryShow() {
    let myDropDown = $("#myDropdown");
    myDropDown.show();
    if (myDropDown.html()==="") {
        JsHttpRequest.query(folder,{ 'w': 'showHistoryList'},
            function (result, errors){ if (errors) {} if (result){
                myDropDown.html(result.content);
            }}, true);
    }
}

function dropHistoryHide() {
    $("#myDropdown").hide();
}

function deleteHistoryItem(history_id) {
    JsHttpRequest.query(folder,{ 'w': 'deleteHistoryItem', 'history_id':history_id},
        function (result, errors){ if (errors) {} if (result){
            dropHistoryHide();
            $("#myDropdown").html("");
            if (detectmob()) showArtSearch();
        }}, true);
}

function toggleBlock(block, slide) {
    $("#" + slide).slideToggle("slow");
    $("." + block).find("i").toggleClass("icon-rotate");
}

function toggleForm(slide) {
    $("#" + slide).slideToggle("slow");
}

function rotateIcon(a) {
    $(a).find("i").toggleClass("rotate__icon");
}

// function triggerTabAuto(year) {
//     $(".year-list").each(function () {$(this).removeClass("span-red");});
//     $("#year-"+year).addClass("span-red");
//     JsHttpRequest.query(folder,{ 'w': 'tab_auto', 'year':year },
//         function (result, errors){ if (errors) {} if (result){
//             let link = $("#link_auto"); link.removeClass("disabled"); link.trigger("click");
//             $("#link_model").addClass("disabled");
//             $("#link_modelid").addClass("disabled");
//             $("#link_group").addClass("disabled");
//             $("#tab_auto").html(result.content);
//             $([document.documentElement, document.body]).animate({
//                 scrollTop: $("#navigation").offset().top
//             }, 500);
//         }}, true);
//     return true;
// }
//
// function triggerTabModel(auto, year) {
//     $(".auto-list").each(function () {$(this).removeClass("span-red");});
//     $("#auto-"+auto).addClass("span-red");
//     JsHttpRequest.query(folder,{ 'w': 'tab_model', 'auto':auto, 'year':year },
//         function (result, errors){ if (errors) {} if (result){
//             let link = $("#link_model"); link.removeClass("disabled"); link.trigger("click");
//             $("#link_modelid").addClass("disabled");
//             $("#link_group").addClass("disabled");
//             $("#tab_model").html(result.content);
//             $([document.documentElement, document.body]).animate({
//                 scrollTop: $("#navigation").offset().top
//             }, 500);
//         }}, true);
//     return true;
// }
//
// function triggerTabModelId(model, auto, year) {
//     $(".model-list").each(function () {$(this).removeClass("span-red");});
//     $("#model-"+model).addClass("span-red");
//     JsHttpRequest.query(folder,{ 'w': 'tab_modelid', 'model':model, 'auto':auto, 'year':year },
//         function (result, errors){ if (errors) {} if (result){
//             let link = $("#link_modelid"); link.removeClass("disabled"); link.trigger("click");
//             $("#link_group").addClass("disabled");
//             $("#tab_modelid").html(result.content);
//             $([document.documentElement, document.body]).animate({
//                 scrollTop: $("#navigation").offset().top
//             }, 500);
//         }}, true);
//     return true;
// }
//
// function triggerTabGroup(modelid ,model, auto, year) {
//     $(".modelid-list").each(function () {$(this).removeClass("span-red");});
//     $("#modelid-"+modelid).addClass("span-red");
//     JsHttpRequest.query(folder,{ 'w': 'tab_group', 'modelid':modelid, 'model':model, 'auto':auto, 'year':year },
//         function (result, errors){ if (errors) {} if (result){
//             let link = $("#link_group"); link.removeClass("disabled"); link.trigger("click");
//             $("#tab_group").html(result.content);
//             $([document.documentElement, document.body]).animate({
//                 scrollTop: $("#navigation").offset().top
//             }, 500);
//         }}, true);
//     return true;
// }

/*==== Load Catalogue page ====*/
// function triggerTabModel2(auto,model,modelid) {
//     window.history.pushState("catalogue", "Auto", "/catalogue/auto/"+auto+"/");
//     $(".auto-list").each(function () {$(this).removeClass("span-red");});
//     $("#auto-"+auto).addClass("span-red");
//     JsHttpRequest.query(folder,{ 'w': 'tab_model', 'auto':auto },
//         function (result, errors){ if (errors) {} if (result){
//             let link = $("#link_model"); link.removeClass("disabled"); link.trigger("click");
//             $("#link_modelid").addClass("disabled");
//             $("#link_group").addClass("disabled");
//             $("#tab_model").html(result.content);
//             if (model!==undefined && model!=="") {triggerTabModelId2(auto,model,modelid);}
//         }}, true);
//     return true;
// }
//
// function triggerTabModelId2(auto,model,modelid) {
//     window.history.pushState("catalogue", "Auto", "/catalogue/auto/"+auto+"/"+model+"/");
//     $(".model-list").each(function () {$(this).removeClass("span-red");});
//     $("#model-"+model).addClass("span-red");
//     JsHttpRequest.query(folder,{ 'w': 'tab_modelid', 'model':model, 'auto':auto },
//         function (result, errors){ if (errors) {} if (result){
//             let link = $("#link_modelid"); link.removeClass("disabled"); link.trigger("click");
//             $("#link_group").addClass("disabled");
//             $("#tab_modelid").html(result.content);
//             if (modelid!==undefined && modelid!=="") {setTimeout(triggerTabGroup2(auto,model,modelid),2000);}
//         }}, true);
//     return true;
// }
//
// function triggerTabGroup2(auto,model,modelid) {
//     window.history.pushState("catalogue", "Auto", "/catalogue/auto/"+auto+"/"+model+"/"+modelid+"/");
//     $(".modelid-list").each(function () {$(this).removeClass("span-red");});
//     $("#modelid-"+modelid).addClass("span-red");
//     JsHttpRequest.query(folder,{ 'w': 'tab_group', 'modelid':modelid, 'model':model, 'auto':auto },
//         function (result, errors){ if (errors) {} if (result){
//             let link = $("#link_group"); link.removeClass("disabled"); link.trigger("click");
//             $("#tab_group").html(result.content);
//         }}, true);
//     return true;
// }
//
// function triggerCatalogueTabs(auto,model,modelid) {
//     if (auto!==undefined && auto!=="") triggerTabModel2(auto,model,modelid);
// }

function saveSellForm() {
    let company_input=$("#reg_company"), company=company_input.val();
    let name_input=$("#reg_name"), name=name_input.val();
    let phone_input=$("#reg_phone"), phone=phone_input.val();
    let email_input=$("#reg_email"), email=email_input.val();
    let upload_file=$("#upload_image"), file_id=upload_file.text();
    let city_id=$("#user_city option:selected").val();
    let comment_input=$("#reg_comment"), comment=comment_input.val();

    if (company==="") company_input.addClass("required_input"); else company_input.removeClass("required_input");
    if (name==="") name_input.addClass("required_input"); else name_input.removeClass("required_input");
    if (phone==="") phone_input.addClass("required_input"); else phone_input.removeClass("required_input");
    if (city_id===undefined) $(".select2").addClass("required_input"); else $(".select2").removeClass("required_input");
    if (file_id==="" || file_id===undefined) $("#upload_btn").addClass("required_input"); else $("#upload_btn").removeClass("required_input");

    if ((company!=="") && (phone!=="") && (name!=="") && (city_id!==undefined)) {
        if (file_id==="" || file_id===undefined) { showAlertModal("{upload_file_first}!","{error_cap}",0); } else {
            JsHttpRequest.query(folder,{'w':'saveSellerForm', 'company':company, 'name':name, 'phone':phone, 'email':email, 'city_id':city_id, 'comment':comment},
                function (result, errors){ if (errors) {alert(errors);} if (result){
                    if (result.content!==true) {
                        showAlertModal("{bad_file}","{error_cap}",0);
                    } else {
                        showAlertModal("{message_sell}","{data_saved}",1,goHome);
                    }
                }}, true);
        }
    } else {
        showAlertModal("{input_all_data}!", "{error_cap}", 0);
    }
}

function showHideNavigation(head_id) {
    $("#navigation-hide").show();
    JsHttpRequest.query(folder,{'w':'showHeadTemplate', 'head_id':head_id},
        function (result, errors){ if (errors) {alert(errors);} if (result){
        $("#content-nav__content").html(result.content);
        // $("#content-nav__header").html(result.header);
        $("#content-nav__footer").html(result.footer);
        $(".header-nav__li").each(function() {
            $(this).removeClass("header-nav__li-active");
        });
        $("li[data-nav-id='" + head_id + "']").addClass("header-nav__li-active");
        $(".backdrop").addClass("backdrop-show");
    }}, true);
}

function closeHideNavigation() {
    $("#navigation-hide").hide();
    $(".header-nav__li").each(function () {
        $(this).removeClass("header-nav__li-active");
    });
    $(".backdrop").removeClass("backdrop-show");
}

function getSpecialOffersList() {
    let template_id = $("#special_offers_filter option:selected").val();
    let update_actions = $("#special_offers_update").val();
    JsHttpRequest.query(folder,{'w':'getSpecialOffersList', 'template_id':template_id, 'update_actions':update_actions},
        function (result, errors){ if (errors) {alert(errors);} if (result){
            $("#special_offers_range").html(result.content);
            $(".tooltips").tooltip();
        }}, true);
}

function showHomeCars() {
    JsHttpRequest.query(folder,{'w':'showHomeCars'},
        function (result, errors){ if (errors) {alert(errors);} if (result){
            $("#js-details").html(result.content);
        }}, true);
}

