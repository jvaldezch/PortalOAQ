
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


function exportToExcel() {
    window.location.href = "/clientes/data/reporte-cuenta-de-gastos?fechaIni=" + dateini + "&fechaFin=" + dateend + "&excel=true";
}
    
$(document).ready(function () {

    var dg = $("#dg").edatagrid();

    dg.edatagrid({
        pagination: true,
        singleSelect: true,
        striped: true,
        rownumbers: true,
        fitColumns: false,
        pageSize: 20,
        idField: 'id',
        method: 'GET',
        queryParams: {
            fechaIni: dateini,
            fechaFin: dateend
        },
        url: '/clientes/data/reporte-cuenta-de-gastos',
        remoteFilter: true,
        toolbar: [{
                text: 'Guardar en Excel',
                iconCls: 'icon-download',                
                handler: function () {
                    exportToExcel();
                }
            }],
        frozenColumns: [[
                {field: 'patente', width: 50, title: 'Patente'},
                {field: 'aduana', width: 50, title: 'Aduana'},
                {field: 'pedimento', width: 80, title: 'Pedimento'},
                {field: 'referencia', width: 100, title: 'Referencia'},
                {field: 'factura', width: 90, title: 'Folio'}
            ]],
        columns: [[
                {field: 'regimen', width: 70, title: 'Regimen'},
                {field: 'ie', width: 50, title: 'I/E'},
                {field: 'anticipo', width: 90, title: 'Anticipo'},
                {field: 'honorarios', width: 90, title: 'Honorarios'},
                {field: 'valor', width: 90, title: 'Valor'},
                {field: 'valor_aduana', width: 90, title: 'Valor Aduana'},
                {field: 'iva', width: 90, title: 'I.V.A.'},
                {field: 'fecha_pedimento', width: 90, title: 'F. Pedimento'},
                {field: 'ref_factura', width: 90, title: 'Ref. Factura'},
                {field: 'bultos', width: 90, title: 'Bultos'},
                {field: 'sub_total', width: 90, title: 'Sub total'},
                {field: 'total', width: 90, title: 'Total'}
            ]]
    });
    
    var customToolbar = '<td style="padding-left: 5px"><span><span class="l-btn-text">Desde</span><input id="dateini" style="width:100px; text-align: center"></span></td>';
    customToolbar += '<td style="padding-left: 5px"><span><span class="l-btn-text">Hasta</span><input id="dateend" style="width:100px; text-align: center"></span></td>';

    $(".datagrid-toolbar").find("table > tbody > tr").append(customToolbar);

    $("#dateini").datebox({
        value: dateini,
        required: true,
        showSeconds: false,
        onChange: function (newValue) {
            Cookies.set('dateini', newValue);
            dateini = newValue;
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
            dateend = newValue; 
            dg.edatagrid('reload', {
                fechaIni: dateini,
                fechaFin: newValue
            });
        }
    });

});
  