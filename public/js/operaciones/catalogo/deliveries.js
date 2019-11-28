/**
 * programmed by Jvaldez at gmail
 * 2015.may.14
 */

var dg;


window.enviarReferencia = function() {
    var ids = [];
    var rows = dg.datagrid('getSelections');
    for (var i = 0; i < rows.length; i++) {
        ids.push(rows[i].id);
    }
    
};


Date.prototype.yyyymmdd = function () {
    var mm = this.getMonth() + 1; // getMonth() is zero-based
    var dd = this.getDate();

    return [this.getFullYear(),
        (mm > 9 ? '' : '0') + mm,
        (dd > 9 ? '' : '0') + dd
    ].join('-');
};

const formatter = new Intl.NumberFormat('en-US', {
  style: 'currency',
  currency: 'USD',
  minimumFractionDigits: 2
});

window.formatDate = function(value) {
    var date = new Date(value);
    return date.yyyymmdd();
};

window.formatCurrency = function(val, row) {
    return formatter.format(val);
};

window.formatNumber = function(val, row) {
    return parseFloat(val).toFixed(2);
};

$(document).ready(function () {
    
    dg = $("#dg").edatagrid();

    dg.edatagrid({
        pagination: true,
        singleSelect: true,
        striped: true,
        rownumbers: true,
        fitColumns: false,
        pageSize: 20,
        idField: "id",
        method: 'GET',
        url: "/operaciones/get/obtener-deliveries",
        updateUrl: "/operaciones/get/actualizar-carta",
        rowStyler: function (index, row) {
            
        },
        queryParams: {
	},
        onClickRow: function (index, row) {},
        onBeginEdit: function (index, row) {
        },
        onBeforeEdit: function (index, row) {},
        onAfterEdit: function (index, row) {
            
        },
        onCancelEdit: function (index, row) {
            row.editing = false;
            $(this).datagrid("refreshRow", index);
        },
        onAdd: function (index, row) {},
        onRowContextMenu: function (e, index, row) {            
        },
        remoteFilter: true,
        toolbar: [
            {
                text: "Guardar",
                iconCls: "icon-save",
                handler: function () {
                    $("#dg").edatagrid("saveRow");
                }
            },
            {
                text: "Cancelar",
                iconCls: "icon-undo",
                handler: function () {
                    $("#dg").edatagrid("cancelRow");
                }
            },
            {
                text: "Actualizar",
                iconCls: "icon-reload",
                handler: function () {
                    $("#dg").edatagrid("reload");
                }
            }
        ],
        frozenColumns: [
            [               
                {field: "delivery", width: 100, title: "Delivery number"},
                {field: "billDocument", width: 90, title: "Bill document"},
                {field: "billDate", width: 80, title: "Bill date",
                    formatter: function (value, row) {
                                return formatDate(value);
                    }}
            ]
        ],
        columns: [
            [
                {field: "material", width: 120, title: "Material"},
                {field: "customerMaterial", width: 180, title: "Customer material"},
                {field: "description", width: 250, title: "Description"},
                {field: "billQuantity", width: 80, title: "Bill Qty.", align: 'right',
                    formatter: function (value, row) {
                                return formatNumber(value);
                    }},
                {field: "su", width: 50, title: "SU"},
                {field: "unitPrice", width: 100, title: "Unit price", align: 'right',
                    formatter: function (value, row) {
                                return formatCurrency(value);
                    }},
                {field: "net", width: 100, title: "Net", align: 'right',
                    formatter: function (value, row) {
                                return formatCurrency(value);
                    }},
                {field: "currency", width: 50, title: "Curr."},
                {field: "providerName", width: 250, title: "Nom. Proveedor"},
                {field: "poNumber", width: 180, title: "PO Number"},
                {field: "salesDocument", width: 120, title: "Sales document"},
                {field: "gross", width: 100, title: "Gross", align: 'right'},
                {field: "unit", width: 50, title: "Unit"},
                {field: "sorg", width: 100, title: "SOrg"},
                {field: "dstCountry", width: 100, title: "Dest. country"},
                {field: "item", width: 70, title: "Item"},
                {field: "city", width: 100, title: "City"},
                {field: "createdBy", width: 150, title: "Created By"},
                {field: "created", width: 100, title: "On",
                    formatter: function (value, row) {
                                return formatDate(value);
                    }},
                {field: "billTo", width: 100, title: "Bill to"},
                {field: "name", width: 100, title: "Name 2"},
                {field: "address", width: 100, title: "Address"},
                {field: "billt", width: 100, title: "BillT"},
                {field: "street", width: 180, title: "Street"},
                {field: "houseNumber", width: 100, title: "House No."},
                {field: "poBox", width: 100, title: "PO Box"},
                {field: "poBoxCountry", width: 100, title: "PO Box cty"},
                {field: "postalCode", width: 100, title: "Pstl Code"},
                {field: "country", width: 100, title: "Country"},
                {field: "reference", width: 100, title: "Reference"}
            ]
        ]
    });

    dg.edatagrid("enableFilter", []);
    
    $.each(['billDate', 'billQuantity', 'su', 'unitPrice', 'net', 'currency', 'providerName', 'poNumber', 'salesDocument', 'gross', 'unit', 'sorg', 'wun', 'dstCountry', 'item', 'city', 'createdBy', 'created', 'on', 'billTo', 'address', 'billt', 'name', 'street', 'houseNumber', 'poBox', 'poBoxCountry', 'postalCode', 'country', 'reference'], function (index, value) {
        $(".datagrid-editable-input[name='" + value + "']").hide();
    });
    
    $(document.body).on("click", ".fa-pencil-alt", function (ev) {
        ev.preventDefault();
        window.location.href = "/operaciones/catalogo/editar-carta-instrucciones?id=" + $(this).data("id");
    });
    
    $(document.body).on("click", ".fa-trash-alt", function (ev) {
        ev.preventDefault();         
        var id = $(this).data("id");
        $.confirm({ title: "<i class=\"fas fa-exclamation-triangle\"></i> Confirmar", escapeKey: "cerrar", boxWidth: "350px", useBootstrap: false, type: "red",
            buttons: {
                si: {btnClass: "btn-red", action: function () {
                    $.ajax({url: '/operaciones/post/borrar-carta-instruccion', dataType: "json", type: "POST",
                        data: {id :id},
                        success: function (res) {
                            if (res.success === true) {
                                dg.edatagrid("reload");
                                return true;                                
                            }
                        }
                    });
                }},
                no: {action: function () {}}
            },
            content: '<p>¿Está seguro que desea eliminar la carta de instrucción?</p>'
        });
    });
    
    $(document.body).on("click", "#nuevaCarta", function (ev) {
        ev.preventDefault();
        $.confirm({ title: "Nueva carta de instrucciones", escapeKey: "cerrar", boxWidth: "550px", useBootstrap: false, type: "green",
            buttons: {
                agregar: {btnClass: "btn-green", action: function () {
                    if ($("#form_letter").valid()) {

                    }
                    return false;
                }},
                cerrar: {action: function () {}}
            },
            content: function () {
                var self = this;
                return $.ajax({
                    url: "/operaciones/get/nueva-carta",
                    method: "get"
                }).done(function (res) {
                    self.setContent(res.html);
                }).fail(function () {
                    self.setContent("Something went wrong.");
                });
            }
        });
    });
    
    $(document.body).on("click", "#subirFacturas", function (ev) {
        ev.preventDefault();
        $.confirm({ title: "Subir layout de facturas", escapeKey: "cerrar", boxWidth: "550px", useBootstrap: false, type: "green",
            buttons: {
                subir: {btnClass: "btn-green", action: function () {
                    if ($("#layout_upload").valid()) {
                        $("#layout_upload").ajaxSubmit({url: "/operaciones/post/subir-facturas", dataType: "json", type: "POST",
                            beforeSend: function() {
                                
                            },
                            success: function (res) {
                                if (res.success === true) {
                                    
                                }
                            }
                        });
                    }
                    return false;
                }},
                cerrar: {action: function () {}}
            },
            content: function () {
                var self = this;
                return $.ajax({
                    url: "/operaciones/get/subir-facturas",
                    method: "get"
                }).done(function (res) {
                    self.setContent(res.html);
                }).fail(function () {
                    self.setContent("Something went wrong.");
                });
            }
        });
    });
    
    $.each(['edit', 'trash', 'send', 'print', 'creado', 'creadoPor', 'modificada', 'modificadaPor', 'fecha'], function (index, value) {
        $(".datagrid-editable-input[name='" + value + "']").hide();
    });
    
});