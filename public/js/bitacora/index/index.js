/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

var tbl;
var row;
var rowIndex = 0;

function imprimir() {
    window.open('/bitacora/get/imprimir', '_blank');
}

/*function agregarNuevo() {
    tbl.edatagrid('addRow', {
        index: 0,
        row: {
            patente: 3589,
            aduana: 640
        }
    });
}

function previo() {
    row = tbl.datagrid('getSelected');
    if (row) {
        $.confirm({title: "Detalle de guía", escapeKey: "cerrar", boxWidth: "650px", useBootstrap: false, type: "blue",
            buttons: {
                cerrar: {btnClass: "btn-red", action: function () {}}
            },
            content: function () {
                var self = this;
                return $.ajax({url: "/bitacora/get/detalle-guia?idGuia=" + row.id, dataType: "json", method: "get"
                }).done(function (res) {
                    var html = "";
                    if(res.success === true) { html = res.html; }
                    self.setContent(html);
                }).fail(function () {
                    self.setContent("Something went wrong.");
                });
            }
        });
    }
}

function crearTrafico() {
    row = tbl.datagrid('getSelected');
}

function reclamar() {
    row = tbl.datagrid('getSelected');
}

function cancelar() {
    tbl.datagrid('cancelEdit', rowIndex);
}

function consecutivo() {
    row = tbl.datagrid('getSelected');
    if (row) {
        if (row.referencia == null) {
            if (row.patente == 3589 && (row.aduana == 640 || row.aduana == 240)) {
                $.ajax({url: "/bitacora/get/consecutivo", type: "GET", dataType: "json", cache: false,
                    data: {id: row.id, patente: row.patente, aduana: row.aduana, tipoOperacion: row.tipoOperacion},
                    success: function (res) {
                        if (res.success === true) {
                            tbl.edatagrid('reload');
                        } else {
                            $.messager.alert('Error', res.message);
                        }
                    }
                });
            }
        } else {
            $.messager.alert('Error', 'No se puede obtener consecutivo ya que el registro ya cuenta con ese dato.');
        }
    } else {
        $.messager.alert('Advertencia', 'Usted no ha seleccionado ningun registro.');
    }
    return true;
}

function borrar() {
    row = tbl.datagrid('getSelected');
    if (row) {
        $.messager.confirm('Confirmar', '¿Está seguro de que desea borrar el registro?', function (r) {
            if (r) {
                $.ajax({url: "/bitacora/post/borrar", type: "POST", dataType: "json", cache: false,
                    data: {id: row.id},
                    success: function (res) {
                        if (res.success === true) {
                            tbl.edatagrid('reload');
                        } else {
                            $.messager.alert('Error', res.message);
                        }
                    }
                });
            }
        });
    } else {
        $.messager.alert('Advertencia', 'Usted no ha seleccionado ningun registro.');
    }
    return true;
}

function agrupar() {
    var ids = [];
    var rows = tbl.datagrid('getSelections');
    for (var i = 0; i < rows.length; i++) {
        ids.push(rows[i].id);
    }
    if (ids.length > 1) {
        $.ajax({url: "/bitacora/post/agrupar", type: "POST", dataType: "json", cache: false,
            data: {ids: ids},
            success: function (res) {
                if (res.success === true) {
                    tbl.edatagrid('reload');
                } else {
                    $.messager.alert('Error', res.message);
                }
            }
        });
    } else {
        $.messager.alert('Advertencia', 'Usted no ha seleccionado al menos dos registros.');
    }
}*/

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

