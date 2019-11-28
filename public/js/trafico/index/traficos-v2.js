/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

var dg;

function formatMensajero(val, row) {
    return '<img src="/images/icons/message.png" class="mensajero" data-id="' + row.id + '">';
}

function misMensajes(ids) {
    $.ajax({url: '/trafico/get/alertas-de-mensajes', dataType: 'json', type: 'GET',
        data: {ids: ids},
        success: function (res) {
            if (res.success === true) {
                $.each(res.ids, function (index, value) {
                    $('.mensajero[data-id=' + value.idTrafico + ']').attr('src', '/images/icons/message-new.png');
                });
            }
        }
    });
}

function solicitudesEstatus(ids) {
    $.ajax({url: '/trafico/get/alertas-de-solicitudes', dataType: 'json', type: 'GET',
        data: {ids: ids},
        success: function (res) {
            if (res.success === true) {
                $.each(res.ids, function (index, value) {
                    $('.solicitudEstatus[data-id=' + value.idTrafico + ']').html(value.estatus);
                });
            }
        }
    });
}

function solicitudAnticipo(id) {
    var id = id;
    $.confirm({
        title: "Solicitud de anticipo", type: "green", content: '¿Está seguro de que desea crear una nueva solicitud?', escapeKey: "cerrar", boxWidth: "350px", useBootstrap: false,
        buttons: {
            si: {
                btnClass: "btn-green",
                action: function () {
                    $.ajax({url: '/trafico/ajax/solicitud-desde-trafico', cache: false, dataType: 'json', type: 'POST',
                        data: {idTrafico: id}
                    }).done(function (res) {
                        if (res.success === true) {
                            window.location.href = "/trafico/index/editar-solicitud-enh?id=" + res.id + "&aduana=" + res.aduana;
                        } else {
                            $.alert({title: "Error", type: "red", content: res.message, boxWidth: "250px", useBootstrap: false});
                        }
                    });
                }
            },
            no: function () {}
        }
    });
}

function formatCarrierNaviera(val, row) {
    if (row.carrierNaviera) {
        var naviera = '';
        $.ajax({url: "/trafico/crud/navieras?idNaviera=" + row.carrierNaviera, type: 'get', dataType: 'json', async: false,
            success: function (res) {
                naviera = res.nombre;
            }
        });
        return naviera;
    } else {
        return '';
    }
}

function formatLink(val, row) {
    return '<a href="/trafico/index/editar-trafico?id=' + row.id + '">' + row.referencia + '</a>';
}

function formatImpo(val, row) {
    if (row.ie == "TOCE.IMP") {
        return '<img src="/images/icons/impo.png">';
    } else {
        return '<img src="/images/icons/expo.png">';
    }
}

function newUser() {
    $('#dlg').dialog('open').dialog('center').dialog('setTitle', 'Nuevo trafico');
    $('#fm').form('clear');
}

function saveUser() {
    $('#fm').form('submit', {url: "/trafico/crud/trafico-nuevo",
        onSubmit: function () {
            return $(this).form('validate');
        },
        success: function (result) {
            var result = eval('(' + result + ')');
            if (result.errorMsg) {
                $.messager.show({
                    title: 'Error',
                    msg: result.errorMsg
                });
            } else {
                $('#dlg').dialog('close');        // close the dialog
                $('#dg').datagrid('reload');    // reload the user data
            }
        }
    });
}

function actualizarDesdeSitawin(id, patente, aduana, pedimento) {
    if (parseInt(patente) === 3589 && (parseInt(aduana) === 240 || parseInt(aduana) === 640 || parseInt(aduana) === 800)) {
        $.messager.confirm('Confirmar', '¿Está seguro de que desea actualizar el trafico?', function (r) {
            $.ajax({url: "/trafico/get/actualizar-desde-sistema", cache: false, dataType: "json", data: {id: id}, type: "GET",
                success: function (res) {
                    if (res.success === true) {
                        $('#dg').datagrid('reload');
                    }
                }
            });
        });
    }
}

function borrarTrafico(id) {
    console.log("Borrar ID: " + id);
}

$.fn.datebox.defaults.formatter = function (date) {
    var y = date.getFullYear();
    var m = date.getMonth() + 1;
    var d = date.getDate();
    return y + '-' + (m < 10 ? ('0' + m) : m) + '-' + (d < 10 ? ('0' + d) : d);
};
$.fn.datebox.defaults.parser = function (s) {
    if (!s)
        return new Date();
    var ss = s.split('-');
    var y = parseInt(ss[0], 10);
    var m = parseInt(ss[1], 10);
    var d = parseInt(ss[2], 10);
    if (!isNaN(y) && !isNaN(m) && !isNaN(d)) {
        return new Date(y, m - 1, d);
    } else {
        return new Date();
    }
};

