/**
 * 
 * 
 */

$(document).ready(function () {

    $(document.body).on("change", "select[name^='aduana']", function () {
        $.ajax({
            url: "/trafico/post/obtener-clientes",
            cache: false,
            type: "post",
            dataType: "json",
            data: {idAduana: $("#aduana").val()}
        }).done(function (res) {
            if (res.success === true) {
                $("#cliente").empty()
                        .append("<option value=\"\">---</option>")
                        .append(res.html);
                $("#cliente").removeAttr("disabled");
            } else {
                mensajeAlerta(res.message);
            }
        });
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
            cliente: "required",
            aduana: "required",
            operacion: "required",
            pedimento: {
                required: true,
                minlength: 7
            },
            referencia: "required"
        },
        messages: {
            cliente: "Campo necesario",
            aduana: "Campo necesario",
            operacion: "Campo necesario",
            pedimento: {
                    required: "Campo necesario",
                    minlength: "Pedimento debe ser de 7 numeros"
            },
            referencia: "Campo necesario"
        }
    });

    $(document.body).on("click", "#save", function(e) {
        e.preventDefault();
        if ($("#form").valid()) {
            $("#form").ajaxSubmit({
                url: "/trafico/post/guardar-solicitud-anticipo",
                type: "post",
                dataType: "json",
                timeout: 3000,
                success: function(res) {
                    if (res.success === true) {
                         window.location.href = ("/trafico/index/editar-solicitud?id=" + res.id + "&aduana=" + res.aduana);
                    } else {
                        alert(res.message);
                    }
                }
            });
        }
    });
    
    $(document.body).on("input", "#referencia", function(e) {
        var input = $(this);
        var start = input[0].selectionStart;
        $(this).val(function (_, val) {
            return val.toUpperCase();
        });
        input[0].selectionStart = input[0].selectionEnd = start;
    });

});
