/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function populate(frm, arr, data) {
    $.each(arr, function (index, value) {
        var ctrl = $("#" + value, frm);
        switch (ctrl.prop("type")) {
            case "radio":
            case "checkbox":
                ctrl.each(function () {
                    if ($(this).attr('value') === value)
                        $(this).attr("checked", value);
                });
                break;
            default:
                ctrl.val(data[value]);
        }
    });
}

function cambiarArchivo(value) {
    $.ajax({url: "/rrhh/post/cambiar-archivo", dataType: "json", timeout: 3000, type: "POST",
        data: {id: value, tipoArchivo: $("#select_" + value).val()},
        beforeSend: function () {
            //$("#icon_" + value).html("<div class=\"traffic-icon traffic-icon-edit\" onclick=\"editarArchivo(" + value + ")\"></div>"
            //        + "<div class=\"traffic-icon traffic-icon-delete\" onclick=\"borrarArchivo(" + value + ")\"></div>");
            $("#icon_" + value).html('<div id="icon_' + value + '" style="font-size:1.4em; color: #2f3b58; float: right; margin: 3px"><i class="fas fa-pencil-alt" onclick="editarArchivo(' + value + ')"></i>&nbsp;<i class="far fa-trash-alt" onclick="borrarArchivo(' + value + ')"></i></div>');
        },
        success: function (res) {
            if (res.success === true) {
                $("#edit_" + value).html(res.nombre);
            }
        }
    });
}

function borrarArchivo(value) {
    $.confirm({title: '¡Confirmar!', boxWidth: "400px", useBootstrap: false, content: '¿Está seguro que desea eliminar el archivo?',
        buttons: {
            si: {
                btnClass: "btn-blue",
                action: function () {
                    $.ajax({url: "/rrhh/post/borrar-archivo", data: {id: value}, type: "post", dataType: "json", cache: false,
                        success: function (res) {
                            if (res.success === true) {
                                $.alert('¡Archivo borrado del sistema!');
                            }
                        }
                    });
                }
            },
            no: {btnClass: "btn-red", action: function () {}}
        }
    });
}

function editarArchivo(value) {
    $.ajax({url: "/rrhh/post/editar-archivo", dataType: "json", timeout: 3000, type: "POST",
        data: {id: value},
        beforeSend: function () {
            //$("#icon_" + value).html("<div class=\"traffic-icon traffic-icon-save\" onclick=\"cambiarArchivo(" + value + ")\"></div>"
            //        + "<div class=\"traffic-icon traffic-icon-cancel\" onclick=\"cancelarEdicion(" + value + ")\"></div>");
            $("#icon_" + value).html('<div id="icon_' + value + '" style="font-size:1.4em; color: #2f3b58; float: right; margin: 3px"><i class="far fa-save" onclick="cambiarArchivo(' + value + ')"></i>&nbsp;<i class="fas fa-times" onclick="cancelarEdicion(' + value + ')"></i></div>');
        },
        success: function (res) {
            if (res.success === true) {
                $("#edit_" + value).html(res.html);
            }
        }
    });
}

function cancelarEdicion(value) {
    $.ajax({url: "/rrhh/post/cancelar-edicion", type: "post", dataType: "json", data: {id: value}, timeout: 3000,
        beforeSend: function () {
            //$("#icon_" + value).html("<div class=\"traffic-icon traffic-icon-edit\" onclick=\"editarArchivo(" + value + ")\"></div>"
            //        + "<div class=\"traffic-icon traffic-icon-delete\" onclick=\"borrarArchivo(" + value + ")\"></div>");
            $("#icon_" + value).html('<div id="icon_' + value + '" style="font-size:1.4em; color: #2f3b58; float: right; margin: 3px"><i class="fas fa-pencil-alt" onclick="editarArchivo(' + value + ')"></i>&nbsp;<i class="far fa-trash-alt" onclick="borrarArchivo(' + value + ')"></i></div>');
        },
        success: function (res) {
            if (res.success === true) {
                $("#edit_" + value).html(res.nombre);
            }
        }
    });
}

