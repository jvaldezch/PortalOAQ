function load(rol) {
    $.ajax({
        url: "/usuarios/ajax/cargar-menu",
        type: "post",
        data: {rol: rol},
        dataType: "json",
        success: function (res) {
            if (res.success === true) {
                $("#estatus").html(res.html);
            }
        }
    });
}

function edit(value) {
    $.ajax({
        url: "/usuarios/ajax/editar-menu",
        type: "post",
        data: {id: value},
        dataType: "json",
        success: function (res) {
            if (res.success === true) {
                $("#editor").html(res.html);
            }
        }
    });
}

function save(idAccion, nombre, orden) {
    $.ajax({
        url: "/usuarios/ajax/guardar-menu",
        type: "post",
        data: {idAccion: idAccion, nombre: nombre, orden: orden},
        dataType: "json",
        success: function (res) {
            if (res.success === true) {
                load($("#rol").val());
            }
        }
    });
}

function agregarMenu(idAccion, idRol) {
    $.ajax({
        url: "/usuarios/ajax/menu-agregar",
        type: "post",
        data: {idAccion: idAccion, idRol: idRol},
        dataType: "json",
        success: function (res) {
            if (res.success === true) {
                load($("#rol").val());
            }
        }
    });
}

function removerMenu(idAccion, idRol) {
    $.ajax({
        url: "/usuarios/ajax/menu-remover",
        type: "post",
        data: {idAccion: idAccion, idRol: idRol},
        dataType: "json",
        success: function (res) {
            if (res.success === true) {
                load($("#rol").val());
            }
        }
    });
}

$(document).ready(function () {
    
    $("#roles").validate({
        errorPlacement: function (error, element) {
            $(element)
                    .closest("form")
                    .find("label[for='" + element.attr("id") + "']")
                    .append(error);
        },
        errorElement: "span",
        errorClass: "traffic-error",
        rules: {
            rol: "required"
        },
        messages: {
            rol: " [Selccionar rol]"
        }
    });
    
    $("#load").click(function (e) {
        e.preventDefault();
        if ($("#roles").valid()) {
            load($("#rol").val());
        }
    });
    
    $("#add-action").validate({
        errorPlacement: function (error, element) {
            $(element)
                    .closest("form")
                    .find("label[for='" + element.attr("id") + "']")
                    .append(error);
        },
        errorElement: "span",
        errorClass: "traffic-error",
        rules: {
            module: "required",
            controller: "required",
            action: "required",
            name: "required"
        },
        messages: {
            module: " [Selccionar modulo]",
            controller: " [Selccionar controlador]",
            action: " [Proporcionar accion]",
            name: " [Selccionar nombre]"
        }
    });
    
    $("#save-action").click(function (e) {
        e.preventDefault();
        if ($("#add-action").valid()) {
            $("#add-action").ajaxSubmit({
                url: "/usuarios/ajax/agregar-menu",
                type: "post",
                dataType: "json",
                success: function(res) {
                    
                }
            });
        }
    });
    
});