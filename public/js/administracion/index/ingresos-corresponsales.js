/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

var form = "#form";

var options = {
    dataType: "json",
    success: function (res) {
        if (res.success === true) {
            $("#result").html(res.html);
            $("#graph").show();
            graph(res.ingresos, res.egresos, $("#corresponsal option:selected").text());
        }
    }
};

function graph(ingresos, egresos, title) {
    var ing = [];
    var egr = [];
    $.each(ingresos, function (i, item) {
        if (i === '12') {
            return true;
        }
        ing.push(parseFloat(item));
    });
    $.each(egresos, function (i, item) {
        if (i === '12') {
            return true;
        }
        egr.push(parseFloat(item));
    });
    $("#graph").highcharts({
        chart: {
            type: 'column'
        },
        title: {
            text: title
        },
        colors: ['#4572A7', '#AA4643', '#89A54E', '#80699B', '#3D96AE', '#DB843D',
            '#92A8CD', '#A47D7C', '#B5CA92', '#990000', '#009900', '#009955', '#778899', '#445566', '#229944'],
        xAxis: {
            categories: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic', 'Prom'],
            crosshair: true,
            minPadding: 0,
            maxPadding: 0
        },
        yAxis: {
            min: 0,
            title: {
                text: 'MXN'
            }
        },
        tooltip: {
            headerFormat: '<span style="font-size:14px"><strong>{point.key}</strong></span><table>',
            pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
                    '<td style="padding:0"><b>{point.y:,.0f}</b></td></tr>',
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
                name: 'Ingresos',
                data: ing
            }, {
                name: 'Egresos',
                data: egr
            }]
    });
}

$(document).ready(function () {

    $(form).validate({
        rules: {
            year: "required",
            corresponsal: "required"
        },
        messages: {
            year: "Campo requerido.",
            corresponsal: "Debe seleccionar."
        }
    });

    $("#submit").click(function (e) {
        e.preventDefault();
        if ($(form).valid()) {
            $(form).ajaxSubmit(options);
        }
    });

});
