

function loadPhotos() {
    var idTrafico = $("#idTrafico").val();
    if (idTrafico !== undefined) {
        $.ajax({url: "/trafico/post/cargar-fotos", dataType: "json", timeout: 10000, type: "POST", 
            data: {id: $("#idTrafico").val(), borrar: 0, uri: '/clientes/get/download-photos'},
            success: function (res) {
                if (res.success === true) {
                    $("#photos").html(res.html);
                }
            }        
        });
    }
}

$(document).ready(function() {
    
    $(document.body).on("click", "#ftpLink", function (ev) {
        var id = $(this).data("id");
        $.confirm({ title: "Enlace de descarga", escapeKey: "cerrar", boxWidth: "450px", useBootstrap: false, type: "green",
            buttons: {
                cerrar: {btnClass: "btn-green", action: function () {}}
            },
            content: function () {
                var self = this;
                return $.ajax({
                    url: "/clientes/get/link-ftp?id=" + id,
                    method: "get"
                }).done(function (res) {
                    self.setContent(res);
                }).fail(function () {
                    self.setContent("Something went wrong.");
                });
            }
        });
    });
    
    loadPhotos();
    
    $(document.body).on("click",".image-link",function (ev) {
        ev.preventDefault();
        var w = window.open("/clientes/get/image?id=" + $(this).data("id"), 'Trafico Image ' + $(this).data("id"), 'toolbar=0,location=0,menubar=0,height=750,width=950,scrollbars=yes');
        w.focus();
        return false;
    });
    
});