/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function openTraffic() {
    var row = $('#dg').datagrid('getSelected');
    if (row) {
        var win = window.open('/trafico/index/editar-trafico?id=' + row.id, '_blank');
        win.focus();
    }
}

function documentsComplete() {
    var row = $('#dg').datagrid('getSelected');
    if (row) {
        $.ajax({
            url: "/trafico/post/trafico-documentos-completos",
            cache: false,
            dataType: "json",
            data: {id: row.id},
            type: "POST",
            success: function (res) {
                if (res.success === true) {
                    $.alert({
                        title: "Confirmación",
                        type: "green",
                        content: "Los datos han sido guardados de manera exitosa.",
                        boxWidth: "350px",
                        useBootstrap: false
                    });
                    $('#dg').datagrid('reload');
                }
            }
        });
    }
}

function cancelOperation() {
    var row = $('#dg').datagrid('getSelected');
    if (row) {
        $.ajax({
            url: "/trafico/post/cancelar-operacion", cache: false, dataType: "json", data: {id: row.id}, type: "POST",
            success: function (res) {
                if (res.success === true) {
                    $.alert({
                        title: "Confirmación",
                        type: "green",
                        content: "Tráfico ha sido cancelado.",
                        boxWidth: "350px",
                        useBootstrap: false
                    });
                    $('#dg').datagrid('reload');
                }
            }
        });
    }
}

function soia() {
    var row = $('#dg').datagrid('getSelected');
    if (row) {
        $.confirm({
            title: "Estatus SOIA", escapeKey: "cerrar", boxWidth: "660px", useBootstrap: false, type: "green",
            buttons: {
                cerrar: {
                    btnClass: "btn-red", action: function () {
                    }
                }
            },
            content: function () {
                var self = this;
                return $.ajax({
                    url: "/trafico/get/soia?id=" + row.id, method: "GET"
                }).done(function (res) {
                    self.setContent(res);
                }).fail(function () {
                    self.setContent("Something went wrong.");
                });
            }
        });
    }
}

function justification() {
    var row = $('#dg').datagrid('getSelected');
    if (row) {
        $.ajax({
            url: "/trafico/post/trafico-justificar", cache: false, dataType: "json", data: {id: row.id}, type: "POST",
            success: function (res) {
                if (res.success === true) {
                    $.alert({
                        title: "Confirmación",
                        type: "green",
                        content: "Los datos han sido guardados de manera exitosa.",
                        boxWidth: "350px",
                        useBootstrap: false
                    });
                    $('#dg').datagrid('reload');
                }
            }
        });
    }
}

window.menuHandler = function(item) {
    if (item.name == 'open') {
        openTraffic();
    }
    if (item.name == 'soia') {
        soia();
    }
    if (item.name == 'documents') {
        documentsComplete();
    }
    if (item.name == 'cancel') {
        cancelOperation();
    }
    if (item.name == 'justification') {
        justification();
    }
    if (item.name == 'delete') {
        deleteTraffic();
    }
    if (item.name == 'status') {
        let row = $('#dg').datagrid('getSelected');
        let idTrafico = row.id;
        cambiarEstatus(idTrafico, item.status);
    }
};

function cambiarEstatus(idTrafico, estatus) {
    $.ajax({
        url: "/trafico/post/cambiar-estatus", type: 'POST', dataType: 'json', async: false,
        data: {idTrafico: idTrafico, estatus: estatus},
        success: function (res) {
            if (res.success === true) {
                $('#dg').datagrid('reload');
            }
        }
    });
}

function formatEstatus(val, row) {
    if (parseInt(val) === 1) {
        return '<div class="semaphore-black"></div>';
    } else if (parseInt(val) === 2) {
        return '<div class="semaphore-blue"></div>';
    } else if (parseInt(val) === 3) {
        return '<div class="semaphore-green"></div>';
    } else if (parseInt(val) === 5) {
        return '<div class="semaphore-yellow"></div>';
    } else if (parseInt(val) === 6) {
        return '<div class="semaphore-red"></div>';
    } else if (parseInt(val) === 7) {
        return '<div class="semaphore-pink"></div>';
    }
}

