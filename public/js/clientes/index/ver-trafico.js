/**
 * 
 */

var tipo_archivos;

window.get_file_type = function(i) {
    if (tipo_archivos[i]) {
        return tipo_archivos[i]['nombre'];
    } else {
        return '';
    }
};

window.format_dates = function(value) {
    if (value) {
        var dateObj = new Date(value);
        var momentObj = moment(dateObj);
        return momentObj.format('YYYY-MM-DD');
    } else {
        return '';
    }
};

window.load_comments = function() {
    $("#traffic-comments").show();
    $.ajax({url: "/clientes/get/obtener-comentarios", dataType: "json", timeout: 10000, type: "GET",
        data: {id_trafico: $("#id_trafico").val()},
        success: function (res) {
            if (res.success === true) {
                if (res.results['bitacora'] !== null) {
                    $("#traffic_log").html('');
                    var rows = res.results['bitacora'];
                    for (var i = 0; i < rows.length; i++) {
                        var row = rows[i];
                        var html = '<tr>';
                        html += '<td style="font-size: 10px !important; line-height: 11px">' + row.bitacora + '</td>';
                        html += '<td style="font-size: 10px !important; line-height: 11px">' + (row.usuario !== null ? row.usuario : '') + '</td>';
                        html += '<td style="text-align: center; font-size: 10px !important; line-height: 11px">' + row.creado + '</td>';
                        html += '</tr>';
                        $("#traffic_log").append(html);
                    }
                } else {
                    $("#traffic_log").html('<tr><td colspan="3" style="font-size: 10px !important; line-height: 11px">No hay comentarios</td></tr>');
                }
                if (res.results['comentarios'] !== null) {
                    $("#traffic_comments").html('');
                    var rows = res.results['comentarios'];
                    for (var i = 0; i < rows.length; i++) {
                        var row = rows[i];
                        var html = '<tr>';
                        html += '<td>' + row.nombre + '</td>';
                        html += '<td>' + (row.mensaje !== null ? row.mensaje : '');
                        if (row.nombreArchivo) {
                            html += '<br><img src="/images/icons/attachment.gif"><span style="font-size: 11px"><a href="/archivo/get/descargar-archivo-temporal?id=' + row.idArchivo + '">' + row.nombreArchivo + '</a></span>';
                        }
                        html += '</td>';
                        html += '<td>' + row.creado + '</td>';
                        html += '<tr>';
                        $("#traffic_comments").append(html);
                    }
                } else {
                    $("#traffic_comments").html('<tr><td colspan="3" style="font-size: 10px !important; line-height: 11px">No hay comentarios</td></tr>');
                }
                if (res.results['archivos'] !== null) {
                    $("#attached_files").html('');
                    var row = res.results['archivos'];
                    for (var i = 0; i < row.length; i++) {
                        $("#attached_files").append('<img src="/images/icons/attachment.gif"><span style="font-size: 11px"><a href="/archivo/get/descargar-archivo-temporal?id=' + row.idArchivo + '">' + row.nombreArchivo + '</a></span><br>');
                    }
                }
            } else {
                $("#traffic_log").html('<tr><td colspan="3" style="font-size: 10px !important; line-height: 11px">No hay comentarios</td></tr>');
            }
        }       
    });
};

window.load_files = function() {
    $('#traffic_files').show();
    $.ajax({url: "/clientes/get/obtener-archivos", dataType: "json", timeout: 30000, type: "GET",
        data: {id_trafico: $("#id_trafico").val()},
        beforeSend: function() {
            $('#traffic_files').LoadingOverlay('show', {color: 'rgba(255, 255, 255, 0.9)'});
        },
        success: function (res) {
            $('#traffic_files').LoadingOverlay('hide');
            if (res.success === true) {
                var table_row;
                for (var i = 0; i < res.results.length; i++) {
                    var row = res.results[i];
                    var table_row = '';
                    table_row += '<tr>';
                    if (row.exists === true) {
                        table_row += '<td><a class="traffic-a" href="/clientes/get/descargar-archivo?id=' + row.id + '">' + row.nom_archivo + '</a></td>';
                    } else {
                        table_row += '<td>' + row.nom_archivo + '</td>';
                    }
                    table_row += '<td>' + get_file_type(row.tipo_archivo) + '</td>';
                    table_row += '<td style="text-align: center">' + format_dates(row.creado) + '</td>';
                    table_row += '<td style="text-align: center">' + row.usuario.toUpperCase() + '</td>';
                    table_row += '</tr>';
                    $("#table_files").find('tbody').append(table_row);
                }
            }
        },
        complete: function (res) {
            $('#traffic_files').LoadingOverlay('hide');
        }
    });
};

window.tipos_archivos = function() {
    if (localStorage.getItem("tipo_archivos") === null) {
        return $.ajax({url: '/clientes/get/obtener-tipos-archivos',
            success: function (res) {
                if (res.success === true) {
                    tipo_archivos = res.results;
                    localStorage.setItem("tipo_archivos", JSON.stringify(tipo_archivos));
                    return true;
                }
            }
        });
    } else {
        tipo_archivos = JSON.parse(localStorage.getItem("tipo_archivos"));
    }
};

window.current_active = function() {
    if(Cookies.get("active") === "#information") {
        load_comments();
    }
    if(Cookies.get("active") === "#files") {
        load_files();
    }
};

$(document).ready(function () {
    
    $.when(tipos_archivos()).done(function (res) {
        
    });
    
    var valid = ["#information", "#files"];

    $(document.body).on("click", "#traffic-tabs li a", function() {
        var href = $(this).attr("href");
        Cookies.set("active", href);
        current_active();
    });

    if (Cookies.get("active") !== undefined) {
        if(valid.indexOf(Cookies.get("active")) !== -1) {
            $("a[href='" + Cookies.get("active") + "']").tab("show");
            current_active();
        } else {
            $("a[href='#information']").tab("show");
            Cookies.set("active", "#information");
            current_active();
        }
    } else {
        $("a[href='#information']").tab("show");
        Cookies.set("active", "#information");
        current_active();
    }
    
});

