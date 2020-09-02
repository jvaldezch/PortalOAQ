/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function zeroPad(num, places) {
    let zero = places - num.toString().length + 1;
    return Array(+(zero > 0 && zero)).join("0") + num;
}

window.abrir = function() {
    let row = $('#dg').datagrid('getSelected');
    if (row) {
        let win = window.open('/bodega/index/editar-entrada?id=' + row.id, '_blank');
        win.focus();
    }
};

window.formatEstatus = function(val, row) {
    if (parseInt(val) === 1) {
        return '<div class="semaphore-black"></div>';
    } else if (parseInt(val) === 2) {
        return '<div class="semaphore-yellow"></div>';
    } else if (parseInt(val) === 3) {
        return '<div class="semaphore-blue"></div>';
    } else if (parseInt(val) === 4) {
        return '<div class="semaphore-green"></div>';
    } 
};

window.formatMensajero = function(val, row) {
    return '<i class="fas fa-envelope mensajero" data-id="' + row.id + '" style="font-size: 1.2em; color: #2f3b58; cursor: pointer"></i>';
};

window.formatLink = function(val, row) {
    return '<a href="/trafico/index/editar-trafico?id=' + row.id + '">' + row.referencia + '</a>';
};

window.formatImpo = function(val, row) {
    if (row.ie === "TOCE.IMP") {
        return '<i class="fas fa-arrow-circle-down" style="color: #2f3b58"></i>';
    } else {
        return '<i class="fas fa-arrow-circle-up" style="color: #2e963a"></i>';
    }
};

$.fn.datebox.defaults.formatter = function (date) {
    let y = date.getFullYear();
    let m = date.getMonth() + 1;
    let d = date.getDate();
    return y + '-' + (m < 10 ? ('0' + m) : m) + '-' + (d < 10 ? ('0' + d) : d);
};

$.fn.datebox.defaults.parser = function (s) {
    if (!s)
        return new Date();
    let ss = s.split('-');
    let y = parseInt(ss[0], 10);
    let m = parseInt(ss[1], 10);
    let d = parseInt(ss[2], 10);
    if (!isNaN(y) && !isNaN(m) && !isNaN(d)) {
        return new Date(y, m - 1, d);
    } else {
        return new Date();
    }
};

