
var emailData = {};
emailData["emails"] = {};
    
var base = "/bodega/index/editar-entrada";

$.datetimepicker.setLocale('es'); // https://xdsoft.net/jqplugins/datetimepicker/

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

function stringPadLeft(value) {
    var str = "" + value;
    var pad = "00";
    return pad.substring(0, pad.length - str.length) + str;
}

function getFormattedDate(value) {
    return value;
}

function crearAdenda(idFactura) {
    $.ajax({url: '/bodega/facturas/crear-adenda',
        data: {idFactura: idFactura},
        beforeSend: function() {
            $.LoadingOverlay("show", {color: "rgba(255, 255, 255, 0.9)"});
        },
        success: function (res) {
            if (res.success === true) {
                $.LoadingOverlay("hide");
                loadInvoices();
            } else {
                $.LoadingOverlay("hide");
                $.alert({title: "Error", type: "red", content: res.message, boxWidth: "350px", useBootstrap: false});
            }
        }
    });
}

function borrarFactura(id) {
    var id = id;
    $.confirm({title: "Factura", escapeKey: "cerrar", boxWidth: "350px", useBootstrap: false, type: "red",
        buttons: {
            si: {btnClass: "btn-red", action: function () {
                    $.ajax({url: "/bodega/post/borrar-factura", dataType: "json", timeout: 10000, type: "POST",
                        data: {id: id},
                        beforeSend: function() {
                            $.LoadingOverlay("show", {color: "rgba(255, 255, 255, 0.9)"});
                        },
                        success: function (res) {                            
                            $.LoadingOverlay("hide");
                            if (res.success === true) {
                                loadInvoices();
                            } else {
                                $.alert({title: "Error", type: "red", content: res.message, boxWidth: "250px", useBootstrap: false});
                            }
                        }
                    });
            }},
            no: {action: function () {}}
        },
        content: "¿Está seguro que desea borrar esta esta factura?"
    });
}

function borrarImagen(idImage) {
    var r = confirm("¿Está seguro que desea borrar la imagen?");
    if (r === true) {
        $.ajax({url: "/bodega/post/borrar-imagen", dataType: "json", timeout: 3000, data: {id: idImage}, type: "POST",
            success: function (res) {
                if (res.success === true) {
                    loadPhotos();
                }
            }
        });
    }
}

function borrarArchivo(id) {
    var answer = confirm("¿Desea borrar el archivo?");
    if (answer) {
        $.ajax({url: "/bodega/post/borrar-archivo", dataType: "json", cache: false, data: {id: id}, type: "POST",
            success: function (data) {
                loadFiles();
            }
        });
    }
}

function verFactura(idFactura) {
    var w = window.open("/bodega/get/ver-factura?id=" + idFactura, "varFactura", "toolbar=0,location=0,menubar=0,height=550,width=1024,scrollbars=yes");
    w.focus();
}

function descargarArchivo(href) {
    location.href = href;
}

