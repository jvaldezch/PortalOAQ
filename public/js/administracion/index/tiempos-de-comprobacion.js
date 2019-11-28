$(document).ready(function () {

    $("input[name='fechaIni'], input[name='fechaFin']").datepicker({
        format: "yyyy-mm-dd",
        language: "es",
        autoclose: true
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
            rfc: {required: true},
            fechaIni: {required: true},
            fechaFin: {required: true}
        },
        messages: {
            rfc: "SE REQUIERE",
            fechaIni: "SE REQUIERE",
            fechaFin: "SE REQUIERE"
        }
    });
    
    $(document.body).on("click", "#submit", function () {
        $("#form").submit();
    });
    
    $(document.body).on("submit", "#form", function (ev) {
        if (!$("#form").valid()) {
            ev.preventDefault();
        }
    });
    
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
        $("#rfc").val("");
    });
    
    $(document.body).on("change", "#nombre", function () {
        $.ajax({
            url: "/trafico/get/rfc-de-cliente",
            type: "get",
            data: {name: $("#nombre").val()},
            dataType: "json",
            success: function (res) {
                if (res) {
                    $("#rfc").val(res[0]["rfc"]);
                }
            }
        });
    });

});