$(document).ready(function () {

    tbl = $('#log').edatagrid();

    tbl.edatagrid({method: 'get', idField: 'id', height: 535, singleSelect: false, remoteSort: true, remoteFilter: true, pagination: true, rownumbers: false,
        pageSize: 20,
        pageList: [20, 30, 40, 50],
        fitColumns: false,
        title: "Bitacora de pedimentos",
        url: '/bitacora/get/obtener',
        saveUrl: '/bitacora/post/agregar',
        updateUrl: '/bitacora/post/actualizar',
        destroyUrl: '/bitacora/post/borrar',
        rowStyler:function(index, row){
            if (row.estatus == 2){
                return 'background-color:#d8eee6;';
            }
        },
        toolbar: [
            //{text: 'Agregar', iconCls: 'icon-add', handler: agregarNuevo},
            {text: 'Actualizar', iconCls: 'icon-reload', handler: function () {
                    tbl.edatagrid('reload');
                }},
            //{text: 'Cancelar', iconCls: 'icon-undo', handler: cancelar},
            //{text: 'Guardar', iconCls: 'icon-save', handler: function () {
            //        tbl.edatagrid('saveRow');
            //    }},
            //{text: 'Borrar', iconCls: 'icon-remove', handler: borrar},
            {text: 'Imprimir', iconCls: 'icon-print', handler: imprimir}
        ],
        frozenColumns: [[
                {field: 'estatus', hidden: false,
                    formatter: function (value) {
                        return '';
                    }},
                {field: 'ck', checkbox: true, hidden: false},
                {field: 'patente', title: 'Patente', editor: {type: 'validatebox', options: {required: true}}},
                {field: 'aduana', title: 'Aduana', editor: {type: 'validatebox', options: {required: true}}},
                {field: 'referencia', title: 'Referencia', editor: {type: 'text'}},
                {field: 'pedimento', title: 'Pedimento', editor: {type: 'text'}},
                {field: 'idCliente', title: 'Cliente', width: 300, editor: {
                        type: 'combobox',
                        options: {valueField: 'id', textField: 'nombre', panelWidth: 350, panelHeight: 130, url: '/bitacora/get/clientes'}
                    }, formatter: function (value, row) {
                        return row.nombreCliente;
                    }}
            ]],
        columns: [[
                {field: 'tipoOperacion', title: 'Tipo Operación', width: 130,
                    editor: {
                            type: 'combobox',
                            options: {
                                valueField: 'value',
                                textField: 'label',
                                panelHeight: 150,
                                panelWidth: 250,
                                data: [{value: 'TOCE.IMP', label: 'Importación'}, 
                                {value: 'TOCE.EXP', label: 'Exportación'}]
                            }
                        }},
                {field: 'clavePedimento', title: 'Clave', width: 100, editor: {
                        type: 'combobox',
                        options: {valueField: 'clave', textField: 'clave', panelWidth: 100, panelHeight: 150, url: '/bitacora/get/claves'}
                    }},
                {field: 'blGuia', title: 'BL / Guía', width: 150, sortable: true, editor: {type: 'validatebox', options: {required: true}}},
                {field: 'observaciones', title: 'Observaciones', width: 250, editor: {type: 'text'}},
                {field: 'fechaNotificacion', title: 'F. Notificación', width: 130},
                {field: 'fechaRevalidacion', title: 'F. Revalidación', width: 130},
                {field: 'fechaPrevio', title: 'F. Previo', width: 130},
                {field: 'creado', title: 'Creado', width: 130},
                {field: 'creadoPor', title: 'Creado Por', width: 120},
                {field: 'actualizado', title: 'Actualizado', width: 130},
                {field: 'actualizadoPor', title: 'Actualizado Por', width: 120}
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
            //$('#mm').menu('show', {
            //    left: e.pageX,
            //    top: e.pageY
            //});
        }
    });
    
    tbl.edatagrid('enableFilter', [{}]);
    
    $.each(['patente', 'aduana', 'idCliente', 'clavePedimento', 'tipoOperacion', 'modelo', 'marca', 'numeroFotos', 'numeroPiezas', 'numeroSerie', 'numeroParte', 'actualizado', 'actualizadoPor', 'completa', 'averia', 'observaciones', 'nombreProveedor', 'paisOrigen', 'fechaPrevio', 'fechaRevalidacion', 'fechaNotificacion', 'creado', 'creadoPor'], function (index, value) {
        $(".datagrid-editable-input[name='" + value + "']").hide();
    });

});

