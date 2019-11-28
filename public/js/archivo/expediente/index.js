/**
 * 
 * @param {type} param
 */
var editingId;

function borrar(id) {
    console.log(id);
    $.messager.confirm('Confirmar', '¿Está seguro de que desea borrar el archivo seleccionado?', function (r) {
        if (r) {
            $('#files').treegrid('remove', id);
            /*$.post('/operaciones/catalogo/proveedor-borrar', {id: id}, function (res) {
             if (res.success === true) {
             $('#providers').edatagrid('reload');
             $('#parts').datagrid('load', '/operaciones/catalogo/productos?idCliente=' + $("#idCliente").val());
             } else {
             $.messager.show({
             title: 'Error',
             msg: res.errorMsg
             });
             }
             }, 'json');*/
        }
    });
}

function guardar() {
    if (editingId !== undefined) {
        $('#files').treegrid('endEdit', editingId);
        var row = $('#files').treegrid('getSelected');
        if (row) {
            console.log(row.tipoArchivo);
        }
        $('#files').treegrid('update', {
            id: editingId,
            row: {editor: '<a onclick="javascript:borrar(' + editingId + ');"><img src="/images/icons/basura.png" /></a>'}
        });
        editingId = undefined;
    }
}

function editar() {
    if (editingId !== undefined) {
        $('#files').treegrid('select', editingId);
        return;
    }
    var row = $('#files').treegrid('getSelected');
    if (row) {
        editingId = row.id;
        $('#files').treegrid('update', {
            id: editingId,
            row: {editor: '<a onclick="javascript:guardar();" style="margin-right: 5px"><img src="/images/icons/guardar.png" /></a><a onclick="javascript:cancelar();"><img src="/images/icons/cancelar.png" /></a>'}
        });
        $('#files').treegrid('beginEdit', editingId);
    }
}

function cancelar() {
    if (editingId !== undefined) {
        $('#files').treegrid('cancelEdit', editingId);
        $('#files').treegrid('update', {
            id: editingId,
            row: {editor: '<a onclick="javascript:borrar(' + editingId + ');"><img src="/images/icons/basura.png" /></a>'}
        });
        editingId = undefined;
    }
}

function linkDescarga(value, row, index) {
    if (row.tipoArchivo) {
        return '<a href="/archivo/get/descargar-archivo?id=' + row.id + '">' + value + '</a>';
    } else {
        return value;
    }
}

$(document).ready(function () {

    $('#files').treegrid({
        url: '/archivo/expediente/obtener-archivos',
        idField: 'id',
        treeField: 'name',
        showFooter: true,
        columns: [[
                {field: 'name', title: 'Archivo', width: 500, formatter: linkDescarga},
                {field: 'tipoArchivo', title: 'Tipo Archivo', width: 250, align: 'left',
                    editor: {
                        type: 'combobox',
                        options: {
                            valueField: 'id',
                            textField: 'nombre',
                            panelWidth: 350,
                            panelHeight: 130,
                            url: '/archivo/expediente/tipos-de-archivos'
                        }
                    },
                    formatter: function (value, row) {
                        return row.nombreArchivo;
                    }
                },
                {field: 'usuario', title: 'Usuario', width: 120},
                {field: 'creado', title: 'Creado', width: 120},
                {field: 'editor', title: '', width: 70}
            ]],
        onBeforeLoad: function (row, param) {
            param.id = $("#idExpediente").val();
            if (row !== null) {
                param.tipoArchivo = row.id;
            }
        },
        onContextMenu: function (e, row) {
            if (row) {
                e.preventDefault();
                $(this).treegrid('select', row.id);
                $('#mm').menu('show', {
                    left: e.pageX,
                    top: e.pageY
                });
            }
        }
    });

});