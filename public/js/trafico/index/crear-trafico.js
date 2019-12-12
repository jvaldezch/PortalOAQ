/**
 * programmed by Jvaldez at gmail
 * 2015.may.14
 */

function check(e, value) {
    //Check Charater
    var unicode = e.charCode ? e.charCode : e.keyCode;
    if (value.indexOf(".") != -1) {
        if (unicode == 46) {
            return false;
        }
    }
    if (unicode != 8) {
        if ((unicode < 48 || unicode > 57) && unicode != 46) {
            return false;
        }
    }
}

function checkLength(len, ele) {
    var fieldLength = ele.value.length;
    if (fieldLength <= len) {
        return true;
    } else {
        var str = ele.value;
        str = str.substring(0, str.length - 1);
        ele.value = str;
    }
}

function obtenerTipoCambio() {
    $.ajax({url: "/automatizacion/ws/tipo-cambio", cache: false, dataType: "json", type: "POST",
        success: function (res) {
            if (res.success === true) {
                $("#tipoCambio").val(res.value);
            }
        }
    });
}

function ordenarPorNombre(jsonObject) {
    var dataArray = [];
    var id;
    for (id in jsonObject) {
        var nombre = jsonObject[id];
        dataArray.push({id: parseInt(id), nombre: nombre});
    }
    dataArray.sort(function (a, b) {
        if (a.nombre < b.nombre)
            return -1;
        if (b.nombre < a.nombre)
            return 1;
        return 0;
    });
    return dataArray;
}

