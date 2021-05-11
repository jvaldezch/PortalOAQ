var dg;

function zeroPad(num, places) {
    var zero = places - num.toString().length + 1;
    return Array(+(zero > 0 && zero)).join("0") + num;
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

var baseurl = "/clientes/get/trafico";

function exportToExcel() {
    window.location.href = baseurl + "?fechaIni=" + dateini + "&fechaFin=" + dateend + "&excel=true";
}

window.format_dates = function (value, row) {
    if (value) {
        var dateObj = new Date(value);
        var momentObj = moment(dateObj);
        return momentObj.format('YYYY-MM-DD');
    } else {
        return '';
    }
};

window.format_link = function (value, row) {
    return '<a href="/clientes/index/ver-trafico?id=' + row.id + '">' + row.referencia + '</a>';
};

window.format_traffic_light = function (value, row) {
    if (row.semaforo === 1) {
        return 'Verde';
    } else if (row.semaforo === 2) {
        return 'Rojo';
    } else {
        return '';
    }
};

window.format_impo = function (value, row) {
    if (row.ie === "TOCE.IMP") {
        return '<i class="fas fa-arrow-circle-down" style="font-size: 1.2em; color: #2f3b58; padding-top: 2px"></i>';
    } else {
        return '<i class="fas fa-arrow-circle-up" style="font-size: 1.2em; color: #2e963a; padding-top: 2px"></i>';
    }
};


$(document).ready(function () {

    dg = $("#dg").edatagrid();

    dg.edatagrid({
        pagination: true,
        singleSelect: true,
        striped: true,
        rownumbers: false,
        fitColumns: true,
        pageSize: 20,
        idField: 'id',
        method: 'GET',
        queryParams: {
            fechaIni: dateini,
            fechaFin: dateend
        },
        url: baseurl,
        remoteFilter: true,
        toolbar: [{
            text: 'Guardar en Excel',
            iconCls: 'icon-download',
            handler: function () {
                exportToExcel();
            }
        }],
        frozenColumns: [[
            {
                field: 'imex', width: 30, title: 'I/E',
                formatter: format_impo
            },
            { field: 'patente', width: 50, title: 'Patente' },
            { field: 'aduana', width: 50, title: 'Aduana' },
            { field: 'pedimento', width: 80, title: 'Pedimento' },
            {
                field: 'referencia', width: 150, title: 'Referencia',
                formatter: format_link
            }
        ]],
        columns: [[
            { field: "cvePedimento", width: 40, title: "Cve." },
            {
                field: "fechaEta", width: 90, title: "F. ETA",
                formatter: format_dates
            },
            {
                field: "fechaPago", width: 85, title: "F. Pago",
                formatter: format_dates
            },
            { field: "blGuia", width: 105, title: "BL/Guía" },
            { field: "contenedorCaja", width: 105, title: "Cont./Caja" },
            { field: "observaciones", width: 250, title: "Observaciones" },
            {
                field: "semaforo", width: 150, title: "Semaforo",
                formatter: format_traffic_light
            }
        ]]
    });

    dg.edatagrid("enableFilter", []);

    var customToolbar = '<td style="padding-left: 5px"><span><span class="l-btn-text">Desde</span><input id="dateini" style="width:100px; text-align: center"></span></td>';
    customToolbar += '<td style="padding-left: 5px"><span><span class="l-btn-text">Hasta</span><input id="dateend" style="width:100px; text-align: center"></span></td>';

    $(".datagrid-toolbar").find("table > tbody > tr").append(customToolbar);

    $("#dateini").datebox({
        value: dateini,
        required: true,
        showSeconds: false,
        onChange: function (newValue) {
            Cookies.set('dateini', newValue);
            dg.edatagrid('reload', {
                fechaIni: newValue,
                fechaFin: dateend
            });
        }
    });

    $("#dateend").datebox({
        value: dateend,
        required: true,
        showSeconds: false,
        onChange: function (newValue) {
            Cookies.set('dateend', newValue);
            dg.edatagrid('reload', {
                fechaIni: dateini,
                fechaFin: newValue
            });
        }
    });

    $.each(['imex', 'msg', 'coves', 'edocuments', 'upl', 'ie', 'estatusExpediente', 'cvePedimento', 'fechaPago', 'estatus', 'fechaEtd', 'fechaLiberacion', 'fechaEntrada', 'fechaPresentacion', 'fechaFacturacion', 'fechaEta', 'fechaRevalidacion', 'fechaPrevio', 'fechaDespacho', 'fechaEtaAlmacen', 'fechaEnvioProforma', 'fechaEnvioDocumentos', 'fechaNotificacion', 'fechaDeposito', 'fechaCitaDespacho', 'fechaProformaTercero', 'fechaArriboTransfer', 'fechaSolicitudTransfer', 'fechaVistoBueno', 'facturas', 'cantidadFacturas', 'cantidadPartes', 'almacen', 'fechaVistoBuenoTercero', 'fechaComprobacion', 'tipoCarga', 'fechaEir', 'fechaInstruccionEspecial', 'idPlanta', 'diasDespacho', 'estatusRepositorio', 'observaciones', 'cumplimientoAdministrativo', 'cumplimientoOperativo', 'ccConsolidado', 'semaforo'], function (index, value) {
        $(".datagrid-editable-input[name='" + value + "']").hide();
    });

    $.each(['referencia'], function (index, value) {
        $(document.body).on("input", ".datagrid-editable-input[name='" + value + "']", function () {
            var input = $(this);
            var start = input[0].selectionStart;
            $(this).val(function (_, val) {
                return val.toUpperCase();
            });
            input[0].selectionStart = input[0].selectionEnd = start;
        });
    });

});
