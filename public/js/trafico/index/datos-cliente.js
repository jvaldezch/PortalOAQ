/**
 * programmed by Jvaldez at gmail
 * 2015.may.14
 */

function removerContacto(idContacto) {
    if (window.confirm("¿Está seguro que desea remover este contacto?")) {
        $.ajax({url: "/trafico/ajax/remover-contacto-cliente", data: {id: idContacto}, dataType: "json", type: "POST",
            success: function (res) {
                if (res.success === true) {
                    window.location.replace("/trafico/index/datos-cliente?id=" + $("#idCliente").val());
                }
            }
        });
    }
}

function removeCustom(id) {
    console.log("id: " + id);
}

function cargarArchivos() {
    $.ajax({url: "/trafico/post/cargar-archivos-cliente", type: "POST", dataType: "json",
        data: {idCliente: $("#idCliente").val()},
        success: function (res) {
            if (res.success === true) {
                $("#customerFiles").html(res.html);
            }
        }
    });
}
function archivo(id, accion, tipoArchivo, fecha) {
    tipoArchivo = tipoArchivo || null;
    fecha = fecha || null;
    $.post("/trafico/post/editar-archivo-fiscal", {id: id, accion: accion, tipoArchivo: tipoArchivo, fecha: fecha})
            .done(function (res) {
                if (res.success === true) {
                    $("#fileDescription_" + id).html(res.select);
                    $("#icon_" + id).html(res.icons);
                    $("#fileExpiration_" + id).html(res.date);
                }
            });
}

function editarArchivo(id) {
    archivo(id, "edit");
}

function cancelarEdicion(id) {
    archivo(id, "cancel");
}

function guardarArchivo(id) {
    archivo(id, "save", $("#selectFile_" + id).val(), $("#date_" + id).val());
}

function borrarArchivo(id) {
    $.confirm({title: "Confirmar", content: '¿Está seguro de que desea eliminar el archivo?', escapeKey: "cerrar", boxWidth: "250px", useBootstrap: false,
        buttons: {
            si: {
                btnClass: "btn-blue",
                action: function () {
                    $.ajax({url: "/trafico/post/borrar-archivo-cliente", dataType: "json", timeout: 3000, type: "POST",
                        data: {id: id, idCliente: $("#idCliente").val()},
                        success: function (res) {
                            if (res.success === true) {
                                cargarArchivos();
                            }
                        }
                    });
                }
            },
            no: function () {}
        }
    });
}

function currentActive(current) {

    if (Cookies.get("active") === "#customer-information") {
    }
    
    if (Cookies.get("active") === "#customer-documents") {
        cargarArchivos();
    }
    
    if (Cookies.get("active") === "#customer-vucem") {
    }
    
    if (Cookies.get("active") === "#customer-log") {
    }
    
    if (Cookies.get("active") === "#customer-parts") {
        partes();
    }
}

window.paises = function (id) {
    return $.ajax({url: '/trafico/facturas/paises',
        success: function (res) {
            if (res.success === true) {
                for (var i = 0; i < res.result.length; i++) {
                    var row = res.result[i];
                    $(id).append($("<option />").val(row.cve_pais).text(row.cve_pais + ' - ' + row.nombre));
                }
                return true;
            }
        }
    });
};

window.monedas = function () {
    return $.ajax({url: '/trafico/facturas/monedas',
        success: function (res) {
            if (res.success === true) {
                for (var i = 0; i < res.result.length; i++) {
                    var row = res.result[i];
                    $('#divisa').append($("<option />").val(row.codigo).text(row.codigo + ' - ' + row.moneda));
                }
                return true;
            }
        }
    });
};

window.obtenerSellos = function (idCliente) {
    return $.ajax({url: '/trafico/get/obtener-sellos-cliente', type: "GET",
        data: {idCliente: idCliente},
        beforeSend: function () {
            $("#sellos-cliente").html('');
        },
        success: function (res) {
            if (res.success === true) {
                $("#sellos-cliente").html(res.html);
                return true;
            }
            return false;
        }
    });
};

