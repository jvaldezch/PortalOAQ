
$(document).ready(function () {

    $(document.body).on("click", "#send-aduanet", function(ev) {
        ev.preventDefault();
        $.ajax({url: '/principal/post/enviar-pedimento',
            type: "POST",
            data: {idTrafico: $("#idTrafico").val()},
            success: function (res) {
                if (res.success === true) {
                }
            }
        });

    });

    $(document.body).on("click", "#send-aduanet", function(ev) {
        ev.preventDefault();
        $.ajax({url: '/principal/post/csv-aduanet',
            type: "POST",
            data: {idTrafico: $("#idTrafico").val()},
            success: function (res) {
                if (res.success === true) {
                }
            }
        });

    });

    $(document.body).on("change", "#destinoOrigen", function() {
        $.ajax({url: '/pedimento/post/actualizar',
            type: "POST",
            data: {idPedimento: $("#idPedimento").val(), name: $(this).attr("name"), value: $(this).val()},
            success: function (res) {
                if (res.success === true) {
                }
            }
        });
    });

    $(document.body).on("change", "#tipoCambio", function() {
        $.ajax({url: '/pedimento/post/actualizar',
            type: "POST",
            data: {idPedimento: $("#idPedimento").val(), name: 'tipoCambio', value: $(this).val()},
            success: function (res) {
                if (res.success === true) {
                }
            }
        });
    });
});