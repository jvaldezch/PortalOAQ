
$(document).ready(function () {
    $('#precioPagadoFecha, #precioPorPagarFecha, #incrementablesFecha, #decrementablesFecha').datetimepicker({
        language: 'es',
        autoclose: true,
    });

    $(document.body).on("click", ".edit-edocument", function (ev) {
        let id = $(this).data('id');
        let tp = $(this).data('type');
        let ed = $(this).data('edocument');
        if (tp == 'CV') {
            datosCove(ed);
        }
    });

    $("#form").validate({
        errorPlacement: function (error, element) {
            $(element)
                .closest("form")
                .find("#" + element.attr("id"))
                .after(error);
        },
        errorElement: "span",
        errorClass: "traffic-error",
        rules: {
            precioPagadoImporte: {
                required: function (element) {
                    if($("#precioPagadoCheck").is(':checked')) {
                        return true;
                    }
                }
            }
        },
        messages: {
            precioPagadoImporte: "Se requiere",
        }
    });

    $(document.body).on("click", ".save-edocument", function (ev) {
        if ($("#form").valid()) {
            $("#form").ajaxSubmit({
                url: "/manifestacion/post/guardar-edocument", 
                dataType: "json", 
                type: "POST",
                success: function (res) {
                    if (res.success === true) {
                    }
                }
            });
        }
    });

    $(document.body).on("click", "#precioPagadoCheck", function (ev) {
        let d;
        if($(this).is(":checked")) {
            d = false;
        } else {
            d = true;
        }
        $("#precioPagadoImporte").prop("disabled", d);
        $("#precioPagadoTipo").prop("disabled", d);
        $("#precioPagadoFecha").prop("disabled", d);
    });

    $(document.body).on("click", "#precioPorPagarCheck", function (ev) {
        let d;
        if($(this).is(":checked")) {
            d = false;
        } else {
            d = true;
        }
        $("#precioPorPagarImporte").prop("disabled", d);
        $("#precioPorPagarTipo").prop("disabled", d);
        $("#precioPorPagarFecha").prop("disabled", d);
    });

    $(document.body).on("click", "#incrementablesCheck", function (ev) {
        let d;
        if($(this).is(":checked")) {
            d = false;
        } else {
            d = true;
        }
        $("#incrementablesImporte").prop("disabled", d);
        $("#incrementablesTipo").prop("disabled", d);
        $("#incrementablesFecha").prop("disabled", d);
        $("#incrementablesCargoACliente").prop("disabled", d);
    });

    $(document.body).on("click", "#decrementablesCheck", function (ev) {
        let d;
        if($(this).is(":checked")) {
            d = false;
        } else {
            d = true;
        }
        $("#decrementablesImporte").prop("disabled", d);
        $("#decrementablesTipo").prop("disabled", d);
        $("#decrementablesFecha").prop("disabled", d);
        $("#decrementablesCargoACliente").prop("disabled", d);
    });

    $(document.body).on("click", "#compensoPagoCheck", function (ev) {
        let d;
        if($(this).is(":checked")) {
            d = false;
        } else {
            d = true;
        }
        $("#motivo").prop("disabled", d);
        $("#prestacionMercancia").prop("disabled", d);
        $("#compensoPagoTipo").prop("disabled", d);
        $("#compensoPagoFecha").prop("disabled", d);
    });

});