
var loadMsg = '<span style="font-family: sans-serif; font-size: 12px">Por favor espere... <div class="traffic-icon traffic-loader"></div></span>';

function styleError(msg) {
    return "<span style=\"color:red; font-family: sans-serif; font-size: 11px\"> [ <strong>" + msg + "</strong> ] </span>";
}

function load(id, url) {
    $(id).show();
    $(id).html(loadMsg);
    $.ajax({
        url: url,
        type: "post",
        dataType: "json",
        timeout: 3000,
        success: function (res) {
            if (res.success === true) {
                $(id).html(res.html);

                $('#prefix-table').DataTable({
                    "lengthMenu": [[15, 25, 50, -1], [15, 25, 50, "All"]],
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

            }
        }
    });
}

$(document).ready(function () {

    load("#result", "/usuarios/ajax/obtener-prefijos");

    $("#form").validate({
        errorPlacement: function (error, element) {
            $(element)
                .closest("form")
                .find("label[for='" + element.attr("id") + "']")
                .append(error);
        },
        errorElement: "span",
        errorClass: "traffic-error",
        rules: {
            idDocumento: { required: true },
            prefijo: { required: true }
        },
        messages: {
            idDocumento: "Selec. documento",
            prefijo: "Prefijo es necesario"
        }
    });

    $("#submit").click(function (e) {
        e.preventDefault();
        if ($("#form").valid()) {
            $("#form").ajaxSubmit({
                success: function (res) {
                    if (res.success === true) {
                        load("#result", "/usuarios/ajax/obtener-prefijos");
                    } else {
                        $("#prefijoError").html(styleError(res.error));
                    }
                }
            });
        }
    });

    $("#prefijo").on('input', function (evt) {
        var input = $(this);
        var start = input[0].selectionStart;
        $(this).val(function (_, val) {
            return val.toUpperCase();
        });
        input[0].selectionStart = input[0].selectionEnd = start;
    });

});