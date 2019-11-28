/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$(document).ready(function () {
    
    $('#table').dataTable({
        "sDom": "<'traffic-cols'<'traffic-col-50'l><'traffic-col-50'f><'traffic-clear-5'>t<'traffic-clear-5'><'traffic-col-50'i><'traffic-col-50'p><'traffic-clear-5'>>",
        "sPaginationType": "bootstrap",
        "oLanguage": {
            "sLengthMenu": "_MENU_ registros por página"
        },
        "iDisplayLength": 10,
        "aaSorting": [[10, "desc"]]
    });
    
    $(document.body).on("click", ".closeSession",function (ev) {
        ev.preventDefault();
        var id = $(this).data("id");
        $.confirm({
            title: "Solicitud de anticipo", type: "red", content: '¿Está seguro de que desea cerrar la sesión del usuario?', escapeKey: "cerrar", boxWidth: "350px", useBootstrap: false,
            buttons: {
                si: {
                    btnClass: "btn-red",
                    action: function () {
                        $.ajax({url: "/usuarios/post/cerrar-sesion", cache: false, type: "post", dataType: "json", data: {id: id}
                        }).done(function (res) {
                            if (res.success === true) {
                                $('#row_' + id).hide();
                            } else {
                                $.alert({title: "Error", type: "red", content: res.message, boxWidth: "250px", useBootstrap: false});
                            }
                        });
                    }
                },
                no: function () {}
            }
        });
    });
    
});
