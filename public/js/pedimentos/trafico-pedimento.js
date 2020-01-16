
$(document).ready(function () {

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
});