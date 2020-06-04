/**
 * programmed by Jvaldez at gmail
 * 2015.may.14
 */

var getUrlParameter = function getUrlParameter(sParam) {
    var sPageURL = decodeURIComponent(window.location.search.substring(1)),
        sURLVariables = sPageURL.split('&'),
        sParameterName,
        i;
    for (i = 0; i < sURLVariables.length; i++) {
        sParameterName = sURLVariables[i].split('=');
        if (sParameterName[0] === sParam) {
            return sParameterName[1] === undefined ? true : sParameterName[1];
        }
    }
};

var chartUsers;

function semaforo(year, month, idCliente, idAduana) {
    $.ajax({
        url: '/trafico/crud/grafica-semaforos',
        type: "GET",
        dataType: "json",
        data: {year: year, idCliente: idCliente, idAduana: idAduana},
        success: function(res) {
            if (res.success === true) {
                console.log(res.data);
                $("#semaforo").highcharts({
                    chart: { type: 'column' },
                    title: { text: `Rojo ${year - 1} vs ${year}` },
                    subtitle: {  },
                    xAxis: {
                        categories: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
                        type: 'category'
                    },
                    yAxis: {title: { text: 'Operaciones con semaforo rojo' }},
                    legend: { enabled: false },
                    plotOptions: {
                        series: {
                            borderWidth: 0,
                            dataLabels: {
                                enabled: true,
                                format: '{point.y}'
                            }
                        }
                    },
                    tooltip: {
                        headerFormat: '<span style="font-size:11px">{series.name}</span><br>',
                        pointFormat: '<span style="color:{point.color}">{point.name}</span>: <b>{point.y}</b><br/>'
                    },
                    series: [{ 
                        name: `Rojos ${year - 1}`, 
                        colorByPoint: true, 
                        data: res.data[0]
                    },{ 
                        name: `Rojos ${year}`, 
                        colorByPoint: true, 
                        data: res.data[1]
                    }]
                });
            }
        },
        cache: false
    });
}

$(document).ready(function () {
    
    var today = new Date();
    var iso = today.toISOString().substring(0, 10);
    var d = iso.split("-");
    
    if (getUrlParameter('month')) {
        $("#month").val(getUrlParameter('month'));
    } else {
        $("#month").val(parseInt(d[1]));
    }
    
    if (getUrlParameter('year')) {
        $("#year").val(getUrlParameter('year'));
    } else {
        $("#year").val(parseInt(d[0]));
    }
    
    if (getUrlParameter('idCliente')) {
        $("#idCliente").val(getUrlParameter('idCliente'));
    } else {
        $("#idCliente").val(parseInt(d[0]));
    }
    
    if (getUrlParameter('idAduana')) {
        $("#idAduana").val(getUrlParameter('idAduana'));
    } else {
        $("#idAduana").val(parseInt(d[0]));
    }
    
    semaforo($("#year").val(), $("#month").val(), $("#idCliente").val(), $("#idAduana").val());
    
});