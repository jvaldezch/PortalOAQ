/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

var jsonData = [];
var weights = 0;

function remove(type, id) {
    $("#" + type + "-tarifas").find(".rTableRow#" + id).hide();
    data = jsonData[type][id];
    if(data) {
        jsonData[type][id] = null;
    }
}

function newRow(id, name, type, impo, expo, modo) {
    var html = '<div class="rTableRow" id="' + id + '">';
    html += '<div class="rTableCell">' + name + '</div>';
    html += '<div class="rTableCell">$&nbsp;<input type="text" class="traffic-input-small impo" value="' + impo + '" data-id="' + id + '" data-type="' + type + '" /></div>';
    html += '<div class="rTableCell">$&nbsp;<input type="text" class="traffic-input-small expo" value="' + expo + '" data-id="' + id + '" data-type="' + type + '" /></div>';
    html += '<div class="rTableCell">' + modo + '</div>';
    html += '<div class="rTableCell"><div class="traffic-icon traffic-icon-iminus" style="margin: 0" onclick="remove(\'' + type + '\', ' + id + ');"></div></div>';
    return html += '</div>';
}

function newConcept(id, name, type, importe, modo) {
    var html = '<div class="rTableRow" id="' + id + '">';
    html += '<div class="rTableCell">' + name + '</div>';
    html += '<div class="rTableCell">$&nbsp;<input type="text" class="traffic-input-small importe" value="' + importe + '" data-id="' + id + '" data-type="' + type + '" /></div>';
    html += '<div class="rTableCell">' + modo + '</div>';
    html += '<div class="rTableCell"><div class="traffic-icon traffic-icon-iminus" style="margin: 0" onclick="remove(\'' + type + '\', ' + id + ');"></div></div>';
    return html += '</div>';
}

function newWeight(id, type, weight, price) {
    var html = '<div class="rTableRow" id="' + id + '">';
    html += '<div class="rTableCell"><input type="text" class="traffic-input-medium peso" value="' + weight + '" data-id="' + id + '" data-type="' + type + '" /></div>';
    html += '<div class="rTableCell">$&nbsp;<input type="text" class="traffic-input-small precio" value="' + price + '" data-id="' + id + '" data-type="' + type + '" /></div>';
    html += '<div class="rTableCell"><div class="traffic-icon traffic-icon-iminus" style="margin: 0" onclick="remove(\'' + type + '\', ' + id + ');"></div></div>';
    return html += '</div>';
}

function newRowPercentage(id, name, type, impo, expo, modo) {
    var html = '<div class="rTableRow" id="' + id + '">';
    html += '<div class="rTableCell">' + name + '</div>';
    html += '<div class="rTableCell"><input type="text" class="traffic-input-small impo" style="width:30px; margin-left: 10px !important" value="' + impo + '" data-id="' + id + '" data-type="' + type + '" />&nbsp;%</div>';
    html += '<div class="rTableCell"><input type="text" class="traffic-input-small expo" style="width:30px; margin-left: 10px !important" value="' + expo + '" data-id="' + id + '" data-type="' + type + '" />&nbsp;%</div>';
    html += '<div class="rTableCell">' + modo + '</div>';
    html += '<div class="rTableCell">&nbsp;</div>';
    return html += '</div>';
}

