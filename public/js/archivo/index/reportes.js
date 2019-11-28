$(document).ready(function () {
    
    $("#nombre").typeahead({
        source: function (query, process) {
            return $.ajax({
                url: "/comercializacion/index/json-customers-by-name",
                type: "get",
                data: {name: query},
                dataType: "json",
                success: function (res) {
                    return process(res);
                }
            });
        }
    }).change(function () {
        $("#rfc").val("");
    });
    
    $("#nombre").change(function () {
        $.ajax({
            url: "/comercializacion/index/json-customer-rfc-by-name",
            type: "get",
            data: {name: $("#nombre").val()},
            dataType: "json",
            success: function (res) {
                if (res) {
                    $("#rfc").val(res);
                }
            }
        });
    });
    
    $("#referencia, #rfc").on("input", function (evt) {
        var input = $(this);
        var start = input[0].selectionStart;
        $(this).val(function (_, val) {
            return val.toUpperCase();
        });
        input[0].selectionStart = input[0].selectionEnd = start;
    });
    
    $("#fechaInicio, #fechaFin").datepicker({
        calendarWeeks: true,
        autoclose: true,
        language: "es",
        format: "yyyy-mm-dd"
    });
    
    $("#form").validate({
        errorPlacement: function (error, element) {
            $(element)
                    .closest("form")
                    .find("label[for='" + element.attr("id") + "']")
                    .append(error);
        },
        errorElement: "span",
        errorClass: "traffic-error",
        rules: {
            layout: "required",
            rfc: "required"
        },
        messages: {
            layout: "SELECCIONAR LAYOUT.",
            rfc: "RFC NECESARIO."
        }
    });
    
    $(document.body).on("click", "#submit", function (ev) {
        ev.preventDefault();
        if ($("#form").valid()) {
            window.open("/archivo/data/layout-expedientes?layout=" + $("input[name='layout']:checked").val()+"&fechaInicio="+$("#fechaInicio").val()+"&fechaFin="+$("#fechaFin").val()+"&rfc="+$("#rfc").val(), "_blank", "toolbar=0,location=0,menubar=0,height=550,width=1024,scrollbars=yes");
        }
    });
    
});


