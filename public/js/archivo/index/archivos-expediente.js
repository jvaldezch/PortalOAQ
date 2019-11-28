/**
 * programmed by Jvaldez at gmail
 * 2015.may.14
 */

var url = "";
var validacion = false;

function abrirArchivo(href) {
    window.open(href, "_blank", "toolbar=0,location=0,menubar=0,height=550,width=800,scrollbars=yes");
}

function descargarArchivo(href) {
    location.href = href;
}

function actualizarReferencia(patente, aduana, referencia) {
    $.ajax({
        url: "/archivo/ajax/actualizar-datos-expediente",
        type: "post",
        dataType: "json",
        data: {referencia: referencia, patente: patente, aduana: aduana},
        timeout: 9000,
        success: function (res) {
            if (res.success === true) {
                $("#pedimento").val(res.pedimento);
                $("#rfc_cliente").val(res.rfcCliente);
                mostrarChecklist();
            } else if (res.success === false) {
                $("#errors").html(res.html)
                        .show();
            }
        }
    });
}

function mostrarChecklist() {
    $("#checklist").attr("href", "/archivo/data/checklist?patente=" + $("#patente").val() + "&aduana=" + $("#aduana").val() + "&pedimento=" + $("#pedimento").val() + "&referencia=" + $("#referencia").val())
            .show();
    cargarValidacion($("#patente").val(), $("#aduana").val(), $("#pedimento").val());
}

function sendToFtp(rfcCliente, referencia) {
    $.get("/automatizacion/index/envio-expedientes", {referencia: referencia, rfc: rfcCliente}, function (res) {
        if (res.success === true) {
            alert("La referencia se ha enviado, por favor activar el Worker");
        } else {
            alert(res.message);
        }
    });
}

function cargarArchivos(patente, aduana, referencia) {
    $.ajax({
        url: "/archivo/ajax/archivos-expediente",
        type: "post",
        data: {patente: patente, aduana: aduana, referencia: referencia},
        dataType: "json",
        success: function (res) {
            if (res.success === true) {
                $("#files").html(res.html);
                if ($("#pedimento").val() !== "") {
                    mostrarChecklist();
                }
            }
            if ($("#loadExternal").val() === "true") {
                actualizarReferencia(patente, aduana, referencia);
            }
        }
    });
}

function cargarValidacion(patente, aduana, pedimento) {
    if(validacion === false) {
        validacion = true;
        $.ajax({
            url: "/archivo/ajax/archivos-validacion",
            type: "post",
            data: {patente: patente, aduana: aduana, pedimento: pedimento},
            dataType: "json",
            success: function (res) {
                if (res.success === true) {
                    $(".traffic-table tr:last").after(res.html);
                }
            }
        });
    }
}

$(document).ready(function () {

    var bar = $(".bar");
    var percent = $(".percent");

    $(document.body).on("change", "#file", function () {
        var percentVal = "0%";
        bar.width(percentVal);
        percent.html(percentVal);
    });

    $(document.body).on("click", "#delete", function (ev) {
        ev.preventDefault();
        var r = confirm("¿Desea borrar la referencia?");
        var arr = [];
        if (r === true) {
            $(".hover").each(function (index, item) {
                arr.push($(item).data("id"));
            });
            $.ajax({
                url: "/archivo/ajax/borrar-expediente",
                type: "post",
                cache: false,
                dataType: "json",
                data: {patente: $("#patente").val(), aduana: $("#aduana").val(), referencia: $("#referencia").val(), arr: arr},
                success: function (res) {
                    if (res.success === true) {
                        window.location.replace("/archivo/index/referencias");
                    }
                }
            });
        }
    });

    $.validator.addMethod("regx", function (value, element, regexpr) {
        return regexpr.test(value);
    }, "RFC no es válido.");

    $("#form-headers").validate({
        errorPlacement: function (error, element) {
            $(element)
                    .closest("form")
                    .find("#" + element.attr("id"))
                    .after(error);
        },
        errorElement: "span",
        errorClass: "traffic-error",
        rules: {
            pedimento: {
                required: true,
                minlength: 7,
                digits: true
            },
            referencia: {
                required: true,
                minlength: 6
            },
            rfc_cliente: {
                required: true,
                regx: /^[A-Z]{3,4}([0-9]{2})([0-9]{2})([0-9]{2})?[A-Z0-9]{3,4}/
            }
        },
        messages: {
            pedimento: {
                required: "Proporcionar el pedimento",
                minlength: "Minimo 7 digitos",
                digits: "Pedimento deben ser solo números"
            },
            referencia: {
                required: "Proporcionar referencia",
                minlength: "Minimo 6 digitos"
            },
            rfc_cliente: {
                required: "Proporcionar el RFC del cliente"
            },
            file: {
                required: "No ha seleccionado un archivo"
            }
        }
    });

    $(document.body).on("click", "#submit", function (ev) {
        ev.preventDefault();
        if ($("#form-headers").valid()) {
            $("#form-headers").ajaxSubmit({
                type: "post",
                dataType: "json",
                url: "/archivo/data/upload-files",
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
                    setTimeout(function(){ bar.width(0); percent.html(0); }, 500);
                    if (res.success === true) {
                        $("#file").val("");
                        cargarArchivos($("#patente").val(), $("#aduana").val(), $("#referencia").val());
                    }
                }
            });
        }
    });

    $("#update-reference").one("click", function (e) {
        e.preventDefault();
        if (window.confirm("Esta opción modifica todos los datos del expediente: ¿Está seguro que desea continuar?")) {
            $(this).prop("disabled", true)
                    .addClass("disabled");
            $.ajax({
                url: "/archivo/data/actualizar-referencia",
                type: "post",
                cache: false,
                dataType: "json",
                data: {patente: $("#patente").val(), aduana: $("#aduana").val(), pedimento: $("#pedimento").val(), referencia: $("#referencia").val(), rfc: $("#rfc_cliente").val()},
                success: function (res) {
                    if (res.success === true) {
                        window.location.replace("/archivo/index/archivos-expediente?ref=" + $("#referencia").val() + "&patente=" + $("#patente").val() + "&aduana=" + $("#aduana").val());
                    }
                }
            });
        } else {
            console.log("You choose no!");
        }
    });

    $(document.body).on("input", "#referencia, #rfc_cliente", function (ev) {
        var input = $(this);
        var start = input[0].selectionStart;
        $(this).val(function (_, val) {
            return val.toUpperCase();
        });
        input[0].selectionStart = input[0].selectionEnd = start;
    });

    $("#checklistModal").jqm({
        ajax: "@href",
        modal: true,
        trigger: "#checklist"
    });

    cargarArchivos($("#patente").val(), $("#aduana").val(), $("#referencia").val());

});