function formatArchive(val, row) {
    if (row.revisionAdministracion == null && row.revisionOperaciones == null) {
        // sin checklist
        return '<i class="fas fa-archive" data-id="' + row.id + '" style="font-size: 1.2em; color: #c1c1c1; cursor: pointer; padding-top: 2px"></i>';
    }
    if (row.revisionAdministracion == null && row.revisionOperaciones == 1) {
        // rev. operaciones
        return '<i class="fas fa-archive" data-id="' + row.id + '" style="font-size: 1.2em; color: #F59211; cursor: pointer; padding-top: 2px"></i>';
    }
    if (row.revisionAdministracion == 1 && row.revisionOperaciones == null) {
        // rev. admon
        return '<i class="fas fa-archive" data-id="' + row.id + '" style="font-size: 1.2em; color: #0099ff; cursor: pointer; padding-top: 2px"></i>';
    }
    if (row.revisionAdministracion == 1 && row.revisionOperaciones == 1) {
        // completo
        return '<i class="fas fa-archive" data-id="' + row.id + '" style="font-size: 1.2em; color: green; cursor: pointer; padding-top: 2px"></i>';
    }
}

function formatUpload(val, row) {
    return '<i class="fas fa-cloud-upload-alt upload-files" data-id="' + row.id + '" style="font-size: 1.2em; color: #2f3b58; cursor: pointer; padding-top: 2px"></i>';
}

function formatMensajero(val, row) {
    return '<i class="fas fa-envelope mensajero" data-id="' + row.id + '" style="font-size: 1.2em; color: #2f3b58; cursor: pointer"></i>';
}

function formatCarrierNaviera(val, row) {
    if (row.carrierNaviera) {
        var naviera = '';
        $.ajax({
            url: "/trafico/crud/navieras?idNaviera=" + row.carrierNaviera, type: 'get', dataType: 'json', async: false,
            success: function (res) {
                naviera = res.nombre;
            }
        });
        return naviera;
    } else {
        return '';
    }
}

function formatLink(val, row) {
    return '<a href="/trafico/index/editar-trafico?id=' + row.id + '">' + row.referencia + '</a>';
}

function formatImpo(val, row) {
    if (row.ie === "TOCE.IMP") {
        return '<i class="fas fa-arrow-circle-down" style="font-size: 1.2em; color: #2f3b58; padding-top: 2px"></i>';
    } else {
        return '<i class="fas fa-arrow-circle-up" style="font-size: 1.2em; color: #2e963a; padding-top: 2px"></i>';
    }
}

function newUser() {
    $('#dlg').dialog('open').dialog('center').dialog('setTitle', 'Nuevo trafico');
    $('#fm').form('clear');
}

function saveUser() {
    $('#fm').form('submit', {
        url: "/trafico/crud/trafico-nuevo",
        onSubmit: function () {
            return $(this).form('validate');
        },
        success: function (result) {
            var result = eval('(' + result + ')');
            if (result.errorMsg) {
                $.messager.show({
                    title: 'Error',
                    msg: result.errorMsg
                });
            } else {
                $('#dlg').dialog('close');        // close the dialog
                $('#dg').datagrid('reload');    // reload the user data
            }
        }
    });
}

function updateSitawin() {
    var row = $('#dg').datagrid('getSelected');
    if (row) {
        if (row.patente == 3589 && (row.aduana == 240 || row.aduana == 640 || row.aduana == 800)) {
            $.messager.confirm('Confirmar', '¿Está seguro de que desea actualizar el trafico?', function (r) {
                $.ajax({
                    url: "/trafico/get/actualizar-desde-sistema",
                    cache: false,
                    dataType: "json",
                    data: {id: row.id},
                    type: "GET",
                    success: function (res) {
                        if (res.success === true) {
                            $('#dg').datagrid('reload');
                        }
                    }
                });
            });
        }
    }
}

$.fn.datebox.defaults.formatter = function (date) {
    var y = date.getFullYear();
    var m = date.getMonth() + 1;
    var d = date.getDate();
    return y + '-' + (m < 10 ? ('0' + m) : m) + '-' + (d < 10 ? ('0' + d) : d);
};

$.fn.datebox.defaults.parser = function (s) {
    if (!s)
        return new Date();
    var ss = s.split('-');
    var y = parseInt(ss[0], 10);
    var m = parseInt(ss[1], 10);
    var d = parseInt(ss[2], 10);
    if (!isNaN(y) && !isNaN(m) && !isNaN(d)) {
        return new Date(y, m - 1, d);
    } else {
        return new Date();
    }
};

$.extend($.fn.combobox.defaults, {
    loader: function (param, success, error) {
        var target = this;
        var opts = $(target).combobox('options');
        if (!opts.url)
            return false;
        $.ajax({
            type: opts.method, url: opts.url, data: param, dataType: 'json',
            success: function (data) {
                if ($(target).parent().length) {
                    success(data);
                }
            },
            error: function () {
                error.apply(this, arguments);
            }
        });
    }
});