$.extend($.fn.combobox.defaults, {
    loader: function (param, success, error) {
        var target = this;
        var opts = $(target).combobox('options');
        if (!opts.url)
            return false;
        $.ajax({type: opts.method, url: opts.url, data: param, dataType: 'json',
            success: function (data) {
                if ($(target).parent().length) {
                    success(data);
                }
            },
            error: function () {
                error.apply(this, arguments);
            }
        });
    }
});

function initGeneral() {
    
    var customToolbar = '<td><span class="l-btn-left l-btn-icon-left"><span class="l-btn-text">Todas las operaciones</span><input type="checkbox" id="allOperations" /></span></td>';
    customToolbar += '<td><span class="l-btn-left l-btn-icon-left"><span class="l-btn-text">Pagadas</span><input type="checkbox" id="pagadas" /></span></td>';
    customToolbar += '<td><span class="l-btn-left l-btn-icon-left"><span class="l-btn-text">Liberadas</span><input type="checkbox" id="liberadas" /></span></td>';
    customToolbar += '<td><span class="l-btn-left l-btn-icon-left"><span class="l-btn-text">Importaciones</span><input type="checkbox" id="impos" /></span></td>';
    customToolbar += '<td><span class="l-btn-left l-btn-icon-left"><span class="l-btn-text">Exportaciones</span><input type="checkbox" id="expos" /></span></td>';
    
    $(".datagrid-toolbar").find("table > tbody > tr").append(customToolbar);

    var arr = "#allOperations,#pagadas,#liberadas,#impos,#expos";

    $(document.body).on("click", arr, function () {
        if ($(this).is(":checked")) {
            Cookies.set($(this).attr("id"), true);
        } else {
            Cookies.set($(this).attr("id"), false);
        }
        dg.edatagrid('reload');
    });

    $(document.body).on('click', '#traficosLiberados', function () {
        var dateTime = new Date();
        dateTime = moment(dateTime).format("YYYY-MM-DD");
        window.open("/trafico/crud/traficos-liberados?fecha=" + dateTime + "&tipo=50", "viewFile", "toolbar=0,location=0,menubar=0,height=550,width=880,scrollbars=yes");
    });

    var array = arr.split(",");

    $.each(array, function (index, value) {
        var str = value.replace("#", "");
        if (Cookies.get(str) !== undefined) {
            if (Cookies.get(str) === "true") {
                $("#" + str).prop("checked", true);
            }
        }
    });

    $(".mensajero").each(function () {
        var id = $(this).data("id");
        $.ajax({url: "/trafico/get/mis-mensajes", cache: false, dataType: "json", data: {idTrafico: id}, type: "POST",
            success: function (res) {
                if (res.success === true) {
                    if (res.cantidad > 0) {
                        $(".mensajero[data-id=" + id + "]").attr("src", "/images/icons/message-new.png");
                    }
                }
            }
        });
    });

    $.each(['imex', 'msg', 'ie', 'cvePedimento', 'fechaPago', 'estatus', 'fechaEtd', 'fechaLiberacion', 'fechaEntrada', 'fechaPresentacion', 'fechaFacturacion', 'fechaEta', 'fechaRevalidacion', 'fechaPrevio', 'fechaDespacho', 'fechaEtaAlmacen', 'fechaEnvioProforma', 'fechaEnvioDocumentos', 'fechaNotificacion', 'fechaDeposito', 'fechaCitaDespacho', 'fechaProformaTercero', 'fechaArriboTransfer', 'fechaSolicitudTransfer', 'fechaVistoBueno', 'facturas', 'cantidadFacturas', 'cantidadPartes', 'almacen', 'fechaVistoBuenoTercero', 'fechaComprobacion', 'tipoCarga', 'fechaEir', 'fechaInstruccionEspecial', 'idPlanta', 'diasDespacho'], function (index, value) {
        $(".datagrid-editable-input[name='" + value + "']").hide();
    });

    $.each(['referencia', 'nombreCliente'], function (index, value) {
        $(document.body).on("input", ".datagrid-editable-input[name='" + value + "']", function () {
            var input = $(this);
            var start = input[0].selectionStart;
            $(this).val(function (_, val) {
                return val.toUpperCase();
            });
            input[0].selectionStart = input[0].selectionEnd = start;
        });
    });

}

function pad(n) {
    return (n < 10) ? ("0" + n) : n;
}

function formatearFecha(string) {
    var mDate = new Date(string);
    var month = mDate.getMonth() + 1;
    var day = mDate.getDate() + 1;
    if (mDate.getFullYear() == 1969) {
        return 'n/a';
    }
    return mDate.getFullYear() + "-" + pad(month) + "-" + pad(day);
}

