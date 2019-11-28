$(function () {
    $('#fecha').datepicker({
        calendarWeeks: true,
        autoclose: true,
        language: 'es',
        format: 'yyyy-mm-dd'
    });

    callGraphs($("#year").val(), $("#month").val(), $("#day").val());
});

function callGraphs(year, month, day) {
    $("#container-qro").html('');
    $("#container-aero").html('');
    $("#container-nl").html('');
    loadBarGraph("#container-qro", '/operaciones/data/operaciones-diarias-clientes', year, month, day, 3589, 640, 'Querétaro ');
    loadBarGraph("#container-aero", '/operaciones/data/operaciones-diarias-clientes', year, month, day, 3589, 646, 'Aeropuerto (Qro) ');
    loadBarGraph("#container-nl", '/operaciones/data/operaciones-diarias-clientes', year, month, day, 3589, 240, 'Nuevo Laredo ');
    loadPieGraph("#container-users-qro", '/operaciones/data/operaciones-diarias-usuarios', year, month, day, 3589, 640, 'Querétaro ');
    loadPieGraph("#container-users-aero", '/operaciones/data/operaciones-diarias-usuarios', year, month, day, 3589, 646, 'Aeropuerto ');
    loadPieGraph("#container-users-nl", '/operaciones/data/operaciones-diarias-usuarios', year, month, day, 3589, 240, 'Nuevo Laredo ');
}
function loadBarGraph(id, url, year, month, day, patente, aduana, legend) {
    $.getJSON(url, {year: year, month: month, day: day, patente: patente, aduana: aduana}, function (chartData) {
        $(id).highcharts({
            chart: {
                type: 'column'
            },
            title: {
                text: name
            },
            subtitle: {
                text: legend + ' ' + patente + '-' + aduana + ' ' + day + '/' + month + '/' + year
            },
            colors: ['#4572A7', '#AA4643', '#89A54E', '#80699B', '#3D96AE', '#DB843D',
                '#92A8CD', '#A47D7C', '#B5CA92', '#990000', '#009900', '#009955', '#778899', '#445566', '#229944'],
            xAxis: {
                categories: []
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
            plotOptions: {
                column: {
                    pointPadding: 0.2,
                    borderWidth: 0
                }
            },
            series: [{
                    name: 'Clientes',
                    data: chartData
                }]
        });
    });
}
function loadPieGraph(id, url, year, month, day, patente, aduana, legend) {
    $.getJSON(url, {year: year, month: month, day: day, patente: patente, aduana: aduana}, function (chartData) {
        $(id).highcharts({
            chart: {
                plotBackgroundColor: null,
                plotBorderWidth: null,
                plotShadow: false,
                type: 'pie'
            },
            title: {
                text: name
            },
            subtitle: {
                text: legend + ' ' + patente + '-' + aduana + ' ' + day + '/' + month + '/' + year
            },
            colors: ['#4572A7', '#AA4643', '#89A54E', '#80699B', '#3D96AE', '#DB843D',
                '#92A8CD', '#A47D7C', '#B5CA92', '#990000', '#009900', '#009955', '#778899', '#445566', '#229944'],
            xAxis: {
                categories: []
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
            plotOptions: {
                column: {
                    pointPadding: 0.2,
                    borderWidth: 0
                }
            },
            series: [{
                    name: 'Pedimentos pagados',
                    data: chartData
                }]
        });
    });
}