function initGeneral() {

    var arr = "#allOperations,#pagadas,#liberadas,#impos,#expos,#fdates,#ninvoices,#checklist";

    $(document.body).on("click", arr, function () {
        if ($(this).is(":checked")) {
            Cookies.set($(this).attr("id"), true);
        } else {
            Cookies.set($(this).attr("id"), false);
        }
        $('#dg').edatagrid('reload');
    });

    var customToolbar = '<td style="padding-left: 5px"><span><span class="l-btn-text">Todas:</span><input type="checkbox" id="allOperations" /></span></td>';
    customToolbar += '<td style="padding-left: 5px"><span><span class="l-btn-text">Pagadas:</span><input type="checkbox" id="pagadas" /></span></td>';
    customToolbar += '<td style="padding-left: 5px"><span><span class="l-btn-text">Liberadas:</span><input type="checkbox" id="liberadas" /></span></td>';
    customToolbar += '<td style="padding-left: 5px"><span><span class="l-btn-text">Impos:</span><input type="checkbox" id="impos" /></span></td>';
    customToolbar += '<td style="padding-left: 5px"><span><span class="l-btn-text">Expos:</span><input type="checkbox" id="expos" /></span></td>';
    customToolbar += '<td style="padding-left: 5px"><span><span class="l-btn-text">Sin fact.:</span><input type="checkbox" id="ninvoices" /></span></td>';
    customToolbar += '<td style="padding-left: 5px"><span><span class="l-btn-text">Expdte. incompl.:</span><input type="checkbox" id="checklist" /></span></td>';
    customToolbar += '<td style="padding-left: 5px"><span><span class="l-btn-text">Fechas:</span><input type="checkbox" id="fdates" /></span></td>';
    customToolbar += '<td style="padding-left: 5px"><span><span class="l-btn-text">Desde</span><input id="dateini" style="width:100px; text-align: center"></span></td>';
    customToolbar += '<td style="padding-left: 5px"><span><span class="l-btn-text">Hasta</span><input id="dateend" style="width:100px; text-align: center"></span></td>';

    $(".datagrid-toolbar").find("table > tbody > tr").append(customToolbar);

    $(document.body).on('click', '#traficosLiberados', function () {
        var dateTime = new Date();
        dateTime = moment(dateTime).format("YYYY-MM-DD");
        window.open("/trafico/crud/traficos-liberados?fecha=" + dateTime + "&tipo=50", "viewFile", "toolbar=0,location=0,menubar=0,height=550,width=880,scrollbars=yes");
    });

    $(document.body).on('click', '#traficosAperturados', function () {
        var dateTime = new Date();
        dateTime = moment(dateTime).format("YYYY-MM-DD");
        window.open("/trafico/crud/traficos-aperturados?fecha=" + dateTime + "&tipo=50", "viewFile", "toolbar=0,location=0,menubar=0,height=550,width=880,scrollbars=yes");
    });

    var array = arr.split(",");

    $.each(array, function (index, value) {
        var str = value.replace("#", "");
        if (Cookies.get(str) !== undefined) {
            if (Cookies.get(str) === "true") {
                $("#" + str).prop("checked", true);
            }
        }
    });

    $.each(['imex', 'msg', 'coves', 'edocuments', 'upl', 'ie', 'estatusExpediente', 'cvePedimento', 'fechaPago', 'estatus', 'fechaEtd', 'fechaLiberacion', 'fechaEntrada', 'fechaPresentacion', 'fechaFacturacion', 'fechaEta', 'fechaRevalidacion', 'fechaPrevio', 'fechaDespacho', 'fechaEtaAlmacen', 'fechaEnvioProforma', 'fechaEnvioDocumentos', 'fechaNotificacion', 'fechaDeposito', 'fechaCitaDespacho', 'fechaProformaTercero', 'fechaArriboTransfer', 'fechaSolicitudTransfer', 'fechaVistoBueno', 'facturas', 'cantidadFacturas', 'cantidadPartes', 'almacen', 'fechaVistoBuenoTercero', 'fechaComprobacion', 'tipoCarga', 'fechaEir', 'fechaInstruccionEspecial', 'idPlanta', 'diasDespacho', 'estatusRepositorio', 'observaciones', 'cumplimientoAdministrativo', 'cumplimientoOperativo', 'ccConsolidado', 'semaforo'], function (index, value) {
        $(".datagrid-editable-input[name='" + value + "']").hide();
    });

    $.each(['referencia', 'nombreCliente'], function (index, value) {
        $(document.body).on("input", ".datagrid-editable-input[name='" + value + "']", function () {
            var input = $(this);
            var start = input[0].selectionStart;
            $(this).val(function (_, val) {
                return val.toUpperCase();
            });
            input[0].selectionStart = input[0].selectionEnd = start;
        });
    });

}