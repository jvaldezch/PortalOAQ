/**
 * programmed by Jvaldez at gmail
 * 2015.may.14
 */

let clavesPedimento;
let clientes;

let jsonData = {};

function obtenerMisTraficos() {
    $.ajax({
        url: '/trafico/get/trafico-tmp-todos', cache: false, dataType: 'json', type: 'GET',
        success: function (res) {
            if (res.success === true) {
                $('#traficos').html(res.html);
            }
        }
    });
}

function mensajeAlerta(mensaje) {
    $.alert({
        title: "Alerta", type: "red", typeAnimated: true, useBootstrap: false, boxWidth: "250px",
        content: mensaje
    });
}

function inputText(value, name, width) {
    return '<td style="text-align: center"><input type="text" value="' + value + '" name="' + name + '" style="width: ' + width + 'px; text-align: center" /></td>';
}

function fecha(value) {
    let date = new Date(value);
    return date.getFullYear() + "-" + (date.getMonth() + 1).pad() + "-" + date.getDate().pad();
}

function inputDate(value, name, width) {
    return '<td style="text-align: center"><input type="text" class="' + name + '" name="' + name + '" value="' + fecha(value) + '" style="width: ' + width + 'px; text-align: center" /></td>';
}

function selectClientes(value, name, width) {
    let html = '<select name="' + name + '" style="width: ' + width + 'px; height: 23px">';
    $.each(clientes, function (k, v) {
        if (parseInt(value) === v.id) {
            html += '<option value="' + v.id + '" selected="true">' + v.nombre + '</option>';
        } else {
            html += '<option value="' + v.id + '">' + v.nombre + '</option>';
        }
    });
    return html += '</select>';
}

function selectClavesPedimento(value, name, width) {
    let html = '<select name="' + name + '" style="width: ' + width + 'px; height: 23px">';
    $.each(clavesPedimento, function (k, v) {
        if (parseInt(value) === v) {
            html += '<option value="' + v + '" selected="true">' + v + '</option>';
        } else {
            html += '<option value="' + v + '">' + v + '</option>';
        }
    });
    return html += '</select>';
}

function icono(id, tipoOperacion) {
    if (tipoOperacion === 'TOCE.IMP') {
        return '<i data-id="' + id + '" class="fas fa-arrow-circle-down changeTipoOperacion" style="font-size:1.2em; color: #2f3b58; cursor: pointer"></i><input data-id="' + id + '" type="hidden" class="tipoOperacion" name="tipoOperacion" value="' + tipoOperacion + '" />';
    } else {
        return '<i data-id="' + id + '" class="fas fa-arrow-circle-up changeTipoOperacion" style="font-size:1.2em; color: #2e963a; cursor: pointer"></i><input data-id="' + id + '" type="hidden" class="tipoOperacion" name="tipoOperacion" value="' + tipoOperacion + '" />';
    }
}

function obtenerClientes(idAduana) {
    return $.ajax({
        url: "/trafico/get/obtener-clientes", dataType: "json", timeout: 3000, type: "GET",
        data: {idAduana: idAduana},
        success: function (res) {
            if (res.success === true) {
                clientes = res.rows;
                return true;
            }
        }
    });
}

function cancelar(id) {
    $.ajax({
        url: "/trafico/get/trafico-tmp-seleccionar", dataType: "json", timeout: 3000, type: "GET",
        data: {id: id},
        success: function (res) {
            if (res.success === true) {
                let html = '<td><input type="checkbox" class="sTraffic" data-id="' + id + '" /></td>';
                html += '<td>' + ((res.row["ie"] === 'TOCE.IMP') ? '<i class="fas fa-arrow-circle-down" style="font-size:1.2em; color: #2f3b58"></i>' :
                    '<i class="fas fa-arrow-circle-up" style="font-size:1.2em; color: #2e963a"></i>') + '</td>';
                html += '<td style="text-align: center">' + res.row["patente"] + '</td>';
                html += '<td style="text-align: center">' + res.row["aduana"] + '</td>';
                html += '<td style="text-align: center">' + res.row["pedimento"] + '</td>';
                html += '<td style="text-align: center">' + res.row["referencia"] + '</td>';
                html += '<td style="text-align: center">' + res.row["cvePedimento"] + '</td>';
                html += '<td style="text-align: center">' + fecha(res.row["fechaEta"]) + '</td>';
                html += '<td>' + res.row["nombre"] + '</td>';
                html += '<td><div style="font-size:1.2em; color: #2f3b58; float: right; margin-right: 5px"><i data-id="' + id + '" class="fas fa-pencil-alt editTraffic" style="cursor: pointer; margin-right: 5px"></i><i data-id="' + id + '" class="fas fa-trash-alt deleteTraffic" style="cursor: pointer"></i></div></td>';
                $('.trafficRow_' + id).html(html);
            }
        }
    });
}

