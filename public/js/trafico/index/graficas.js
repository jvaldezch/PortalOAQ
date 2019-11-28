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

function usuarios(year, month, idCliente, idAduana) {
    $.ajax({
        url: '/trafico/crud/grafica-usuarios',
        type: "GET",
        dataType: "json",
        data: {year: year, month: month, idCliente: idCliente, idAduana: idAduana},
        success: function(res) {
            if (res.success === true) {
                $("#porUsuarios").highcharts({
                    chart: {
                        plotBackgroundColor: null,
                        plotBorderWidth: null,
                        plotShadow: false,
                        type: 'pie'
                    },
                    title: {
                        text: 'Tráficos por usuario, ' + year
                    },
                    tooltip: {
                        pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
                    },
                    plotOptions: {
                        pie: {
                            allowPointSelect: true,
                            cursor: 'pointer',
                            dataLabels: {
                                enabled: true,
                                format: '<b>{point.name}</b>: {point.percentage:.1f} %',
                                style: {
                                    color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black'
                                }
                            }
                        }
                    },
                    series: [{
                        name: 'Usuarios',
                        colorByPoint: true,
                        data: res.results
                    }]
                });
            }
        },
        cache: false
    });
}

function cumplimiento(year, month, idCliente, idAduana) {
    $.ajax({
        url: '/trafico/crud/grafica-cumplimiento',
        type: "GET",
        dataType: "json",
        data: {year: year, month: month, idCliente: idCliente, idAduana: idAduana},
        success: function(res) {
            if (res.success === true) {
                $("#cumplimiento").highcharts({
                    chart: {
                        plotBackgroundColor: null,
                        plotBorderWidth: null,
                        plotShadow: false,
                        type: 'pie'
                    },
                    title: {
                        text: 'Cumplimiento, ' + year
                    },
                    tooltip: {
                        pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
                    },
                    plotOptions: {
                        pie: {
                            allowPointSelect: true,
                            cursor: 'pointer',
                            dataLabels: {
                                enabled: true,
                                format: '<b>{point.name}</b>: {point.percentage:.1f} %',
                                style: {
                                    color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black'
                                }
                            }
                        }
                    },
                    series: [{
                        name: 'Usuarios',
                        colorByPoint: true,
                        data: res.results
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
    
    usuarios($("#year").val(), $("#month").val(), $("#idCliente").val(), $("#idAduana").val());
    cumplimiento($("#year").val(), $("#month").val(), $("#idCliente").val(), $("#idAduana").val());
    
});