/**
 * programmed by Jvaldez at gmail
 * 2015.may.14
 */


$(document).ready(function () {

    $(document.body).on("change", "select[name^='aduana']", function () {
        $.ajax({
            url: "/trafico/data/obtain-my-ops",
            cache: false,
            type: "post",
            dataType: "json",
            data: {aduana: $("#aduana").val()}
        }).done(function (data) {
            if (data.success === true) {
                $("#divops").html(data.html);
            }
        });
    });
    
    $(document.body).on("change", "select[name^='cliente']", function () {
        $.ajax({
            url: "/trafico/post/rfc-sociedad",
            cache: false,
            type: "POST",
            dataType: "json",
            data: {idCliente: $("#cliente").val()},
            success: function(res) {
                if (res.plantas !== null) {
                    $("#divplanta").html(res.plantas);
                } else {
                    $("#divplanta").html('<select name="planta" id="planta" class="traffic-select-medium" tabindex="3" disabled="disabled"><option value="">---</option></select>');
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
            planta: {
                required: {depends: function(elm) {
                    return $(this).is(":not(:disabled)");
                }}
            },
            pedimento: {
                required: true,
                minlength: 7,
                maxlength: 7,
                regx: /^[0-9]{7}$/
            },
            referencia: "required"
        },
        messages: {
            cliente: "Campo necesario",
            aduana: "Campo necesario",
            operacion: "Campo necesario",
            planta: "Campo necesario",
            pedimento: {
                    required: "Campo necesario",
                    minlength: "Pedimento debe ser de 7 digitos",
                    maxlength: "Pedimento dede ser de 7 digitos",
                    digits: "No debe contener letras"
            },
            referencia: "Campo necesario"
        }
    });

    $("#add-request").click(function (e) {
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

    function reloadIframe(res, statusText, xhr, $form) {
        if (res.success === true) {
            document.getElementById("requests-frame").contentDocument.location.reload(true);
        } else {
            $.alert({
                title: "¡Advertencia!",
                content: res.message
            });
        }
    }
    
    $("#referencia").on("input", function (evt) {
        var input = $(this);
        var start = input[0].selectionStart;
        $(this).val(function (_, val) {
            return val.toUpperCase();
        });
        input[0].selectionStart = input[0].selectionEnd = start;
    });

});

function editRequest(id, aduana) {
    window.location.href = "/trafico/index/editar-solicitud?id=" + id + "&aduana=" + aduana;
}

function sendRequest(id) {
    $.ajax({
        url: "/trafico/post/enviar-solicitud",
        cache: false,
        type: "post",
        dataType: "json",
        data: {id: id},
        success: function (res) {
            if (res.success === true) {
                document.getElementById("requests-frame").contentDocument.location.reload(true);
            }
        }
    });
}

function deleteRequest(id) {
    var r = confirm("¿Está seguro que desea eleminar la solicitud");
    if (r === true) {
        $.ajax({
            url: "/trafico/data/delete-request",
            cache: false,
            type: "post",
            dataType: "json",
            data: {id: id},
            success: function (res) {
                if (res.success === true) {
                    document.getElementById("requests-frame").contentDocument.location.reload(true);
                }
            }
        });
    }
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

$("#referencia").on("input", function (evt) {
    var input = $(this);
    var start = input[0].selectionStart;
    $(this).val(function (_, val) {
        return val.toUpperCase();
    });
    input[0].selectionStart = input[0].selectionEnd = start;
});