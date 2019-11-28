/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function obtenerReferenciasTemporales() {
    $.post("/v2/ajax/obtener-referencias-temporales", {idUsuario: $("#idUsuario").val()}, function (res) {
        if (res.success === true) {
            $(".newTraffic").remove();
            $(".newTrafficTitles").after(res.html);
        } 
    });
}

$(document).ready(function () {

    $(document.body).on("change", "select[name^=\'idAduana']", function (ev) {
        ev.preventDefault();
        $("#idCliente").empty()
                .append('<option value="">---</option>')
                .attr("disabled", "disabled");
        $.post("/v2/post/usuario-clientes", {idUsuario: $("#idUsuario").val(), idAduana: $("#idAduana").val()}, function (res) {
            if (res.success === true) {
                $.each(ordenarPorNombre(res.array), function (key, value) {
                    $("#idCliente").append("<option value=" + value.id + ">" + value.nombre + "</option>");
                });
                $("#idCliente").removeAttr("disabled");
            } else {
                mensajeAlerta(res.message);
            }
        });
    });

    $("#form").validate({
        errorPlacement: function (error, element) {
            $(element)
                    .closest("form")
                    .find("#" + element.attr("id"))
                    .after(error);
        },
        errorElement: "span",
        errorClass: "error",
        rules: {
            idAduana: "required",
            idCliente: "required",
            cvePedimento: "required",
            tipoOperacion: "required",
            pedimento: {
                required: true,
                minlength: 7,
                digits: true
            },
            referencia: {
                required: true,
                minlength: 7
            }
        },
        messages: {
            idAduana: "Seleccionar aduana.",
            idCliente: "Seleccionar cliente.",
            cvePedimento: "Clave de pedimento necesaria",
            tipoOperacion: "Seleccionar tipo de operación.",
            pedimento: {
                required: "Proporcionar el pedimento",
                minlength: "Minimo 7 digitos",
                digits: "Pedimento deben ser solo números"
            },
            referencia: {
                required: "Proporcionar referencia",
                minlength: "Minimo 7 digitos"
            }
        }
    });

    $(document.body).on("click", "#addMore", function (ev) {
        ev.preventDefault();
        var num = parseInt($.trim($("#more").val()));
        $("#more").val(++num);
    });

    $(document.body).on("focusout", "#pedimento", function (ev) {
        if ($("#idAduana").val() !== '' && $("#pedimento").val()) {
            $.post("/v2/post/obtener-referencia", {idAduana: $("#idAduana").val(), pedimento: $("#pedimento").val(), tipoOperacion: $("#tipoOperacion").val()}, function (res) {
                if (res.success === true) {
                    $("#referencia").val(res.referencia);
                }
            });
        }
    });

    $(document.body).on("click", "#submit", function (ev) {
        ev.preventDefault();
        if ($("#form").valid()) {
            $("#form").ajaxSubmit({
                cache: false,
                url: "/v2/post/nuevo-trafico",
                type: "post",
                dataType: "json",
                timeout: 3000,
                success: function (res) {
                    if (res.success === true) {
                        obtenerReferenciasTemporales();                        
                    }
//                    if (res.success === true) {
//                        $(".newTrafficTitles").after(res.html);
//                    }
                }
            });
        }
    });

    $(document.body).on("click", "#addMore", function (ev) {
        ev.preventDefault();
        $("#newTraffic").append();
    });
    
    obtenerReferenciasTemporales();

});