function enviarEdocument(idArchivo) {
    $.confirm({ title: "Digitalizar Edocument", escapeKey: "cerrar", boxWidth: '80%', useBootstrap: false, type: "blue",
        closeIcon: true,
        buttons: {
            enviar: {btnClass: "btn-green", action: function () {
                if ($("#formFileType").valid()) {
                    $("#formFileType").ajaxSubmit({type: "POST", dataType: "json", timeout: 10000, url: "/bodega/post/enviar-edocument",
                        beforeSend: function() {
                            
                        },
                        success: function (res) {
                            if (res.success === true) {
                            
                            }
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
            return $.ajax({
                url: "/trafico/get/cargar-tipos-edocuments?idArchivo=" + idArchivo + "&idTrafico=" + $("#idTrafico").val(),
                method: "get"
            }).done(function (res) {
                self.setContent(res.html);
            }).fail(function () {
                self.setContent("Something went wrong.");
            });
        }
    });
}

function enviarFacturaVucem(idFactura) {
    $.ajax({url: "/bodega/post/enviar-vucem-factura", dataType: "json", type: "POST",
        data: {idTrafico: $("#idTrafico").val(), idFactura: idFactura},
        timeout: 3000
    });
}

function verCove(idFactura, cove) {
    var w = window.open("/bodega/get/ver-edocument?idFactura=" + idFactura + "&idTrafico=" + $("#idTrafico").val() + "&cove=" + cove + "&type=cove", "verCove", "toolbar=0,location=0,menubar=0,height=550,width=850,scrollbars=yes");
    w.focus();
}

function htmlEntities(str) {
    return String(str).replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;");
}

function vucemPreview(id) {
    var w = window.open("/bodega/facturas/vucem-preview?id=" + id + "&idTrafico=" + $("#idTrafico").val(), "previewXml", "toolbar=0,location=0,menubar=0,height=550,width=850,scrollbars=yes");
    w.focus();
}
    
function borrarVucem(id) {
    var id = id;
    $.confirm({title: "VUCEM", escapeKey: "cerrar", boxWidth: "350px", useBootstrap: false, type: "red",
        buttons: {
            si: {btnClass: "btn-red", action: function () {
                    $.ajax({url: "/bodega/post/borrar-vucem", dataType: "json", timeout: 10000, type: "POST",
                        data: {id: id},
                        beforeSend: function() {
                            $.LoadingOverlay("show", {color: "rgba(255, 255, 255, 0.9)"});
                        },
                        success: function (res) {                            
                            $.LoadingOverlay("hide");
                            if (res.success === true) {
                                getVucemLog();
                            } else {
                                $.alert({title: "Error", type: "red", content: res.message, boxWidth: "250px", useBootstrap: false});
                            }
                        }
                    });
            }},
            no: {action: function () {}}
        },
        content: "¿Está seguro que desea borrar esta operación?"
    });
}

function enviarAVucem(id) {
    $.ajax({url: "/bodega/get/enviar-vucem", dataType: "json", timeout: 10000, type: "POST",
        data: {idTrafico: $("#idTrafico").val(), id: id},
        beforeSend: function() {
            $.LoadingOverlay("show", {color: "rgba(255, 255, 255, 0.9)"});
        },
        success: function (res) {
            $.LoadingOverlay("hide");
            if (res.success === true) {
                getVucemLog();
            } else {
                $.alert({title: "Error", type: "red", content: res.message, boxWidth: "250px", useBootstrap: false});
            }
        }
    });
}

function consultarVucem(id) {
    $.ajax({url: "/bodega/get/consulta-respuesta-vucem", dataType: "json", timeout: 10000, type: "POST",
        data: {id: id},
        beforeSend: function() {
            $.LoadingOverlay("show", {color: "rgba(255, 255, 255, 0.9)"});
        },
        success: function (res) {
            $.LoadingOverlay("hide");
            if (res.success === true) {
                getVucemLog();
            } else {
                $.alert({title: "Error", type: "red", content: res.message, boxWidth: "250px", useBootstrap: false});
            }
        }
    });
}

function consultaDetalleLog(id) {
    $.ajax({url: "/bodega/get/consulta-detalle-log", dataType: "json", timeout: 10000, type: "GET",
        data: {id: id},
        success: function (res) {
            if (res.success === true) {
                $(".consultarVucem[data-id=" + id + "]").click(function () {
                    $.ajax({url: "/bodega/get/consulta-respuesta-vucem", dataType: "json", timeout: 10000, type: "GET",
                        data: {id: res.results.id},
                        success: function (res) {
                            if (res.success === true) {
                                getVucemLog();
                            }
                        }
                    });
                }).show();
            }
        }
    });
}

function changeFile(value) {
    $.ajax({url: "/trafico/ajax/cambiar-tipo-archivo", context: document.body, dataType: "json", data: {id: value, type: $("#select_" + value).val(), idTrafico: $("#idTrafico").val()}, type: "GET"
    }).done(function (res) {        
        if (res.success === true) {
            $("#edit_" + value).html("<p>" + res.type + "</p>");
            $("#icon_" + value).html(res.icons);
        } else if (res.success === false) {
            alert("Ocurrio un error al guardar cambios.");
        }
    });
}

function editarArchivo(value) {
    $.ajax({url: "/trafico/ajax/tipos-archivos", context: document.body, data: {id: value}, type: "GET",
        beforeSend: function () {
            $("#icon_" + value).html('<div style="font-size:1.3em; color: #2f3b58; float: left; margin-right: 5px"><i class="far fa-save" onclick="changeFile(' + value + ')"></i>&nbsp;<i class="fas fa-times" onclick="cancelEdit(' + value + ')"></i></div>');
        }
    }).done(function (data) {
        $("#edit_" + value).html(data);
    });
}

function cancelEdit(value) {
    $.ajax({url: "/trafico/ajax/cancelar-edicion", context: document.body, data: {id: value, type: $("#select_" + value).val()}, type: "GET",
        dataType: "json"
    }).done(function (res) {
        if (res.success === true) {
            $("#edit_" + value).html(res.type);
            $("#icon_" + value).html(res.icons);
        } else if (res.success === false) {
            alert("Ocurrio un error al guardar cambios.");
        }
    });
}

window.loadPackages = function() {
    $.ajax({url: "/bodega/get/obtener-bultos", dataType: "json", timeout: 10000, data: {id: $("#idTrafico").val()}, type: "POST",
        success: function (res) {
            if (res.success === true) {
                $("#traffic-packages").html(res.html);
            }
        }
    });
};

window.loadInvoices = function() {
    $.ajax({url: "/bodega/get/obtener-facturas", dataType: "json", timeout: 10000, data: {id: $("#idTrafico").val()}, type: "POST",
        success: function (res) {
            if (res.success === true) {
                $("#traffic-invoices").html(res.html);
            }
        }
    });
};

window.loadOther = function () {
    $.ajax({url: "/bodega/get/obtener-consolidado", dataType: "json", timeout: 10000, data: {id: $("#idTrafico").val()}, type: "POST",
        success: function (res) {
            if (res.success === true) {
                var thtml = '<table class="traffic-table traffic-table-left">';
                thtml += '<thead><tr><th colspan="2" class="traffic-table-title">CONSOLIDADOS</th></tr></thead><tbody>';
                var i;
                for (i in res.results) {
                    thtml += '<tr><td><a href="/bodega/index/editar-entrada?id=' + res.results[i].id + 
                            '" target="_blank">' + res.results[i].referencia + '</a></td><td style="width: 24px"><i class="far fa-trash-alt remove-traffic" data-id="' + res.results[i].id + '" style="cursor: pointer"></i></td></tr>';
                }
                thtml += '</tbody></table>';
                $('.consolidados').html(thtml);
            }
        }
    });
};

window.loadComments = function() {
    $("#traffic-comments").show();
    $.ajax({url: "/bodega/get/obtener-comentarios", dataType: "json", timeout: 10000, type: "POST",
        data: {idTrafico: $("#idTrafico").val()},
        success: function (res) {
            if (res.success === true) {
                if (res.results['bitacora'] !== null) {
                    $("#trafficLog").html('');
                    let rows = res.results['bitacora'];
                    if (rows.length > 0) {
                        for (let i = 0; i < rows.length; i++) {
                            let row = rows[i];
                            let html = '<tr>';
                            html += '<td style="font-size: 10px !important; line-height: 11px">' + row.bitacora + '</td>';
                            html += '<td style="font-size: 10px !important; line-height: 11px">' + (row.usuario !== null ? row.usuario : '') + '</td>';
                            html += '<td style="text-align: center; font-size: 10px !important; line-height: 11px">' + row.creado + '</td>';
                            html += '</tr>';
                            $("#trafficLog").append(html);
                        }
                    } else {
                        $("#trafficLog").append("<tr><td colspan='3' style='text-align: center'>No hay bitacora</td></tr>");
                    }
                } else {
                    $("#trafficLog").html('<tr><td colspan="3" style="font-size: 10px !important; line-height: 11px">No hay comentarios</td></tr>');
                }
                if (res.results['comentarios'] !== null) {
                    $("#trafficComments").html('');
                    let rows = res.results['comentarios'];
                    for (let i = 0; i < rows.length; i++) {
                        let row = rows[i];
                        let html = '<tr>';
                        html += '<td>' + row.nombre + '</td>';
                        html += '<td>' + (row.mensaje !== null ? row.mensaje : '');
                        if (row.nombreArchivo) {
                            html += '<br><img src="/images/icons/attachment.gif"><span style="font-size: 11px"><a href="/archivo/get/descargar-archivo-temporal?id=' + row.idArchivo + '">' + row.nombreArchivo + '</a></span>';
                        }
                        html += '</td>';
                        html += '<td>' + row.creado + '</td>';
                        html += '<tr>';
                        $("#trafficComments").append(html);
                    }
                } else {

                    $("#trafficComments").html('<tr><td colspan="3" style="font-size: 10px !important; line-height: 11px">No hay comentarios</td></tr>');
                }
                if (res.results['archivos'] !== null) {
                    $("#attachedFiles").html('');
                    let row = res.results['archivos'];
                    for (let i = 0; i < row.length; i++) {
                        $("#attachedFiles").append('<img src="/images/icons/attachment.gif"><span style="font-size: 11px"><a href="/archivo/get/descargar-archivo-temporal?id=' + row.idArchivo + '">' + row.nombreArchivo + '</a></span><br>');
                    }
                }


            } else {
                console.log(res.results['comentarios']);
                $("#trafficLog").html('<tr><td colspan="3" style="font-size: 10px !important; line-height: 11px">No hay comentarios</td></tr>');
            }
        }       
    });
};

window.loadRegister = function() {
    $("#traffic-register").show();
    $.ajax({url: "/bodega/get/cargar-registros", dataType: "json", timeout: 20000, data: {id: $("#idTrafico").val()}, type: "POST",
        success: function (res) {
            if (res.success === true) {
                $("#traffic-register").html(res.html);
            }
        }        
    });
};

window.loadFiles = function() {
    $('#traffic-files').show();
    $.ajax({url: "/bodega/get/obtener-archivos", dataType: "json", timeout: 30000, data: {id: $("#idTrafico").val()}, type: "POST",
        beforeSend: function() {
            $('#traffic-files').LoadingOverlay('show', {color: 'rgba(255, 255, 255, 0.9)'});
        },
        success: function (res) {
            $('#traffic-files').LoadingOverlay('hide');
            if (res.success === true) {
                $('#traffic-files').html(res.html);
            }
        },
        complete: function (res) {
            $('#traffic-files').LoadingOverlay('hide');
        }
    });
};

window.loadPhotos = function() {
    $('#traffic-photos').show();
    $.ajax({url: "/bodega/get/obtener-fotos", dataType: "json", timeout: 30000, type: "POST", data: {id: $("#idTrafico").val()},
        beforeSend: function() {
            $('#traffic-photos').LoadingOverlay('show', {color: 'rgba(255, 255, 255, 0.9)'});
        },
        success: function (res) {
            if (res.success === true) {
                $('#traffic-photos').LoadingOverlay('hide');
                $('#traffic-photos').html(res.html);
            }
        },
        complete: function (res) {
            $('#traffic-photos').LoadingOverlay('hide');
        }
    });
};

window.guardarEnDisco = function(id) {
    return $.ajax({url: "/bodega/get/vucem-guardar", dataType: "json", type: "GET", 
        data: {id: id},
        success: function (res) {
            if (res.success === true) {
                $.alert({title: "Confirmación", type: "blue", content: "Documento guardado de manera exitosa.", boxWidth: "350px", useBootstrap: false});
            } else {
                $.alert({title: "Error", type: "red", content: res.message, boxWidth: "350px", useBootstrap: false});
            }
        }
    });
};

window.getVucemSignatures = function() {
    return $.ajax({url: "/bodega/get/vucem-firmas", dataType: "json", type: "POST", 
        data: {idTrafico: $("#idTrafico").val()},
        beforeSend: function (xhr) {
            $("#vucemSignatures").html('');
        },
        success: function (res) {
            if (res.success === true) {
                if (res.results.length === 0) {
                    $("#vucemSignatures").append('<tr><td colspan="5" style="text-align: center"><em>No hay sellos disponibles.</em></tr>');
                    return true;
                }
                if (res.results['agente']) {
                    var row = res.results['agente'];
                    for (var i = 0; i < row.length; i++) {
                        $("#vucemSignatures").append('<tr><td><input type="radio" name="sello" data-type="agente" value="' + row[i].id + '" ></td><td>' + row[i].patente + '</td><td>' + row[i].rfc + '</td><td style="text-align: left">' + row[i].razon + '</td><td></td></tr>');
                    }
                }
                if (res.results['cliente']) {
                    var row = res.results['cliente'];
                    for (var i = 0; i < row.length; i++) {
                        $("#vucemSignatures").append('<tr><td><input type="radio" name="sello" data-type="cliente" value="' + row[i].id + '" ></td><td></td><td>' + row[i].rfc + '</td><td style="text-align: left">' + row[i].razon + '</td><td></td></tr>');                        
                    }
                }
                if (res.results['config']) {
                    var row = res.results['config'];
                    if (row.idSelloAgente !== null) {
                        $('input[name=sello][value=' + row.idSelloAgente +']').prop('checked', true);
                    }
                    if (row.idSelloCliente !== null) {
                        $('input[name=sello][value=' + row.idSelloCliente +']').prop('checked', true);
                    }
                }
                if ($(':input[name="sello"]').length === 1) {
                    $(':input[name="sello"]').prop('checked', true)
                        .attr('checked', true);

                    var idSello = $('input[name="sello"]:checked').val();
                    var tipo = $('input[name="sello"]:checked').data('type');
                    var idTrafico = $("#idTrafico").val();
                    
                    establecerSello(idTrafico, idSello, tipo);
                }
                return true;
            }
            return false;
        }        
    });
    
};

window.obtenerDefault = function (idCliente) {
    return $.ajax({url: '/bodega/get/obtener-sello-default', type: "GET",
        data: {idCliente: idCliente},
        success: function (res) {
            if (res.success === true) {
                $("input[name=sello][value=" + res.id + "]").prop("checked", true);
            }
        }
    });
};

window.getVucemLog = function() {
    $.ajax({url: "/bodega/get/vucem-bitacora", dataType: "json", type: "POST", 
        data: {idTrafico: $("#idTrafico").val()},
        success: function (res) {
            if (res.success === true) {
                $("#vucemLog").html(res.html);
            }
        }        
    });
};

window.addPackage = function() {
    $.confirm({title: "Borrar bulto", escapeKey: "cerrar", boxWidth: "350px", useBootstrap: false, type: "green",
        buttons: {
            si: {btnClass: "btn-green", action: function () {
                    $.ajax({url: "/bodega/post/agregar-bulto", dataType: "json", timeout: 10000, type: "POST",
                        data: {idTrafico: $("#idTrafico").val()},
                        success: function (res) {
                            if (res.success === true) {                                
                                $("#bultos").val(res.totalBultos);
                                loadPackages();
                            }
                        }
                    });
            }},
            no: {action: function () {}}
        },
        content: "¿Está seguro que desea borrar esta este bulto?"
    });
};

window.establecerSello = function(idTrafico, idSello, tipo) {
    return $.ajax({url: "/bodega/post/establecer-sello-vucem", cache: false, dataType: "json", type: "POST",
        data: {idTrafico: idTrafico, idSello: idSello, tipo: tipo},
        success: function (res) {
            if (res.success === true) {
                
            } else {
                $.alert({title: "Advertencia", type: "red", content: res.message, boxWidth: "250px", useBootstrap: false});
            }
        }
    });
};

function currentActive(current) {
    if(Cookies.get("active") === "#information") {
        loadOther();
        loadInvoices();
        loadPackages();
        loadComments();
    }
    if(Cookies.get("active") === "#files") {
        loadFiles();     
        loadPhotos();     
    }
    if(Cookies.get("active") === "#vucem") {
        getVucemLog();
        $.when( getVucemSignatures() ).done(function( res ) {
            if (res.success === true) {
                obtenerDefault($("#idCliente").val());
            }
        });
    }
}

function abrirPrevio(idGuia) {
    $.confirm({title: "Detalle de guía", escapeKey: "cerrar", boxWidth: "650px", useBootstrap: false, type: "blue",
        buttons: {
            cerrar: {btnClass: "btn-red", action: function () {}}
        },
        content: function () {
            var self = this;
            return $.ajax({url: "/bitacora/get/detalle-guia?idGuia=" + idGuia, dataType: "json", method: "GET"
            }).done(function (res) {
                var html = "";
                if(res.success === true) { html = res.html; }
                self.setContent(html);
            }).fail(function () {
                self.setContent("Something went wrong.");
            });
        }
    });
}

$(document).ready(function () {
            
    var valid = ["#information", "#comments", "#register", "#files", "#photos", "#vucem"];

    $(document.body).on("click", "#traffic-tabs li a", function() {
        var href = $(this).attr("href");
        Cookies.set("active", href);
        currentActive(Cookies.get("active"));
    });

    if (Cookies.get("active") !== undefined) {
        if(valid.indexOf(Cookies.get("active")) !== -1) {
            $("a[href='" + Cookies.get("active") + "']").tab("show");
            currentActive(Cookies.get("active"));
        } else {
            $("a[href='#information']").tab("show");
            Cookies.set("active", "#information");
            currentActive(Cookies.get("active"));
        }
    } else {
        $("a[href='#information']").tab("show");
        Cookies.set("active", "#information");
        currentActive(Cookies.get("active"));
    }

    $(document.body).on("change", "#check-all", function() {
        $(".checkvucem").prop("checked", $(this).prop("checked"));
    });

//    $("#horaRecepcionDocs").timepicker({"step": 15, "timeFormat": "h:i A"});

    /*** FACTURAS  ****/
    $("#form-invoice").validate({
        errorPlacement: function (error, element) {
            $(element)
                    .closest("form")
                    .find("#" + element.attr("id"))
                    .after(error);
        },
        errorElement: "span",
        errorClass: "errorlabel",
        rules: {
            numFactura: {required: true}
        },
        messages: {
            numFactura: "Antes de agregar se requiere capturar número de factura"
        }
    });

    $(document.body).on('click', '#addInvoice', function (ev) {
        ev.preventDefault();
        if ($("#form-invoice").valid()) {
            $("#form-invoice").ajaxSubmit({url: "/bodega/post/agregar-factura", dataType: "json", timeout: 3000, type: "POST",
                success: function (res) {
                    $('#numFactura').val('');
                    if (res.success === true) {
                        loadInvoices();
                    } else {
                        $.alert({title: "Error", type: "red", content: res.message, boxWidth: "250px", useBootstrap: false});
                    }
                }
            });
        }
        return false;
    });

    /*** GUIAS  ****/
    $("#form-tracking").validate({
        errorPlacement: function (error, element) {
            $(element)
                    .closest("form")
                    .find("label[for='" + element.attr("id") + "']")
                    .append(error);
        },
        errorElement: "span",
        errorClass: "errorlabel",
        rules: {
            transportista: {required: true},
            tipoguia: {required: true},
            number: {required: true}
        },
        messages: {
            transportista: "SE REQUIERE",
            tipoguia: "SE REQUIERE",
            number: "SE REQUIERE"
        }
    });

    $(document).on("input", "#ubicacion, #contenedorCajaEntrada, #contenedorCajaSalida, #numFactura, #proveedor, #number, #comment, #proveedores, #blGuia, #lineaTransporte, #placas, #candados", function() {
        var input = $(this);
        var start = input[0].selectionStart;
        $(this).val(function (_, val) {
            return val.toUpperCase();
        });
        input[0].selectionStart = input[0].selectionEnd = start;
    });

    var bar = $(".barImage");
    var percent = $(".percentImage");
    
    $("#formPhotos").validate({
        errorPlacement: function (error, element) {
            $(element)
                    .closest("form")
                    .find("label[for='" + element.attr("id") + "']")
                    .after(error);
        },
        rules: {
            "images[]": {
                required: true
            }
        },
        messages: {
            "images[]": {
                required: " [No se ha seleccionado una imagen.]"
            }
        }
    });

    $(document.body).on("click","#delete-traffic",function (e) {
        e.preventDefault();
        $.confirm({title: "Confirmación", escapeKey: "cerrar", boxWidth: "350px", useBootstrap: false,
            buttons: {
                no: {
                    action: function () {
                    }
                },
                si: {
                    btnClass: "btn-red",
                    action: function () {
                        $.ajax({url: "/bodega/post/borrar-entrada", cache: false, type: "POST", dataType: "json", data: {id: $("#idTrafico").val()},
                            beforeSend: function() {                 
                            },
                            success: function(res) {
                                if(res.success === true) {
                                    window.location.href = "/bodega/index/index";
                                } else {
                                    $.alert({title: "Error", type: "red", content: res.message, boxWidth: "350px", useBootstrap: false});
                                }
                            }
                        });
                        return false;
                    }
                }
            },
            content: '¿Está seguro(a) que desea eliminar la referencia?'
        });
    });
    
    $(document.body).on("click",".image-link",function (ev) {
        ev.preventDefault();
        var w = window.open("/bodega/get/read-image?id=" + $(this).data("id"), 'Trafico Image ' + $(this).data("id"), 'toolbar=0,location=0,menubar=0,height=750,width=950,scrollbars=yes');
        w.focus();
        return false;
    });
    
    $(document.body).on("click","#uploadImage",function (ev) {
        ev.preventDefault();
        if ($("#formPhotos").valid()) {
            $("#formPhotos").ajaxSubmit({url: "/bodega/post/subir-imagenes",
                beforeSend: function () {
                    var percentVal = "0%";
                    bar.width(percentVal);
                    percent.html(percentVal);
                },
                uploadProgress: function (event, position, total, percentComplete) {
                    var percentVal = percentComplete + "%";
                    bar.width(percentVal);
                    percent.html(percentVal);
                },
                success: function() {
                    loadPhotos();
                }
            });
        }
    });
    
    $("#form-files").validate({
        errorPlacement: function (error, element) {
            $(element)
                    .closest("form")
                    .find("label[for='" + element.attr("id") + "']")
                    .after(error);
        },
        rules: {
            "file[]": {
                required: true
            }
        },
        messages: {
            "file[]": {
                required: " [No se ha seleccionado un archivo.]"
            }
        }
    });
    
    $("#btn-upload-files").click(function (ev) {
        ev.preventDefault();
        if ($("#form-files").valid()) {
            $("#form-files").ajaxSubmit({type: "POST", dataType: "json", timeout: 10000,
                beforeSend: function() {
                    $('#traffic-files').LoadingOverlay('show', {color: 'rgba(255, 255, 255, 0.9)'});
                },
                success: function (res) {
                    $('#traffic-files').LoadingOverlay('hide');
                    if (res.success === true) {
                        $("#file").val(''); 
                        loadFiles();
                    } else {
                        $.alert({title: "Error", type: "red", content: res.message, boxWidth: "350px", useBootstrap: false});
                    }
                }
            });
        }
    });

    $(document.body).on("click", "#enviar-notificacion", function (ev) {
        ev.preventDefault();
        $.confirm({title: "Enviar notificación a cliente", type: 'blue', escapeKey: "cerrar", boxWidth: "550px", useBootstrap: false,
            buttons: {
                no: {
                    action: function () {
                    }
                },
                si: {
                    btnClass: "btn-blue",
                    action: function () {
                        $.ajax({url: "/bodega/post/enviar-notificacion", cache: false, type: "POST", dataType: "json", data: {id: $("#idTrafico").val()},
                            beforeSend: function() {
                            },
                            success: function(res) {
                                if(res.success !== true) {
                                    $.alert({title: "Error", type: "red", content: res.message, boxWidth: "350px", useBootstrap: false});
                                }
                            }
                        });
                        return false;
                    }
                }
            },
            content: function () {
                var self = this;
                return $.ajax({url: "/bodega/get/notificacion", dataType: "json", method: "GET",
                    data: {idTrafico: $("#idTrafico").val()}
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
    });

    $(document.body).on("click", "#checklist", function (ev) {
        ev.preventDefault();
        $.confirm({title: "Checklist de intregración de expediente", escapeKey: "cerrar", boxWidth: "650px", useBootstrap: false,
            buttons: {
                auto: {
                    btnClass: "btn-blue",
                    action: function () {
                        llenarChecklist();
                        return false;
                    }
                },
                guardar: {
                    btnClass: "btn-green",
                    action: function () {
                        $("#complete").show();
                        $(this).addClass("traffic-btn-disabled")
                                .removeClass("traffic-btn-success");
                        $("#formChecklist").ajaxSubmit({url: "/archivo/post/guardar-checklist", dataType: "json", type: "POST",
                            success: function (res) {
                                if (res.success === true) {
                                    verificarChecklist();
                                }
                            }
                        });
                    }
                },
                cerrar: {
                    btnClass: "btn-red",
                    action: function () {}
                }
            },
            content: function () {
                var self = this;
                return $.ajax({url: "/archivo/post/checklist", dataType: "json", method: "POST",
                    data: {idTrafico: $("#idTrafico").val()}
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
    });
    
//    $("#getInvoices").qtip({ // Grab some elements to apply the tooltip to
//        content: {
//            text: "Cargar facturas desde sistema de pedimentos."
//        }
//    });
    
    $(document.body).on("click", ".editInvoice", function (ev) {
        var w = window.open("/bodega/facturas/editar-factura?idFactura=" + $(this).data("id"), 'editarFactura', 'toolbar=0,location=0,menubar=0,height=750,width=950,scrollbars=yes');
        w.focus();
        return false;
    });
    
    $(document.body).on("click", "#getInvoices", function () {
        $.confirm({title: "Facturas de pedimento", escapeKey: "cerrar", boxWidth: "650px", useBootstrap: false,
            buttons: {
                seleccionar: {
                    btnClass: "btn-blue",
                    action: function () {
                        var facturas = [];
                        var boxes = $("input[class=invoice]:checked");
                        if ((boxes).size() > 0) {
                            $(boxes).each(function () {
                                facturas.push($(this).data("factura"));
                            });
                            $.post("/bodega/post/seleccionar-facturas", {idTrafico: $("#idTrafico").val(), facturas: facturas})
                                    .done(function (res) {
                                        if(res.success === true) {
                                            loadInvoices();
                                        }
                                    });
                        }
                    }
                },
                cerrar: {
                    btnClass: "btn-red",
                    action: function () {}
                }
            },
            content: function () {
                var self = this;
                return $.ajax({url: "/bodega/get/facturas-pedimento?idTrafico=" + $("#idTrafico").val(), dataType: "json", method: "GET"
                }).done(function (res) {
                    var html = "";
                    if(res.success === true) {
                        html = res.html;
                    }
                    self.setContent(html);
                }).fail(function () {
                    self.setContent("Something went wrong.");
                });
            }
        });
    });
    
    $(document.body).on("click", "#loadTemplate", function () {
        $.confirm({title: "Importar plantilla", escapeKey: "cerrar", boxWidth: "350px", useBootstrap: false,
            buttons: {
                subir: {
                    btnClass: "btn-blue",
                    action: function () {
                        if ($("#formTemplate").valid()) {
                            $("#formTemplate").ajaxSubmit({url: "/bodega/post/subir-plantilla", dataType: "json", type: "POST",
                                beforeSend: function() {
                                    $.LoadingOverlay("show", {color: "rgba(255, 255, 255, 0.9)"});
                                },
                                success: function (res) {
                                    if (res.success === true) {
                                        $.LoadingOverlay("hide");
                                        loadInvoices();
                                    } else {
                                        $.LoadingOverlay("hide");
                                        $.alert({title: "Error", type: "red", content: res.message, boxWidth: "350px", useBootstrap: false});
                                    }
                                }
                            });
                        }
                    }
                },
                cerrar: {
                    btnClass: "btn-red",
                    action: function () {}
                }
            },
            content: function () {
                var self = this;
                return $.ajax({url: "/bodega/get/importar-plantilla?idTrafico=" + $("#idTrafico").val(), dataType: "json", method: "GET"
                }).done(function (res) {
                    var html = "";
                    if(res.success === true) {
                        html = res.html;
                    }
                    self.setContent(html);
                }).fail(function () {
                    self.setContent("Something went wrong.");
                });
            }
        });
    });
    
    $(document.body).on("click", "#selectAllInvoices", function () {
        var checkboxes = $("input[class=invoice]");
        if ($(this).is(":checked")) {
            checkboxes.prop("checked", true);
        } else {
            checkboxes.prop("checked", false);
        }
    });
    
    $(document.body).on("click", "#attach", function () {
        $("#filess").click();
    });
    
    $(document.body).on("change", "#commentsForm #filess", function () {
        var filename = $('input[type=file]').val().replace(/C:\\fakepath\\/i, '');
        $("#attachedFiles").append('<img src="/images/icons/attachment.gif"><span style="font-size: 11px">' + filename + '</span>');
    });
    
    $(document.body).on("click", "#addComment", function(ev) {
        ev.preventDefault();
        $(this).prop('disabled', true);
        if ($("#commentsForm").valid()) {
            $("#commentsForm").ajaxSubmit({url: "/bodega/post/agregar-comentario-trafico", dataType: "json",
                beforeSend: function() {
                },
                success: function (res) {
                    if (res.success === true) {
                        $('#attachedFiles').html('');
                        $('#comment').val('');
                        $(this).removeProp('disabled');
                        loadComments();
                    } else {
                        $.alert({title: "Error", type: "red", content: res.message, boxWidth: "250px", useBootstrap: false});
                    }
                }
            });
        }
    });
    
    $(document.body).on("click", ".openFile", function (ev) {
        ev.preventDefault();        
        var id = $(this).data("id");
        var w = window.open("/archivo/get/ver-archivo?id=" + id, "viewFile", "toolbar=0,location=0,menubar=0,height=550,width=880,scrollbars=yes");
        w.focus();
    });
    
    $(document.body).on("click", ".preview", function (ev) {
        ev.preventDefault();
        var id = $(this).data("id");
        var num = $(this).data("num");
        var w =window.open("/bodega/get/vucem-preview?idFactura=" + id, num, "toolbar=0,location=0,menubar=0,height=500,width=880,scrollbars=yes");
        w.focus();
    });
    
    $(document.body).on('change', 'input[name="sello"]', function (ev) {
        
        var idSello = $('input[name="sello"]:checked').val();
        var tipo = $('input[name="sello"]:checked').data('type');
        var idTrafico = $("#idTrafico").val();
        
        establecerSello(idTrafico, idSello, tipo);
        
    });

    $(document.body).on("click", "#sendToVucem", function () {
        var facturas = [];
        var checkboxes = $("input[class=invoice]:checked");
        if ((checkboxes).size() > 0) {
            $(checkboxes).each(function () {
                facturas.push($(this).val());
            });
            $.ajax({url: '/bodega/post/vucem-enviar-facturas', dataType: "json", timeout: 3000, type: "POST",
                data: {idTrafico: $("#idTrafico").val(), facturas: facturas},
                beforeSend: function() {
                    $.LoadingOverlay("show", {color: "rgba(255, 255, 255, 0.9)"});
                },
                success: function (res) {
                    if (res.success === true) {
                        $.LoadingOverlay("hide");
                    } else {
                        $.LoadingOverlay("hide");
                        $.alert({title: "Error", type: "red", content: res.message, boxWidth: "350px", useBootstrap: false});
                    }
                }
            });
        } else {
            $.alert({
                title: "¡Oops!",
                type: "orange",
                content: "No ha seleccionado facturas.",
                escapeKey: true,
                boxWidth: "310px",
                useBootstrap: false
            });
        }
    });
    
    $(document.body).on("click", "#cargarXml", function (ev) {
        var idTrafico = $("#idTrafico").val();
        $.confirm({ title: "Cargar CDFi", escapeKey: "cerrar", boxWidth: "550px", useBootstrap: false, type: "orange",
            buttons: {
                cargar: {btnClass: "btn-orange", action: function () {
                    if ($("#uploadForm").valid()) {
                        $("#uploadForm").ajaxSubmit({url: "/bodega/post/subir-cdfis", type: "POST", dataType: "json",
                            success: function (res) {
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
                return $.ajax({
                    url: "/bodega/get/cargar-xml?idTrafico=" + idTrafico,
                    method: "get"
                }).done(function (res) {
                    self.setContent(res.html);
                }).fail(function () {
                    self.setContent("Something went wrong.");
                });
            }
        });
    });
    
    $(document.body).on("click", "#btn-download", function (ev) {
        ev.preventDefault();
        var id = $(this).data("id");
        $.ajax({url: "/bodega/get/descarga-carpeta-expediente", dataType: "json", type: "GET",
            data: {id: id},
            success: function (res) {
                if (res.success === true) {
                    location.href = "/archivo/get/descargar-carpeta?id=" + res.id;
                }
            }
        });
    });
    
    $(document.body).on("click", ".add-email", function (ev) {
        var id = $(this).data("id");
        if($(this).is(':checked')) {
            emailData["emails"][id] = id;
        } else {
            delete emailData["emails"][id];
        }
    });
    
    $(document.body).on("click", "#template-casa", function (ev) {
        ev.preventDefault();
        var id = $(this).data("id");
        location.href = "/bodega/get/descarga-plantilla-casa?id=" + id;
    });
    
    $(document.body).on("click", "#template-slam", function (ev) {
        ev.preventDefault();
        var id = $(this).data("id");
        location.href = "/bodega/get/descarga-plantilla-slam?id=" + id;
    });

    $("#commentsForm").validate({
        errorPlacement: function (error, element) {
            $(element)
                    .closest("form")
                    .find("#" + element.attr("id"))
                    .after(error);
        },
        errorElement: "span",
        errorClass: "traffic-error",
        rules: {
            comment: {required: true}
        },
        messages: {
            comment: "SE REQUIERE COMENTARIO"
        }
    });
    
    slider = $("#slider").slideReveal({push: false, position: "right", speed: 600, trigger: $(".handle"), overlay: true, width: '380px',
        show: function(slider, trigger){
            $.ajax({url: "/bodega/crud/obtener-fechas", cache: false, type: "GET", dataType: "json", 
                data: {idTrafico: $("#idTrafico").val()}
            }).done(function (res) {
                if (res.success === true) {
                    $.each(res.dates, function (index, value) {
                        if (value !== null) {
                            $("#datesForm #" + index).val(getFormattedDate(value));
                        }
                    });
                }
            });
        }
    });
    
    $("#datesForm input[class=traffic-input-date]").datetimepicker({
        format:'YYYY-MM-DD H:mm',
        formatTime:'H:mm',
        formatDate:'YYYY-MM-DD'
    });
    
    $(document.body).on("click", "#guardar-fechas", function(ev) {
        ev.preventDefault();
        $("#datesForm").ajaxSubmit({url: "/bodega/post/guardar-fechas-entrada", timeout: 3000, dataType: "json", type: "POST",
            success: function (res) {
                if (res.success === true) {
                    $.toast({text: "<strong>Guardado</strong>", bgColor: "green", stack : 3, position : "bottom-right"});
                } else {
                    $.alert({title: "¡Advertencia!", closeIcon: true, backgroundDismiss: true, type: "red", escapeKey: "cerrar", boxWidth: "400px", useBootstrap: false, content: res.message});
                }
            }
        });
    });
    
    $("#form-information").validate({
        errorPlacement: function (error, element) {
            $(element)
                    .closest("form")
                    .find("#" + element.attr("id"))
                    .after(error);
        },
        errorElement: "span",
        errorClass: "traffic-error",
        rules: {
            blGuia: {required: true}
        },
        messages: {
            blGuia: "Campo necesario"
        }
    });
    
    $(document.body).on("click", "#update-traffic", function (ev) {
        ev.preventDefault();
        if ($("#form-information").valid()) {
            $("#form-information").ajaxSubmit({url: "/bodega/post/actualizar-entrada", type: "POST", dataType: "json",
                success: function (res) {
                    if (res.success === true) {
                        $.toast({text: "<strong>Guardado</strong>", bgColor: "green", stack : 3, position : "bottom-right"});
                    }
                }
            });
        } else {
            return false;
        }
    });
    
    $(document.body).on('click', '.remove-traffic', function (ev) {
        ev.preventDefault();
        var id = $(this).data('id');
        $.confirm({ title: "Remover tráfico", escapeKey: "cerrar", boxWidth: "350px", useBootstrap: false, type: "red",
            buttons: {
                si: {btnClass: "btn-red", action: function () {
                    $.ajax({url: "/bodega/post/remover-entrada", dataType: "json", type: "POST",
                        data: {id: id},
                        success: function (res) {
                            if (res.success === true) {
                                loadOther();
                                return true;
                            }
                        }
                    });
                    return false;
                }},
                no: {action: function () {}}
            },
            content: '¿Está seguro que desea remove este trafico del consolidado?'
        });
    });    
    
    $(document.body).on("click", "#help", function (ev) {
        ev.preventDefault();
        $.confirm({title: "Ayuda de prefijos", escapeKey: "cerrar", boxWidth: "710px", useBootstrap: false, type: "blue",
            buttons: {
                imprimir: {
                    btnClass: "btn-blue",
                    action: function () {
                        $(location).attr("href", "/archivo/get/imprimir-prefijos");
                    }
                },
                cerrar: {btnClass: "btn-red", action: function () {}}
            },
            content: function () {
                var self = this;
                return $.ajax({url: "/archivo/get/ayuda-documentos", method: "GET"
                }).done(function (res) {
                    self.setContent(res);
                }).fail(function () {
                    self.setContent("Something went wrong.");
                });
            }
        });
    });  
    
    $(document.body).on("click", ".editar-bulto", function (ev) {
        ev.preventDefault();
        let id = $(this).data('id');
        $.confirm({title: "Editar bulto", escapeKey: "cerrar", boxWidth: "500px", useBootstrap: false, type: "blue",
            buttons: {
                cerrar: {action: function () {}},
                guardar: {
                    btnClass: "btn-blue",
                    action: function () {
                        $("#frmPackage").ajaxSubmit({url: "/bodega/post/guardar-bulto", dataType: "json", type: "POST",
                            success: function (res) {
                                if (res.success === true) {
                                    loadPackages();
                                }
                            }
                        });                        
                    }
                }
            },
            content: function () {
                var self = this;
                return $.ajax({url: "/bodega/get/editar-bulto?id=" + id, method: "GET"
                }).done(function (res) {
                    self.setContent(res.html);
                }).fail(function () {
                    self.setContent("Something went wrong.");
                });
            }
        });
    });  
    
    $(document.body).on("click", ".borrar-bulto", function (ev) {
        ev.preventDefault();
        let id = $(this).data('id');
        
        $.confirm({title: "Borrar bulto", escapeKey: "cerrar", boxWidth: "350px", useBootstrap: false, type: "red",
            buttons: {
                si: {btnClass: "btn-red", action: function () {
                        $.ajax({url: "/bodega/post/borrar-bulto", dataType: "json", timeout: 10000, type: "POST",
                            data: {id: id, idTrafico: $("#idTrafico").val()},
                            success: function (res) {
                                if (res.success === true) {
                                    $("#bultos").val(res.totalBultos);
                                    loadPackages();
                                }
                            }
                        });
                }},
                no: {action: function () {}}
            },
            content: "¿Está seguro que desea borrar esta este bulto?"
        });
    });
    
    var kg = 0.45359237;
    
    $("#pesoLbs").change(function() {
        var pkg = parseFloat($(this).val() * kg).toFixed(4);
        $("#pesoKg").val(pkg);
    });
    
    $("#pesoKg").change(function() {
        var pkg = parseFloat($(this).val() / kg).toFixed(4);
        $("#pesoLbs").val(pkg);
    });
    
    $(document.body).on("click", "#selectAllPackages", function () {
        var checkboxes = $("input[class=package]");
        if ($(this).is(":checked")) {
            checkboxes.prop("checked", true);
        } else {
            checkboxes.prop("checked", false);
        }
    });
    
    let bultos = [];
    
    $(document.body).on("click", "#subdiv", function (ev) {
        ev.preventDefault();
        
        let id = $(this).data('id');
        
        let checkboxes = $("input[class=package]:checked");

        bultos = [];

        if ((checkboxes).size() > 0) {
            
            $(checkboxes).each(function () {
                bultos.push($(this).val());
            });
            
            $.confirm({title: "Subdivir entrada", escapeKey: "cerrar", boxWidth: "500px", useBootstrap: false, type: "blue",
                buttons: {
                    cerrar: {action: function () {}},
                    guardar: {
                        btnClass: "btn-blue",
                        action: function () {

                            let restantes = $("#bultos_restantes").val();

                            $("#subdivision").ajaxSubmit({url: "/bodega/post/subdividir", dataType: "json", type: "POST",
                                success: function (res) {
                                    if (res.success === true) {
                                        $("#bultos").val(restantes);
                                        loadPackages();
                                    }
                                }
                            });
                        }
                    }
                },
                content: function () {
                    let self = this;
                    return $.ajax({url: "/bodega/get/preview-subidivision", method: "GET",
                        data: {id: id, bultos: bultos}
                    }).done(function (res) {
                        self.setContent(res.html);
                    }).fail(function () {
                        self.setContent("Something went wrong.");
                    });
                }
            });
            
        } else {
            $.alert({
                title: "¡Oops!",
                type: "orange",
                content: "No ha seleccionado bultos.",
                escapeKey: true,
                boxWidth: "310px",
                useBootstrap: false
            });
        }
    });

    $(document.body).on("click", "#ctipobulto", function (ev) {
        ev.preventDefault();

        let id = $(this).data('id');

        let checkboxes = $("input[class=package]:checked");

        bultos = [];

        if ((checkboxes).size() > 0) {

            $(checkboxes).each(function () {
                bultos.push($(this).val());
            });

            $.confirm({title: "Cambiar tipo de bulto", escapeKey: "cerrar", boxWidth: "500px", useBootstrap: false, type: "blue",
                buttons: {
                    cerrar: {action: function () {}},
                    guardar: {
                        btnClass: "btn-blue",
                        action: function () {
                            $("#formtipobulto").ajaxSubmit({url: "/bodega/post/cambiar-tipo-bulto", dataType: "json", type: "POST",
                                success: function (res) {
                                    if (res.success === true) {
                                        loadPackages();
                                    }
                                }
                            });
                        }
                    }
                },
                content: function () {
                    let self = this;
                    return $.ajax({url: "/bodega/get/cambiar-tipo-bulto", method: "GET",
                        data: {id: id, bultos: bultos}
                    }).done(function (res) {
                        self.setContent(res.html);
                    }).fail(function () {
                        self.setContent("Something went wrong.");
                    });
                }
            });

        } else {
            $.alert({
                title: "¡Oops!",
                type: "orange",
                content: "No ha seleccionado bultos.",
                escapeKey: true,
                boxWidth: "310px",
                useBootstrap: false
            });
        }
    });
    
});