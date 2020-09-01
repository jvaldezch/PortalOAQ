/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
function exportToExcel(datavalues) {
    window.location.href = "/administracion/crud/reportes?" + datavalues + "&excel=true";
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

function grafica(idAduana, fechaInicio, fechaFin) {
    var values;
    $.ajax({
        url: '/administracion/crud/cantidad',
        type: 'GET',
        dataType: 'json',
        data: {idAduana: idAduana, fechaInicio: fechaInicio, fechaFin: fechaFin},
        error: function () {
        },
        success: function (res) {
            if (res.success === true) {
                $("#container").highcharts({
                    chart: { type: 'column' },
                    title: { text: 'Facturación ' + res.aduana },
                    xAxis: {
                        type: 'category'
                    },
                    plotOptions: {
                        series: {
                            dataLabels: {
                                enabled: true,
                                format: '{point.name}<br>{point.y} ({point.p})'
                            }
                        }
                    },
                    tooltip: {
                        headerFormat: '<span style="font-size:11px">{series.name}</span><br>',
                        pointFormat: '<span style="color:{point.color}">{point.name}</span>: <b>{point.y}</b> of total<br/>'
                    },
                    yAxis: {title: { text: 'Cantidad de facturas' }},
                    legend: { enabled: false },
                    series: [{
                            data: [{
                                    name: res.leyendaEnTiempo,
                                    y: parseInt(res.fueraTiempo),
                                    p: res.pFueraTiempo,
                                    color: '#90ed7d'
                                }, {
                                    name: res.leyendaFueraTiempo,
                                    y: parseInt(res.enTiempo),
                                    p: res.pEnTiempo,
                                    color: '#f45555'
                                }]
                        }]
                });
            }
        }
    });
}

function submitForm() {
    if ($("#ff").form('validate') === true) {
        var dg = $('#dg').edatagrid();
        var data = $("#ff").form();
        if (parseInt(data.context.getElementById('tipoReporte').value) === 10) {
            $.messager.alert('Warning', 'The warning message');
        }
        if (parseInt(data.context.getElementById('tipoReporte').value) === 11) {
            $.messager.alert('Warning', 'The warning message');
        }
        if (parseInt(data.context.getElementById('tipoReporte').value) === 12) {
            if (parseInt(data.context.getElementById('idAduana').value) !== 0) {
                grafica(data.context.getElementById('idAduana').value, data.context.getElementById('fechaInicio').value, data.context.getElementById('fechaFin').value);
            }
            var datavalues = $("#ff").serialize();
            dg.edatagrid({
                pagination: true,
                singleSelect: true,
                striped: true,
                rownumbers: true,
                fitColumns: false,
                height: 562,
                method: "get",
                remoteFilter: true,
                url: "/administracion/crud/reportes?" + datavalues,
                pageSize: 20,
                toolbar: [{
                        text: 'Guardar',
                        iconCls: 'icon-save',
                        handler: function () {
                            exportToExcel(datavalues);
                        }
                    }],
                frozenColumns: [[
                        {field: 'FolioID', width: 50, title: 'Folio'},
                        {field: 'Patente', width: 50, title: 'Patente'},
                        {field: 'AduanaID', width: 50, title: 'Aduana'},
                        {field: 'Pedimento', width: 80, title: 'Pedimento'},
                        {field: 'Referencia', width: 100, title: 'Referencia'}
                    ]],
                columns: [[
                        {field: 'Fecha', width: 110, title: 'Fecha Facturación'},
                        {field: 'FechaPedimento', width: 110, title: 'Fecha Pedimento'},
                        {field: 'FechaDiff', width: 100, title: 'Días Diferencia'},
                        {field: 'Nombre', width: 300, title: 'Cliente'},
                        {field: 'Factura', width: 140, title: 'Factura'}
                    ]]
            });
        }
    }
    return;
}

$(document).ready(function () {

    $('#fechaInicio').datebox({
        value: (new Date().toString('dd-MMM-yyyy'))
    });

    var date = new Date();
    $('#fechaInicio').datebox({value: $.fn.datebox.defaults.formatter(new Date(date.getFullYear(), date.getMonth(), 1))});

    $('#fechaFin').datebox({
        value: (new Date().toString('dd-MMM-yyyy'))
    });

});

