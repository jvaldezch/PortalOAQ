/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

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

    $(document.body).on("click", ".closeSession", function (ev) {
        ev.preventDefault();
        var id = $(this).data("id");
        $.confirm({
            title: "Solicitud de anticipo", type: "red", content: '¿Está seguro de que desea cerrar la sesión del usuario?', escapeKey: "cerrar", boxWidth: "350px", useBootstrap: false,
            buttons: {
                si: {
                    btnClass: "btn-red",
                    action: function () {
                        $.ajax({
                            url: "/usuarios/post/cerrar-sesion", cache: false, type: "post", dataType: "json", data: { id: id }
                        }).done(function (res) {
                            if (res.success === true) {
                                $('#row_' + id).hide();
                            } else {
                                $.alert({ title: "Error", type: "red", content: res.message, boxWidth: "250px", useBootstrap: false });
                            }
                        });
                    }
                },
                no: function () { }
            }
        });
    });

});
