/**
 * 
 * @type Number
 */

var tbl;
var rowIndex = 0;

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

function cancelar() {
    var row = tbl.edatagrid('getSelected');
    var rowIndex = tbl.edatagrid('getRowIndex', row);
    tbl.edatagrid('cancelEdit', rowIndex);
}

function descargarArchivo() {
    var row = tbl.edatagrid('getSelected');
    if (row) {
        window.location.href = '/archivo/get/descargar-archivo-terminal?id=' + row.id;
    } else {
        $.messager.alert('Advertencia', 'Debe primero seleccionar un registro para poder descargars.');
    }
}

function borrarRepositorio() {
    var ids = [];
    var rows = tbl.datagrid('getSelections');
    for (var i = 0; i < rows.length; i++) {
        ids.push(rows[i].id);
    }
    if (ids.length >= 1) {
        $.ajax({url: "/automatizacion/terminal/borrar-repositorio?ids=" + ids, type: "get", dataType: "json", timeout: 3000,
            success: function (res) {
                if (res.success === true) {
                    tbl.edatagrid('reload');
                }
            }
        });
    } else {
        $.messager.alert('Advertencia', 'Usted no ha seleccionado ningun registros.');
    }
}

function enviarRepositorio() {
    var ids = [];
    var rows = tbl.datagrid('getSelections');
    for (var i = 0; i < rows.length; i++) {
        ids.push(rows[i].id);
    }
    if (ids.length >= 1) {
        $.ajax({url: "/automatizacion/terminal/enviar-repositorio?ids=" + ids, type: "get", dataType: "json", timeout: 3000,
            success: function (res) {
                if (res.success === true) {
                    tbl.edatagrid('reload');
                }
            }
        });
    } else {
        $.messager.alert('Advertencia', 'Usted no ha seleccionado ningun registros.');
    }
}

function borrar() {
    var ids = [];
    var rows = tbl.datagrid('getSelections');
    for (var i = 0; i < rows.length; i++) {
        ids.push(rows[i].id);
    }
    if (ids.length >= 1) {

    } else {
        $.messager.alert('Advertencia', 'Usted no ha seleccionado ningun registros.');
    }
}

function analizar() {
    var ids = [];
    var rows = tbl.datagrid('getSelections');
    for (var i = 0; i < rows.length; i++) {
        if (rows[i].nombreArchivo.match(/.xml$/)) {
            ids.push(rows[i].id);
        }
    }
    if (ids.length >= 1) {
        tbl.datagrid('options').loadMsg = 'Procesando, espere por favor...';
        tbl.datagrid('loading');
        $.ajax({url: "/automatizacion/terminal/analizar?ids=" + ids, type: "get", dataType: "json", timeout: 10000,
            success: function (res) {
                if (res.success === true) {
                    tbl.edatagrid('reload');
                }
            }
        });
    } else {
        $.messager.alert('Advertencia', 'Usted no ha seleccionado al menos dos registros.');
    }
}

function reporte() {
    if ($('#filterForm').form('validate') === true) {
        var dataValues = $("#filterForm").serialize();
        window.location = '/archivo/get/reporte-facturas-terminal?' + dataValues;
    }
}

function downloadZip() {
    if ($('#filterForm').form('validate') === true) {
        var dataValues = $("#filterForm").serialize();
        window.location = '/archivo/get/descargar-facturas-terminal?' + dataValues;
    }
}

function updateGrid() {
    if ($('#filterForm').form('validate') === true) {
        tbl.datagrid('reload',{
            fechaInicio: $("#fechaInicio").val(),
            fechaFin: $("#fechaFin").val(),
            switchData: $("#switchData").switchbutton('options').checked
        });
    }
}

