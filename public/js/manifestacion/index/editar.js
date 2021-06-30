/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

let id_manifestacion;

window.datosCove = function (edocument) {
    $.ajax({
        url: '/manifestacion/get/datos-edocument',
        dataType: "json",
        type: "GET",
        data: { id: id_manifestacion, edocument: edocument },
        beforeSend: function () {
            $("body").LoadingOverlay("show", { color: "rgba(255, 255, 255, 0.9)" });
        },
        success: function (res) {
            $("body").LoadingOverlay("hide");
            if (res.success === true) {
                $("#cove").html(res.html);
            }
        }
    });
};

window.obtenerRfcConsulta = function () {

    $.ajax({
        url: '/manifestacion/get/rfc-consulta',
        dataType: "json",
        type: "GET",
        data: { id: id_manifestacion },
        beforeSend: function () {
            $("body").LoadingOverlay("show", { color: "rgba(255, 255, 255, 0.9)" });
        },
        success: function (res) {
            $("body").LoadingOverlay("hide");
            if (res.success === true) {
                for (const k in res.results) {

                    var tr = document.createElement("tr");
                    tr.setAttribute("id", res.results[k].rfc);

                    var td_1 = document.createElement("td");
                    td_1.setAttribute("style", "width: 32px");

                    var td_2 = document.createElement("td");
                    td_2.innerText = res.results[k].rfc;

                    var td_3 = document.createElement("td");
                    td_3.setAttribute("style", "text-align: center; width: 80px");

                    var i_del = document.createElement("i");
                    i_del.setAttribute("style", "margin-left: 7px");
                    i_del.setAttribute("class", "fas fa-trash delete-rfc");
                    i_del.setAttribute("data-id", res.results[k].id);

                    td_3.appendChild(i_del);

                    tr.appendChild(td_1);
                    tr.appendChild(td_2);
                    tr.appendChild(td_3);

                    $("#rfcConsulta").append(tr);
                }
            } else {
                var tr = document.createElement("tr");
                var td = document.createElement("td");
                td.setAttribute("colspan", 3);
                var em = document.createElement("em");

                em.innerText = 'No hay RFC de consulta.';

                td.appendChild(em);
                tr.appendChild(td);

                $("#rfcConsulta").html(tr);
            }
        }
    });
};

window.obtenerEdocuments = function () {
    id_manifestacion = $("#id").val();
    $.ajax({
        url: '/manifestacion/get/edocuments',
        dataType: "json",
        type: "GET",
        data: { id: id_manifestacion },
        beforeSend: function () {
            $("body").LoadingOverlay("show", { color: "rgba(255, 255, 255, 0.9)" });
            $("#edocuments").html('');
        },
        success: function (res) {
            $("body").LoadingOverlay("hide");
            if (res.success === true) {
                for (const k in res.results) {

                    var tr = document.createElement("tr");
                    tr.setAttribute("id", res.results[k].edocument);

                    var td_1 = document.createElement("td");
                    td_1.setAttribute("style", "width: 32px");
                    var chkb = document.createElement("input");
                    chkb.setAttribute("type", "checkbox");
                    chkb.setAttribute("class", "toggle-edocument");
                    chkb.setAttribute("data-id", res.results[k].id);

                    if (res.results[k].usar && res.results[k].usar == 1) {
                        chkb.setAttribute("checked", true);
                    }

                    td_1.appendChild(chkb);

                    var td_2 = document.createElement("td");
                    td_2.innerText = res.results[k].edocument;

                    var td_3 = document.createElement("td");
                    td_3.setAttribute("style", "text-align: center; width: 80px");
                    var i_edit = document.createElement("i");
                    i_edit.setAttribute("class", "fas fa-pencil-alt edit-edocument");
                    i_edit.setAttribute("data-id", res.results[k].id);
                    i_edit.setAttribute("data-type", res.results[k].tipo);
                    i_edit.setAttribute("data-edocument", res.results[k].edocument);

                    var i_del = document.createElement("i");
                    i_del.setAttribute("style", "margin-left: 7px");
                    i_del.setAttribute("class", "fas fa-trash delete-edocument");
                    i_del.setAttribute("data-id", res.results[k].id);

                    if (res.results[k].tipo == 'CV') {
                        td_3.appendChild(i_edit);
                        td_3.appendChild(i_del);
                    }

                    tr.appendChild(td_1);
                    tr.appendChild(td_2);
                    tr.appendChild(td_3);

                    $("#edocuments").append(tr);
                }
            } else {
                var tr = document.createElement("tr");
                var td = document.createElement("td");
                td.setAttribute("colspan", 3);
                var em = document.createElement("em");

                em.innerText = 'No hay edocuments.';

                td.appendChild(em);
                tr.appendChild(td);

                $("#edocuments").html(tr);
            }
        }
    });
};

