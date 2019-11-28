/**
 * programmed by Jvaldez at gmail
 * 2015.may.14
 */

function borrarInhouseRfc(id) {
    $.ajax({
        url: "/usuarios/post/borrar-inhouse-rfc",
        type: "post",
        data: {id: id},
        dataType: "json",
        success: function (res) {
            if (res.success === true) {
                cargarDatos("#inhouseRfc", "/usuarios/post/obtener-clientes");
            }
        }
    });
}

$(document).ready(function () {

    $("#formInhouse").validate({
        errorPlacement: function (error, element) {
            $(element)
                    .closest("form")
                    .find("#" + element.attr("id"))
                    .after(error);
        },
        errorElement: "span",
        errorClass: "traffic-error",
        rules: {
            clienteInhouse: "required"
        },
        messages: {
            clienteInhouse: "Seleccionar cliente."
        }
    });
    
    $("#formInhouse #agregarCliente").click(function (e) {
        e.preventDefault();
        if ($("#formInhouse").valid()) {
            $("#formInhouse").ajaxSubmit({
                cache: false,
                url: "/usuarios/post/agregar-cliente-inhouse",
                type: "post",
                dataType: "json",
                success: function (res) {
                    if (res.success === true) {
                        $("#formInhouse #idCliente").val("");
                        cargarDatos("#inhouseRfc", "/usuarios/post/obtener-clientes");
                    }
                }
            });
        }
    });
    
});