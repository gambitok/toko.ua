$(document).ready(function() {

    if ($("#cars_form-selected").length !== 0 && $("#car_form-select").length !== 0) {

        $("body").scroll(function() {
            let checked_index = $(".cars-nav__item-checked")[0];
            let header = $("#catalogue-main");
            let top = header.offset().top;
            let sticky = top - 64;

            if (window.pageYOffset >= top) {
                $("#catalogue-auto").addClass("sticky");
                $("#myHeader").addClass("sticky-header-active");
                $("#myBackdrop").addClass("sticky-backdrop-active");
            } else {
                $("#catalogue-auto").removeClass("sticky");
                $("#myHeader").removeClass("sticky-header-active");
                $("#myBackdrop").removeClass("sticky-backdrop-active");
            }
            if ($("#toggle_active_nav").val() == 0) {
                if (window.pageYOffset >= sticky) {
                    hideCarsNavigation(checked_index);
                } else {
                    showCarsNavigation(checked_index);
                }
            } else {
                if (window.pageYOffset >= sticky) {
                    //
                } else {
                    $("#toggle_active_nav").val(0);
                }
            }
        });

    }

    // if ($("#cars_form-selected").length !== 0 && $("#car_form-select").length !== 0) {
    //     $("#catalogue-auto").addClass("sticky");
    //     $("body").scroll(function() {
    //         let checked_index = $(".cars-nav__item-checked")[0];
    //         let header = $("#catalogue-auto");
    //         let top = header.offset().top;
    //         let sticky = top - 64;
    //         if (window.pageYOffset >= top) {
    //             $("#myHeader").addClass("sticky-header-active");
    //             $("#myBackdrop").addClass("sticky-backdrop-active");
    //         } else {
    //             $("#myHeader").removeClass("sticky-header-active");
    //             $("#myBackdrop").removeClass("sticky-backdrop-active");
    //         }
    //         if ($("#toggle_active_nav").val() == 0) {
    //             if (window.pageYOffset >= sticky) {
    //                 hideCarsNavigation(checked_index);
    //             } else {
    //                 showCarsNavigation(checked_index);
    //             }
    //         } else {
    //             if (window.pageYOffset >= sticky) {
    //                 //
    //             } else {
    //                 $("#toggle_active_nav").val(0);
    //             }
    //         }
    //     });
    // }

    // hide on mobile
    if (detectmob()) {
        if ($("div[data-type='manuf']").attr("data-id") === "0") {
            toggleCarsNavigation($("div[data-type='manuf']"));
        }
    }
});

// function showPop(e) {
//     let index = $(".cars-nav__item-checked")[0];
//
//     if (index.length === 0) {
//         index = e;
//     }
//
//     let data_pred = $("div[data-type='manuf']");
//
//     let offset;
//     let left = $(index).offset().left;
//     let pred_left = 0;
//
//     if (data_pred.length !== 0) {
//         pred_left = $(data_pred).offset().left;
//         offset = left - pred_left;
//     } else {
//         offset = 0;
//     }
//
//     let pop = $("#pop");
//     pop.css('display', 'block');
//     pop.css('position', 'absolute');
//     pop.css('left', offset);
//     pop.css('top', 72);
// }
//
// function hidePop() {
//     let pop = $("#pop");
//     pop.css('display', 'none');
// }

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
    let str_id = $("#details_str_id").val();
    // $("#cars-tab1").html("<div class=\"spinner-border\"></div>");
    // $("#cars-tab2").html("<div class=\"spinner-border\"></div>");
    // $("#cars-tab3").html("<div class=\"spinner-border\"></div>");
    // $("#cars-tab4").html("<div class=\"spinner-border\"></div>");
    // $("#cars-tab5").html("<div class=\"spinner-border\"></div>");
    // $("#cars-tab6").html("<div class=\"spinner-border\"></div>");
    JsHttpRequest.query(folder,{'w':'getCarsSearchContent', 'type':type, 'attr':attr, 'str_id':str_id},
        function (result, errors){ if (errors) {alert(errors);} if (result) {
            let tab = $("#" + result.tab);
            tab.html(result.list);
            let nav = $("div[data-type='" + result.nav + "']");
            nav.html(result.title);
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

function showCarsForm() {
    $("#CarsForm").modal("show");
    JsHttpRequest.query(folder,{'w':'showCarsForm'},
        function (result, errors){ if (errors) {alert(errors);} if (result){
            $("#cars_block").html(result.content);
        }}, true);
}