$(document).ready(function () {
    
    if (localStorage.getItem("tarifa") !== null) {
        var string = localStorage.getItem("tarifa");
        jsonData = jQuery.parseJSON(string);
    } else {
        if($("#id").val() !== "") {
            var obj = jQuery.parseJSON($.ajax({type: "GET", url: "/trafico/get/obtener-tarifa", data: {id: $("#id").val()}, dataType: "json", async: false}).responseText);
            jsonData = jQuery.parseJSON(obj.tarifa);
        } else {            
            jsonData = {"aereas": [], "maritimas": [], "especiales": [], "terrestres": [], "conceptos": [], "otros": [], "consolidado": []};
            var arr = ["aereas", "terrestres", "maritimas", "especiales"];
            $.each(arr, function (index, value) {
                jsonData[value][99] = {"id": 99, "name": "Tarifa Porcentaje (Activo fijo)", "impo": 0.39, "expo": 0.20, "modo": "Sobre Valor Factura"};
            });
        }
    }

    if (jsonData) {
        var data = "data:text/json;charset=utf-8," + encodeURIComponent(JSON.stringify(jsonData));
        $("#download").attr("href", data);
        
        if (jsonData.idCliente) {
            $("#idCliente").val(jsonData.idCliente);
        }
        if (jsonData.idCliente) {
            $("#tipoVigencia").val(jsonData.tipoVigencia);
        }
        var arr = ["aereas", "terrestres", "maritimas", "especiales"];
        $.each(arr, function (index, value) {
            if (jsonData[value]) {
                $.each(jsonData[value], function (index, data) {
                    if (data) {
                        if (data.id !== 99) {
                            $("#" + value + "-tarifas").find(".rTableRow:last").after(newRow(data.id, data.name, value, data.impo, data.expo, data.modo));
                        } else {
                            $("#" + value + "-tarifas").find(".rTableRow:last").after(newRowPercentage(data.id, data.name, value, data.impo, data.expo, data.modo));
                        }
                    }
                });
            }
        });
        var arr = ["conceptos", "otros"];
        $.each(arr, function (index, value) {
            if (jsonData[value]) {
                $.each(jsonData[value], function (index, data) {
                    if (data) {
                        $("#" + value + "-tarifas").find(".rTableRow:last").after(newConcept(data.id, data.name, value, data.importe, data.modo !== null ? data.modo : ""));
                    }
                });
            }
        });
        if (jsonData["consolidado"]) {
            $.each(jsonData["consolidado"], function (index, data) {
                if (data) {
                    $("#consolidado-tarifas").find(".rTableRow:last").after(newWeight(data.id, "consolidado", data.peso, data.precio));
                    weights = data.id + 1;
                }
            });
        }
        if (jsonData["notas"]) {
            $.each(jsonData["notas"], function (index, data) {
                if (data) {
                    $("input:checkbox[value=" + index + "]").prop("checked", true);
                }
            });
        }
        if (jsonData["firmante"]) {
            if(jsonData["firmante"][0].nombre) {
                $("#emisor").val(jsonData["firmante"][0].nombre);
            }
            if(jsonData["firmante"][1].nombre) {
                $("#receptor").val(jsonData["firmante"][1].nombre);
            }
        }
    }
    
    $(document.body).on("click", ".addConcept, .addAnotherConcept", function () {
        $(".traffic-error").remove();
        var type = $(this).data("type");
        if ($("#" + type).val() === "") {
            $("#" + type).after('<span class="traffic-error" style="display: inline-block; margin-left: 5px">No ha seleccionado concepto.</span>');
        } else {
            var id = $("#" + type).val();
            var name = $("#" + type + " option:selected").text();
            var obj = jQuery.parseJSON($.ajax({type: "GET", url: "/trafico/get/obtener-tarifa-concepto", data: {id: id}, dataType: "json", async: false}).responseText);
            data = jsonData[type][id];
            if (data !== undefined && data !== null) {
                return false;
            }
            jsonData[type][id] = {"id": id, "name": name, "importe": 0, modo: obj.modoCalculo !== null ? obj.modoCalculo : null};
            $("#" + type + "-tarifas").find(".rTableRow:last").after(newConcept(id, name, type, 0, obj.modoCalculo !== null ? obj.modoCalculo : ""));
        }
    });

    $(document.body).on("click", ".add", function () {
        $(".traffic-error").remove();
        var type = $(this).data("type");
        if ($("#" + type).val() === "") {
            $("#" + type).after('<span class="traffic-error" style="display: inline-block; margin-left: 5px">No ha seleccionado aduana.</span>');
        } else {
            var id = $("#" + type).val();
            var name = $("#" + type + " option:selected").text();
            data = jsonData[type][id];
            console.log("type: " + type + " id: " + id + " name: " + name);
            if (data !== undefined && data !== null) {
                console.log("Exists!");
                return false;
            }
            jsonData[type][id] = {"id": id, "name": name, "impo": 0, "expo": 0, modo: "Pedimento / Guía"};
            $("#" + type + "-tarifas").find(".rTableRow:last").after(newRow(id, name, type, 0, 0, "Pedimento / Guía"));
        }
    });

    $(document.body).on("click", "#addWeight", function () {
        if ($("#weight").val() === "" || $("#price").val() === "") {
            $("#price").after('<span class="traffic-error" style="display: inline-block; margin-left: 5px">Los valores son necesarios.</span>');
        } else {
            var type = $(this).data("type");
            if (jsonData[type] === undefined) {
                jsonData[type] = [];
            }
            var weight = $("#weight").val();
            var price = $("#price").val();
            jsonData[type][weights] = {id: weights, "peso": weight, "precio": price};
            weights++;
            $("#" + type + "-tarifas").find(".rTableRow:last").after(newWeight(weights, type, weight, price));
        }
    });

    $(document.body).on("change", "#idCliente", function () {
        if ($(this).val() !== "") {
            jsonData.idCliente = $("#idCliente").val();
        }
    });

    $(document.body).on("change", ".peso", function () {
        jsonData[$(this).data("type")][$(this).data("id")].peso = $(this).val();
    });

    $(document.body).on("change", ".precio", function () {
        jsonData[$(this).data("type")][$(this).data("id")].precio = $(this).val();
    });

    $(document.body).on("change", ".impo", function () {
        jsonData[$(this).data("type")][$(this).data("id")].impo = $(this).val();
    });

    $(document.body).on("change", ".expo", function () {
        jsonData[$(this).data("type")][$(this).data("id")].expo = $(this).val();
    });

    $(document.body).on("change", ".importe", function () {
        jsonData[$(this).data("type")][$(this).data("id")].importe = $(this).val();
    });

    $(document.body).on("change", "#tipoVigencia", function () {
        jsonData.tipoVigencia = $("#tipoVigencia").val();
    });

    $(document.body).on("change", "#idCliente", function () {
        jsonData.idCliente = $("#idCliente").val();
    });
    
    $(document.body).on("change", "#emisor", function () {
        if (jsonData["firmante"] === undefined) {
            jsonData["firmante"] = [];
        }
        jsonData["firmante"][0] = {nombre: $(this).val()};
    });
    
    $(document.body).on("change", "#receptor", function () {
        if (jsonData["firmante"] === undefined) {
            jsonData["firmante"] = [];
        }
        jsonData["firmante"][1] = {nombre: $(this).val()};
    });
    
    $(document.body).on("click", ".notes", function () {
        var type = $(this).data("type");
        if (jsonData[type] === undefined) {
            jsonData[type] = [];
        }
        if($(this).is(":checked")) {
            jsonData[type][$(this).val()] = true;
        } else {
            jsonData[type][$(this).val()] = null;
        }
    });

    $(document.body).on("click", "#new", function () {
        var r = confirm("¿Está seguro que desea eliminar los datos?");
        if (r === true) {
            localStorage.removeItem("tarifa");
            window.location.replace("/trafico/index/nueva-tarifa");
        }
    });

    $(document.body).on("click", "#save", function () {
        jsonData.idCliente = $("#idCliente").val();
        $.post("/trafico/post/guardar-tarifa", {tarifa: JSON.stringify(jsonData)})
                .done(function (res) {
                    if (res.success === true) {
                        window.location.replace("/trafico/index/nueva-tarifa?id=" + res.id);
                    }
                });
        localStorage.setItem("tarifa", JSON.stringify(jsonData));
    });

});
