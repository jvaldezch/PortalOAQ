
var loadMsg = "<span style=\"font-family: sans-serif; font-size: 12px\">Por favor espere... <div class=\"traffic-icon traffic-loader\"></div></span>";

function removerCliente(idAduana, idCliente) {
    if (window.confirm("¿Está seguro que desea remover este cliente?")) {
        $.ajax({
            url: "/trafico/ajax/remover-cliente",
            type: "post",
            data: {idAduana: idAduana, idCliente: idCliente},
            dataType: "json",
            success: function (res) {
                if (res.success === true) {
                    load("#customers", "/trafico/office/obtener-clientes");
                }
            }
        });
    }
}

function removerConcepto(idAduana, idConcepto) {
    if (window.confirm("¿Está seguro que desea remover este concepto?")) {
        $.ajax({
            url: "/trafico/ajax/remover-concepto",
            type: "post",
            data: {idAduana: idAduana, idConcepto: idConcepto},
            dataType: "json",
            success: function (res) {
                if (res.success === true) {
                    load("#concepts", "/trafico/office/obtener-conceptos");
                }
            }
        });
    }
}
function removerTransporte(idAduana, idTransporte) {
    if (window.confirm("¿Está seguro que desea remover este transporte?")) {
        $.ajax({
            url: "/trafico/ajax/remover-transporte",
            type: "post",
            data: {idAduana: idAduana, idTransporte: idTransporte},
            dataType: "json",
            success: function (res) {
                if (res.success === true) {
                    load("#transportation", "/trafico/office/obtener-transporte");
                }
            }
        });
    }
}

function removerAlmacen(idAduana, idAlmacen) {
    if (window.confirm("¿Está seguro que desea remover este almacen?")) {
        $.ajax({
            url: "/trafico/ajax/remover-almacen",
            type: "post",
            data: {idAduana: idAduana, idAlmacen: idAlmacen},
            dataType: "json",
            success: function (res) {
                if (res.success === true) {
                    load("#storages", "/trafico/office/obtener-almacenes");
                }
            }
        });
    }
}

function removerNaviera(idAduana, idNaviera) {
    if (window.confirm("¿Está seguro que desea remover esta naviera?")) {
        $.ajax({
            url: "/trafico/ajax/remover-naviera",
            type: "post",
            data: {idAduana: idAduana, idNaviera: idNaviera},
            dataType: "json",
            success: function (res) {
                if (res.success === true) {
                    load("#shipping", "/trafico/office/obtener-navieras");
                }
            }
        });
    }
}

function removerContacto(idContacto) {
    if (window.confirm("¿Está seguro que desea remover este contacto?")) {
        $.ajax({
            url: "/trafico/ajax/remover-contacto",
            type: "post",
            data: {id: idContacto},
            dataType: "json",
            success: function (res) {
                if (res.success === true) {
                    load("#contacts", "/trafico/office/obtener-contactos");
                }
            }
        });
    }
}

function cambiarOrden(idConcepto, current, action) {
    var value;
    if (action === 'decrease') {
        if (current === 1) {
            alert("No se puede bajar el valor.");
            return false;
        } else {
            value = current - 1;
        }
    } else if (action === 'increase') {
        value = current + 1;
    }
    $.ajax({
        url: "/trafico/ajax/cambiar-orden",
        type: "post",
        data: {idConcepto: idConcepto, orden: value},
        dataType: "json",
        success: function (res) {
            if (res.success === true) {
                load("#concepts", "/trafico/office/obtener-conceptos");
            }
        }
    });
}

function editarBanco(idAduana, idBanco) {
    $.ajax({
        url: "/trafico/ajax/datos-banco",
        type: "post",
        data: {idAduana: idAduana, idBanco: idBanco},
        dataType: "json",
        success: function (res) {
            if (res.success === true) {
                $("#bank").attr("action", "/trafico/ajax/actualizar-banco");
                $("#addBank").html("Actualizar")
                        .removeClass("btn-success")
                        .addClass("btn-warning");
                $.each(res, function (name, val) {
                    var $el = $('[name="' + name + '"]'), type = $el.attr('type');
                    switch (type) {
                        case "checkbox":
                            $el.attr('checked', 'checked');
                            break;
                        case "select":
                            $el.filter('[value="' + val + '"]').attr('selected', 'selected');
                        case "radio":
                            $el.filter('[value="' + val + '"]').attr('checked', 'checked');
                            break;
                        default:
                            $el.val(val);
                    }
                });
            }
        }
    });
}

function load(id, url) {
    $(id).show();
    $(id).html(loadMsg);
    $.ajax({
        url: url,
        type: "post",
        dataType: "json",
        data: {id: $("#idAduana").val()},
        timeout: 3000,
        success: function (res) {
            if (res.success === true) {
                $(id).html(res.html);
            } else {
                $(id).html(res.html);                
            }
        }
    });
}

