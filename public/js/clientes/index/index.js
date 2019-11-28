$(function () {
    loadGraph("#container-qro", $("#year").val(), $("#rfc").val(), $("#name").val(), 3589, 640, 'Querétaro ');
    loadGraph("#container-nl", $("#year").val(), $("#rfc").val(), $("#name").val(), 3589, 240, 'Nuevo Laredo ');
//    loadGraph("#container-aero", $("#year").val(), $("#rfc").val(), $("#name").val(), 3589, 646, 'Aeropuerto ');
});
function loadGraph(id, year, rfc, name, patente, aduana, legend) {
    $.getJSON('/clientes/data/operaciones-clientes', {year: year, rfc: rfc, name: name, patente: patente, aduana: aduana}, function (chartData) {
        if(chartData.success === false) {
            $(id).hide();            
            return false;
        }
        if(chartData) {
            $(id).highcharts({
                title: {
                    text: name
                },
                subtitle: {
                    text: legend + ': ' + patente + '-' + aduana + ' ' + year
                },
                colors: ['#4572A7', '#AA4643', '#89A54E', '#80699B', '#3D96AE', '#DB843D', '#92A8CD', '#A47D7C', '#B5CA92', '#990000', '#009900', '#009955', '#778899', '#445566', '#229944'],
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
        }
    });
}