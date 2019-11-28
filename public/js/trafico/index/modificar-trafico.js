
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
        
    $(document.body).on("change", "select[name^='cliente']", function () {
        $.ajax({url: "/trafico/post/rfc-sociedad", cache: false, dataType: "json", type: "POST",
            data: {idCliente: $("#cliente").val()},
            success: function(res) {
                if (res.rfcSociedad !== null) {
                    $("#rfcSociedad").val(res.rfcSociedad);
                }
                if (res.plantas !== null) {
                    $("#divplanta").html(res.plantas);
                } else {
                    $("#divplanta").html('<select name="planta" id="planta" class="traffic-select-medium" tabindex="3" disabled="disabled"><option value="">---</option></select>');
                }
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
            cvePedimento: "required",
            pedimento: {
                required: true,
                minlength: 7,
                digits: true
            },
            referencia: {
                required: true,
                minlength: 7
            }
        },
        messages: {
            cliente: "Seleccionar cliente.",
            aduana: "Seleccionar aduana.",
            operacion: "Seleccionar tipo de operación.",
            cvePedimento: "Clave de pedimento necesaria",
            pedimento: {
                required: "Proporcionar el pedimento",
                minlength: "Minimo 7 digitos",
                digits: "Pedimento deben ser solo números"
            },
            referencia: {
                required: "Proporcionar referencia",
                minlength: "Minimo 7 digitos"
            }
        }
    });

    $(document.body).on("input", "#referencia", function (evt) {
        var input = $(this);
        var start = input[0].selectionStart;
        $(this).val(function (_, val) {
            return val.toUpperCase();
        });
        input[0].selectionStart = input[0].selectionEnd = start;
    });
    
    $(document.body).on("click", "#submit", function (ev) {
        ev.preventDefault();
        if ($("#form").valid()) {
            $("#form").ajaxSubmit({
                cache: false,
                url: "/trafico/ajax/modificar-trafico",
                type: "POST",
                dataType: "json",
                timeout: 3000,
                success: function (res) {
                    if (res.success === true) {
                        window.location.href = "/trafico/index/editar-trafico?id=" + $("#idTrafico").val();
                    } else if (res.success === false) {
                        $.alert({title: "Error", type: "red", content: res.message, boxWidth: "250px", useBootstrap: false});
                    }
                }
            });
        }
    });
    
});