function cargarArchivos() {
    $.ajax({url: "/rrhh/get/cargar-archivos", type: "post", dataType: "json", data: {id: $("#idEmpleado").val()}, timeout: 3000,
        success: function (res) {
            if (res.success === true) {
                $("#employeeFiles").html(res.html);
            }
        }
    });
}

function descargarArchivo(href) {
    window.location.href = href;
}

window.establecerPropiedad = function(idEmpleado, propiedad, estatus) {
    return $.ajax({type: "POST", url: "/rrhh/post/establecer-propiedad", data: {idEmpleado: idEmpleado, propiedad: propiedad, estatus: estatus}, dataType: "json",
        success: function (res) {
            if (res.success === true) {

            }
        }});
};

$(document).ready(function () {

    var arr = [];
    var generales = ["idEmpresa", "numeroEmpleado", "nombre", "apellido", "emailEmpresa", "emailPersonal", "telefono", "rfc", "curp", "nss"];
    var direccion = ["calle", "numInt", "numExt", "ciudad", "estado", "pais", "codigoPostal", "colonia", "entidad", "municipio"];
    var otros = ["edad", "fechaNacimiento", "puesto", "fechaNacimiento", "creditoInfonavit", "fechaIngreso", "fechaBaja", "banco", "numeroCuenta", "clabe", "estadoCivil", "escolaridad", "grupoSanguineo"];

    $("#traffic-tabs li a").each(function () {
        arr.push($(this).attr("href"));
    });

    $("#traffic-tabs li a").on("click", function () {
        var href = $(this).attr("href");
        Cookies.set("active", href);
    });

    if (Cookies.get("active") !== undefined && jQuery.inArray(Cookies.get("active"), arr) !== -1) {
        $("a[href=\"" + Cookies.get("active") + "\"]").tab("show");
    } else {
        $("a[href=\"#generales\"]").tab("show");
        Cookies.set("active", "#generales");
    }

    if ($("#idEmpleado").val() !== "") {
        var obj = jQuery.parseJSON($.ajax({type: "GET", url: "/rrhh/get/datos-empleado", data: {idEmpleado: $("#idEmpleado").val()}, dataType: "json", async: false}).responseText);
        if (obj.success === true) {
            jsonData = obj.json;
            if (jsonData["generales"]) {
                $("#idEmpresa").attr("readonly", "true");
                populate("#information", generales, jsonData["generales"]);
            }
            if (jsonData["direccion"]) {
                populate("#address", direccion, jsonData["direccion"]);
            }
            if (jsonData["otros"]) {
                var other = jQuery.parseJSON(jsonData["otros"]["json"]);
                jsonData["otros"] = other;
                populate("#other", otros, other);
            }
            if (jsonData["generales"]["idUsuario"]) {
                $("#idUsuario").val(jsonData["generales"]["idUsuario"]);
            }
            if (jsonData["depto"]) {
                $.ajax({type: "GET", url: "/rrhh/get/departamentos", data: {idEmpresa: $("#idEmpresa").val(), idDepto: jsonData["depto"]["idDepto"], multiple: false}, dataType: "json",
                    success: function (res) {
                        if (res.success === true) {
                            $("#deptos").html(res.html);
                        }
                    }});
                if (jsonData["depto"]["idDepto"]) {
                    obtenerPuestos(jsonData["depto"]["idDepto"], jsonData["depto"]["idPuesto"]);
                }
            }
        }
    }

    function obtenerPuestos(idDepto, idPuesto) {
        $.ajax({type: "GET", url: "/rrhh/get/puestos", data: {idDepto: idDepto, idPuesto: idPuesto, multiple: false}, dataType: "json",
            success: function (res) {
                if (res.success === true) {
                    $("#puestos").html(res.html);
                }
            }});
    }

    $(document.body).on("change", "#idDepto", function () {
        if (!jsonData["generales"]) {
            jsonData["generales"] = {};
        }
        jsonData["generales"]["idDepto"] = $(this).val();
        obtenerPuestos($(this).val(), null);
    });

    $(document.body).on("change", "#idPuesto", function () {
        if (!jsonData["generales"]) {
            jsonData["generales"] = {};
        }
        jsonData["generales"]["idPuesto"] = $(this).val();
    });

    $.each(otros, function (index, value) {
        $(document.body).on("change", "#" + value, function () {
            if (!jsonData["otros"]) {
                jsonData["otros"] = {};
            }
            jsonData["otros"][value] = $(this).val();
        });
    });

    $.each(generales, function (index, value) {
        $(document.body).on("change", "#" + value, function () {
            if (!jsonData["generales"]) {
                jsonData["generales"] = {};
            }
            jsonData["generales"][value] = $(this).val();
        });
    });

    $.each(direccion, function (index, value) {
        $(document.body).on("change", "#" + value, function () {
            if (!jsonData["direccion"]) {
                jsonData["direccion"] = {};
            }
            jsonData["direccion"][value] = $(this).val();
        });
    });

    $(document.body).on("click", "#changePicture", function () {
        $.confirm({title: "Cambiar foto de empleado", escapeKey: "cerrar", boxWidth: "400px", useBootstrap: false,
            buttons: {
                cambiar: {
                    id: "customId",
                    btnClass: "btn-blue",
                    action: function () {
                        var img = $("#uploadedPhoto");
                        $("#pictureProfile").attr("src", img.val());
                        $("#formImages").ajaxSubmit();
                    }
                },
                cerrar: {
                    btnClass: "btn-red",
                    action: function () {}
                }
            },
            content: function () {
                var self = this;
                return $.ajax({url: "/rrhh/get/cambiar-perfil?idEmpleado=" + $("#idEmpleado").val(), dataType: "json", method: "get"}).done(function (res) {
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

    var bar = $(".barImage");
    var percent = $(".percentImage");

    $("#formFiles").validate({
        errorPlacement: function (error, element) {
            $(element)
                    .closest("form")
                    .find("#" + element.attr("id"))
                    .after(error);
        },
        errorElement: "span",
        errorClass: "traffic-error",
        rules: {
            "files[]": {
                required: true
            }
        },
        messages: {
            "files[]": {
                required: " [No se ha seleccionado archivo.]"
            }
        }
    });

    $(document.body).on("click", "#uploadFiles", function (ev) {
        ev.preventDefault();
        if ($("#formFiles").valid()) {
            $("#formFiles").ajaxSubmit({type: "post", url: "/rrhh/post/subir-archivo-empleado",
                beforeSend: function () {
                    var percentVal = "0%";
                    bar.width(percentVal);
                    percent.html(percentVal);
                },
                uploadProgress: function (event, position, total, percentComplete) {
                    var percentVal = percentComplete + "%";
                    bar.width(percentVal);
                    percent.html(percentVal);
                },
                success: function () {
                    cargarArchivos();
                }
            });
        }
    });

    $(document.body).on("click", "#searchUser", function (ev) {
        ev.preventDefault();
        $.confirm({lazyOpen: true, title: "Usuarios del sistema", escapeKey: "cerrar", boxWidth: "650px", useBootstrap: false,
            buttons: {
                cerrar: {
                    btnClass: "btn-red",
                    action: function () {}
                }
            },
            content: function () {
                var self = this;
                return $.ajax({url: "/rrhh/get/usuarios", dataType: "json", method: "get"}).done(function (res) {
                    var html = "";
                    if(res.success === true) {
                        html = res.html;
                    }
                    self.setContent(html);
                }).fail(function () {
                    self.setContent("Something went wrong.");
                });
            }
        });
    });
    
    $(document.body).on("click", "#save", function () {
        $.post("/rrhh/post/guardar-datos-empleado", {idEmpleado: $("#idEmpleado").val(), json: JSON.stringify(jsonData)})
                .done(function (res) {
                    if (res.success === true) {
                        $.toast({text: "<strong>Guardado exitoso</strong>", bgColor: "green", stack: 3, position: "bottom-right"});
                    }
                });
    });

    $(document.body).on("input", "#curp, #rfc, #puesto, #nombre, #apellido", function (evt) {
        var input = $(this);
        var start = input[0].selectionStart;
        $(this).val(function (_, val) {
            return val.toUpperCase();
        });
        input[0].selectionStart = input[0].selectionEnd = start;
    });

    $("#fechaNacimiento, #fechaIngreso, #fechaBaja").datepicker({
        calendarWeeks: true,
        autoclose: true,
        language: "es",
        format: "yyyy-mm-dd"
    });

    cargarArchivos();
    
    $('#calendar').fullCalendar({
        header: {
            left: '',
            center: 'title',
            right: 'prev,next'
        },
        navLinks: true,
        editable: true,
        eventLimit: true,
        firstDay: 0,
        dayClick: function() {

        },
        eventSources: [{url: '/rrhh/get/retardos', type: 'GET',
                data: function() {
                    var fecha = $('#calendar').fullCalendar('getDate');
                    return {
                        idEmpleado: $("#idEmpleado").val(),
                        fecha: fecha.format()
                    };
                }
            }
        ]
    });

    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        $('#calendar').fullCalendar('render');
    });
    
    $("#retardo, #falta").datepicker({
        format: 'yyyy-mm-dd',
        language: "es"
    });

    $(document.body).on("click", "#agregarRetardo", function () {
        var date = $("#retardo").val();
        var parts = date.split('-');
        $.ajax({type: "POST", url: "/rrhh/post/agregar-retardo", data: {idEmpleado: $("#idEmpleado").val(), fecha: $("#retardo").val(), retardo: 1}, dataType: "json",
            success: function (res) {
                if (res.success === true) {
                    $('#calendar').fullCalendar('renderEvent', {
                        title: "Retardo",
                        allDay: true,
                        start: new Date(parts[0], parts[1] - 1, parts[2]),
                        color: "#c79100"
                    });
                }
            }});
    });
    
    $(document.body).on("click", "#agregarFalta", function () {
        var date = $("#falta").val();
        var parts = date.split('-');
        $.ajax({type: "POST", url: "/rrhh/post/agregar-retardo", data: {idEmpleado: $("#idEmpleado").val(), fecha: $("#falta").val(), falta: 1}, dataType: "json",
            success: function (res) {
                if (res.success === true) {
                    $('#calendar').fullCalendar('renderEvent', {
                        title: "Falta",
                        allDay: true,
                        start: new Date(parts[0], parts[1] - 1, parts[2]),
                        color: "#c70039"
                    });
                }
            }});
    });
    
    $(document.body).on("click", "#activeEmployee", function (ev) {
        
        var checked = $('#activeEmployee:checked').length > 0;
        
        $.ajax({type: "POST", url: "/rrhh/post/estatus-empleado", data: {idEmpleado: $("#idEmpleado").val(), checked: checked}, dataType: "json",
            success: function (res) {
                if (res.success === true) {
                    
                }
            }});
        
    });
    
    $(document.body).on("click", ".openFile", function (ev) {
        ev.preventDefault();        
        var id = $(this).data("id");
        window.open("/rrhh/get/ver-archivo?id=" + id, "viewFile", "toolbar=0,location=0,menubar=0,height=550,width=880,scrollbars=yes");
    });
    
    $(document.body).on("click", "#doctos", function (ev) {
        if($("#doctos").prop('checked')) {
            establecerPropiedad($("#idEmpleado").val(), 'doctos', 1);
        } else {
            establecerPropiedad($("#idEmpleado").val(), 'doctos', 0);
        }
    });
    
    $(document.body).on("click", "#capacit", function (ev) {
        if($("#capacit").prop('checked')) {
            establecerPropiedad($("#idEmpleado").val(), 'capacit', 1);
        } else {
            establecerPropiedad($("#idEmpleado").val(), 'capacit', 0);
        }
    });
    
});
