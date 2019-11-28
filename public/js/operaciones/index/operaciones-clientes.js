$(function () {
    $("#submit").click(function(evt){
        evt.preventDefault();
        callGraphs($("#year").val(), $("#rfc").val(), $("#rfc option:selected").text());
    });    
});

function loadLineGraph(id, url, year, rfc, name, patente, aduana, legend) {
    $.getJSON(url, {year: year, rfc: rfc, name: name, patente: patente, aduana: aduana}, function (chartData) {
        $(id).highcharts({
            title: {
                text: name
            },
            subtitle: {
                text: legend + ': ' + patente + '-' + aduana + ' ' + year
            },
            colors: ['#4572A7', '#AA4643', '#89A54E', '#80699B', '#3D96AE', '#DB843D', 
                '#92A8CD', '#A47D7C', '#B5CA92', '#990000', '#009900', '#009955', '#778899', '#445566', '#229944'],
            xAxis: {
                categories: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
                crosshair: true,
                minPadding: 0, maxPadding: 0
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
            series: chartData
        });
    });
}

function callGraphs(year, rfc, name) {
    $("#container-qro").html('');
    $("#container-nl").html('');
    $("#container-aero").html('');
    $("#container-cve-qro").html('');
    $("#container-cve-nl").html('');
    $("#container-cve-aero").html('');
    loadLineGraph("#container-qro", '/operaciones/data/operaciones-clientes', year, rfc, name, 3589, 640, 'Querétaro ');
    loadLineGraph("#container-nl", '/operaciones/data/operaciones-clientes', year, rfc, name, 3589, 240, 'Nuevo Laredo ');
    loadLineGraph("#container-aero", '/operaciones/data/operaciones-clientes', year, rfc, name, 3589, 646, 'Aeropuerto ');
    loadLineGraph("#container-corp", '/operaciones/data/operaciones-clientes', year, rfc, name, 3589, 645, 'Aeropuerto ');
    loadLineGraph("#container-cve-qro", '/operaciones/data/operaciones-clientes-cve-pedimentos', year, rfc, name, 3589, 640, 'Querétaro ');
    loadLineGraph("#container-cve-nl", '/operaciones/data/operaciones-clientes-cve-pedimentos', year, rfc, name, 3589, 240, 'Nuevo Laredo ');
    loadLineGraph("#container-cve-aero", '/operaciones/data/operaciones-clientes-cve-pedimentos', year, rfc, name, 3589, 646, 'Aeropuerto ');
    loadLineGraph("#container-cve-corp", '/operaciones/data/operaciones-clientes-cve-pedimentos', year, rfc, name, 3589, 645, 'Aeropuerto ');
}