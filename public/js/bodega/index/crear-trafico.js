/**
 * programmed by Jvaldez at gmail
 * 2015.may.14
 */

$.datetimepicker.setLocale('es');

$.datetimepicker.setDateFormatter({
    parseDate: function (date, format) {
        var d = moment(date, format);
        return d.isValid() ? d.toDate() : false;
    },
    
    formatDate: function (date, format) {
        return moment(date).format(format);
    },

    //Optional if using mask input
    formatMask: function(format){
        return format
            .replace(/Y{4}/g, '9999')
            .replace(/Y{2}/g, '99')
            .replace(/M{2}/g, '19')
            .replace(/D{2}/g, '39')
            .replace(/H{2}/g, '29')
            .replace(/m{2}/g, '59')
            .replace(/s{2}/g, '59');
    }
});

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

function editarTransporteModal(titulo, idBodega, idLineaTransporte) {
    $.confirm({title: titulo, escapeKey: "cerrar", boxWidth: "550px", useBootstrap: false, type: "blue",
        buttons: {
            guardar: {btnClass: "btn-blue", action: function () {
                    
                }},
            cerrar: {action: function () {}}
        },
        content: function () {
            var self = this;
            return $.ajax({url: "/bodega/get/editar-transporte?idBodega=" + idBodega + "&idLineaTransporte=" + idLineaTransporte, dataType: "json", method: "get"
            }).done(function (res) {
                var html = "";
                if (res.success === true) {
                    html = res.html;
                }
                self.setContent(html);
            }).fail(function () {
                self.setContent("Something went wrong.");
            });
        }
    });
}

function editarProveedorModal(titulo, idBodega, idCliente, idProveedor) {
    $.confirm({title: titulo, escapeKey: "cerrar", boxWidth: "550px", useBootstrap: false, type: "blue",
        buttons: {
            guardar: {btnClass: "btn-blue", action: function () {
                    if ($("#frmProvider").valid()) {
                        $("#frmProvider").ajaxSubmit({url: "/bodega/post/guardar-proveedor", dataType: "json", type: "POST",
                            success: function (res) {
                                obtenerProveedores(idCliente, idBodega);
                            }
                        });
                    } else {
                        return false;
                    }
                }},
            cerrar: {action: function () {}}
        },
        content: function () {
            var self = this;
            return $.ajax({url: "/bodega/get/editar-proveedor?idBodega=" + idBodega + "&idCliente=" + idCliente + "&idProveedor=" + idProveedor, dataType: "json", method: "get"
            }).done(function (res) {
                var html = "";
                if (res.success === true) {
                    html = res.html;
                }
                self.setContent(html);
            }).fail(function () {
                self.setContent("Something went wrong.");
            });
        }
    });
}

window.obtenerProveedores = function (idCliente, idBodega) {
    $.ajax({url: "/bodega/post/obtener-proveedores", cache: false, dataType: "json", type: "POST",
        data: {idCliente: idCliente, idBodega: idBodega},
        success: function(res) {
            if (res.success === true) {
                $('#idProveedor').empty()
                        .append('<option value="">---</option>');
                if (res.results.length > 0) {
                    $.each(res.results, function (k, v) {
                        $('#idProveedor').append('<option value="' + v["id"] + '">' + v["nombre"] + '</option>');
                    });
                    $('#idProveedor').removeAttr('disabled');
                }
            }
        }
    });
};

window.obtenerTransportes = function (idBodega) {
    $.ajax({url: "/bodega/post/obtener-transportes", cache: false, dataType: "json", type: "POST",
        data: {idBodega: idBodega},
        success: function(res) {
            if (res.transportes !== null) {
                $("#divtransporte").html(res.transportes);
            } else {
                $("#divtransporte").html('<select name="idLineaTransporte" id="idLineaTransporte" class="traffic-select-large" tabindex="9" disabled="disabled"><option value="">---</option></select>');
            }
        }
    });
};

window.nuevoProveedor = function (idBodega, idCliente) {
    editarProveedorModal("Nuevo proveedor", idBodega, idCliente);
};

window.editarProveedor = function (idBodega, idCliente, idProveedor) {
    editarProveedorModal("Editar proveedor", idBodega, idCliente, idProveedor);
};

window.nuevoTransporte = function (idBodega) {
    editarTransporteModal("Nuevo transporte", idBodega);
};

