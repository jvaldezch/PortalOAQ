var months = [ "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", 
               "Julio", "Augosto", "Septiembre", "Octobre", "Noviembre", "Diciembre" ];
           
$(function () {
    callGraphs($("#year").val(), $("#month").val());
});

function callGraphs(year, month) {
    loadBarGraph("#container-monthly", '/operaciones/data/operaciones-mensuales', year, month, 'Operaciones totales ');
    loadBarImpExpGraph("#container-monthly-impexp-qro", '/operaciones/data/operaciones-mensuales-imp-exp', year, month, 3589, 640, 'OPE.ESP');
    loadBarImpExpGraph("#container-monthly-impexp-aero", '/operaciones/data/operaciones-mensuales-imp-exp', year, month, 3589, 646, 'AEROPUERTO');
    loadBarImpExpGraph("#container-monthly-impexp-nl", '/operaciones/data/operaciones-mensuales-imp-exp', year, month, 3589, 240, 'NVO.LAREDO');
}
function loadBarGraph(id, url, year, month, legend) {
    $.getJSON(url, {year: year, month: month }, function (chartData) {
        $(id).highcharts({
            chart: {
                type: 'column'
            },
            title: {
                text: 'OPERACIONES MENSUALES'
            },
            subtitle: {
                text: 'PEDIMENTOS PAGADOS ' + months[(month-1)] + ', ' + year
            },
            colors: ['#4572A7', '#AA4643', '#89A54E', '#80699B', '#3D96AE', '#DB843D',
                '#92A8CD', '#A47D7C', '#B5CA92', '#990000', '#009900', '#009955', '#778899', '#445566', '#229944'],
            xAxis: {
                categories: [1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31]
            },
            yAxis: {
                min: 0,
                title: {
                    text: 'Pedimentos'
                }
            },
            tooltip: {
                headerFormat: '<span style="font-size:14px"><strong>{point.key}</strong></span><table>',
                pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
                        '<td style="padding:0"><b>{point.y}</b></td></tr>',
                footerFormat: '</table>',
                shared: true,
                useHTML: true
            },
            series: chartData
        });
    });
}
function loadBarImpExpGraph(id, url, year, month, patente, aduana, legend) {
    $.getJSON(url, {year: year, month: month, patente: patente, aduana: aduana }, function (chartData) {
        $(id).highcharts({
            chart: {
                type: 'column'
            },
            title: {
                text: 'IMPOS/EXPOS'
            },
            subtitle: {
                text: legend + ' ' + months[(month-1)] + ', ' + year
            },
            colors: ['#4572A7', '#AA4643', '#89A54E', '#80699B', '#3D96AE', '#DB843D',
                '#92A8CD', '#A47D7C', '#B5CA92', '#990000', '#009900', '#009955', '#778899', '#445566', '#229944'],
            xAxis: {
                categories: [1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31]
            },
            yAxis: {
                min: 0,
                title: {
                    text: 'Pedimentos'
                }
            },
            tooltip: {
                headerFormat: '<span style="font-size:14px"><strong>{point.key}</strong></span><table>',
                pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
                        '<td style="padding:0"><b>{point.y}</b></td></tr>',
                footerFormat: '</table>',
                shared: true,
                useHTML: true
            },
            series: chartData
        });
    });
}