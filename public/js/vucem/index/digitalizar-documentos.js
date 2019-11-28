function triggerSelect() {
    
    $("select").selectBoxIt({
        theme: "default",
        autoWidth: false,
        aggressiveChange: true
    });
    
}

function borrarArchivo(uuid) {
    var r = confirm("¿Está seguro de borrar el archivo?");
    if (r === true) {
        $.ajax({
            type: "post",
            url: "/vucem/data/borrar-edocument",
            dataType: "json",
            data: {uuid: uuid},
            success: function (res) {
                if (res.success === true) {
                    document.getElementById("documents-frame").contentDocument.location.reload(true);
                }
            }
        });
    }
}

$(document).ready(function () {

    triggerSelect();

    $("#files").validate({
        errorPlacement: function (error, element) {
            $(element)
                    .closest("form")
                    .find("#" + element.attr("id"))
                    .after(error);
        },
        ignore:'',
        errorElement: "span",
        errorClass: "traffic-error",
        rules: {
            firmante: {required: true},
            file: {required: true},
            patente: {required: true},
            aduana: {required: true},
            referencia: {
                required: true,
                minlength: 7
            },
            pedimento: {
                required: true,
                minlength: 7,
                maxlength: 7,
                digits: true
            },
            tipo: {required: true},
            subTipo: {
                required: {
                    depends: function(element) {
                        if (!$("#subTipo").is(':disabled')) {
                            return true;
                        } else {
                            return false;
                        }
                    }
                }
            },
            rfc: {
                required: true,
                minlength: 12
            }
        },
        messages: {
            firmante: {required: "Seleccionar firmante"},
            file: {required: "Seleccionar un archivo"},
            patente: {required: "Seleccionar patente"},
            aduana: {required: "Seleccionar aduana"},
            referencia: {
                required: "Proporcionar referencia",
                minlength: "Campo debe contar con al menos 7 caracteres o números"
            },
            pedimento: {
                required: "Campo necesario",
                minlength: "Pedimento debe ser de 7 digitos",
                maxlength: "Pedimento dede ser de 7 digitos",
                digits: "No debe contener letras"
            },
            tipo: {required: "Seleccionar tipo de documento"},
            subTipo: {required: "Seleccionar subtipo de documento"},
            rfc: {
                required: "Especificar el RFC de consulta",
                minlength: "Campo debe contar con al menos 12 caracteres o números"
            }
        }
    });
    
    $(document.body).on("click", "#upload", function (e) {
        e.preventDefault();
        if ($("#files").valid()) {
            if ($("#tipo").val() === '') {
                alert("Seleccionar el tipo de documento.");
                return false;
            }
            var validator = {};
            if ($("#rfc").val() !== '' && ($("#rfc").val() === $("#firmante").val())) {
                var validator = $("#files").validate();
                validator.showErrors({
                    "rfc": "El RFC de consulta no debe ser igual que el RFC del firmante."
                });
                return false;
            }
            $("#files").ajaxSubmit({url: "/vucem/post/subir-archivo-digitalizar", type: "POST", dataType: "json",
                success: function (res) {
                    if (res.success === true) {
                        $("#file").replaceWith($("#file").clone());
                        document.getElementById("documents-frame").contentDocument.location.reload(true);
                    }
                }
            });
        }
    });

    $(document.body).on("change", "#firmante", function (e) {
        if ($("#firmante").val() !== '') {
            Current = $("#firmante").val();
            var url = "/vucem/data/obtener-aduanas-edocs?rfc=" + $("#firmante").val();
            if (url.indexOf('#') === 0) {
                $(url).modal('open');
            } else {
                $.get(url, function (data) {
                    $('<div class="modal hide fade" style="width: 450px; margin-left: -275px">' + data + '</div>')
                            .modal()
                            .on('hidden', function () {
                                $(this).remove();
                            });
                }).success(function () {
                    $('input:text:visible:first').focus();
                    $.unblockUI();
                    $(".blockUI").fadeOut("slow");
                });
            }
        }
    });

    $(document.body).one("click", "#enviar", function (e) {
        e.preventDefault();
        $(this).prop("disabled", true)
                .addClass("disabled");
        $.ajax({
            type: "post",
            url: "/vucem/post/revisar-para-enviar",
            dataType: "json",
            success: function (res) {
                if (res.success === true) {
                    $.ajax({url: "/vucem/post/background-edoc", type: "POST"
                    }).done(function () {
                        window.location.replace("/vucem/index/e-documents");
                    });
                }
            }
        });
    });
    
    $("#nombre").typeahead({
        source: function (query, process) {
            return $.ajax({url: "/trafico/get/clientes", type: "GET", data: {name: query}, dataType: "json",
                success: function (res) {
                    return process(res);
                }
            });
        }
    }).change(function () {
        $("#rfc").val("");
    });

    $(document.body).on("change", "#tipo", function () {
        var idDocumento = $(this).val();
        $.ajax({url: "/vucem/get/checar-subtipos", type: "GET", data: {idDocumento: idDocumento}, dataType: "json",
            success: function (res) {
                if (res.success === true) {
                    $.each(res.result, function (i, item) {
                        $('#subTipo').append($('<option>', {
                            value: item.value,
                            text: item.text
                        }));
                    });
                    $('#subTipo').removeAttr("disabled");
                    $("#subTipo").selectBoxIt("refresh");
                } else {
                    $('#subTipo').prop('disabled', true)
                            .html('<option value="">---</option>');
                    $("#subTipo").selectBoxIt("refresh");
                }
            }
        });
    });
    
    $(document.body).on("change", "#nombre", function () {
        $.ajax({url: "/trafico/get/rfc-de-cliente", type: "GET", data: {name: $("#nombre").val()}, dataType: "json",
            success: function (res) {
                if (res) {
                    $("#rfc").val(res[0]["rfc"]);
                }
            }
        });
    });
    
    $(document.body).on("input", "#referencia, #rfc", function () {
        var input = $(this);
        var start = input[0].selectionStart;
        $(this).val(function (_, val) {
            return val.toUpperCase();
        });
        input[0].selectionStart = input[0].selectionEnd = start;
    });

});
