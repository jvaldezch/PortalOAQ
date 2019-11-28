/**
 * programmed by Jvaldez at gmail
 * 2015.may.14
 */


$(document).ready(function () {
    
    $.validator.addMethod("regx", function (value, element, regexpr) {
        return regexpr.test(value);
    }, "RFC no es válido.");
    
    $("#form").validate({
        errorPlacement: function (error, element) {
            $(element)
                    .closest("form")
                    .find("#" + element.attr("id"))
                    .after(error);
        },
        errorElement: "span",
        errorClass: "traffic-error",
        rules: {
            patente: {
                required: true,
                minlength: 4,
                maxlength: 4,
                digits: true
            },
            rfc: {
                required: true,
                minlength: 10,
                regx: /^[A-Z]{3,4}([0-9]{2})([0-9]{2})([0-9]{2})?[A-Z0-9]{3,4}/
            },
            nombre: "required"
        },
        messages: {
            patente: {
                required: "Campo necesario",
                minlength: "Patente debe ser de 4 digitos",
                maxlength: "Patente dede ser de 4 digitos",
                digits: "No debe contener letras"
            },
            rfc: "Campo necesario",
            nombre: "Campo necesario"
        }
    });
    
    $(document.body).on("click", "#submit", function (ev) {
        ev.preventDefault();
        if ($("#form").valid()) {
            $("#form").ajaxSubmit({
                type: "post",
                url: "/trafico/post/agregar-agente-aduanal",
                dataType: "json",
                success: function (res) {
                    if (res.success === true) {
                        window.location.href = "/trafico/index/agentes-aduanales";
                    }
                }
            });
        }
    });
    
    $(document.body).on("click", "#update", function (ev) {
        ev.preventDefault();
        if ($("#form").valid()) {
            $("#form").ajaxSubmit({
                type: "post",
                url: "/trafico/post/agregar-agente-aduanal",
                dataType: "json",
                success: function (res) {
                    if (res.success === true) {
                        window.location.href = "/trafico/index/agentes-aduanales";
                    }
                }
            });
        }
    });
    
    $(document.body).on("input", "#rfc, #nombre", function () {
        var input = $(this);
        var start = input[0].selectionStart;
        $(this).val(function (_, val) {
            return val.toUpperCase();
        });
        input[0].selectionStart = input[0].selectionEnd = start;
    });

});