window.obtenerDefault = function (idCliente) {
    return $.ajax({url: '/trafico/get/obtener-sello-default', type: "GET",
        data: {idCliente: idCliente},
        success: function (res) {
            if (res.success === true) {
                $("input[data-id=" + res.id + "]").prop("checked", true);
            }
        }
    });
};

window.actualizarSello = function (id) {
    $.confirm({title: "Editar sello", escapeKey: "cerrar", type: "green", boxWidth: "90%", useBootstrap: false,
        buttons: {
            cerrar: {
                btnClass: "btn-red",
                action: function(){}
            }
        },
        content: function () {
            var self = this;
            return $.ajax({url: "/trafico/get/actualizacion-sello-cliente", dataType: "json", method: "GET",
                data: {id: id, idCliente: $('#idCliente').val()}
            }).done(function (res) {
                var html = "";
                if(res.success === true) {
                    html = res.html;
                }
                self.setContent(html);
            }).fail(function () {
                self.setContent("Something went wrong.");
            });
        }
    });
};

window.descargarPartes = function() {
    window.location.href = "/trafico/facturas/partes-cliente?idCliente=" + $('#idCliente').val() + "&excel=true";
};

var dg;

window.partes = function () {
    
    dg = $('#dg').edatagrid();
    
    dg.edatagrid({
        pagination: true,
        singleSelect: true,
        striped: true,
        rownumbers: true,
        fitColumns: false,
        height: 562,
        method: "get",
        remoteFilter: true,
        url: "/trafico/facturas/partes-cliente",
        pageSize: 20,
        queryParams: {
            idCliente: $('#idCliente').val()
        },
        toolbar: [{
                text: 'Guardar',
                iconCls: 'icon-save',
                handler: function () {
                    descargarPartes();
                }
            }],
        frozenColumns: [[
                {field: 'idPro', width: 50, title: 'ID prov.'},
                {field: 'identificador', width: 120, title: 'Tax Id'},
                {field: 'nombreProveedor', width: 220, title: 'Nom. Proveedor'},
                {field: 'fraccion', width: 80, title: 'Fracción'},
                {field: 'numParte', width: 150, title: 'Num.Parte'}
            ]],
        columns: [[
                {field: 'descripcion', width: 250, title: 'Descripción'},
                {field: 'umc', width: 120, title: 'UMC'},
                {field: 'umt', width: 120, title: 'UMT'},
                {field: 'oma', width: 120, title: 'OMA'},
                {field: 'paisOrigen', width: 120, title: 'País Origen'},
                {field: 'paisVendedor', width: 120, title: 'País Vendedor'}
            ]]
    });
    
};

