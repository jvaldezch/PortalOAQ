/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function editRequest(id, aduana) {
    window.location.href = "/trafico/index/editar-solicitud?id=" + id + "&aduana=" + aduana;
}

function sendRequest(id) {
    $.ajax({
        url: "/trafico/post/enviar-solicitud",
        cache: false,
        type: 'post',
        dataType: 'json',
        data: {id: id},
        success: function (res) {
            if (res.success === true) {
                document.getElementById("requests-frame").contentDocument.location.reload(true);
            }
        }
    });
}

function deleteRequest(id) {
    $.ajax({
        url: '/trafico/data/delete-request',
        cache: false,
        type: 'post',
        dataType: 'json',
        data: {id: id},
        success: function (res) {
            if (res.success === true) {
                document.getElementById("requests-frame").contentDocument.location.reload(true);
            }
        }
    });
}

function deletePreRequest(id) {
    $.ajax({
        url: '/trafico/data/delete-pre-request',
        cache: false,
        type: 'post',
        dataType: 'json',
        data: {id: id},
        success: function (res) {
            if (res.success === true) {
                document.getElementById("requests-frame").contentDocument.location.reload(true);
            }
        }
    });
}

function reloadIframe(data, statusText, xhr, $form) {
    if (data.success === true) {
        document.getElementById("requests-frame").contentDocument.location.reload(true);
    } else {
        $.alert({
            title: "¡Advertencia!",
            content: data.message
        });
    }
}

$(document).ready(function () {

    $(document.body).on("change", "select[name^='aduana']", function () {
        $.ajax({ url: '/trafico/get/clientes-corresponsal', cache: false, type: 'get', dataType: 'json',
            data: {idAduana: $("#aduana").val()},
            success: function(res) {
                $('#cliente').empty().append($("<option />").val('').text("---"));
                if (res.success === true) {
                    $.each(res.result, function(i, value) {
                        if (i !== '') {
                            $("#cliente").append($("<option />").val(i).text(value));
                        }
                    });
                }
            }
        });
    });

    $("#form-requests").validate({
        errorPlacement: function (error, element) {
            $(element)
                    .closest("form")
                    .find("#" + element.attr("id"))
                    .after(error);
        },
        errorElement: "span",
        errorClass: "traffic-error",
        rules: {
            cliente: "required",
            aduana: "required",
            operacion: "required",
            pedimento: {
                required: true,
                minlength: 7,
                maxlength: 7,
                digits: true
            },
            referencia: "required"
        },
        messages: {
            cliente: "Campo necesario",
            aduana: "Campo necesario",
            operacion: "Campo necesario",
            pedimento: {
                    required: "Campo necesario",
                    minlength: "Pedimento debe ser de 7 digitos",
                    maxlength: "Pedimento dede ser de 7 digitos",
                    digits: "No debe contener letras"
            },
            referencia: "Campo necesario"
        }
    });

    $(document.body).on("click","#add-request",function (e) {
        e.preventDefault();
        if ($("#form-requests").valid()) {
            $("#form-requests").ajaxSubmit({
                url: "/trafico/data/add-new-request",
                type: "post",
                dataType: "json",
                timeout: 3000,
                success: reloadIframe
            });
        }
    });

    $(document.body).on("input", "#referencia", function (evt) {
        var input = $(this);
        var start = input[0].selectionStart;
        $(this).val(function (_, val) {
            return val.toUpperCase();
        });
        input[0].selectionStart = input[0].selectionEnd = start;
    });

});


