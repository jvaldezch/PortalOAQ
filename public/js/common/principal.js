$(document).ready(function () {
    
    if (Cookies.get("logout") === undefined) {
        Cookies.set("logout", false);        
    }
    var window_focus;

    /*$(window).focus(function () {
        window_focus = true;
        $.ajax({
            url: "/default/ajax/verify-session",
            cache: false,
            type: "post",
            success: function (res) {
                if (res.success === false) {
                    $.blockUI({ message: null });
                    document.location.href = "/default/index/logout";
                }
            }
        });
    }).blur(function () {
        window_focus = false;
    });*/
    
});

function goBack() {
    window.history.back();
}

var getUrlParameter = function getUrlParameter(sParam) {
    var sPageURL = decodeURIComponent(window.location.search.substring(1)),
            sURLVariables = sPageURL.split("&"),
            sParameterName,
            i;

    for (i = 0; i < sURLVariables.length; i++) {
        sParameterName = sURLVariables[i].split("=");

        if (sParameterName[0] === sParam) {
            return sParameterName[1] === undefined ? true : sParameterName[1];
        }
    }
};

function addUrlParameter(key, value) {
    key = encodeURI(key);
    value = encodeURI(value);
    var kvp = document.location.search.substr(1).split("&");
    var i = kvp.length;
    var x;
    while (i--) {
        x = kvp[i].split("=");
        if (x[0] === key) {
            x[1] = value;
            kvp[i] = x.join("=");
            break;
        }
    }
    if (i < 0) {
        kvp[kvp.length] = [key, value].join("=");
    }
    document.location.search = kvp.join("&");
}

function removeUrlParameter(key, sourceURL) {
    var rtn = sourceURL.split("?")[0],
            param,
            params_arr = [],
            queryString = (sourceURL.indexOf("?") !== -1) ? sourceURL.split("?")[1] : "";
    if (queryString !== "") {
        params_arr = queryString.split("&");
        for (var i = params_arr.length - 1; i >= 0; i -= 1) {
            param = params_arr[i].split("=")[0];
            if (param === key) {
                params_arr.splice(i, 1);
            }
        }
        rtn = rtn + "?" + params_arr.join("&");
    }
    document.location.href = rtn;
}
