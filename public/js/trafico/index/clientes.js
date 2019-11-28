/**
 * programmed by Jvaldez at gmail
 * 2015.may.14
 */

function decodeEntities(arreglo) {
    var arr = [];
    arreglo.forEach(function (entry) {
        arr.push($('<textarea />').html(entry).text());
    });
    return arr;
}

$(document).ready(function () {

    $(document.body).on("click", "input[name=filter]", function () {
        Cookies.set("filter", $(this).val());
        window.location.replace("/trafico/index/clientes");
    });
    
    if (Cookies.get("filter") !== undefined) {        
        $("input[name=filter][value=" + Cookies.get("filter") + "]").attr("checked", "checked");
    } else {
        $("input[name=filter][value=" + 0 + "]").attr("checked", "checked");
    }

    $(document.body).on("input", "#busqueda", function (ev) {
        $("#errors").html("");
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
    
    $("#busqueda").typeahead({
        source: function (query, process) {
            return $.ajax({
                url: "/trafico/get/clientes",
                type: "get",
                data: {name: query},
                dataType: "json",
                success: function (res) {
                    return process(decodeEntities(res));
                }
            });
        }
    });
    
});