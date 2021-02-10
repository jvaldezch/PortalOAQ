/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

let funcion = 'claves';

function claves(page, buscar) {
    page = page || 1;
    buscar = buscar || "";
    $.ajax({
        type: "post",
        dataType: "json",
        url: "/trafico/post/claves",
        data: { page: page, buscar: buscar },
        beforeSend: function () {
            $("body").LoadingOverlay("show", { color: "rgba(255, 255, 255, 0.9)" });
        },
        success: function (res) {
            $("body").LoadingOverlay("hide");
            if (res.success === true) {
                $("#pagina").val(res.pagina);
                $("#funcion").val(res.funcion);
                $("#results").html(res.html);
            }
        }
    });
}

function unidades(page, buscar) {
    page = page || 1;
    buscar = buscar || "";
    $.ajax({
        type: "post",
        dataType: "json",
        url: "/trafico/post/unidades",
        data: { page: page, buscar: buscar },
        beforeSend: function () {
            $("body").LoadingOverlay("show", { color: "rgba(255, 255, 255, 0.9)" });
        },
        success: function (res) {
            $("body").LoadingOverlay("hide");
            if (res.success === true) {
                $("#pagina").val(res.pagina);
                $("#funcion").val(res.funcion);
                $("#results").html(res.html);
            }
        }
    });
}

function monedas(page, buscar) {
    page = page || 1;
    buscar = buscar || "";
    $.ajax({
        type: "post",
        dataType: "json",
        url: "/trafico/post/monedas",
        data: { page: page, buscar: buscar },
        beforeSend: function () {
            $("body").LoadingOverlay("show", { color: "rgba(255, 255, 255, 0.9)" });
        },
        success: function (res) {
            $("body").LoadingOverlay("hide");
            if (res.success === true) {
                $("#pagina").val(res.pagina);
                $("#funcion").val(res.funcion);
                $("#results").html(res.html);
            }
        }
    });
}

function tigie(page, buscar) {
    page = page || 1;
    buscar = buscar || "";
    $.ajax({
        type: "get",
        dataType: "json",
        url: "/trafico/facturas/tigie",
        data: { page: page, buscar: buscar },
        beforeSend: function () {
            $("body").LoadingOverlay("show", { color: "rgba(255, 255, 255, 0.9)" });
        },
        success: function (res) {
            $("body").LoadingOverlay("hide");
            if (res.success === true) {
                $("#pagina").val(res.pagina);
                $("#funcion").val(res.funcion);
                $("#results").html(res.html);
            }
        }
    });
}

$(document).ready(function () {

    $(document.body).on("click", "#claves", function () {
        claves();
        funcion = 'claves';
    });

    $(document.body).on("click", "#unidades", function () {
        unidades();
        funcion = 'unidades';
    });

    $(document.body).on("click", "#monedas", function () {
        monedas();
        funcion = 'monedas';
    });

    $(document.body).on("click", "#tigie", function () {
        tigie();
        funcion = 'tigie';
    });

    $(document.body).on("input", "#buscar", function (ev) {
        ev.preventDefault();
        var busqueda = $(this).val();
        if (busqueda.length >= 2) {
            window[funcion](1, busqueda);
        }
    });

    $(document.body).on("input", "#buscar", function (ev) {
        var input = $(this);
        var start = input[0].selectionStart;
        $(this).val(function (_, val) {
            return val.toUpperCase();
        });
        input[0].selectionStart = input[0].selectionEnd = start;
    });

    claves(1);

});

