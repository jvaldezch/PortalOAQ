/**
 * programmed by Jvaldez at gmail
 * 2015.may.14
 */

/**
 * 
 * @param {type} id
 * @returns {undefined}
 */
function archivosDeExpediente(id) {
    $.ajax({url: "/archivo/post/archivos-de-expediente", dataType: "json", type: "POST",
        data: {id: id},
        success: function (res) {
            if (res.success === true) {
                $("#files").html(res.html);
            }
        }
    });
}

/**
 * 
 * @param {type} id
 * @returns {undefined}
 */
function mvhcEstatusObtener(id) {
    $.ajax({url: "/archivo/get/mvhc-estatus-obtener", cache: false, dataType: "json", data: {id: id}, type: "GET",
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
    $.ajax({url: "/archivo/post/mvhc-estatus", cache: false, dataType: "json", data: {id: id, estatus: estatus}, type: "POST",
        success: function (res) {
            if (res.success === true) {
                if (res.success === true) {
                    $.toast({text: "<strong>Guardado</strong>", bgColor: "green", stack : 3, position : "bottom-right"});
                }
            }
        }
    });
}

function loadPhotos() {
    var idTrafico = $("#idTrafico").val();
    if (idTrafico !== undefined) {
        $.ajax({url: "/trafico/post/cargar-fotos", dataType: "json", timeout: 10000, type: "POST", 
            data: {id: $("#idTrafico").val(), borrar: 0},
            success: function (res) {
                if (res.success === true) {
                    $("#photos").html(res.html);
                }
            }        
        });
    }
}

window.actualizarTafico = function(id) {
    return $.ajax({url: "/archivo/get/actualizar-trafico", dataType: "json", type: "GET",
        data: {id: id},
        success: function (res) {
            if (res.success === true) {
                $.toast({text: "<strong>Se actualizó tráfico.</strong>", bgColor: "blue", stack : 3, position : "bottom-right"});
                return true;
            }
        }
    });    
};

window.buscarTafico = function(id) {
    return $.ajax({url: "/archivo/get/buscar-trafico", dataType: "json", type: "GET",
        data: {id: id},
        success: function (res) {
            if (res.success === true) {
                $.toast({text: "<strong>Se actualizó tráfico.</strong>", bgColor: "blue", stack : 3, position : "bottom-right"});
                return true;
            } else {
                $("#no_traffic").show();
            }
        }
    });    
};

$(document).ready(function () {

    $.validator.addMethod("regx", function (value, element, regexpr) {
        return regexpr.test(value);
    }, "RFC no es válido.");

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
            pedimento: {required: true, minlength: 7, digits: true},
            referencia: {required: true, minlength: 4},
            rfc_cliente: {required: true, regx: /^[A-Z]{3,4}([0-9]{2})([0-9]{2})([0-9]{2})?[A-Z0-9]{3,4}/},
            "file[]": {required: true}
        },
        messages: {
            pedimento: {
                required: "Proporcionar el pedimento",
                minlength: "Minimo 7 digitos",
                digits: "Pedimento deben ser solo números"
            },
            referencia: {
                required: "Proporcionar referencia",
                minlength: "Minimo 4 digitos"
            },
            rfc_cliente: {required: "Proporcionar el RFC del cliente"},
            "file[]": {required: "No ha seleccionado un archivo(s)"}
        }
    });

    var bar = $(".bar");
    var percent = $(".percent");

    $(document.body).on("click", "#submit", function (ev) {
        ev.preventDefault();
        if ($("#form").valid()) {
            $("#form").ajaxSubmit({url: "/archivo/post/subir-archivos-expediente", dataType: "json", type: "POST",
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
                success: function (res) {
                    setTimeout(function () {
                        bar.width(0);
                        percent.html(0);
                    }, 500);
                    if (res.success === true) {
                        $("#file").val("");
                        archivosDeExpediente($("#id").val());
                    }
                }
            });
        }
    });

    $(document.body).on("click", ".cancelarArchivo", function () {
        var id = $(this).data("id");
        $.ajax({url: "/archivo/post/cancelar", dataType: "json", type: "POST",
            data: {id: id},
            success: function (res) {
                if (res.success === true) {
                    $("#edit_" + id).html(res.tipo);
                    var html = '<i class="fas fa-pencil-alt editarArchivo" data-id="' + id + '" style="font-size:1.1em"></i>&nbsp;';
                    html += '<i class="fas fa-trash-alt borrarArchivo" data-id="' + id + '" style="font-size:1.1em"></i>';
                    $("#icon_" + id).html(html);
                }
            }
        });
    });

    $(document.body).on("click", ".guardarArchivo", function () {
        var id = $(this).data("id");
        $.ajax({url: "/archivo/post/guardar", dataType: "json", type: "POST",
            data: {idRepo: $("#id").val(), id: id, tipo: $("#select_" + id).val()},
            beforeSend: function () {
                var html = '<i class="fas fa-pencil-alt editarArchivo" data-id="' + id + '" style="font-size:1.1em"></i>&nbsp;';
                html += '<i class="fas fa-trash-alt borrarArchivo" data-id="' + id + '" style="font-size:1.1em"></i>';
                $("#icon_" + id).html(html);
            },
            success: function (res) {
                if (res.success === true) {
                    $("#edit_" + res.id).html(res.tipo);
                }
            }
        });
    });

    $(document.body).on("click", ".editarArchivo", function () {
        var id = $(this).data("id");
        $.ajax({url: "/archivo/post/tipos-de-archivo", dataType: "json", type: "POST",
            data: {id: id},
            beforeSend: function () {
                var html = '<i class="far fa-save guardarArchivo" data-id="' + id + '" style="font-size:1.3em"></i>&nbsp;';
                html += '<i class="fas fa-times cancelarArchivo" data-id="' + id + '" style="font-size:1.3em"></i>';
                $("#icon_" + id).html(html);
            },
            success: function (res) {
                if (res.success === true) {
                    $("#edit_" + res.id).html(res.html);
                }
            }
        });
    });

    $(document.body).on("click", ".borrarArchivo", function () {
        var id = $(this).data("id");
        $.confirm({title: "Confirmar", content: '¿Está seguro de que desea eliminar el archivo?', type: 'red', escapeKey: "cerrar", boxWidth: "250px", useBootstrap: false,
            buttons: {
                si: {
                    btnClass: "btn-red",
                    action: function () {
                        $.post("/archivo/post/borrar", {idRepo: $("#id").val(), id: id})
                                .done(function (res) {
                                    if (res.success === true) {
                                        archivosDeExpediente($("#id").val());
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

    $(document.body).on("click", "#enviarFtp", function () {
        $.confirm({title: "FTP",
            escapeKey: "cerrar", boxWidth: "650px", useBootstrap: false, type: "blue",
            buttons: {
                si: {
                    btnClass: "btn-blue",
                    action: function () {
                        $.get("/automatizacion/ftp/enviar-repositorio-ftp", {id: $("#id").val()}, function (res) {
                            if (res.success === true) {
                                $.alert({title: "Enviado", type: "blue", content: res.message, boxWidth: "350px", useBootstrap: false});
                            } else {
                                $.alert({title: "Error", type: "red", content: res.message, boxWidth: "350px", useBootstrap: false});
                            }
                        });
                    }
                },
                no: function () {}
            },
            content: function () {
                var self = this;
                return $.ajax({url: "/archivo/get/estatus-ftp", dataType: "json", method: "GET",
                    data: {id: $("#id").val()}
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
        $.confirm({title: "Checklist de intregración de expediente", escapeKey: "cerrar", boxWidth: "850px", useBootstrap: false,
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
                    data: {id: $("#id").val()}
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

    $(document.body).on("click", "#editRepo", function (ev) {
        ev.preventDefault();
        var id = $("#id").val();
        $.confirm({title: "Editar expediente", escapeKey: "cerrar", boxWidth: "710px", useBootstrap: false,
            buttons: {
                guardar: {
                    btnClass: "btn-blue",
                    action: function () {
                        if ($("#formEdit").valid()) {
                            $("#formEdit").ajaxSubmit({url: "/archivo/post/actualizar-repositorio", cache: false, dataType: "json", type: "POST",
                                success: function (res) {
                                    if (res.success === true) {
                                        $(location).attr("href", "/archivo/index/expediente?id=" + res.id);
                                    } else {
                                        $.alert({title: "Error", content: res.message, type: "red", boxWidth: "450px", useBootstrap: false});
                                        return false;
                                    }
                                }
                            });
                        } else {
                            return false;
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
                return $.ajax({url: "/archivo/post/editar-expediente", dataType: "json", method: "POST",
                    data: {id: id}
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

    $(document.body).on("click", "#deleteRepo", function (ev) {
        ev.preventDefault();
        var id = $(this).data("id");
        $.confirm({
            title: "Confirmar", content: '¿Está seguro de que desea eliminar el archivo?', escapeKey: "cerrar", boxWidth: "250px", type: "red", useBootstrap: false,
            buttons: {
                si: {
                    btnClass: "btn-blue",
                    action: function () {
                        $.post("/archivo/post/borrar-repositorio", {id: id})
                                .done(function (res) {
                                    if (res.success === true) {
                                        window.location.href = "/archivo/index/index";
                                    } else {
                                        return false;
                                    }
                                });
                    }
                },
                no: function () { }
            }
        });
    });

    $(document.body).on("click", "#moveRepo", function (ev) {
        ev.preventDefault();
        var id = $(this).data("id");
        $.confirm({
            title: "Mover expediente", escapeKey: "cerrar", boxWidth: "790px", useBootstrap: false,
            buttons: {
                guardar: {
                    text: "MOVER",
                    btnClass: "btn-blue",
                    action: function () {
                        if ($("#formDestiny").valid()) {
                            if ($("#formDestiny #pedimento").val() === $("#formSource #pedimento").val() && $("#formDestiny #aduana").val() === $("#formSource #aduana").val() && $("#formDestiny #patente").val() === $("#formSource #patente").val() && $("#formDestiny #referencia").val() === $("#formSource #referencia").val()) {
                                $.alert({title: "Error", content: "Los valores no deben ser iguales.", type: "red", boxWidth: "450px", useBootstrap: false});
                                return false;
                            }
                            $("#formDestiny").ajaxSubmit({url: "/archivo/post/mover-repositorio", cache: false, dataType: "json", type: "POST",
                                success: function (res) {
                                    if (res.success === true) {
                                        window.location.href = "/archivo/index/expediente?id=" + res.id;
                                    } else {
                                        $.alert({title: "Error", content: res.message, type: "red", boxWidth: "450px", useBootstrap: false});
                                    }
                                }
                            });
                        } else {
                            return false;
                        }
                    }
                },
                cerrar: { btnClass: "btn-red", action: function () {} }
            },
            content: function () {
                var self = this;
                return $.ajax({url: "/archivo/post/mover-expediente", dataType: "json", method: "POST",
                    data: {id: id}
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

    $("#help").qtip({
        content: {
            text: "Mostrar la ayuda para los prefijos del sistema."
        }
    });

    $("#dirReload").qtip({
        content: {
            text: "Recargar archivos de expediente."
        }
    });
   
    $(document.body).on("click", "#permalink", function (ev) {
        ev.preventDefault();
        var id = $(this).data("id");
        $.confirm({ title: "Permalink", escapeKey: "cerrar", boxWidth: "660px", useBootstrap: false, type: "green",
            buttons: {
                cerrar: {btnClass: "btn-green", action: function () {}}
            },
            content: function () {
                var self = this;
                return $.ajax({ url: "/archivo/get/permalink?id=" + id, method: "GET"
                }).done(function (res) {
                    self.setContent(res);
                }).fail(function () {
                    self.setContent("Something went wrong.");
                });
            }
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

    archivosDeExpediente($("#id").val());
    
    mvhcEstatusObtener($("#id").val());

    $("button[title]").qtip();
    
    $(document.body).on("click", ".openFile", function (ev) {
        ev.preventDefault();        
        var id = $(this).data("id");
        window.open("/archivo/get/ver-archivo?id=" + id, "viewFile", "toolbar=0,location=0,menubar=0,height=550,width=880,scrollbars=yes");
    });
    
    $(document.body).on("click", "#ftpLink", function (ev) {
        var id = $(this).data("id");
        $.confirm({ title: "Enlace de descarga", escapeKey: "cerrar", boxWidth: "450px", useBootstrap: false, type: "green",
            buttons: {
                cerrar: {btnClass: "btn-green", action: function () {}}
            },
            content: function () {
                var self = this;
                return $.ajax({
                    url: "/archivo/get/link-ftp?id=" + id,
                    method: "get"
                }).done(function (res) {
                    self.setContent(res);
                }).fail(function () {
                    self.setContent("Something went wrong.");
                });
            }
        });
    });

    $(document.body).on("click", "#mvhcCliente", function (ev) {
        var id = $(this).data("id");
        if ($(this).is(':checked')) {
            mvhcEstatus(id, 1);
        } else {
            mvhcEstatus(id, 0);
        }
    });

    $(document.body).on("click", "#mvhcFirmado", function (ev) {
        var id = $(this).data("id");
        if ($(this).is(':checked')) {
            mvhcEstatus(id, 2);
        } else {
            mvhcEstatus(id, 1);
        }
    });
    
    $(document.body).on("click", "#mvhcEnviada", function (ev) {
        var id = $(this).data("id");
        if ($(this).is(':checked')) {
            mvhcEnviada(id, 1);
        } else {
            mvhcEnviada(id, 0);
        }
    });
    
    
    $(document.body).on("click", "#saveMvhcNumGuia", function (ev) {
        ev.preventDefault();
        
        var id = $(this).data("id");
        var mvhcGuia = $("#mvhcGuia").val();
        
        $.ajax({url: "/trafico/post/mvhc-num-guia", cache: false, dataType: "json", data: {id: id, mvhcGuia: mvhcGuia}, type: "POST",
            success: function (res) {
                if (res.success === true) {
                }
            }
        });
        
    });
    
    $(document.body).on("click", "#recargarDirectorio", function (ev) {
        ev.preventDefault();
        var id = $(this).data("id");        
        $.ajax({url: "/archivo/get/recargar-directorio", cache: false, dataType: "json", data: {id: id}, type: "GET",
            success: function (res) {
                if (res.success === true) {
                    archivosDeExpediente(res.id);
                }
            }
        });
        
    });
    
    $(document.body).on("click", "#sendEmail", function (ev) {
        var id = $(this).data("id");
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
                    $.ajax({url: "/archivo/post/enviar-email", dataType: "json", type: "POST",
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
                var self = this;
                return $.ajax({
                    url: "/archivo/get/enviar-email?id=" + id,
                    method: "get"
                }).done(function (res) {
                    self.setContent(res);
                }).fail(function () {
                    self.setContent("Something went wrong.");
                });
            }
        });
    });
    
    loadPhotos();
    
    $(document.body).on("click",".image-link",function (ev) {
        ev.preventDefault();
        var w = window.open("/trafico/data/read-image?id=" + $(this).data("id"), 'Trafico Image ' + $(this).data("id"), 'toolbar=0,location=0,menubar=0,height=750,width=950,scrollbars=yes');
        w.focus();
        return false;
    });
    
    if ($("#idTrafico").val() !== '') {
        actualizarTafico($("#id").val());
    } else {
        buscarTafico($("#id").val());
    }

});