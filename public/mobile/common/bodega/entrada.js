
window.obtenerFacturas = function () {
    var id = $("#id").val();
    $.ajax({
        url: "/mobile/get/obtener-facturas", dataType: "json", timeout: 10000, data: { id: id }, type: "GET",
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

window.obtenerBultos = function () {
    var id = $("#id").val();
    var estatus = $("#estatus").val();
    $.ajax({
        url: "/mobile/get/obtener-bultos", dataType: "json", timeout: 10000, data: { id: id }, type: "GET",
        success: function (res) {
            if (res.success === true) {
                if (res.result.length > 0) {
                    var html = '';
                    for (var i = 0; i < res.result.length; i++) {
                        let row = res.result[i];
                        let uuid = '';
                        if (row['uuid'] !== null)
                            uuid = row['uuid'];

                        html += `<tr>` +
                            `<td>${row['numBulto']}</td>` +
                            `<td>${uuid}</td>` +
                            `<td><a href="/mobile/bodega/editar-bulto?id=${row['id']}&estatus=${estatus}"><i class="fas fa-pencil-alt"></i></a></td>` +
                            `</tr>`;
                    }

                } else {
                    var html = '<tr><td colspan="3"><em>No hay bultos</em></td></tr>';
                }
                $("table.guias > tbody").html(html);
            }
        }
    });
};

$(document).ready(function () {

    obtenerFacturas();
    obtenerBultos();

});