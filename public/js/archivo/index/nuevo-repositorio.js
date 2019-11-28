/**
 * programmed by Jvaldez at gmail
 * 2015.may.14
 */

function check(e, value) {
    //Check Charater
    var unicode = e.charCode ? e.charCode : e.keyCode;
    if (value.indexOf(".") != -1) {
        if (unicode == 46) {
            return false;
        }
    }
    if (unicode != 8) {
        if ((unicode < 48 || unicode > 57) && unicode != 46) {
            return false;
        }
    }
}

function checkLength(len, ele) {
    var fieldLength = ele.value.length;
    if (fieldLength <= len) {
        return true;
    } else {
        var str = ele.value;
        str = str.substring(0, str.length - 1);
        ele.value = str;
    }
}

$(document).ready(function () {

    $("#nombre").typeahead({
        source: function (query, process) {
            return $.ajax({
                url: "/trafico/get/clientes",
                type: "get",
                data: {name: query},
                dataType: "json",
                success: function (res) {
                    return process(res);
                }
            });
        }
    }).change(function () {
        $("#rfc_cliente").val("");
    });

    $(document.body).on("change", "#nombre", function () {
        $.ajax({
            url: "/trafico/get/rfc-de-cliente",
            type: "get",
            data: {name: $("#nombre").val()},
            dataType: "json",
            success: function (res) {
                if (res) {
                    $("#rfc_cliente").val(res[0]["rfc"]);
                }
            }
        });
    });

    $(document.body).on("click", "#submit", function (e) {
        e.preventDefault();
        if ($("#form").valid()) {
            $("#form").ajaxSubmit({
                cache: false,
                type: "post",
                dataType: "json",
                url: "/archivo/post/validar-repositorio",
                success: function (res) {
                    if (res.success === true) {
                        $(location).attr("href", "/archivo/index/expediente?id=" + res.id);
                    } else {
                        $.alert({title: "Error", type: "red", content: res.message, boxWidth: "300px", useBootstrap: false});
                    }
                }
            });
        }
    });

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
            aduana: {
                required: true
            },
            pedimento: {
                required: true,
                minlength: 7,
                maxlength: 7,
                regx: /^[0-9]{7}$/
            },
            referencia: {
                required: true,
                minlength: 4
            },
            rfc_cliente: {
                required: true,
                minlength: 10,
                regx: /^[A-Z]{3,4}([0-9]{2})([0-9]{2})([0-9]{2})?[A-Z0-9]{3,4}/
            }
        },
        messages: {
            aduana: {
                required: "Proporcionar aduana"
            },
            pedimento: {
                required: "Campo necesario",
                minlength: "Pedimento debe ser de 7 digitos",
                maxlength: "Pedimento dede ser de 7 digitos",
                digits: "No debe contener letras"
            },
            referencia: {
                required: "Proporcionar referencia",
                minlength: "Minimo 4 digitos"
            },
            rfc_cliente: {
                required: "Proporcionar el RFC del cliente",
                minlength: "Minimo 10 caracteres"
            }
        }
    });

    $(document).on("change", "#patente", function () {
        $.ajax({
            url: "/archivo/ajax/obtener-aduanas",
            data: {patente: $(this).val(), id: $("#id").val()},
            type: "post",
            dataType: "json",
            cache: false,
            success: function (data) {
                if (data.success === true) {
                    $("#customs").html(data.html);
                }
            }
        });
    });

    $(document.body).on("input", "#referencia, #rfc_cliente, #nombre", function () {
        var input = $(this);
        var start = input[0].selectionStart;
        $(this).val(function (_, val) {
            return val.toUpperCase();
        });
        input[0].selectionStart = input[0].selectionEnd = start;
    });

});