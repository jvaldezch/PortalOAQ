/**
 * 
 * @type type
 */

function removeInvoice(invoice, id) {
    $.confirm({title: "Confirmar", escapeKey: "cerrar", boxWidth: "380px", useBootstrap: false, type: "red",
            buttons: {
                si: {
                    btnClass: "btn-blue",
                    action: function () {
                        $.ajax({url: "/vucem/data/remover-factura", dataType: 'json', type: "POST",
                            data: {factura: invoice, id: id}
                        }).done(function (msg) {
                            if (msg.success === true) {
                                window.location.reload();
                            }
                        });
                    }
                },
                no: {
                    btnClass: "btn-red",
                    action: function () {}
                }
            },
            content: '¿Está seguro que desea remover la factura?'
        });
}

function removeInvoiceId(id) {
    var resp = confirm("¿Está seguro que desea remover la factura?");
    if (resp === true) {
        $.ajax({
            type: "POST",
            url: "/vucem/data/remover-factura-id",
            data: {id: id},
            dataType: 'json'
        }).done(function (msg) {
            if (msg.success === true) {
                window.location.reload();
            }
        });
    }
}

function sendVucem(id) {
    $.ajax({
        url: "/vucem/ajax/enviar-factura-vucem",
        type: "post",
        cache: false,
        dataType: "json",
        data: {id: id},
        success: function (res) {
            if(res.success === false) {
                $("#error-message").html(res.message);
                $("#errors").show();
            } else if(res.success === true) {
                $("#row_" + res.id).hide();
            }
        }
    });
}

$(document).ready(function () {
//    cargarFacturas();
});

function setSubDivision(idfact) {
    $.ajax({
        url: "/vucem/data/set-invoice-subvision",
        type: "post",
        cache: false,
        dataType: "json",
        data: {idfact: idfact},
        success: function (data) {

        }
    });
}

function setRelFact(idfact) {
    $.ajax({
        url: "/vucem/data/set-invoice-relfact",
        type: "post",
        cache: false,
        dataType: "json",
        data: {idfact: idfact},
        success: function (data) {

        }
    });
}

function setSendFact(idfact) {
    $.ajax({
        url: "/vucem/data/set-invoice-sendfact",
        type: "post",
        cache: false,
        dataType: "json",
        data: {idfact: idfact},
        success: function (data) {

        }
    });
}

