/**
 * programmed by Jvaldez at gmail
 * 2015.may.14
 */

let edit = false;
let idCliente = null;

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

function exportProvidersToExcel(idCliente) {
    window.location.href = "/operaciones/catalogo/proveedores?idCliente=" + idCliente + "&excel=true";
}

function exportPartsToExcel(idCliente, idProveedor) {
    window.location.href = "/operaciones/catalogo/productos?idCliente=" + idCliente + "&idProveedor=" + idProveedor + "&excel=true";
}

function newProduct() {
    $('#dlg').dialog('open').dialog('center').dialog('setTitle', 'Nuevo producto');
    $('#fm').form('clear');
}

function saveProduct() {
    $('#fm').form('submit', {
        url: "/operaciones/catalogo/producto-nuevo",
        ajax: true,
        iframe: false,
        onSubmit: function (param) {
            return $("#fm").form('validate');
        },
        success: function (result) {
            var result = eval('(' + result + ')');
            if (result.errorMsg) {
                $.messager.show({
                    title: 'Error',
                    msg: result.errorMsg
                });
            } else {
                $('#parts').datagrid('reload');
            }
        }
    });
}

function saveProvider() {
    $('#fmprov').form('submit', {
        url: "/operaciones/catalogo/proveedor-nuevo",
        ajax: true,
        iframe: false,
        onSubmit: function (param) {
            param.idCliente = $("#idCliente").val();
            return $("#fmprov").form('validate');
        },
        success: function (result) {
            var result = eval('(' + result + ')');
            if (result.errorMsg) {
                $.messager.show({
                    title: 'Error',
                    msg: result.errorMsg
                });
            } else {
                $('#dlgprov').dialog('close');
                $('#providers').datagrid('reload');
            }
        }
    });
}

function uploadImage() {
    $('#fmimage').form('submit', {
        url: "/operaciones/catalogo/subir-fotos",
        ajax: true,
        iframe: false,
        onSubmit: function (param) {
            return $('#fmimage').form('validate');
        },
        success: function (result) {
            var result = eval('(' + result + ')');
            if (result.errorMsg) {
                $.messager.show({
                    title: 'Error',
                    msg: result.errorMsg
                });
            } else {
                $('#dlg').dialog('close');
                $('#parts').edatagrid('reload');
            }
        }
    });
}

function abrirImagen(href, name) {
    $("#sourceImage").attr("src", href);
    $("#dlgimg").dialog({
        title: name,
        closeOnEscape: true,
        modal: true,
        position: 'top',
        width: 750,
        height: 600,
        show: "blind",
        hide: "explode",
        autoOpen: false
    });
}

function photosDialog() {
    var imgs = '';
    $.ajax({
        url: "/operaciones/catalogo/imagenes?idProducto=" + $("#idProducto").val(),
        type: "GET",
        dataType: "json",
        cache: false,
        success: function (res) {
            if (res.success === true) {
                $.each(res.data, function (index, value) {
                    imgs += '<div class="imgContent" style="height: 66px; padding: 2px; display: block; cursor: pointer;"><img class="img" src="' + value.carpeta + '/' + value.miniatura + '" onclick="javascript:abrirImagen(\'' + value.carpeta + '/' + value.imagen + '\',\'' + value.nombre + '\')" /></div><br>';
                });
            }
            $("#dlgimgs").append(imgs);
        }
    });
    $("#dlgimgs").dialog({
        title: 'Fotos',
        closeOnEscape: true,
        modal: true,
        position: 'top',
        width: 600,
        height: 250,
        show: "blind",
        hide: "explode",
        autoOpen: false  ///added this line
    });
}

function uploadDialog() {
    $("#dlg").dialog({
        title: 'Subir fotos',
        closeOnEscape: true,
        modal: true,
        position: 'top',
        width: 400,
        height: 200,
        show: "blind",
        hide: "explode",
        autoOpen: false  ///added this line
    });
}

