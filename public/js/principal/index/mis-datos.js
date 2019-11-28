/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function editarSolicitud(id) {
    $.confirm({title: "Editar solicitud", escapeKey: "cerrar", boxWidth: "650px", useBootstrap: false,
            buttons: {
                guardar: {
                    btnClass: "btn-green",
                    action: function () {
                        $.ajax({ url: "/principal/post/guardar-solicitud", type: "POST", dataType: "json", cache: false, data: {data: JSON.stringify(jsonData)},
                            success: function (res) {
                                if (res.success === true) {
                                }
                            }
                        });
                    }
                },
                cerrar: {
                    action: function () {}
                }
            },
            content: function () {
                var self = this;
                return $.ajax({
                    url: "/principal/get/editar-solicitud", method: "post", dataType: "json", data: {id: id}
                }).done(function (res) {
                    var html = "";
                    if (res.success === true) {
                        html = res.html;
                    }
                    self.setContent(html);
                }).fail(function () {
                    self.setContent("Something went wrong.");
                });
            }
        });
}

function borrarSolicitud(id) {
    $.confirm({title: "Confirmar", content: '¿Está seguro de que desea eliminar la solicitud?', escapeKey: "no", boxWidth: "350px", useBootstrap: false, type: "red",
        buttons: {
            si: {
                btnClass: "btn-blue",
                action: function () {
                    $.post("/principal/post/borrar-solicitud", {id: id})
                            .done(function (res) {
                                if (res.success === true) {
                                    misSolicitudes();
                                }
                            });
                }
            },
            no: function () {}
        }
    });
}

function misSolicitudes() {
    $.ajax({url: "/principal/get/mis-solicitudes", type: "GET", dataType: "json", cache: false,
        success: function (res) {
            if (res.success === true) {
                var content = '';
                $('#solicitudes tbody').empty();
                $.each(res.result, function(index, value) {
                    content += '<tr><td>' + '</td><td>' + value.tipoSolicitud + '</td><td>' + value.creado + '</td><td><a onclick="javascript:editarSolicitud(' + value.id + ');" style="cursor: pointer">Editar</a>&nbsp;|&nbsp;<a  onclick="javascript:borrarSolicitud(' + value.id + ');" style="cursor: pointer">Borrar</a>&nbsp;|&nbsp;<a href="/principal/get/imprimir-solicitud?id=' + value.id + '" target="_blank">Imprimir</a></td></tr>';
                });
            }
            $('#solicitudes tbody').append(content);
        }
    });
}

$(document).ready(function () {
    
    $('#directory').dataTable({
        "sDom": "<'traffic-cols'<'traffic-col-50'l><'traffic-col-50'f><'traffic-clear-5'>t<'traffic-clear-5'><'traffic-col-50'i><'traffic-col-50'p><'traffic-clear-5'>>",
        "sPaginationType": "bootstrap",
        "oLanguage": {
            "sLengthMenu": "_MENU_ registros por página"
        },
        "iDisplayLength": 10,
        "bStateSave": true,
        "aaSorting": [],
        "bSort": false
    });
    
    
    $("#formRequest").validate({
        errorPlacement: function (error, element) {
            $(element)
                    .closest("form")
                    .find("#" + element.attr("id"))
                    .after(error);
        },
        errorElement: "span",
        errorClass: "traffic-error",
        rules: {
            idSolicitud: "required"
        },
        messages: {
            idSolicitud: "Seleccionar una solicitud."
        }
    });
    
    $(document.body).on("click", "#submit", function (ev) {
        ev.preventDefault();
        if ($("#formRequest").valid()) {
            $("#formRequest").ajaxSubmit({url: "/principal/post/nueva-solicitud", type: "post", dataType: "json", cache: false,
                success: function (res) {
                    if (res.success === true) {
                        misSolicitudes();
                    }
                }
            });
        }
    });
    
    $("#formUsuario").validate({
        errorPlacement: function (error, element) {
            $(element)
                    .closest("form")
                    .find("#" + element.attr("id"))
                    .after(error);
        },
        errorElement: "span",
        errorClass: "traffic-error",
        rules: {
            telefono: "required",
            extension: "required"
        },
        messages: {
            telefono: "Seleccionar un cliente.",
            extension: "Seleccionar una patente."
        }
    });
    
    $(document.body).on("click", "#update", function (e) {
        e.preventDefault();
        if ($("#formUsuario").valid()) {
            $("#formUsuario").ajaxSubmit({url: "/principal/post/mis-datos", type: "post", dataType: "json", cache: false,
                success: function (res) {
                    if (res.success === true) {
                        
                    }
                }
            });
        }
    });
    
    misSolicitudes();
    
});

