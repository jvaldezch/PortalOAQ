/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function exportToExcel(datavalues) {
    window.location.href = "/trafico/crud/reporte-traficos?" + datavalues + "&excel=true";
}

function exportNoInvoiceToExcel(datavalues) {
    window.location.href = "/trafico/crud/reporte-traficos-sinfacturar?" + datavalues + "&excel=true";
}

Date.prototype.yyyymmdd = function () {
    var mm = this.getMonth() + 1; // getMonth() is zero-based
    var dd = this.getDate();

    return [this.getFullYear(),
        (mm > 9 ? '' : '0') + mm,
        (dd > 9 ? '' : '0') + dd
    ].join('-');
};

function fecha(value) {
    var date = new Date(value);
    return date.yyyymmdd();
}

function invoiceLink(val, row) {
    if (row.folio) {
        return '<a href="/trafico/index/ver-folio?id=' + row.folio + '" target="_blank">' + row.folio + '</a>';        
    } else {
        return '';
    }
}

function trafficLink(val, row) {
    return '<a href="/trafico/index/editar-trafico?id=' + row.id + '" target="_blank">' + row.referencia + '</a>';
}

const formatter = new Intl.NumberFormat('en-US', {
  style: 'currency',
  currency: 'USD',
  minimumFractionDigits: 2
});

function formatCurrency(val, row) {
    return formatter.format(val);
}

