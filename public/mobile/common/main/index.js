
$(document).ready(function () {
    
    $("#form-signin").validate({
        errorPlacement: function (error, element) {
            $(element)
                    .closest("form")
                    .find("#" + element.attr("id"))
                    .after(error);
        },
        errorElement: "span",
        errorClass: "error",
        rules: {
            username: "required",
            password: "required"
        },
        messages: {
            username: "Se requiere usuario.",
            password: "Se requiere contraseña."
        }
    });
    
    $(document.body).on("click", "#submit", function (ev) {
        ev.preventDefault();
        if ($("#form-signin").valid()) {
            $("#form-signin").ajaxSubmit({ url: "/mobile/auth/login", cache: false, type: "post", dataType: "json",
                success: function (res) {
                    if (res.success === true) {
                        document.location = "/mobile/main/inicio";
                    } else {
                        $("#form-signin").validate().showErrors({password: res.message});
                    }
                }
            });
        }
    });

});