function formatProgress(val, row) {
    var html = '<ul class="progress" data-id="' + row.id + '">';
    html += (row.fechaEta !== null) ? '<li class="fechaEta">' + formatearFecha(row.fechaEta) + '</li>' : '';
    html += (row.fechaNotificacion !== null) ? '<li class="fechaNotificacion">' + formatearFecha(row.fechaNotificacion) + '</li>' : '';
    html += (row.fechaEnvioDocumentos !== null) ? '<li class="fechaEnvioDocumentos">' + formatearFecha(row.fechaEnvioDocumentos) + '</li>' : '';
    html += (row.fechaEnvioProforma !== null) ? '<li class="fechaEnvioProforma">' + formatearFecha(row.fechaEnvioProforma) + '</li>' : '';
    html += (row.fechaVistoBueno !== null) ? '<li class="fechaVistoBueno">' + formatearFecha(row.fechaVistoBueno) + '</li>' : '';
    html += (row.fechaRevalidacion !== null) ? '<li class="fechaRevalidacion">' + formatearFecha(row.fechaRevalidacion) + '</li>' : '';
    html += (row.fechaPrevio !== null) ? '<li class="fechaPrevio">' + formatearFecha(row.fechaPrevio) + '</li>' : '';
    html += (row.fechaPago !== null) ? '<li class="fechaPago">' + formatearFecha(row.fechaPago) + '</li>' : '';
    html += (row.fechaLiberacion !== null) ? '<li class="fechaLiberacion">' + formatearFecha(row.fechaLiberacion) + '</li>' : '';
    html += (row.fechaFacturacion !== null) ? '<li class="fechaFacturacion">' + formatearFecha(row.fechaFacturacion) + '</li>' : '';
    html += '<li class="last" id="' + row.id + '" onclick="obtenerMenu(' + row.id + ');">+</li>';
    html += '</ul>';
    return html;
}

function obtenerMenu(id) {
    $.ajax({url: '/trafico/crud/get-menu?idTrafico=' + id}).success(function (content) {
        $("#contentPop").html(content);
    });
    $('li.last#' + id).popModal({
        html : $('#contentPop'),
        asMenu : true
    });
}

