/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$(document).ready(function () {

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
            idAduana: "required",
            idCliente: "required",
            tipoOperacion: "required",
            tipoOperacion: "required",
            cvePedimento: "required",
            pedimento: {
                required: true,
                minlength: 7,
                maxlength: 7
            },
            referencia: {
                required: true,
                minlength: 4
            }
        },
        messages: {
            idAduana: "Seleccionar aduana.",
            idCliente: "Seleccionar cliente.",
            tipoOperacion: "Seleccionar tipo de operación.",
            cvePedimento: "Clave de pedimento necesaria",
            pedimento: {
                required: "Campo necesario",
                minlength: "Pedimento debe ser de 7 digitos",
                maxlength: "Pedimento dede ser de 7 digitos",
                digits: "No debe contener letras"
            },
            referencia: {
                required: "Proporcionar referencia",
                minlength: "Mínimo 4 caracteres alfanumérico"
            }
        }
    });

    $(document.body).on("change", "#referencia", function (ev) {
        var idAduana = $("#idAduana").val();
        var referencia = $(this).val();
        $.ajax({
            url: '/manifestacion/get/trafico', 
            dataType: "json", 
            type: "GET",
            data: { idAduana: idAduana, referencia: referencia },
            beforeSend: function () {
                $("body").LoadingOverlay("show", { color: "rgba(255, 255, 255, 0.9)" });
            },
            success: function (res) {
                $("body").LoadingOverlay("hide");
                if (res.success === true) {
                    let r = res.result;
                    $("#idCliente").val(r.idCliente);
                    $("#pedimento").val(r.pedimento);
                    $("#cvePedimento").val(r.cvePedimento);
                    $("#tipoOperacion").val(r.ie);
                }
            }
        });
    });

    $(document.body).on("click", "#save", function (ev) {
        ev.preventDefault();
        if ($("#form").valid()) {
            $("#form").ajaxSubmit({
                url: "/manifestacion/post/nueva", 
                cache: false, 
                dataType: "json", 
                type: "POST",
                beforeSend: function () {
                    $("body").LoadingOverlay("show", { color: "rgba(255, 255, 255, 0.9)" });
                },
                success: function (res) {
                    if (res.success === true) {
                        window.location.href = `/manifestacion/index/editar?id=${res.id}`;
                    } else {
                        $("body").LoadingOverlay("hide");
                    }
                }
            });
        }
    });

});