$(function () {
    $.getJSON('/operaciones/data/operaciones-por-usuarios', { year: $("#year").val(), patente: $("#patente").val(), aduana: $("#aduana").val() }, function(chartData) {
        $('#container').highcharts({
            chart: {
                type: 'column',
                spacingBottom: 15,
                spacingTop: 5,
                spacingLeft: 5,
                spacingRight: 5
            },
            title: {
                text: 'PEDIMENTOS PAGADOS ' +  + $("#year").val() + ', ' + $("#patente").val() + '-' + $("#aduana").val()
            },
            colors: ['#4572A7', '#AA4643', '#89A54E', '#80699B', '#3D96AE', '#DB843D', '#92A8CD', '#A47D7C', '#B5CA92', '#990000', '#009900', '#009955', '#778899', '#445566','#229944'],
            xAxis: {
                categories: ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'],
                crosshair: true,
                minPadding:0, maxPadding:0
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
    
    $.getJSON('/operaciones/data/operaciones-totales', { year: $("#year").val(), patente: $("#patente").val(), aduana: $("#aduana").val() }, function(chartData) {
        $('#container-line').highcharts({
            title: {
                text: 'PEDIMENTOS PAGADOS ' + $("#year").val() + ', ' + $("#patente").val() + '-' + $("#aduana").val()
            },
            colors: ['#4572A7', '#AA4643', '#89A54E', '#80699B', '#3D96AE', '#DB843D', '#92A8CD', '#A47D7C', '#B5CA92', '#990000', '#009900', '#009955', '#778899', '#445566','#229944'],
            xAxis: {
                categories: ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'],
                crosshair: true,
                minPadding:0, maxPadding:0
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
    
    $.getJSON('/operaciones/data/operaciones-sumarizadas', { year: $("#year").val(), patente: $("#patente").val(), aduana: $("#aduana").val() }, function(chartData) {
        $('#container-pie').highcharts({
            chart: {
                plotBackgroundColor: null,
                plotBorderWidth: null,
                plotShadow: false,
                type: 'pie'
            },
            title: {
                text: 'PEDIMENTOS PAGADOS ' + $("#year").val() + ', ' + $("#patente").val() + '-' + $("#aduana").val()
            },
            colors: ['#4572A7', '#AA4643', '#89A54E', '#80699B', '#3D96AE', '#DB843D', '#92A8CD', '#A47D7C', '#B5CA92', '#990000', '#009900', '#009955', '#778899', '#445566','#229944'],
            xAxis: {
                categories: ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'],
                crosshair: true,
                minPadding:0, maxPadding:0
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
                name: "Pedimentos pagados",
                colorByPoint: true,
                data: chartData
            }]
        });
    });
});