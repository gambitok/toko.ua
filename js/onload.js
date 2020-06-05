var folder='/content.php';

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

var timer;
$('.header-nav__li').on({'mouseover': function () {
        var self = this;
        timer = setTimeout(function () {
            showHideNavigation($(self).attr("data-nav-id"));
        }, 500);
    },
    'mouseout' : function () {
        clearTimeout(timer);
    }
});

$("body").click(function(e) {
    if ($(e.target).attr('id') == 'search_art'
        || $(e.target).attr('class') == 'search-nav'
        || $(e.target).attr('class') == 'search-nav__item'
    ) { return true; } else {
        dropHistoryHide();
    }
});

function detectmob() {
    if (navigator.userAgent.match(/Android/i)
        || navigator.userAgent.match(/webOS/i)
        || navigator.userAgent.match(/iPhone/i)
        || navigator.userAgent.match(/iPad/i)
        || navigator.userAgent.match(/iPod/i)
        || navigator.userAgent.match(/BlackBerry/i)
        || navigator.userAgent.match(/Windows Phone/i)) {
        return true;
    } else {
        return false;
    }
}

function getCookie(cname) {
    var name = cname + "=";
    var ca = document.cookie.split(';');
    for(var i = 0; i < ca.length; i++) {
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
        var d = new Date();
        d.setTime(d.getTime() + exp*1000);
        exp = props.expires = d
    }

    if(exp && exp.toUTCString) { props.expires = exp.toUTCString() }
    value = encodeURIComponent(value);
    var updatedCookie = name + "=" + value;

    for(var propName in props){
        updatedCookie += "; " + propName;
        var propValue = props[propName];
        if(propValue !== true){ updatedCookie += "=" + propValue }
    }

    document.cookie = updatedCookie
}

function loadInputNumber() { "use strict";
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

$(document).ready(function() { "use strict";
    // tooltips
    $(".tooltips").tooltip();

    // lazy load
    var myLazyLoad = new LazyLoad({ elements_selector: ".lazy" });

    // input only numbers
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

    $(".carousel").carousel({
        interval: 122000
    });

    // ancor
    $(".ancor__link a").click(function() {
        var elementClick = $(this).attr("href");
        var destination = $(elementClick).offset().top;
        jQuery("html:not(:animated),body:not(:animated)").animate({
            scrollTop: destination
        }, 800);
        return false;
    });

    // slider filter range
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

    $(".params-data").each(function () {
        $(this).slider();
        $(this).on("slide", function(slideEvt) {
            $("#" + $(this).attr("name")).text(slideEvt.value);
        });
    });

    // art search input
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
    var $wind = $(window), $fixedup = $(".ancor"), limit2 = 240;
    function tglup (state) { $fixedup.toggleClass("hidden", state); }
    $wind.on("scroll", function () {
        var topd = $wind.scrollTop();
        if (topd < limit2) { tglup(true); } else { tglup(false); }
    });

    // mobile slider
    $(".bar").bigSlide();

});

