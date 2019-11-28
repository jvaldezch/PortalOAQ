/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
var tbl;
var row;
var rowIndex = 0;

function cancelar() {
    tbl.datagrid('cancelEdit', rowIndex);
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

function abrirNuevoTab(url) {
  var win = window.open(url, '_blank');
  win.focus();
}

function formatoSalida(id) {
    var ids = [];
    var rows = tbl.datagrid('getSelections');
    for (var i = 0; i < rows.length; i++) {
        ids.push(rows[i].id);
    }
    if (ids.length === 0 && id !== undefined && Number.isInteger(id)) {
        abrirNuevoTab('/bitacora/get/imprimir-formato-salida?id=' + id);
    } else if (ids.length > 0) {
        abrirNuevoTab('/bitacora/get/imprimir-formato-salida-multiple?id=' + ids.join(','));        
    } else {
        mensajeAlerta('Lo sentimos pero no se ha seleccionado ninguna referencia.');
    }
}

function mensajeAlerta(mensaje) {
    $.alert({title: "Alerta", type: "red", typeAnimated: true,useBootstrap: false, boxWidth: "250px",
        content: mensaje
    });
}

String.prototype.capitalize = function() {
    return this.charAt(0).toUpperCase() + this.slice(1);
};

$(document).ready(function () {
    
    tbl = $('#log').edatagrid();
    
    var arr = "#noPagadas";
    
    $(document.body).on("click", arr, function () {
        if ($(this).is(":checked")) {
            Cookies.set($(this).attr("id"), true);
        } else {
            Cookies.set($(this).attr("id"), false);
        }
        tbl.edatagrid('reload');
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

    tbl.edatagrid({method: 'get', idField: 'id', height: 645, singleSelect: false, remoteSort: true, remoteFilter: true, pagination: true, rownumbers: false,
        pageSize: 20,
        pageList: [20, 30, 40, 50],
        fitColumns: false,
        title: "Bitacora de pedimentos",
        url: '/bitacora/get/obtener-pagados',
        queryParams: {
            tipoAduana: 1
	},
        toolbar: [
            {text: 'Actualizar', iconCls: 'icon-reload', handler: function () {
                    tbl.edatagrid('reload');
                }},
            {text: 'Imprimir', iconCls: 'icon-print', handler: formatoSalida}
        ],
        frozenColumns: [[
                {field:'ck',title:'',checkbox:true},
                {field: 'imex', width: 30, checkbox: false, title: '', 
                    formatter: function(value, row) {
                        if (row.ie == "TOCE.IMP") {
                            return '<img src="/images/icons/impo.png">';
                        } else {
                            return '<img src="/images/icons/expo.png">';        
                        }
                    } 
                },
                {field: 'msg', width: 30, checkbox: false, title: '', 
                    formatter: function(value, row) {
                        return '<img src="/images/icons/message.png" class="mensajero" data-id="' + row.id +'">';
                    } 
                },
                {field: 'patente', width: 50, title: 'Patente'},
                {field: 'aduana', width: 50, title: 'Aduana'},
                {field: 'pedimento', width: 80, title: 'Pedimento'},
                {field: 'referencia', width: 100, title: 'Referencia', 
                    formatter: function(value, row) {
                        return '<a href="/trafico/index/editar-trafico?id=' + row.id + '">' + row.referencia + '</a>';
                    } 
                }
            ]],
        columns: [[
                {field: 'nombreCliente', width: 350, title: 'Nombre Cliente'},
                {field: 'blGuia', width: 150, title: 'BL/Guía', editor: {type: 'text'}},
                {field: 'fechaPago', width: 145, title: 'F. Pago', editor: {type: 'datetimebox'}, options: {required: false, validType: 'datetimebox'}},
                {field: 'fechaLiberacion', width: 145, title: 'F. Liberación', editor: {type: 'datetimebox'}, options: { required: false, validType:'datetime' }},
                {field: 'nombre', width: 120, title: 'Usuario', 
                    formatter: function(value, row) {
                        return value.capitalize();
                    }
                }
            ]],
        onBeginEdit: function (index, row) {
            row.editing = true;
        },
        onEndEdit: function (index, row) {
            row.editing = false;
            tbl.edatagrid('reload');
        },
        onBeforeEdit: function (index, row) {
            row.editing = true;
            tbl.datagrid('refreshRow', index);
        },
        onAfterEdit: function (index, row) {
            row.editing = false;
            tbl.edatagrid('reload');
        },
        onCancelEdit: function (index, row) {
            row.editing = false;
            tbl.edatagrid('reload');
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
                        case "print":
                            formatoSalida(row.id);
                            break;
                    }
                }
            });
        }
    });
    
    tbl.edatagrid('enableFilter', [{}]);
    
    var customToolbar = '<td><span class="l-btn-left l-btn-icon-left"><span class="l-btn-text">No pagadas</span><input type="checkbox" id="noPagadas" /></span></td>';
    
    $(".datagrid-toolbar").find("table > tbody > tr").append(customToolbar);
    
    $.each(['imex', 'msg', 'patente', 'aduana'], function (index, value) {
        $(".datagrid-editable-input[name='" + value + "']").hide();
    });
    
});
