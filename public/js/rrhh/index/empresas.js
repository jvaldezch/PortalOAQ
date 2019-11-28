/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$(document).ready(function () {

    function obtenerDepartamentos(idEmpresa) {
        $.ajax({type: "GET", url: "/rrhh/get/departamentos", data: {idEmpresa: idEmpresa}, dataType: "json",
            success: function (res) {
                if (res.success === true) {
                    $("#deptos").html(res.html);
                }
            }});
    }

    function obtenerPuestos(idDepto) {
        $.ajax({type: "GET", url: "/rrhh/get/puestos", data: {idDepto: idDepto}, dataType: "json",
            success: function (res) {
                if (res.success === true) {
                    $("#puestos").html(res.html);
                }
            }});
    }
    
    function obtenerActividades(idPuesto) {
        $.ajax({type: "GET", url: "/rrhh/get/actividades", data: {idPuesto: idPuesto}, dataType: "json",
            success: function (res) {
                if (res.success === true) {
                    $("#actividades").html(res.html);
                }
            }});
        $.ajax({type: "GET", url: "/rrhh/get/edicion-puesto", data: {idEmpresa: $("#idEmpresa option:selected").val(), idDepto: $("#idDepto option:selected").val(), idPuesto: $("#idPuesto option:selected").val()}, dataType: "json",
            success: function (res) {
                if (res.success === true) {
                    $("#edit").html(res.html);
                }
            }});
    }

    function alerta(message) {
        $.alert({title: "¡Advertencia!", closeIcon: true, backgroundDismiss: true, type: "red", escapeKey: "cerrar", boxWidth: "300px", useBootstrap: false, content: message});
    }
    
    $(document.body).on("click", "#borrarDepto", function () {
        alerta("Funcionalidad no lista.");
    });

    $(document.body).on("click", "#editarDepto", function () {
        var idEmpresa = $("#idEmpresa").val();
        var idDepto = $("#idDepto").val();
        //var nombreDepto = $("#idDepto option:selected").text();
        if (idEmpresa !== "" && idDepto !== "") {
            $.ajax({url: '/rrhh/get/editar',dataType: 'json', type: 'GET', 
                data: {idEmpresa: idEmpresa, idDepto: idDepto, tipo: 'depto'},
                success: function (res) {
                    if (res.success === true) {
                        //obtenerDepartamentos(idEmpresa);
                        $("#edit").html(res.html);
                    } else {
                        alerta(res.message);
                    }
                }});
//            $("#edit[name='idDepto']").val(idDepto);
//            $("#edit[name='nombreDepto']").val($("#nombreDepto").val());
//            $.confirm({title: "Editar departamento", type: "green", escapeKey: "cerrar", boxWidth: "350px", useBootstrap: false,
//                buttons: {
//                    guardar: {
//                        btnClass: "btn-green",
//                        action: function () {
//                            $.ajax({type: "POST", url: "/rrhh/post/editar-departamento", data: {idEmpresa: idEmpresa, idDepto: idDepto, nombreDepto: $("#nombreDepto").val()}, dataType: "json",
//                                success: function (res) {
//                                    if (res.success === true) {
//                                        obtenerDepartamentos(idEmpresa);
//                                    }
//                                }});
//                        }
//                    },
//                    cerrar: function () {}
//                },
//                content: 'Nombre del departamento:<br><input type="text" id="nombreDepto" name="nombreDepto" style="width: 300px" value="' + nombreDepto + '" />'
//            });
        } else {
            alerta("No se ha seleccionado empresa o departamento para editar.");
        }
    });
    
    $(document.body).on("click", "#agregarDepto", function () {
        var idEmpresa = $("#idEmpresa").val();
        if ($("#idEmpresa").val() !== "") {
            $.confirm({title: "Agregar departamento", type: "green", escapeKey: "cerrar", boxWidth: "350px", useBootstrap: false,
                buttons: {
                    agregar: {
                        btnClass: "btn-green",
                        action: function () {
                            $.ajax({type: "POST", url: "/rrhh/post/agregar-departamento", data: {idEmpresa: idEmpresa, nombreDepto: $("#nombreDepto").val()}, dataType: "json",
                                success: function (res) {
                                    if (res.success === true) {
                                        obtenerDepartamentos(idEmpresa);
                                    }
                                }});
                        }
                    },
                    cerrar: function () {}
                },
                content: 'Nombre del departamento:<br><input type="text" id="nombreDepto" name="nombreDepto" style="width: 300px" />'
            });
        } else {
            alerta("No se ha seleccionado empresa.");
        }
    });

    $(document.body).on("click", "#borrarPuesto", function () {
        alerta("Funcionalidad no lista.");
    });
    
    $(document.body).on("click", "#editarPuesto", function () {
        var idDepto = $("#idDepto option:selected").val();
        var idPuesto = $("#idPuesto option:selected").val();
        var nombrePuesto = $("#idPuesto option:selected").text();
        if ($("#idEmpresa").val() !== "" && nombrePuesto !== "") {
            $.confirm({title: "Editar departamento", type: "green", escapeKey: "cerrar", boxWidth: "350px", useBootstrap: false,
                buttons: {
                    guardar: {
                        btnClass: "btn-green",
                        action: function () {
                            $.ajax({type: "POST", url: "/rrhh/post/editar-puesto", data: {idDepto: idDepto, idPuesto: idPuesto, nombrePuesto: $("#nombrePuesto").val()}, dataType: "json",
                                success: function (res) {
                                    if (res.success === true) {
                                        obtenerPuestos(idDepto);
                                    }
                                }});
                        }
                    },
                    cerrar: function () {}
                },
                content: 'Nombre del puesto:<br><input type="text" id="nombrePuesto" name="nombrePuesto" style="width: 300px" value="' + nombrePuesto + '" />'
            });
        } else {
            alerta("No se ha seleccionado puesto para editar.");
        }
    });
    
    $(document.body).on("click", "#agregarPuesto", function () {
        if ($("#idDepto").length && $("#idDepto").val() !== "") {
            var idDepto = $("#idDepto option:selected").val();
            $.confirm({title: "Agregar puesto", type: "green", escapeKey: "cerrar", boxWidth: "350px", useBootstrap: false,
                buttons: {
                    agregar: {
                        btnClass: "btn-green",
                        action: function () {
                            $.ajax({type: "POST", url: "/rrhh/post/agregar-puesto", data: {idDepto: idDepto, nombrePuesto: $("#nombrePuesto").val()}, dataType: "json",
                                success: function (res) {
                                    if (res.success === true) {
                                        obtenerPuestos(idDepto);
                                    }
                                }});
                        }
                    },
                    cerrar: function () {}
                },
                content: 'Nombre del puesto:<br><input type="text" id="nombrePuesto" name="nombrePuesto" style="width: 300px" /><script>$("#nombrePuesto").focus();</script>'
            });
        } else {
            alerta("No se ha seleccionado departamento.");
        }
    });
    
    $(document.body).on("click", "#borrarActividad", function () {
        alerta("Funcionalidad no lista.");
    });
    
    $(document.body).on("click", "#editarActividad", function () {
        var idPuesto = $("#idPuesto option:selected").val();
        var idActividad = $("#idActividad option:selected").val();
        var nombreActividad = $("#idActividad option:selected").text();
        if ($("#idPuesto").val() !== "" && nombreActividad !== "") {
            $.confirm({title: "Editar actividad", type: "green", escapeKey: "cerrar", boxWidth: "350px", useBootstrap: false,
                buttons: {
                    guardar: {
                        btnClass: "btn-green",
                        action: function () {
                            $.ajax({type: "POST", url: "/rrhh/post/editar-actividad", data: {idPuesto: idPuesto, idActividad: idActividad, nombreActividad: $("#nombreActividad").val()}, dataType: "json",
                                success: function (res) {
                                    if (res.success === true) {
                                        obtenerActividades(idPuesto);
                                    }
                                }});
                        }
                    },
                    cerrar: function () {}
                },
                content: 'Nombre del actividad:<br><input type="text" id="nombreActividad" name="nombreActividad" style="width: 300px" value="' + nombreActividad + '" />'
            });
        } else {
            alerta("No se ha seleccionado actividad para editar.");
        }
    });
    
    $(document.body).on("click", "#agregarActividad", function () {
        if ($("#idPuesto").length && $("#idPuesto").val() !== "") {
            var idDepto = $("#idDepto option:selected").val();
            var idPuesto = $("#idPuesto option:selected").val();
            $.confirm({title: "Agregar actividad", type: "green", escapeKey: "cerrar", boxWidth: "350px", useBootstrap: false,
                buttons: {
                    agregar: {
                        btnClass: "btn-green",
                        action: function () {
                            $.ajax({type: "POST", url: "/rrhh/post/agregar-actividad", data: {idDepto: idDepto, idPuesto: idPuesto, nombreActividad: $("#nombreActividad").val()}, dataType: "json",
                                success: function (res) {
                                    if (res.success === true) {
                                        obtenerActividades(idPuesto);
                                    }
                                }});
                        }
                    },
                    cerrar: function () {}
                },
                content: 'Nombre de la actividad:<br><input type="text" id="nombreActividad" name="nombreActividad" style="width: 300px" />'
            });
        } else {
            alerta("No se ha seleccionado puesto.");
        }
    });

    $(document.body).on("change", "#idEmpresa", function () {
        obtenerDepartamentos($(this).val());
    });

    $(document.body).on("change", "#idDepto", function () {
        obtenerPuestos($(this).val());
    });
    
    $(document.body).on("change", "#idPuesto", function () {
        obtenerActividades($(this).val());
    });

    $(document.body).on("click", "#submit", function () {
        
    });
    
});

