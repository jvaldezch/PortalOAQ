
function viewReport() {
    var desglose = 0;
    if ($("#desglose").is(":checked")) {
        desglose = 1;
    }
    window.open("/administracion/data/consulta-cuenta-de-gastos?rfc=" + $("#rfc").val() + "&fechaIni=" + $("#fechaIni").val() + "&fechaFin=" + $("#fechaFin").val() + "&desglose=" + desglose, "_blank", "toolbar=0,location=0,menubar=0,height=550,width=1024,scrollbars=yes");
}

$(document).ready(function () {
    
    $("input[name='fechaIni']").datepicker({
        format: "yyyy-mm-dd"
    }).on("changeDate", function () {
        if ($("#rfc").val().length > 9) {
            $("#submit").removeAttr("disabled");
        }
        $(this).datepicker("hide");
    });
    
    $("input[name='fechaFin']").datepicker({
        format: "yyyy-mm-dd"
    }).on("changeDate", function () {
        if ($("#rfc").val().length > 9) {
            $("#submit").removeAttr("disabled");
        }
        $(this).datepicker("hide");
    });

    var elementsText = ["rfc", "nombre"];
    $.each(elementsText, function (index, value) {
        $("#" + value).keyup(function () {
            $(this).val($(this).val().toUpperCase());
        });
    });

    $("#rfc").keypress(function () {
        if ($(this).val().length > 9) {
            $("#submit").removeAttr("disabled");
        } else if ($(this).val().length <= 8) {
            $("#submit").attr("disabled", "disabled");
        }
    }).blur(function () {
        if ($(this).val().length > 9) {
            $("#submit").removeAttr("disabled");
        }
    });

    $("#customer-list").click(function () {
        $("#myModal").modal({
            remote: "/administracion/index/clientes"
        });
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
                    $("#submit").removeAttr("disabled");
                }
            }
        });
    });

    /*$("#nombre").typeahead({
        source: function (query, process) {
            return $.ajax({
                url: "/comercializacion/index/json-customers-by-name",
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

    $("#nombre").change(function () {
        $.ajax({
            url: "/comercializacion/index/json-customer-rfc-by-name",
            type: "get",
            data: {name: $("#nombre").val()},
            dataType: "json",
            success: function (res) {
                if (res) {
                    $("#rfc").val(res);
                    $("#submit").removeAttr("disabled");
                }
            }
        });
    });*/
    
    $("#submit").click(function (e) {
        e.preventDefault();
        viewReport();
    });
});