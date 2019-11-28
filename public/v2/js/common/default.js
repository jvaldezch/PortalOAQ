/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Esta funcion ordena un objecto JSON que tiene id y nombre como atributos.
 * 
 * @param {json} jsonObject
 * @returns {Array|ordenarPorNombre.dataArray}
 */
function ordenarPorNombre(jsonObject) {
    var dataArray = [];
    var id;
    for (id in jsonObject) {
        var nombre = jsonObject[id];
        dataArray.push({id: parseInt(id), nombre: nombre});
    }
    dataArray.sort(function (a, b) {
        if (a.nombre < b.nombre)
            return -1;
        if (b.nombre < a.nombre)
            return 1;
        return 0;
    });
    return dataArray;
}

/**
 * URL : https://craftpip.github.io/jquery-confirm/#customizing
 * 
 * @param {type} mensaje
 * @returns {undefined}
 */
function mensajeAlerta(mensaje) {
    $.alert({
        title: "¡Advertencia!",
        content: mensaje,
        type: "orange",
        typeAnimated: true,
        useBootstrap: false
    });
}

function mensajeConfirmacion() {
    $.confirm({
        title: "¡Cuidado!",
        content: "Esta acción es irreversible ¿está seguro que desea remover el registro?",
        type: "red",
        typeAnimated: true,
        useBootstrap: false,
        buttons: {
            si: {
                text: "Si",
                btnClass: "button-red",
                action: function () {
                }
            },
            no: {
                text: "No",
                action: function () {
                }
            }
        }
    });
}

$(document).ready(function () {

    $(window).focus(function () {
        window_focus = true;
        $.ajax({
            url: "/v2/session/verify",
            cache: false,
            type: "post",
            success: function (res) {
                if (res.success === false) {
                    document.location = "/principal/index/index";
                }
            }
        });
    }).blur(function () {
        window_focus = false;
    });

    $(document.body).on("click", "#logout", function () {
        console.log("logout!");
        $.ajax({
            url: "/v2/session/logout",
            cache: false,
            type: "post",
            success: function (res) {
                if (res.success === true) {
                    document.location = "/principal/index/index";
                }
            }
        });
    });

    $(document.body).on("input", "#buscar, #referencia, #rfcCliente, #observaciones, #comentarios", function (evt) {
        var input = $(this);
        var start = input[0].selectionStart;
        $(this).val(function (_, val) {
            return val.toUpperCase();
        });
        input[0].selectionStart = input[0].selectionEnd = start;
    });

});

