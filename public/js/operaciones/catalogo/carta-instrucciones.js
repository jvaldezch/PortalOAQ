/**
 * programmed by Jvaldez at gmail
 * 2015.may.14
 */

var dg;

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

window.enviarReferencia = function() {
    var ids = [];
    var rows = dg.datagrid('getSelections');
    for (var i = 0; i < rows.length; i++) {
        ids.push(rows[i].id);
    }
    /*if (ids.length >= 1) {
        $.ajax({url: "/automatizacion/terminal/enviar-repositorio?ids=" + ids, type: "get", dataType: "json", timeout: 3000,
            success: function (res) {
                if (res.success === true) {
                    dg.edatagrid('reload');
                }
            }
        });
    } else {
        $.messager.alert('Advertencia', 'Usted no ha seleccionado ningun registros.');
    }*/
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
        url: "/operaciones/get/obtener-cartas",
        updateUrl: "/operaciones/get/actualizar-carta",
        rowStyler: function (index, row) {
            
        },
        queryParams: {
	},
        onClickRow: function (index, row) {
        },
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
                {field: 'ck', checkbox: true, hidden: false},
                {field: 'edit', width: 24, title: '', 
                    formatter: function(val, row){
                        return '<i class="fas fa-pencil-alt" data-id="' + row.id + '" style="cursor: pointer"></i>';
                    }},
                {field: 'trash', width: 24, title: '', 
                    formatter: function(val, row){
                        return '<i class="fas fa-trash-alt" data-id="' + row.id + '" style="cursor: pointer"></i>';
                    }},
                {field: 'send', width: 24, title: '', 
                    formatter: function(val, row){
                        return '<i class="fas fa-paper-plane" data-id="' + row.id + '" style="cursor: pointer"></i>';
                    }},
                {field: 'print', width: 24, title: '', 
                    formatter: function(val, row){
                        return '<i class="fas fa-print" data-id="' + row.id + '" style="cursor: pointer"></i>';
                    }},
                {field: "numCarta", width: 120, title: "Num. carta"},
                {field: "sello", width: 150, title: "Sello"}
            ]
        ],
        columns: [
            [
                {field: "patente", width: 60, title: "Patente"},
                {field: "aduana", width: 60, title: "Aduana"},
                {field: "pedimento", width: 80, title: "Pedimento"},
                {field: "referencia", width: 80, title: "Referencia"},
                {field: "dirigida", width: 200, title: "Dirigida a"},
                {field: "fecha", width: 100, title: "Fecha de carta",
                    formatter: function (value, row) {
                        if (value) {
                            return formatDate(value);
                        }
                    }},
                {field: "creado", width: 100, title: "Creada",
                    formatter: function (value, row) {
                        if (value) {
                            return formatDate(value);
                        }
                    }},
                {field: "creadoPor", width: 100, title: "Creada por"},
                {field: "modificada", width: 130, title: "Modificada"},
                {field: "modificadaPor", width: 100, title: "Modificada por"}
            ]
        ]
    });

    dg.edatagrid("enableFilter", []);
    
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
                        $("#form_letter").ajaxSubmit({url: "/operaciones/post/nueva-carta-instrucciones", dataType: "json", type: "POST",
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
    
    $(document.body).on("click", ".fa-paper-plane", function (ev) {
        ev.preventDefault();
        var id = $(this).data("id");
        $.confirm({ title: "Enviar a tráfico", escapeKey: "cerrar", boxWidth: "550px", useBootstrap: false, type: "blue",
            buttons: {
                confirmar: {btnClass: "btn-blue", action: function () {
                    
                }},
                cerrar: {action: function () {}}
            },
            content: function () {
                var self = this;
                return $.ajax({
                    url: "/operaciones/get/enviar-carta",
                    method: "get"
                }).done(function (res) {
                    self.setContent(res.html);
                }).fail(function () {
                    self.setContent("Something went wrong.");
                });
            }
        });
    });
    
    $(document.body).on("click", ".fa-print", function (ev) {
        ev.preventDefault();
        var id = $(this).data("id");
        $.confirm({ title: "Imprimir", escapeKey: "cerrar", boxWidth: "550px", useBootstrap: false, type: "green",
            buttons: {
                imprimir: {btnClass: "btn-green", action: function () {
                    
                }},
                cerrar: {action: function () {}}
            },
            content: function () {
                var self = this;
                return $.ajax({
                    url: "/operaciones/get/imprimir-carta",
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
                            success: function (res) {
                                if (res.success === true) {
                                    $.toast({text: "<strong>Guardado</strong>", bgColor: "green", stack : 3, position : "bottom-right"});
                                    return true;
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
    
    $(document.body).on("click", "#subirCatalogo", function (ev) {
        ev.preventDefault();
        $.confirm({ title: "Subir layout de facturas", escapeKey: "cerrar", boxWidth: "550px", useBootstrap: false, type: "green",
            buttons: {
                subir: {btnClass: "btn-green", action: function () {
                    if ($("#catalog_upload").valid()) {
                        $("#catalog_upload").ajaxSubmit({url: "/operaciones/post/subir-catalogo", dataType: "json", type: "POST",
                            success: function (res) {
                                if (res.success === true) {
                                    $.toast({text: "<strong>Guardado</strong>", bgColor: "green", stack : 3, position : "bottom-right"});
                                    return true;
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