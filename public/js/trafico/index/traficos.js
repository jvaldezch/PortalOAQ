/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function zeroPad(num, places) {
  var zero = places - num.toString().length + 1;
  return Array(+(zero > 0 && zero)).join("0") + num;
}


var dg;

var dateini;
var dateend;

window.descargaReporte = function() {
    window.location.href = '/trafico/reportes/traficos?fechaInicio=' + dateini + '&fechaFin=' + dateend + '&filterRules=' + JSON.stringify(dg.datagrid('options').filterRules);
};

$(document).ready(function() {
    
    var today = new Date();
    var dd = today.getDate();
    var mm = today.getMonth() + 1;
    var yyyy = today.getFullYear();

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

    dg = $("#dg").edatagrid();

    dg.edatagrid({
        pagination: true,
        singleSelect: true,
        striped: true,
        rownumbers: true,
        fitColumns: false,
        pageSize: 20,
        idField: "id",
        url: "/trafico/crud/traficos",
        updateUrl: "/trafico/crud/trafico-actualizar",
        rowStyler: function (index, row) {
            if (row.diasRetraso >= 3) {
                return 'background-color: #ffdada;';
            }
        },
        queryParams: {
            fechaInicio: dateini,
            fechaFin: dateend
	},
        onClickRow: function (index, row) {},
        onBeginEdit: function (index, row) {
            //            var tc = $('#dg').datagrid('getEditor', {
            //                index: index,
            //                field: 'tipoCarga'
            //            });
            //            if (tc !== undefined) {
            //                $(tc.target).combobox('reload', '/trafico/crud/tipo-carga?patente=' + row.patente + '&aduana=' + row.aduana);
            //            }
        },
        onBeforeEdit: function (index, row) {},
        onAfterEdit: function (index, row) {
            if (row.fechaPago !== "") {
                row.estatus = 2;
            }
            if (row.fechaLiberacion !== "") {
                row.estatus = 3;
            }
            row.editing = false;
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
            },
            {
                text: "Descarga",
                iconCls: "icon-download",
                handler: function (param) {
                    descargaReporte();
                }
            }
        ],
        frozenColumns: [
            [
                {
                    field: "estatus",
                    width: 20,
                    title: "",
                    formatter: formatEstatus
                },
                {
                    field: "imex",
                    width: 30,
                    checkbox: false,
                    title: "",
                    formatter: formatImpo
                },
                {
                    field: "msg",
                    width: 30,
                    checkbox: false,
                    title: "",
                    formatter: formatMensajero
                },
                {
                    field: "upl",
                    width: 30,
                    checkbox: false,
                    title: "",
                    formatter: formatUpload
                },
                {field: 'estatusExpediente', width: 30, title: "",
                    formatter: formatArchive},
                {field: "patente", width: 50, title: "Patente"},
                {field: "aduana", width: 50, title: "Aduana"},
                {field: "pedimento", width: 80, title: "Pedimento"},
                {
                    field: "referencia",
                    width: 100,
                    title: "Referencia",
                    formatter: formatLink
                }
            ]
        ],
        columns: [
            [
                {field: "cvePedimento", width: 40, title: "Cve."},
                {field: "nombreCliente", width: 320, title: "Nombre Cliente"},
                {field: "coves", width: 30, title: "CV"},
                {field: "edocuments", width: 30, title: "ED"},
                {field: "nombre", width: 120, title: "Usuario"},
                {
                    field: "fechaEta",
                    width: 90,
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
                    field: "fechaFacturacion",
                    width: 100,
                    title: "F. Facturación",
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
                    field: "fechaInstruccionEspecial",
                    width: 90,
                    title: "Justificación",
                    formatter(value, row) {
                        if (row.fechaInstruccionEspecial !== null) {
                            return "Si";
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
                    field: "cumplimientoOperativo",
                    width: 100,
                    title: "Cump. Ope.",
                    formatter: function (value, row) {
                        if (parseInt(row.cumplimientoOperativo) === 1) {
                            return 'Si';
                        }
                        if (parseInt(row.cumplimientoOperativo) === 0) {
                            return 'No';
                        }
                    },
                    editor: {
                        type: 'combobox',
                        options: {
                            panelHeight: 'auto',
                            valueField: 'value',
                            textField: 'name',
                            data: [
                                {
                                    value: '0',
                                    name: 'No'
                                },
                                {
                                    value: '1',
                                    name: 'Si'
                                }
                            ]
                        }
                    }
                },
                {
                    field: "observaciones",
                    width: 250,
                    title: "Observaciones",
                    editor: {type: "text"}
                },
                {field: 'ccConsolidado', width: 120, title: 'CC. Consolidado', editor: {type: "text"}},
                {field: 'semaforo', width: 100, title: 'Semaforo',
                    formatter(value, row) {
                        if (parseInt(row.semaforo) === 1) {
                            return 'Verde';
                        } else if (parseInt(row.semaforo) === 2) {
                            return 'Rojo';
                        } else {
                            return '';
                        }

                    }}
            ]
        ]
    });

    dg.edatagrid("enableFilter", []);

    initGeneral();

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

    $(document.body).on('click', '#inventario', function () {
        var dateTime = new Date();
        dateTime = moment(dateTime).format("YYYY-MM-DD");
        window.open("/trafico/crud/traficos-inventario?fechaIni=" + dateTime + "&fechaFin=" + dateTime + "&tipo=80&tipoAduana=0", "reporteInventario", "toolbar=0,location=0,menubar=0,height=550,width=880,scrollbars=yes");
    });
  
});
