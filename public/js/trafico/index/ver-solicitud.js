/**
 * programmed by Jvaldez at gmail
 * 2015.may.14
 */

function verSolicitud(idSolicitud) {
    window.open("/trafico/data/ver-solicitud?id=" + idSolicitud, '_blank', 'toolbar=0,location=0,menubar=0,height=550,width=1024,scrollbars=yes');
}

function deleteRequest(idSolicitud) {
    var r = confirm("¿Está seguro que desea remover la solicitud?");
    if (r === true) {
        $('#confirm').jqmShow();
    }
}

function cargarArchivos() {
    $.ajax({ url: "/administracion/ajax/archivos-solicitud", type: "POST", dataType: "json",
        data: {idSolicitud: $("#idSolicitud").val()},
        success: function (res) {
            if (res.success === true) {
                $("#files").html(res.html);
            }
        }
    });
}

$(document).ready(function () {

    $("#confirm").jqm({modal: true});

    $("#closeModal").click(function () {
        $('#confirm').jqmHide();
    });

    $("#form-cancel").validate({
        errorPlacement: function (error, element) {
            $(element)
                    .closest("form")
                    .find("label[for='" + element.attr("id") + "']")
                    .append(error);
        },
        rules: {
            comentario: "required"
        },
        messages: {
            comentario: "Debe propocionar un motivo de cancelación."
        }
    });

    $(document.body).on('click', '#saveRequest', function (ev) {
        ev.preventDefault();
        if ($("#form-cancel").valid()) {
            $("#form-cancel").ajaxSubmit({
                dataType: "json",
                success: function (res) {
                    if (res.success === true) {
                        $('#confirm').jqmHide();
                        window.location.replace("/trafico/index/ultimas-solicitudes");
                    }
                }
            });
        }
    });

    $("#form-approved").validate({
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
    
    $("#modal").jqm({
        ajax: "/trafico/data/cancelacion-solicitud?id=" + $("#idSolicitud").val(),
        modal: true
    }); 
    
    $(document.body).on('click', '.deleteFile', function (ev) {
        var id = $(this).data('id');
        var rq = $(this).data('request');
        $.confirm({title: "Confirmar", escapeKey: "cerrar", boxWidth: "350px", useBootstrap: false, type: "red",
            buttons: {
                si: {btnClass: "btn-red", action: function () {
                        $.ajax({url: "/administracion/ajax/borrar-archivo", dataType: "json", timeout: 10000, type: "POST",
                            data: {id: id, request: rq},
                            success: function (res) {
                                if (res.success === true) {
                                    cargarArchivos();
                                } else {
                                    $.alert({title: "Error", type: "red", content: res.message, boxWidth: "250px", useBootstrap: false});
                                }
                            }
                        });
                }},
                no: {action: function () {}}
            },
            content: "¿Está seguro que desea borrar el archivo?"
        });
    });

    $(document.body).on("click", "#save-approved", function (ev) {
        ev.preventDefault();
        if ($("#form-approved").valid()) {
            if($("#proceso").val() === '4') {
                $("#modal").jqmShow();
            }
            $("#form-approved").ajaxSubmit({
                url: '/trafico/ajax/actualizar-solicitud',
                type: 'POST',
                dataType: 'json',
                success: function (res) {
                    if (res.success === true) {
                        window.location.replace("/trafico/index/ver-solicitud?id=" + $("#idSolicitud").val());
                    } else {
                        $.alert({title: "¡Advertencia!", closeIcon: true, backgroundDismiss: true, type: "red", escapeKey: "cerrar", boxWidth: "400px", useBootstrap: false, content: res.message});
                    }
                }
            });
        }
    });

    $("#form-comments").validate({
        errorPlacement: function (error, element) {
            $(element)
                    .closest("form")
                    .find("label[for='" + element.attr("id") + "']")
                    .append(error);
        },
        rules: {
            comments: "required"
        },
        messages: {
            comments: "Debe poner un comentario."
        }
    });

    $(document.body).on('click', '#add-complement', function (ev) {
        ev.preventDefault();
        $.ajax({
            timeout: 3000,
            cache: false,
            type: 'POST',
            dataType: 'JSON',
            url: '/trafico/ajax/agregar-complemento',
            data: {idSolicitud: $("#idSolicitud").val()},
            success: function (res) {
                if (res.success === true) {
                    if ($("#rol").val() === 'corresponsal') {
                        window.location.replace("/trafico/index/solicitudes-corresponsal");
                    } else {
                        window.location.replace("/trafico/index/crear-nueva-solicitud");
                    }
                } else {
                    $.alert({title: "¡Advertencia!", closeIcon: true, backgroundDismiss: true, type: "red", escapeKey: "cerrar", boxWidth: "400px", useBootstrap: false, content: res.message});
                }
            }
        });
    });

    $(document.body).on("click", '#add-comment', function (ev) {
        console.log();
        ev.preventDefault();
        if ($("#form-comments").valid()) {
            $("#form-comments").ajaxSubmit({
                url: "/trafico/post/agregar-comentario-solicitud",
                dataType: "json",
                success: function (res) {
                    if (res.success === true) {
                        window.location.replace("/trafico/index/ver-solicitud?id=" + $("#solicitud").val());
                    }
                }
            });
        }
    });

    $(document.body).on('input', '#comments, #comentario', function () {
        var input = $(this);
        var start = input[0].selectionStart;
        $(this).val(function (_, val) {
            return val.toUpperCase();
        });
        input[0].selectionStart = input[0].selectionEnd = start;
    });
    
    $("#formFiles").validate({
        rules: {
            "file[]": {
                required: true
            }
        },
        messages: {
            "file[]": {
                required: " [No se ha seleccionado un archivo.]"
            }
        }
    });
    
    $("#uploadFiles").click(function (e) {
        e.preventDefault();
        if ($("#formFiles").valid()) {
            $("#formFiles").ajaxSubmit({
                url: "/administracion/ajax/subir-archivos-solicitud",
                type: "post",
                dataType: "json",
                timeout: 5000,
                success: function (res) {
                    if (res.success === true) {
                        cargarArchivos();
                        document.getElementById("file").value = null;
                    } else {
                        alert(res.message);
                    }
                }
            });
        }
    });
    
    cargarArchivos();

});