$(document).ready(function () {
    
    $('#fechaInicio').datebox({
        value: (new Date().toString('dd-MMM-yyyy'))
    });
    
    var date = new Date();
    $('#fechaInicio').datebox({value:$.fn.datebox.defaults.formatter(new Date(date.getFullYear(), date.getMonth(), 1))});
    
    $('#fechaFin').datebox({
        value: (new Date().toString('dd-MMM-yyyy'))
    });

    tbl = $('#invoices').edatagrid();

    tbl.edatagrid({method: 'post', idField: 'id', height: 665, singleSelect: false, remoteSort: true, remoteFilter: true, pagination: true, rownumbers: true,
        pageSize: 20,
        pageList: [20, 30, 40, 50],
        fitColumns: false,
        title: "Facturas Terminal",
        url: '/archivo/post/obtener-facturas-terminal',
        updateUrl: '/archivo/post/actualizar-factura-terminal',
        queryParams: {
            fechaInicio: $("#fechaInicio").val(),
            fechaFin: $("#fechaFin").val(),
            switchData: $("#switchData").switchbutton('options').checked
        },
        toolbar: [
            {text: 'Actualizar', iconCls: 'icon-reload', handler: function () {
                    tbl.edatagrid('reload');
                }},
            {text: 'Cancelar', iconCls: 'icon-undo', handler: cancelar},
            {text: 'Guardar', iconCls: 'icon-save', handler: function () {
                    tbl.edatagrid('saveRow');
                }},
            {text: 'Borrar', iconCls: 'icon-remove', handler: borrar}
        ],
        frozenColumns: [[
                {field: 'ck', checkbox: true, hidden: false,
                    styler: function (index, row) {
                        if (!row.nombreArchivo.match(/.xml$/)) {
                            return {class: 'dg-nocheck'};
                        }
                    }},
                {field: 'idRepositorio', title: 'idRepo', 
                    formatter: function (value, row) {
                        if (value) {
                            return '<a href="/archivo/index/expediente?id=' + value + '" target="_blank">' + value + '</a>';
                        } else {
                            return '';
                        }
                    }},
                {field: 'pedimento', title: 'Pedimento', width: 70, editor: {type: 'text'}},
                {field: 'referencia', title: 'Referencia', width: 80, editor: {type: 'text'}},
                {field: 'folio', title: 'Folio', width: 70, editor: {type: 'text'}}
            ]],
        columns: [[
                {field: 'fechaFolio', title: 'Fecha', width: 90,
                    formatter: function (value, row) {
                        if (value) {
                            return $.fn.datebox.defaults.formatter(new Date(value));
                        } else {
                            return '';
                        }
                    }},
                {field: 'rfcCliente', title: 'RFC Cliente', width: 100},
                {field: 'rfcEmisor', title: 'RFC Emisor', width: 110},
                {field: 'rfcReceptor', title: 'RFC Receptor', width: 110},
                {field: 'guia', title: 'Guía', width: 120, editor: {type: 'text'}},
                {field: 'nombreArchivo', title: 'Archivo', width: 290,
                    formatter: function (value, row) {
                        if (!row.nombreArchivo.match(/.xml$/)) {
                            return '<a style="cursor: pointer" class="openPdf" data-id="' + row.id + '">' + row.nombreArchivo + '</a>';
                        } else {
                            return '<a style="cursor: pointer" class="openXml" data-id="' + row.id + '">' + row.nombreArchivo + '</a>';
                        }
                    }},
                {field: 'creado', title: 'Fecha Creación', width: 125}
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
        }
    });
    
    tbl.edatagrid('enableFilter', []);
    
    $.each(['idRepositorio', 'patente', 'aduana'], function (index, value) {
        $(".datagrid-editable-input[name='" + value + "']").hide();
    });

    $("#uploads").validate({
        errorPlacement: function (error, element) {
            $(element)
                    .closest("form")
                    .find("label[for=\"" + element.attr("id") + "\"]")
                    .append(error);
        },
        errorElement: "span",
        errorClass: "traffic-error",
        rules: {
            "files[]": {
                required: true
            }
        },
        messages: {
            "files[]": {
            }
        }
    });

    $('#dd').dialog({title: 'XML', width: 700, height: 400, closed: true, cache: false, modal: true});

    $(document.body).on("click", ".openPdf", function (ev) {
        ev.preventDefault();
        var id = $(this).data("id");
        $('#dd').dialog('refresh', '/archivo/get/pdf-factura-terminal?id=' + id)
                .dialog('open');
    });

    $(document.body).on("click", ".openXml", function (ev) {
        ev.preventDefault();
        var id = $(this).data("id");
        $('#dd').dialog('refresh', '/archivo/get/xml-factura-terminal?id=' + id)
                .dialog('open');
    });

    $(document.body).on("click", "#submit", function (ev) {
        ev.preventDefault();
        if ($('#file').filebox('getValue') === "") {
            $.messager.alert('Advertencia', 'No ha seleccionado archivos.');
        } else {
            tbl.datagrid('options').loadMsg = 'Procesando, espere por favor...';
            tbl.datagrid('loading');
            if ($("#uploads").valid()) {
                $("#uploads").ajaxSubmit({type: "post", dataType: "json",
                    success: function (res) {
                        if (res.success === true) {
                            $("#uploads").trigger("reset");
                        }
                        tbl.edatagrid('reload');
                    }
                });
            }
        }
    });

});