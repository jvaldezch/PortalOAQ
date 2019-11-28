/**
 * programmed by Jvaldez at gmail
 * 2015.may.14
 */


Date.prototype.yyyymmdd = function () {
    var mm = this.getMonth() + 1; // getMonth() is zero-based
    var dd = this.getDate();

    return [this.getFullYear(),
        (mm > 9 ? '' : '0') + mm,
        (dd > 9 ? '' : '0') + dd
    ].join('-');
};

window.formatDate = function(value) {
    var date = new Date(value);
    return date.yyyymmdd();
};

const formatter = new Intl.NumberFormat('en-US', {
  style: 'currency',
  currency: 'USD',
  minimumFractionDigits: 2
});

window.formatCurrency = function(val, row) {
    return formatter.format(val);
};

window.formatNumber = function(val, row) {
    return parseFloat(val).toFixed(2);
};

var dg;

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
        url: "/operaciones/get/obtener-carta",
        rowStyler: function (index, row) {            
        },
        queryParams: {
            id: $("#id").val()
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
        frozenColumns: [
            [
                {field: 'trash', width: 32, title: '', align: 'center',
                    formatter: function(val, row){
                        return '<i class="fas fa-trash-alt" data-id="' + row.id + '" style="cursor: pointer"></i>';
                    }},
                {field: "provider", width: 100, title: "Proveedor"},
                {field: "rfcCliente", width: 100, title: "RFC Cliente"},
                {field: "deliveryNumber", width: 100, title: "Delivery no."}
            ]
        ],
        columns: [
            [
                {field: "providerName", width: 280, title: "Cliente"},
                {field: "taxId", width: 80, title: "Tax ID"},
                {field: "cvePedimento", width: 70, title: "Cve. Ped."},
                {field: "incoterm", width: 70, title: "Incoterm"},
                {field: "billDate", width: 100, title: "Fecha Factura",
                    formatter: function (value, row) {
                                return formatDate(value);
                    }},
                {field: "billDocument", width: 100, title: "No de Factura"},
                {field: "material", width: 110, title: "Num. parte"},
                {field: "pieces", width: 80, title: "Piezas"},
                {field: "description", width: 200, title: "Descripción"},
                {field: "billQuantity", width: 60, title: "Piezas"},
                {field: "fraccion", width: 90, title: "Fracción"},
                {field: "vinculacion", width: 50, title: "Vinc."},
                {field: "net", width: 90, title: "Valor USD", align: 'right',
                    formatter: function (value, row) {
                                return formatCurrency(value);
                    }},
                {field: "vinculacion", width: 60, title: "Nafta"},
                {field: "vinculacion", width: 60, title: "Bultos"}, // editable
                {field: "gross", width: 60, title: "Peso Kg", align: 'right',
                    formatter: function (value, row) {
                        if (row.unit === 'KG') {
                            return value;
                        }
                        if (row.unit === 'G') {
                            return parseFloat(value) / 1000;
                        }
                    }}
            ]
        ]
    });
    
    $(document.body).on("input", "#sello, #dirigida, #deliveryNumber", function () {
        var input = $(this);
        var start = input[0].selectionStart;
        $(this).val(function (_, val) {
            return val.toUpperCase();
        });
        input[0].selectionStart = input[0].selectionEnd = start;
    });
    
    $(document.body).on("click", "#add-element", function (ev) {
        ev.preventDefault();
        var id = $("#id").val();
        $.confirm({ title: "Agregar retornable", escapeKey: "cerrar", boxWidth: "550px", useBootstrap: false, type: "green",
            buttons: {
                agregar: {btnClass: "btn-green", action: function () {
                    if ($("#form_part").valid()) {
                        $("#form_part").ajaxSubmit({url: "/operaciones/post/agregar-retornable", dataType: "json", type: "POST",
                            success: function (res) {
                                if (res.success === true) {
                                    dg.edatagrid("reload");
                                } else {
                                    $.alert({title: "Error", type: "red", content: res.message, boxWidth: "350px", useBootstrap: false});
                                    return false;
                                }
                            }
                        });
                    } else {
                        return false;                        
                    }
                }},
                cerrar: {action: function () {}}
            },
            content: function () {
                var self = this;
                return $.ajax({
                    url: "/operaciones/get/nuevo-retornable",
                    method: "get",
                    data: {idCarta: id}
                }).done(function (res) {
                    self.setContent(res.html);
                }).fail(function () {
                    self.setContent("Something went wrong.");
                });
            }
        });
    });
    
    $(document.body).on("click", "#save", function (ev) {
        ev.preventDefault();
        $.ajax({url: '/operaciones/post/guardar-carta-instrucciones', dataType: "json", type: "POST",
            data: {id: $("#id").val(), sello: $("#sello").val(), dirigida: $("#dirigida").val(), fecha: $("#fecha").val()},
            success: function (res) {
                if (res.success === true) {
                    $.toast({text: "<strong>Guardado</strong>", bgColor: "green", stack : 3, position : "bottom-right"});
                    return true;
                }
            }
        });
    });
    
    $(document.body).on("click", ".fa-plus", function (ev) {
        ev.preventDefault();
        $.ajax({url: '/operaciones/post/agregar-delivery', dataType: "json", type: "POST",
            data: {id: $("#id").val(), deliveryNumber: $("#deliveryNumber").val()},
            success: function (res) {
                if (res.success === true) {
                    dg.edatagrid("reload");                    
                    return true;
                } else {
                    
                }
            }
        });
    });

    $("#fecha").datepicker({
        calendarWeeks: true,
        autoclose: true,
        language: "es",
        format: "yyyy-mm-dd"
    });
    
});