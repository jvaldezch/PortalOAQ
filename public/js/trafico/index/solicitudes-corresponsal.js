/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
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
                $('#requests-table').DataTable({
                    "lengthMenu": [[25, 50, -1], [25, 50, "All"]],
                    "language": {
                        "decimal": "",
                        "emptyTable": "No data available in table",
                        "info": "Mostrando _START_ a _END_ de _TOTAL_ registros",
                        "infoEmpty": "Showing 0 to 0 of 0 entries",
                        "infoFiltered": "(filtered from _MAX_ total entries)",
                        "infoPostFix": "",
                        "thousands": ",",
                        "lengthMenu": "Mostrando _MENU_ registros",
                        "loadingRecords": "Cargando ...",
                        "processing": "Procesando ...",
                        "search": "Buscar:",
                        "zeroRecords": "No matching records found",
                        "paginate": {
                            "first": "Primero",
                            "last": "Último",
                            "next": "Sig.",
                            "previous": "Ant."
                        }
                    }
                });

            } else {
                $("#mis-solicitudes").append(`<tr><td colspan="7" style="text-align: center;"><em>${res.message}</em></td></tr>`);
            }
        }
    }).done(function (res) {
    });
}

function editRequest(id, aduana) {
    window.location.href = "/trafico/index/editar-solicitud?id=" + id + "&aduana=" + aduana;
}

function sendRequest(id) {
    $.ajax({
        url: "/trafico/post/enviar-solicitud",
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

function deleteRequest(id) {
    $.ajax({
        url: '/trafico/data/delete-request',
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

$(document).ready(function () {

    $(document.body).on("change", "select[name^='aduana']", function () {

        id_aduana = $("#aduana").val();
        Cookies.set("portalSolicitudAduana", id_aduana);
        allCustomsRequests(id_aduana);

        $.ajax({
            url: '/trafico/get/clientes-corresponsal', cache: false, type: 'get', dataType: 'json',
            data: { idAduana: id_aduana },
            success: function (res) {
                $('#cliente').empty().append($("<option />").val('').text("---"));
                if (res.success === true) {
                    $.each(res.result, function (i, value) {
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

    $(document.body).on("click", "#add-request", function (e) {
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

    $(document.body).on("input", "#referencia", function (evt) {
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


