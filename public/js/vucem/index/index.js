function downloadAllCoves(name) {
    var checkedValues = $('input[name=' + name + ']:checkbox:checked').map(function () {
        return this.value;
    }).get();
    if (typeof checkedValues[0] !== 'undefined' && checkedValues[0] !== null) {
        $.ajax({
            url: "/vucem/data/descarga-coves",
            type: "post",
            data: {files: checkedValues},
            success: function (data) {
                var obj = jQuery.parseJSON(data);
                if (obj.success === true) {
                    window.location.href = "/vucem/data/descargar-coves?filename=" + encodeURIComponent(obj.filename);
                }
            }
        });
    } else {
        alert("No a seleccionado nada.");
    }
}

/*function enviaraPedimento(id, cove) {
    $.ajax({
        url: "/vucem/ajax/enviar-pedimento",
        type: "post",
        data: {id: id, solicitud: cove},
        success: function (res) {
            if(res.success === false) {
                alert(res.message);                
            }
        }
    });
}*/

function selectAllCoves(name) {
    $('input[name=' + name + ']').each(function () {
        this.checked = true;
    });
}
function unselectAllCoves(name) {
    $('input[name=' + name + ']').each(function () {
        this.checked = false;
    });
}

$(document).ready(function () {
    
    $('#example').dataTable({
        "sDom": "<'traffic-cols'<'traffic-col-50'l><'traffic-col-50'f><'traffic-clear-5'>t<'traffic-clear-5'><'traffic-col-50'i><'traffic-col-50'p><'traffic-clear-5'>>",
        "sPaginationType": "bootstrap",
        "oLanguage": {
            "sLengthMenu": "_MENU_ registros por página"
        },
        "iDisplayLength": 25,
        "bStateSave": true,
        "aaSorting": [],
        "bSort": false
    });

    $(document.body).on("click", ".vucem", function (ev) {
        var id = $(this).data("id");
        $("#estatus_" + id).hide();
        $.post("/vucem/ajax/consultar-solicitud-cove", {id: id})
                .done(function (res) {
                    if (res.success === true) {
                        $(".vucem[data-id=" + id + "]").hide();
                        $("#estatus_" + id).html('<div class="semaphore-green"></div><a style="cursor: pointer; float:left" title="Consultar el COVE enviado." href="/vucem/index/consultar-cove-enviado?id=' + id + '"><i class="icon icon-file"></i></a><a  style="cursor: pointer; float:left" data-id="' + id + '" title="Mandar a expediente" class="enviarAExpediente"><i class="icon-hdd"></i></a>');                        
                        $.alert({title: "VUCEM Responde", content: res.message, boxWidth: "350px", useBootstrap: false, type: "green"});
                    } else {
                        $(".vucem[data-id=" + id + "]").hide();
                        $("#estatus_" + id).html('<div class="semaphore-red"></div><a style="cursor: pointer; float:left" title="Consultar el COVE enviado." href="/vucem/index/consultar-cove-enviado?id=' + id + '"><i class="icon icon-file"></i></a><a style="cursor: pointer; float:left" title="Borrar COVE" class="deleteCove" data-id="' + id + '"><i class="icon icon-trash"></i></a>');                        
                        $.alert({title: "VUCEM Error", content: res.message, boxWidth: "350px", useBootstrap: false, type: "red"});
                    }
                    $("#estatus_" + id).show();                        
                });
    });
    
    $(document.body).on("click", "#refresh", function (e) {
        e.preventDefault();
        if (!$("#refresh").hasClass("disabled")) {
            $.blockUI({
                centerY: 0,
                css: {top: '10px', left: '', right: '10px'},
                message: '<h4 style="color: #333; padding: 10px 10px; border: 0; background: #fff; text-align: left"><div class="traffic-icon traffic-loader"></div>Por favor espere...</h4>',
                baseZ: 2000
            });
            if (!$(this).hasClass("disabled")) {
                $.ajax({
                    url: "/vucem/ajax/solicitudes-coves",
                    type: "post",
                    dataType: "json",
                    success: function (res) {
                        if (res.success === true) {
                            setTimeout($.unblockUI, 1000);
                            location.reload();
                        } else {
                            setTimeout($.unblockUI, 1000);
                        }
                    }
                });
            }
        }
    });

    setTimeout(function () {
        $("#refresh").removeClass("disabled");
    }, 5 * 1000);

    $("#refresh-hdd").click(function (e) {
        e.preventDefault();
        $.ajax({
            url: "/vucem/ajax/hdd-coves-background",
            type: "post",
            dataType: 'json',
            success: function () {
            }
        });
    });

    $(document.body).on("click", ".deleteCove",function (e) {
        e.preventDefault();
        var id = $(this).data("id");
        $.confirm({
            title: "Confirmar",
            content: "¿Está seguro de que desea eliminar el archivo?",
            type: "red",
            escapeKey: "cerrar",
            boxWidth: "250px",
            useBootstrap: false,
            buttons: {
                si: {
                    btnClass: "btn-blue",
                    action: function () {
                        $.post("/vucem/data/borrar-solicitud-cove", {id: id})
                                .done(function (res) {
                                    if (res.success === true) {
                                        $("tr.row_" + id).hide();
                                    }
                                });
                    }
                },
                no: function () {}
            }
        });
    });

    $(document.body).on("click", ".enviarAPedimento", function (e) {
        e.preventDefault();
        var id = $(this).data("id");
        $.ajax({
            url: "/vucem/ajax/enviar-pedimento",
            type: "post",
            data: {id: id},
            success: function (res) {
                if (res.success === true) {
                    $(".enviarAPedimento[data-id=" + id + "]").hide();
                    $.alert({title: "Ok", content: res.message, boxWidth: "250px", useBootstrap: false, type: "green"});
                } else {
                    $.alert({title: "Error", content: res.message, boxWidth: "250px", useBootstrap: false, type: "red"});
                }
            }
        });
    });
    
    $(document.body).on("click", ".enviarAExpediente", function (e) {
        e.preventDefault();
        var id = $(this).data("id");
        $.ajax({
            url: "/automatizacion/vucem/print-cove?save=true&id=" + id,
            type: "get",
            dataType: "json",
            success: function (res) {
                if (res.success === true) {
                    $(".enviarAExpediente[data-id=" + id + "]").hide();
                    $.alert({title: "Ok", content: "Archivo guardado en repositorio.", boxWidth: "250px", useBootstrap: false, type: "green"});
                } else {
                    $.alert({title: "Error", content: "A ocurrido un error al guardar en repositorio digital.", boxWidth: "250px", useBootstrap: false, type: "red"});
                }
            }
        });
    });
    
    $(document.body).on("click", ".adenda", function (e) {
        e.preventDefault();
        $.ajax({
            url: "/vucem/ajax/adenda-cove",
            type: "post",
            data: {id: $(this).data("id"), factura: $(this).data("factura"), cove: $(this).data("cove")},
            success: function (res) {
                if (res.success === true) {
                    window.location.href = "/vucem/index/nuevo-cove-facturas";
                }
            }
        });
    });
    
    $(document.body).on("click", ".reenviar", function (e) {
        e.preventDefault();
        $.ajax({
            url: "/vucem/ajax/reenviar-cove",
            type: "post",
            data: {id: $(this).data("id"), factura: $(this).data("factura")},
            success: function (res) {
                if (res.success === true) {
                    window.location.href = "/vucem/index/nuevo-cove-facturas";
                }
            }
        });
    });

    /*$(document.body).on("click", ".resent", function (e) {
        e.preventDefault();
        var resp = confirm("¿Esta seguro que desea volver a enviar el COVE?");
        if (resp === true) {
            $.ajax({
                url: "/vucem/ajax/resend-cove",
                type: "post",
                data: {id: $(this).attr("data")},
                success: function (res) {
                    if (res.success === true) {
                        location.reload();
                    } else {
                        alert("ERROR: " + res.message);
                    }
                }
            });
        }
    });

    $(".removecove").click(function (e) {
        e.preventDefault();
        var resp = confirm("¿Esta seguro que desea borrar este registro?");
        if (resp === true) {
            $.ajax({
                url: "/vucem/data/remover-cove",
                type: "post",
                data: {id: $(this).attr("data")},
                success: function (data) {
                    var obj = jQuery.parseJSON(data);
                    if (obj.success === true) {
                        location.reload();
                    }
                }
            });
        }
    });*/

    /*$(".printcove").click(function (e) {
        e.preventDefault();
        if (!$("input:radio[name='printer']").is(":checked")) {
            alert("No ha seleccionado impresora");
        } else {
            var printer = $("input[name='printer']:checked").val();

            var resp = confirm("¿Esta seguro que desea imprimir?");
            if (resp === true) {
                $.ajax({
                    url: "/automatizacion/index/print-document",
                    type: "post",
                    data: {document: $(this).attr("data"), printer: printer},
                    success: function (data) {
                        var obj = jQuery.parseJSON(data);
                        if (obj.success === false) {
                            alert("Hubo un error en la impresión.");
                        }
                    }
                });
            }
        }
    });*/
    
});
