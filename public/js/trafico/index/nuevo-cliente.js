/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
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
            rfc: { 
                required: true,
                regx: /^[A-Z]{3,4}([0-9]{2})([0-9]{2})([0-9]{2})?[A-Z0-9]{3,4}/
            },
            nombre: "required",
            idEmpresa: "required"
        },
        messages: {
            rfc:  {
                required: "Proporcioner RFC del cliente."
            },
            nombre: "Proporcioner el nombre del cliente.",
            idEmpresa: "Seleccionar empresa."
        }
    });

    $(document.body).on("click", "#submit", function (e) {
        e.preventDefault();
        if ($("#form").valid()) {
            $("#form").ajaxSubmit({
                dataType: "json",
                success: function (res) {
                    if (res.success === true) {
                        window.location.replace("/trafico/index/clientes");
                    } else {
                        $("#errors").html(res.message);
                    }
                }
            });
        }
    });

    $(document.body).on("input", "#nombre, #rfc, #rfcSociedad", function (e) {
        $("#errors").html("");
        var input = $(this);
        var start = input[0].selectionStart;
        $(this).val(function (_, val) {
            return val.toUpperCase();
        });
        input[0].selectionStart = input[0].selectionEnd = start;
    });

});
