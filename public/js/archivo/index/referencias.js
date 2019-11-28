
$(document).ready(function () {
    
    $("#referencia").on("input", function (evt) {
        var input = $(this);
        var start = input[0].selectionStart;
        $(this).val(function (_, val) {
            return val.toUpperCase();
        });
        input[0].selectionStart = input[0].selectionEnd = start;
    });
    
    var arr = "#revisados,#revisadosAdm,#revisadosOp,#completos"; // dont't use spaces

    $(document.body).on("click", arr, function () {
        if ($(this).is(":checked")) {
            Cookies.set($(this).attr("id"), true);
            window.location.replace("/archivo/index/referencias");
        } else {
            Cookies.set($(this).attr("id"), false);
            window.location.replace("/archivo/index/referencias");
        }
    });
    
    var array = arr.split(",");

    $.each(array, function (index, value) {
        var str = value.replace("#", "");
        if (Cookies.get(str) !== undefined) {
            if (Cookies.get(str) === "true") {
                $("#" + str).prop("checked", true);
            }
        }
    });

});