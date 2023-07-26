
$('.modal').on('shown.bs.modal', function () {
    new LazyLoad({ elements_selector: ".lazy" });
})

const folder = '/content.php';

function showSearchInput() {
    $('#InputForm').modal('show').on('shown.bs.modal', function () {
        $('#search_input').focus();
    }).on('keypress',function(e) {
        if (e.which == 13) {
            artSearch('search_input');
        }
    });
    showSearchDropdown();
}

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

function toggleSocialIcons() {
    if (detectmob()) {
        iosStyle();
    } else {
        let social_icons = $(".anchor-contacts-li-item");
        social_icons.toggleClass("anchor-contacts-li-hidden");
        if (social_icons.hasClass("anchor-contacts-li-hidden")) {
            $(".anchor-contacts-li-main").find("a").find("img").attr("src", "/images/icons/socials/png/chats.png");
        } else {
            $(".anchor-contacts-li-main").find("a").find("img").attr("src", "/images/icons/socials/png/close.png");
        }
    }
}

function iosStyle() {
    const mbody = document.getElementById('body-default').innerHTML;
    let contact_telegram = $("#contact_telegram").val();
    let contact_facebook = $("#contact_facebook").val();
    let contact_viber    = $("#contact_viber").val();
    let contact_phone    = $("#contact_phone").val();
    let contact_phone2   = $("#contact_phone2").val();
    let contact_phone3   = $("#contact_phone3").val();
    let contact_cancel   = $("#contact_cancel").val();

    const buttons = [
        {
            id: 'left-button',
            type: 'button',
            class: 'btn btn-lg btn-outline-primary',
            innerHTML: contact_telegram,
            onclick: () => location.href="https://t.me/tokoua_bot"
        },
        {
            id: 'left-button',
            type: 'button',
            class: 'btn btn-lg btn-outline-primary',
            innerHTML: contact_facebook,
            onclick: () => location.href="https://www.messenger.com/t/1564721550425159/?messaging_source=source%3Apages%3Amessage_shortlink&source_id=1441792"
        },
        {
            type: 'button',
            class: 'btn btn-lg btn-outline-primary',
            innerHTML: contact_viber,
            onclick: () => location.href="viber://pa?chatURI=tokogroup"
        },
        {
            type: 'button',
            class: 'btn btn-lg btn-outline-danger',
            innerHTML: contact_phone,
            onclick: () => location.href="tel:0970803060"
        },
        {
            type: 'button',
            class: 'btn btn-lg btn-outline-danger',
            innerHTML: contact_phone2,
            onclick: () => location.href="tel:0500803060"
        },
        {
            type: 'button',
            class: 'btn btn-lg btn-outline-danger',
            innerHTML: contact_phone3,
            onclick: () => location.href="tel:0930803060"
        },
        {
            type: 'button',
            class: 'btn btn-lg btn-outline-secondary',
            innerHTML: contact_cancel,
            onclick: () => iOSModal.hide()
        }
    ];
    iOSModal.show(mbody, buttons);
}

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

    if (getCookie('tpoint_id_status') === "" && getCookie('user_id') === "") {

        setTimeout(function() {
            showRegionForm();
        }, 5000);
    }

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
                //void(Tawk_API.toggle());
                toggleSocialIcons();
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

function showModalSearch() {
    if (!detectmob()) {
    } else {
        $("#SearchForm").modal('show');
        $("#search-mobile").addClass("search-mobile-fixed");
        $("#search-dropdown").addClass("search-dropdown-fixed");
        showSearchDropdown2();
    }
}

$(document).ready(function() {
    $('#SearchForm').on('click', function () {
        $("#search-mobile").removeClass("search-mobile-fixed");
        $("#search-dropdown").removeClass("search-dropdown-fixed");
        $("#SearchForm").modal('hide');
    });
});
