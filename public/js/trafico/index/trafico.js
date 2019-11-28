/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$(document).ready(function () {
    
    var params = ["idAduana"];

    params.forEach(function (entry) {
        var param = getUrlParameter(entry);
        if (param) {
            $(".traffic-pagination a").each(function () {
                var href = $(this).attr("href");
                if (href && href.indexOf(entry + "=") === -1) {
                    $(this).attr("href", href + "&" + entry + "=" + param);
                }
            });
        }
    });
    
    var arr = "#allOperations,#pagadas,#liberadas,#impos,#expos"; // dont't use spaces

    $(document.body).on("click", arr, function () {
        if ($(this).is(":checked")) {
            Cookies.set($(this).attr("id"), true);
            window.location.replace("/trafico/index/trafico");
        } else {
            Cookies.set($(this).attr("id"), false);
            window.location.replace("/trafico/index/trafico");
        }
    });

    var array = arr.split(",");

    $.each(array, function (index, value) {
        var str = value.replace("#", "");
        if (Cookies.get(str) !== undefined) {
            if (Cookies.get(str) === "true") {
                $("#" + str).prop("checked", true);
            }
        }
    });
    
    $(document.body).on("input", "#buscar", function () {
        var input = $(this);
        var start = input[0].selectionStart;
        $(this).val(function (_, val) {
            return val.toUpperCase();
        });
        input[0].selectionStart = input[0].selectionEnd = start;
    });
    
    $("#reporteModal").jqm({
        ajax: "@href",
        modal: true,
        trigger: "#reporte"
    });
    
    $(document.body).on("click", "#closeModal", function (ev) {
        ev.preventDefault();
        $("#reporteModal").jqmHide();
    });
    
    $(document.body).on("click", "#ownOperations", function (ev) {
        ev.preventDefault();
        var ids = [];
        var boxes = $("input[class=operation]:checked");
        if ((boxes).size() === 0) {
            $.alert({
                title: "¡Alerta!",
                content: "No ha seleccionado ninguna operación"
            });
        } else {
            $(boxes).each(function () {
                ids.push($(this).attr("id"));
            });
            $.ajax({
                url: "/trafico/post/cambiar-propietario",
                cache: false,
                type: "post",
                dataType: "json",
                data: {ids: ids},
                success: function(res) {
                    if (res.success === true) {
                    }
                }
            });
        }
    });
    
    $(document.body).on("click", "#selectAll", function () {
        var checkboxes = $("input[class=operation]");
        if ($(this).is(":checked")) {
            checkboxes.prop("checked", true);
        } else {
            checkboxes.prop("checked", false);
        }
    });
    
    $("#allMessages").qtip({ // Grab some elements to apply the tooltip to
        content: {
            text: "Mostrar todos los mensajes sin leer."
        }
    });
    
    $(".mensajero").each(function () {
        var id = $(this).data("id");
        $.ajax({
            url: "/trafico/get/mis-mensajes",
            cache: false,
            type: "post",
            dataType: "json",
            data: {idTrafico: id},
            success: function (res) {
                if (res.success === true) {
                    if (res.cantidad > 0) {
                        $(".mensajero[data-id=" + id + "]").attr("src", "/images/icons/message-new.png");
                    }
                }
            }
        });
    });

});