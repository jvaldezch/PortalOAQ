/**
 * programmed by Jvaldez at gmail
 * 2015.may.14
 */

function borrarPermiso(id) {
    $.ajax({
        url: "/usuarios/ajax/borrar-permiso",
        type: "post",
        data: {id: id},
        dataType: "json",
        success: function (res) {
            if (res.success === true) {
                cargarDatos("#usuarioSellos", "/usuarios/ajax/obtener-sellos");
            }
        }
    });
}

$(document).ready(function () {
    
    $(document.body).on("change", "#formFiel #razonSocial", function () {
        $("#formFiel #patentesFiel").html('<select disabled="disabled" class="traffic-select-small" id="patenteFiel" name="patenteFiel"><option label="-- Seleccionar --" value="">-- Seleccionar --</option></select>');
        $("#formFiel #aduanasFiel").html('<select disabled="disabled" class="traffic-select-small" id="aduanaFiel" name="aduanaFiel"><option label="-- Seleccionar --" value="">-- Seleccionar --</option></select>');
        $.ajax({
            url: "/usuarios/post/obtener-patentes-sellos",
            cache: false,
            type: "post",
            dataType: "json",
            data: {rfc: $("#formFiel #razonSocial").val()}
        }).done(function (res) {
            if (res.success === true) {
                $("#formFiel #patentesFiel").html(res.html);
            }
        });
    });

    $(document.body).on("change", "#formFiel #patenteFiel", function () {
        $("#aduanasFiel").html('<select disabled="disabled" class="traffic-select-small" id="aduanaFiel" name="aduanaFiel"><option label="-- Seleccionar --" value="">-- Seleccionar --</option></select>');
        $.ajax({
            url: "/usuarios/post/obtener-aduanas-sellos",
            cache: false,
            type: "post",
            dataType: "json",
            data: {patente: $(this).val(), rfc: $("#formFiel #razonSocial").val()}
        }).done(function (res) {
            if (res.success === true) {
                $("#formFiel #aduanasFiel").html(res.html);
            }
        });
    });
    
    $("#formFiel").validate({
        errorPlacement: function (error, element) {
            $(element)
                    .closest("form")
                    .find("#" + element.attr("id"))
                    .after(error);
        },
        errorElement: "span",
        errorClass: "traffic-error",
        rules: {
            razonSocial: "required",
            patente: "required",
            aduana: "required"
        },
        messages: {
            razonSocial: "Seleccionar un cliente.",
            patente: "Seleccionar una patente.",
            aduana: "Seleccionar una aduana."
        }
    });

    $("#formFiel #agregarFiel").click(function (e) {
        e.preventDefault();
        if ($("#formFiel").valid()) {
            $("#formFiel").ajaxSubmit({
                cache: false,
                url: "/usuarios/post/agregar-permiso-sello",
                type: "post",
                dataType: "json",
                success: function (res) {
                    if (res.success === true) {
                        cargarDatos("#usuarioSellos", "/usuarios/ajax/obtener-sellos");
                    }
                }
            });
        }
    });

});