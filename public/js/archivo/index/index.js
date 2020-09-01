
$(document).ready(function () {

    $("#referencia").on("input", function (evt) {
        var input = $(this);
        var start = input[0].selectionStart;
        $(this).val(function (_, val) {
            return val.toUpperCase();
        });
        input[0].selectionStart = input[0].selectionEnd = start;
    });

    $(document.body).on("click", "input[name='filter[]']", function () {
        Cookies.set("filtro", $(this).val());
        window.location.replace("/archivo/index/index");
    });

    if (Cookies.get("filtro") !== undefined) {
        $("input[name='filter[]'][value=" + Cookies.get("filtro") + "]").prop("checked", true);
    } else {
        $("input[name='filter[]'][value=0]").prop("checked", true);
    }
    
    $("#fecha-inicio, #fecha-fin").on("change", function (evt) {
        var val = $(this).val();
        var id = $(this).attr("id");
        
        Cookies.set(id, val);
    });
    
    $("#fecha-inicio, #fecha-fin").datepicker({
        calendarWeeks: true,
        autoclose: true,
        language: "es",
        format: "yyyy-mm-dd",
        onSelect: function() {
            $(this).change();
        }
    });

});