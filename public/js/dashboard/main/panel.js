

var total;
var page = 1;
var nextPage = 1;
var pages = 1;
var limit = 10;
var pause = false;
var time = 25000;
var contentHeight = 0;
var rowHeight = 45;

var documents;

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

function getAllUrlParams(url) {

    var queryString = url ? url.split('?')[1] : window.location.search.slice(1);
    var obj = {};

    if (queryString) {
        queryString = queryString.split('#')[0];
        var arr = queryString.split('&');

        for (var i = 0; i < arr.length; i++) {
            var a = arr[i].split('=');

            var paramNum = undefined;
            var paramName = a[0].replace(/\[\d*\]/, function (v) {
                paramNum = v.slice(1, -1);
                return '';
            });

            var paramValue = typeof (a[1]) === 'undefined' ? true : a[1];
            paramName = paramName.toLowerCase();
            paramValue = paramValue.toLowerCase();
            if (obj[paramName]) {
                if (typeof obj[paramName] === 'string') {
                    obj[paramName] = [obj[paramName]];
                }
                if (typeof paramNum === 'undefined') {
                    obj[paramName].push(paramValue);
                }
                else {
                    obj[paramName][paramNum] = paramValue;
                }
            }
            else {
                obj[paramName] = paramValue;
            }
        }
    }

    return obj;
}

window.documentsType = function () {
    $.get("/dashboard/get/documents-type")
            .done(function (res) {
                setTimeout(function () { }, 5000);
                if (res.success === true) {
                    documents = res.results;
                }
            });
};

window.uploadFile = function (id, type) {
    $.get("/dashboard/get/upload-form", {id: id, type: type})
            .done(function (res) {
                setTimeout(function () { }, 5000);
                if (res.success === true) {
                    if (type === 'fechaPrevio') {
                        $('.modal-photos-title').html('&nbsp;Fotos previo: ' + res.referencia + " (" + res.pedimento + ")");
                        $('.modal-photos-body').html(res.html);
                        $('.modal-photos').modal('show');
                    } else {
                        $('.modal-upload-title').html('<i class="fa fa-upload" aria-hidden="true"></i>&nbsp;Subir documentos: ' + res.referencia + " (" + res.pedimento + ")");
                        $('.modal-upload-body').html(res.html);
                        $('.modal-upload').modal('show');
                    }

                }
            });
};

window.viewFiles = function(id) {
    NProgress.start();
    $.get("/dashboard/get/view-files", {id: id})
            .done(function (res) {
                setTimeout(function () { }, 5000);
                if (res.success === true) {
                    $('.modal-files-title').html('<i class="fa fa-files-o" aria-hidden="true"></i>&nbsp;Información del tráfico: ' + res.referencia + " (" + res.pedimento + ")");
                    $('.modal-files-body').html(res.html);
                    $('.modal-files').modal('show');
                    NProgress.done();
                }
            });
};

window.viewComments = function(id) {
    NProgress.start();
    $.get("/dashboard/get/view-comments", {id: id})
            .done(function (res) {
                setTimeout(function () { }, 5000);
                if (res.success === true) {
                    $('.modal-comments-title').html('<i class="fa fa-comments-o" aria-hidden="true"></i>&nbsp;Comentarios: ' + res.referencia + " (" + res.pedimento + ")");
                    $('.modal-comments-body').html(res.html);
                    $('.modal-comments').modal('show');
                    NProgress.done();
                }
            });
};

window.viewPhotos = function(id) {
    NProgress.start();
    $.get("/dashboard/get/view-photos", {id: id})
            .done(function (res) {
                setTimeout(function () { }, 5000);
                if (res.success === true) {
                    $('.modal-photos-title').html('<i class="fa fa-comments-o" aria-hidden="true"></i>&nbsp;Fotos: ' + res.referencia + " (" + res.pedimento + ")");
                    if (res.html) {
                        $('.modal-photos-body').html(res.html);
                    } else {
                        $('.modal-photos-body').html('No hay fotos cargadas');
                    }
                    $('.modal-photos').modal('show');
                    NProgress.done();
                }
            });
};

window.uploadIcon = function(id, type) {
    return '<i class="fa fa-upload" aria-hidden="true" style="margin-left: 5px; cursor: pointer" onclick="uploadFile(' + id + ', \'' + type + '\');"></i>';
};

window.viewIcon = function(id, type, fecha) {
    return fecha + '&nbsp;<i class="fa fa-eye" aria-hidden="true" style="cursor: pointer" title="Ver documentos" onclick="uploadFile(' + id + ', \'' + type + '\');"></i>';
};

window.descargarArchivo = function(href) {
    location.href = href;
};

/**
 * 
 * @param {Object} item
 * @returns {String}
 */
