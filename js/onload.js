$('.modal').on('shown.bs.modal', function () {
    new LazyLoad({ elements_selector: ".lazy" });
})

const folder = '/content.php';

// MAIN NAVIGATION
$(".header-main").mouseover(function() {
    closeHideNavigation();
});

$(".main").mouseover(function() {
    closeHideNavigation();
});

$(".footer").mouseover(function() {
    closeHideNavigation();
});

$(".backdrop").mouseover(function() {
    closeHideNavigation();
});

let timer;
$('.header-nav__li').on({'mouseover': function () {
        let self = this;
        timer = setTimeout(function () {
            showHideNavigation($(self).attr("data-nav-id"));
        }, 500);
    },
    'mouseout' : function () {
        clearTimeout(timer);
    }
});

$("body").click(function(e) {
    if ($(e.target).attr('id') === 'search_art'
        || $(e.target).attr('class') === 'search-nav'
        || $(e.target).attr('class') === 'search-nav__item'
    ) { return true; } else {
        dropHistoryHide();
    }
});

function showViberForm() {
    $("#ViberForm").modal("show");
}

function showTelegramForm() {
    $("#TelegramForm").modal("show");
}

function detectmob() {
    return !!(navigator.userAgent.match(/Android/i)
        || navigator.userAgent.match(/webOS/i)
        || navigator.userAgent.match(/iPhone/i)
        || navigator.userAgent.match(/iPad/i)
        || navigator.userAgent.match(/iPod/i)
        || navigator.userAgent.match(/BlackBerry/i)
        || navigator.userAgent.match(/Windows Phone/i));
}

function getCookie(cname) {
    let name = cname + "=";
    var ca = document.cookie.split(';');
    for (var i = 0; i < ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ') {
            c = c.substring(1);
        }
        if (c.indexOf(name) == 0) {
            return c.substring(name.length, c.length);
        }
    }
    return "";
}

function setCookie(name, value, props) {
    props = { path: '/' };
    props = props || {};
    var exp = props.expires;

    if (typeof exp == "number" && exp) {
        let d = new Date();
        d.setTime(d.getTime() + exp*1000);
        exp = props.expires = d
    }

    if (exp && exp.toUTCString) { props.expires = exp.toUTCString() }
    value = encodeURIComponent(value);
    var updatedCookie = name + "=" + value;

    for (var propName in props){
        updatedCookie += "; " + propName;
        var propValue = props[propName];
        if(propValue !== true){ updatedCookie += "=" + propValue }
    }

    document.cookie = updatedCookie
}

function loadInputNumber() {
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

$(document).ready(function() {

    // Tooltips
    $(".tooltips").tooltip();

    // Lazy Load for images
    new LazyLoad({ elements_selector: ".lazy" });

    // Only numbers for buying
    loadInputNumber();

    // Main Banner
    $(".carousel").carousel({
        interval: 122000
    });

    // Ancor link (for scrolling top)
    // $(".ancor__link a").click(function() {
    //     let elementClick = $(this).attr("href");
    //     let destination = $(elementClick).offset().top;
    //     jQuery("html:not(:animated),body:not(:animated)").animate({
    //         scrollTop: destination
    //     }, 800);
    //     return false;
    // });

    // Catalog Filters
    let input_price = $("#filter-price"), input_delivery = $("#filter-delivery");
    if (input_price.length) {
        input_price.slider();
        input_price.on("slide", function(e) {
            let evalue = e.value;
            $("#price_val").text(evalue);
            $("#price_val_min").val(evalue[0]);
            $("#price_val_max").val(evalue[1]);
        });
    }
    if (input_delivery.length) {
        input_delivery.slider();
        input_delivery.on("slide", function (e) {
            let evalue = e.value;
            $("#dd_val").text(evalue);
            $("#dd_val_min").val(evalue[0]);
            $("#dd_val_max").val(evalue[1]);
        });
    }

    $(".params-data").each(function () {
        $(this).slider();
        $(this).on("slide", function(slideEvt) {
            $("#" + $(this).attr("name")).text(slideEvt.value);
        });
    });

    // Searching on `Enter` key
    $("#search_art").keyup(function(event) {
        if (event.keyCode === 13) {
            artSearch("search_art");
        }
    });
    $("#search_art2").keyup(function(event) {
        if (event.keyCode === 13) {
            artSearch("search_art2");
        }
    });
    $("#search_art3").keyup(function(event) {
        if (event.keyCode === 13) {
            artSearch("search_art3");
        }
    });
    $("#userlogin").keyup(function(event) {
        if (event.keyCode === 13) {
            loginForm();
        }
    });
    $("#userpassword").keyup(function(event) {
        if (event.keyCode === 13) {
            loginForm();
        }
    });

    // fixed top search
    var $win = $(window), $fixed = $(".fixed"), limit1 = 240;
    function tgl (state) { $fixed.toggleClass("hidden", state); }
    $win.on("scroll", function () {
        var top = $win.scrollTop();
        if (top < limit1) { tgl(true); } else { tgl(false); }
    });

    // fixed top search
    // var $wind = $(window), $fixedup = $(".ancor"), limit2 = 240;
    // function tglup (state) { $fixedup.toggleClass("hidden", state); }
    // $wind.on("scroll", function () {
    //     var topd = $wind.scrollTop();
    //     if (topd < limit2) { tglup(true); } else { tglup(false); }
    // });

    // Adaptive navigation
    $(".bar").bigSlide({
        menu: ('#menu')
    });

    // Adaptive Catalog navigation
    $(".bar-catalog").bigSlide({
        menu: ('#menu-catalog')
    });

    // Adaptive Catalog navigation
    $(".bar-menu").bigSlide({
        menu: ('#bar-menu')
    });

    if (detectmob()) {
        setCatalogFilters();
    }

    $("[autofocus]").on("focus", function() {
        if (this.setSelectionRange) {
            let len = this.value.length * 2;
            this.setSelectionRange(len, len);
        }
        this.scrollTop = 999999;
    }).focus();

    let cookie_user_id = getCookie("user_id");

    if (!detectmob()) {
        if (cookie_user_id === "") {
            setTimeout(function () {
                void(Tawk_API.toggle());
            }, 60000);
        }
    }

});

function getMenuBar(head_id) {
    JsHttpRequest.query(folder,{'w':'getMenuBar', 'head_id':head_id},
        function (result, errors){ if (errors) {alert(errors);} if (result){
            $("#menu-bar-content").html(result.content);
        }}, true);
}