$(document).ready(function () {

    obtenerTipoCambio();

    $(document.body).on("change", "select[name^='aduana']", function () {
        var idAduana = $("#aduana").val();
        $.ajax({url: "/trafico/post/obtener-clientes", cache: false, dataType: "json", type: "POST",
            data: {idAduana: idAduana},
            success: function (res) {
                if (res.success === true) {
                    if (parseInt(idAduana) === 1 || parseInt(idAduana) === 2) {
                        $.ajax({url: "/bitacora/get/consecutivo", cache: false, dataType: "json", type: "POST",
                            data: {idAduana: idAduana}
                        }).done(function (res) {
                            if (res.success === true) {
                                $("#pedimento").val(res.pedimento);
                                $("#referencia").val(res.referencia);
                            }
                        });
                    } else {
                        $("#pedimento").removeAttr("readonly");
                        $("#referencia").removeAttr("readonly");
                    }
                    if (parseInt(res.tipoAduana) === 2) {
                        $("#blGuia").removeAttr("readonly");
                    }
                    $("#cliente").empty()
                            .append("<option value=\"\">---</option>")
                            .append(res.html);
                    $("#cliente").removeAttr("disabled");
                } else {
                    $.alert({title: "¡Advertencia!", closeIcon: true, backgroundDismiss: true, type: "red", escapeKey: "cerrar", boxWidth: "350px", useBootstrap: false, content: res.message});
                }
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
                }
            }
        });
    });
    
    $("#form").validate({
        errorPlacement: function (error, element) {
            $(element)
                    .closest("form")
                    .find("#" + element.attr("id"))
                    .after(error);
        },
        errorElement: "span",
        errorClass: "traffic-error",
        rules: {
            cliente: "required",
            aduana: "required",
            operacion: "required",
            cvePedimento: "required",
            fechaEta: "required",
            pedimento: {
                required: true,
                minlength: 7,
                maxlength: 7,
                regx: /^[0-9]{7}$/
            },
            blGuia: {
                required: {
                    depends: function(element) {
                        if (parseInt($("#aduana").val()) === 2 && $("#aduana").val() !== '') {
                            return true;
                        } else {
                            return false;
                        }
                    }
                }
            },
            planta: {
                required: {depends: function(elm) {
                    return $(this).is(":not(:disabled)");
                }}
            },
            pedimentoRectificar: {
                required: {depends: function(elm) {
                    return $("#rectificacion:checked").length;
                }},
                minlength: 7,
                maxlength: 7,
                regx: /^[0-9]{7}$/
            },
            referencia: {
                required: true,
                minlength: 4
            }
        },
        messages: {
            cliente: "Seleccionar cliente.",
            aduana: "Seleccionar aduana.",
            operacion: "Seleccionar tipo de operación.",
            cvePedimento: "Clave de pedimento necesaria",
            fechaEta: "Fecha necesaria",
            planta: "Campo necesario",
            pedimento: {
                required: "Campo necesario",
                minlength: "Pedimento debe ser de 7 digitos",
                maxlength: "Pedimento dede ser de 7 digitos",
                digits: "No debe contener letras"
            },
            blGuia: {
                required: "Guía necesaria"
            },
            pedimentoRectificar: {
                required: "Campo necesario",
                minlength: "Pedimento debe ser de 7 digitos",
                maxlength: "Pedimento dede ser de 7 digitos",
                digits: "No debe contener letras"
            },
            referencia: {
                required: "Proporcionar referencia",
                minlength: "Mínimo 4 caracteres alfanumérico"
            }
        }
    });
    
    function recuperarReferencia(idTrafico, msg) {
        $.confirm({title: "Referencia borrada", escapeKey: "cerrar", boxWidth: "350px", useBootstrap: false, type: "red",
            buttons: {
                si: {btnClass: "btn-red", action: function () {
                        $("#form").ajaxSubmit({url: "/trafico/ajax/recuperar-trafico", cache: false, dataType: "json", timeout: 3000, type: "POST",
                            success: function (res) {
                                if (res.success === true) {
                                    window.location = "/trafico/index/editar-trafico?id=" + res.id;
                                }
                            }
                        });
                }},
                no: {action: function () {}}
            },
            content: msg + " ¿Desea reutilizarlo? Este proceso sobre escribe el tráfico y expediente digital."
        });
    }

    $(document.body).on("click", "#submit", function (ev) {
        ev.preventDefault();
        if ($("#form").valid()) {
            $("#form").ajaxSubmit({url: "/trafico/ajax/nuevo-trafico", cache: false, dataType: "json", timeout: 3000, type: "POST",                
                beforeSend: function() {
                    $("#form").LoadingOverlay("show", {color: "rgba(255, 255, 255, 0.9)"});
                },
                success: function (res) {
                    if (res.success === true) {
                        window.location.href = "/trafico/index/traficos";
                    } else if (res.success === false) {
                        $("#form").LoadingOverlay("hide");
                        var msg = res.message;
                        if (!msg.search("pero ha sido marcado como borrado")) {
                            $.alert({title: "¡Advertencia!", closeIcon: true, backgroundDismiss: true, type: "red", escapeKey: "cerrar", boxWidth: "450px", useBootstrap: false, content: msg});
                        } else {
                            $("#idTrafico").val(res.idTrafico);
                            recuperarReferencia(res.idTrafico, msg);
                        }
                    }
                }
            });
        }
    });
    
    $(document.body).on("click", "#consecutive", function (ev) {
        ev.preventDefault();
        var idAduana = $("#aduana").val();
        if (parseInt(idAduana) === 1 || parseInt(idAduana) === 2) {
            $.ajax({url: "/bitacora/get/consecutivo", cache: false, dataType: "json", type: "POST",
                data: {idAduana: idAduana}
            }).done(function (res) {
                if (res.success === true) {
                    $("#pedimento").val(res.pedimento);
                    $("#referencia").val(res.referencia);
                }
            });
        }
    });
    
    $(document.body).on("click", "#rectificacion", function () {
        if ($(this).is(":checked")) {
            $("#pedimentoRectificar").removeAttr("readonly");
        } else {
            $("#pedimentoRectificar").attr("readonly", "true");
        }
    });
    
    $(document.body).on("click", "#remesa", function () {
        if ($(this).is(":checked")) {
            $("#referenciaOrigen").removeAttr("readonly");
        } else {
            $("#referenciaOrigen").attr("readonly", "true");
        }
    });

    /** UPPER CASE INPUT */
    $(document.body).on("input", "#referencia, #contenedorCaja, #blGuia", function (evt) {
        var input = $(this);
        var start = input[0].selectionStart;
        $(this).val(function (_, val) {
            return val.toUpperCase();
        });
        input[0].selectionStart = input[0].selectionEnd = start;
    });

    $("#fechaNotificacion, #fechaEta").datepicker({
        calendarWeeks: true,
        autoclose: true,
        language: "es",
        format: "yyyy-mm-dd"
    });

});