function newRow(item) {
    var status = '<span class="label label-default">&nbsp;</span>';
    if (parseInt(item.estatus) === 2) {
        status = '<span class="label label-primary">&nbsp;</span>';
    } else if (parseInt(item.estatus) === 3) {
        status = '<span class="label label-success">&nbsp;</span>';
    } else if (parseInt(item.estatus) === 6) {
        status = '<span class="label label-danger">&nbsp;</span>';
    }
    return '<tr><td>' 
            + status 
            + '</td><td>' 
            + '<a href="javascript:void(0)" onclick="viewFiles(' + item.id + ')"><i class="fa fa-folder-open" aria-hidden="true"></i></a>' 
            + '<a href="javascript:void(0)" onclick="viewComments(' + item.id + ')"><i class="fa fa-envelope" aria-hidden="true"></i></a>' 
            + '</td><td>' 
            + item.nombreAduana 
            + '</td><td>' 
            + (item.patente + '-' + item.aduana + '-' + item.pedimento)
            + '</td><td>' 
            + item.referencia 
            + '</td><td>' 
            + ((item.regimen) ? item.regimen : '')
            + '</td><td>' 
            + ((item.fechaNotificacion) ? item.fechaNotificacion : '')
            + '</td><td>' 
            + ((item.fechaEta) ? item.fechaEta : '')
            + '</td><td>' 
            + ((item.fechaEnvioProforma) ? item.fechaEnvioProforma : '')
            + '</td><td>' 
            + ((item.fechaEnvioDocumentos) ? viewIcon(item.id, 'fechaEnvioDocumentos', item.fechaEnvioDocumentos) : uploadIcon(item.id, 'fechaEnvioDocumentos'))
            + '</td><td>' 
            + ((item.fechaRevalidacion) ? viewIcon(item.id, 'fechaRevalidacion', item.fechaRevalidacion) : uploadIcon(item.id, 'fechaRevalidacion'))
            + '</td><td>' 
            //+ ((item.fechaPrevio) ? photosIcon(item.id, 'fechaPrevio', item.fechaPrevio) : '')
            + ((item.fechaPrevio) ? item.fechaPrevio + '&nbsp;<i class="fa fa-eye" aria-hidden="true" style="cursor: pointer" title="Ver fotos" onclick="viewPhotos(' + item.id + ');"></i>' : '')
            + '</td><td>' 
            + ((item.fechaPago) ? item.fechaPago : '')
            + '</td><td>' 
            + ((item.fechaLiberacion) ? item.fechaLiberacion : '')
            + '</td><td>' 
            + ((item.fechaEtaAlmacen) ? item.fechaEtaAlmacen : '')
            + '</td><td>' 
            + ((item.fechaFacturacion) ? item.fechaFacturacion : '')
            + '</td></tr>';
}

/**
 * 
 * @param {Number} page
 * @param {Number} limit
 * @returns {undefined}
 */
function loadData(page, limit) {
    NProgress.start();
    var html;
    $.get("/dashboard/get/traficos", {page: page, limit: limit, year: getUrlParameter('year'), month: getUrlParameter('month'), idAduana: getUrlParameter('idAduana'), code: getAllUrlParams().code})
            .done(function (res) {
                setTimeout(function () { }, 5000);
                if (res.data) {
                    total = res.total;
                    pages = Math.ceil(total / limit);
                    nextPage = parseInt(res.page) + 1;
                    $.each(res.data, function (i, item) {
                        html += newRow(item);
                    });
                    $("#status").html('<p>Total de tráficos: ' + res.total + '</p>');
                }
                $("#tbody").html(html);
                NProgress.done();
            });
    return true;
}

function pretty_time_string(num) {
    return (num < 10 ? "0" : "") + num;
}

