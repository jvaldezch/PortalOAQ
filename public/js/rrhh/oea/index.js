/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

var queryString = function () {
    var query_string = {};
    var query = window.location.search.substring(1);
    var vars = query.split("&");
    for (var i = 0; i < vars.length; i++) {
        var pair = vars[i].split("=");
        if (typeof query_string[pair[0]] === "undefined") {
            query_string[pair[0]] = decodeURIComponent(pair[1]);
        } else if (typeof query_string[pair[0]] === "string") {
            var arr = [query_string[pair[0]], decodeURIComponent(pair[1])];
            query_string[pair[0]] = arr;
        } else {
            query_string[pair[0]].push(decodeURIComponent(pair[1]));
        }
    }
    return query_string;
}();

function eliminarDirectorio(id) {
    $.post("/rrhh/oea/comprobar-contenido", {id: id})
            .done(function (res) {
                if (res.success === true) {
                    $.alert({
                        title: "¡Advertencia!",
                        type: "red",
                        content: "La carpeta contiene elementos y no puede ser eliminada.",
                        escapeKey: true,
                        boxWidth: "310px",
                        useBootstrap: false
                    });
                } else if(res.success === false) {
                    $.post("/rrhh/oea/eliminar-directorio", {id: id})
                        .done(function (res) {
                            if (res.success === true) {
                                window.location.href = window.location.pathname + ((queryString.directorio !== undefined) ? "?directorio=" + queryString.directorio : "");
                            } 
                        });
                }
            });
}

function eliminarArchivos(id) {
    $.confirm({
        title: "Confirmar",
        content: '¿Está seguro de que desea eliminar el archivo?',
        escapeKey: "cerrar",
        boxWidth: "250px",
        useBootstrap: false,
        buttons: {
            si: {
                btnClass: "btn-blue",
                action: function () {
                    var ids = [];
                    ids.push(id);
                    $.post("/rrhh/oea/eliminar-archivos", {ids: ids})
                            .done(function (res) {
                                if (res.success === true) {
                                    window.location.href = window.location.pathname + ((queryString.directorio !== undefined) ? "?directorio=" + queryString.directorio : "");
                                }
                            });
                }
            },
            no: function () {
            }
        }
    });
}

function editarArchivo(id, value) {
    $.confirm({
        title: "Editar archivo",
        escapeKey: "cerrar",
        boxWidth: "310px",
        useBootstrap: false,
        buttons: {
            guardar: {
                btnClass: "btn-blue",
                action: function () {
                    if ($("#editFileForm").valid()) {
                        $("#editFileForm").ajaxSubmit({
                            cache: false,
                            url: "/rrhh/oea/editar-archivo",
                            type: "post",
                            dataType: "json",
                            success: function (res) {
                                if (res.success === true) {
                                    $('.inline-edit-file[data-id="' + id + '"]').html(res.nombre);
                                    return true;
                                }
                            }
                        });
                    } else {
                        return false;
                    }
                }
            },
            cerrar: {
                btnClass: "btn-red",
                action: function () {}
            }
        },
        content: '<form id="editFileForm" method="post" enctype="application/x-www-form-urlencoded">' +
                '<div class="form-group">' +
                '<label style="font-weight: bold">Nombre del archivo:</label>' +
                '<input type="hidden" id="id" name="id" value="' + id + '">' +
                '<input type="text" name="nombre" id="nombre" class="required" style="width: 300px" value="' + value + '" />' +
                '</div>' +
                '</form>' +
                '<script> $("#folderForm").validate({ errorPlacement: function (error, element) { $(element).closest("form").find("#" + element.attr("id")).after(error); }, errorElement: "span", errorClass: "traffic-error", rules: { nombre: { required: true } }, messages: { nombre: { required: " [Nombre necesario.]" } } }); $("#nombre").focus(); </script>'
    });
}

function editarDirectorio(id, value) {
    $.confirm({
        title: "Editar directorio",
        escapeKey: "cerrar",
        boxWidth: "310px",
        useBootstrap: false,
        buttons: {
            guardar: {
                btnClass: "btn-blue",
                action: function () {
                    if ($("#editFolderForm").valid()) {
                        $("#editFolderForm").ajaxSubmit({
                            cache: false,
                            url: "/rrhh/oea/editar-directorio",
                            type: "post",
                            dataType: "json",
                            success: function (res) {
                                if (res.success === true) {
                                    $('.inline-edit-folder[data-id="' + id + '"]').html(res.nombre);
                                }
                            }
                        });
                    } else {
                        return false;
                    }
                }
            },
            cerrar: {
                btnClass: "btn-red",
                action: function () {}
            }
        },
        content: '<form id="editFolderForm" method="post" enctype="application/x-www-form-urlencoded">' +
                '<div class="form-group">' +
                '<label style="font-weight: bold">Nombre del directorio:</label>' +
                '<input type="hidden" id="id" name="id" value="' + id + '">' +
                '<input type="text" name="nombre" id="nombre" class="required" style="width: 300px" value="' + value + '" />' +
                '</div>' +
                '</form>' +
                '<script> $("#folderForm").validate({ errorPlacement: function (error, element) { $(element).closest("form").find("#" + element.attr("id")).after(error); }, errorElement: "span", errorClass: "traffic-error", rules: { nombre: { required: true } }, messages: { nombre: { required: " [Nombre necesario.]" } } }); $("#nombre").focus(); </script>'
    });
}

