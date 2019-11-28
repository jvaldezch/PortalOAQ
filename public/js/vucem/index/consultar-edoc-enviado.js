/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$(document).ready(function () {

    $(document.body).on("click", ".button-save", function (e) {
        e.preventDefault();
        var id = $(this).data("id");
        var solicitud = $(this).data("solicitud");
        $.ajax({url: "/vucem/post/guardar-edocument", cache: false, dataType: "json", type: "POST",
            data: {solicitud: solicitud, id: id},
            success: function (res) {
                if (res.success === true) {
                    $.alert({title: "Confirmación", closeIcon: true, backgroundDismiss: true, type: "green", escapeKey: "cerrar", boxWidth: "300px", useBootstrap: false, content: res.message});
                } else {
                    $.alert({title: "¡Advertencia!", closeIcon: true, backgroundDismiss: true, type: "red", escapeKey: "cerrar", boxWidth: "300px", useBootstrap: false, content: res.message});
                }
            }
        });
    });

});

