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

function formatEstatus(val, row) {
    if (val == 1) {
        return '<div class="semaphore-black"></div>';
    } else if (val == 2) {
        return '<div class="semaphore-blue"></div>';
    } else if (val == 3) {
        return '<div class="semaphore-green"></div>';
    } else if (val == 5) {
        return '<div class="semaphore-yellow"></div>';
    } else if (val == 6) {
        return '<div class="semaphore-red"></div>';
    }
}

function misMensajes(ids) {
    $.ajax({
        url: '/trafico/get/alertas-de-mensajes=', dataType: 'json', type: 'GET',
        data: {ids: ids},
        success: function (res) {
            if (res.success === true) {
                $.each(res.ids, function (index, value) {
                    $('.mensajero[data-id=' + value.idTrafico + ']').attr('src', '/images/icons/message-new.png');
                });
            }
        }
    });
}

function formatMensajero(val, row) {
    return '<img src="/images/icons/message.png" class="mensajero" data-id="' + row.id + '">';
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
        return '<i class="fas fa-arrow-circle-down" style="color: #2f3b58"></i>';
    } else {
        return '<i class="fas fa-arrow-circle-up" style="color: #2e963a"></i>';
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
                    type: "get",
                    dataType: "json",
                    data: {id: row.id},
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

function formatArchive(val, row) {
    if (parseInt(val) === 1) {
        return '<i class="fas fa-archive" data-id="' + row.id + '" style="font-size: 1.2em; color: #c2c2c2; cursor: pointer; padding-top: 2px"></i>';
    } else if (parseInt(val) === 2) {
        return '<i class="fas fa-archive" data-id="' + row.id + '" style="font-size: 1.2em; color: #0099ff; cursor: pointer; padding-top: 2px"></i>';
    } else if (parseInt(val) === 3) {
        return '<i class="fas fa-archive" data-id="' + row.id + '" style="font-size: 1.2em; color: #F59211; cursor: pointer; padding-top: 2px"></i>';
    } else if (parseInt(val) === 4) {
        return '<i class="fas fa-archive" data-id="' + row.id + '" style="font-size: 1.2em; color: green; cursor: pointer; padding-top: 2px"></i>';
    } else {
        return '<i class="fas fa-archive" data-id="' + row.id + '" style="font-size: 1.2em; color: #c2c2c2; cursor: pointer; padding-top: 2px"></i>';
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
            type: opts.method,
            url: opts.url,
            data: param,
            dataType: 'json',
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

var tipoAduana = 2;

$(document).ready(function () {

    var dg = $('#dg').edatagrid();

    dg.edatagrid({
        pagination: true,
        singleSelect: true,
        striped: true,
        rownumbers: true,
        fitColumns: false,
        pageSize: 20,
        idField: 'id',
        queryParams: {
            tipoAduana: tipoAduana
        },
        url: '/trafico/crud/traficos',
        updateUrl: '/trafico/crud/trafico-actualizar',
        rowStyler: function (index, row) {
        },
        onClickRow: function (index, row) {
        },
        onBeginEdit: function (index, row) {
        },
        onBeforeEdit: function (index, row) {
        },
        onAfterEdit: function (index, row) {
            if (row.fechaPago != "") {
                row.estatus = 2;
            }
            if (row.fechaLiberacion != "") {
                row.estatus = 3;
            }
            row.editing = false;
            $(this).datagrid('refreshRow', index);
        },
        onCancelEdit: function (index, row) {
            row.editing = false;
            $(this).datagrid('refreshRow', index);
        },
        onAdd: function (index, row) {
        },
        onRowContextMenu: function (e, index, row) {
            e.preventDefault();
            $('#mm').menu('show', {
                left: e.pageX,
                top: e.pageY
            });
        },
        onLoadSuccess: function () {
            var ids = [];
            var data = $('#dg').datagrid('getRows');
            $.each(data, function (index, value) {
                ids.push(value.id);
            });
            if (ids.length > 0) {
                misMensajes(ids);
            }
        },
        remoteFilter: true,
        toolbar: [{
            text: 'Guardar',
            iconCls: 'icon-save',
            handler: function () {
                $('#dg').edatagrid('saveRow');
            }
        }, {
            text: 'Cancelar',
            iconCls: 'icon-undo',
            handler: function () {
                $('#dg').edatagrid('cancelRow');
            }
        }, {
            text: 'Actualizar',
            iconCls: 'icon-reload',
            handler: function () {
                $('#dg').edatagrid('reload');
            }
        }],
        frozenColumns: [[
            {
                field: 'estatus', width: 20, title: '',
                formatter: formatEstatus
            },
            {
                field: 'imex', width: 30, checkbox: false, title: '',
                formatter: formatImpo
            },
            {
                field: 'msg', width: 30, checkbox: false, title: '',
                formatter: formatMensajero
            },
            {
                field: 'estatusExpediente', width: 30, title: "",
                formatter: formatArchive
            },
            {field: 'patente', width: 50, title: 'Patente'},
            {field: 'aduana', width: 50, title: 'Aduana'},
            {field: 'pedimento', width: 80, title: 'Pedimento'},
            {field: 'referencia', width: 100, title: 'Referencia', formatter: formatLink}
        ]],
        columns: [[
            {field: 'cvePedimento', width: 40, title: 'Cve.'},
            {field: 'nombreCliente', width: 300, title: 'Nombre Cliente'},
            {field: 'nombre', width: 120, title: 'Usuario'},
            {
                field: 'fechaEta',
                width: 90,
                title: 'F. ETA',
                editor: {type: 'datebox'},
                options: {required: false, validType: 'date'}
            },
            {field: 'blGuia', width: 150, title: 'Guía', editor: {type: 'text'}},
            {
                field: 'fechaPago',
                width: 130,
                title: 'F. Pago',
                editor: {type: 'datetimebox'},
                options: {required: false, validType: 'datetime'}
            },
            {
                field: 'fechaLiberacion',
                width: 130,
                title: 'F. Liberación',
                editor: {type: 'datetimebox'},
                options: {required: false, validType: 'datetime'}
            },
            {
                field: 'fechaFacturacion',
                width: 90,
                title: 'F. Facturación',
                editor: {type: 'datebox'},
                options: {required: false, validType: 'date'}
            },
            {
                field: 'idPlanta', width: 150, title: 'Planta',
                formatter: function (val, row) {
                    return row.descripcionPlanta;
                },
                editor: {
                    type: 'combobox',
                    options: {
                        valueField: 'id',
                        textField: 'descripcion',
                        panelWidth: 250,
                        panelHeight: 90
                    }
                }
            },
            // {
            //     field: 'fechaInstruccionEspecial', width: 90, title: 'Justificación',
            //     formatter(value, row) {
            //         if (row.fechaInstruccionEspecial !== null) {
            //             return 'Si';
            //
            //         }
            //     }
            // },
            {
                field: 'diasDespacho', width: 100, title: 'Días Despacho',
                formatter(value, row) {
                    if (row.fechaLiberacion !== null) {
                        return value;

                    }
                }
            },
            {field: 'observaciones', width: 250, title: 'Observaciones', editor: {type: 'text'}}
        ]]
    });

    $(document.body).on('click', '#traficosLiberados', function () {
        var dateTime = new Date();
        dateTime = moment(dateTime).format("YYYY-MM-DD");
        window.open("/trafico/crud/traficos-liberados?fecha=" + dateTime + "&tipo=51", "viewFile", "toolbar=0,location=0,menubar=0,height=550,width=880,scrollbars=yes");
    });

    var customToolbar = '<td style="padding-left: 5px"><span><span class="l-btn-text">Todas:</span><input type="checkbox" id="allOperations" /></span></td>';
    customToolbar += '<td style="padding-left: 5px"><span><span class="l-btn-text">Pagadas:</span><input type="checkbox" id="pagadas" /></span></td>';
    customToolbar += '<td style="padding-left: 5px"><span><span class="l-btn-text">Liberadas:</span><input type="checkbox" id="liberadas" /></span></td>';
    customToolbar += '<td style="padding-left: 5px"><span><span class="l-btn-text">Impos:</span><input type="checkbox" id="impos" /></span></td>';
    customToolbar += '<td style="padding-left: 5px"><span><span class="l-btn-text">Expos:</span><input type="checkbox" id="expos" /></span></td>';
    customToolbar += '<td style="padding-left: 5px"><span><span class="l-btn-text">Sin facturar:</span><input type="checkbox" id="ninvoices" /></span></td>';
    customToolbar += '<td style="padding-left: 5px"><span><span class="l-btn-text">Fechas:</span><input type="checkbox" id="fdates" /></span></td>';
    customToolbar += '<td style="padding-left: 5px"><span><span class="l-btn-text">Desde</span><input id="dateini" style="width:100px; text-align: center"></span></td>';
    customToolbar += '<td style="padding-left: 5px"><span><span class="l-btn-text">Hasta</span><input id="dateend" style="width:100px; text-align: center"></span></td>';

    $(".datagrid-toolbar").find("table > tbody > tr").append(customToolbar);

    var arr = "#allOperations,#pagadas,#liberadas,#impos,#expos,#fdates,#ninvoices";

    $(document.body).on("click", arr, function () {
        if ($(this).is(":checked")) {
            Cookies.set($(this).attr("id"), true);
        } else {
            Cookies.set($(this).attr("id"), false);
        }
        $('#dg').edatagrid('reload');
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

    $(".mensajero").each(function () {
        var id = $(this).data("id");
        $.ajax({
            url: "/trafico/get/mis-mensajes",
            cache: false,
            type: "post",
            dataType: "json",
            data: {idTrafico: id},
            success: function (res) {
                if (res.success === true) {
                    if (res.cantidad > 0) {
                        $(".mensajero[data-id=" + id + "]").attr("src", "/images/icons/message-new.png");
                    }
                }
            }
        });
    });

    dg.edatagrid('enableFilter', []);

    $.each(['imex', 'msg', 'ie', 'estatusExpediente', 'cvePedimento', 'fechaPago', 'fechaEtd', 'fechaLiberacion', 'fechaEntrada', 'fechaPresentacion', 'fechaFacturacion', 'fechaEta', 'fechaRevalidacion', 'fechaPrevio', 'fechaDespacho', 'fechaEtaAlmacen', 'fechaEnvioProforma', 'fechaEnvioDocumentos', 'fechaNotificacion', 'fechaDeposito', 'fechaCitaDespacho', 'fechaProformaTercero', 'fechaArriboTransfer', 'fechaSolicitudTransfer', 'fechaVistoBueno', 'facturas', 'cantidadFacturas', 'cantidadPartes', 'almacen', 'fechaVistoBuenoTercero', 'fechaComprobacion', 'tipoCarga', 'fechaEir', 'fechaInstruccionEspecial', 'idPlanta', 'diasDespacho', 'estatus', 'observaciones'], function (index, value) {
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

    var today = new Date();
    var dd = today.getDate();
    var mm = today.getMonth() + 1;
    var yyyy = today.getFullYear();

    var dateini;
    var dateend;

    if (Cookies.get("dateini") !== undefined) {
        dateini = Cookies.get("dateini");
    } else {
        dateini = yyyy + "-" + zeroPad(mm, 2) + "-01";
        Cookies.set("dateini", dateini);
    }

    if (Cookies.get("dateend") !== undefined) {
        dateend = Cookies.get("dateend");
    } else {
        dateend = yyyy + "-" + zeroPad(mm, 2) + "-" + zeroPad(dd, 2);
        Cookies.set("dateend", dateend);
    }

    $("#dateini").datebox({
        value: dateini,
        required: true,
        showSeconds: false,
        onChange: function (newValue) {
            Cookies.set('dateini', newValue);
            dg.edatagrid('reload');
        }
    });

    $("#dateend").datebox({
        value: dateend,
        required: true,
        showSeconds: false,
        onChange: function (newValue) {
            Cookies.set('dateend', newValue);
            dg.edatagrid('reload');
        }
    });

});