$(document).ready(function() {
// !== 0 && $("#car_form-select").length !== 0
    if ($("#cars_form-selected").length) {

        $("body").scroll(function() {
            let checked_index = $(".cars-nav__item-checked")[0];
            let header = $("#catalogue-main");
            let top = header.offset().top;
            let sticky = top - 64;
            let catalogue_auto_form = $("#catalogue-auto");
            let toggle_active_nav = $("#toggle_active_nav");

            if (window.pageYOffset >= top) {
                $("#myBackdrop").addClass("sticky-backdrop-active");
                if (!catalogue_auto_form.hasClass("sticky")) {
                    $( "#myHeader" ).animate({width:'toggle'},1000);
                }
                catalogue_auto_form.addClass("sticky");

            } else {
                catalogue_auto_form.removeClass("sticky");
                $("#myBackdrop").removeClass("sticky-backdrop-active");
                $("#myHeader").hide();
            }
            if (toggle_active_nav.val() == 0) {
                if (window.pageYOffset >= sticky) {
                    hideCarsNavigation(checked_index);
                } else {
                    showCarsNavigation(checked_index);
                }
            } else {
                if (window.pageYOffset >= sticky) {
                    //
                } else {
                    toggle_active_nav.val(0);
                }
            }
        });

    }

    // hide on mobile
    if (detectmob()) {
        if ($("div[data-type='manuf']").attr("data-id") === "0") {
            toggleCarsNavigation($("div[data-type='manuf']"));
        }
    }
});

function toggleCarsTab(index) {
    $("#toggle_active_nav").val(1);

    let data = $(index).attr("data-url").split("/");
    let type = data[0];
    let attr = data[1];
    let elem = $("div[data-type='" + type + "']");
    let next = $("div[data-type='" + $(elem).attr('data-next') + "']");

    if ($(elem).attr("data-id") !== "0") {
        clearCarsBlock($(elem).attr("data-tab"));
    }

    $(elem).attr("data-id", attr);

    // Remove Disables
    $(next).removeClass("cars-nav__item-disabled");
    $(next).removeClass("cars-nav__item-hidden");

    // Hide Checked
    $(".cars-nav__item").each(function () {
        $(this).removeClass("cars-nav__item-checked");
    });
    $(next).addClass("cars-nav__item-checked");

    toggleCarsNavigation(next, type, attr);

    // Scroll on Mobile
    if (detectmob()) {
        document.getElementById("scrollManuf").scrollIntoView();
    }
}

function hideCarsNavigation(index) {
    // Tab Non-Disabled
    if (!$(index).hasClass("cars-nav__item-disabled")) {
        // Uncheck Non-Active Nav
        // Close Non-Active Tab
        $("#myBackdrop").addClass("sticky-backdrop-hidden");
        $(index).removeClass("cars-nav__item-active");
        $("#" + $(index).attr("data-tab")).removeClass("cars-tab__block-active");
    }
}

function showCarsNavigation(index, type, attr) {
    let data_pred = $("div[data-type='" + $(index).attr("data-pred") + "']");
    if (type === undefined) {
        type = $(data_pred).attr("data-type");
    }
    if (attr === undefined) {
        attr = $(data_pred).attr("data-id");
    }
    if (type === undefined && attr === undefined) {
        type = "";
        attr = 0;
    }
    // Tab Non-Disabled
    if (!$(index).hasClass("cars-nav__item-disabled")) {
        $("#myBackdrop").removeClass("sticky-backdrop-hidden");
        // Show Active Nav
        $(".cars-nav__item").each(function () {
            $(this).removeClass("cars-nav__item-active");
        });
        $(index).addClass("cars-nav__item-active");
        // Show Active Tab
        $(".cars-tab__block").each(function () {
            $(this).removeClass("cars-tab__block-active");
        });
        let index_tab = $("#" + $(index).attr("data-tab"));
        index_tab.addClass("cars-tab__block-active");
        getCarsSearchContent(type, attr);
    }
}

/*
* applicable_onclick
* */
function toggleNavMob() {
    document.getElementById("scrollManuf").scrollIntoView();
    let checked_index = $(".cars-nav__item-checked")[0];
    toggleCarsNavigation(checked_index);
}

