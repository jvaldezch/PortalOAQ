/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function exportToExcel() {
    
    var data = $("#ff").form();
    
    var idAduana = data.context.getElementById('idAduana').value;
    var idCliente = data.context.getElementById('idCliente').value;
    var fechaInicio = data.context.getElementById('fechaInicio').value;
    var fechaFin = data.context.getElementById('fechaFin').value;
    
    var uri = "/administracion/get/reporte-facturacion?idAduana=" + idAduana + 
                "&idCliente=" + idCliente + "&fechaInicio="+ fechaInicio + "&fechaFin=" + fechaFin + "&excel=true";
    
    window.location.href = uri;
}

function invoiceLink(val, row) {
    if (row.folio) {
        return '<a href="/administracion/index/ver-folio?id=' + row.folio + '" target="_blank">' + row.folio + '</a>';        
    } else {
        return '';
    }
}

function clearForm() {
    $('#ff').form('clear');
}

function trafficLink(val, row) {
    return '<a href="/trafico/index/editar-trafico?id=' + row.idTrafico + '" target="_blank">' + row.idTrafico + '</a>';
}

$.fn.datebox.defaults.formatter = function (date) {
    var y = date.getFullYear();
    var m = date.getMonth() + 1;
    var d = date.getDate();
    return y + '-' + (m < 10 ? ('0' + m) : m) + '-' + (d < 10 ? ('0' + d) : d);
};


const formatter = new Intl.NumberFormat('en-US', {
  style: 'currency',
  currency: 'USD',
  minimumFractionDigits: 2
});

function formatCurrency(val, row) {
    return formatter.format(val);
}

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

var dg;

function submitForm() {
    
    var data = $("#ff").form();
    
    dg.edatagrid('load',{
        url:"/administracion/get/reporte-facturacion",
        idAduana: data.context.getElementById('idAduana').value,
        idCliente: data.context.getElementById('idCliente').value,
        fechaInicio: data.context.getElementById('fechaInicio').value,
        fechaFin: data.context.getElementById('fechaFin').value
    });
};

$(document).ready(function () {
    
    $('#fechaInicio').datebox({
        value: (new Date().toString('dd-MMM-yyyy'))
    });
    
    var date = new Date();
    
    $('#fechaInicio').datebox({value:$.fn.datebox.defaults.formatter(new Date(date.getFullYear(), date.getMonth(), 1))});
    
    $('#fechaFin').datebox({
        value: (new Date().toString('dd-MMM-yyyy'))
    });
    
    dg = $('#dg').edatagrid();
    
    var data = $("#ff").form();
    
    dg.edatagrid({
        pagination: true,
        singleSelect: true,
        striped: true,
        rownumbers: true,
        fitColumns: false,
        height: 562,
        method: "GET",
        remoteFilter: true,
        url: "/administracion/get/reporte-facturacion",
        updateUrl: "/administracion/ajax/actualizar-factura",
        queryParams: {
            idAduana: data.context.getElementById('idAduana').value,
            idCliente: data.context.getElementById('idCliente').value,
            fechaInicio: data.context.getElementById('fechaInicio').value,
            fechaFin: data.context.getElementById('fechaFin').value
        },
        pageSize: 20,
        toolbar: [{
                text: "Guardar",
                iconCls: "icon-save",
                handler: function() {
                  $("#dg").edatagrid("saveRow");
                }
              },
              {
                text: "Cancelar",
                iconCls: "icon-cancel",
                handler: function() {
                  $("#dg").edatagrid("cancelRow");
                }
              },
              {
                text: "Actualizar",
                iconCls: "icon-reload",
                handler: function() {
                  $("#dg").edatagrid("reload");
                }
              },{
                text: 'Descargar reporte',
                iconCls: 'icon-download',
                handler: function () {
                    exportToExcel();
                }
            }],
        frozenColumns: [[
                {field: 'idTrafico', width: 50, title: 'Trafico', formatter: trafficLink},
                {field: 'patente', width: 50, title: 'Patente'},
                {field: 'aduana', width: 50, title: 'Aduana'},
                {field: 'pedimento', width: 80, title: 'Pedimento'},
                {field: 'referencia', width: 100, title: 'Referencia'},
                {field: 'folio', width: 90, title: 'Folio', formatter: invoiceLink}
            ]],
        columns: [[
                {field: 'ie', width: 40, title: 'I/E'},
                {field: 'cvePedimento', width: 40, title: 'Cve.'},
                {field: 'nombreCliente', width: 300, title: 'Nombre cliente'},
                {field: 'blGuia', width: 150, title: 'BL/Guía'},
                {field: 'nombreBuque', width: 150, title: 'Nom. Buque'},
                {field: 'fechaFacturacion', width: 90, title: 'F. Facturacion'},
                {field: 'fechaPago', width: 90, title: 'F. Pago'},
                {field: 'pagoHechos', width: 120, title: 'Pagos hechos', formatter: formatCurrency, align:'right'},
                {field: 'sinComprobar', width: 120, title: 'G. sin comprobar', formatter: formatCurrency, align:'right'},
                {field: 'maniobras', width: 120, title: 'Maniobras', formatter: formatCurrency, align:'right'},
                {field: 'honorarios', width: 90, title: 'Honorarios', formatter: formatCurrency, align:'right'},
                {field: 'subTotal', width: 120, title: 'SubTotal', formatter: formatCurrency, align:'right'},
                {field: 'iva', width: 120, title: 'IVA', formatter: formatCurrency, align:'right'},
                {field: 'total', width: 120, title: 'Total', formatter: formatCurrency, align:'right'},
                {field: 'pagada', width: 70, title: 'Pagada',
                    formatter: function (value, row) {
                        if (parseInt(value) === 1) {
                            return 'Si';                                    
                        } else {
                            return 'No';
                        }
                    },
                    editor:{
                        type:'combobox',
                        options:{
                          panelHeight:'auto',
                          valueField: 'value',
                          textField: 'name',
                          data:[
                            {
                              value: '0',
                              name: 'No'
                            },
                            {
                              value: '1',
                              name: 'Si'
                            }
                          ]
                        }
                      }}                
            ]]
    });
    
    dg.edatagrid("enableFilter", []);
    
    $.each(['idTrafico', 'patente', 'aduana', 'ie', 'cvePedimento', 'blGuia', 'nombreBuque', 'fechaFacturacion', 'fechaPago', 'pagoHechos', 'sinComprobar', 'honorarios', 'iva', 'subTotal', 'pagada', 'total', 'maniobras'], function (index, value) {
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

});

