
$(document).ready(function () {
    
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
            idBodega: "required",
            idCliente: "required",
            operacion: "required",
            fechaEta: "required",
            blGuia: "required",
            bultos: "required",
            proveedor: "required",
            lineaTransporte: "required",
            contenedorCaja: "required",
            planta: {
                required: {depends: function(elm) {
                    return $(this).is(":not(:disabled)");
                }}
            },
            referencia: {
                required: true,
                minlength: 4
            }
        },
        messages: {
            idBodega: "Seleccionar cliente.",
            idCliente: "Seleccionar cliente.",
            operacion: "Seleccionar tipo de operación.",
            fechaEta: "Fecha necesaria",
            planta: "Campo necesario",
            bultos: "Campo necesario",
            proveedor: "Campo necesario",
            lineaTransporte: "Campo necesario",
            contenedorCaja: "Campo necesario",
            blGuia: {
                required: "Guía necesaria"
            },
            referencia: {
                required: "Proporcionar referencia",
                minlength: "Mínimo 4 caracteres alfanumérico"
            }
        }
    });

    $(document.body).on("input", "#referencia", function (evt) {
        let input = $(this);
        let start = input[0].selectionStart;
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
                url: "/bodega/post/modificar-entrada",
                type: "POST",
                dataType: "json",
                timeout: 3000,
                success: function (res) {
                    if (res.success === true) {
                        window.location.href = "/bodega/index/editar-entrada?id=" + $("#idTrafico").val();
                    } else if (res.success === false) {
                        $.alert({title: "Error", type: "red", content: res.message, boxWidth: "250px", useBootstrap: false});
                    }
                }
            });
        }
    });
    
});