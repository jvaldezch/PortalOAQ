$(document).ready(function () {

    $("#example").dataTable({
        "sDom": "<'traffic-cols'<'traffic-col-50'l><'traffic-col-50'f><'traffic-clear-5'>t<'traffic-clear-5'><'traffic-col-50'i><'traffic-col-50'p><'traffic-clear-5'>>",
        "sPaginationType": "bootstrap",
        "oLanguage": {
            "sLengthMenu": "_MENU_ registros por página"
        },
        "iDisplayLength": 10,
        "aaSorting": [[10, "desc"]]
    });

    $(".activate").bind("change", function () {
        if ($(this).is(":checked")) {
            $.post("/usuarios/post/cambiar-alerta", {id: $(this).val(), action: "activate"}, function (res) {
            });
        } else {
            $.post("/usuarios/post/cambiar-alerta", {id: $(this).val(), action: "deactivate"}, function (res) {
            });
        }
    });

    $(".dateFrom").bind("change", function () {
        $.post("/usuarios/post/cambiar-alerta", {id: $(this).data("id"), dateFrom: $(this).val()}, function (res) {
        });
    });

    $(".dateTo").bind("change", function () {
        $.post("/usuarios/post/cambiar-alerta", {id: $(this).data("id"), dateTo: $(this).val()}, function (res) {
        });
    });
    
    $(".alert").bind("change", function () {
        $.post("/usuarios/post/cambiar-alerta", {id: $(this).data("id"), alert: $(this).val()}, function (res) {
        });
    });
    
    $(".content").bind("change", function () {
        $.post("/usuarios/post/cambiar-alerta", {id: $(this).data("id"), content: $(this).html()}, function (res) {
        });
    });
    
    $(document.body).on("click", "#add", function () {
        $.post("/usuarios/post/agregar-alerta", {}, function (res) {
            if (res.success === true) {
                $('#example tr:last').after('<tr></tr>');
            }
        });
    });

    $(".dateFrom, .dateTo").datepicker({
        calendarWeeks: true,
        autoclose: true,
        language: "es",
        format: "yyyy-mm-dd"
    });

});