function editarParteModal(titulo, idProducto) {
    $.confirm({title: titulo, escapeKey: "cerrar", boxWidth: "590px", useBootstrap: false, type: "green",
        buttons: {
            guardar: {btnClass: "btn-green", action: function () {
                    if ($("#frmProduct").valid()) {
                        $("#frmProduct").ajaxSubmit({url: "/trafico/facturas/guardar-parte", dataType: "json", type: "POST",
                            success: function (res) {
                                if (res.success === true) {
                                    productos();
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
            return $.ajax({url: "/trafico/facturas/editar-parte?idProducto=" + idProducto, dataType: "json", method: "get"
            }).done(function (res) {
                var html = "";
                if (res.success === true) {
                    html = res.html;
                }
                self.setContent(html);
            }).fail(function () {
                self.setContent("Something went wrong.");
            });
        }
    });
}

window.editarParte = function (idProducto) {
    editarParteModal("Editar parte", idProducto);
};

$(document).ready(function () {

    $(document.body).on("click", "input[name=esquemaFondo]", function () {
        $.post("/trafico/post/cambiar-esquema-cliente", { idCliente: $("#idCliente").val(), value: $(this).val() }, function (res) {
            console.log(res.success);
        });
    });
    
    $(document.body).on("click", "input[name=tipoCliente]", function () {
        $.post("/trafico/post/cambiar-tipo-cliente", { idCliente: $("#idCliente").val(), value: $(this).val() }, function (res) {
            console.log(res.success);
        });
    });
    
    $(document.body).on("click","input[name=pecaDefault]",function(){
        $.post("/trafico/post/cambiar-peca-cliente", { idCliente: $("#idCliente").val(), value: $(this).val() }, function (res) {
            console.log(res.success);            
        });
    });

    $(document.body).on("focus", ".dateContainer", function () {
        $(".fecha").datepicker({
            calendarWeeks: true,
            autoclose: true,
            language: "es",
            format: "yyyy-mm-dd"
        });
    });

    $.ajax({url: "/webservice/ajax/estatus-web-service", dataType: "json", type: "POST",
        data: {rfc: $("#rfcCliente").val()},
        success: function (res) {
            if (res.success === true) {
                if(res.value === "1") {
                    $('input[name=activateWs]').val([1]);
                } else {
                    $('input[name=activateWs]').val([0]);                    
                }
            } else {
                $('input[name=activateWs]').val([0]);                
            }
        }
    });
    
    $.ajax({url: "/webservice/ajax/estatus-activo-cliente", dataType: "json", type: "POST",
        data: {rfc: $("#rfcCliente").val()},
        success: function (res) {
            if (res.success === true) {
                $('input[name=activate]').val([1]);                
            } else {
                $('input[name=activate]').val([0]);                
            }
        }
    });
    
    
    $(document.body).on("change", "input[name=activate]:radio", function () {
        $.ajax({url: "/webservice/ajax/activar-cliente", dataType: "json", type: "POST",
            data: {value: $(this).val(), rfc: $("#rfcCliente").val()}
        });
    });
    
    $("input[name=activateWs]:radio").change(function () {
        $.ajax({url: "/webservice/ajax/agregar-web-service", dataType: "json", type: "POST",
            data: {value: $(this).val(), rfc: $("#rfcCliente").val()},
            success: function (res) {
                if (res.success === true) {
                    
                }
            }
        });
    });
    
    var arr = [];
    $("#traffic-tabs li a").each(function() {        
        arr.push($(this).attr("href"));
    });
    
    $("#traffic-tabs li a").on("click", function () {
        var href = $(this).attr("href");
        Cookies.set("active", href);
        currentActive(Cookies.get("active"));
    });
    
    if (Cookies.get("active") !== undefined && jQuery.inArray(Cookies.get("active"), arr) !== -1) {
        $("a[href=\"" + Cookies.get("active") + "\"]").tab("show");
        currentActive(Cookies.get("active"));
    } else {
        $("a[href=\"#customer-information\"]").tab("show");
        Cookies.set("active", "#customer-information");
    }
    
    $.validator.addMethod("regex", function(value, element, regexpr) {          
        return regexpr.test(value);
    }, " Caracter no válido.");
    
    $("#formAddress").validate({
        errorPlacement: function (error, element) {
            $(element)
                    .closest("form")
                    .find("#" + element.attr("id"))
                    .after(error);
        },
        errorElement: "span",
        errorClass: "traffic-error",
        rules: {
            razon_soc: {
                required: true,
                regex: /^[-_a-zA-Z0-9ÑñÁÉÍÓÚáéíóú.,& ]+$/
            },
            calle: {
                required: true,
                regex: /^[-_a-zA-Z0-9ÑñÁÉÍÓÚáéíóú., ]+$/
            },
            numext: {required: true},
            estado: {required: true},
            pais: {required: true},
            cp: {
                required: true,
                regex: /^[0-9]+$/
            }
        },
        messages: {
            razon_soc: {
                required: " Campo necesario.",
                regex: " Caracter no válido."
            },
            calle: {
                required: " Campo necesario.",
                regex: " Caracter no válido."
            },
            numext: {required: " Campo necesario."},
            estado: {required: " Campo necesario."},
            pais: {required: " Campo necesario."},
            cp: {
                required: " Campo necesario.",
                regex: " Solo digitos."
            }
        }
    });
    
    $(document.body).on("click", "#updateAddress", function (ev) {
        ev.preventDefault();
        if ($("#formAddress").valid()) {
            $("#formAddress").ajaxSubmit({url: "/trafico/ajax/actualizar-direccion", dataType: "json", timeout: 3000, type: "POST",
                success: function (res) {
                    if (res.success === true) {                        
                        window.location.replace("/trafico/index/datos-cliente?id=" + res.id);
                    }
                }
            });
        }
    });
    
    $("#form-contact").validate({
        errorPlacement: function (error, element) {
            $(element)
                    .closest("form")
                    .find("label[for='" + element.attr("id") + "']")
                    .append(error);
        },
        errorElement: "span",
        rules: {
            nombre: {required: true},
            email: {
                required: true,
                email: true
            },
            tipoContacto: {required: true}
        },
        messages: {
            nombre: " Proporcionar nombre.",
            email: " Proporcionar un email válido.",
            tipoContacto: " Seleccionar tipo de contacto."
        }
    });
    
    $("#formFiles").validate({
        errorPlacement: function (error, element) {
            $(element)
                    .closest("form")
                    .find("#" + element.attr("id"))
                    .after(error);
        },
        errorElement: "span",
        errorClass: "traffic-error",
        rules: {
            'file[]': {
                required: true
            }
        },
        messages: {
            'file[]': {
                required: "No ha seleccionado un archivo"
            }
        }
    });
    
    var bar = $(".bar");
    var percent = $(".percent");
    
    $.validator.addMethod("regx", function(value, element, regexpr) {          
        return regexpr.test(value);
    }, " Caracter no válido.");
    
    $("#formAccess").validate({
        errorPlacement: function (error, element) {
            $(element)
                    .closest("form")
                    .find("#" + element.attr("id"))
                    .after(error);
        },
        errorElement: "span",
        errorClass: "traffic-error",
        rules: {
            password: { required: "#webaccess:checked" },
        },
        messages: {
            password: { required: "Necesario" },
        }
    });
    
    $(document.body).on("click", "#saveAccess", function (ev) {
        ev.preventDefault();
        if ($("#formAccess").valid()) {
            $("#formAccess").ajaxSubmit({
                url: "/trafico/post/actualizar-cliente-acceso",
                type: "post",
                dataType: "json",
                success: function (res) {
                    if (res.success === true) {
                        $.toast({text: "<strong>Guardado</strong>", bgColor: "green", stack : 3, position : "bottom-right"});
                    }
                }
            });
        }
    });
    
    $(document.body).on("click", "#uploadFiles", function (ev) {
        ev.preventDefault();
        if ($("#formFiles").valid()) {
            $("#formFiles").ajaxSubmit({dataType: "json", type: "POST",
                beforeSend: function () {
                    var percentVal = "0%";
                    bar.width(percentVal);
                    percent.html(percentVal);
                },
                uploadProgress: function (event, position, total, percentComplete) {
                    var percentVal = percentComplete + "%";
                    bar.width(percentVal);
                    percent.html(percentVal);
                },
                success: function (res) {
                    $("#file").val("");
                    setTimeout(function(){ bar.width(0); percent.html(0); }, 500);
                    if (res.success === true) {
                        cargarArchivos();
                    }
                }
            });
        }
    });
    
    cargarArchivos();
    
    $(document.body).on("click", "#submit", function (ev) {
        ev.preventDefault();
        $(this).prop("disabled", true)
                .addClass("disabled");
        if ($("#form-contact").valid()) {
            $("#form-contact").ajaxSubmit({dataType: "json", timeout: 3000, type: "POST",
                success: function (res) {
                    if (res.success === true) {                        
                        window.location.replace("/trafico/index/datos-cliente?id=" + res.id);
                    }
                }
            });
        } else {
            $(this).removeProp("disabled")
                    .removeClass("disabled");
        }
    });
    
    $(document.body).on("click", "input[name=vucem]", function (ev) {
        $.ajax({url: "/trafico/post/establecer-default", dataType: "json", type: "POST",
            data: {idCliente: $("#idCliente").val(), idSello: $(this).data("id"), tipo: $(this).data("type")},
            success: function (res) {
                if (res.success === true) {
                } else {
                    $.alert({title: "<strong>Error</strong>", closeIcon: true, backgroundDismiss: true, type: "red", escapeKey: "cerrar", boxWidth: "350px", useBootstrap: false, content: res.message});
                }
            }
        });
    });

    $(document.body).on("input", "#nombre, #razon_soc, #calle, #numint, #numext, #colonia, #localidad, #municipio, #ciudad, #estado, #pais", function () {
        var input = $(this);
        var start = input[0].selectionStart;
        $(this).val(function (_, val) {
            return val.toUpperCase();
        });
        input[0].selectionStart = input[0].selectionEnd = start;
    });
    
    /** CONTACTS FORM **/
    
    $(document.body).on("click", "#addContact", function (e) {
        $.confirm({title: "Agregar contacto", escapeKey: "cerrar", type: 'blue', boxWidth: "650px", useBootstrap: false,
            buttons: {
                cerrar: {
                    action: function () {}
                },
                guardar: {
                    btnClass: "btn-blue",
                    action: function () {
                        if ($("#formContacts").valid()) {
                            $("#formContacts").ajaxSubmit({url: "/trafico/ajax/agregar-contacto-cliente", cache: false, dataType: "json", type: "POST",
                                success: function (res) {
                                    if (res.success === true) {
                                        window.location.replace("/trafico/index/datos-cliente?id=" + res.id);
                                    }
                                }
                            });
                        } else {
                            return false;
                        }
                    }
                }
            },
            content: function () {
                var self = this;
                return $.ajax({url: "/trafico/get/agregar-contacto?idCliente=" + $("#idCliente").val(), dataType: "json", method: "GET"
                }).done(function (res) {
                    var html = "";
                    if(res.success === true) {
                        html = res.html;
                    }
                    self.setContent(html);
                }).fail(function () {
                    self.setContent("Something went wrong.");
                });
            }
        });
    });
    
    $(document.body).on("change", ".alert", function () {
        if ($(this).is(":checked")) {
            var action = "add";
        } else if ($(this).is(":unchecked")) {
            var action = "remove";
        }
        if (action) {
            $.ajax({url: "/trafico/ajax/avisos-cliente", cache: false, dataType: "json", type: "POST",
                data: {id: $(this).data("id"), action: action, alert: $(this).data("alert")},
                success: function (res) {
                    if (res.success === true) {
                        window.location.replace("/trafico/index/datos-cliente?id=" + $("#idCliente").val());
                    }
                }
            });
        }
    });
    
    $("#checklistHelp").jqm({
        ajax: "@href",
        modal: true,
        trigger: "#help"
    });
    
    $("#checklistModal").jqm({
        ajax: "@href",
        modal: true,
        trigger: "#checklist"
    });
    
    $("#addNewCustom").jqm({
        ajax: "/trafico/get/agregar-aduana-cliente?id=" + $("#idCliente").val(),
        modal: true,
        trigger: "#newCustom"
    });
    
    $(document.body).on("click", "#closeModal", function (ev) {
        $("#checklistHelp").jqmHide();
    });
    
    $(document.body).on("click", ".reloadCertificate", function (ev) {
        ev.preventDefault();
        var id = $(this).data("id");
        $.ajax({url: "/trafico/get/actualizar-sello", cache: false, dataType: "json", type: "GET",
            data: {id: id},
            success: function (res) {
                if (res.success === true) {
                    $(".reloadCertificate[data-id=" + id + "]").replaceWith(res.html);
                }
            }
        });
    });
    
    $(document.body).on("click", "#help", function (ev) {
        ev.preventDefault();
    });
    
    $(document.body).on("click", ".editarPlanta", function (ev) {
        var idPlanta = $(this).data("id");
        var idCliente = $(this).data("cliente");
        $.confirm({
            title: "<strong>Nueva Planta</strong>", escapeKey: "cerrar", boxWidth: "500px", useBootstrap: false,
            buttons: {
                guardar: { btnClass: "btn-blue",                    
                    action: function(){
                        var form = "#formNewPlant";
                        if (!$(form).valid()) {
                            return false;
                        } else {
                            $(form).ajaxSubmit({dataType: "json", type: "POST",
                                success: function (res) {
                                    if (res.success === true) {
                                        window.location.href = "/trafico/index/datos-cliente?id=" + idCliente;
                                    } else {
                                        $.alert({title: "<strong>Error</strong>", closeIcon: true, backgroundDismiss: true, type: "red", escapeKey: "cerrar", boxWidth: "350px", useBootstrap: false, content: res.message});
                                    }
                                }
                            });
                        }
                    }                    
                },
                cerrar: {btnClass: "btn-red", action: function(){}}
            },
            content: function () {
                var self = this;
                return $.ajax({url: "/trafico/get/nueva-planta?idCliente=" + idCliente + "&idPlanta=" + idPlanta, dataType: "json", method: "GET"
                }).done(function (res) {
                    var html = "";
                    if(res.success === true) {html = res.html;}
                    self.setContent(html);
                }).fail(function () { self.setContent("Something went wrong."); });
            }
        });
    });
    
    $(document.body).on("click", ".borrarPlanta", function (ev) {
        var idPlanta = $(this).data("id");
        var idCliente = $(this).data("cliente");
        console.log(idPlanta + " cliente " + idCliente);
        $.confirm({
            title: "<strong>Confirmar</strong>", escapeKey: "cerrar", boxWidth: "350px", useBootstrap: false, type: 'red',
            buttons: {
                si: {btnClass: "btn-blue",
                    action: function () {
                        $.ajax({url: "/trafico/post/planta-borrar", type: "POST", cache: false, dataType: "json",
                            data: {idCliente: idCliente, idPlanta: idPlanta},
                            success: function (res) {
                                if (res.success === true) {
                                    window.location.href = "/trafico/index/datos-cliente?id=" + idCliente;
                                } else {
                                    $.alert({title: "<strong>Error</strong>", closeIcon: true, backgroundDismiss: true, type: "red", escapeKey: "cerrar", boxWidth: "350px", useBootstrap: false, content: res.message});
                                }
                            }
                        });
                    }
                },
                no: {action: function () {}}
            },
            content: "¿Está seguro que desea borrar la planta?"
        });
    });
    
    $(document.body).on("click", "#nuevaPlanta", function (ev) {
        var idCliente = $(this).data("id");
        $.confirm({
            title: "<strong>Nueva Planta</strong>", escapeKey: "cerrar", boxWidth: "500px", useBootstrap: false,
            buttons: {
                guardar: { btnClass: "btn-blue",                    
                    action: function(){
                        var form = "#formNewPlant";
                        if (!$(form).valid()) {
                            return false;
                        } else {
                            $(form).ajaxSubmit({dataType: "json", type: "POST",
                                success: function (res) {
                                    if (res.success === true) {
                                        window.location.href = "/trafico/index/datos-cliente?id=" + idCliente;
                                    } else {
                                        $.alert({title: "<strong>Error</strong>", closeIcon: true, backgroundDismiss: true, type: "red", escapeKey: "cerrar", boxWidth: "350px", useBootstrap: false, content: res.message});
                                    }
                                }
                            });
                        }
                    }                    
                },
                cerrar: {btnClass: "btn-red", action: function(){}}
            },
            content: function () {
                var self = this;
                return $.ajax({url: "/trafico/get/nueva-planta?idCliente=" + idCliente, dataType: "json", method: "GET"
                }).done(function (res) {
                    var html = "";
                    if(res.success === true) {html = res.html;}
                    self.setContent(html);
                }).fail(function () { self.setContent("Something went wrong."); });
            }
        });
    });
    
    $(document.body).on("click", "#nuevaTarifa", function (ev) {
        ev.preventDefault();
        localStorage.removeItem("tarifa");
        window.location.replace($(this).attr("href"));
    });
    
    $(document.body).on("click", "#upload", function () {
        var id = $(this).data("id");
        var idCliente = $("#idCliente").val();
        $.confirm({title: "Tarifa firmada", escapeKey: "cerrar", boxWidth: "500px", useBootstrap: false,
            buttons: {
                subir: {
                    text: "Subir tarifa",
                    btnClass: "btn-blue",                    
                    action: function(){
                        if (!$("#formTemplate").valid()) {
                            return false;
                        } else {
                            $("#formTemplate").ajaxSubmit({dataType: "json", type: "POST",
                                success: function (res) {
                                    window.location.href = "/trafico/index/datos-cliente?id=" + idCliente;
                                }
                            });
                        }
                    }                    
                },
                cerrar: {
                    btnClass: "btn-red",
                    action: function(){}
                }
            },
            content: function () {
                var self = this;
                return $.ajax({url: "/trafico/get/subir-tarifa-firmada?idCliente=" + idCliente + "&idTarifa=" + id, dataType: "json", method: "GET"
                }).done(function (res) {
                    var html = "";
                    if(res.success === true) {
                        html = res.html;
                    }
                    self.setContent(html);
                }).fail(function () {
                    self.setContent("Something went wrong.");
                });
            }
        });
    });
    
    $(document.body).on("click", ".openFile", function (ev) {
        ev.preventDefault();        
        var id = $(this).data("id");
        window.open("/archivo/get/ver-archivo-cliente?id=" + id, "viewFile", "toolbar=0,location=0,menubar=0,height=550,width=880,scrollbars=yes");
    });
    
    $(document.body).on("click", "#uploadKey", function (ev) {
        ev.preventDefault();
        var idCliente = $("#idCliente").val();
        $.confirm({title: "Nuevo sello", escapeKey: "cerrar", type: "green", boxWidth: "750px", useBootstrap: false,
            buttons: {
                subir: {
                    text: "Subir sello",
                    btnClass: "btn-green",                    
                    action: function(){
                        

                        if ($("#formUploadKey").valid()) {
                            $("#formUploadKey").ajaxSubmit({dataType: "json", type: "POST",
                                url: "/trafico/post/subir-sello",
                                beforeSend: function() {
                                    $.LoadingOverlay("show", {color: "rgba(255, 255, 255, 0.9)"});
                                },
                                success: function (res) {
                                    $.LoadingOverlay("hide");
                                    if (res.success == true) {
                                        window.location.replace("/trafico/index/datos-cliente?id=" + $("#idCliente").val());
                                    } else {
                                        $.each(res.messages, function(key, value ) {
                                            if (value.error === 'ws') {
                                                validator.showErrors({
                                                    "pwdws": value.message
                                                });
                                            }
                                            if (value.error === 'vu') {
                                                validator.showErrors({
                                                    "pwdvu": value.message
                                                });
                                            }
                                        });
                                    }
                                    return false;
                                }
                            });
                            return false;
                        } else {
                            return false;
                        }
                    }                    
                },
                cerrar: {
                    btnClass: "btn-red",
                    action: function(){}
                }
            },
            content: function () {
                var self = this;
                return $.ajax({url: "/trafico/get/subir-sello?idCliente=" + idCliente, dataType: "json", method: "GET"
                }).done(function (res) {
                    var html = "";
                    if(res.success === true) {
                        html = res.html;
                    }
                    self.setContent(html);
                }).fail(function () {
                    self.setContent("Something went wrong.");
                });
            }
        });
    });

    $.when( obtenerSellos($("#idCliente").val()) ).done(function( res ) {
        if (res.success === true) {
            obtenerDefault($("#idCliente").val());
        }
    });
    
});