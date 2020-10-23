/**
 * programmed by Jvaldez at gmail
 * 2015.may.14
 */

function borrarRepositorio(id) {
    $.ajax({
        cache: false,
        url: "/usuarios/ajax/borrar-repositorio",
        type: "post",
        data: { id: id },
        dataType: "json",
        success: function (res) {
            if (res.success === true) {
                cargarDatos("#usuarioRepositorio", "/usuarios/ajax/obtener-repositorios");
            }
        }
    });
}

$(document).ready(function () {

    $(document.body).on("change", "#formRepositorio #allCustoms", function () {
        var checked = true;
        if ($(this).is(":checked")) {
        } else {
            checked = false;
        }
        $.ajax({
            url: "/usuarios/post/todas-aduanas",
            cache: false,
            type: "post",
            dataType: "json",
            data: { idUsuario: $("#idUsuario").val(), checked: checked }
        }).done(function (res) {
            if (res.success === true) {
                cargarDatos("#usuarioRepositorio", "/usuarios/ajax/obtener-repositorios");
            }
        });
    });

    $(document.body).on("change", "#formRepositorio #patentesExpediente", function () {
        $.ajax({
            url: "/usuarios/post/obtener-aduanas",
            cache: false,
            type: "post",
            dataType: "json",
            data: { patente: $(this).val() }
        }).done(function (res) {
            if (res.success === true) {
                $("#aduanasExpediente").empty()
                    .removeAttr("disabled");
                $("#aduanasExpediente").append('<option value="">---</option>');
                $.each(jQuery.parseJSON(res.aduanas), function (key, value) {
                    $("#aduanasExpediente").append('<option value=' + value.aduana + '>' + value.aduana + ' - ' + value.nombre + '</option>');
                });
            }
        });
    });

    $("#formRepositorio").validate({
        errorPlacement: function (error, element) {
            $(element)
                .closest("form")
                .find("#" + element.attr("id"))
                .after(error);
        },
        errorElement: "span",
        errorClass: "traffic-error",
        rules: {
            patentesExpediente: "required",
            aduanasExpediente: "required"
        },
        messages: {
            patentesExpediente: "Seleccionar campo.",
            aduanasExpediente: "Seleccionar campo."
        }
    });

    $("#formRepositorio #agregarRepositorio").click(function (ev) {
        ev.preventDefault();
        if ($("#formRepositorio").valid()) {
            $("#formRepositorio").ajaxSubmit({
                url: "/usuarios/ajax/agregar-repositorio",
                type: "post",
                dataType: "json",
                success: function (res) {
                    if (res.success === true) {
                        $("#formRepositorio").trigger("reset");
                        cargarDatos("#usuarioRepositorio", "/usuarios/ajax/obtener-repositorios");
                    }
                }
            });
        }
    });

});