$(document).ready(function () {

    $(document.body).on("click", ".confirm-edocument", function (ev) {
        var edocument = $("#nuevoEdocument").val();
        $.ajax({
            url: '/manifestacion/post/agregar-edocument',
            dataType: "json",
            type: "POST",
            data: { id: id_manifestacion, edocument: edocument },
            beforeSend: function () {
                $("body").LoadingOverlay("show", { color: "rgba(255, 255, 255, 0.9)" });
            },
            success: function (res) {
                $("body").LoadingOverlay("hide");
                if (res.success === true) {
                    $("#row-edocument").remove();
                    obtenerEdocuments();
                } else {
                    $.toast({text: `<b>Advertencia</b><br>${res.message}`, bgColor: "red", stack : 3, position : "bottom-right"});
                }
            }
        });
    });

    $(document.body).on("click", ".confirm-rfc", function (ev) {
        var rfc = $("#nuevoRfcConsulta").val();
        $.ajax({
            url: '/manifestacion/post/agregar-rfc-consulta',
            dataType: "json",
            type: "POST",
            data: { id: id_manifestacion, rfc: rfc },
            beforeSend: function () {
                $("body").LoadingOverlay("show", { color: "rgba(255, 255, 255, 0.9)" });
            },
            success: function (res) {
                $("body").LoadingOverlay("hide");
                if (res.success === true) {
                    $("#row-rfc").remove();
                    obtenerRfcConsulta();
                } else {
                    $.toast({text: `<b>Advertencia</b><br>${res.message}`, bgColor: "red", stack : 3, position : "bottom-right"});
                }
            }
        });
    });

    $(document.body).on("click", ".disable-edocument", function (ev) {
        $("#row-edocument").remove();
    });

    $(document.body).on("click", ".disable-rfc", function (ev) {
        $("#row-rfc").remove();
    });

    $(document.body).on("click", ".add-rfc", function (ev) {

        if ($("#row-rfc").length) {
            return false;
        }

        var tr = document.createElement("tr");
        tr.setAttribute("id", "row-rfc");

        var td_1 = document.createElement("td");
        td_1.setAttribute("style", "width: 32px");

        var i_cls = document.createElement("i");
        i_cls.setAttribute("class", "fas fa-times disable-rfc");

        td_1.appendChild(i_cls);

        var td_2 = document.createElement("td");

        var inp = document.createElement("input");
        inp.setAttribute("type", "text");
        inp.setAttribute("id", "nuevoRfcConsulta");
        inp.setAttribute("style", "text-align: center");

        td_2.appendChild(inp);

        var td_3 = document.createElement("td");

        var i_sv = document.createElement("i");
        i_sv.setAttribute("class", "fas fa-save confirm-rfc");

        td_3.appendChild(i_sv);

        tr.appendChild(td_1);
        tr.appendChild(td_2);
        tr.appendChild(td_3);

        $('#rfcConsulta tr:last').after(tr);

    });

    $(document.body).on("click", ".add-edocument", function (ev) {

        if ($("#row-edocument").length) {
            return false;
        }

        var tr = document.createElement("tr");
        tr.setAttribute("id", "row-edocument");

        var td_1 = document.createElement("td");
        td_1.setAttribute("style", "width: 32px");

        var i_cls = document.createElement("i");
        i_cls.setAttribute("class", "fas fa-times disable-edocument");

        td_1.appendChild(i_cls);

        var td_2 = document.createElement("td");

        var inp = document.createElement("input");
        inp.setAttribute("type", "text");
        inp.setAttribute("id", "nuevoEdocument");
        inp.setAttribute("style", "text-align: center");

        td_2.appendChild(inp);

        var td_3 = document.createElement("td");

        var i_sv = document.createElement("i");
        i_sv.setAttribute("class", "fas fa-save confirm-edocument");

        td_3.appendChild(i_sv);

        tr.appendChild(td_1);
        tr.appendChild(td_2);
        tr.appendChild(td_3);

        $('#edocuments tr:last').after(tr);

    });

    $(document.body).on("click", ".toggle-edocument", function (ev) {
        let id = $(this).data('id');
        let chk = $(this).is(":checked");

        $.ajax({
            url: '/manifestacion/post/actualiza-edocument',
            dataType: "json",
            type: "POST",
            data: { id: id, chk: chk },
            beforeSend: function () {
                $("body").LoadingOverlay("show", { color: "rgba(255, 255, 255, 0.9)" });
            },
            success: function (res) {
                $("body").LoadingOverlay("hide");
                if (res.success === true) {
                }
            }
        });
    });

    $(document.body).on("click", ".edit-edocument", function (ev) {
        let id = $(this).data('id');
        let tp = $(this).data('type');
        let ed = $(this).data('edocument');
        if (tp == 'CV') {
            datosCove(ed);
        }
    });

    $(document.body).on("click", "#import-traffic", function (ev) {
        ev.preventDefault();

        var patente = $("#patente").val();
        var aduana = $("#aduana").val();
        var pedimento = $("#pedimento").val();
        var referencia = $("#referencia").val();

        $.ajax({
            url: '/manifestacion/get/edocuments-trafico',
            dataType: "json",
            type: "GET",
            data: { id: id_manifestacion, patente: patente, aduana: aduana, pedimento: pedimento, referencia: referencia },
            beforeSend: function () {
                $("body").LoadingOverlay("show", { color: "rgba(255, 255, 255, 0.9)" });
            },
            success: function (res) {
                $("body").LoadingOverlay("hide");
                if (res.success === true) {
                }
            }
        });
    });

    obtenerEdocuments();
    obtenerRfcConsulta();

    $(document).on("input", "#nuevoRfcConsulta, #nuevoEdocument", function () {
        var input = $(this);
        var start = input[0].selectionStart;
        $(this).val(function (_, val) {
            return val.toUpperCase();
        });
        input[0].selectionStart = input[0].selectionEnd = start;
    });

});