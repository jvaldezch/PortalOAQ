/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$(document).ready(function () {

    $(document.body).on('click', '.purge', function (ev) {
        ev.preventDefault();
        let queue = $(this).data('queue');
        $.confirm({title: "Confirmar", content: '¿Está seguro de que desea eliminar el Queue, esto borrará todos los mensajes y todo el procesamiento de ese Queue será reiniciado?', type: 'red', escapeKey: "cerrar", boxWidth: "350px", useBootstrap: false,
            buttons: {
                si: {
                    btnClass: "btn-red",
                    action: function () {
                        $.ajax({url: '/usuarios/post/purgar-queue', dataType: "json", timeout: 3000, type: "POST",
                            data: {queue: queue},
                            success: function (res) {
                                if (res.success === true) {
                                    location.reload();
                                }
                            }
                        });
                    }
                },
                no: function () {}
            }
        });

    });

});
