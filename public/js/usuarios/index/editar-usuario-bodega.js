/**
 * programmed by Jvaldez at gmail
 * 2015.may.14
 */

function removerBodega(id, idUsuario) {
    $.ajax({
        cache: false,
        url: "/usuarios/post/remover-bodega",
        type: "post",
        data: { id: id, idUsuario: idUsuario },
        dataType: "json",
        success: function (res) {
            if (res.success === true) {
                cargarDatos("#usuarioBodegas", "/usuarios/post/obtener-bodegas");
            } else {
                $.alert({ title: "Advertencia", type: "orange", content: res.message, boxWidth: "350px", useBootstrap: false });
            }
        }
    });
}

$(document).ready(function () {

    $("#formWarehouse").validate({
        errorPlacement: function (error, element) {
            $(element)
                .closest("form")
                .find("#" + element.attr("id"))
                .after(error);
        },
        errorElement: "span",
        errorClass: "traffic-error",
        rules: {
            idBodega: "required"
        },
        messages: {
            idBodega: "Debe seleccionar bodega."
        }
    });

    $("#formWarehouse #addWarehouse").click(function (ev) {
        ev.preventDefault();
        if ($("#formWarehouse").valid()) {
            $("#formWarehouse").ajaxSubmit({
                url: "/usuarios/post/agregar-bodega",
                type: "post",
                dataType: "json",
                success: function (res) {
                    if (res.success === true) {
                        $("#formWarehouse").trigger("reset");
                        cargarDatos("#usuarioBodegas", "/usuarios/post/obtener-bodegas");
                    } else {
                        $.alert({ title: "Advertencia", type: "orange", content: res.message, boxWidth: "350px", useBootstrap: false });
                    }
                }
            });
        }
    });

});