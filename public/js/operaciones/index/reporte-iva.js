$(document).ready(function () {
    
    $("#nombre").typeahead({
        source: function (query, process) {
            return $.ajax({
                url: "/trafico/get/clientes",
                type: "get",
                data: {name: query},
                dataType: "json",
                success: function (res) {
                    return process(res);
                }
            });
        }
    }).change(function () {
        $("#rfc_cliente").val("");
    });

    $(document.body).on("change", "#nombre", function () {
        $.ajax({
            url: "/trafico/get/rfc-de-cliente",
            type: "get",
            data: {name: $("#nombre").val()},
            dataType: "json",
            success: function (res) {
                if (res) {
                    $("#rfc_cliente").val(res[0]["rfc"]);
                }
            }
        });
    });
    
    $(document.body).on("input", "#referencia, #rfc_cliente, #nombre", function () {
        var input = $(this);
        var start = input[0].selectionStart;
        $(this).val(function (_, val) {
            return val.toUpperCase();
        });
        input[0].selectionStart = input[0].selectionEnd = start;
    });
    
    $("#form-report").validate({
        rules: {
            rfc_cliente: "required"
        },
        messages: {
            rfc_cliente: "RFC del cliente es necesario."
        }
    });
    
    $(document.body).on("click", "#submit", function (e) {
        e.preventDefault();
        if ($("#form-report").valid()) {
            var url = "/operaciones/data/ver-reporte-iva?rfc=" + $("#rfc_cliente").val() + "&year=" + $("#year").val() + "&mes=" + $("#mes").val()  + "&aduana=" + $("#aduana").val();
            $("#report-frame").attr("src", url);
        }
    });
    
});

function nextPage(page) {
    var url = "/operaciones/data/ver-reporte-iva?rfc=" + $("#rfc_cliente").val() + "&year=" + $("#year").val() + "&mes=" + $("#mes").val() + "&page=" + page;
    $("#report-frame").attr("src", url);
}

