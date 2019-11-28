/**
 * programmed by Jvaldez at gmail
 * 2015.may.14
 */

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
            sispedimentos: "Debe proporcionar campo."
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
                $("#aduanaUsuario").empty();
                $("#aduanaUsuario").append('<option value="">---</option>');
                $.each(jQuery.parseJSON(res.aduanas), function (key, value) {
                    $("#aduanaUsuario").append('<option value=' + value.aduana + '>' + value.aduana + ' - ' + value.nombre + '</option>');
                });
            }
        });
    });

    $(document.body).on("click", "#viewPassword", function (e) {
        var idUsuario = $("#idUsuario").val();
        e.preventDefault();
        $.confirm({
            title: "Contraseña",
            useBootstrap: false,
            boxWidth: "350px",
            content: '' +
            '<form action="" class="formName" method="post">' +
            '<div class="form-group">' +
            '<label>Proporcionar contraseña:</label>' +
            '<input type="password" placeholder="Password" class="name form-control" required />' +
            '</div>' +
            '</form>' +
            '<script type="text/javascript">$("form.formName .name").focus();</script>',
            buttons: {
                formSubmit: {
                    text: "Submit",
                    btnClass: "btn-blue",
                    action: function () {
                        $.post("/usuarios/post/obtener-password", {idUsuario: idUsuario, password: this.$content.find(".name").val()})
                                .done(function (res) {
                                    if (res.success === true) {
                                        $.alert({title: "Resultados", content: res.pass, useBootstrap: false, boxWidth: "350px"});
                                    }
                                });
                    }
                },
                cancel: function () {}
            },
            onContentReady: function () {
                var jc = this;
                this.$content.find("form").on("submit", function (e) {
                    e.preventDefault();
                    jc.$$formSubmit.trigger("click");
                });
            }
        });
    });
    
    $(document.body).on("click", "#formUsuario #actualizarUsuario", function (e) {
        e.preventDefault();
        if ($("#formUsuario").valid()) {
            $("#formUsuario").ajaxSubmit({
                url: "/usuarios/post/actualizar-usuario",
                type: "post",
                dataType: "json",
                success: function (res) {
                    if (res.success === true) {
                        $.toast({text: "<strong>Guardado</strong>", bgColor: "green", stack : 3, position : "bottom-right"});
                    }
                }
            });
        }
    });

});