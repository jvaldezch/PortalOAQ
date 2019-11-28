/**
 * programmed by Jvaldez at gmail
 * 2015.may.14
 */

function redirectPage(res) {
    if (res.success === true) {
        window.location.replace("/usuarios/index/editar-usuario?id=" + res.id);
    } else {
        if(res.message) {
            $("#error-messages").html("<span class=\"traffic-error\">" + res.message + "</span>");
        }
    }
}

$(document).ready(function () {
    $("#formUsuario").validate({
        errorPlacement: function (error, element) {
            $(element)
                    .closest("form")
                    .find("#" + element.attr("id"))
                    .after(error);
        },
        errorElement: "span",
        errorClass: "traffic-error",
        rules: {
            nombre: "required",
            empresa: "required",
            patenteUsuario: "required",
            aduanaUsuario: "required",
            usuario: {
                required: true,
                minlength: 5
            },
            email: {
                required: true,
                email: true
            },
            password: {
                required: true,
                minlength: 5
            },
            confirm: {
                minlength: 5,
                equalTo: "#password"
            },
            sispedimentos: "required"
        },
        messages: {
            nombre: "Debe proporcionar el nombre del usuario.",
            empresa: "Debe proporcionar campo.",
            patenteUsuario: "Debe proporcionar campo.",
            aduanaUsuario: "Debe proporcionar campo.",
            usuario: {
                required: "Debe proporcionar un nombre de usuario.",
                minlength: "Usuario debe tener minimo 5 caracteres."
            },
            email: "Proporcione un email válido.",
            password: {
                required: "Debe proporcionar una contraseña.",
                minlength: "Contraseña debe tener minimo 5 caracteres."
            },
            confirm: {
                minlength: "Contraseña debe tener minimo 5 caracteres.",
                equalTo: "Las contraseñas no coinciden."
            },
            sispedimentos: "Debe proporcionar campo."
        }
    });

    $("#submit").click(function (e) {
        e.preventDefault();
        if ($("#formUsuario").valid()) {
            $("#formUsuario").ajaxSubmit({
                url: "/usuarios/post/agregar-usuario",
                type: "post",
                dataType: "json",
                success: function(res) {
                    if(res.success === true) {
                        window.location.replace("/usuarios/index/editar-usuario?id=" + res.id);
                    }
                }
            });
        }
    });
    
    $(document.body).on("change", "#formUsuario #patenteUsuario", function () {
        $.ajax({
            url: "/usuarios/post/obtener-aduanas",
            cache: false,
            type: "post",
            dataType: "json",
            data: {patente: $(this).val()}
        }).done(function (res) {
            if (res.success === true) {
                $("#formUsuario #aduanaUsuario").empty();
                $("#formUsuario #aduanaUsuario").append('<option value="">---</option>');
                $.each(jQuery.parseJSON(res.aduanas), function (key, value) {
                    $("#formUsuario #aduanaUsuario").append('<option value=' + value.aduana + '>' + value.aduana + ' - ' + value.nombre + '</option>');
                });
            }
        });
    });
    
    $(document.body).on("focusout", "#nombre", function(){
        var str = $(this).val();
        str = str.toLowerCase().replace(/^[\u00C0-\u1FFF\u2C00-\uD7FF\w]|\s[\u00C0-\u1FFF\u2C00-\uD7FF\w]/g, function(letter) {
            return letter.toUpperCase();
        });
        $(this).val(str);
    });

});