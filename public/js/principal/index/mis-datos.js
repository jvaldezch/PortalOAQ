/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

let tabs = [];

let today = new Date();
let selectedDate = today.toISOString().substring(0, 10);

window.mensajeAlerta = function (mensaje) {
    $.alert({
        title: "Alerta", type: "red", typeAnimated: true, useBootstrap: false, boxWidth: "250px",
        content: mensaje
    });
}

window.descargarArchivo = function (href) {
    window.location.href = href;
};

window.misDocumentos = function () {
    return $.ajax({
        url: '/principal/get/mis-documentos', type: 'GET', dataType: 'json', cache: false,
        beforeSend: function () {
            $('#doctos').LoadingOverlay('show', { color: 'rgba(255, 255, 255, 0.9)' });
        },
        success: function (res) {
            $('#doctos').LoadingOverlay('hide');
            if (res.success === true) {
                $('#doctos').html(res.html);
            }
        }
    });
};

window.currentActive = function (current) {
    if (Cookies.get('active') === '#misdocumentos') {
        misDocumentos();
    }
}

function editarSolicitud(id) {
    $.confirm({
        title: "Editar solicitud", escapeKey: "cerrar", boxWidth: "650px", useBootstrap: false,
        buttons: {
            guardar: {
                btnClass: "btn-green",
                action: function () {
                    $.ajax({
                        url: "/principal/post/guardar-solicitud", type: "POST", dataType: "json", cache: false, data: { data: JSON.stringify(jsonData) },
                        success: function (res) {
                            if (res.success === true) {
                            }
                        }
                    });
                }
            },
            cerrar: {
                action: function () { }
            }
        },
        content: function () {
            var self = this;
            return $.ajax({
                url: "/principal/get/editar-solicitud", method: "post", dataType: "json", data: { id: id }
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
    $.confirm({
        title: "Confirmar", content: '¿Está seguro de que desea eliminar la solicitud?', escapeKey: "no", boxWidth: "350px", useBootstrap: false, type: "red",
        buttons: {
            si: {
                btnClass: "btn-blue",
                action: function () {
                    $.post("/principal/post/borrar-solicitud", { id: id })
                        .done(function (res) {
                            if (res.success === true) {
                                misSolicitudes();
                            }
                        });
                }
            },
            no: function () { }
        }
    });
}

function misSolicitudes() {
    $.ajax({
        url: "/principal/get/mis-solicitudes", type: "GET", dataType: "json", cache: false,
        success: function (res) {
            if (res.success === true) {
                var content = '';
                $('#solicitudes tbody').empty();
                $.each(res.result, function (index, value) {
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
            $("#formRequest").ajaxSubmit({
                url: "/principal/post/nueva-solicitud", type: "post", dataType: "json", cache: false,
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
            $("#formUsuario").ajaxSubmit({
                url: "/principal/post/mis-datos", type: "post", dataType: "json", cache: false,
                success: function (res) {
                    if (res.success === true) {

                    }
                }
            });
        }
    });

    misSolicitudes();
    misDocumentos();

    $("#traffic-tabs li a").each(function () {
        tabs.push($(this).attr("href"));
    });

    $("#traffic-tabs li a").on("click", function () {
        var href = $(this).attr("href");
        Cookies.set("active", href);
        currentActive(Cookies.get("active"));
    });

    if (Cookies.get("active") !== undefined && jQuery.inArray(Cookies.get("active"), tabs) !== -1) {
        $("a[href=\"" + Cookies.get("active") + "\"]").tab("show");
        currentActive(Cookies.get("active"));
    } else {
        $("a[href=\"#misdatos\"]").tab("show");
        Cookies.set("active", "#misdatos");
    }
    
    $(document.body).on("click", ".openFile", function (ev) {
        ev.preventDefault();        
        var id = $(this).data("id");
        window.open("/rrhh/get/ver-archivo?id=" + id, "viewFile", "toolbar=0,location=0,menubar=0,height=550,width=880,scrollbars=yes");
    });

    $(document.body).on("input", "#titulo, #textActivity", function (evt) {
        var input = $(this);
        var start = input[0].selectionStart;
        $(this).val(function (_, val) {
            return val.toUpperCase();
        });
        input[0].selectionStart = input[0].selectionEnd = start;
    });

    Number.prototype.pad = function (size) {
        var s = String(this);
        while (s.length < (size || 2)) {
            s = "0" + s;
        }
        return s;
    };

    function obtenerActividades() {
        $.ajax({
            url: "/principal/get/mis-actividades", method: "GET", dataType: "json", data: { fecha: selectedDate }, success: function (res) {
                $("#myActivities").html(res.html);
            }
        });
    }

    $("#cc").calendar({
        onSelect: function (date) {
            selectedDate = date.getFullYear() + "-" + (date.getMonth() + 1).pad() + "-" + date.getDate().pad();
            $("#selectedDate").html(selectedDate);
            obtenerActividades();
        }
    });

    $(document.body).on('change', '#idDepto', function () {
        var idDepto = $(this).val();
        $.ajax({
            url: "/principal/get/formulario-departamento", type: "GET", dataType: "json", timeout: 3000,
            data: { idDepto: idDepto },
            success: function (res) {
                if (res.success === true) {
                    $("#formulario").html(res.html);
                }
            }
        });
        $("#submit").removeAttr("disabled");
    });

    $(document.body).on('click', '.addActivity', function () {
        $('#newActivity').show();
        $('#textActivity').focus();
    });

    $(document.body).on('click', '.deleteActivity', function () {
        var id = $(this).data("id");
        $.confirm({
            title: "Confirmar", type: "red", content: '¿Está seguro de que desea eliminar la actividad?', escapeKey: "cerrar", boxWidth: "250px", useBootstrap: false,
            buttons: {
                si: {
                    btnClass: "btn-red",
                    action: function () {
                        $.ajax({
                            url: "/principal/post/actividad-borrar", dataType: "json", timeout: 3000, type: "POST",
                            data: { id: id },
                            success: function (res) {
                                if (res.success === true) {
                                    $('.activityRow_' + id).hide();
                                }
                            }
                        });
                    }
                },
                no: function () { }
            }
        });
    });

    $(document.body).on('keyup', '#textActivity', function (e) {
        if (e.keyCode === 13 && $('#textActivity').val() !== "") {
            $(".saveActivity").trigger("click");
        }
    });

    $(document.body).on('click', '.activityRow', function () {
        var id = $(this).data("id");
        $("#formulario").html("");
        $("#idDepto").val("")
            .prop('disabled', true);
        tinymce.activeEditor.setContent("");
        $.ajax({
            url: "/principal/get/actividad-detalle", type: "GET", dataType: "json", timeout: 3000,
            data: { id: id },
            success: function (res) {
                if (res.success === true) {
                    $("#idActividad").val(id);
                    $("#titulo").val(res.titulo);
                    $("#titulo").removeAttr("disabled");
                    $("#idDepto").removeAttr("disabled");
                    if (res.html) {
                        $("#idDepto").val(res.idDepto);
                        $("#formulario").html(res.html);
                        $("#submit").removeAttr("disabled");
                    }
                    var a = ["tipoActividad", "idCliente", "totalTickets", "totalEnvios", "saldoFinal", "expedientesFacturados", "facturasCanceladas", "expedientesArchivados", "pedimentosModulados", "pedimentosPagados", "cantidadVerdes", "cantidadRojos", "quejas", "visitas", "llamadas", "documentos", "multas", "consultas", "duracion"];
                    a.forEach(function (entry) {
                        if (res[entry]) {
                            $("#" + entry).val(res[entry]);
                        }
                    });
                    if (res.observaciones) {
                        tinymce.activeEditor.setContent(res.observaciones);
                    }
                }
                $("#titulo").focus();
            }
        });
    });

    $(document.body).on('click', '.saveActivity', function () {
        $.ajax({
            url: "/principal/post/actividad", type: "POST", dataType: "json", timeout: 3000,
            data: { fecha: selectedDate, titulo: $("#textActivity").val() },
            success: function (res) {
                if (res.success === true) {
                    $("#textActivity").val("");
                    $("#newActivity").hide();
                    obtenerActividades();
                }
            }
        });
    });

    $(document.body).on('click', '#submit', function (ev) {
        ev.preventDefault();
    });

    tinyMCE.init({
        mode: "textareas",
        theme: "advanced",
        plugins: "pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template,autoresize",
        theme_advanced_buttons1: "bold,italic,underline,|,justifyleft,justifycenter,justifyright,justifyfull,|,formatselect,fontselect,fontsizeselect",
        theme_advanced_buttons2: "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,code,|,forecolor,backcolor",
        theme_advanced_toolbar_location: "top",
        theme_advanced_toolbar_align: "left",
        theme_advanced_statusbar_location: "bottom",
        theme_advanced_resizing: false,
        save_onsavecallback: "guardarActividad",
        content_css: "/tinymce/tiny_mce/tinymce.css",
        width: '100%',
        height: 200,
        autoresize_min_height: 200,
        autoresize_max_height: 800
    });

    function guardarActividad(html) {
        var id = $("#id").val();
        $.ajax({
            url: "/principal/post/actividad", type: "POST", dataType: "json", timeout: 3000,
            data: { id: id, idUsuario: $("#idUsuario").val(), fecha: $("#fecha").val(), contenido: html.getContent() },
            success: function (res) {
                if (res.success === true) {
                    $.toast({ text: "<strong>Guardado</strong>", bgColor: "green", stack: 3, position: "bottom-right" });
                }
            }
        });
    }

    $(document.body).on("click", "#submit", function (ev) {
        ev.preventDefault();
        if ($("#activityForm").valid()) {
            tinyMCE.triggerSave();
            $("#activityForm").ajaxSubmit({
                url: "/principal/post/actividad-guardar", type: "post", dataType: "json", cache: false,
                success: function (res) {
                    if (res.success === true) {
                        $.toast({ text: "<strong>Guardado</strong>", bgColor: "green", stack: 3, position: "bottom-right" });
                    }
                }
            });
        }
    });

});

