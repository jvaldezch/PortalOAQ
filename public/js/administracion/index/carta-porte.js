$(document).ready(function () {
    $("#cliente").typeahead({
        source: function (query, process) {
            return $.ajax({
                url: "/comercializacion/index/json-customers-by-name",
                type: "get",
                data: {name: query},
                dataType: "json",
                success: function (res) {
                    return process(res);
                }
            });
        }
    }).change(function () {
        $("#rfcCliente").val("");
    });
    $("#cliente").change(function () {
        $.ajax({
            url: "</comercializacion/index/json-customer-info-by-name",
            type: "get",
            data: {name: $("#cliente").val()},
            dataType: "json",
            success: function (res) {
                if (res) {
                    $("#rfcCliente").val(res.rfc);
                    $("#domicilio").val(unescape(res.domicilio));
                }
            }
        });
    });
    $("input, textarea").keyup(function () {
        this.value = this.value.toUpperCase();
    });

    $("#letter").validate({
        rules: {
            fecha: {
                required: true
            },
            rfcCliente: {
                required: true
            },
            cliente: {
                required: true
            },
            domicilio: {
                required: true
            },
            origen: {
                required: true
            },
            destino: {
                required: true
            },
            cantidad: {
                required: true
            },
            unidad: {
                required: true
            },
            peso: {
                required: true
            },
            mercancia: {
                required: true
            },
            placas: {
                required: true
            },
            operador: {
                required: true
            },
            factura: {
                required: "#facturado:checked",
                minlength: 2
            }
        },
        messages: {
            fecha: {
                required: "Proporcionar fecha de la carta"
            },
            rfcCliente: {
                required: "Proporcionar el RFC del cliente"
            },
            cliente: {
                required: "Proporcionar nombre del cliente"
            },
            domicilio: {
                required: "Proporcionar domicilio del cliente"
            },
            origen: {
                required: "Proporcionar origen de la carga"
            },
            destino: {
                required: "Proporcionar destino de la carga"
            },
            cantidad: {
                required: "Proporcionar cantidad"
            },
            unidad: {
                required: "Proporcionar unidad de medida"
            },
            peso: {
                required: "Proporcionar peso de la carga"
            },
            mercancia: {
                required: "Proporcionar la descripcion de la mercancia"
            },
            placas: {
                required: "Proporcionar las placas de la unidad"
            },
            operador: {
                required: "Proporcionar nombre del operador"
            },
            factura: {
                required: "Debe proporcionar el número de factura"
            }
        }
    });
});