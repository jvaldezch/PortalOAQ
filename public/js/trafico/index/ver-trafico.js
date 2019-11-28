function loadFiles() {
    $.ajax({url: "/trafico/get/ver-archivos", dataType: "json", timeout: 20000, type: "POST",
        data: {idTrafico: $("#idTrafico").val()},
        success: function (res) {
            if (res.success === true) {
                $("#archivos").html(res.html);
            }
        }        
    });
}

function loadPhotos() {
    $.ajax({url: "/trafico/post/cargar-fotos", dataType: "json", timeout: 10000, type: "POST", 
        data: {id: $("#idTrafico").val()},
        success: function (res) {
            if (res.success === true) {
                $("#fotografias").html(res.html);
            }
        }        
    });
}

function downloadFile(href) {
    location.href = href;
}

$(document).ready(function () {
            
    loadFiles();
    loadPhotos();
    
});