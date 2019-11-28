/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

var today = new Date();
var selectedDate = today.toISOString().substring(0, 10);

function mensajeAlerta(mensaje) {
    $.alert({title: "Alerta", type: "red", typeAnimated: true, useBootstrap: false, boxWidth: "250px",
        content: mensaje
    });
}

$(document).ready(function () {

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
        $.ajax({url: "/principal/get/mis-actividades", method: "GET", dataType: "json", data: {fecha: selectedDate}, success: function (res) {
                $("#myActivities").html(res.html);
            }});
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
        $.ajax({url: "/principal/get/formulario-departamento", type: "GET", dataType: "json", timeout: 3000,
            data: {idDepto: idDepto},
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
        $.confirm({title: "Confirmar", type: "red", content: '¿Está seguro de que desea eliminar la actividad?', escapeKey: "cerrar", boxWidth: "250px", useBootstrap: false,
            buttons: {
                si: {
                    btnClass: "btn-red",
                    action: function () {
                        $.ajax({url: "/principal/post/actividad-borrar", dataType: "json", timeout: 3000, type: "POST",
                            data: {id: id},
                            success: function (res) {
                                if (res.success === true) {
                                    $('.activityRow_' + id).hide();
                                }
                            }
                        });
                    }
                },
                no: function () {}
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
        $.ajax({url: "/principal/get/actividad-detalle", type: "GET", dataType: "json", timeout: 3000,
            data: {id: id},
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
        $.ajax({url: "/principal/post/actividad", type: "POST", dataType: "json", timeout: 3000,
            data: {fecha: selectedDate, titulo: $("#textActivity").val()},
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
        $.ajax({url: "/principal/post/actividad", type: "POST", dataType: "json", timeout: 3000,
            data: {id: id, idUsuario: $("#idUsuario").val(), fecha: $("#fecha").val(), contenido: html.getContent()},
            success: function (res) {
                if (res.success === true) {
                    $.toast({text: "<strong>Guardado</strong>", bgColor: "green", stack: 3, position: "bottom-right"});
                }
            }
        });
    }

    $(document.body).on("click", "#submit", function (ev) {
        ev.preventDefault();
        if ($("#activityForm").valid()) {
            tinyMCE.triggerSave();
            $("#activityForm").ajaxSubmit({url: "/principal/post/actividad-guardar", type: "post", dataType: "json", cache: false,
                success: function (res) {
                    if (res.success === true) {
                        $.toast({text: "<strong>Guardado</strong>", bgColor: "green", stack: 3, position: "bottom-right"});
                    }
                }
            });
        }
    });

});

