/**
 * programmed by Jvaldez at gmail
 * 2016.mar.2
 */

function descarga(arr) {
    $.ajax({
        url: "/archivo/ajax/descarga-archivo-validacion",
        cache: false,
        type: "post",
        dataType: "json",
        data: {arr: arr},
        success: function (res) {
            if(res.success === true) {
                window.location="/archivo/data/descarga-archivo?filename=" + res.filename;
            }
        }
    });
}

$(document).ready(function () {
    
    $("#fecha").datepicker({
        calendarWeeks: true,
        autoclose: true,
        language: "es",
        format: "yyyy-mm-dd"
    });
    
});
