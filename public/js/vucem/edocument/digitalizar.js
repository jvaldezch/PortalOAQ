function triggerSelect() {
    $("select").selectBoxIt({
        theme: "default",
        autoWidth: false,
        aggressiveChange: true
    });
}

function cargarMisEdocuments() {
    $.ajax({url: "/vucem/get/cargar-mis-edocuments", dataType: "json", type: "GET",
        success: function (res) {
            if (res.success === true) {
                $("#edocuments").html(res.html);
            }
        }
    });
}

$(document).ready(function () {

    triggerSelect();
    cargarMisEdocuments();

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
    
    $(document.body).on("click", "#upload", function (ev) {
        ev.preventDefault();
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
            $("#files").ajaxSubmit({url: "/vucem/post/subir-archivo", type: "POST", dataType: "json",
                success: function (res) {
                    if (res.success === true) {
                        $("#file").replaceWith($("#file").clone());
                        $("#tipo").val("");
                        $("#subTipo").val("");
                        cargarMisEdocuments();
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

    $(document.body).on("click", ".openFile", function (ev) {
        ev.preventDefault();        
        var id = $(this).data("id");
        window.open("/vucem/get/ver-archivo?id=" + id, "viewFile", "toolbar=0,location=0,menubar=0,height=550,width=880,scrollbars=yes");
    });
    
    $(document.body).on("click", "#enviar", function (ev) {
        ev.preventDefault();        
        $(this).prop("disabled", true)
                .addClass("disabled");
        var checkboxValues = [];
        $('input[class="edocs"]:checked').each(function() {
           checkboxValues.push(this.value);
        });
        if (checkboxValues.length > 0) {
            $.ajax({url: "/vucem/post/enviar-edocuments", type: "POST", dataType: "json", data: {ids: checkboxValues},
                success: function (res) {
                    if (res.success === true) {
                        cargarMisEdocuments();
                        $("#enviar").removeAttr("disabled")
                                .removeClass("disabled");
                        $.alert({title: "Terminado", content: "Todos los archivos fueron enviados correctamente.", boxWidth: "350px", useBootstrap: false, type: "green"});
                        cargarMisEdocuments();
                    }
                }
            });            
        } else {
            $("#enviar").removeAttr("disabled");
            $.alert({title: "Advertencia", content: "No se han seleccionado archivos para enviar a VUCEM.", boxWidth: "350px", useBootstrap: false, type: "red"});
        }
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
    
    $(document.body).on("click", ".delete", function () {
        var id = $(this).data("id");
        $.confirm({title: "Confirmar", type: "blue", escapeKey: "cerrar", boxWidth: "350px", useBootstrap: false,
            content: "¿Está seguro de que desea eliminar el archivo?",
            buttons: {
                si: {
                    btnClass: "btn-blue",
                    action: function () {
                        $.post("/vucem/post/borrar-edocument", {id: id})
                                .done(function (res) {
                                    if (res.success === true) {
                                        cargarMisEdocuments();
                                    } else {
                                        $.alert({title: "Error", content: res.message, boxWidth: "350px", useBootstrap: false, type: "red"});
                                    }
                                });
                    }
                },
                no: function () {}
            }
        });
    });
    
    $(document.body).on("input", "#referencia, #rfc, #nombre", function () {
        var input = $(this);
        var start = input[0].selectionStart;
        $(this).val(function (_, val) {
            return val.toUpperCase();
        });
        input[0].selectionStart = input[0].selectionEnd = start;
    });
    
    $(document.body).on("click", "#selectAll", function () {
        var checkboxes = $(".edocs");
        if ($(this).is(':checked')) {
            checkboxes.prop('checked', true);
        } else {
            checkboxes.prop('checked', false);
        }
    });
    
    $(document.body).on("click", ".process", function () {
        var id = $(this).data("id");
        $(this).attr("src", "/images/preloader.gif");
        $.ajax({url: "/vucem/post/procesar-edocument", type: "POST", data: {id: id}, dataType: "json",
            success: function (res) {
                if (res.success) {
                    cargarMisEdocuments();
                }
            }
        });
    });
    
    $(document.body).on("click", "#sendToVucem", function () {
        var edocs = [];
        var checkboxes = $(".edocs");
        if ((checkboxes).size() > 0) {
        } else {
        }
    });

});
