$(document).ready(function () {
    
    $("#form").validate({
        errorPlacement: function (error, element) {
            $(element)
                    .closest("form")
                    .find("label[for='" + element.attr("id") + "']")
                    .append(error);
        },
        errorElement: "span",
        errorClass: "traffic-error",
        rules: {
            password: "required",
            rptPassword: {
                required: true,
                minlength: 5,
                equalTo: "#password"
            }
        },
        messages: {
            password: "Se requiere campo.",
            confirm_password: {
                required: "Se requiere campo.",
                minlength: "Mínimo de 5 caracteres.",
                equalTo: "Contraseñas deben ser iguales."
            }
        }
    });
    
    $("#submit").click(function (ev) {
        ev.preventDefault();
        if ($("#form").valid()) {
            $("#form").ajaxSubmit({
                cache: false,
                url: "/usuarios/ajax/actualizar-mis-datos",
                type: "post",
                dataType: "json",
                success: function (res) {
                    if (res.success === true) {
                    }
                }
            });
        }
    });
    
});