$(document).ready(function () {
    
    if (Cookies.get('numPedimento') !== undefined) {
        var numPedimento = Cookies.get('numPedimento');
        $("#numpedimento").val(numPedimento);
    }
    
    $("#numreferencia").keydown(function (e) {
        if (e.keyCode === 13) {
            if ($("#numreferencia").val() !== '') {
                $('[data-toggle="modal-slam"]').trigger('click');
            }
        }
    });

    $(document.body).on('click', '[data-toggle="modal-slam"]', function (e) {
        if ($("#numreferencia").val().length !== 7) {
            alert('Número de pedimento debe ser de 7 digitos');
            return false;
        }
        e.preventDefault();
        $.blockUI({
            centerY: 0,
            css: {top: '10px', left: '', right: '10px'},
            message: '<h4 style="padding: 10px 10px; background: none"><img src="/images/loader.gif" style="margin-right:5px" /> Por favor espere...</h4>',
            baseZ: 2000
        });
        if ($("#numreferencia").val() !== '') {
            var url = "/vucem/data/obtener-facturas-slam?referencia=" + $("#numreferencia").val();
            if (url.indexOf('#') === 0) {
                $(url).modal('open');
            } else {
                $.get(url, function (data) {
                    $('<div class="modal hide fade" style="width: 950px; margin-left: -475px">' + data + '</div>')
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
        } else {
            alert('Debe porporcionar un número de referencia.');
        }
    });
    
    $(document.body).on('keydown', '#numpedimento', function (e) {
        if (e.keyCode === 13) {
            if ($('#numpedimento').val() !== '') {
                $('[data-toggle="modal"]').trigger('click');
            }
        }
    });
    
    $(document.body).on('click', '[data-toggle="modal"]', function (e) {
        if ($("#numpedimento").val().length !== 7) {
            $.alert({title: "<strong>Error</strong>", closeIcon: true, backgroundDismiss: true, type: "red", escapeKey: "cerrar", boxWidth: "350px", useBootstrap: false, content: 'Número de pedimento debe ser de 7 digitos'});
            return false;
        }
        e.preventDefault();
        $.blockUI({
            centerY: 0,
            css: {top: '10px', left: '', right: '10px'},
            message: '<h4 style="padding: 10px 10px; background: none; border: 0; color: #444"><img src="/images/loader.gif" style="margin-right:5px" /> Por favor espere...</h4>',
            baseZ: 2000
        });
        if ($("#numpedimento").val() !== '') {
            Cookies.set('numPedimento', $("#numpedimento").val());
            var url = "/vucem/get/mostrar-facturas?sistema=sitawin" + '&pedimento=' + $("#numpedimento").val();
            if (url.indexOf('#') === 0) {
                $(url).modal('open');
            } else {
                $.get(url, function (data) {
                    $('<div class="modal hide fade" style="width: 950px; margin-left: -475px">' + data + '</div>')
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
        } else {
            $.alert({title: "<strong>Error</strong>", closeIcon: true, backgroundDismiss: true, type: "red", escapeKey: "cerrar", boxWidth: "350px", useBootstrap: false, content: 'Debe porporcionar un número de pedimento.'});
        }
    });

    $(document.body).on('click', '#finishCove', function (ev) {
        ev.preventDefault();
        $.ajax({
            url: "/vucem/ajax/existen-facturas",
            type: "post",
            cache: false,
            dataType: "json",
            success: function (res) {
                if (res.success === true) {
                    window.location.href = "/vucem/index/nueva-solicitud";
                } else {
                    $.alert({title: "<strong>Advertencia</strong>", closeIcon: true, backgroundDismiss: true, type: "red", escapeKey: "cerrar", boxWidth: "350px", useBootstrap: false, content: res.message});
                }
            }
        });
    });
    
    $(document.body).on('click', '#uploadTemplate', function (ev) {
        ev.preventDefault();
        $.confirm({title: "Plantilla COVEs", escapeKey: "cerrar", boxWidth: "380px", useBootstrap: false, type: "blue",
            buttons: {
                subir: {
                    btnClass: "btn-blue",
                    action: function () {
                        if ($('#uploadForm').valid()) {
                            $("#uploadForm").ajaxSubmit({url: "/vucem/post/subir-plantilla", dataType: "json", type: "POST",
                                success: function (res) {
                                    if (res.success === true) {
                                        document.location.href = "/vucem/index/nuevo-cove-facturas";
                                    } else {
                                        $.alert({title: "Error", type: "red", content: res.message, boxWidth: "450px", useBootstrap: false});
                                    }
                                }
                            });
                        } else {
                            return false;
                        }
                    }
                },
                cerrar: {
                    btnClass: "btn-red",
                    action: function () {}
                }
            },
            content: function () {
                var self = this;
                return $.ajax({url: "/vucem/get/subir-plantilla", dataType: "json", method: "GET"
                }).done(function (res) {
                    var html = "";
                    if (res.success === true) {
                        html = res.html;
                    }
                    self.setContent(html);
                }).fail(function () {
                    self.setContent("Something went wrong.");
                });
            }
        });
    });
    
    $(document.body).on('click', '#other', function (ev) {
        ev.preventDefault();
        $.confirm({title: 'Facturas de otros sistemas', escapeKey: 'cerrar', boxWidth: '780px', useBootstrap: false, type: 'blue',
            buttons: {
                seleccionar: {
                    btnClass: "btn-blue",
                    action: function () {                        
                        var ids = [];
                        var boxes = $('input[name=invoice]:checked');
                        if((boxes).size() > 0) {
                            $(boxes).each(function(){
                                ids.push($(this).val());
                            });
                        }
                        if (ids.length > 0) {
                            var invoices = ids.toString();
                            invoices = invoices.replace(/,/g, '|');
                            var sistema = $('input[name="system"]:checked').val();
                            var aduana = $('input[name="aduana"]:checked').val();
                            $.ajax({url: "/vucem/post/seleccionar-facturas", cache: false, dataType: "json", type: "POST",
                                data: {sistema: sistema, aduana: aduana, pedimento: $('#buscar').val(), facturas: invoices},
                                success: function (res) {
                                    if (res.success === true) {
                                        
                                    }
                                }
                            });
                        }
                    }
                },
                cerrar: {
                    btnClass: "btn-red",
                    action: function () {
                        $('#search').off("click");
                    }
                }
            },
            content: function () {
                var self = this;
                return $.ajax({url: "/vucem/get/otro-sistema", dataType: "json", method: "GET"
                }).done(function (res) {
                    var html = "";
                    if (res.success === true) {
                        html = res.html;
                    }
                    self.setContent(html);
                }).fail(function () {
                    self.setContent("Something went wrong.");
                });
            }
        });
    });

    $('#selectAllCove').click(function (ev) {
        $('input[name=coveInvoice]').each(function() {
            $(this).prop('checked', !$(this).prop('checked'));            
            setSendFact($(this).val());
        });
    });
    

    $('.viewInvoice').click(function (ev) {
        var uuid = $(this).data('id');
        $.confirm({title: 'FACTURA', escapeKey: 'cerrar', boxWidth: '980px', useBootstrap: false, type: 'blue',
            buttons: {
                cerrar: {
                    btnClass: "btn-red",
                    action: function () {
                    }
                }
            },
            content: function () {
                var self = this;
                return $.ajax({url: "/vucem/get/ver-factura", dataType: "json", method: "GET", data: {uuid: uuid}
                }).done(function (res) {
                    var html = "";
                    if (res.success === true) {
                        html = res.html;
                    }
                    self.setContent(html);
                }).fail(function () {
                    self.setContent("Something went wrong.");
                });
            }
        });
    });
});

