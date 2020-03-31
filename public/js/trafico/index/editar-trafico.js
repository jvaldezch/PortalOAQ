
let emailData = {};
emailData["emails"] = {};
    
let base = "/trafico/index/editar-trafico";

function importarFactura(idFactura) {
    $.ajax({url: '/trafico/facturas/importar-factura',
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

function crearAdenda(idFactura) {
    $.ajax({url: '/trafico/facturas/crear-adenda',
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

function borrarFactura(id_factura) {
    let id = id_factura;
    $.confirm({title: "Factura", escapeKey: "cerrar", boxWidth: "350px", useBootstrap: false, type: "red",
        buttons: {
            si: {btnClass: "btn-red", action: function () {
                    $.ajax({url: "/trafico/post/borrar-factura", dataType: "json", timeout: 10000, type: "POST",
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
        content: "¿Está seguro que desea borrar está factura?"
    });
}

function borrarImagen(idImage) {
    let r = confirm("¿Está seguro que desea borrar la imagen?");
    if (r === true) {
        $.ajax({url: "/trafico/post/borrar-imagen", dataType: "json", timeout: 3000, data: {id: idImage}, type: "POST",
            success: function (res) {
                if (res.success === true) {
                    loadPhotos();
                }
            }
        });
    }
}

function borrarGuia(idGuia) {
    $.ajax({url: "/trafico/ajax/borrar-guia", dataType: "json", timeout: 10000, data: {idTrafico: $("#idTrafico").val(), idGuia: idGuia}, type: "POST",
        success: function (res) {
            if (res.success === true) {
                loadTrackings();
            }
        }
    });
}

function borrarArchivo(id) {
    let answer = confirm("¿Desea borrar el archivo?");
    if (answer) {
        $.ajax({url: "/trafico/post/borrar-archivo", dataType: "json", cache: false, data: {id: id}, type: "POST",
            success: function (data) {
                loadFiles();
            }
        });
    }
}

function verFactura(idFactura) {
    let w = window.open("/trafico/data/ver-factura?id=" + idFactura, "varFactura", "toolbar=0,location=0,menubar=0,height=550,width=1024,scrollbars=yes");
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
                    $("#formFileType").ajaxSubmit({type: "POST", dataType: "json", timeout: 10000, url: "/trafico/ajax/enviar-edocument",
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
            let self = this;
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
    $.ajax({url: "/trafico/post/enviar-vucem-factura", dataType: "json", type: "POST",
        data: {idTrafico: $("#idTrafico").val(), idFactura: idFactura},
        timeout: 3000
    });
}

function verCove(idFactura, cove) {
    let w = window.open("/trafico/get/ver-edocument?idFactura=" + idFactura + "&idTrafico=" + $("#idTrafico").val() + "&cove=" + cove + "&type=cove", "verCove", "toolbar=0,location=0,menubar=0,height=550,width=850,scrollbars=yes");
    w.focus();
}

function htmlEntities(str) {
    return String(str).replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;");
}

function vucemPreview(id) {
    let w = window.open("/trafico/facturas/vucem-preview?id=" + id + "&idTrafico=" + $("#idTrafico").val(), "previewXml", "toolbar=0,location=0,menubar=0,height=550,width=850,scrollbars=yes");
    w.focus();
}
    
function borrarVucem(id_vucem) {
    let id = id_vucem;
    $.confirm({title: "VUCEM", escapeKey: "cerrar", boxWidth: "350px", useBootstrap: false, type: "red",
        buttons: {
            si: {btnClass: "btn-red", action: function () {
                    $.ajax({url: "/trafico/post/borrar-vucem", dataType: "json", timeout: 10000, type: "POST",
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
    $.ajax({url: "/trafico/get/enviar-vucem", dataType: "json", timeout: 10000, type: "POST",
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
    $.ajax({url: "/trafico/get/consulta-respuesta-vucem", dataType: "json", timeout: 10000, type: "POST",
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
    $.ajax({url: "/trafico/get/consulta-detalle-log", dataType: "json", timeout: 10000, type: "GET",
        data: {id: id},
        success: function (res) {
            if (res.success === true) {
                $(".consultarVucem[data-id=" + id + "]").click(function () {
                    $.ajax({url: "/trafico/get/consulta-respuesta-vucem", dataType: "json", timeout: 10000, type: "GET",
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

window.loadInvoices = function() {
    $.ajax({url: "/trafico/ajax/obtener-facturas", dataType: "json", timeout: 10000, data: {id: $("#idTrafico").val()}, type: "POST",
        success: function (res) {
            if (res.success === true) {
                $("#traffic-invoices").html(res.html);
                contarCovesEdocuments();
            }
        }
    });
};

window.loadTrackings = function() {
    $.ajax({url: "/trafico/ajax/obtener-guias", dataType: "json", timeout: 10000, data: {id: $("#form-tracking #idTrafico").val()}, type: "POST",
        success: function (res) {
            if (res.success === true) {
                $("#traffic-trackings").html(res.html);
            } else {
                $("#traffic-trackings").html("<p><em>No existen guías cargadas.</em></p>");
            }
        }
    });
};

window.loadComments = function() {
    $("#traffic-comments").show();
    $.ajax({url: "/trafico/ajax/cargar-comentarios", dataType: "json", timeout: 10000, type: "POST",
        data: {idTrafico: $("#idTrafico").val()},
        success: function (res) {
            if (res.success === true) {
                if (res.results['bitacora'] !== null) {
                    $("#trafficLog").html('');
                    let rows = res.results['bitacora'];
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
                $("#trafficLog").html('<tr><td colspan="3" style="font-size: 10px !important; line-height: 11px">No hay comentarios</td></tr>');
            }
        }       
    });
};

window.loadRegister = function() {
    $("#traffic-register").show();
    $.ajax({url: "/trafico/ajax/cargar-registros", dataType: "json", timeout: 20000, data: {id: $("#idTrafico").val()}, type: "POST",
        success: function (res) {
            if (res.success === true) {
                $("#traffic-register").html(res.html);
            }
        }        
    });
};

window.loadFiles = function() {
    $('#traffic-files').show();
    $.ajax({url: "/trafico/ajax/cargar-archivos", dataType: "json", timeout: 30000, data: {id: $("#idTrafico").val()}, type: "POST",
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
    $.ajax({url: "/trafico/post/cargar-fotos", dataType: "json", timeout: 30000, type: "POST", data: {id: $("#idTrafico").val()},
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
    return $.ajax({url: "/trafico/get/vucem-guardar", dataType: "json", type: "GET", 
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
    return $.ajax({url: "/trafico/get/vucem-firmas", dataType: "json", type: "POST", 
        data: {idTrafico: $("#idTrafico").val()},
        beforeSend: function (xhr) {
            $("#vucemSignatures").html('');
        },
        success: function (res) {
            if (res.success === true) {
                if (res.results['agente']) {
                    let row = res.results['agente'];
                    for (let i = 0; i < row.length; i++) {
                        $("#vucemSignatures").append('<tr><td><input type="radio" name="sello" data-type="agente" value="' + row[i].id + '" ></td><td>' + row[i].patente + '</td><td>' + row[i].rfc + '</td><td style="text-align: left">' + row[i].razon + '</td><td></td></tr>');                        
                    }
                }
                if (res.results['cliente']) {
                    let row = res.results['cliente'];
                    for (let i = 0; i < row.length; i++) {
                        $("#vucemSignatures").append('<tr><td><input type="radio" name="sello" data-type="cliente" value="' + row[i].id + '" ></td><td></td><td>' + row[i].rfc + '</td><td style="text-align: left">' + row[i].razon + '</td><td></td></tr>');
                    }
                }
                if (res.results['config']) {
                    let row = res.results['config'];
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

                    let idSello = $('input[name="sello"]:checked').val();
                    let tipo = $('input[name="sello"]:checked').data('type');
                    let idTrafico = $("#idTrafico").val();
                    
                    establecerSello(idTrafico, idSello, tipo);
                }
                if (res.sellos === false) {
                    $("#vucemSignatures").append('<tr><td colspan="5"><em>El cliente no tiene sellos para VUCEM.</em></td></tr>');
                }
                if (res.address === false) {
                    $("#vucemSignatures").append('<tr><td colspan="5"><em><strong style="color: red">Advertencia</strong>: El cliente no tiene una dirección física de VUCEM favor de comunicarse con el depto. de Comercialización para que sea asignada.</em></td></tr>');
                }
                return true;
            }
            return false;
        }        
    });
    
};

window.obtenerDefault = function (idCliente) {
    return $.ajax({url: '/trafico/get/obtener-sello-default', type: "GET",
        data: {idCliente: idCliente},
        success: function (res) {
            if (res.success === true) {
                $("input[name=sello][value=" + res.id + "]").prop("checked", true);
            }
        }
    });
};

window.getVucemLog = function() {
    $.ajax({url: "/trafico/get/vucem-bitacora", dataType: "json", type: "POST", 
        data: {idTrafico: $("#idTrafico").val()},
        success: function (res) {
            if (res.success === true) {
                $("#vucemLog").html(res.html);
                contarCovesEdocuments();
            }
        }        
    });
};

window.establecerSello = function(idTrafico, idSello, tipo) {
    return $.ajax({url: "/trafico/post/establecer-sello-vucem", cache: false, dataType: "json", type: "POST",
        data: {idTrafico: idTrafico, idSello: idSello, tipo: tipo},
        success: function (res) {
            if (res.success === true) {
                
            } else {
                $.alert({title: "Advertencia", type: "red", content: res.message, boxWidth: "250px", useBootstrap: false});
            }
        }
    });
};

window.ftp_estatus = function(jsonObj) {
    if (jsonObj.success === true) {
        $.each(jsonObj.results, function(i, item) {
            if (i === 'connected') {
                if (item === true) {
                    $("#ftp_connected").css('color', 'green');
                }
            }
            if (i === 'disconnected') {
                if (item === true) {
                    $("#ftp_connected").css('color', '#999');
                }
            }
            if (i.match(/ftp_file_/g)) {
                if (item === 1) {
                    $("#" + i).css('color', 'green');
                }
                if (item === 4) {
                    $("#" + i).css('color', 'red');
                }
            }
        });
    }
};

window.contarCovesEdocuments = function() {
    $.ajax({url: "/trafico/crud/contar-coves-edocuments", dataType: "json", type: "GET", 
        data: {idTrafico: $("#idTrafico").val()},
        success: function (res) {
            if (res.success === true) {
                
            }
        }        
    });
};

window.subirFactura = function(idFactura) {
    $.confirm({title: "Subir PDF de factura", escapeKey: "cerrar", boxWidth: "450px", useBootstrap: false, type: "blue",
        buttons: {
            subir: {
                btnClass: "btn-blue",
                action: function () {
                    $("#uploadInvoicePdf").ajaxSubmit({url: "/trafico/post/pdf-factura", dataType: "json", timeout: 3000, type: "POST",
                        success: function (res) {
                            if (res.success === true) {
                                loadInvoices();
                            }
                        }
                    });
                }
            },
            cerrar: {
                action: function () {}
            }
        },
        content: function () {
            let self = this;
            return $.ajax({url: "/trafico/get/pdf-factura", dataType: "json", method: "GET",
                data: {idTrafico: $("#idTrafico").val(), idFactura: idFactura}
            }).done(function (res) {
                let html = "";
                if (res.success === true) {
                    html = res.html;
                } else {
                    html = res.message;
                }
                self.setContent(html);
            }).fail(function () {
                self.setContent("Something went wrong.");
            });
        }
    });
};

window.loadSoia = function() {
    
    $('#soia_results tbody').empty();        
    let tableRef = document.getElementById('soia_results').getElementsByTagName('tbody')[0];    
    
    $.ajax({url: "/trafico/get/obtener-soia", cache: false, dataType: "json", data: {id: $("#idTrafico").val()}, type: "GET",
        success: function (res) {
            if (res.success === true) {
                
                $.each(res.result, function(i, item) {
                    let newRow   = tableRef.insertRow();
                    let newCell1  = newRow.insertCell(0);
                    let newText1  = document.createTextNode(item.fecha_pago);
                    newCell1.appendChild(newText1);
                    let newCell2  = newRow.insertCell(1);
                    let newText2  = document.createTextNode(item.hora_pago);
                    newCell2.appendChild(newText2);
                    let newCell3  = newRow.insertCell(2);
                    let newText3  = document.createTextNode(item.semaforo);
                    newCell3.appendChild(newText3);
                    let newCell4  = newRow.insertCell(3);
                    let newText4  = document.createTextNode(item.mensaje);
                    newCell4.appendChild(newText4);
                });
                
            } else {
                let newRow   = tableRef.insertRow();
                let cell  = newRow.insertCell(0);
                let text  = document.createTextNode("No hay datos se SOIA.");
                cell.appendChild(text);
                cell.colSpan = 4;
            }
        }
    });
    
};

function currentActive(current) {

    if(Cookies.get("active") === "#information") {
        loadInvoices();
        loadTrackings();        
        loadComments();
    }
    if(Cookies.get("active") === "#files") {
        loadFiles();     
        loadPhotos();     
    }
    if(Cookies.get("active") === "#soia") {
        loadSoia();
    }
    if(Cookies.get("active") === "#vucem") {
        getVucemLog();
        $.when( getVucemSignatures() ).done(function( res ) {
            if (res.success === true) {
                obtenerDefault($("#idCliente").val());
            }
        });
    }
    if(Cookies.get("active") === "#pedimento-capt") {
        let id = $("#idTrafico").val();
        $.ajax({url: "/trafico/pedimentos/captura-pedimento", cache: false, dataType: "json", data: {id: id}, type: "GET",
            success: function (res) {
                if (res.success === true) {
                    $("#captura-pedimento").html(res.html);
                }
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
            let self = this;
            return $.ajax({url: "/bitacora/get/detalle-guia?idGuia=" + idGuia, dataType: "json", method: "GET"
            }).done(function (res) {
                let html = "";
                if(res.success === true) { html = res.html; }
                self.setContent(html);
            }).fail(function () {
                self.setContent("Something went wrong.");
            });
        }
    });
}

function verificarChecklist() {
    $.ajax({url: "/trafico/get/verificar-checklist", dataType: "json", type: "GET", 
        data: {idTrafico: $("#idTrafico").val()},
        success: function (res) {
            if (res.success === true) {
                $("#estatusChecklist").html(res.status);
            }
        }        
    });
}
    
function mvhcEstatusObtener(id) {
    $.ajax({url: "/trafico/get/mvhc-estatus-obtener", cache: false, dataType: "json", data: {id: id}, type: "GET",
        success: function (res) {
            if (res.success === true) {
                if (parseInt(res.mvhcCliente) === 1) {
                    $("#mvhcCliente").attr("checked", true);
                }
                if (parseInt(res.mvhcFirmado) === 1) {
                    $("#mvhcFirmado").attr("checked", true);
                }
                if (parseInt(res.mvhcEnviada) === 1) {
                    $("#mvhcEnviada").attr("checked", true);
                }
                $("#mvhcGuia").val(res.numGuia);
            }
        }
    });
}

function mvhcEnviada(id, estatus) {
    $.ajax({url: "/trafico/post/mvhc-estatus-enviada", cache: false, dataType: "json", data: {id: id, estatus: estatus}, type: "POST",
        success: function (res) {
            if (res.success === true) {
            }
        }
    });
}

function mvhcEstatus(id, estatus) {
    $.ajax({url: "/trafico/post/mvhc-estatus", cache: false, dataType: "json", data: {id: id, estatus: estatus}, type: "POST",
        success: function (res) {
            if (res.success === true) {
            }
        }
    });
}

window.enviarEmailPermalink = function(id, emailData, uri, ccs) {
    return $.ajax({url: "/trafico/post/enviar-email-permalink", dataType: "json", type: "POST",
        data: {id: id, data: JSON.stringify(emailData), uri: uri, ccs},
        success: function (res) {
            if (res.success === true) {
                return true;
            } else {
                $.alert({title: "Error", content: res.message, type: "red", boxWidth: "350px", useBootstrap: false});
                return false;
            }
        }
    });
};

window.buscarExpedienteIndex = function(idTrafico) {
    return $.ajax({url: "/trafico/get/buscar-expediente-index", dataType: "json", type: "GET",
        data: {id: idTrafico},
        success: function (res) {
            if (res.success === true) {
                $("#idRepositorio").val(res.id);
                $("a#archive")
                        .attr("href", "/archivo/index/expediente?id=" + res.id)
                        .show();
                return true;
            }
        }
    });    
};

let jc;

$(document).ready(function () {
            
    let valid = ["#information", "#files", "#vucem", "#soia", "#pedimento-capt", "#other"];

    $(document.body).on("click", "#traffic-tabs li a", function() {
        let href = $(this).attr("href");
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

    $(document.body).on("click", "#save-traffic",function () {
        let form1 = $("#form-additional");
        $.ajax({url: "/trafico/ajax/guardar-trafico", dataType: "json", timeout: 10000, type: "POST", data: {pedimento: form1.serialize()}
        });
    });

    $("#horaRecepcionDocs").timepicker({"step": 15, "timeFormat": "h:i A"});

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
            $("#form-invoice").ajaxSubmit({url: "/trafico/ajax/agregar-factura", dataType: "json", timeout: 3000, type: "POST",
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

    $("#add-tracking-number").one("click", function (ev) {
        ev.preventDefault();
        $(this).prop("disabled", true)
                .addClass("disabled");
        if ($("#form-tracking").valid()) {
            $("#form-tracking").ajaxSubmit({
                url: "/trafico/ajax/agregar-guia",
                type: "post",
                dataType: "json",
                timeout: 3000,
                success: function (res) {
                    if (res.success === true) {
                        loadTrackings();
                    }
                }
            });
        } else {
            $(this).removeProp("disabled")
                    .removeClass("disabled");
        }
    });

    $(document).on("input", "#numFactura, #proveedor, #number, #comment, #contenedorCaja, #nombreBuque, #placas, #ordenCompra, #candados", function() {
        let input = $(this);
        let start = input[0].selectionStart;
        $(this).val(function (_, val) {
            return val.toUpperCase();
        });
        input[0].selectionStart = input[0].selectionEnd = start;
    });
    
    function revisarSolicitudAnticipo() {
        $.post("/trafico/ajax/revisar-solicitud", {idTrafico: $("#idTrafico").val()}, function (res) {
            if (res.success === true) {
                if (res.aduana) {
                    $("#traffic-view-request").show()
                            .attr("href", "/trafico/index/editar-solicitud?id=" + res.id + "&aduana=" + res.aduana);
                } else {
                    $("#traffic-view-request").show()
                            .attr("href", "/trafico/index/ver-solicitud?id=" + res.id);
                }
            } else {
                $("#traffic-request").show();
            }
        });
    }

    $(document.body).on("click", "#traffic-request",function (ev) {
        ev.preventDefault();
        $.confirm({
            title: "Solicitud de anticipo", type: "green", content: '¿Está seguro de que desea crear una nueva solicitud?', escapeKey: "cerrar", boxWidth: "350px", useBootstrap: false,
            buttons: {
                si: {
                    btnClass: "btn-blue",
                    action: function () {
                        $.ajax({url: "/trafico/ajax/solicitud-desde-trafico", cache: false, type: "POST", dataType: "json", data: {idTrafico: $("#idTrafico").val()}
                        }).done(function (res) {
                            if (res.success === true) {
                                window.location.href = "/trafico/index/editar-solicitud?id=" + res.id + "&aduana=" + res.aduana;
                            } else {
                                $.alert({title: "Error", type: "red", content: res.message, boxWidth: "250px", useBootstrap: false});
                            }
                        });
                    }
                },
                no: function () {}
            }
        });
    });

    $(document.body).on("click", "#mn-slam",function (e) {
        e.preventDefault();
        let id = $(this).data("id");
        $.confirm({title: "Confirmación", escapeKey: "cerrar", boxWidth: "350px", useBootstrap: false, type: "blue",
            buttons: {
                si: {
                    btnClass: "btn-blue",
                    action: function () {
                        $.ajax({url: "/trafico/get/actualizar-desde-servicio", cache: false, type: "GET",dataType: "json", data: {id: id, sistema: 'slam'},
                            beforeSend: function() {
                                $.LoadingOverlay("show", {color: "rgba(255, 255, 255, 0.9)"});                   
                            },
                            success: function (res) {
                                $.LoadingOverlay("hide");
                                if (res.success === true) {
                                    loadInvoices();
                                    loadTrackings();
                                    loadRegister();
                                } else {
                                    $.alert({title: "Error", type: "red", content: res.message, boxWidth: "250px", useBootstrap: false});
                                }                                
                            }
                        });
                    }
                },
                no: {
                    action: function () {}
                }
            },
            content: "¿Esta seguro que desea continuar? Algunos datos pueden ser reeamplazados."
        });
    });
    
    $(document.body).on("click", "#mn-casa",function (e) {
        e.preventDefault();
        let id = $(this).data("id");
        $.confirm({title: "Confirmación", escapeKey: "cerrar", boxWidth: "350px", useBootstrap: false, type: "blue",
            buttons: {
                si: {
                    btnClass: "btn-blue",
                    action: function () {
                        $.ajax({url: "/trafico/get/actualizar-desde-servicio", cache: false, type: "GET",dataType: "json", data: {id: id, sistema: 'casa'},
                            beforeSend: function() {
                                $.LoadingOverlay("show", {color: "rgba(255, 255, 255, 0.9)"});                   
                            },
                            success: function (res) {
                                $.LoadingOverlay("hide");
                                if (res.success === true) {
                                    loadInvoices();
                                    loadTrackings();
                                    loadRegister();
                                } else {
                                    $.alert({title: "Error", type: "red", content: res.message, boxWidth: "250px", useBootstrap: false});
                                }                                
                            }
                        });
                    }
                },
                no: {
                    action: function () {}
                }
            },
            content: "¿Esta seguro que desea continuar? Algunos datos pueden ser reeamplazados."
        });
    });
    
    $(document.body).on("click", "#update-traffic",function () {
        $.confirm({title: "Confirmación", escapeKey: "cerrar", boxWidth: "350px", useBootstrap: false, type: "blue",
            buttons: {
                si: {
                    btnClass: "btn-blue",
                    action: function () {
                        $.ajax({url: "/trafico/get/actualizar-desde-sistema", cache: false, type: "GET",dataType: "json", data: {id: $("#idTrafico").val()},
                            beforeSend: function() {
                                $.LoadingOverlay("show", {color: "rgba(255, 255, 255, 0.9)"});                   
                            },
                            success: function (res) {
                                $.LoadingOverlay("hide");
                                if (res.success === true) {
                                    loadInvoices();
                                    loadTrackings();
                                    loadRegister();
                                } else {
                                    $.alert({title: "Error", type: "red", content: res.message, boxWidth: "250px", useBootstrap: false});
                                }                                
                            }
                        });
                    }
                },
                no: {
                    action: function () {}
                }
            },
            content: "¿Esta seguro que desea continuar? Algunos datos pueden ser reeamplazados."
        });
    });

    let bar = $(".barImage");
    let percent = $(".percentImage");
    
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

    $(document.body).on("click","#deleteTraffic",function (e) {
        let r = confirm("¿Está seguro que desea eliminar el trafico?");
        if (r === true) {
            $.ajax({url: "/trafico/post/borrar-trafico", cache: false, type: "POST", dataType: "json", data: {id: $("#idTrafico").val()},
                beforeSend: function() {                 
                },
                success: function(res) {
                    if(res.success === true) {
                        window.location.href = "/trafico/index/traficos";
                    } else {
                        alert(res.message);
                    }
                }
            });
        }
    });
    
    $(document.body).on("click",".image-link",function (ev) {
        ev.preventDefault();
        let w = window.open("/trafico/data/read-image?id=" + $(this).data("id"), 'Trafico Image ' + $(this).data("id"), 'toolbar=0,location=0,menubar=0,height=750,width=950,scrollbars=yes');
        w.focus();
        return false;
    });
    
    $(document.body).on("click","#uploadImage",function (ev) {
        ev.preventDefault();
        if ($("#formPhotos").valid()) {
            $("#formPhotos").ajaxSubmit({url: "/trafico/post/cargar-imagenes",
                beforeSend: function () {
                    let percentVal = "0%";
                    bar.width(percentVal);
                    percent.html(percentVal);
                },
                uploadProgress: function (event, position, total, percentComplete) {
                    let percentVal = percentComplete + "%";
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

    $(document.body).on("click", "#checklist", function (ev) {
        ev.preventDefault();
        $.confirm({title: "Checklist de intregración de expediente", escapeKey: "cerrar", boxWidth: "850px", useBootstrap: false,
            buttons: {
                imprimir: {
                    action: function () {
                        window.location.href = "/archivo/get/imprimir-checklist?idTrafico=" + $("#idTrafico").val();
                    }
                },
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
                let self = this;
                return $.ajax({url: "/archivo/post/checklist", dataType: "json", method: "POST",
                    data: {idTrafico: $("#idTrafico").val()}
                }).done(function (res) {
                    let html = "";
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

    $(document.body).on("click", "#view-as-customer", function (ev) {
        ev.preventDefault();
        $.confirm({title: "Vista de expediente (cliente)", escapeKey: "cerrar", boxWidth: "850px", useBootstrap: false,
            buttons: {
                cerrar: {
                    btnClass: "btn-red",
                    action: function () {}
                }
            },
            content: function () {
                let self = this;
                return $.ajax({url: "/archivo/get/vista-previa", dataType: "json", method: "POST",
                    data: {idTrafico: $("#idTrafico").val()}
                }).done(function (res) {
                    let html = "";
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

    $(document.body).on("click", "#custom-upload", function (ev) {
        ev.preventDefault();
        $.confirm({title: "Subir expedientes", escapeKey: "cerrar", boxWidth: "850px", useBootstrap: false,
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
                        $("#formChecklist").ajaxSubmit({url: "/trafico/get/subir-archivos", dataType: "json", type: "GET",
                            success: function (res) {
                                if (res.success === true) {
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
                let self = this;
                return $.ajax({url: "/archivo/post/checklist", dataType: "json", method: "POST",
                    data: {idTrafico: $("#idTrafico").val()}
                }).done(function (res) {
                    let html = "";
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
    
    $("#getInvoices").qtip({ // Grab some elements to apply the tooltip to
        content: {
            text: "Cargar facturas desde sistema de pedimentos."
        }
    });
    
    $(document.body).on("click", ".editInvoice", function (ev) {
        let w = window.open("/trafico/facturas/editar-factura?idFactura=" + $(this).data("id"), 'editarFactura', 'toolbar=0,location=0,menubar=0,height=750,width=950,scrollbars=yes');
        w.focus();
        return false;
    });
    
    $(document.body).on("click", "#getInvoices", function () {
        $.confirm({title: "Facturas de pedimento", escapeKey: "cerrar", boxWidth: "650px", useBootstrap: false,
            buttons: {
                seleccionar: {
                    btnClass: "btn-blue",
                    action: function () {
                        let facturas = [];
                        let boxes = $("input[class=invoice]:checked");
                        if ((boxes).size() > 0) {
                            $(boxes).each(function () {
                                facturas.push($(this).data("factura"));
                            });
                            $.post("/trafico/post/seleccionar-facturas", {idTrafico: $("#idTrafico").val(), facturas: facturas})
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
                let self = this;
                return $.ajax({url: "/trafico/get/facturas-pedimento?idTrafico=" + $("#idTrafico").val(), dataType: "json", method: "GET"
                }).done(function (res) {
                    let html = "";
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
                            $("#formTemplate").ajaxSubmit({url: "/trafico/post/subir-plantilla", dataType: "json", type: "POST",
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
                let self = this;
                return $.ajax({url: "/trafico/get/importar-plantilla?idTrafico=" + $("#idTrafico").val(), dataType: "json", method: "GET"
                }).done(function (res) {
                    let html = "";
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
        let checkboxes = $("input[class=invoice]");
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
        let filename = $('input[type=file]').val().replace(/C:\\fakepath\\/i, '');
        $("#attachedFiles").append('<img src="/images/icons/attachment.gif"><span style="font-size: 11px">' + filename + '</span>');
    });
    
    $(document.body).on("click", "#addComment", function(ev) {
        ev.preventDefault();
        $(this).prop('disabled', true);
        if ($("#commentsForm").valid()) {
            $("#commentsForm").ajaxSubmit({url: "/trafico/post/agregar-comentario-trafico", dataType: "json",
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
    
    $(document.body).on("click", "#semaforo", function () {
        $.confirm({title: 'Semáforo', closeIcon: true, backgroundDismiss: true, type: 'dark', typeAnimated: true, escapeKey: "cerrar", boxWidth: "350px", useBootstrap: false,
            buttons: {
                guardar: {
                    btnClass: "btn-green",
                    action: function () {
                        if ($("#formSemaphore").valid()) {
                            $("#formSemaphore").ajaxSubmit({type: "POST", dataType: "json",
                                success: function (res) {
                                    if (res.success === true) {
                                    } else {
                                        $.alert({title: "Error", type: "red", content: res.message, boxWidth: "250px", useBootstrap: false});
                                    }
                                }
                            });
                        } else {
                            return false;
                        }
                    }
                },
                cerrar: {
                    action: function () {}
                }
            },
            content: function () {
                let self = this;
                return $.ajax({url: "/trafico/get/semaforo?idTrafico=" + $("#idTrafico").val(), dataType: "json", method: "GET"
                }).done(function (res) {
                    let html = "";
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
    
    $(document.body).on("click", "#ordenRemision", function (ev) {
        ev.preventDefault();
        let id = $(this).data("id");
        $.confirm({ closeIcon: true, backgroundDismiss: true, title: 'Generar orden de remisión', type: 'dark', typeAnimated: true, escapeKey: "cerrar", boxWidth: "550px", useBootstrap: false,
            buttons: {
                guardar: {
                    btnClass: "btn-green",
                    action: function () {
                        if ($("#formOrder").valid()) {
                            $("#formOrder").ajaxSubmit({type: "POST", dataType: "json",
                                success: function (res) {
                                    if (res.success === true) {
                                        let win = window.open("/trafico/get/imprimir-orden-de-remision?idTrafico=" + id + "&idRemision=" + res.id, '_blank');
                                        win.focus();
                                    } else {
                                        $.alert({title: "Error", type: "red", content: res.message, boxWidth: "250px", useBootstrap: false});
                                    }
                                }
                            });
                        } else {
                            return false;
                        }
                    }
                },
                cerrar: {
                    action: function () {}
                }
            },
            content: function () {
                let self = this;
                return $.ajax({url: "/trafico/get/orden-remision?idTrafico=" + id, dataType: "json", method: "get"
                }).done(function (res) {
                    let html = "";
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
    
    $(document.body).on("click", ".openFile", function (ev) {
        ev.preventDefault();
        let id = $(this).data("id");
        let w = window.open("/archivo/get/ver-archivo?id=" + id, "viewFile", "toolbar=0,location=0,menubar=0,height=550,width=880,scrollbars=yes");
        w.focus();
    });

    $(document.body).on("click", "#xml-pedimento", function (ev) {
        ev.preventDefault();
        let idTrafico = $("#idTrafico").val();
        $.ajax({url: "/trafico/pedimentos/descarga", cache: false, dataType: "json", data: {idTrafico: idTrafico}, type: "GET",
            success: function (res) {
                if (res.success === true) {
                    
                } else {
                    $.alert({title: "Error", type: "red", content: res.message, boxWidth: "250px", useBootstrap: false});
                }
            }
        });
    });
    
    $(document.body).on("click", "#justificar", function (ev) {
        ev.preventDefault();
        let id = $(this).data("id");
        $.ajax({url: "/trafico/post/trafico-justificar", cache: false, dataType: "json", data: {id: id}, type: "POST",
            success: function (res) {
                if (res.success === true) {
                    $.alert({title: "Confirmación", type: "green", content: "Los datos han sido guardados de manera exitosa.", boxWidth: "350px", useBootstrap: false});
                }
            }
        });
    });

    $(document.body).on("click", "#desjustificar", function (ev) {
        ev.preventDefault();
        let id = $(this).data("id");
        $.ajax({url: "/trafico/post/trafico-desjustificar", cache: false, dataType: "json", data: {id: id}, type: "POST",
            success: function (res) {
                if (res.success === true) {
                    $.alert({title: "Confirmación", type: "green", content: "Los datos han sido guardados de manera exitosa.", boxWidth: "350px", useBootstrap: false});
                }
            }
        });
    });

    $(document.body).on("click", "#envioDocumentos", function (ev) {
        ev.preventDefault();
        let id = $(this).data("id");
        $.ajax({url: "/trafico/post/trafico-documentos-completos", cache: false, dataType: "json", type: "POST",
            data: {id: id}, 
            success: function (res) {
                if (res.success === true) {
                    $.alert({title: "Confirmación", type: "green", content: "Los datos han sido guardados de manera exitosa.", boxWidth: "350px", useBootstrap: false});
                }
            }
        });
    });
    
    $(document.body).on("click", ".preview", function (ev) {
        ev.preventDefault();
        let id = $(this).data("id");
        let num = $(this).data("num");
        let w =window.open("/trafico/get/vucem-preview?idFactura=" + id, num, "toolbar=0,location=0,menubar=0,height=500,width=880,scrollbars=yes");
        w.focus();
    });
    
    $(document.body).on('change', 'input[name="sello"]', function (ev) {
        let idSello = $('input[name="sello"]:checked').val();
        let tipo = $('input[name="sello"]:checked').data('type');
        let idTrafico = $("#idTrafico").val();        
        establecerSello(idTrafico, idSello, tipo);        
    });
    
    $("#loadInvoices").qtip({ // Grab some elements to apply the tooltip to
        content: {
            text: "Actualizar el listado de facturas."
        }
    });

    $("#sendToVucem").qtip({
        content: {
            text: "Enviar a VUCEM."
        }
    });

    $(document.body).on("click", "#sendToVucem", function () {
        let facturas = [];
        let checkboxes = $("input[class=invoice]:checked");
        if ((checkboxes).size() > 0) {
            $(checkboxes).each(function () {
                facturas.push($(this).val());
            });
            $.ajax({url: '/trafico/post/vucem-enviar-facturas', dataType: "json", timeout: 3000, type: "POST",
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
    
    $(document.body).on("click", "#sendEmail", function (ev) {
        let id = $(this).data("id");
        $.confirm({ title: "Enviar email", escapeKey: "cerrar", boxWidth: "550px", useBootstrap: false, type: "blue",
            buttons: {
                enviar: {btnClass: "btn-blue", action: function () {
                    if (jQuery.isEmptyObject(emailData["archivos"])) {
                        $.alert({title: "Advertencia", content: 'No ha seleccionado archivos para enviar.', type: "red", boxWidth: "350px", useBootstrap: false});
                        return false;
                    }
                    if (jQuery.isEmptyObject(emailData["emails"])) {
                        $.alert({title: "Advertencia", content: 'No ha seleccionado o no existen contactos para enviar.', type: "red", boxWidth: "350px", useBootstrap: false});
                        return false;
                    }
                    $.ajax({url: "/trafico/post/enviar-email", dataType: "json", type: "POST",
                        data: {id: id, data: JSON.stringify(emailData)},
                        success: function (res) {
                            if (res.success === true) {
                                return true;
                            } else {
                                $.alert({title: "Error", content: res.message, type: "red", boxWidth: "450px", useBootstrap: false});
                            }
                        }
                    });
                }},
                cerrar: {action: function () {}}
            },
            content: function () {
                let self = this;
                return $.ajax({
                    url: "/trafico/get/enviar-email?id=" + id,
                    method: "get"
                }).done(function (res) {
                    self.setContent(res);
                }).fail(function () {
                    self.setContent("Something went wrong.");
                });
            }
        });
    });
    
    $(document.body).on("click", "#cargarXml", function (ev) {
        let idTrafico = $("#idTrafico").val();
        $.confirm({ title: "Cargar CDFi", escapeKey: "cerrar", boxWidth: "550px", useBootstrap: false, type: "orange",
            buttons: {
                cargar: {btnClass: "btn-orange", action: function () {
                    if ($("#uploadForm").valid()) {
                        $("#uploadForm").ajaxSubmit({url: "/trafico/post/subir-cdfis", type: "POST", dataType: "json",
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
                let self = this;
                return $.ajax({
                    url: "/trafico/get/cargar-xml?idTrafico=" + idTrafico,
                    method: "get"
                }).done(function (res) {
                    self.setContent(res.html);
                }).fail(function () {
                    self.setContent("Something went wrong.");
                });
            }
        });
    });
    
    $(document.body).on("click", "#mvhcEnviada", function (ev) {
        let id = $(this).data("id");
        if ($(this).is(':checked')) {
            mvhcEnviada(id, 1);
        } else {
            mvhcEnviada(id, 0);
        }
    });
    
    $(document.body).on("click", "#mvhcCliente", function (ev) {
        let id = $(this).data("id");
        if ($(this).is(':checked')) {
            mvhcEstatus(id, 1);
        } else {
            mvhcEstatus(id, 0);
        }
    });

    $(document.body).on("click", "#mvhcFirmado", function (ev) {
        let id = $(this).data("id");
        if ($(this).is(':checked')) {
            mvhcEstatus(id, 2);
        } else {
            mvhcEstatus(id, 1);
        }
    });
    
    $(document.body).on("click", "#btn-download", function (ev) {
        ev.preventDefault();
        let id = $(this).data("id");
        $.ajax({url: "/trafico/get/descarga-carpeta-expediente", dataType: "json", type: "GET",
            data: {id: id},
            success: function (res) {
                if (res.success === true) {
                    location.href = "/archivo/get/descargar-carpeta?id=" + res.id;
                }
            }
        });
    });
    
    $(document.body).on("click", ".add-email", function (ev) {
        let id = $(this).data("id");
        if($(this).is(':checked')) {
            emailData["emails"][id] = id;
        } else {
            delete emailData["emails"][id];
        }
    });
    
    $(document.body).on("click", "#btn-permalink", function (ev) {
        ev.preventDefault();
        let id = $(this).data("id");
        $.confirm({ title: "Permalink", escapeKey: "cerrar", boxWidth: "660px", useBootstrap: false, type: "green",
            buttons: {
                enviar: {btnClass: "btn-green", action: function () {
                    $.when(enviarEmailPermalink(id, emailData, $("#permalinkUri").val(), $("#ccs").val())).done(function(ra){
                        if (ra.success === true) {
                            return true;
                        }
                    });
                    return false;                        
                }},
                cerrar: {action: function () {}}
            },
            content: function () {
                let self = this;
                return $.ajax({ url: "/trafico/get/permalink-trafico?id=" + id, method: "GET"
                }).done(function (res) {
                    self.setContent(res);
                }).fail(function () {
                    self.setContent("Something went wrong.");
                });
            }
        });
    });
    
    $(document.body).on("click", "#template-casa", function (ev) {
        ev.preventDefault();
        let id = $(this).data("id");
        location.href = "/trafico/get/descarga-plantilla-casa?id=" + id;
    });
    
    $(document.body).on("click", "#template-slam", function (ev) {
        ev.preventDefault();
        let id = $(this).data("id");
        location.href = "/trafico/get/descarga-plantilla-slam?id=" + id;
    });
    
    
    $(document.body).on("click", "#saveMvhcNumGuia", function (ev) {
        ev.preventDefault();

        let id = $(this).data("id");
        let mvhcGuia = $("#mvhcGuia").val();
        
        $.ajax({url: "/trafico/post/mvhc-num-guia", cache: false, dataType: "json", data: {id: id, mvhcGuia: mvhcGuia}, type: "POST",
            success: function (res) {
                if (res.success === true) {
                }
            }
        });
        
    });
    
    $(document.body).on("click", "#saveTraffic", function (ev) {
        ev.preventDefault();

        let idTrafico = $(this).data("id");
        let contenedorCaja = $("#contenedorCaja").val();
        let nombreBuque = $("#nombreBuque").val();
        let placas = $("#placas").val();
        let ordenCompra = $("#ordenCompra").val();
        let candados = $("#candados").val();
        let tipoCarga = $("#tipoCarga").val();
        
        $.ajax({url: "/trafico/post/modificar-trafico", cache: false, dataType: "json", type: "POST",
            data: {idTrafico: idTrafico, contenedorCaja: contenedorCaja, nombreBuque: nombreBuque, placas: placas, ordenCompra: ordenCompra, candados: candados, tipoCarga: tipoCarga},
            success: function (res) {
                if (res.success === true) {
                    $.toast({text: "<strong>Guardado</strong>", bgColor: "green", stack : 3, position : "bottom-right"});
                    loadComments();
                }
            }
        });
        
    });
    
    $(document.body).on("click", "#soia", function (ev) {
        ev.preventDefault();
        let id = $(this).data("id");
        $.confirm({ title: "Estatus SOIA", escapeKey: "cerrar", boxWidth: "660px", useBootstrap: false, type: "green",
            buttons: {
                cerrar: {btnClass: "btn-red",action: function () {}}
            },
            content: function () {
                let self = this;
                return $.ajax({ url: "/trafico/get/soia?id=" + id, method: "GET"
                }).done(function (res) {
                    self.setContent(res);
                }).fail(function () {
                    self.setContent("Something went wrong.");
                });
            }
        });
    });
    
    $("#help").qtip({        
        position: {
            at: 'bottom right'
        },
        content: {
            text: "Mostrar la ayuda para los prefijos del sistema."
        }
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
                let self = this;
                return $.ajax({url: "/archivo/get/ayuda-documentos", method: "GET"
                }).done(function (res) {
                    self.setContent(res);
                }).fail(function () {
                    self.setContent("Something went wrong.");
                });
            }
        });
    });
    
    $(document.body).on("click", "#enviarFtp", function () {
        let id = $(this).data("id");
        jc = $.confirm({title: "FTP",
            escapeKey: "cerrar", boxWidth: "650px", useBootstrap: false, type: "blue",
            onContentReady: function () {
                let self = this;
                //this.buttons.enviar.disable();
            },
            buttons: {
                enviar: {
                    btnClass: "btn-blue",
                    action: function () {
                        
                        let self = this;
                        this.buttons.enviar.disable();
                        
                        $("#ftp_working").show();
                        
                        let ftp_result = document.getElementById("ftp_result");
                        
                        xhr = new XMLHttpRequest();
                        xhr.open("GET", "/automatizacion/ftp/envio-manual?id=" + id, true);
                        
                        xhr.onprogress = function(e) {
                            
                            let ks = e.currentTarget.responseText.split("\n");
                            if (ks.length === 1) {
                                let item = $.parseJSON(ks[0]);
                                ftp_estatus(item);
                            }
                            if (ks.length > 1) {
                                let item = $.parseJSON(ks[ks.length - 2]);
                                ftp_estatus(item);
                            }
                            
                        };
                        xhr.onreadystatechange = function() {
                            if (xhr.readyState === 4) {
                                let ks = xhr.responseText.split("\n");
                                let item = $.parseJSON(ks[ks.length - 2]);
                                ftp_estatus(item);
                                
                                $("#ftp_working").hide();
                                this.buttons.enviar.enable();
                            }
                        };
                        xhr.send();
                        
                        
                        return false;
                    }
                },
                cerrar: function () {}
            },
            content: function () {
                let self = this;
                return $.ajax({url: "/trafico/get/enviar-ftp", dataType: "json", method: "GET",
                    data: {id: id}
                }).done(function (res) {
                    let html = "";
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
    
    verificarChecklist();
    
    revisarSolicitudAnticipo();
    
    contarCovesEdocuments();
    
    mvhcEstatusObtener($("#idTrafico").val());
    
    if ($("#idRepositorio").val() === "") {
        buscarExpedienteIndex($("#idTrafico").val());
    }
    
    $(document.body).on("click", ".editInvoideData", function () {
        let id = $(this).data("id");
        $.confirm({title: "Editar factura original", escapeKey: "cerrar", boxWidth: "450px", useBootstrap: false, type: "blue",
            buttons: {
                guardar: {
                    btnClass: "btn-blue",
                    action: function () {                        
                        $("#editInvoicePdf").ajaxSubmit({url: "/trafico/post/guardar-factura-original", dataType: "json", timeout: 3000, type: "POST",
                            success: function (res) {
                                if (res.success === true) {
                                    $.toast({text: "<strong>Guardado</strong>", bgColor: "green", stack : 3, position : "bottom-right"});
                                }
                            }
                        });
                    }
                },
                cerrar: {
                    action: function () {}
                }
            },
            content: function () {
                let self = this;
                return $.ajax({url: "/trafico/get/editar-factura-original", dataType: "json", method: "GET",
                    data: {idTrafico: $("#idTrafico").val(), id: id}
                }).done(function (res) {
                    let html = "";
                    if (res.success === true) {
                        html = res.html;
                    } else {
                        html = res.message;
                    }
                    self.setContent(html);
                }).fail(function () {
                    self.setContent("Something went wrong.");
                });
            }
        });
    });

    function mensajeAlerta(mensaje) {
        $.alert({title: "Alerta", type: "red", typeAnimated: true, useBootstrap: false, boxWidth: "250px",
            content: mensaje
        });
    }

    $(document.body).on("click", ".send-multiple", function () {
        let ids = [];
        let boxes = $("input[class=checkvucem]:checked");
        if ((boxes).size() === 0) {
            mensajeAlerta('Usted no ha seleccionado nada.');
        }
        if ((boxes).size() > 0) {
            $(boxes).each(function () {
                ids.push($(this).data('id'));
            });
            $.confirm({title: "Enviar a VUCEM", escapeKey: "cerrar", boxWidth: "450px", useBootstrap: false, type: "blue",
                buttons: {
                    confirmar: {
                        btnClass: "btn-blue",
                        action: function () {
                            ids.forEach(function(id) {

                                if ($('.vucem-send[data-id=' + id + ']')) {
                                    setTimeout(function () {
                                        enviarAVucem(id)
                                    }, 3000);
                                }
                                if ($('.vucem-request[data-id=' + id + ']')) {
                                    setTimeout(function () {
                                        consultarVucem(id)
                                    }, 3000);
                                }

                            });
                        }
                    },
                    cerrar: {
                        action: function () {}
                    }
                },
                content: function () {
                    let self = this;
                    return $.ajax({url: "/trafico/get/vucem-enviar-multiple", dataType: "json", method: "GET",
                        data: {idTrafico: $("#idTrafico").val(), ids: ids}
                    }).done(function (res) {
                        let html = "";
                        if (res.success === true) {
                            html = res.html;
                        } else {
                            html = res.message;
                        }
                        self.setContent(html);
                    }).fail(function () {
                        self.setContent("Something went wrong.");
                    });
                }
            });

        }
    });

    $('#fechaEta, #fechaEnvioDocumentos, #fechaVistoBueno, #fechaRevalidacion, #fechaPrevio, #fechaEtaAlmacen').datetimepicker({
        language: 'es',
        autoclose: true,
    });
    
});