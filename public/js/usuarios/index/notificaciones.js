/**
 *
 */

window.actualizarContacto = function(id, type, value) {
    let val = (value === true) ? 1 : 0;
    $.ajax({
        url: '/usuarios/post/actualizar-contactos', type: 'POST',
        data: {id: id, type: type, value: val},
        beforeSend: function () {
            $(".contacts").LoadingOverlay("show", {color: "rgba(255, 255, 255, 0.9)"});
        },
        success: function (res) {
            $(".contacts").LoadingOverlay("hide");
            if (res.success !== true) {
                $.alert({title: "Error", type: "red", content: res.message, boxWidth: "350px", useBootstrap: false});
            }
        }
    });
};

window.borrarContacto = function(id) {
    $.ajax({
        url: '/usuarios/post/borrar-contacto', type: 'POST',
        data: {id: id},
        beforeSend: function () {
            $(".contacts").LoadingOverlay("show", {color: "rgba(255, 255, 255, 0.9)"});
        },
        success: function (res) {
            $(".contacts").LoadingOverlay("hide");
            if (res.success !== true) {
                $.alert({title: "Error", type: "red", content: res.message, boxWidth: "350px", useBootstrap: false});
            } else {
                $("#contact_" + id).hide()
            }
        }
    });
};

$(document).ready(function () {

    $(document.body).on("click", ".creacion", function () {
        actualizarContacto($(this).data('id'), "creacion", $(this).is(":checked"));
    });

    $(document.body).on("click", ".gerencia", function () {
        actualizarContacto($(this).data('id'), "gerencia", $(this).is(":checked"));
    });

    $(document.body).on("click", ".administracion", function () {
        actualizarContacto($(this).data('id'), "administracion", $(this).is(":checked"));
    });

    $(document.body).on("click", ".operacion", function () {
        actualizarContacto($(this).data('id'), "operacion", $(this).is(":checked"));
    });

    $(document.body).on("click", ".deposito", function () {
        actualizarContacto($(this).data('id'), "deposito", $(this).is(":checked"));
    });

    $(document.body).on("click", ".comentario", function () {
        actualizarContacto($(this).data('id'), "comentario", $(this).is(":checked"));
    });

    $(document.body).on("click", ".comentarioSolicitud", function () {
        actualizarContacto($(this).data('id'), "comentarioSolicitud", $(this).is(":checked"));
    });

    $(document.body).on("click", ".solicitud", function () {
        actualizarContacto($(this).data('id'), "solicitud", $(this).is(":checked"));
    });

    $(document.body).on("click", ".habilitado", function () {
        actualizarContacto($(this).data('id'), "habilitado", $(this).is(":checked"));
    });

    $(document.body).on("click", ".delete", function () {
        let id = $(this).data('id');
        $.confirm({title: "Contacto", escapeKey: "cerrar", boxWidth: "350px", useBootstrap: false, type: "red",
            buttons: {
                si: {btnClass: "btn-red", action: function () {
                        borrarContacto(id)
                    }},
                no: {action: function () {}}
            },
            content: "¿Está seguro que desea borrar este contacto?"
        });
    });

    $(document.body).on("click", "#submit", function (ev) {        ev.preventDefault();

        $.confirm({ title: "Agregar nuevo contacto", escapeKey: "cerrar", boxWidth: '60%', useBootstrap: false, type: "blue",
            closeIcon: true,
            buttons: {
                agregar: {btnClass: "btn-green", action: function () {
                        if ($("#formNewContact").valid()) {
                            $("#formNewContact").ajaxSubmit({type: "POST", dataType: "json", url: "/usuarios/post/agregar-contacto",
                                beforeSend: function() {
                                    $(".contacts").LoadingOverlay("show", {color: "rgba(255, 255, 255, 0.9)"});
                                },
                                success: function (res) {
                                    $(".contacts").LoadingOverlay("hide");
                                    if (res.success === true) {
                                        window.location.href = "/usuarios/index/notificaciones";
                                    }
                                }
                            });
                        }
                        return false;
                    }},
                cerrar: {action: function () {}}
            },
            content: function () {
                let self = this;
                return $.ajax({
                    url: "/usuarios/post/nuevo-contacto",
                    method: "post"
                }).done(function (res) {
                    self.setContent(res.html);
                }).fail(function () {
                    self.setContent("Something went wrong.");
                });
            }
        });
    });

});