function formatImage(val, row) {
    var imgs = '';
    $.each(row.imagenes, function (index, value) {
        imgs += '<div class="imgContent" style="height: 66px; padding: 2px; display: block; float: left; cursor: pointer;"><img class="img" src="' + value.carpeta + '/' + value.miniatura + '" onclick="javascript:abrirImagen(\'' + value.carpeta + '/' + value.imagen + '\',\'' + value.nombre + '\')" /></div>';
    });
    return imgs;
}

function deleteProviderRow() {
    var row = $('#providers').datagrid('getSelected');
    if (row) {
        $.messager.confirm('Confirmar', '¿Está seguro de que desea borrar el registro seleccionado?', function (r) {
            if (r) {
                $.post('/operaciones/catalogo/proveedor-borrar', { id: row.id }, function (res) {
                    if (res.success === true) {
                        $('#providers').edatagrid('reload');
                        $('#parts').datagrid('load', '/operaciones/catalogo/productos?idCliente=' + $("#idCliente").val());
                    } else {
                        $.messager.show({
                            title: 'Error',
                            msg: res.errorMsg
                        });
                    }
                }, 'json');
            }
        });
    }
}

$(document).ready(function () {

    var tblprov = $('#providers').edatagrid();

    var tblpart = $('#parts').edatagrid();

    tblpart.edatagrid({
        title: "Catalogo de partes",
        singleSelect: true,
        fitColumns: false,
        url: '/operaciones/catalogo/productos?idCliente=' + $("#idCliente").val(),
        updateUrl: '/operaciones/catalogo/producto-guardar',
        destroyUrl: '/operaciones/catalogo/producto-borrar',
        method: 'get',
        idField: 'id',
        height: 400,
        remoteFilter: true,
        pagination: true,
        rowStyler: function (index, row) {
            if (row.valido) {
                return 'background-color: #ebfff2'
            }
        },
        toolbar: [{
            text: 'Actualizar',
            iconCls: 'icon-reload',
            handler: function () {
                $('#parts').edatagrid('reload');
            }
        }, {
            text: 'Cancelar',
            iconCls: 'icon-undo',
            handler: function () {
                $('#parts').edatagrid('cancelRow');
            }
        }, {
            text: 'Guardar',
            iconCls: 'icon-save',
            handler: function () {
                $('#parts').edatagrid('saveRow');
            }
        }, {
            text: 'Borrar',
            iconCls: 'icon-remove',
            handler: function () {
                $('#parts').edatagrid('destroyRow');
            }
        }, {
            text: 'Exportar',
            iconCls: 'icon-download',
            handler: function () {
                exportPartsToExcel($("#idCliente").val(), $("#idProveedor").val());
            }
        }],
        destroyMsg: {
            norecord: {
                title: 'Advertencia',
                msg: '<span style="color: red">No se ha seleccionado ningún registro.</span>'
            },
            confirm: {
                title: 'Confirmar',
                msg: '¿Está seguro de que desea borrar el registro seleccionado?'
            }
        },
        frozenColumns: [[
            {
                field: 'valido',
                title: 'Aprob.',
                width: 50,
                formatter: function (value, row) {
                    if (value === null) {
                        return `<input type="checkbox" class="valid-product" data-id="${row.id}"/>`;
                    } else {
                        return `<input type="checkbox" class="valid-product" data-id="${row.id}" checked/>`;
                    }
                }
            },
            { field: 'fraccion', title: 'Fracción' },
            { field: 'fraccion_2020', title: 'Fracc. (2020)', editor: { type: 'text' } },
            { field: 'nico', title: 'NICO', editor: { type: 'text' } },
            { field: 'numParte', title: 'Num. Parte Cliente', editor: { type: 'text' } }
        ]],
        columns: [[
            { field: 'descripcion', title: 'Descripción', width: 220, editor: { type: 'text' } },
            { field: 'paisOrigen', title: 'País Origen', width: 150, editor: { type: 'combobox', options: { valueField: 'cve_pais', textField: 'nombre', panelWidth: 350, panelHeight: 130 } } },
            { field: 'umc', width: 120, title: 'UMC', editor: { type: 'combobox', options: { valueField: 'clave', textField: 'desc', panelWidth: 150, panelHeight: 130 } } },
            { field: 'umt', width: 120, title: 'UMT', editor: { type: 'combobox', options: { valueField: 'clave', textField: 'desc', panelWidth: 150, panelHeight: 130 } } },
            { field: 'oma', width: 120, title: 'OMA', editor: { type: 'combobox', options: { valueField: 'unidad_medida', textField: 'desc_es', panelWidth: 150, panelHeight: 130 } } },
            { field: 'imagenes', width: 300, title: 'Imagen', formatter: formatImage }
        ]],
        onBeginEdit: function (index, row) {
            var pais = $('#parts').datagrid('getEditor', {
                index: index,
                field: 'paisOrigen'
            });
            if (pais !== undefined) {
                $(pais.target).combobox('reload', '/operaciones/catalogo/paises');
            }
            var umc = $('#parts').datagrid('getEditor', {
                index: index,
                field: 'umc'
            });
            if (umc !== undefined) {
                $(umc.target).combobox('reload', '/operaciones/catalogo/umc');
            }
            var umt = $('#parts').datagrid('getEditor', {
                index: index,
                field: 'umt'
            });
            if (umt !== undefined) {
                $(umt.target).combobox('reload', '/operaciones/catalogo/umc');
            }
            var oma = $('#parts').datagrid('getEditor', {
                index: index,
                field: 'oma'
            });
            if (oma !== undefined) {
                $(oma.target).combobox('reload', '/operaciones/catalogo/oma');
            }
        },
        onEndEdit: function (index, row) {

        },
        onBeforeEdit: function (index, row) {
            row.editing = true;
            $(this).datagrid('refreshRow', index);
        },
        onAfterEdit: function (index, row) {
            row.editing = false;
            $(this).datagrid('refreshRow', index);
        },
        onCancelEdit: function (index, row) {
            row.editing = false;
            $(this).datagrid('refreshRow', index);
        },
        onRowContextMenu: function (e, index, row) {
            e.preventDefault();
            $('#idProducto').val(row.id);
            $('#mm').menu('show', {
                left: e.pageX,
                top: e.pageY
            });
        }
    });

    tblpart.edatagrid('enableFilter', [{
        field: 'fraccion',
        type: 'textbox'
    }, {
        field: 'numParteCliente',
        type: 'textbox'
    }]);

    tblprov.edatagrid({
        title: "Proveedores",
        singleSelect: true,
        fitColumns: false,
        url: '/operaciones/catalogo/proveedores?idCliente=' + $("#idCliente").val(),
        updateUrl: '/operaciones/catalogo/proveedor-guardar',
        destroyUrl: '/operaciones/catalogo/proveedor-borrar',
        method: 'get',
        idField: 'id',
        height: 345,
        remoteFilter: true,
        pagination: true,
        rowStyler: function (index, row) {
            if (row.valido) {
                return 'background-color: #ebfff2'
            }
        },
        toolbar: [{
            text: 'Actualizar',
            iconCls: 'icon-reload',
            handler: function () {
                $('#providers').edatagrid('reload');
            }
        }, {
            text: 'Cancelar',
            iconCls: 'icon-undo',
            handler: function () {
                $('#providers').edatagrid('cancelRow');
            }
        }, {
            text: 'Guardar',
            iconCls: 'icon-save',
            handler: function () {
                $('#providers').edatagrid('saveRow');
            }
        }, {
            text: 'Borrar',
            iconCls: 'icon-remove',
            handler: deleteProviderRow
        }, {
            text: 'Exportar',
            iconCls: 'icon-download',
            handler: function () {
                exportProvidersToExcel($("#idCliente").val());
            }
        }],
        destroyMsg: {
            norecord: {
                title: 'Advertencia',
                msg: '<span style="color: red">No se ha seleccionado ningún registro.</span>'
            }
        },
        frozenColumns: [[
            {
                field: 'valido',
                title: 'Aprob.',
                width: 50,
                formatter: function (value, row) {
                    if (value === null) {
                        return `<input type="checkbox" class="valid-provider" data-id="${row.id}"/>`;
                    } else {
                        return `<input type="checkbox" class="valid-provider" data-id="${row.id}" checked/>`;
                    }
                }
            },
            { field: 'identificador', title: 'Identificador', editor: { type: 'text' } },
            { field: 'nombre', title: 'Proveedor', editor: { type: 'text' } }
        ]],
        columns: [[
            { field: 'calle', width: 180, title: 'Calle', editor: { type: 'text' } },
            { field: 'numInt', title: 'Num. Interior', editor: { type: 'text' } },
            { field: 'numExt', title: 'Num. Exterior', editor: { type: 'text' } },
            { field: 'colonia', width: 180, title: 'Colonia', editor: { type: 'text' } },
            { field: 'localidad', width: 180, title: 'Localidad', editor: { type: 'text' } },
            { field: 'ciudad', width: 180, title: 'Ciudad', editor: { type: 'text' } },
            { field: 'estado', width: 200, title: 'Estado', editor: { type: 'text' } },
            { field: 'codigoPostal', title: 'CP', editor: { type: 'text' } },
            {
                field: 'pais', width: 100, title: 'País', editor: {
                    type: 'combobox',
                    options: {
                        valueField: 'cve_pais',
                        textField: 'nombre',
                        panelWidth: 350,
                        panelHeight: 130
                    }
                }
            }
        ]],
        onClickRow: function (index, row) {
            $('#parts').datagrid('load', '/operaciones/catalogo/productos?idCliente=' + $("#idCliente").val() + "&idProveedor=" + row.id);
            $('#idProveedor').val(row.id);
            $('#proveedor').textbox('setValue', row.nombre);
        },
        onBeginEdit: function (index, row) {
            var pais = $('#providers').datagrid('getEditor', {
                index: index,
                field: 'pais'
            });
            if (pais !== undefined) {
                $(pais.target).combobox('reload', '/operaciones/catalogo/paises');
            }
        },
        onEndEdit: function (index, row) {
            row.editing = false;
            $(this).datagrid('refreshRow', index);
        },
        onBeforeEdit: function (index, row) {
            row.editing = true;
            $(this).datagrid('refreshRow', index);
        },
        onAfterEdit: function (index, row) {
            row.editing = false;
            $(this).edatagrid('refreshRow', index);
        },
        onCancelEdit: function (index, row) {
            row.editing = false;
            $(this).datagrid('refreshRow', index);
        },
        onRowContextMenu: function (e) {
            e.preventDefault();
        }
    });

    tblprov.edatagrid('enableFilter', [{
        field: 'identificador',
        type: 'textbox'
    }, {
        field: 'nombre',
        type: 'textbox'
    }, {
        field: 'clave',
        type: 'textbox'
    }]);

    $.each(['calle', 'numExt', 'numInt', 'colonia', 'localidad', 'ciudad', 'estado', 'codigoPostal', 'pais', 'numParteProveedor', 'paisOrigen', 'umc', 'umt', 'oma', 'imagenes'], function (index, value) {
        $(".datagrid-editable-input[name='" + value + "']").hide();
    });

    $(document.body).on("click", ".valid-product", function(e) {        
        $.ajax({
            url: "/operaciones/catalogo/validar-producto",
            type: "post",
            data: { idCliente: idCliente, id: $(this).data("id")},
            dataType: "json",
            success: function (res) {
                if (res) {
                }
            }
        });
    });

    $(document.body).on("click", ".valid-provider", function(e) {
        $.ajax({
            url: "/operaciones/catalogo/validar-proveedor",
            type: "post",
            data: { idCliente: idCliente, id: $(this).data("id")},
            dataType: "json",
            success: function (res) {
                if (res) {
                }
            }
        });
    });

});