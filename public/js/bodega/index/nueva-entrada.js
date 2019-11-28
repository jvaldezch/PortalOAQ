/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

var jsonData = {};

function llenarDatos(div, arr) {
    $(div).find(".numero span").html(arr.numero);
    $(div).find(".direccion span").html(arr.direccion);
    $(div).find(".ciudad span").html(arr.ciudad);
    $(div).find(".estado span").html(arr.estado);
    $(div).find(".pais span").html(arr.pais);
}

window.editarProveedorModal = function (titulo, idCliente, idProveedor) {
    $.confirm({title: titulo, escapeKey: "cerrar", boxWidth: "450px", useBootstrap: false, type: "blue",
        buttons: {
            guardar: {btnClass: "btn-blue", action: function () {
                    /*if ($("#frmProvider").valid()) {
                        $("#frmProvider").ajaxSubmit({url: "/trafico/facturas/guardar-proveedor", dataType: "json", type: "POST",
                            success: function (res) {
                                if (res.success === true) {
                                    location.replace("/trafico/facturas/editar-factura?idFactura=" + idFactura);
                                }
                            }
                        });
                    } else {
                        return false;
                    }*/
                }},
            cerrar: {action: function () {}}
        },
        content: function () {
            var self = this;
            return $.ajax({url: "/bodega/get/proveedor?idCliente=" + idCliente + "&idProveedor=" + idProveedor + "&tipoOperacion=" + $(".tipoOperacion").val(), dataType: "json", method: "get"
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
};

window.editarProveedor = function () {
    var idCliente = $('#idCliente').val();
    var idProveedor = $('#idProveedor').val();
    editarProveedorModal("Editar proveedor", idCliente, idProveedor);
};

window.nuevoProveedor = function () {
    var idCliente = $('#idCliente').val();
    editarProveedorModal("Nuevo proveedor", idCliente);
};


$(document).ready(function () {

    if (localStorage.getItem("nuevaEntrada") !== null) {
        var string = localStorage.getItem("nuevaEntrada");
        jsonData = jQuery.parseJSON(string);
    }

    if (jsonData) {
        if (jsonData.tipoOperacion) {
            $(".tipoOperacion[value=" + jsonData.tipoOperacion + "]").prop("checked", true);
        }
        if (jsonData.idCliente) {
            $("#idCliente").val(jsonData.idCliente)
                .trigger("change");
        }
    }

    $(document.body).on("change", "#idCliente", function () {
        jsonData.idCliente = $(this).val();
        $.ajax({ url: "/bodega/post/obtener-proveedores", cache: false, dataType: "json", type: "POST",
            data: {idCliente: $(this).val(), tipoOperacion: $(".tipoOperacion").val()},
            success: function (res) {
                if (res.success === true) {
                    if (res.direccion) {
                        llenarDatos(".dataCliente", res.direccion);
                    }
                    if (res.proveedores) {
                        $('#idProveedor').empty().append($("<option />").val('').text("---"));
                        $.each(res.proveedores, function(i, value) {
                            console.log(value);
                            $("#idProveedor").append($("<option />").val(value.id).text(value.text));
                        });
                        $("#idProveedor").removeAttr("disabled");
                    }
                }
            }
        });
    });
    
    $(document.body).on("change", "#idProveedor", function () {
        jsonData.idProveedor = $(this).val();
        if($(this).val() !== "") {
            $("#editProvider").show();
            $.ajax({ url: "/bodega/post/obtener-embarcador", cache: false, dataType: "json", type: "POST",
                data: {idCliente: $("#idCliente").val(), idProveedor: $(this).val(), tipoOperacion: $(".tipoOperacion").val()},
                success: function (res) {
                    if (res.success === true) {
                        if (res.direccion) {
                            llenarDatos(".dataProveedor", res.direccion);
                        }
                        if (res.embarcadores) {
                            /*$('#idProveedor').empty().append($("<option />").val('').text("---"));
                            $.each(res.proveedores, function(i, value) {
                                console.log(value);
                                $("#idProveedor").append($("<option />").val(value.id).text(value.text));
                            });
                            $("#idProveedor").removeAttr("disabled");*/
                        }
                    }
                }
            });
        }
    });

    $(document.body).on("click", "#editProvider", function () {
        editarProveedor();
    });

    $(document.body).on("click", "#addProvider", function () {
        nuevoProveedor();
    });

    $(document.body).on("change", "#idEmbarcador", function () {
        jsonData.idEmbarcador = $(this).val();
    });
    
    $(document.body).on("change", "#idConsignatario", function () {
        jsonData.idConsignatario = $(this).val();
    });

    $(document.body).on("click", ".tipoOperacion", function () {
        jsonData.tipoOperacion = $(this).val();
    });

    $("#form").validate({
        errorPlacement: function (error, element) {
            $(element)
                    .closest("form")
                    .find("#" + element.attr("id"))
                    .after(error);
        },
        errorElement: "span",
        errorClass: "errorlabel",
        rules: {
            idCliente: {required: true},
            fechaEta: {required: true},
            idProveedor: {required: true},
            idEmbarcador: {required: function (element) {
                if($("#noEmbarcador").is(':checked')){
                    return false;
                }
            }},
            idConsignatario: {required: function (element) {
                if($("#noConsignatario").is(':checked')){
                    return false;
                }
            }}
        },
        messages: {
            idCliente: "Debe seleccionar un cliente",
            fechaEta: "Fecha ETA es necesaria",
            idProveedor: "Debe seleccionar un proveedor",
            idEmbarcador: "Debe seleccionar un embarcador",
            idConsignatario: "Debe seleccionar un consignatario"
        }
    });
    
    $(document.body).on("click", "#save", function () {
        localStorage.setItem("nuevaEntrada", JSON.stringify(jsonData));
        if ($("#form").valid()) {
            $("#form").ajaxSubmit({ url: "/bodega/post/nueva-entrada", dataType: "json", timeout: 3000, type: "POST",
                success: function (res) {
                    if (res.success === true) {
                        
                    } else {
                        $.alert({title: "Error", type: "red", content: res.message, boxWidth: "250px", useBootstrap: false});
                    }
                }
            });
            
        }
    });
    
    $("#fechaEta").datepicker({
        calendarWeeks: true,
        autoclose: true,
        language: "es",
        format: "yyyy-mm-dd"
    });

});

