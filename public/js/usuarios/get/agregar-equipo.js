let dp;

$(document).ready(function () {

    $("#add-equipment").validate({
        errorPlacement: function (error, element) {
            $(element)
                .closest("form")
                .find(`#${element.attr("id")}`)
                .after(error);
        },
        errorElement: "span",
        errorClass: "traffic-error-span",
        rules: {
            marca: "required",
            modelo: "required",
            numeroSerie: "required",
            entrego: "required",
            recibio: "required",
            autorizo: "required",
            entregada: "required"
        },
        messages: {
            marca: "[Campo es necesario]",
            modelo: "[Campo es necesario]",
            numeroSerie: "[Campo es necesario]",
            entrego: "[Campo es necesario]",
            recibio: "[Campo es necesario]",
            autorizo: "[Campo es necesario]",
            entregada: "[Campo es necesario]"
        }
    });

    $(document.body).on("input", "#marca, #modelo, #numeroSerie, #accesorios, #observaciones, #entrego, #recibio, #autorizo", function () {
        var input = $(this);
        var start = input[0].selectionStart;
        $(this).val(function (_, val) {
            return val.toUpperCase();
        });
        input[0].selectionStart = input[0].selectionEnd = start;
    });

    $("#entregada").datepicker({
        calendarWeeks: true,
        autoclose: true,
        language: "es",
        format: "yyyy-mm-dd",
        orientation: 'right'
    });
});