
$(document).ready(function () {

    $("#form-files").validate({
        errorPlacement: function (error, element) {
            $(element)
                .closest("form")
                .find("#" + element.attr("id"))
                .after(error);
        },
        rules: {
            "files[]": {
                required: true
            }
        },
        messages: {
            "files[]": {
                required: " No se ha seleccionado archivos."
            }
        }
    });

    $("#upload").click(function (ev) {
        ev.preventDefault();
        if ($("#form-files").valid()) {
            $("#form-files").ajaxSubmit({
                type: "POST", dataType: "json", timeout: 10000,
                beforeSend: function () {
                    $('body').LoadingOverlay('show', { color: 'rgba(255, 255, 255, 0.9)' });
                },
                success: function (res) {
                    $('body').LoadingOverlay('hide');
                    if (res.success === true) {
                        $("#files").val('');
                        location.reload();
                    } else {
                        $.alert({ title: "Error", type: "red", content: res.message, boxWidth: "350px", useBootstrap: false });
                    }
                }
            });
        }
    });

});