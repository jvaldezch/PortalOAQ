/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function verSolicitud(idSolicitud) {
    window.open("/trafico/data/ver-solicitud?id=" + idSolicitud, "_blank", "toolbar=0,location=0,menubar=0,height=550,width=1024,scrollbars=yes");
}

function cargarArchivos() {
    $.ajax({
        url: "/administracion/ajax/archivos-solicitud",
        type: "post",
        data: {idSolicitud: $("#idSolicitud").val()},
        dataType: "json",
        success: function (res) {
            if (res.success === true) {
                $("#files").html(res.html);
            }
        }
    });
}

function fileOperations(id, request) {
    $.ajax({
        url: "/administracion/ajax/borrar-archivo",
        type: "post",
        data: {id: id, request: request},
        dataType: "json",
        success: function (res) {
        }
    });
}

$(document).ready(function () {
    
    cargarArchivos();
    
    $("#process").validate({
        errorPlacement: function (error, element) {
            $(element)
                    .closest("form")
                    .find("label[for='" + element.attr("id") + "']")
                    .append(error);
        },
        errorElement: "span",
        errorClass: "traffic-error",
        rules: {
            esquema: "required",
            proceso: "required"
        },
        messages: {
            esquema: "Debe seleccionar esquema de fondo.",
            proceso: "Debe seleccionar el proceso."
        }
    });
    
    $(document.body).on("click", ".request", function (ev) {
        ev.preventDefault();
        if($(this).data("request") === "delete") {
            var r = confirm("¿Desea borrar el archivo?");
            var arr = [];
            if (r === false) {
                return;
            } else {
                fileOperations($(this).data("id"), $(this).data("request"));
                return;
            }
        }
        location.href = "/administracion/data/descargar-archivo?id="+$(this).data("id");
    });
    
    $(document.body).on("click", "#save-approved", function (ev) {
        ev.preventDefault();
        if ($("#process").valid()) {
            $("#process").ajaxSubmit({url: "/administracion/ajax/actualizar-solicitud", dataType: "json", type: "POST",
                success: function (res) {
                    if (res.success === true) {
                        window.location.replace("/administracion/index/ver-solicitud?id=" + $("#idSolicitud").val());
                    } else {
                        $.alert({title: "¡Advertencia!", closeIcon: true, backgroundDismiss: true, type: "red", escapeKey: "cerrar", boxWidth: "400px", useBootstrap: false, content: res.message});
                    }
                }
            });
        }
    });
    
    $(document.body).on("input", "#comments, #comentario", function () {
        var input = $(this);
        var start = input[0].selectionStart;
        $(this).val(function (_, val) {
            return val.toUpperCase();
        });
        input[0].selectionStart = input[0].selectionEnd = start;
    });
    
    $("#form-files").validate({
        errorPlacement: function (error, element) {
            $(element)
                    .closest("form")
                    .find("label[for='" + element.attr("id") + "']")
                    .append(error);
        },
        errorElement: "span",
        errorClass: "traffic-error",
        rules: {
            "file[]": {
                required: true
            }
        },
        messages: {
            "file[]": {
                required: "Se requiere seleccionar un archivo"
            }
        }
    });
    
    $(document.body).on("click", "#upload", function (e) {
        e.preventDefault();
        if ($("#form-files").valid()) {
            $("#form-files").ajaxSubmit({
                type: "post",
                dataType: "json",
                url: "/administracion/ajax/subir-archivos-solicitud",
                success: function(res) {
                    if(res.success === true) {
                        $("#file").val("");
                    }
                }
            });
        }
    });
    
});
