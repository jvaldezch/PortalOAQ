/**
 * programmed by Jvaldez at gmail
 * 2015.may.14
 */

$(document).ready(function () {

    $(document.body).on('click', '.edit-equipment', function (ev) {
        ev.preventDefault();
        let id = $(this).data('id');
        $.confirm({
            title: "Editar equipo de computo", escapeKey: "cerrar", boxWidth: "850px", useBootstrap: false, type: "blue",
            buttons: {
                guardar: {
                    btnClass: "btn-blue", action: function () {
                        if ($("#edit-equipment").valid()) {
                            $("#edit-equipment").ajaxSubmit({
                                url: "/usuarios/post/editar-equipo",
                                dataType: "json",
                                type: "POST",
                                success: function (res) {
                                    
                                }
                            });
                        }
                        return false;
                    }
                },
                cerrar: { action: function () { } }
            },
            content: function () {
                var self = this;
                return $.ajax({
                    url: "/usuarios/get/editar-equipo",
                    dataType: "json",
                    method: "get",
                    data: { id: id }
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
    });

    $(document.body).on('click', '.add-equipment', function (ev) {
        ev.preventDefault();
        let id = $(this).data('id');
        $.confirm({
            title: "Agregar equipo de computo", escapeKey: "cerrar", boxWidth: "850px", useBootstrap: false, type: "blue",
            buttons: {
                guardar: {
                    btnClass: "btn-blue", action: function () {
                        if ($("#add-equipment").valid()) {
                            $("#add-equipment").ajaxSubmit({
                                url: "/usuarios/post/agregar-equipo",
                                dataType: "json",
                                type: "POST",
                                success: function (res) {
                                    if (res.success === true) {
                                        location.replace(`/usuarios/index/editar-usuario?id=${id}`);
                                    }
                                }
                            });
                        }
                        return false;
                    }
                },
                cerrar: { action: function () { } }
            },
            content: function () {
                var self = this;
                return $.ajax({
                    url: "/usuarios/get/agregar-equipo",
                    dataType: "json",
                    method: "get",
                    data: { idUsuario: id }
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

    });

});