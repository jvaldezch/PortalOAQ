/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$(document).ready(function () {

    $(document.body).on("click", "#import-traffic", function (ev) {
        ev.preventDefault();

        var patente = $("#patente").val();
        var aduana = $("#aduana").val();
        var pedimento = $("#pedimento").val();
        var referencia = $("#referencia").val();

        $.ajax({
            url: '/manifestacion/get/edocuments-trafico', 
            dataType: "json", 
            type: "GET",
            data: { patente: patente, aduana: aduana, pedimento: pedimento, referencia: referencia },
            beforeSend: function () {
                $("body").LoadingOverlay("show", { color: "rgba(255, 255, 255, 0.9)" });
            },
            success: function (res) {
                $("body").LoadingOverlay("hide");
                if (res.success === true) {
                }
            }
        });
    });

});