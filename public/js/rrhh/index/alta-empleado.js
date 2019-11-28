/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$(document).ready(function () {
    
    $("#information").validate({
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
            apellido: "required",
            idEmpresa: "required"
        },
        messages: {
            nombre: "Campo necesario",
            apellido: "Campo necesario",
            idEmpresa: "Campo necesario"
        }
    });
    
    $(document.body).on("click", "#save", function (ev) {
        ev.preventDefault();
        if ($("#information").valid()) {
            $.post("/rrhh/post/alta-empleado", {nombre: $("#nombre").val(), apellido: $("#apellido").val(), idEmpresa: $("#idEmpresa").val()})
                    .done(function (res) {
                        if (res.success === true) {
                            window.location.href = "/rrhh/index/informacion-empleado?id=" +  res.id;
                        }
                    });            
        }
    });
    
    $(document.body).on("input", "#nombre, #apellido", function (evt) {
        var input = $(this);
        var start = input[0].selectionStart;
        $(this).val(function (_, val) {
            return val.toUpperCase();
        });
        input[0].selectionStart = input[0].selectionEnd = start;
    });
    
});
