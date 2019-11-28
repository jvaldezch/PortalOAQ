/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

window.misDocumentos = function() {
    return $.ajax({url: '/principal/get/mis-documentos', type: 'GET', dataType: 'json', cache: false,
        beforeSend: function () {
            $('#doctos').LoadingOverlay('show', {color: 'rgba(255, 255, 255, 0.9)'});
        },
        success: function (res) {
            $('#doctos').LoadingOverlay('hide');
            if (res.success === true) {
                $('#doctos').html(res.html);
            }
        }
    });
};


window.descargarArchivo = function(href) {
    window.location.href = href;
};

$(document).ready(function () {
    
    misDocumentos();
    
    $(document.body).on("click", ".openFile", function (ev) {
        ev.preventDefault();        
        var id = $(this).data("id");
        window.open("/rrhh/get/ver-archivo?id=" + id, "viewFile", "toolbar=0,location=0,menubar=0,height=550,width=880,scrollbars=yes");
    });
    
});

