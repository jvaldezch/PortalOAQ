/**
 * programmed by Jvaldez at gmail
 * 2015.may.14
 */

function removerValidador(patente, aduana, id) {
    $.ajax({
        cache: false,
        url: "/usuarios/ajax/remover-validador",
        type: "post",
        data: {patente: patente, aduana: aduana, id: id},
        dataType: "json",
        success: function (res) {
            if (res.success === true) {
                cargarDatos("#usuarioValidador", "/usuarios/ajax/obtener-validador-asignado");
            }
        }
    });
}

$(document).ready(function () {

    $("#formValidador").validate({
        errorPlacement: function (error, element) {
            $(element)
                    .closest("form")
                    .find("#" + element.attr("id"))
                    .after(error);
        },
        errorElement: "span",
        errorClass: "traffic-error",
        rules: {
            idAduanaValidador: "required"
        },
        messages: {
            idAduanaValidador: "Debe seleccionar aduana."
        }
    });

    $("#formValidador #addValidador").click(function (e) {
        e.preventDefault();
        if ($("#formValidador").valid()) {
            $("#formValidador").ajaxSubmit({
                cache: false,
                url: "/usuarios/ajax/agregar-validador",
                type: "post",
                dataType: "json",
                success: function (res) {
                    if (res.success === true) {
                        cargarDatos("#usuarioValidador", "/usuarios/ajax/obtener-validador-asignado");
                    }
                }
            });
        }
    });

});