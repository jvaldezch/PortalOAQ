/**
 * programmed by Jvaldez at gmail
 * 2015.may.14
 */



window.obtenerSellosAgente = function (idAgente) {
    return $.ajax({url: '/trafico/get/obtener-sellos-agente', type: "GET",
        data: {idAgente: idAgente},
        beforeSend: function () {
            $("#sellos-agente").html('');
        },
        success: function (res) {
            if (res.success === true) {
                $("#sellos-agente").html(res.html);
                return true;
            }
            return false;
        }
    });
};

$(document).ready(function () {
    
    $.validator.addMethod("regx", function (value, element, regexpr) {
        return regexpr.test(value);
    }, "RFC no es válido.");
    
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
            patente: {
                required: true,
                minlength: 4,
                maxlength: 4,
                digits: true
            },
            rfc: {
                required: true,
                minlength: 10,
                regx: /^[A-Z]{3,4}([0-9]{2})([0-9]{2})([0-9]{2})?[A-Z0-9]{3,4}/
            },
            nombre: "required"
        },
        messages: {
            patente: {
                required: "Campo necesario",
                minlength: "Patente debe ser de 4 digitos",
                maxlength: "Patente dede ser de 4 digitos",
                digits: "No debe contener letras"
            },
            rfc: "Campo necesario",
            nombre: "Campo necesario"
        }
    });
    
    $(document.body).on("click", "#update", function (ev) {
        ev.preventDefault();
        if ($("#form").valid()) {
            $("#form").ajaxSubmit({
                type: "post",
                url: "/trafico/post/editar-agente-aduanal",
                dataType: "json",
                success: function (res) {
                    if (res.success === true) {
                        $.toast({text: "<strong>Guardado</strong>", bgColor: "green", stack : 3, position : "bottom-right"});
                    }
                }
            });
        }
    });
    
    $(document.body).on("input", "#rfc, #nombre", function () {
        var input = $(this);
        var start = input[0].selectionStart;
        $(this).val(function (_, val) {
            return val.toUpperCase();
        });
        input[0].selectionStart = input[0].selectionEnd = start;
    });    
    
    $(document.body).on("click", "#uploadKey", function (ev) {
        ev.preventDefault();
        var idAgente = $("#idAgente").val();
        var patente = $("#patente").val();
        $.confirm({title: "Nuevo sello", escapeKey: "cerrar", type: "green", boxWidth: "750px", useBootstrap: false,
            buttons: {
                subir: {
                    text: "Subir sello",
                    btnClass: "btn-green",                    
                    action: function() {
                        if ($("#formUploadKey").valid()) {
                            $("#formUploadKey").ajaxSubmit({dataType: "json", type: "POST",
                                url: "/trafico/post/subir-sello-agente",
                                beforeSend: function() {
                                    
                                },
                                success: function (res) {
                                    if (res.success === true) {
                                        obtenerSellosAgente($("#idAgente").val());
                                        $.toast({text: "<strong>Guardado</strong>", bgColor: "green", stack : 3, position : "bottom-right"});
                                    } else {
                                        $.alert({title: "<strong>Error</strong>", closeIcon: true, backgroundDismiss: true, type: "red", escapeKey: "cerrar", boxWidth: "350px", useBootstrap: false, content: res.message});
                                    }
                                }
                            });
                            return false;
                        } else {
                            return false;
                        }
                    }                    
                },
                cerrar: {
                    btnClass: "btn-red",
                    action: function(){}
                }
            },
            content: function () {
                var self = this;
                return $.ajax({url: "/trafico/get/subir-sello-agente?idAgente=" + idAgente + "&patente=" + patente, dataType: "json", method: "GET"
                }).done(function (res) {
                    var html = "";
                    if(res.success === true) {
                        html = res.html;
                    }
                    self.setContent(html);
                }).fail(function () {
                    self.setContent("Something went wrong.");
                });
            }
        });
    });

    $.when(obtenerSellosAgente($("#idAgente").val())).done(function( res ) {
        if (res.success === true) {
            
        }
    });
    
    $(document.body).on("click", "input[name='active']", function () {
        var id = $(this).data('id');
        if($(this).is(":checked")) {
            
        } else {
            
        }
    }); 

});