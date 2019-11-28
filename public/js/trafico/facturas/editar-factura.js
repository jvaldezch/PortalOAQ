/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function number_format(number, decimals, dec_point, thousands_sep) {
    number = (number + '').replace(/[^0-9+-Ee.]/g, '');
    var n = !isFinite(+number) ? 0 : +number,
        prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
        sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
        dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
        s = '',
        toFixedFix = function (n, prec) {
            var k = Math.pow(10, prec);
            return '' + Math.round(n * k) / k;
        };
    // Fix for IE parseFloat(0.55).toFixed(0) = 0;
    s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
    if (s[0].length > 3) {
        s[0] = s[0].replace(/B(?=(?:d{3})+(?!d))/g, sep);
    }
    if ((s[1] || '').length < prec) {
        s[1] = s[1] || '';
        s[1] += new Array(prec - s[1].length + 1).join('0');
    }
    return s.join(dec);
}

function editarProveedorModal(titulo, idTrafico, idFactura, idProv) {
    $.confirm({title: titulo, escapeKey: "cerrar", boxWidth: "550px", useBootstrap: false, type: "blue",
        buttons: {
            guardar: {btnClass: "btn-blue", action: function () {
                    if ($("#frmProvider").valid()) {
                        $("#frmProvider").ajaxSubmit({url: "/trafico/facturas/guardar-proveedor", dataType: "json", type: "POST",
                            success: function (res) {
                                if (res.success === true) {
                                    location.replace("/trafico/facturas/editar-factura?idFactura=" + idFactura);
                                }
                            }
                        });
                    } else {
                        return false;
                    }
                }},
            cerrar: {action: function () {}}
        },
        content: function () {
            var self = this;
            return $.ajax({url: "/trafico/facturas/editar-proveedor?idTrafico=" + idTrafico + "&idFactura=" + idFactura + "&idProv=" + idProv, dataType: "json", method: "get"
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
}

function editarProductoModal(titulo, idTrafico, idCliente, idFactura, idProveedor, idProducto) {
    $.confirm({title: titulo, escapeKey: "cerrar", boxWidth: "630px", useBootstrap: false, type: "green",
        buttons: {
            guardar: {btnClass: "btn-green", action: function () {
                    if ($("#frmProduct").valid()) {
                        $("#frmProduct").ajaxSubmit({url: "/trafico/facturas/guardar-producto", dataType: "json", type: "POST",
                            success: function (res) {
                                if (res.success === true) {
                                    productos();
                                } else {
                                    $.alert({title: "Error", type: "red", content: res.message, boxWidth: "350px", useBootstrap: false});
                                    return false;
                                }
                            }
                        });
                    } else {
                        return false;
                    }
                }},
            cerrar: {action: function () {}}
        },
        content: function () {
            var self = this;
            return $.ajax({url: "/trafico/facturas/editar-producto?idTrafico=" + idTrafico + "&idCliente=" + idCliente + "&idFactura=" + idFactura + "&idProveedor=" + idProveedor + "&idProducto=" + idProducto, dataType: "json", method: "get"
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
}

window.detalle = function () {
    return $.ajax({url: '/trafico/facturas/detalle',
        data: {idFactura: $('#idFactura').val()},
        success: function (res) {
            if (res.success === true) {
                var row = res.result;
                var valorFacturaMonExt = number_format(row.valorFacturaMonExt, 2, '.', ',');
                var valorFacturaUsd = number_format(row.valorFacturaUsd, 2, '.', ',');
                var fletes = number_format(row.fletes, 2, '.', ',');
                var seguros = number_format(row.seguros, 2, '.', ',');
                var embalajes = number_format(row.embalajes, 2, '.', ',');
                var otros = number_format(row.otros, 2, '.', ',');
                $('#incoterm').val(row.incoterm);
                $('#numExportador').val(row.numExportador);
                $('#valorFacturaMonExt').val(valorFacturaMonExt);
                $('#valorFacturaUsd').val(valorFacturaUsd);
                $('#fletes').val(fletes);
                $('#seguros').val(seguros);
                $('#embalajes').val(embalajes);
                $('#otros').val(otros);
                if (row.factorMonExt) {
                    $('#factorMonExt').val(row.factorMonExt);
                } else {
                    $('#factorMonExt').val(1.00000);
                }
                $('#divisa').val(row.divisa);
                $('#paisFactura').val(row.paisFactura);
                $('#subdivision').val(row.subdivision);
                $('#certificadoOrigen').val(row.certificadoOrigen);
                $('#relFacturas').val(row.relFacturas);
                $('#observaciones').val(row.observaciones);
                $('#fechaFactura').val(row.fechaFactura);
            }
        }
    });
};

window.proveedores = function () {
    return $.ajax({url: '/trafico/facturas/proveedores?idFactura=' + $('#idFactura').val(),
        success: function (res) {
            if (res.success === true) {
                localStorage.setItem("proveedoresFactura", JSON.stringify(res.result));
                for (var i = 0; i < res.result.length; i++) {
                    var row = res.result[i];
                    $('#idProv').append($("<option />").val(row.id).text(row.text));
                }
            }
        }
    });
};

window.proveedor = function () {
    return $.ajax({url: '/trafico/facturas/proveedor',
        data: {idFactura: $('#idFactura').val()},
        success: function (res) {
            if (res.success === true) {
                localStorage.setItem("proveedorFactura", JSON.stringify(res.result));
                var row = res.result;
                $('#idProv').val(row.id);
                $('#tipoIdentificador').val(row.tipoIdentificador);
                if (row.tipoIdentificador === null || row.tipoIdentificador === "") {
                    $("#tipoIdentificador").after('<span style="float: left; color: red; font-size: 9px; margin-left: 5px">Favor de editar proveedor para corregir tipo de identificador.</span>');
                }
                $('#identificador').val(row.identificador);
                $('#calle').val(row.calle);
                $('#numExt').val(row.numExt);
                $('#numInt').val(row.numInt);
                $('#colonia').val(row.colonia);
                $('#localidad').val(row.localidad);
                $('#municipio').val(row.municipio);
                $('#estado').val(row.estado);
                $('#codigoPostal').val(row.codigoPostal);
                $('#pais').val(row.pais);
            }
        }
    });
};

window.fillIncoterms = function () {
    var incoterms = JSON.parse(localStorage.getItem("incoterms"));
    for (var i = 0; i < incoterms.length; i++) {
        var row = incoterms[i];
        $('#incoterm').append($("<option />").val(row.clave).text(row.clave));
    }
};

window.incoterms = function () {
    if (localStorage.getItem("incoterms") === null) {
        return $.ajax({url: '/trafico/facturas/incoterms',
            success: function (res) {
                if (res.success === true) {
                    localStorage.setItem("incoterms", JSON.stringify(res.result));
                    fillIncoterms();
                    return true;
                }
            }
        }); 
    } else {
        var incoterms = JSON.parse(localStorage.getItem("incoterms"));
        if (incoterms.length === 0) {
            return $.ajax({url: '/trafico/facturas/incoterms',
                success: function (res) {
                    if (res.success === true) {
                        localStorage.setItem("incoterms", JSON.stringify(res.result));
                        fillIncoterms();
                        return true;
                    }
                }
            });
        }
        fillIncoterms();
    }
};

window.fillPaises = function (id) {
    var paises = JSON.parse(localStorage.getItem("paises"));
    for (var i = 0; i < paises.length; i++) {
        var row = paises[i];
        $(id).append($("<option />").val(row.cve_pais).text(row.cve_pais + ' - ' + row.nombre));
    }
};

window.paises = function (id) {
    if (localStorage.getItem("paises") === null) {
        return $.ajax({url: '/trafico/facturas/paises',
            success: function (res) {
                if (res.success === true) {
                    localStorage.setItem("paises", JSON.stringify(res.result));
                    fillPaises(id);
                    return true;
                }
            }
        });
    } else {
        var paises = JSON.parse(localStorage.getItem("paises"));
        if (paises.length === 0) {
            return $.ajax({url: '/trafico/facturas/paises',
                success: function (res) {
                    if (res.success === true) {
                        localStorage.setItem("paises", JSON.stringify(res.result));
                        fillPaises(id);
                        return true;
                    }
                }
            });
        }
        fillPaises(id);
    }
};

window.fillMonedas = function () {
    var monedas = JSON.parse(localStorage.getItem("monedas"));
    for (var i = 0; i < monedas.length; i++) {
        var row = monedas[i];
        $('#divisa').append($("<option />").val(row.codigo).text(row.codigo + ' - ' + row.moneda));
    }
};

window.monedas = function () {
    if (localStorage.getItem("monedas") === null) {
            return $.ajax({url: '/trafico/facturas/monedas',
                success: function (res) {
                    if (res.success === true) {
                        localStorage.setItem("monedas", JSON.stringify(res.result));
                        fillMonedas();
                        return true;
                    }
                }
            });
    } else {
        var divisas = JSON.parse(localStorage.getItem("monedas"));
        if (divisas.length === 0) {
            return $.ajax({url: '/trafico/facturas/monedas',
                success: function (res) {
                    if (res.success === true) {
                        localStorage.setItem("monedas", JSON.stringify(res.result));
                        fillMonedas();
                        return true;
                    }
                }
            });            
        }
        fillMonedas();
    }
};

window.guardar = function () {
    if ($("#formInvoice").valid()) {
        $("#formInvoice").ajaxSubmit({url: "/trafico/facturas/guardar", dataType: "json", timeout: 3000, type: "POST",
            success: function (res) {
                if (res.success === true) {
                    $.toast({text: "<strong>Guardado</strong>", bgColor: "green", stack : 3, position : "bottom-right"});
                } else {
                    $.alert({title: "Error", type: "red", content: res.message, boxWidth: "250px", useBootstrap: false});
                }
            }
        });
    }
};

window.nuevoProveedor = function () {
    editarProveedorModal("Nuevo proveedor", $('#idTrafico').val(), $('#idFactura').val());
};

window.editarProveedor = function (idProv) {
    var idProv = $('#idProv').val();
    editarProveedorModal("Editar proveedor", $('#idTrafico').val(), $('#idFactura').val(), idProv);
};

window.editarProducto = function (idProducto) {
    if ($('#idProv').val() !== '') {
        editarProductoModal("Editar producto", $('#idTrafico').val(), $('#idCliente').val(), $('#idFactura').val(), $('#idProv').val(), idProducto);
    } else {
        $.alert({title: "Error", type: "red", content: "No se ha seleccionado el proveedor de la factura.", boxWidth: "250px", useBootstrap: false});
    }
};

window.nuevoProducto = function () {
    if ($('#idProv').val() !== '') {
        editarProductoModal("Nuevo producto", $('#idTrafico').val(), $('#idCliente').val(), $('#idFactura').val(), $('#idProv').val());
    } else {
        $.alert({title: "Error", type: "red", content: "No se ha seleccionado el proveedor de la factura.", boxWidth: "250px", useBootstrap: false});
    }
};

window.cancelar = function (id) {
};


window.borrarProducto = function (id) {
    $('.divTableRow#' + id).hide();
    return $.ajax({url: '/trafico/facturas/borrar-producto', dataType: "json", timeout: 10000, type: "POST",
        data: {id: id},
        success: function (res) {
            if (res.success === true) {
                productos();
            }
        }
    });
};

window.productos = function () {
    return $.ajax({url: '/trafico/facturas/productos',
        data: {idFactura: $('#idFactura').val()},
        success: function (res) {
            if (res.success === true) {
                localStorage.setItem("productosFactura", JSON.stringify(res.result));
                $('.divTableBody').html('');
                for (var i = 0; i < res.result.length; i++) {
                    var row = res.result[i];
                    var html = '<div class="divTableRow" id="' + row.id + '">';
                    html += '<div class="divTableCell">' + ((row.fraccion !== null) ? row.fraccion : '') + '</div>';
                    html += '<div class="divTableCell">' + ((row.numParte !== null) ? row.numParte : '') + '</div>';
                    html += '<div class="divTableCell" style="text-align: left">' + row.descripcion;
                    if (row.marca || row.modelo || row.subModelo || row.numSerie) {
                        html += '<br><span>';
                        if (row.marca) {
                            html += '<strong>Marca:</strong> ' + row.marca + " ";
                        }
                        if (row.modelo) {
                            html += '<strong>Modelo:</strong> ' + row.modelo + " ";
                        }
                        if (row.subModelo) {
                            html += '<strong>SubModelo:</strong> ' + row.subModelo + " ";
                        }
                        if (row.numSerie) {
                            html += '<strong>Num. serie:</strong> ' + row.numSerie + " ";
                        }
                        html += '</span>';
                    }
                    html += '</div>';
                    html += '<div class="divTableCell" style="text-align: right">' + ((row.precioUnitario !== null) ? number_format(row.precioUnitario, 6, '.', ',') : '') + '</div>';
                    html += '<div class="divTableCell" style="text-align: right">' + ((row.cantidadFactura !== null) ? number_format(row.cantidadFactura, 3, '.', ',') : '') + '</div>';
                    html += '<div class="divTableCell" style="text-align: right">' + ((row.valorComercial !== null) ? number_format(row.valorComercial, 3, '.', ',') : '') + '</div>';
                    html += '<div class="divTableCell">' + ((row.umc !== null) ? row.umc : '') + '</div>';
                    html += '<div class="divTableCell" style="text-align: right">' + ((row.cantidadOma !== null) ? number_format(row.cantidadOma, 3, '.', ',') : '') + '</div>';
                    html += '<div class="divTableCell">' + ((row.oma !== null) ? row.oma : '') + '</div>';
                    html += '<div class="divTableCell" style="text-align: right">';
                    if (!$("#edit").val()) {
                        html += '<div class="traffic-icon traffic-icon-edit" onclick="editarProducto(' + row.id + ');"></div>';
                        html += '<div class="traffic-icon traffic-icon-delete" onclick="borrarProducto(' + row.id + ');"></div>';
                    }
                    html += '</div>';
                    $('.divTableBody').append(html);
                }
                return true;
            }
        }
    });
};


$(document).ready(function () {
    
    $("#formInvoice").validate({
        errorPlacement: function (error, element) {
            $(element)
                    .closest("form")
                    .find("label[for='" + element.attr("id") + "']")
                    .append(error);
        },
        errorElement: "span",
        errorClass: "errorlabel",
        rules: {
        },
        messages: {
        }
    });

    $.when(paises('#pais'), monedas(), incoterms()).done(function (pai, mon, inc) {
        detalle();
        productos();
        $.when(proveedores()).done(function (res) {
                $.when(proveedor()).done(function (rs) {
                    $.LoadingOverlay("hide", true);
                });            
        });
    });
    
    $("#fechaFactura").datepicker({
        calendarWeeks: true,
        autoclose: true,
        language: "es",
        format: "yyyy-mm-dd",
        trigger: '#changeDate'
    });
    
    $("#changeDate").click(function () {
        $("#fechaFactura").datepicker('show');
    });
    
    $(document.body).on('change', '#idProv', function(ev) {
        ev.preventDefault();
        var idProv = $(this).val();
        if (idProv !== '') {
            $.ajax({url: '/trafico/facturas/cambiar-proveedor', dataType: "json", timeout: 10000, type: "POST",
                data: {idFactura: $('#idFactura').val(), idProv: idProv},
                success: function (res) {
                    if (res.success === true) {
                        location.replace('/trafico/facturas/editar-factura?idFactura=' + $('#idFactura').val());
                    }
                }
            });
        }
    });
    
    if ($("#edit").val()) {
        $("#formInvoice :input").prop("disabled", true);
    }
    
    $(document).on("input", "#observaciones, #identificador, #calle, #numExt, #numInt, #colonia, #localidad, #municipio, #estado", function() {
        var input = $(this);
        var start = input[0].selectionStart;
        $(this).val(function (_, val) {
            return val.toUpperCase();
        });
        input[0].selectionStart = input[0].selectionEnd = start;
    });

});

function importarFactura() {
    $.LoadingOverlay("show", {color: "rgba(255, 255, 255, 0.9)"});
    $.ajax({url: '/trafico/facturas/importar-factura',
        data: {idFactura: $('#idFactura').val()},
        success: function (res) {
            if (res.success === true) {
                location.replace('/trafico/facturas/editar-factura?idFactura=' + $('#idFactura').val());
            }
        }
    });
}


