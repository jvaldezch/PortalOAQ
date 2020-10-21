/**
 * programmed by Jvaldez at gmail
 * 2015.may.14
 */

let id_aduana;
let id_cliente;

window.allCustomsRequests = function (idAduana) {
    $.ajax({
        url: "/trafico/post/solicitudes",
        cache: false,
        type: "post",
        dataType: "json",
        data: { idAduana: idAduana },
        beforeSend: function () {
            $('body').LoadingOverlay('show', { color: 'rgba(255, 255, 255, 0.9)' });
        },
        success: function (res) {
            $('body').LoadingOverlay('hide');
            $("#mis-solicitudes").html('');
            if (res.success === true) {
                for (var i = 0; i < res.results.length; i++) {
                    var row = res.results[i];
                    let html = '<tr>';
                    if (row.detalle && row.conceptos) {
                        html += `<td style="text-align: center; color: #2f3b58"><i title="Enviar solicitud" style="cursor: pointer; font-size: 1.4em" class="fas fa-inbox" onclick="sendRequest(${row.id},${row.idAduana});"></i></td>`;
                    } else {
                        html += '<td>&nbsp;</td>';
                    }
                    html += `<td>${row.nombreCliente}</td>`;
                    html += `<td style="text-align: center">${row.aduana}-${row.patente}-${row.pedimento}</td>`;
                    html += `<td style="text-align: center">${row.referencia}</td>`;
                    html += `<td style="text-align: center">${row.complemento == null ? '' : 'S'}</td>`;
                    html += `<td style="text-align: center">${moment(row.creado).format('MM/DD/YYYY hh:mm a')}</td>`;
                    html += `<td style="text-align: center; color: #2f3b58; padding: 3px !important">`;
                    html += `<i class="fas fa-pencil-alt" onclick="editRequest(${row.id},${row.idAduana});" style="cursor: pointer; font-size: 1.4em; margin-right: 5px"></i>`;
                    html += `<i class="far fa-trash-alt" onclick="deletePreRequest(${row.id});" style="cursor: pointer; font-size: 1.4em"></i>`;
                    html += `</td>`;
                    html += '</tr>';
                    $("#mis-solicitudes").append(html);
                }

            } else {
                $("#mis-solicitudes").append(`<tr><td colspan="7" style="text-align: center;"><em>${res.message}</em></td></tr>`);
            }
        }
    }).done(function (res) {
    });
}

$(document).ready(function () {

    $(document.body).on("change", "select[name^='aduana']", function () {
        id_aduana = $("#aduana").val();
        Cookies.set("portalSolicitudAduana", id_aduana);
        allCustomsRequests(id_aduana);

        $.ajax({
            url: "/trafico/data/obtain-my-ops",
            cache: false,
            type: "post",
            dataType: "json",
            data: { aduana: id_aduana }
        }).done(function (data) {
            if (data.success === true) {
                $("#divops").html(data.html);
            }
        });

    });

    $(document.body).on("change", "select[name^='cliente']", function () {
        id_cliente = $("#cliente").val();
        Cookies.set("portalSolicitudCliente", id_cliente);
        $.ajax({
            url: "/trafico/post/rfc-sociedad",
            cache: false,
            type: "POST",
            dataType: "json",
            data: { idCliente: id_cliente },
            success: function (res) {
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
                required: {
                    depends: function (elm) {
                        return $(this).is(":not(:disabled)");
                    }
                }
            },
            pedimento: {
                required: true,
                minlength: 7,
                maxlength: 7
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
                success: reloadData
            });
        }
    });

    function reloadData(res, statusText, xhr, $form) {
        if (res.success === true) {
            allCustomsRequests(id_aduana);
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

    id_aduana = Cookies.get('portalSolicitudAduana');
    if (id_aduana) {
        $("select[name^='aduana']").val(id_aduana);
        allCustomsRequests(id_aduana);
    }

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
        data: { id: id },
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
            data: { id: id },
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
        data: { id: id },
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