function submitForm() {
    if ($("#ff").form('validate') === true) {
        var dg = $('#dg').edatagrid();
        var data = $("#ff").form();
        if (parseInt(data.context.getElementById('tipoReporte').value) === 1) {
            var datavalues = $("#ff").serialize();
            dg.edatagrid({
                pagination: true,
                singleSelect: true,
                striped: true,
                rownumbers: true,
                fitColumns: false,
                height: 562,
                method: "get",
                remoteFilter: true,
                url: "/trafico/crud/reporte-traficos?" + datavalues,
                pageSize: 20,
                toolbar: [{
                        text: 'Guardar',
                        iconCls: 'icon-save',
                        handler: function () {
                            exportToExcel(datavalues);
                        }
                    }],
                frozenColumns: [[
                        {field: 'patente', width: 50, title: 'Patente'},
                        {field: 'aduana', width: 50, title: 'Aduana'},
                        {field: 'pedimento', width: 80, title: 'Pedimento'},
                        {field: 'referencia', width: 100, title: 'Referencia'}
                    ]],
                columns: [[
                        {field: 'ie', width: 70, title: 'I/E'},
                        {field: 'cvePedimento', width: 40, title: 'Cve.'},
                        {field: 'nombreCliente', width: 300, title: 'Nombre Cliente'},                        
                        {field: "coves", width: 30, title: "CV"},
                        {field: "edocuments", width: 30, title: "ED"},
                        {field: 'nombre', width: 300, title: 'Usuario'},
                        {field: 'fechaEta', width: 100, title: 'ETA'},
                        {field: 'fechaNotificacion', width: 140, title: 'F. Notificación'},
                        {field: 'fechaEnvioDocumentos', width: 140, title: 'F. Envio Doctos.'},
                        {field: 'fechaEntrada', width: 140, title: 'F. Entrada'},
                        {field: 'fechaPresentacion', width: 140, title: 'F. Presentación'},
                        {field: 'fechaEnvioProforma', width: 140, title: 'F. Envio Proforma'},
                        {field: 'fechaVistoBueno', width: 140, title: 'F. VoBo'},
                        {field: 'fechaRevalidacion', width: 100, title: 'F. Revalidación'},
                        {field: 'fechaPrevio', width: 145, title: 'F. Previo'},
                        {field: 'fechaPago', width: 145, title: 'F. Pago'},
                        {field: 'fechaLiberacion', width: 145, title: 'F. Liberación'},
                        {field: 'fechaEtaAlmacen', width: 145, title: 'ETA Almacen'},
                        {field: 'fechaFacturacion', width: 100, title: 'F. Facturación'},
                        {field: 'blGuia', width: 150, title: 'BL/Guía'},
                        {field: 'nombreAlmacen', width: 150, title: 'Almacen'},
                        {field: 'descripcionPlanta', width: 150, title: 'Planta'}
                    ]]
            });
        }
        if (parseInt(data.context.getElementById('tipoReporte').value) === 5) { // traficos aereos
            var tipoAduana = 2;
            var datavalues = $("#ff").serialize() + "&tipoAduana=" + tipoAduana;
            dg.edatagrid({
                pagination: true,
                singleSelect: true,
                striped: true,
                rownumbers: true,
                fitColumns: false,
                height: 562,
                method: "get",
                remoteFilter: true,
                url: "/trafico/crud/reporte-traficos?" + datavalues,
                pageSize: 20,
                queryParams: {
                    tipoAduana: tipoAduana
                },
                toolbar: [{
                        text: 'Guardar',
                        iconCls: 'icon-save',
                        handler: function () {
                            exportToExcel(datavalues);
                        }
                    }],
                frozenColumns: [[
                        {field: 'patente', width: 50, title: 'Patente'},
                        {field: 'aduana', width: 50, title: 'Aduana'},
                        {field: 'pedimento', width: 80, title: 'Pedimento'},
                        {field: 'referencia', width: 100, title: 'Referencia'}
                    ]],
                columns: [[
                        {field: 'ie', width: 70, title: 'Tipo Operación'},
                        {field: 'nombre', width: 120, title: 'Usuario'},
                        {field: 'cvePedimento', width: 40, title: 'Cve.'},
                        {field: 'nombreCliente', width: 300, title: 'Nombre Cliente'},
                        {field: 'fechaEta', width: 140, title: 'F. ETA'},
                        {field: 'fechaEntrada', width: 140, title: 'F. Entrada'},
                        {field: 'fechaPresentacion', width: 140, title: 'F. Presentación'},
                        {field: 'blGuia', width: 150, title: 'Guía'},
                        {field: 'nombreAlmacen', width: 150, title: 'Almacen'},
                        {field: 'fechaInstruccionEspecial', width: 140, title: 'F. Instruciones Esp.'},
                        {field: 'fechaRevalidacion', width: 100, title: 'F. Revalidación'},
                        {field: 'fechaPrevio', width: 145, title: 'F. Previo'},
                        {field: 'fechaPago', width: 145, title: 'F. Pago'},
                        {field: 'fechaLiberacion', width: 145, title: 'F. Liberación'},
                        {field: 'fechaEtaAlmacen', width: 100, title: 'F. ETA Destino'},
                        {field: 'fechaFacturacion', width: 100, title: 'F. Facturación'},
                        {field: 'descripcionPlanta', width: 100, title: 'Planta'}
                    ]]
            });
        }
        if (parseInt(data.context.getElementById('tipoReporte').value) === 6) { // traficos maritimos
            var tipoAduana = 3;
            var datavalues = $("#ff").serialize() + "&tipoAduana=" + tipoAduana;
            dg.edatagrid({
                pagination: true,
                singleSelect: true,
                striped: true,
                rownumbers: true,
                fitColumns: false,
                height: 562,
                method: "get",
                remoteFilter: true,
                url: "/trafico/crud/reporte-traficos?" + datavalues,
                pageSize: 20,
                queryParams: {
                    tipoAduana: tipoAduana
                },
                toolbar: [{
                        text: 'Guardar',
                        iconCls: 'icon-save',
                        handler: function () {
                            exportToExcel(datavalues);
                        }
                    }],
                frozenColumns: [[
                        {field: 'patente', width: 50, title: 'Patente'},
                        {field: 'aduana', width: 50, title: 'Aduana'},
                        {field: 'pedimento', width: 80, title: 'Pedimento'},
                        {field: 'referencia', width: 100, title: 'Referencia'}
                    ]],
                columns: [[
                        {field: 'ie', width: 70, title: 'Tipo Operación'},
                        {field: 'nombre', width: 120, title: 'Usuario'},
                        {field: 'cvePedimento', width: 40, title: 'Cve.'},
                        {field: 'nombreCliente', width: 300, title: 'Nombre Cliente'},
                        {field: 'nombre', width: 120, title: 'Usuario'},
                        {field: 'blGuia', width: 150, title: 'BL', editor: {type: 'text'}},
                        {field: 'contenedorCaja', width: 150, title: 'Contenedor/CS'},
                        {field: 'fechaEta', width: 120, title: 'F. ETA Puerto'},
                        {field: 'nombreAlmacen', width: 100, title: 'Almacen'},
                        {field: 'fechaInstruccionEspecial', width: 140, title: 'F. Instruciones Esp.'},
                        {field: 'fechaRevalidacion', width: 100, title: 'F. Revalidación'},
                        {field: 'fechaPrevio', width: 145, title: 'F. Previo'},
                        {field: 'fechaEntrada', width: 90, title: 'F. Entrada'},
                        {field: 'fechaPago', width: 145, title: 'F. Pago'},
                        {field: 'fechaLiberacion', width: 145, title: 'F. Liberación'},
                        {field: 'fechaEtaAlmacen', width: 100, title: 'ETA Almacen'},
                        {field: 'fechaFacturacion', width: 145, title: 'F. Facturación'},
                        {field: 'carga', width: 145, title: 'Tipo de Carga'},
                        {field: 'descripcionPlanta', width: 145, title: 'Planta'}
                    ]]
            });
        }
        if (parseInt(data.context.getElementById('tipoReporte').value) === 7) { // traficos ops espec
            var tipoAduana = 1;
            var datavalues = $("#ff").serialize() + "&tipoAduana=" + tipoAduana;
            dg.edatagrid({
                pagination: true,
                singleSelect: true,
                striped: true,
                rownumbers: true,
                fitColumns: false,
                height: 562,
                method: "get",
                remoteFilter: true,
                url: "/trafico/crud/reporte-traficos?" + datavalues,
                pageSize: 20,
                queryParams: {
                    tipoAduana: tipoAduana
                },
                toolbar: [{
                        text: 'Guardar',
                        iconCls: 'icon-save',
                        handler: function () {
                            exportToExcel(datavalues);
                        }
                    }],
                frozenColumns: [[
                        {field: 'patente', width: 50, title: 'Patente'},
                        {field: 'aduana', width: 50, title: 'Aduana'},
                        {field: 'pedimento', width: 80, title: 'Pedimento'},
                        {field: 'referencia', width: 100, title: 'Referencia'}
                    ]],
                columns: [[
                        {field: 'ie', width: 70, title: 'Tipo Operación'},
                        {field: 'nombre', width: 120, title: 'Usuario'},
                        {field: 'cvePedimento', width: 40, title: 'Cve.'},
                        {field: 'nombreCliente', width: 300, title: 'Nombre Cliente'},
                        {field: 'nombre', width: 120, title: 'Usuario'},       
                        {field: 'fechaInstruccionEspecial', width: 140, title: 'F. Instruciones Esp.'},
                        {field: 'fechaEnvioProforma', width: 145, title: 'F. Envío Proforma'},
                        {field: 'fechaVistoBueno', width: 145, title: 'F. VoBo'},
                        {field: 'fechaPago', width: 145, title: 'F. Pago'},
                        {field: 'fechaLiberacion', width: 145, title: 'F. Liberación'},
                        {field: 'fechaFacturacion', width: 145, title: 'F. Facturación'},
                        {field: 'carga', width: 150, title: 'Tipo de Carga'}
                    ]]
            });
        }
        if (parseInt(data.context.getElementById('tipoReporte').value) === 8) { // traficos terrestre
            var tipoAduana = 4;
            var datavalues = $("#ff").serialize() + "&tipoAduana=" + tipoAduana;
            dg.edatagrid({
                pagination: true,
                singleSelect: true,
                striped: true,
                rownumbers: true,
                fitColumns: false,
                height: 562,
                method: "get",
                remoteFilter: true,
                url: "/trafico/crud/reporte-traficos?" + datavalues,
                pageSize: 20,
                queryParams: {
                    tipoAduana: tipoAduana
                },
                toolbar: [{
                        text: 'Guardar',
                        iconCls: 'icon-save',
                        handler: function () {
                            exportToExcel(datavalues);
                        }
                    }],
                frozenColumns: [[
                        {field: 'patente', width: 50, title: 'Patente'},
                        {field: 'aduana', width: 50, title: 'Aduana'},
                        {field: 'pedimento', width: 80, title: 'Pedimento'},
                        {field: 'referencia', width: 100, title: 'Referencia'}
                    ]],
                columns: [[
                        {field: 'ie', width: 70, title: 'Tipo Operación'},
                        {field: 'nombre', width: 120, title: 'Usuario'},
                        {field: 'cvePedimento', width: 40, title: 'Cve.'},
                        {field: 'nombreCliente', width: 300, title: 'Nombre Cliente'},
                        {field: 'fechaEta', width: 140, title: 'F. ETA'},
                        {field: 'fechaEntrada', width: 140, title: 'F. Entrada'},
                        {field: 'fechaPresentacion', width: 140, title: 'F. Presentación'},
                        {field: 'fechaInstruccionEspecial', width: 140, title: 'F. Instruciones Esp.', editor: {type: 'datebox'}, options: { required: false, validType:'date' }},
                        {field: 'fechaPrevio', width: 145, title: 'F. Previo', editor: {type: 'datetimebox'}, options: { required: false, validType:'datetime' }},
                        {field: 'fechaPago', width: 145, title: 'F. Pago', editor: {type: 'datetimebox'}, options: {required: false, validType: 'datetimebox'}},
                        {field: 'fechaLiberacion', width: 145, title: 'F. Liberación', editor: {type: 'datetimebox'}, options: { required: false, validType:'datetime' }},
                        {field: 'fechaEtaAlmacen', width: 100, title: 'F. ETA Destino', editor: {type: 'datebox'}, options: { required: false, validType:'date' }},
                        {field: 'fechaFacturacion', width: 100, title: 'F. Facturación'}
                    ]]
            });
        }
        if (parseInt(data.context.getElementById('tipoReporte').value) === 2) {
            var datavalues = $("#ff").serialize();
            dg.edatagrid({
                pagination: true,
                singleSelect: true,
                striped: true,
                rownumbers: true,
                fitColumns: false,
                height: 562,
                method: "get",
                remoteFilter: true,
                url: "/trafico/crud/reporte-traficos?" + datavalues,
                pageSize: 20,
                toolbar: [{
                        text: 'Guardar',
                        iconCls: 'icon-save',
                        handler: function () {
                            exportToExcel(datavalues);
                        }
                    }],
                columns: [[
                        {field: 'numero', width: 100, title: 'Sello'},
                        {field: 'nombreCliente', width: 300, title: 'Cliente'},
                        {field: 'referencia', width: 100, title: 'Trafico'},
                        {field: 'pedimento', width: 100, title: 'Pedimento'},
                        {field: 'fechaPago', width: 110, title: 'Fecha'}
                    ]]
            });
        }
        if (parseInt(data.context.getElementById('tipoReporte').value) === 3) {
            $.messager.alert('Warning','The warning message');
        }
        if (parseInt(data.context.getElementById('tipoReporte').value) === 4) {
            var datavalues = $("#ff").serialize();
            dg.edatagrid({
                pagination: true,
                singleSelect: true,
                striped: true,
                rownumbers: true,
                fitColumns: false,
                height: 562,
                method: "get",
                remoteFilter: true,
                url: "/trafico/crud/reporte-traficos?" + datavalues,
                pageSize: 20,
                toolbar: [{
                        text: 'Guardar',
                        iconCls: 'icon-save',
                        handler: function () {
                            exportToExcel(datavalues);
                        }
                    }],
                columns: [[
                        {field: 'patente', width: 55, title: 'Patente'},
                        {field: 'aduana', width: 55, title: 'Aduana'},
                        {field: 'pedimento', width: 70, title: 'Pedimento'},
                        {field: 'referencia', width: 100, title: 'Referencia'},
                        {field: 'fechaEntrada', width: 150, title: 'Fecha Entrada'},
                        {field: 'fechaEnvioDocumentos', width: 150, title: 'Fecha Envio Documentos'},
                        {field: 'fechaRevalidacion', width: 130, title: 'Fecha Revalidación'},
                        {field: 'fechaPago', width: 100, title: 'Fecha Pago'},
                        {field: 'fechaLiberacion', width: 110, title: 'Fecha Liberación'},
                        {field: 'usuario', width: 110, title: 'Usuario'}
                    ]]
            });
        }
        if (parseInt(data.context.getElementById('tipoReporte').value) === 10) {
            $.messager.alert('Warning','The warning message');
        }
        if (parseInt(data.context.getElementById('tipoReporte').value) === 11) {
            $.messager.alert('Warning','The warning message');
        }
        if (parseInt(data.context.getElementById('tipoReporte').value) === 12) {
            $.messager.alert('Warning','The warning message');
        }
        if (parseInt(data.context.getElementById('tipoReporte').value) === 13) {
            var datavalues = $("#ff").serialize();
            dg.edatagrid({
                pagination: true,
                singleSelect: true,
                striped: true,
                rownumbers: true,
                fitColumns: false,
                height: 562,
                method: "get",
                remoteFilter: true,
                url: "/trafico/crud/reporte-inventarios?" + datavalues,
                pageSize: 20,
                toolbar: [{
                        text: 'Guardar',
                        iconCls: 'icon-save',
                        handler: function () {
                            exportToExcel(datavalues);
                        }
                    }],
                frozenColumns: [[
                        {field: 'patente', width: 50, title: 'Patente'},
                        {field: 'aduana', width: 50, title: 'Aduana'},
                        {field: 'pedimento', width: 80, title: 'Pedimento'},
                        {field: 'referencia', width: 100, title: 'Referencia'}
                    ]],
                columns: [[
                        {field: 'ie', width: 70, title: 'I/E'},
                        {field: 'cvePedimento', width: 40, title: 'Cve.'},
                        {field: 'nombreCliente', width: 300, title: 'Nombre Cliente'},
                        {field: 'contenedorCaja', width: 20, title: 'Contenedor / Caja'},
                        {field: 'fechaPago', width: 110, title: 'Fecha Pago',
                            formatter: function (value, row) {
                                return fecha(value);
                            }},
                        {field: 'fechaLiberacion', width: 110, title: 'Fecha Liberación',
                            formatter: function (value, row) {
                                return fecha(value);
                            }},
                        {field: 'semaforo', width: 100, title: 'Semáforo',
                            formatter: function (value, row) {
                                if (parseInt(value) === 1) {
                                    return 'Verde';                                    
                                } else if(parseInt(value) === 2) {
                                    return 'Rojo';
                                } else {
                                    return '';
                                }
                            }},
                        {field: 'observacionSemaforo', width: 100, title: 'Observación'},
                        {field: 'revisionOperaciones', width: 110, title: 'Expediente',
                            formatter: function (value, row) {
                                if (parseInt(value) === 1) {
                                    return 'Si';                                    
                                } else {
                                    return '';
                                }
                            }}
                    ]]
            });
        }
        if (parseInt(data.context.getElementById('tipoReporte').value) === 14) {
            var datavalues = $("#ff").serialize();
            dg.edatagrid({
                pagination: true,
                singleSelect: true,
                striped: true,
                rownumbers: true,
                fitColumns: false,
                height: 562,
                method: "get",
                remoteFilter: true,
                url: "/trafico/crud/facturacion?" + datavalues,
                pageSize: 20,
                toolbar: [{
                        text: 'Guardar',
                        iconCls: 'icon-save',
                        handler: function () {
                            exportToExcel(datavalues);
                        }
                    }],
                frozenColumns: [[
                        {field: 'patente', width: 50, title: 'Patente'},
                        {field: 'aduana', width: 50, title: 'Aduana'},
                        {field: 'usuario', width: 80, title: 'Usuario'}
                    ]],
                columns: [[
                        {field: 'ene', width: 70, title: 'Enero'},
                        {field: 'feb', width: 70, title: 'Febrero'},
                        {field: 'mar', width: 70, title: 'Marzo'},
                        {field: 'abr', width: 70, title: 'Abril'},
                        {field: 'may', width: 70, title: 'Mayo'},
                        {field: 'jun', width: 70, title: 'Junio'},
                        {field: 'jul', width: 70, title: 'Julio'},
                        {field: 'ago', width: 70, title: 'Agosto'},
                        {field: 'sep', width: 70, title: 'Sept.'},
                        {field: 'oct', width: 70, title: 'Oct.'},
                        {field: 'nov', width: 70, title: 'Nov.'},
                        {field: 'dic', width: 70, title: 'Dic.'},
                        {field: 'total', width: 70, title: 'Total'},
                    ]]
            });
        }
        if (parseInt(data.context.getElementById('tipoReporte').value) === 70) {
            var datavalues = $("#ff").serialize();
            dg.edatagrid({
                pagination: true,
                singleSelect: true,
                striped: true,
                rownumbers: true,
                fitColumns: false,
                height: 562,
                method: "get",
                remoteFilter: true,
                url: "/trafico/crud/reporte-vucem?" + datavalues,
                pageSize: 20,
                toolbar: [{
                        text: 'Guardar',
                        iconCls: 'icon-save',
                        handler: function () {
                            exportToExcel(datavalues);
                        }
                    }],
                frozenColumns: [[
                        {field: 'nombre', width: 220, title: 'Nombre de usuario'},
                        {field: 'sinError', width: 70, title: 'Sin error'},
                        {field: 'conError', width: 70, title: 'Con error'},
                        {field: 'total', width: 70, title: 'Total'}
                    ]],
                columns: [[
                    ]]
            });
        }
        if (parseInt(data.context.getElementById('tipoReporte').value) === 71) {
            var datavalues = $("#ff").serialize();
            dg.edatagrid({
                pagination: true,
                singleSelect: true,
                striped: true,
                rownumbers: true,
                fitColumns: false,
                height: 562,
                method: "get",
                remoteFilter: true,
                url: "/trafico/crud/reporte-vucem?" + datavalues,
                pageSize: 20,
                toolbar: [{
                        text: 'Guardar',
                        iconCls: 'icon-save',
                        handler: function () {
                            exportToExcel(datavalues);
                        }
                    }],
                frozenColumns: [[
                        {field: 'nombre', width: 220, title: 'Nombre de usuario'},
                        {field: 'sinError', width: 70, title: 'Sin error'},
                        {field: 'conError', width: 70, title: 'Con error'},
                        {field: 'total', width: 70, title: 'Total'}
                    ]],
                columns: [[
                    ]]
            });
        }
        if (parseInt(data.context.getElementById('tipoReporte').value) === 72) { // reporte indicadores
            var datavalues = $("#ff").serialize();
            dg.edatagrid({
                pagination: true,
                singleSelect: true,
                striped: true,
                rownumbers: true,
                fitColumns: false,
                height: 562,
                method: "get",
                remoteFilter: true,
                url: "/trafico/crud/reporte-indicadores?" + datavalues,
                pageSize: 20,
                toolbar: [{
                        text: 'Guardar',
                        iconCls: 'icon-save',
                        handler: function () {
                            exportToExcel(datavalues);
                        }
                    }],
                frozenColumns: [[
                        {field: 'patente', width: 60, title: 'Patente'},
                        {field: 'aduana', width: 60, title: 'Aduana'},
                        {field: 'pedimento', width: 80, title: 'Pedimento'},
                        {field: 'referencia', width: 90, title: 'Referencia', formatter: trafficLink},
                        {field: 'cvePedimento', width: 40, title: 'Cve.'}
                    ]],
                columns: [[
                        {field: 'nombreCliente', width: 270, title: 'Nombre Cliente'},
                        {field: 'tipoAduana', width: 140, title: 'Tipo de Aduana'},
                        {field: 'ie', width: 70, title: 'I/E'},
                        {field: 'semaforo', width: 70, title: 'Semaforo', 
                            formatter(value, row){
                                if (value == 1) {
                                    return 'Verde';
                                } else if (value == 2) {
                                    return 'Rojo';
                                } else {
                                    return '';
                                }
                            }},
                        {field: 'observacionSemaforo', width: 150, title: 'Obs. Semaforo'},
                        //{field: 'blGuia', width: 150, title: 'BL/Guía', editor: {type: 'text'}},
                        //{field: 'contenedorCaja', width: 150, title: 'Cont./Caja'},
                        {field: 'descripcionPlanta', width: 150, title: 'Planta'},
                        /*{field: 'revisionOperaciones', width: 90, title: 'Expediente',
                            formatter: function (value, row) {
                                if (parseInt(value) === 1) {
                                    return 'Si';
                                } else {
                                    return '';
                                }
                            }},*/
                        {field: 'fechaEta', width: 140, title: 'Fecha ETA'},
                        {field: 'fechaPago', width: 140, title: 'Fecha Pago'},
                        {field: 'fechaEnvioDocumentos', width: 140, title: 'Fecha Env. Documentos'},
                        {field: 'fechaLiberacion', width: 140, title: 'Fecha Liberación'},
                        {field: 'fechaFacturacion', width: 110, title: 'Fecha Facturación'},
                        {field: 'nombreUsuario', width: 120, title: 'Usuario'},
                        //{field: 'folio', width: 90, title: 'Folio', formatter: invoiceLink},
                        {field: 'fechaFacturacionSica', width: 120, title: 'F. Facturacion Sica'},
                        {field: 'honorarios', width: 90, title: 'Honorarios'},
                        {field: 'justificado', width: 120, title: 'Justificado', 
                            formatter(value, row){
                                if (value == 1) {
                                    return 'Si';
                                } else {
                                    return '';
                                }
                            }},
                        /*{field: 'cumplimientoAdministrativo', width: 100, title: 'Cump. Admin.',
                            formatter: function (value, row) {
                                if (parseInt(value) === 1) {
                                    return 'Si';
                                } else if (parseInt(value) === 0) {
                                    return 'No';
                                } else {
                                    return '';
                                }
                            }},
                        {field: 'cumplimientoOperativo', width: 100, title: 'Cump. Ope.',
                            formatter: function (value, row) {
                                if (parseInt(value) === 1) {
                                    return 'Si';
                                } else if (parseInt(value) === 0) {
                                    return 'No';
                                } else {
                                    return '';
                                }
                            }},*/
                        /*{field: 'cumplimiento', width: 90, title: 'Cumplimiento',
                            formatter: function (value, row) {
                                if (parseInt(value) === 1) {
                                    return 'Si';
                                } else {
                                    return '';
                                }
                            }},*/
                        {field: 'observaciones', width: 250, title: 'Observaciones'},
                        {field: 'ccConsolidado', width: 120, title: 'CC. Consolidado'},
                        {field: 'revisionAdministracion', width: 120, title: 'Rev. Admon.',
                            formatter: function (value, row) {
                                if (parseInt(value) === 1) {
                                    return 'Si';                                    
                                } else {
                                    return '';
                                }
                            }},
                        {field: 'revisionOperaciones', width: 140, title: 'Rev. Operaciones',
                            formatter: function (value, row) {
                                if (parseInt(value) === 1) {
                                    return 'Si';                                    
                                } else {
                                    return '';
                                }
                            }},
                        {field: 'completo', width: 70, title: 'Completo',
                            formatter: function (value, row) {
                                if (parseInt(value) === 1) {
                                    return 'Si';                                    
                                } else {
                                    return '';
                                }
                            }},
                        {field: 'mvhcCliente', width: 120, title: 'MV / HC Cliente',
                            formatter: function (value, row) {
                                if (parseInt(value) === 1) {
                                    return 'Si';                                    
                                } else {
                                    return '';
                                }
                            }},
                        {field: 'mvhcFirmada', width: 120, title: 'Firmada',
                            formatter: function (value, row) {
                                if (parseInt(value) === 1) {
                                    return 'Si';                                    
                                } else {
                                    return '';
                                }
                            }}
                    ]]
            });
        }
        if (parseInt(data.context.getElementById('tipoReporte').value) === 73) { // reporte de mv/hc
            var datavalues = $("#ff").serialize();
            var datavalues = $("#ff").serialize();
            dg.edatagrid({
                pagination: true,
                singleSelect: true,
                striped: true,
                rownumbers: true,
                fitColumns: false,
                height: 562,
                method: "get",
                remoteFilter: true,
                url: "/trafico/crud/reporte-estatus-mvhc?" + datavalues,
                pageSize: 20,
                toolbar: [{
                        text: 'Guardar',
                        iconCls: 'icon-save',
                        handler: function () {
                            exportToExcel(datavalues);
                        }
                    }],
                frozenColumns: [[
                        {field: 'patente', width: 60, title: 'Patente'},
                        {field: 'aduana', width: 60, title: 'Aduana'},
                        {field: 'pedimento', width: 80, title: 'Pedimento'},
                        {field: 'referencia', width: 90, title: 'Referencia'}
                    ]],
                columns: [[
                        {field: 'nombreCliente', width: 270, title: 'Nombre Cliente'},
                        {field: 'revisionAdministracion', width: 120, title: 'Rev. Admon.',
                            formatter: function (value, row) {
                                if (parseInt(value) === 1) {
                                    return 'Si';                                    
                                } else {
                                    return '';
                                }
                            }},
                        {field: 'revisionOperaciones', width: 120, title: 'Rev. Operaciones',
                            formatter: function (value, row) {
                                if (parseInt(value) === 1) {
                                    return 'Si';                                    
                                } else {
                                    return '';
                                }
                            }},
                        {field: 'completo', width: 70, title: 'Completo',
                            formatter: function (value, row) {
                                if (parseInt(value) === 1) {
                                    return 'Si';                                    
                                } else {
                                    return '';
                                }
                            }},
                        {field: 'mvhcCliente', width: 120, title: 'MV / HC N/A',
                            formatter: function (value, row) {
                                if (parseInt(value) === 1) {
                                    return 'Si';                                    
                                } else {
                                    return '';
                                }
                            }},
                        {field: 'mvhcFirmada', width: 120, title: 'Firmada',
                            formatter: function (value, row) {
                                if (parseInt(value) === 1) {
                                    return 'Si';                                    
                                } else {
                                    return '';
                                }
                            }},
                        {field: 'mvhcEnviada', width: 120, title: 'Enviada',
                            formatter: function (value, row) {
                                if (parseInt(value) === 1) {
                                    return 'Si';                                    
                                } else {
                                    return '';
                                }
                            }},
                        {field: 'numGuia', width: 150, title: 'Num. Guía'}
                    ]]
            });
        }
        if (parseInt(data.context.getElementById('tipoReporte').value) === 74) { // reporte de facturacion pendiente
            var datavalues = $("#ff").serialize();
            dg.edatagrid({
                pagination: true,
                singleSelect: true,
                striped: true,
                rownumbers: true,
                fitColumns: false,
                height: 562,
                method: "get",
                remoteFilter: true,
                url: "/trafico/crud/reporte-traficos-sinfacturar?" + datavalues,
                pageSize: 20,
                toolbar: [{
                        text: 'Guardar',
                        iconCls: 'icon-save',
                        handler: function () {
                            exportNoInvoiceToExcel(datavalues);
                        }
                    }],
                frozenColumns: [[
                        {field: 'patente', width: 50, title: 'Patente'},
                        {field: 'aduana', width: 50, title: 'Aduana'},
                        {field: 'pedimento', width: 80, title: 'Pedimento'},
                        {field: 'referencia', width: 100, title: 'Referencia'}
                    ]],
                columns: [[
                        {field: 'ie', width: 70, title: 'I/E'},
                        {field: 'cvePedimento', width: 40, title: 'Cve.'},
                        {field: 'nombreCliente', width: 300, title: 'Nombre Cliente'},
                        {field: 'nombre', width: 300, title: 'Usuario'},
                        {field: 'fechaEta', width: 100, title: 'ETA'},
                        {field: 'fechaNotificacion', width: 140, title: 'F. Notificación'},
                        {field: 'fechaEnvioDocumentos', width: 140, title: 'F. Envio Doctos.'},
                        {field: 'fechaEntrada', width: 140, title: 'F. Entrada'},
                        {field: 'fechaPresentacion', width: 140, title: 'F. Presentación'},
                        {field: 'fechaEnvioProforma', width: 140, title: 'F. Envio Proforma'},
                        {field: 'fechaVistoBueno', width: 140, title: 'F. VoBo'},
                        {field: 'fechaRevalidacion', width: 100, title: 'F. Revalidación'},
                        {field: 'fechaPrevio', width: 145, title: 'F. Previo'},
                        {field: 'fechaPago', width: 145, title: 'F. Pago'},
                        {field: 'fechaLiberacion', width: 145, title: 'F. Liberación'},
                        {field: 'fechaEtaAlmacen', width: 145, title: 'ETA Almacen'},
                        {field: 'fechaFacturacion', width: 100, title: 'F. Facturación'},
                        {field: 'blGuia', width: 150, title: 'BL/Guía'},
                        {field: 'nombreAlmacen', width: 150, title: 'Almacen'},
                        {field: 'descripcionPlanta', width: 150, title: 'Planta'}
                    ]]
            });
        }
        if (parseInt(data.context.getElementById('tipoReporte').value) === 75) { // Reporte tráfico y facturación
            var datavalues = $("#ff").serialize();
            dg.edatagrid({
                pagination: true,
                singleSelect: true,
                striped: true,
                rownumbers: true,
                fitColumns: false,
                height: 562,
                method: "get",
                remoteFilter: true,
                url: "/trafico/crud/reporte-traficos-facturacion?" + datavalues,
                pageSize: 20,
                toolbar: [{
                        text: 'Guardar',
                        iconCls: 'icon-save',
                        handler: function () {
                            exportToExcel(datavalues);
                        }
                    }],
                frozenColumns: [[
                        {field: 'patente', width: 50, title: 'Patente'},
                        {field: 'aduana', width: 50, title: 'Aduana'},
                        {field: 'pedimento', width: 80, title: 'Pedimento'},
                        {field: 'referencia', width: 100, title: 'Referencia',
                            formatter: trafficLink }
                    ]],
                columns: [[
                        {field: 'ie', width: 70, title: 'I/E'},
                        {field: 'cvePedimento', width: 40, title: 'Cve.'},
                        {field: 'nombreCliente', width: 300, title: 'Nombre cliente'},
                        {field: 'blGuia', width: 150, title: 'BL/Guía'},
                        {field: 'nombreBuque', width: 150, title: 'Nom. Buque'},
                        {field: 'folio', width: 90, title: 'Folio', formatter: invoiceLink},
                        {field: 'fechaFacturacion', width: 90, title: 'F. Facturacion'},
                        {field: 'fechaPago', width: 90, title: 'F. Pago'},
                        {field: 'pagoHechos', width: 120, title: 'Pagos hechos', formatter: formatCurrency, align:'right'},
                        {field: 'sinComprobar', width: 120, title: 'G. sin comprobar', formatter: formatCurrency, align:'right'},
                        {field: 'honorarios', width: 90, title: 'Honorarios', formatter: formatCurrency, align:'right'},
                        {field: 'iva', width: 90, title: 'IVA', formatter: formatCurrency, align:'right'},
                        {field: 'subTotal', width: 90, title: 'SubTotal', formatter: formatCurrency, align:'right'},
                        {field: 'pagada', width: 70, title: 'Pagada',
                            formatter: function (value, row) {
                                if (parseInt(value) === 1) {
                                    return 'Si';                                    
                                } else {
                                    return 'No';
                                }
                            }}
                    ]]
            });
        }
        if (parseInt(data.context.getElementById('tipoReporte').value) === 76) { // reporte entrega expedientes
            var datavalues = $("#ff").serialize();
            var datavalues = $("#ff").serialize();
            dg.edatagrid({
                pagination: true,
                singleSelect: true,
                striped: true,
                rownumbers: true,
                fitColumns: false,
                height: 562,
                method: "get",
                remoteFilter: true,
                url: "/trafico/crud/reporte-entrega?" + datavalues,
                pageSize: 20,
                toolbar: [{
                        text: 'Guardar',
                        iconCls: 'icon-save',
                        handler: function () {
                            exportToExcel(datavalues);
                        }
                    }],
                frozenColumns: [[
                        {field: 'patente', width: 60, title: 'Patente'},
                        {field: 'aduana', width: 60, title: 'Aduana'},
                        {field: 'pedimento', width: 80, title: 'Pedimento'},
                        {field: 'referencia', width: 90, title: 'Referencia', formatter: trafficLink}
                    ]],
                columns: [[
                        {field: 'fechaPago', width: 120, title: 'Fecha Pago'},
                        {field: 'revisionAdministracion', width: 120, title: 'Rev. Admon.',
                            formatter: function (value, row) {
                                if (parseInt(value) === 1) {
                                    return 'Si';                                    
                                } else {
                                    return '';
                                }
                            }},
                        {field: 'revisionOperaciones', width: 120, title: 'Rev. Operaciones',
                            formatter: function (value, row) {
                                if (parseInt(value) === 1) {
                                    return 'Si';                                    
                                } else {
                                    return '';
                                }
                            }},
                        {field: 'completo', width: 70, title: 'Completo',
                            formatter: function (value, row) {
                                if (parseInt(value) === 1) {
                                    return 'Si';                                    
                                } else {
                                    return '';
                                }
                            }},
                        {field: 'mvhcCliente', width: 120, title: 'MV / HC Cliente',
                            formatter: function (value, row) {
                                if (parseInt(value) === 1) {
                                    return 'Si';                                    
                                } else {
                                    return '';
                                }
                            }},
                        {field: 'mvhcFirmada', width: 120, title: 'Firmada',
                            formatter: function (value, row) {
                                if (parseInt(value) === 1) {
                                    return 'Si';                                    
                                } else {
                                    return '';
                                }
                            }}
                    ]]
            });
        }
        if (parseInt(data.context.getElementById('tipoReporte').value) === 77) { // Sellos de agentes
            var datavalues = $("#ff").serialize();
            var datavalues = $("#ff").serialize();
            dg.edatagrid({
                pagination: true,
                singleSelect: true,
                striped: true,
                rownumbers: true,
                fitColumns: false,
                height: 562,
                method: "get",
                remoteFilter: true,
                url: "/trafico/crud/sellos?" + datavalues,
                pageSize: 20,
                toolbar: [{
                        text: 'Guardar',
                        iconCls: 'icon-save',
                        handler: function () {
                            exportToExcel(datavalues);
                        }
                    }],
                frozenColumns: [[
                        {field: 'patente', width: 70, title: 'Patente'},
                        {field: 'nombre', width: 350, title: 'Nombre'}
                    ]],
                columns: [[
                        {field: 'valido_desde', width: 90, title: 'Válido desde', formatter: fecha},
                        {field: 'valido_hasta', width: 90, title: 'Válido hasta', formatter: fecha}
                    ]]
            });
        }
        if (parseInt(data.context.getElementById('tipoReporte').value) === 78) { // Sellos de clientes
            var datavalues = $("#ff").serialize();
            var datavalues = $("#ff").serialize();
            dg.edatagrid({
                pagination: true,
                singleSelect: true,
                striped: true,
                rownumbers: true,
                fitColumns: false,
                height: 562,
                method: "get",
                remoteFilter: true,
                url: "/trafico/crud/sellos?" + datavalues,
                pageSize: 20,
                toolbar: [{
                        text: 'Guardar',
                        iconCls: 'icon-save',
                        handler: function () {
                            exportToExcel(datavalues);
                        }
                    }],
                frozenColumns: [[
                        {field: 'rfc', width: 110, title: 'RFC'},
                        {field: 'razon', width: 550, title: 'Razón social'}
                    ]],
                columns: [[
                        {field: 'valido_desde', width: 90, title: 'Válido desde', formatter: fecha},
                        {field: 'valido_hasta', width: 90, title: 'Válido hasta', formatter: fecha}
                    ]]
            });
        }
    }
    return;
}

function clearForm() {
    $('#ff').form('clear');
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

$(document).ready(function () {
    
    $('#fechaInicio').datebox({
        value: (new Date().toString('dd-MMM-yyyy'))
    });
    
    var date = new Date();
    $('#fechaInicio').datebox({value:$.fn.datebox.defaults.formatter(new Date(date.getFullYear(), date.getMonth(), 1))});
    
    $('#fechaFin').datebox({
        value: (new Date().toString('dd-MMM-yyyy'))
    });

});

