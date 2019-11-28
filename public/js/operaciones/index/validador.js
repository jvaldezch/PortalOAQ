
$(document).ready(function () {

    if (typeof (Worker) !== "undefined") {
        console.log("Workers supported");
    } else {
        console.log("Workers not supported");
    }

    $("#file-content").linedtextarea();
    
    $("#form-validation").validate({
        errorPlacement: function (error, element) {
            $(element)
                    .closest("form")
                    .find("label[for='" + element.attr("id") + "']")
                    .append(error);
        },
        errorElement: "span",
        errorClass: "traffic-error",
        rules: {
            patente: "required",
            aduana: "required"
        },
        messages: {
            patente: "Seleccione patente.",
            aduana: "Seleccione aduana."
        }
    });

    $(document.body).on('click', '#load-files', function (e) {
        e.preventDefault();
        if ($("#form-validation").valid()) {
            $("#form-validation").ajaxSubmit({cache: false, type: "POST", dataType: "json",
                url: "/operaciones/validador/obtener-archivos-validacion",
                success: function (res) {
                    if (res.success === true) {
                        $("#archivos").html(res.archivos);
                        $("#pagos").html(res.pagos);
                    } else {
                        $.alert({title: "Advertencia", type: "orange", content: res.message, boxWidth: "350px", useBootstrap: false});
                    }
                },
                complete: function() {
                    contenidoValidador($("#patente").val(), $("#aduana").val());
                }
            });
        }
    });
    
    $(document.body).on('click', '#uploadFile', function (e) {
        if ($('#patente').val() === '' && $('#aduana').val() === '') {
            $.alert({title: "Error", content: "Debe seleccionar patente y aduana.", type: "red", boxWidth: "300px", useBootstrap: false});
            return false;
        }
        e.preventDefault();
        $.confirm({ title: "Subir archivo M3", escapeKey: "cerrar", boxWidth: "400px", useBootstrap: false, type: "green",
            buttons: {
                subir: {btnClass: "btn-green", action: function () {
                        if ($("#fileUpload").valid()) {
                            $("#fileUpload").ajaxSubmit({url: "/operaciones/post/subir-archivo", cache: false, dataType: "json", type: "POST",
                                success: function (res) {
                                    contenidoValidador($("#patente").val(), $("#aduana").val());
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
                return $.ajax({url: "/operaciones/get/subir-archivo", method: "GET", data: {patente: $('#patente').val(), aduana:$('#aduana').val()}
                }).done(function (res) {
                    self.setContent(res);
                }).fail(function () {
                    self.setContent("Something went wrong.");
                });
            }
        });
    });
    
    $(document.body).on('click', '#search-files', function (e) {
        e.preventDefault();
        $.ajax({url: "/operaciones/validador/buscar-archivo", cache: false, dataType: "json", type: "POST",
            data: {search: $("#search").val()},
            success: function (res) {
                if(res.success === true) {
                    if(res.validacion === true) {
                        contenidoArchivo(res.id);
                    } else {
                        contenidoArchivoPago(res.id);
                    }
                } else {
                    $.alert({title: "Error", type: "red", content: "No se encontro el archivo", boxWidth: "350px", useBootstrap: false});
                }
            }
        });
    });

    $(document.body).on('change', '#patente', function (e) {
        $.ajax({url: "/operaciones/data/get-customs", cache: false, dataType: "json", type: "POST",
            data: {patente: $(this).val()}
        }).done(function (data) {
            if (data.success === true) {
                $("#divcustoms").html(data.html);
            }
        });
    });
});

function enviar(id) {
    console.log(id);
}

function contenidoArchivo(id) {
    $.ajax({url: "/operaciones/validador/contenido-archivo", cache: false, dataType: "json", type: "POST",
        data: {id: id},
        beforeSend: function() {
            $("#worker-status").html("");
            $(".content-row").removeClass("content-row-active");
            $("#m3_" + id).addClass("content-row-active");
        },
        success: function (res) {
            $("#file-content").html(base64_decode(res.contenido));
            $("#estatus").html(res.estatus);
            $("#worker-status").html(res.bitacora);
        }
    });
}

function contenidoArchivoPago(id) {
    $.ajax({url: "/operaciones/validador/contenido-archivo-pago", cache: false, dataType: "json", type: "POST",
        data: {id: id},
        beforeSend: function() {
            $("#worker-status").html("");
            $(".content-row").removeClass("content-row-active");
            $("#ae_" + id).addClass("content-row-active");
        },
        success: function (res) {
            $("#file-content").html(base64_decode(res.contenido));
            $("#estatus").html(res.estatus);
            $("#worker-status").html(res.bitacora);
        }
    });
}

function validarArchivo(id) {
    $.ajax({url: "/operaciones/validador/validar-archivo", cache: false, dataType: "json", type: "POST",
        data: {id: id},
        beforeSend: function() {
            $("#imgm_" + id).hide();
            $("#imgl_" + id).show();    
            $("#worker-status").html("");
        },
        success: function (res) {
            if (res.success === true) {
                if (typeof (worker) === "undefined") {
                    var worker = new Worker("/js/operaciones/index/worker.js");
                    worker.postMessage({"cmd": "start", "msg": res.id});
                }
                worker.onmessage = function (event) {
                    if(event.data.success !== false) {
                        $("#worker-status").html(event.data.message);
                    } else {
                        $("#worker-status").html(event.data.message);
                        worker.terminate();
                        contenidoArchivo(id);
                    }
                };
            }
        }
    });
}

function reenviarArchivo(id) {
    $.ajax({url: "/operaciones/validador/reenviar-archivo", cache: false, dataType: "json", type: "POST",
        data: {id: id},
        beforeSend: function() {
            $("#imgm_" + id).hide();
            $("#imgl_" + id).show();    
            $("#worker-status").html("");
        },
        success: function (res) {
            if (res.success === true) {
                if (typeof (worker) === "undefined") {
                    var worker = new Worker("/js/operaciones/index/worker.js");
                    worker.postMessage({"cmd": "start", "msg": res.id});
                }
                worker.onmessage = function (event) {
                    if(event.data.success !== false) {
                        $("#worker-status").html(event.data.message);
                    } else {
                        $("#worker-status").html(event.data.message);
                        worker.terminate();
                        contenidoArchivo(id);
                    }
                };
            }
        }
    });
    
}

function savetoDisk(id) {
    $.ajax({url: "/operaciones/validador/save-to-disk", cache: false, dataType: "json", type: "POST",
        data: {id: id},
        success: function (res) {
            
        }
    });
}

function savetoDiskRes(id) {
    $.ajax({url: "/operaciones/validador/save-to-disk-res", cache: false, dataType: "json", type: "POST",
        data: {id: id},
        success: function (res) {
            
        }
    });
}

function pagarArchivo(id) {
    $.ajax({url: "/operaciones/validador/pagar-archivo", cache: false, dataType: "json", type: "POST",
        data: {id: id},
        beforeSend: function() {
            $("#imge_" + id).hide();
            $("#imgl_" + id).show();    
            $("#worker-status").html("");
        },
        success: function (res) {
            if (res.success === true) {
                if (typeof (worker) === "undefined") {
                    var worker = new Worker("/js/operaciones/index/worker_pago.js");
                    worker.postMessage({"cmd": "start", "msg": res.id});
                }
                worker.onmessage = function (event) {
                    if(event.data.success !== false) {
                        $("#worker-status").html(event.data.message);
                    } else {
                        $("#worker-status").html(event.data.message);
                        worker.terminate();
                        contenidoArchivoPago(id);
                    }
                };
            }
        }
    });
}

function contenidoRespuesta(id) {
    $.ajax({url: "/operaciones/validador/contenido-archivo-respuesta", cache: false, dataType: "json", type: "POST",
        data: {id: id},
        success: function (res) {
            $("#file-content").html(base64_decode(res.contenido));
            $("#download-link").html("<a class=\"btn btn-small btn-warning\" style=\"margin: 5px\" href=\"/operaciones/validador/download-file?id=" + res.archivo + "&type=res\">Descargar archivo</a>");
        }
    });
}

function contenidoRespuestaPago(id) {
    $.ajax({url: "/operaciones/validador/contenido-archivo-a", cache: false, dataType: "json", type: "POST",
        data: {id: id},
        success: function (res) {
            $("#file-content").html(base64_decode(res.contenido));            
        }
    });
}

function contenidoM3(id) {
    $.ajax({url: "/operaciones/validador/contenido-archivo-m3", cache: false, dataType: "json", type: "POST",
        data: {id: id},
        success: function (res) {
            $("#file-content").html(base64_decode(res.contenido));
            $("#download-link").html("<a class=\"btn btn-small btn-warning\" style=\"margin: 5px\" href=\"/operaciones/validador/download-file?id=" + res.archivo + "\">Descargar archivo</a>");
        }
    });
}

function contenidoPago(id) {
    $.ajax({url: "/operaciones/validador/contenido-archivo-e", cache: false, dataType: "json", type: "POST",
        data: {id: id},
        success: function (res) {
            $("#file-content").html(base64_decode(res.contenido));
        }
    });
}

function base64_decode(data) {
    var b64 = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";
    var o1, o2, o3, h1, h2, h3, h4, bits, i = 0,
            ac = 0,
            dec = "",
            tmp_arr = [];
    if (!data) {
        return data;
    }
    data += "";
    do { // unpack four hexets into three octets using index points in b64
        h1 = b64.indexOf(data.charAt(i++));
        h2 = b64.indexOf(data.charAt(i++));
        h3 = b64.indexOf(data.charAt(i++));
        h4 = b64.indexOf(data.charAt(i++));
        bits = h1 << 18 | h2 << 12 | h3 << 6 | h4;
        o1 = bits >> 16 & 0xff;
        o2 = bits >> 8 & 0xff;
        o3 = bits & 0xff;
        if (h3 == 64) {
            tmp_arr[ac++] = String.fromCharCode(o1);
        } else if (h4 == 64) {
            tmp_arr[ac++] = String.fromCharCode(o1, o2);
        } else {
            tmp_arr[ac++] = String.fromCharCode(o1, o2, o3);
        }
    } while (i < data.length);
    dec = tmp_arr.join("");
    return dec.replace(/\0+$/, "");
}

function base64_encode(data) {
    var b64 = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";
    var o1, o2, o3, h1, h2, h3, h4, bits, i = 0,
            ac = 0,
            enc = "",
            tmp_arr = [];
    if (!data) {
        return data;
    }
    do { // pack three octets into four hexets
        o1 = data.charCodeAt(i++);
        o2 = data.charCodeAt(i++);
        o3 = data.charCodeAt(i++);
        bits = o1 << 16 | o2 << 8 | o3;
        h1 = bits >> 18 & 0x3f;
        h2 = bits >> 12 & 0x3f;
        h3 = bits >> 6 & 0x3f;
        h4 = bits & 0x3f;
        // use hexets to index into b64, and append result to encoded string
        tmp_arr[ac++] = b64.charAt(h1) + b64.charAt(h2) + b64.charAt(h3) + b64.charAt(h4);
    } while (i < data.length);
    enc = tmp_arr.join("");
    var r = data.length % 3;
    return (r ? enc.slice(0, r - 3) : enc) + "===".slice(r || 3);
}

function contenidoValidador(patente, aduana) {    
    $.ajax({ url: "/automatizacion/validador/archivos-validador", cache: false, dataType: "json", type: "POST",
        data: {patente: patente, aduana: aduana, idUsuario: $("#idUsuario").val()},
        beforeSend: function () {
            $("#loading").show();
        },
        success: function (res) {
            if(res.success === true) {
                $("#remote-files").html(res.html);
                $("#loading").hide();
            }
            setTimeout(function(){
                $("#reload-ftp").removeAttr("disabled");
            }, (1000 * 60));
        }
    });
}

function descargaUnArchivo(patente, aduana, nombre) {    
    $.ajax({url: "/automatizacion/validador/descarga-un-archivo", cache: false, dataType: "json", type: "POST",
        data: {patente: patente, aduana: aduana, archivo: nombre},
        success: function (res) {
            if(res.success === true) {
                $.alert({title: "Confirmación", type: "blue", content: "DESCARGA DE ARCHIVO " + res.directorio, boxWidth: "350px", useBootstrap: false});
            } else {
                $.alert({title: "Error", type: "red", content: res.message, boxWidth: "350px", useBootstrap: false});
            }
        }
    });
}