function toggleCarsNavigation(index, type, attr) {

    $("#toggle_active_nav").val(1);

    let data_pred = $("div[data-type='" + $(index).attr("data-pred") + "']");
    if (type === undefined) {
        type = $(data_pred).attr("data-type");
    }
    if (attr === undefined) {
        attr = $(data_pred).attr("data-id");
    }
    if (type === undefined && attr === undefined) {
        type = "";
        attr = 0;
    }

    // Tab Non-Disabled
    if (!$(index).hasClass("cars-nav__item-disabled")) {
        // Uncheck Non-Active Nav
        // Close Non-Active Tab
        if ($(index).hasClass("cars-nav__item-active")) {
            $("#myBackdrop").addClass("sticky-backdrop-hidden");
            $(index).removeClass("cars-nav__item-active");
            $("#" + $(index).attr("data-tab")).removeClass("cars-tab__block-active");
        } else {
            $("#myBackdrop").removeClass("sticky-backdrop-hidden");
            // Show Active Nav
            $(".cars-nav__item").each(function () {
                $(this).removeClass("cars-nav__item-active");
            });
            $(index).addClass("cars-nav__item-active");
            // Show Active Tab
            $(".cars-tab__block").each(function () {
                $(this).removeClass("cars-tab__block-active");
            });
            let index_tab = $("#" + $(index).attr("data-tab"));
            index_tab.addClass("cars-tab__block-active");
            getCarsSearchContent(type, attr);
        }
    }
}

/*
* update car content
* */
function getCarsSearchContent(type, attr) {
    let group_id = $("#details_group_id").val();
    JsHttpRequest.query(folder,{'w':'getCarsSearchContent', 'type':type, 'attr':attr, 'group_id':group_id},
        function (result, errors){ if (errors) {alert(errors);} if (result) {
            let tab = $("#" + result.tab);
            tab.html(result.list);
            let nav = $("div[data-type='" + result.nav + "']");
            nav.html(result.title);
            if (result.skip > 0) {
                let index = $("div[data-url='bodyc/" + result.skip + "']")
                toggleCarsTab(index);
            }
        }}, true);
}

/*
* Clear tab styles
* */
function clearCarsBlock(data_tab) {
    let cur_tab = parseInt(data_tab.match(/\d+/));
    if (cur_tab !== undefined) {
        for (let i = 1; i <= 6; i++) {
            if (i > cur_tab) {
                let active_tab = $("div[data-tab='cars-tab" + i + "']");
                active_tab.removeClass("cars-nav__item-active");
                active_tab.removeClass("cars-nav__item-checked");
                JsHttpRequest.query(folder,{'w':'clearCarsBlock', 'sel_tab':i, 'cur_tab':cur_tab},
                    function (result, errors){ if (errors) {alert(errors);} if (result) {
                        // default Classes
                        active_tab.addClass(result.content[0]);
                        // default Texts
                        active_tab.html(result.content[1]);
                    }}, true);
            }
        }
    }
}

function setActiveCar() {
    let type = $("#active_nav").val();

    if (type !== "" && type !== undefined) {
        let elem = $("div[data-type='" + type + "']");

        // Remove HIDDEN
        $(elem).removeClass("cars-nav__item-disabled");

        // Remove ALL ACTIVE + CHECKED
        $(".cars-nav__item").each(function () {
            $(this).removeClass("cars-nav__item-active");
            $(this).removeClass("cars-nav__item-checked");
        });

        // Set ACTIVE + CHECKED
        $(elem).addClass("cars-nav__item-active cars-nav__item-checked");

        // Remove ALL ACTIVE Tab
        $(".cars-tab__block").each(function () {
            $(this).removeClass("cars-tab__block-active");
        });

        // Set ACTIVE + CHECKED Tab
        $("#" + $(elem).attr("data-tab")).addClass("cars-tab__block-active");
    }
}

/*
* get car chose form
* if car already chosen
* */
function showCarsForm() {
    let form = "cars";
    JsHttpRequest.query(folder,{'w':'showModalForm', 'form':form},
        function (result, errors){ if (errors) {alert(errors);} if (result){
            $("#modals").append(result.content);
            $("#CarsForm").modal("show");
        }}, true);
    JsHttpRequest.query(folder,{'w':'showCarsForm'},
        function (result, errors){ if (errors) {alert(errors);} if (result){
            $("#cars_block").html(result.content);
        }}, true);
}

/*
* get car chosen form or garage form
* */
function getCarsSelectUser(block, mfa_link = "", model_link = "", group_id = 0) {
    JsHttpRequest.query(folder,{'w':'getCarsSelectUser', 'mfa_link':mfa_link, 'model_link':model_link, 'group_id':group_id},
        function (result, errors){ if (errors) {alert(errors);} if (result){
            $("#" + block).html(result.content);
        }}, true);
}

/*
* get car chose form
* */
function getCarsSearch(block, mfa_link = "", model_link = "", group_id = 0) {
    JsHttpRequest.query(folder,{'w':'getCarsSearch', 'mfa_link':mfa_link, 'model_link':model_link, 'group_id':group_id},
        function (result, errors){ if (errors) {alert(errors);} if (result){
            $("#" + block).html(result.content);
        }}, true);
}