function currentActive(current) {
    if (current === "#office-information") {
        load("#contacts", "/trafico/office/obtener-contactos");
    }
    if (current === "#office-customers") {
        load("#customers", "/trafico/office/obtener-clientes");
    }
    if (current === "#office-storages") {
        load("#storages", "/trafico/office/obtener-almacenes");
    }
    if (current === "#office-transportation") {
        load("#transportation", "/trafico/office/obtener-transporte");
    }
    if (current === "#office-shipping") {
        load("#shipping", "/trafico/office/obtener-navieras");
    }
    if (current === "#office-concepts") {
        load("#concepts", "/trafico/office/obtener-conceptos");
    }
    if (current === "#office-banks") {
        load("#banks", "/trafico/office/obtener-bancos");
    }
}

$(document).ready(function () {
    
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
        $("a[href=\"#office-information\"]").tab("show");
        Cookies.set("active", "#office-information");
        currentActive(Cookies.get("active"));
    }

    $("#nombreAlmacen, #nombreTransporte, #nombreNaviera, #descripcion, #sucursal, #razonSocial").on("input", function (evt) {
        var input = $(this);
        var start = input[0].selectionStart;
        $(this).val(function (_, val) {
            return val.toUpperCase();
        });
        input[0].selectionStart = input[0].selectionEnd = start;
    });

    /** CONTACT FORM **/

    $("#form-contacts").validate({
        errorPlacement: function (error, element) {
            $(element)
                    .closest("form")
                    .find("label[for='" + element.attr("id") + "']")
                    .append(error);
        },
        errorElement: "span",
        errorClass: "traffic-error-span",
        rules: {
            nombre: "required",
            email: "required",
            tipoContacto: "required"
        },
        messages: {
            nombre: "[Nombre es necesario]",
            email: "[Email es necesario]",
            tipoContacto: "[Campo necesario]"
        }
    });

    $(document.body).on("click", "#add-contact", function (e) {
        e.preventDefault();
        if ($("#form-contacts").valid()) {
            $("#form-contacts").ajaxSubmit({
                cache: false,
                url: "/trafico/ajax/agregar-contacto",
                type: "post",
                dataType: "json",
                success: function (res) {
                    if (res.success === true) {
                        $('#form-contacts').trigger("reset");
                        load("#contacts", "/trafico/office/obtener-contactos");
                    }
                }
            });
        }
    });
    
    $(document.body).on("change", ".alert", function (e) {
        e.preventDefault();
        if ($(this).is(":checked")) {
            var action = "add";
        } else if ($(this).is(":unchecked")) {
            var action = "remove";
        }
        if (action) {
            $.ajax({ cache: false, url: "/trafico/post/avisos", type: "POST", dataType: "json",
                data: {id: $(this).data("id"), action: action, alert: $(this).data("alert")},
                success: function (res) {
                    if (res.success === true) {
                        $.toast({text: "<strong>Guardado</strong>", bgColor: "green", stack : 3, position : "bottom-right"});
                    }
                }
            });
        }
    });

    /** CUSTOMER FORM **/

    $("#form-customer").validate({
        errorPlacement: function (error, element) {
            $(element)
                    .closest("form")
                    .find("label[for='" + element.attr("id") + "']")
                    .append(error);
        },
        errorElement: "span",
        errorClass: "traffic-error-span",
        rules: {
            idCliente: "required"
        },
        messages: {
            idCliente: "[Debe seleccionar cliente]"
        }
    });

    $(document.body).on("click", "#add-customer", function (e) {
        e.preventDefault();
        if ($("#form-customer").valid()) {
            $("#form-customer").ajaxSubmit({
                cache: false,
                url: "/trafico/ajax/agregar-cliente-aduana",
                type: "post",
                dataType: "json",
                success: function (res) {
                    if (res.success === true) {
                        load("#customers", "/trafico/office/obtener-clientes");
                    }
                }
            });
        }
    });

    /** STORAGE FORM **/

    $("#form-storage").validate({
        errorPlacement: function (error, element) {
            $(element)
                    .closest("form")
                    .find("label[for='" + element.attr("id") + "']")
                    .append(error);
        },
        errorElement: "span",
        errorClass: "traffic-error-span",
        rules: {
            nombreAlmacen: "required"
        },
        messages: {
            nombreAlmacen: "[Es necesario el nombre del almacen]"
        }
    });

    $(document.body).on("click", "#add-storage", function (e) {
        e.preventDefault();
        if ($("#form-storage").valid()) {
            $("#form-storage").ajaxSubmit({
                cache: false,
                url: "/trafico/ajax/nuevo-almacen",
                type: "post",
                dataType: "json",
                success: function (res) {
                    if (res.success === true) {
                        load("#storages", "/trafico/office/obtener-almacenes");
                    }
                    
                }
            });
        }
    });

    /** TRANSPORT FORM **/

    $("#form-transportation").validate({
        errorPlacement: function (error, element) {
            $(element)
                    .closest("form")
                    .find("label[for='" + element.attr("id") + "']")
                    .append(error);
        },
        errorElement: "span",
        errorClass: "traffic-error-span",
        rules: {
            nombreTransporte: "required"
        },
        messages: {
            nombreTransporte: "[Es necesario el nombre del transporte]"
        }
    });

    $(document.body).on("click", "#add-transport", function (e) {
        e.preventDefault();
        if ($("#form-transportation").valid()) {
            $("#form-transportation").ajaxSubmit({
                cache: false,
                url: "/trafico/ajax/nuevo-transporte",
                type: "post",
                dataType: "json",
                success: function (res) {
                    if (res.success === true) {
                        load("#transportation", "/trafico/office/obtener-transporte");
                    }
                }
            });
        }
    });

    /** CONCEPTS FORM **/

    $(document.body).on("change", "#idTipoConcepto", function () {
        if ($(this).val() !== '') {
            $.ajax({
                url: "/trafico/data/obtain-concepts",
                type: "post",
                data: {idTipoConcepto: $(this).val()},
                dataType: "json",
                success: function (res) {
                    if (res.success === true) {
                        $("#conceptos").html(res.html);
                    }
                }
            });
        }
    });

    $("#form-concepts").validate({
        errorPlacement: function (error, element) {
            $(element)
                    .closest("form")
                    .find("label[for='" + element.attr("id") + "']")
                    .append(error);
        },
        errorElement: "span",
        errorClass: "traffic-error-span",
        rules: {
            idTipoConcepto: "required",
            idCuenta: "required",
            concepto: "required"
        },
        messages: {
            idTipoConcepto: "[Seleccione un tipo de concepto]",
            idCuenta: "[Seleccione concepto]",
            concepto: "[Proporcione un nombre para mostrar]"
        }
    });

    $("#add-concept").click(function (e) {
        e.preventDefault();
        if ($("#form-concepts").valid()) {
            $("#form-concepts").ajaxSubmit({
                cache: false,
                url: "/trafico/data/add-new-account",
                type: "post",
                dataType: "json",
                success: function (res) {
                    if (res.success === true) {
                        load("#concepts", "/trafico/office/obtener-conceptos");
                    } else if (res.success === false) {
                        $("#error-conceptos").html(res.message);
                    }
                }
            });
        }
    });

    /***  SHIPPING FORM **/

    $("#form-shipping").validate({
        errorPlacement: function (error, element) {
            $(element)
                    .closest("form")
                    .find("label[for='" + element.attr("id") + "']")
                    .append(error);
        },
        errorElement: "span",
        errorClass: "traffic-error-span",
        rules: {
            nombreNaviera: "required"
        },
        messages: {
            nombreNaviera: "[Es necesario el nombre de la naviera]"
        }
    });

    $("#add-shipper").click(function (e) {
        e.preventDefault();
        if ($("#form-shipping").valid()) {
            $("#form-shipping").ajaxSubmit({
                cache: false,
                url: "/trafico/ajax/nueva-naviera",
                type: "post",
                dataType: "json",
                success: function (res) {
                    if (res.success === true) {
                        load("#shipping", "/trafico/office/obtener-navieras");
                    }
                }
            });
        }
    });
    
    /***  BANK FORM **/

    $("#formBank").validate({
        errorPlacement: function (error, element) {
            $(element)
                    .closest("form")
                    .find("#" + element.attr("id"))
                    .after(error);
        },
        errorElement: "span",
        errorClass: "traffic-error-span",
        rules: {
            descripcion: "required",
            nombreBanco: "required",
            cuenta: "required",
            clabe: "required",
            razonSocial: "required"
        },
        messages: {
            descripcion: "[Es necesario este campo]",
            nombreBanco: "[Es necesario este campo]",
            cuenta: "[Es necesario este campo]",
            clabe: "[Es necesario este campo]",
            razonSocial: "[Es necesario este campo]"
        }
    });
    
    $("#addBank").click(function (e) {
        e.preventDefault();
        if ($("#formBank").valid()) {
            $("#formBank").ajaxSubmit({
                cache: false,
                url: "/trafico/ajax/nuevo-banco",
                type: "post",
                dataType: "json",
                success: function (res) {
                    if (res.success === true) {
                        load("#banks", "/trafico/office/obtener-bancos");
                    }
                }
            });
        }
    });
    
    $(document.body).on("click", ".setDefaultBank", function(){
        $.ajax({
            url: "/trafico/office/cambiar-banco",
            type: "post",
            data: {idAduana: $("#idAduana").val(), idBanco: $(this).data("id")},
            dataType: "json",
            success: function (res) {
                if (res.success === true) {
                }
            }
        });
    });

});