function mostrarCalendario(idTrafico, tipoFecha) {
    $.confirm({title: "", escapeKey: "cerrar", boxWidth: "300px", useBootstrap: false,
        buttons: {
            guardar: {
                btnClass: "btn-blue",
                action: function () {
                    var idTrafico = $('#idTrafico').val();
                    var tipoFecha = $('#tipoFecha').val();
                    var fecha = $('#selectedDate').val();
                    $.ajax({url: "/trafico/post/atualizar-fecha-trafico", cache: false, dataType: "json", data: {idTrafico: idTrafico, tipoFecha: tipoFecha, fecha: fecha}, type: "POST",
                        success: function (res) {
                            if (res.success === true) {
                                dg.edatagrid('reload');
                            }
                        }
                    });
                }
            },
            no: function () {}
        },
        content: function () {
            var self = this;
            return $.ajax({url: "/trafico/get/obtener-fecha", dataType: "json", method: "GET", data: {idTrafico: idTrafico, tipoFecha: tipoFecha}
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

$(document).ready(function () {

    dg = $('#dg').edatagrid();

    dg.edatagrid({
        pagination: true,
        singleSelect: true,
        striped: true,
        rownumbers: true,
        fitColumns: false,
        pageSize: 20,
        idField: 'id',
        url: '/trafico/crud/traficos',
        updateUrl: '/trafico/crud/trafico-actualizar',
        rowStyler: function (index, row) {
        },
        onClickRow: function (index, row) {
        },
        onBeginEdit: function (index, row) {
            var tc = dg.datagrid('getEditor', {
                index: index,
                field: 'tipoCarga'
            });
            if (tc !== undefined) {
                $(tc.target).combobox('reload', '/trafico/crud/tipo-carga?patente=' + row.patente + '&aduana=' + row.aduana);
            }
            var pl = $('#dg').datagrid('getEditor', {
                index: index,
                field: 'idPlanta'
            });
            if (pl !== undefined) {
                $(pl.target).combobox('reload', '/trafico/crud/plantas?idTrafico=' + row.id);
            }
        },
        onAdd: function (index, row) {
        },
        onBeforeEdit: function (index, row) {
        },
        onAfterEdit: function (index, row) {
        },
        onCancelEdit: function (index, row) {
            row.editing = false;
            $(this).datagrid('refreshRow', index);
        },
        onRowContextMenu: function (e, index, row) {
            e.preventDefault();
            $('#mm').menu('show', {
                left: e.pageX,
                top: e.pageY
            });
            $('#mm').menu({
                onClick: function (item) {
                    switch (item.name) {
                        case "actualizar":
                            actualizarDesdeSitawin(row.id, row.patente, row.aduana, row.pedimento);
                            break;
                        case "solicitud":
                            solicitudAnticipo(row.id);
                            break;
                        case "borrar":
                            borrarTrafico(row.id);
                            break;
                    }
                }
            });
        },
        onLoadSuccess:function(){
            var ids = [];
            var data = dg.datagrid('getRows');
            $.each(data, function( index, value ){
                ids.push(value.id);
            });
            if (ids.length > 0) {
                misMensajes(ids);
                solicitudesEstatus(ids);
            }
        },
        remoteFilter: true,
        toolbar: [{
                text: 'Guardar',
                iconCls: 'icon-save',
                handler: function () {
                    dg.edatagrid('saveRow');
                }
            }, {
                text: 'Cancelar',
                iconCls: 'icon-undo',
                handler: function () {
                    dg.edatagrid('cancelRow');
                }
            }, {
                text: 'Actualizar',
                iconCls: 'icon-reload',
                handler: function () {
                    dg.edatagrid('reload');
                }
            }],
        frozenColumns: [[
                {field: 'estatus', width: 20, title: '',
                    formatter: function (value, row) {
                        return '<div class="solicitudEstatus" data-id="' + row.id + '"><div class="semaphore-grey"></div></div>';
                    }
                },
                {field: 'imex', width: 30, checkbox: false, title: '', 
                    formatter: formatImpo
                },
                {field: 'msg', width: 30, checkbox: false, title: '', 
                    formatter: formatMensajero
                },
                {field: 'patente', width: 50, title: 'Patente'},
                {field: 'aduana', width: 50, title: 'Aduana'},
                {field: 'pedimento', width: 80, title: 'Pedimento'},
                {field: 'referencia', width: 100, title: 'Referencia', formatter: formatLink}
            ]],
        columns: [[
                {field: 'cvePedimento', width: 40, title: 'Cve.'},
                {field: 'nombreCliente', width: 300, title: 'Nombre Cliente'},
                {field: 'nombre', width: 120, title: 'Usuario'},
                {field: 'progress', width: 890, title: '',
                    formatter: formatProgress},
                {field: 'fechaInstruccionEspecial', width: 140, title: 'F. Instruciones Esp.', editor: {type: 'datetimebox'}, options: {required: false, validType: 'datetime'}},
                {field: 'fechaEtaAlmacen', width: 100, title: 'F. ETA Destino', editor: {type: 'datetimebox'}, options: {required: false, validType: 'datetime'}},
                {field: 'blGuia', width: 150, title: 'BL/Guía', editor: {type: 'text'}},
                {field: 'contenedorCaja', width: 150, title: 'Cont./Caja', editor: {type: 'text'}},
                {field: 'proveedores', width: 150, title: 'Proveedor(es)', editor: {type: 'text'}},
                {field: 'facturas', width: 150, title: 'Factura(s)', editor: {type: 'text'}},
                {field: 'cantidadFacturas', width: 150, title: 'Cant. Factura(s)', editor: {type: 'text'}},
                {field: 'cantidadPartes', width: 150, title: 'Cant. Parte(s)', editor: {type: 'text'}},
                {field: 'tipoCarga', width: 150, title: 'Tipo de Carga', editor: {
                        type: 'combobox',
                        options: {
                            valueField: 'id',
                            textField: 'tipoCarga',
                            panelWidth: 350,
                            panelHeight: 130
                        }
                    }, formatter(value, row) {
                        return row.carga;
                    }},
                {field: 'almacen', width: 150, title: 'Almacen', editor: {
                        type: 'combobox',
                        options: {
                            valueField: 'id',
                            textField: 'almacen',
                            panelWidth: 250,
                            panelHeight: 110
                        }
                    }},
                {field: 'idPlanta', width: 150, title: 'Planta',
                    formatter: function (val, row) {
                        return row.descripcionPlanta;
                    },
                    editor: {
                        type: 'combobox',
                        options: {
                            valueField: 'id',
                            textField: 'descripcion',
                            panelWidth: 250,
                            panelHeight: 90
                        }
                    }},
                {field: 'diasDespacho', width: 100, title: 'Días Despacho',
                    formatter(value, row) {
                        if (row.fechaLiberacion !== null) {
                            return value;
                        }
                    }}
            ]]
    });

    dg.edatagrid('enableFilter', []);

    initGeneral();

});