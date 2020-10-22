$(document).ready(function () {

    $("#alerts-table").DataTable({
        "lengthMenu": [[5, 15, 25, 50, -1], [5, 15, 25, 50, "All"]],
        "language": {
            "decimal": "",
            "emptyTable": "No data available in table",
            "info": "Mostrando _START_ a _END_ de _TOTAL_ registros",
            "infoEmpty": "Showing 0 to 0 of 0 entries",
            "infoFiltered": "(filtered from _MAX_ total entries)",
            "infoPostFix": "",
            "thousands": ",",
            "lengthMenu": "Mostrando _MENU_ registros",
            "loadingRecords": "Cargando ...",
            "processing": "Procesando ...",
            "search": "Buscar:",
            "zeroRecords": "No matching records found",
            "paginate": {
                "first": "Primero",
                "last": "Último",
                "next": "Sig.",
                "previous": "Ant."
            }
        }
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