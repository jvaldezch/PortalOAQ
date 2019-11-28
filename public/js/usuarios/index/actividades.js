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

Number.prototype.pad = function (size) {
    var s = String(this);
    while (s.length < (size || 2)) {
        s = "0" + s;
    }
    return s;
};

function obtenerActividades() {
    $.ajax({url: "/usuarios/get/actividades-usuarios", method: "GET", dataType: "json", data: {fecha: selectedDate}, success: function (res) {
            $("#activities").html(res.html);
        }});
}

$(document).ready(function () {

    $("#cc").calendar({
        onSelect: function (date) {
            selectedDate = date.getFullYear() + "-" + (date.getMonth() + 1).pad() + "-" + date.getDate().pad();
            $("#selectedDate").html(selectedDate);
            obtenerActividades();
        }
    });
    
    obtenerActividades();

});
