
function exportToExcel() {
    window.location.href = "/clientes/get/proveedores?excel=true";
}

$(document).ready(function () {
    
    var tblprov = $('#providers').edatagrid();
    
    tblprov.edatagrid({
        title: "Proveedores",
        singleSelect: true,
        fitColumns: false,
        url: '/clientes/get/proveedores',
        method: 'get',
        idField: 'id',
        height: 660,
        pageSize: 20,
        remoteFilter: true,
        pagination: true,
        toolbar: [{
                text:'Exportar a Excel', 
                iconCls:'icon-download',
                handler: function(){
                    exportToExcel();
                }
            }],
        destroyMsg: {
            norecord: {
                title: 'Advertencia',
                msg: '<span style="color: red">No se ha seleccionado ningún registro.</span>'
            }
        },
        frozenColumns: [[
                {field: 'identificador', title: 'Identificador'},
                {field: 'nombre', title: 'Proveedor'}
        ]],
        columns: [[
                {field: 'calle', width: 180, title: 'Calle'},
                {field: 'numInt', title: 'Num. Interior'},
                {field: 'numExt', title: 'Num. Exterior'},
                {field: 'colonia', width: 180, title: 'Colonia'},
                {field: 'localidad', width: 180, title: 'Localidad'},
                {field: 'ciudad', width: 180, title: 'Ciudad'},
                {field: 'estado', width: 200, title: 'Estado'},
                {field: 'codigoPostal', title: 'CP'},
                {field: 'pais', width: 100, title: 'País'},
                {field: 'fraccion', title: 'Fracción'},
                {field: 'numParte', title: 'Num. Parte Cliente'},
                {field: 'descripcion', title: 'Descripción', width: 220},
                {field: 'paisOrigen', title: 'País Origen', width: 150},
                {field: 'umc', width: 120, title: 'UMC'},
                {field: 'umt', width: 120, title: 'UMT'},
                {field: 'oma', width: 120, title: 'OMA'}
                
            ]],
        onClickRow: function (index, row) {
            
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

});