$.extend($.fn.combobox.defaults, {
    loader: function (param, success, error) {
        let target = this;
        let opts = $(target).combobox('options');
        if (!opts.url)
            return false;
        $.ajax({type: opts.method, url: opts.url, data: param, dataType: 'json',
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

    let arr = "#allOperations,#pagadas,#liberadas,#intraffic,#fdates,#ninvoices";

    $(document.body).on("click", arr, function () {
        if ($(this).is(":checked")) {
            Cookies.set($(this).attr("id"), true);
        } else {
            Cookies.set($(this).attr("id"), false);
        }
        $('#dg').edatagrid('reload');
    });

    let customToolbar = '<td style="padding-left: 5px"><span><span class="l-btn-text">Todas:</span><input type="checkbox" id="allOperations" /></span></td>';
    customToolbar += '<td style="padding-left: 5px"><span><span class="l-btn-text">Liberadas:</span><input type="checkbox" id="liberadas" /></span></td>';
    customToolbar += '<td style="padding-left: 5px"><span><span class="l-btn-text">En tráfico:</span><input type="checkbox" id="intraffic" /></span></td>';
    customToolbar += '<td style="padding-left: 5px"><span><span class="l-btn-text">Fechas:</span><input type="checkbox" id="fdates" /></span></td>';
    customToolbar += '<td style="padding-left: 5px"><span><span class="l-btn-text">Desde</span><input id="dateini" style="width:100px; text-align: center"></span></td>';
    customToolbar += '<td style="padding-left: 5px"><span><span class="l-btn-text">Hasta</span><input id="dateend" style="width:100px; text-align: center"></span></td>';

    $(".datagrid-toolbar").find("table > tbody > tr").append(customToolbar);

    $(document.body).on('click', '#traficosLiberados', function () {
        let dateTime = new Date();
        dateTime = moment(dateTime).format("YYYY-MM-DD");
        window.open("/trafico/crud/traficos-liberados?fecha=" + dateTime + "&tipo=50", "viewFile", "toolbar=0,location=0,menubar=0,height=550,width=880,scrollbars=yes");
    });

    let array = arr.split(",");

    $.each(array, function (index, value) {
        let str = value.replace("#", "");
        if (Cookies.get(str) !== undefined) {
            if (Cookies.get(str) === "true") {
                $("#" + str).prop("checked", true);
            }
        }
    });

    $.each(['imex', 'idTrafico', 'siglas', 'msg', 'bultos', 'fechaPago', 'estatus', 'fechaEtd', 'fechaLiberacion', 'fechaEntrada', 'fechaPresentacion', 'fechaFacturacion', 'fechaEta', 'fechaRevalidacion', 'fechaPrevio', 'fechaDespacho', 'fechaEtaAlmacen', 'fechaEnvioProforma', 'fechaEnvioDocumentos', 'fechaNotificacion', 'fechaDeposito', 'fechaCitaDespacho', 'fechaProformaTercero', 'fechaArriboTransfer', 'fechaSolicitudTransfer', 'fechaVistoBueno', 'facturas', 'cantidadFacturas', 'cantidadPartes', 'almacen', 'fechaVistoBuenoTercero', 'fechaComprobacion', 'tipoCarga', 'fechaEir', 'fechaInstruccionEspecial', 'idPlanta', 'diasDespacho', 'estatusRepositorio', 'observaciones', 'cumplimientoAdministrativo', 'cumplimientoOperativo'], function (index, value) {
        $(".datagrid-editable-input[name='" + value + "']").hide();
    });

    $.each(['referencia', 'nombreCliente'], function (index, value) {
        $(document.body).on("input", ".datagrid-editable-input[name='" + value + "']", function () {
            let input = $(this);
            let start = input[0].selectionStart;
            $(this).val(function (_, val) {
                return val.toUpperCase();
            });
            input[0].selectionStart = input[0].selectionEnd = start;
        });
    });

}

function formatLinkWarehouse(val, row) {
    return '<a href="/bodega/index/editar-entrada?id=' + row.id + '">' + row.referencia + '</a>';
}

window.consolidasTraficos = function(idMaster, ids) {
    return $.ajax({url: "/bodega/post/consolidar-traficos", dataType: "json", timeout: 10000, type: "POST",
        data: {idMaster: idMaster, ids: ids}
    });
};

let dg;

window.consolidar = function () {    
    let ids = [];
    let rows = dg.datagrid('getSelections');
    for (let i = 0; i < rows.length; i++) {
        ids.push(rows[i].id);
    }
    if (ids.length > 1) {
        $.confirm({title: "Consolidar tráficos", escapeKey: "cerrar", boxWidth: "450px", useBootstrap: false, type: "blue",
            buttons: {
                si: {btnClass: "btn-blue", action: function () {
                        let idMaster = $('input[name=master]:checked').val();
                        let ids = $("#ids").val();
                        $.when( consolidasTraficos(idMaster, ids) ).done(function( res ) {
                            if (res.success === true) {
                                dg.edatagrid("reload");
                                return true;
                            } else {
                                $.alert({title: "Error", type: "red", content: res.message, boxWidth: "350px", useBootstrap: false});
                            }
                        });
                        return false;
                }},
                no: {action: function () {}}
            },
            content: function () {
                let self = this;
                return $.ajax({
                    url: "/bodega/get/consolidar-traficos",
                    method: "get",
                    data: {ids: ids}
                }).done(function (res) {
                    self.setContent(res.html);
                }).fail(function () {
                    self.setContent("Something went wrong.");
                });
            }
        });        
    } else {
        $.alert({title: "Advertencia", type: "red", content: "Debe seleccionar más de una referencia para consolidar.", boxWidth: "250px", useBootstrap: false});
    }
};

window.enviarATrafico = function () {    
    let ids = [];
    let rows = dg.datagrid('getSelections');
    for (let i = 0; i < rows.length; i++) {
        ids.push(rows[i].id);
    }
    if (ids.length === 1) {
        $.confirm({title: "Enviar a tráfico", escapeKey: "cerrar", boxWidth: "450px", useBootstrap: false, type: "blue",
            buttons: {
                si: {btnClass: "btn-blue", action: function () {
                        if ($("#sendTraffic").valid()) {
                            $("#sendTraffic").ajaxSubmit({url: "/bodega/post/enviar-a-trafico", cache: false, dataType: "json", timeout: 3000, type: "POST",
                                success: function (res) {
                                    if (res.success === true) {
                                        window.location.href = "/trafico/index/editar-trafico?id=" + res.id;
                                    }
                                }
                            });
                            
                        }
                        return false;
                }},
                no: {action: function () {}}
            },
            content: function () {
                let self = this;
                return $.ajax({
                    url: "/bodega/get/enviar-a-trafico",
                    method: "get",
                    data: {id: ids[0]}
                }).done(function (res) {
                    self.setContent(res.html);
                }).fail(function () {
                    self.setContent("Something went wrong.");
                });
            }
        });        
    } else {
        $.alert({title: "Advertencia", type: "red", content: "Solo es posible enviar un tráfico.", boxWidth: "250px", useBootstrap: false});
    }
};

window.ordenCarga = function() {
    let ids = [];
    let rows = dg.datagrid('getSelections');
    for (let i = 0; i < rows.length; i++) {
        ids.push(rows[i].id);
    }
    if (ids.length >= 1) {
        $.confirm({title: "Asignar orden de carga", escapeKey: "cerrar", boxWidth: "450px", useBootstrap: false, type: "blue",
            buttons: {
                si: {btnClass: "btn-blue", action: function () {
                    if ($("#loadOrder").valid()) {
                        $("#loadOrder").ajaxSubmit({url: "/bodega/post/asignar-orden-carga", dataType: "json", timeout: 3000, type: "POST",
                            success: function (res) {
                                if (res.success === true) {
                                    dg.edatagrid("reload");
                                }
                            }
                        });
                    } else {
                        return false;
                    }
                }},
                no: {action: function () {}}
            },
            content: function () {
                let self = this;
                return $.ajax({
                    url: "/bodega/get/orden-carga",
                    method: "get",
                    data: {ids: ids}
                }).done(function (res) {
                    self.setContent(res.html);
                }).fail(function () {
                    self.setContent("Something went wrong.");
                });
            }
        });        
    } else {
        $.alert({title: "Advertencia", type: "red", content: "Debe seleccionar más de una referencia para consolidar.", boxWidth: "250px", useBootstrap: false});
    }
};

$(document).ready(function () {

    dg = $("#dg").edatagrid();

    dg.edatagrid({
        pagination: true,
        singleSelect: false,
        striped: true,
        rownumbers: true,
        fitColumns: false,
        pageSize: 20,
        idField: "id",
        queryParams: {
            bodega: true
        },
        url: "/bodega/post/entradas",
        updateUrl: "/bodega/post/entrada-actualizar",
        rowStyler: function (index, row) {},
        onClickRow: function (index, row) {},
        onBeginEdit: function (index, row) {
        },
        onBeforeEdit: function (index, row) {},
        onAfterEdit: function (index, row) {
            $(this).datagrid("refreshRow", index);
        },
        onCancelEdit: function (index, row) {
            row.editing = false;
            $(this).datagrid("refreshRow", index);
        },
        onAdd: function (index, row) {},
        onRowContextMenu: function (e, index, row) {
            e.preventDefault();
            $("#mm").menu("show", {
                left: e.pageX,
                top: e.pageY
            });
        },
        remoteFilter: true,
        toolbar: [
            {
                text: "Guardar",
                iconCls: "icon-save",
                handler: function () {
                    $("#dg").edatagrid("saveRow");
                }
            },
            {
                text: "Cancelar",
                iconCls: "icon-undo",
                handler: function () {
                    $("#dg").edatagrid("cancelRow");
                }
            },
            {
                text: "Actualizar",
                iconCls: "icon-reload",
                handler: function () {
                    $("#dg").edatagrid("reload");
                }
            }
        ],
        frozenColumns: [
            [
                {
                    field: "estatusCarga",
                    width: 20,
                    title: "",
                    formatter: formatEstatus
                },
                {
                    field: "msg",
                    width: 30,
                    checkbox: false,
                    title: "",
                    formatter: formatMensajero
                },
                {
                    field: 'ck', checkbox: true, hidden: false,
                    styler: function (index, row) {
                    }
                },
                {
                    field: "idTrafico",
                    width: 25,
                    title: "",
                    formatter(value, row) {
                        if (value !== null) {
                            return 'C';
                        }
                    }
                },
                {
                    field: "referencia",
                    width: 100,
                    title: "Referencia",
                    formatter: formatLinkWarehouse
                }
            ]
        ],
        columns: [
            [
                {field: "siglas", width: 50, title: "Bodega"},
                {field: "nombreCliente", width: 300, title: "Nombre Cliente"},
                {field: "ordenCarga", width: 120, title: "Orden de carga"},
                {field: "nombre", width: 120, title: "Usuario"},
                {field: "bultos", width: 50, title: "Bultos"},
                {
                    field: "fechaEta",
                    width: 100,
                    title: "F. ETA",
                    editor: {type: "datetimebox"},
                    options: {required: false, validType: "datetime"}
                },
                {
                    field: "fechaEnvioDocumentos",
                    width: 105,
                    title: "F. Envio Doctos.",
                    editor: {type: "datetimebox"},
                    options: {required: false, validType: "datetime"}
                },
                {
                    field: "fechaPago",
                    width: 95,
                    title: "F. Pago",
                    editor: {type: "datetimebox"},
                    options: {required: false, validType: "datetime"}
                },
                {
                    field: "fechaLiberacion",
                    width: 95,
                    title: "F. Liberación",
                    editor: {type: "datetimebox"},
                    options: {required: false, validType: "datetime"}
                },
                {
                    field: "blGuia",
                    width: 150,
                    title: "BL/Guía",
                    editor: {type: "text"}
                },
                {
                    field: "contenedorCaja",
                    width: 150,
                    title: "Cont./Caja",
                    editor: {type: "text"}
                },
                {
                    field: "idPlanta",
                    width: 150,
                    title: "Planta",
                    formatter: function (val, row) {
                        return row.descripcionPlanta;
                    },
                    editor: {
                        type: "combobox",
                        options: {
                            valueField: "id",
                            textField: "descripcion",
                            panelWidth: 250,
                            panelHeight: 90
                        }
                    }
                },
                {
                    field: "diasDespacho",
                    width: 100,
                    title: "Días Despacho",
                    formatter(value, row) {
                        if (row.fechaLiberacion !== null) {
                            return value;
                        }
                    }
                },
                {
                    field: "observaciones",
                    width: 250,
                    title: "Observaciones",
                    editor: {type: "text"}
                }
            ]
        ]
    });

    dg.edatagrid("enableFilter", []);

    initGeneral();

    let today = new Date();
    let dd = today.getDate();
    let mm = today.getMonth() + 1;
    let yyyy = today.getFullYear();

    let dateini;
    let dateend;

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
