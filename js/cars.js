$(document).ready(function() {

    $(".cars-nav__item").on('click', function() {
        toggleCarsNavigation(this);
    });

    if (detectmob()) {
        // hide on mobile
        toggleCarsNavigation($("div[data-type='manuf']"));
    }

});

function toggleCarsTab(index) {
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

    if (detectmob()) {
        // $("html, body").animate({
        //     scrollTop: $("#scrollManuf").position().top
        // }, "slow");
        document.getElementById("scrollManuf").scrollIntoView();
    }
}

function showCarsNavigation(index) {
    // Remove Disables
    $(index).removeClass("cars-nav__item-disabled");
    $(index).removeClass("cars-nav__item-hidden");

    // Show Active Nav
    $(".cars-nav__item").each(function () {
        $(this).removeClass("cars-nav__item-active");
        $(this).removeClass("cars-nav__item-checked");
    });
    $(index).addClass("cars-nav__item-active");
    $(index).addClass("cars-nav__item-checked");

    // Show Active Tab
    $(".cars-tab__block").each(function () {
        $(this).removeClass("cars-tab__block-active");
    });
    let index_tab = $("#" + $(index).attr("data-tab"));
    index_tab.addClass("cars-tab__block-active");
    return true;
}

function toggleCarsNavigation(index, type, attr) {
    let data_pred = $("div[data-type='" + $(index).attr("data-pred") + "']");
    if (type===undefined) { type = $(data_pred).attr("data-type"); }
    if (attr===undefined) { attr = $(data_pred).attr("data-id"); }
    if (type===undefined && attr===undefined) { type=""; attr=0; }

    // Tab Non-Disabled
    if (!$(index).hasClass("cars-nav__item-disabled")) {
        // Uncheck Non-Active Nav
        // Close Non-Active Tab
        if ($(index).hasClass("cars-nav__item-active")) {
            $(index).removeClass("cars-nav__item-active");
            $("#" + $(index).attr("data-tab")).removeClass("cars-tab__block-active");
        } else {
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

function getCarsSearchContent(type, attr) {
    JsHttpRequest.query(folder,{'w':'getCarsSearchContent', 'type':type, 'attr':attr},
        function (result, errors){ if (errors) {alert(errors);} if (result) {

            let tab = $("#" + result.tab);
            tab.html(result.list);

            let nav = $("div[data-type='" + result.nav + "']");
            nav.html(result.title);

        }}, true);
}

function clearCarsBlock(data_tab) {
    let cur_tab = parseInt(data_tab.match(/\d+/));
    if (cur_tab!==undefined) {
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

function scrollTo(index) {
    $([document.documentElement, document.body]).animate({
        scrollTop: index.offset().top
    }, 500);
}

function setActiveCar() {

    let type = $("#active_nav").val();

    if (type!=="" && type!==undefined) {
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