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
            patente: { required: true },
            aduana: { required: true },
            tipoAduana: { required: true },
            corresponsal: { required: true },
            nombre: { required: true }
        },
        messages: {
            patente: { required: "Campo necesario" },
            aduana: { required: "Campo necesario" },
            tipoAduana:  { required: "Campo necesario" },
            corresponsal:  { required: "Campo necesario" },
            nombre:  { required: "Campo necesario" }
        }
    });
    
    $("#addAgent[title]").qtip();
    
    $(document.body).on("click", "#addAgent", function (ev) {
        ev.preventDefault();
        window.location.href = "/trafico/index/agregar-agente-aduanal";
    });
    
    $(document.body).on("click", "#submit", function (ev) {
        ev.preventDefault();
        if ($("#form").valid()) {
            $("#form").ajaxSubmit({
                url: "/trafico/post/nueva-oficina",
                type: "post",
                dataType: "json",
                success: function (res) {
                    if (res.success === true) {
                        window.location.replace("/trafico/index/oficinas");
                    } else {
                        $.toast({heading: '¡Error!', text: res.message, bgColor: "red", stack : 3, position : "bottom-left", icon: "error"});
                    }
                }
            });
        }
    });

    $(document).on("input", "#nombre", function() {
        var input = $(this);
        var start = input[0].selectionStart;
        $(this).val(function (_, val) {
            return val.toUpperCase();
        });
        input[0].selectionStart = input[0].selectionEnd = start;
    });
    
});