$(document).ready(function () {

    $(document.body).on("click", ".button-home", function () {
        window.location.href = window.location.pathname;
    });

    $(document.body).on("click", ".editFolder", function () {
        console.log($(this).data("id"));
    });

    $(document.body).on("click", ".editFile", function () {
        console.log($(this).data("id"));
    });

    $(document.body).on("click", ".button-upload", function (ev) {
        ev.preventDefault();
        $.confirm({
            title: "Subir archivos a la carpeta actual",
            escapeKey: "cerrar",
            boxWidth: "500px",
            useBootstrap: false,
            buttons: {
                subir: {
                    btnClass: "btn-blue",
                    action: function () {
                        if ($("#uploadForm").valid()) {
                            $("#uploadForm").ajaxSubmit({
                                cache: false,
                                url: "/rrhh/oea/subir-archivos",
                                type: "post",
                                dataType: "json",
                                success: function (res) {
                                    if (res.success === true) {
                                        window.location.href = window.location.pathname + ((queryString.directorio !== undefined) ? "?directorio=" + queryString.directorio : "");
                                    }
                                }
                            });
                        } else {
                            return false;
                        }
                    }
                },
                cerrar: {
                    btnClass: "btn-red",
                    action: function () {}
                }
            },
            content: '' +
                    '<form id="uploadForm" method="post" enctype="multipart/form-data">' +
                    '<div class="form-group">' +
                    '<label style="font-weight: bold">Seleccionar archivos para subir:</label>' +
                    '<input type="hidden" id="directorio" name="directorio" value="' + $("#directorio").val() + '">' +
                    '<input type="file" name="file[]" id="file" class="required" multiple />' +
                    '</div>' +
                    '</form>' +
                    '<script> $("#uploadForm").validate({ errorPlacement: function (error, element) { $(element).closest("form").find("#" + element.attr("id")).after(error); }, errorElement: "span", errorClass: "traffic-error", rules: { "file[]": { required: true } }, messages: { "file[]": { required: " [No se ha seleccionado un archivo.]" } } }); </script>'
        });
    });
    
    $(document.body).on("click", ".button-newfolder", function (ev) {
        ev.preventDefault();
        $.confirm({
            title: "Crear nuevo directorio",
            escapeKey: "cerrar",
            boxWidth: "310px",
            useBootstrap: false,
            buttons: {
                crear: {
                    btnClass: "btn-blue",
                    action: function () {
                        if ($("#folderForm").valid()) {
                            $("#folderForm").ajaxSubmit({
                                cache: false,
                                url: "/rrhh/oea/crear-directorio",
                                type: "post",
                                dataType: "json",
                                success: function (res) {
                                    if (res.success === true) {
                                        window.location.href = window.location.pathname + ((queryString.directorio !== undefined) ? "?directorio=" + queryString.directorio : "");
                                    }
                                }
                            });
                        } else {
                            return false;
                        }
                    }
                },
                cerrar: {
                    btnClass: "btn-red",
                    action: function () {}
                }
            },
            content: '<form id="folderForm" method="post" enctype="application/x-www-form-urlencoded">' +
                    '<div class="form-group">' +
                    '<label style="font-weight: bold">Nombre de la carpeta a crear en el directorio actual:</label>' +
                    '<input type="hidden" id="directorio" name="directorio" value="' + $("#directorio").val() + '">' +
                    '<input type="text" name="folderName" id="folderName" class="required" style="width: 300px" />' +
                    '</div>' +
                    '</form>' +
                    '<script> $("#folderForm").validate({ errorPlacement: function (error, element) { $(element).closest("form").find("#" + element.attr("id")).after(error); }, errorElement: "span", errorClass: "traffic-error", rules: { folderName: { required: true } }, messages: { folderName: { required: " [Nombre de la carpeta necesario.]" } } }); $("#folderName").focus(); </script>'
        });
    });

    $.contextMenu({
        selector: ".inline-edit-file",
        callback: function (key, options) {
            if(key === "edit") {
                editarArchivo($(this).data("id"), $(this).html());
            }
            if(key === "delete") {
                eliminarArchivos($(this).data("id"));
            }
        },
        items: {
            "edit": {name: "Editar", icon: "edit"},
            "delete": {name: "Eliminar", icon: "delete"}
        }
    });

    $.contextMenu({
        selector: ".inline-edit-folder",
        callback: function (key, options) {
            if(key === "edit") {
                editarDirectorio($(this).data("id"), $(this).html());
            }
            if(key === "delete") {
                eliminarDirectorio($(this).data("id"), $(this).html());
            }
        },
        items: {
            "edit": {name: "Editar", icon: "edit"},
            "delete": {name: "Eliminar", icon: "delete"}
        }
    });
    
    $(document.body).on("click", "#selectAll", function () {
        var checkboxes = $("input[class=singleFile]");
        if ($(this).is(":checked")) {
            checkboxes.prop("checked", true);
        } else {
            checkboxes.prop('checked', false);
        }
    });
    
    $(document.body).on("click", ".button-delete", function () {
        var ids = [];
        var boxes = $("input[class=singleFile]:checked");
        if ((boxes).size() === 0) {
            $.alert({
                title: "¡Oops!",
                type: "orange",
                content: "No ha seleccionado nada para borrar.",
                escapeKey: true,
                boxWidth: "310px",
                useBootstrap: false
            });
        }
        if ((boxes).size() > 0) {
            $(boxes).each(function () {
                ids.push($(this).data("id"));
            });
            console.log(ids);
        }
    });

});
