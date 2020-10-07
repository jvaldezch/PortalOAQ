/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

let vald = $("#formUpdateKey").validate({
    errorPlacement: function (error, element) {
        $(element)
                .closest("form")
                .find("#" + element.attr("id"))
                .after(error);
    },
    errorElement: "span",
    errorClass: "traffic-error",
    rules: {
        pwdws: {required: true}
    },
    messages: {
        pwdws: {required: " [Proporcionar contraseña de Servicios Web]"}
    }
});

$(document).ready(function () {
    
    $(document.body).on('click', '.download-key', function(ev) {
        ev.preventDefault();
        let id = $(this).data('id');
        window.open(`/trafico/get/descargar-sello-cliente?id=${id}`);
    });

    $(document.body).on('click', '#updateKey', function(ev) {
        ev.preventDefault();
        if ($("#formUpdateKey").valid()) {
            $("#formUpdateKey").ajaxSubmit({
                url: "/trafico/post/actualizar-sello", dataType: "json", type: "POST",
                success: function (res) {
                    if (res.success === true) {

                    } else {
                        alert(res.message);
                    }
                }
            });
        }
    });

    $(document.body).on('click', '#updateKeys', function(ev) {
        ev.preventDefault();        
        let ids = [];
        let boxes = $("input[class=archivo-sello]:checked");
        if ((boxes).size() === 0) {
            alert("No ha seleccionado ningun sello.");
        }
        if ((boxes).size() > 0) {
            
            $(boxes).each(function () {
                ids.push($(this).val());
            });
            
            $.ajax({url: "/trafico/post/actualizar-sellos", dataType: "json", type: "POST",
                data: {idCliente: $("#idCliente").val(), idSello: $("#idSello").val(), ids: ids},
                success: function (res) {
                }
            });
        }
    });

    $(document.body).on("click", ".select-sellos", function () {
        let checkboxes = $("input[class=archivo-sello]");
        if ($(this).is(':checked')) {
            checkboxes.prop('checked', true);
        } else {
            checkboxes.prop('checked', false);
        }
    });
    
});