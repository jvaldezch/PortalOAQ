
$(document).ready(function () {

    $("#form-photos").validate({
        errorPlacement: function (error, element) {
            $(element)
                .closest("form")
                .find("#" + element.attr("id"))
                .after(error);
        },
        rules: {
            "photos[]": {
                required: true
            }
        },
        messages: {
            "photos[]": {
                required: " No se ha seleccionado fotos."
            }
        }
    });

    $("#upload").click(function (ev) {
        ev.preventDefault();
        if ($("#form-photos").valid()) {
            $("#form-photos").ajaxSubmit({
                type: "POST", dataType: "json", timeout: 10000,
                beforeSend: function () {
                    $('body').LoadingOverlay('show', { color: 'rgba(255, 255, 255, 0.9)' });
                },
                success: function (res) {
                    $('body').LoadingOverlay('hide');
                    if (res.success === true) {
                        $("#photos").val('');
                        location.reload();
                    } else {
                        $.alert({ title: "Error", type: "red", content: res.message, boxWidth: "350px", useBootstrap: false });
                    }
                }
            });
        }
    });


});