Number.prototype.pad = function (size) {
    let s = String(this);
    while (s.length < (size || 2)) {
        s = "0" + s;
    }
    return s;
};

function obtenerClientesSelect(idAduana) {
    return $.ajax({
        url: '/trafico/get/obtener-clientes', dataType: 'json', timeout: 3000, type: 'GET',
        data: {idAduana: idAduana},
        success: function (res) {
            if (res.success === true) {
                $('#cliente').empty()
                    .append('<option value="">---</option>');
                $.each(res.rows, function (k, v) {
                    $('#cliente').append('<option value="' + v["id"] + '">' + v["nombre"] + '</option>');
                });
                $('#cliente').removeAttr('disabled');
                return true;
            } else {
                mensajeAlerta(res.message);
                return false;
            }
        }
    });
}

$(document).ready(function () {

    if (localStorage.getItem("crearTraficoMultiple") !== null) {
        let string = localStorage.getItem("crearTraficoMultiple");
        jsonData = jQuery.parseJSON(string);
        $.each(jsonData, function (k, v) {
            if (k === "cliente") {
                if (jsonData["aduana"] && jsonData["cliente"]) {
                    $.when(obtenerClientesSelect(jsonData["aduana"])).done(function (resp) {
                        if (resp.success === true) {
                            $("#cliente").val(jsonData["cliente"]);
                        }
                    });
                }
            } else {
                $("#" + k).val(v);
            }
        });
    }

    obtenerMisTraficos();

    $.ajax({
        url: "/trafico/get/obtener-claves-pedimento", dataType: "json", timeout: 3000, type: "GET",
        success: function (res) {
            if (res.success === true) {
                clavesPedimento = res.rows;
            }
        }
    });

    $(document.body).on('click', '.changeTipoOperacion', function () {
        let id = $(this).data('id');
        let tipo = $('.tipoOperacion[data-id=' + id + ']').val();
        if (tipo === 'TOCE.EXP') {
            $('.tipoOperacion[data-id=' + id + ']').val('TOCE.IMP');
            $(this).removeClass('fa-arrow-circle-down').addClass('fa-arrow-circle-up').css('color', '#2f3b58');
        } else {
            $('.tipoOperacion[data-id=' + id + ']').val('TOCE.EXP');
            $(this).removeClass('fa-arrow-circle-up').addClass('fa-arrow-circle-down').css('color', '#2e963a');
        }
    });

    $(document.body).on('change', '#aduana', function () {
        obtenerClientesSelect($(this).val());
    });

    $(document.body).on('click', '.createTraffics', function (ev) {
        ev.preventDefault();
        let ids = [];
        let boxes = $('input[class=sTraffic]:checked');
        if ((boxes).size() === 0) {
            mensajeAlerta('Usted no ha seleccionado nada.');
        }
        if ((boxes).size() > 0) {
            $('.createTraffics').attr("disabled", true)
                .addClass("disabled");
            $(boxes).each(function () {
                ids.push($(this).data('id'));
            });
            $.ajax({
                url: '/trafico/post/trafico-tmp-crear', cache: false, dataType: 'json', type: 'POST',
                data: {ids: ids},
                success: function (res) {
                    if (res.success === true) {
                        $('.createTraffics').removeAttr("disabled")
                            .removeClass("disabled");
                        obtenerMisTraficos();
                    }
                }
            });
        }
    });

    $(document.body).on('click', '.selectAll', function (ev) {
        ev.preventDefault();
        let checkboxes = $("input[class=sTraffic]");
        if ($(this).is(':checked')) {
            checkboxes.prop('checked', true);
        } else {
            checkboxes.prop('checked', false);
        }
    });

    $(document.body).on('click', '.cancelSave', function (ev) {
        ev.preventDefault();
        let id = $(this).data('id');
        cancelar(id);
    });

    $(document.body).on('click', '.saveTraffic', function (ev) {
        ev.preventDefault();
        let id = $(this).data('id');
        let fields = {};
        fields['id'] = id;
        $('.trafficRow_' + id + ' :input').each(function () {
            fields[this.name] = this.value;
        });
        $.ajax({
            url: "/trafico/post/trafico-tmp-guardar", dataType: "json", timeout: 3000, type: "POST",
            data: fields,
            success: function () {
                cancelar(id);
            }
        });
    });

    $(document.body).on('click', '.editTraffic', function (ev) {
        ev.preventDefault();
        let id = $(this).data('id');
        $.ajax({
            url: "/trafico/get/trafico-tmp-seleccionar", dataType: "json", timeout: 3000, type: "GET",
            data: {id: id},
            success: function (res) {
                if (res.success === true) {
                    $.when(obtenerClientes(res.row["idAduana"])).done(function (res) {
                        let html = '<td></td>';
                        html += '<td>' + icono(id, res.row['tipoOperacion']) + ' </td>';
                        html += inputText(res.row['patente'], 'editPatente', 50);
                        html += inputText(res.row['aduana'], 'editAduana', 40);
                        html += inputText(res.row['pedimento'], 'editPedimento', 70);
                        html += inputText(res.row['referencia'], 'editReferencia', 70);
                        html += '<td style="text-align: center">' + selectClavesPedimento(res.row['cvePedimento'], 'editClave', 50) + '</td>';
                        html += inputDate(res.row['fechaEta'], 'editFechaEta', 70);
                        html += '<td>' + selectClientes(res.row['idCliente'], 'editCliente', 360) + '</td>';
                        html += '<td><div style="font-size:1.2em; color: #2f3b58; float: right; margin-right: 5px"><i data-id="' + id + '" class="far fa-save saveTraffic" style="cursor: pointer; margin-right: 5px"></i><i data-id="' + id + '" class="far fa-window-close cancelSave" style="cursor: pointer"></i></div></td>';
                        $('.trafficRow_' + id).html(html);
                    });
                }
            }
        });
    });

    $(document.body).on('click', '.deleteTraffic', function (ev) {
        ev.preventDefault();
        let id = $(this).data('id');
        $.confirm({
            title: "Confirmar",
            type: "red",
            content: '¿Está seguro de que desea eliminar el tráfico?',
            escapeKey: "cerrar",
            boxWidth: "250px",
            useBootstrap: false,
            buttons: {
                si: {
                    btnClass: "btn-red",
                    action: function () {
                        $.ajax({
                            url: "/trafico/post/trafico-tmp-borrar", dataType: "json", timeout: 3000, type: "POST",
                            data: {id: id},
                            success: function (res) {
                                if (res.success === true) {
                                    obtenerMisTraficos();
                                }
                            }
                        });
                    }
                },
                no: function () {
                }
            }
        });
    });

    $(document.body).on('click', '#submit', function (ev) {
        ev.preventDefault();
        if ($("#form").valid()) {
            $("#form").ajaxSubmit({
                url: "/trafico/post/trafico-tmp-agregar", cache: false, dataType: "json", timeout: 3000, type: "POST",
                success: function (res) {
                    if (res.success === true) {
                        obtenerMisTraficos();
                    }
                }
            });
        }
    });

    $(document.body).on('focus', '.editFechaEta', function (ev) {
        ev.preventDefault();
        $(this).datepicker({
            calendarWeeks: true,
            autoclose: true,
            language: "es",
            format: "yyyy-mm-dd"
        });
    });

    $("#fechaNotificacion, #fechaEta").datepicker({
        calendarWeeks: true,
        autoclose: true,
        language: "es",
        format: "yyyy-mm-dd"
    });

    $("#form").validate({
        errorPlacement: function (error, element) {
            $(element)
                .closest("form")
                .find("#" + element.attr("id"))
                .after(error);
        },
        errorElement: "span",
        errorClass: "traffic-error",
        rules: {
            cliente: "required",
            aduana: "required",
            operacion: "required",
            cvePedimento: "required",
            fechaEta: "required",
            pedimento: {
                required: true,
                minlength: 7,
                maxlength: 7,
                digits: true
            },
            referencia: {
                required: true,
                minlength: 4
            }
        },
        messages: {
            cliente: "Seleccionar cliente.",
            aduana: "Seleccionar aduana.",
            operacion: "Seleccionar tipo de operación.",
            cvePedimento: "Clave de pedimento necesaria",
            fechaEta: "Fecha necesaria",
            planta: "Campo necesario",
            pedimento: {
                required: "Campo necesario",
                minlength: "Pedimento debe ser de 7 digitos",
                maxlength: "Pedimento dede ser de 7 digitos",
                digits: "No debe contener letras"
            },
            referencia: {
                required: "Proporcionar referencia",
                minlength: "Mínimo 4 caracteres alfanumérico"
            }
        }
    });

    $(document.body).on("input", "#referencia", function (ev) {
        ev.preventDefault();
        let input = $(this);
        let start = input[0].selectionStart;
        $(this).val(function (_, val) {
            return val.toUpperCase();
        });
        input[0].selectionStart = input[0].selectionEnd = start;
    });

    $(document.body).on("change", "#referencia, #pedimento, #cliente, #cantidad, #cvePedimento, #fechaEta, #operacion, #aduana", function (ev) {
        ev.preventDefault();
        jsonData[$(this).attr("id")] = $(this).val();
        localStorage.setItem("crearTraficoMultiple", JSON.stringify(jsonData));
    });

});