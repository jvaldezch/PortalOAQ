
window.obtenerFacturas = function () {
    var id = $("#id").val();
    $.ajax({url: "/mobile/get/obtener-facturas", dataType: "json", timeout: 10000, data: {id: id}, type: "GET",
        success: function (res) {
            if (res.success === true) {
                if (res.result.length > 0) {
                    var html = '';
                    for (var i = 0; i < res.result.length; i++) {
                        var row = res.result[i];
                        html += "<tr><td>" + row['numFactura'] + 
                                "</td><td>" + ((row['cove'] !== null) ? row['cove'] : '') + 
                                "</td><td><a href=\"/mobile/traficos/editar-factura?id=" + row['id'] + "\"><i class=\"fas fa-pencil-alt\"></i></a></td></tr>";
                    }
                } else {
                    var html = '<tr><td colspan="3"><em>No hay facturas</em></td></tr>';                    
                }
                $("table.facturas > tbody").html(html);
            }
        }
    });
};

window.obtenerGuias = function () {
    var id = $("#id").val();
    $.ajax({url: "/mobile/get/obtener-guias", dataType: "json", timeout: 10000, data: {id: id}, type: "GET",
        success: function (res) {
            if (res.success === true) {
                if (res.result.length > 0) {
                    var html = '';
                    for (var i = 0; i < res.result.length; i++) {
                        var row = res.result[i];
                        html += "<tr>" + 
                                "<td>" + row['guia'] + "</td>" +
                                "<td>" + ((row['tipo'] !== null) ? row['tipo'] : '') + "</td>" +
                                "<td><a href=\"/mobile/traficos/editar-guia?id=" + row['id'] + "\"><i class=\"fas fa-pencil-alt\"></i></a></td>" + 
                                "</tr>";
                    }
                    
                } else {
                    var html = '<tr><td colspan="3"><em>No hay guías</em></td></tr>';                    
                }
                $("table.guias > tbody").html(html);
            }
        }
    });
};

$(document).ready(function () {

    obtenerFacturas();
    obtenerGuias();

});