window.editarTransporte = function (idBodega, idLineaTransporte) {
    editarTransporteModal("Editar transporte", idBodega, idLineaTransporte);
};

$(document).ready(function () {
    
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

    $(document.body).on("click", "#submit", function (ev) {
        ev.preventDefault();
        if ($("#form").valid()) {
            $("#form").ajaxSubmit({url: "/bodega/post/nuevo-trafico", cache: false, dataType: "json", timeout: 3000, type: "POST",
                beforeSend: function() {
                    $("#form").LoadingOverlay("show", {color: "rgba(255, 255, 255, 0.9)"});
                },
                success: function (res) {
                    if (res.success === true) {
                       window.location.href = "/bodega/index/editar-entrada?id=" + res.id;
                    } else {                        
                        $("#form").LoadingOverlay("hide");
                        var msg = res.message;
                        if (!msg.search("pero ha sido marcado como borrado")) {
                            $.alert({title: "¡Advertencia!", closeIcon: true, backgroundDismiss: true, type: "red", escapeKey: "cerrar", boxWidth: "450px", useBootstrap: false, content: msg});
                        }
                    }
                }
            });
        }
    });
    
    $(document.body).on("click", ".edit-provider", function (ev) {
        ev.preventDefault();
        var idCliente = $("#idCliente").val();
        var idProveedor = $("#idProveedor").val();
        if (idCliente && idProveedor) {
            editarProveedor(idCliente, idProveedor);        
        } else {
            $.alert({title: "Error", type: "red", content: "No ha seleccionado cliente.", boxWidth: "350px", useBootstrap: false});
        }
    });
    
    $(document.body).on("click", ".new-provider", function (ev) {
        ev.preventDefault();
        var idBodega = $("#idBodega").val();
        var idCliente = $("#idCliente").val();
        if (idCliente) {
            nuevoProveedor(idBodega, idCliente);        
        } else {
            $.alert({title: "Error", type: "red", content: "No ha seleccionado cliente.", boxWidth: "350px", useBootstrap: false});
        }
    });
    
    $(document.body).on("click", ".new-transport", function (ev) {
        ev.preventDefault();
        var idBodega = $("#idBodega").val();
        if (idBodega) {
            nuevoTransporte(idBodega);
        } else {
            $.alert({title: "Error", type: "red", content: "No ha seleccionado bodega.", boxWidth: "350px", useBootstrap: false});
        }
    });
    
    $(document.body).on("click", ".edit-transport", function (ev) {
        ev.preventDefault();
        var idBodega = $("#idBodega").val();
        var idLineaTransporte = $("#idLineaTransporte").val();
        if (idBodega && idLineaTransporte) {
            editarTransporte(idBodega, idLineaTransporte);
        } else {
            $.alert({title: "Error", type: "red", content: "No ha seleccionado bodega.", boxWidth: "350px", useBootstrap: false});
        }
    });

    /** UPPER CASE INPUT */
    $(document.body).on("input", "#referencia, #proveedor, #contenedorCaja, #lineaTransporte, #blGuia, #proveedores, #contenedorCajaEntrada", function (evt) {
        var input = $(this);
        var start = input[0].selectionStart;
        $(this).val(function (_, val) {
            return val.toUpperCase();
        });
        input[0].selectionStart = input[0].selectionEnd = start;
    });
    
    $("#fechaEta").datetimepicker({
        format:'YYYY-MM-DD',
        formatDate:'YYYY-MM-DD',
        timepicker:false
    });
    
    $(document.body).on("change", "select[name^='idCliente']", function () {
        obtenerProveedores($("#idCliente").val(), $("#idBodega").val());
    });
    
    $(document.body).on("change", "select[name^='idProveedor']", function () {
        $.ajax({url: "/bodega/get/obtener-plantas", cache: false, dataType: "json", type: "POST",
            data: {idCliente: $("#idCliente").val()},
            success: function(res) {
                if (res.plantas !== null) {
                    $("#divplanta").html(res.plantas);
                } else {
                    $("#divplanta").html('<select name="idPlanta" id="idPlanta" class="traffic-select-medium" tabindex="3" disabled="disabled"><option value="">---</option></select>');
                }
            }
        });
    });
    
    $(document.body).on("change", "select[name^='idBodega']", function () {
        obtenerTransportes($("#idBodega").val());
    });

});