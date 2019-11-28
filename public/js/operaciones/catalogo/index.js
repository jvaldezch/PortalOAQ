/**
 * programmed by Jvaldez at gmail
 * 2015.may.14
 */


$(document).ready(function () {

    $(document.body).on("input", "#busqueda", function (ev) {
        $("#errors").html("");
        var input = $(this);
        var start = input[0].selectionStart;
        $(this).val(function (_, val) {
            return val.toUpperCase();
        });
        input[0].selectionStart = input[0].selectionEnd = start;
    });
    
});