
window.deleteUser = function (value) {
    var resp = confirm("¿Esta seguro que desea borrar el usuario?");
    if (resp === true) {
        $.ajax({
            url: "/usuarios/index/delete-user",
            context: document.body,
            data: {
                id: value
            },
            type: 'GET'
        }).done(function (data) {
            var obj = jQuery.parseJSON(data);
            if (obj.success === true) {
                window.location.href = "/usuarios/index/usuarios";
            } else if (obj.success === false) {
                alert("Ocurrio un error al borrar el usuario.");
            }
        });
    }
}

$(document).ready(function () {

    $('#users-table').DataTable({
        "lengthMenu": [[15, 25, 50, -1], [15, 25, 50, "All"]],
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

});