function graficaOperaciones() {

    var etiquetas = [];
    var datos = [];
    var colores = [
            {"name": "blue", "normal": "#3498DB", "hover": "#49A9EA"},
            {"name": "green", "normal": "#26B99A", "hover": "#36CAAB"},
            {"name": "purple", "normal": "#9B59B6", "hover": "#B370CF"},
            {"name": "aero", "normal": "#BDC3C7", "hover": "#CFD4D8"},
            {"name": "red", "normal": "#E74C3C", "hover": "#E95E4F"},
            {"name": "darkgrey", "normal": "#26B00A", "hover": "#36CAAB"},
            {"name": "blue", "normal": "#3498DB", "hover": "#49A9EA"}
        ];
    var cantidadColores = colores.length;
    $.ajax({
        url: "/dashboard/get/por-aduana",
        data: {year: getUrlParameter('year'), month: getUrlParameter('month'), code: getAllUrlParams().code},
        success: function (res) {
            if (res.data) {
                $.each(res.data, function (i, item) {
                    etiquetas.push(item.label);
                    datos.push(item.value);
                    $("#porAduana").append('<tr><td><i class="fa fa-square ' + colores[i].name + '"></i></td><td><p style="font-size: 12px; text-align: left; padding-left: 2px">' + item.label + '</p></td><td><p style="font-size: 12px">' + item.value + '</p></td></tr>');
                });
                $(".count-previous-month").html(res.totalAnterior);
                $(".count-month").html(res.totalLiberar);
                $(".count-current-month").html(res.totalMes);
                var diff = parseInt(res.totalMes) - parseInt(res.totalAnterior);
                var inc =  (diff / parseInt(res.totalAnterior)) * 100;
                if (Math.ceil(inc) > 0) {
                    $("#diff").html('<span class="count_bottom"><i class="green"><i class="fa fa-sort-asc"></i> ' + Math.ceil(inc) + ' %</i> vs mes pasado</span>');
                } else {
                    $("#diff").html('<span class="count_bottom"><i class="red"><i class="fa fa-sort-desc"></i> ' + Math.ceil(inc) + ' %</i> vs mes pasado</span>');                    
                }
            }
        },
        async: false
    });

    if ($('.canvasDoughnutOperaciones').length) {

        var chart_doughnut_settings = {
            type: 'doughnut',
            tooltipFillColor: "rgba(51, 51, 51, 0.55)",
            data: {
                labels: etiquetas,
                datasets: [{
                        data: datos,
                        backgroundColor: [
                            colores[0].normal,
                            colores[1].normal,
                            colores[2].normal,
                            colores[3].normal,
                            colores[4].normal,
                            colores[5].normal,  
                            colores[6].normal
                        ],
                        hoverBackgroundColor: [
                            colores[0].hover,
                            colores[1].hover,
                            colores[2].hover,
                            colores[3].hover,
                            colores[4].hover,
                            colores[5].hover,
                            colores[6].hover
                        ]
                    }]
            },
            options: {
                legend: false,
                responsive: false
            }
        };

        $(".canvasDoughnutOperaciones").each(function () {
            var chart_element = $(this);
            var chart_doughnut = new Chart(chart_element, chart_doughnut_settings);
        });

    }
}

window.logout = function() {
    $('.modal-logout-title').html('<i class="fa fa-question-circle" aria-hidden="true"></i>&nbsp;Confirmar');
    $('.modal-logout-body').html('¿Está seguro que desea salir de nuestro panel?');
    $('.modal-logout').modal('show');
};

$(document).ready(function () {
    
    documentsType();

    var now = moment();
    now.locale("es");
    $("#timer").text(now.format("D [de] MMMM YYYY, h:mm:ss a"));

    $(document.body).on("change", "#idAduana", function () {
        var year = (getUrlParameter('year') !== undefined) ? '&year=' + getUrlParameter('year') : '';
        var month = (getUrlParameter('month') !== undefined) ? '&month=' + getUrlParameter('month') : '';
        if ($(this).val() !== "0") {
            window.location.href = '/dashboard/main?code=' + getUrlParameter('code') + year + month + '&idAduana=' + $(this).val();
        } else {
            window.location.href = '/dashboard/main?code=' + getUrlParameter('code') + year + month;
        }
    });

    if (getUrlParameter('idAduana')) {
        $("#idAduana").val(getUrlParameter('idAduana'));
    }
    
    $(document.body).on("click", "#add-comment", function (ev) {
        ev.preventDefault();
        $.ajax({url: "/dashboard/post/agregar-comentario", dataType: "json", timeout: 10000, type: "POST",
            data: {idTrafico: $("#idTrafico").val(), message: $("#message").val()},
            beforeSend: function() {
                NProgress.start();
            },
            success: function (res) {
                $("#message").val('');
                $.get("/dashboard/get/comments", {idTrafico: $("#idTrafico").val()})
                    .done(function (res) {
                        setTimeout(function () { }, 5000);
                        if (res.success === true) {
                            $.each(res.comments, function (i, item) {
                                if (!$('#comment_' + item['id']).length) {
                                    $('.comments-table tr:last').after('<tr id="comment_' + item["id"] + '"><td>' + item["nombre"] + '</td><td>' + item["mensaje"] + '</td><td>' + item["creado"] + '</td></tr>');
                                }
                            });
                            NProgress.done();
                        }
                    });
            }
        });
    });
    
    $(document.body).on("click", "#pause", function () {
        if (pause === false) {
            pause = true;
            $(this).html('<span class="glyphicon glyphicon-play" aria-hidden="true"></span>');
        } else {
            pause = false;
            $(this).html('<span class="glyphicon glyphicon-pause" aria-hidden="true"></span>');
            loadData(nextPage, limit);
        }
    });

    loadData(page, 500);

    graficaOperaciones();

    $("#searchInput").on("keyup", function () {
        var value = $(this).val().toLowerCase();
        $("#datatable tbody tr").filter(function () {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });

});