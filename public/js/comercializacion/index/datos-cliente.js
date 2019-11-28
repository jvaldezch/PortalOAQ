/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$(document).ready(function () {

    $('#add').click(function (event) {
        event.preventDefault();
        window.location = '/comercializacion/index/agregar-contactos';
    });

    $('.delContact').click(function (event) {
        event.preventDefault();
        var href = $(this).attr('href');
        bootbox.confirm("¿Está seguro que desea borrar este contacto?", function (result) {
            if (result === true) {
                $.ajax({
                    url: href,
                    type: "get",
                    success: function (data) {
                        var obj = jQuery.parseJSON(data);
                        if (obj.success === true) {
                            window.location = '<?= $this->referer ?>';
                        }
                    }
                });
            }
        });
    });

});