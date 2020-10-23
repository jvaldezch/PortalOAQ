/**
 * programmed by Jvaldez at gmail
 * 2015.may.14
 */

function removerClienteTrafico(idUsuario, idAduana, idCliente) {
    $.ajax({
        cache: false,
        url: "/usuarios/ajax/remover-cliente-usuario",
        type: "post",
        data: { idUsuario: idUsuario, idAduana: idAduana, idCliente: idCliente },
        dataType: "json",
        success: function (res) {
            if (res.success === true) {
                cargarDatos("#usuarioTraficos", "/usuarios/ajax/obtener-aduanas-trafico");
            }
        }
    });

}

$(document).ready(function () {

    $("#formTraffic").validate({
        errorPlacement: function (error, element) {
            $(element)
                .closest("form")
                .find("#" + element.attr("id"))
                .after(error);
        },
        errorElement: "span",
        errorClass: "traffic-error",
        rules: {
            idAduana: "required",
            idCliente: "required"
        },
        messages: {
            idAduana: "Seleccionar campo.",
            idCliente: "Seleccionar campo."
        }
    });

    $("#formTraffic #agregarTrafico").click(function (ev) {
        ev.preventDefault();
        if ($("#formTraffic").valid()) {
            $("#formTraffic").ajaxSubmit({
                url: "/usuarios/ajax/agregar-nueva-aduana",
                type: "post",
                dataType: "json",
                success: function (res) {
                    if (res.success === true) {
                        $("#formTraffic").trigger("reset");
                        cargarDatos("#usuarioTraficos", "/usuarios/ajax/obtener-aduanas-trafico");
                    }
                }
            });
        }
    });

});