$(document).ready(function () {
    
    $("#form").validate({
        errorPlacement: function (error, element) {
            $(element)
                    .closest("form")
                    .find("#" + element.attr("id"))
                    .after(error);
        },
        errorElement: "span",
        errorClass: "traffic-error",
        rules: {
            appName: {required: true},
            sistemaOperativo: {required: true},
            versionName: {required: true},
            versionCode: {required: true},
            archivo: {
                required: true,
                extension: "apk"
            }
        },
        messages: {
            appName: {required: "Campo necesario"},
            sistemaOperativo: {required: "Campo necesario"},
            versionName: {required: "Campo necesario"},
            versionCode: {required: "Campo necesario"},
            archivo: {required: "Campo necesario"}
        }
    });
    
    $(document.body).on("click", "#submit", function (ev) {
        ev.preventDefault();
        if ($("#form").valid()) {
            $("#form").ajaxSubmit({
                url: "/usuarios/post/aplicacion-subir",
                type: "post",
                dataType: "json",
                success: function (res) {
                    if (res.success === true) {
                        window.location = "/usuarios/index/aplicaciones";
                    } else {
                        alert(res.message);                        
                    }
                }
            });
            return true;
        }
    });
    
});