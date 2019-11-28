$(document).ready(function () {
    
    $("#form").validate({
        errorPlacement: function (error, element) {
            $(element)
                    .closest("form")
                    .find("label[for='" + element.attr("id") + "']")
                    .append(error);
        },
        errorElement: "span",
        errorClass: "traffic-error",
        rules: {
            rfc: { required: true },
            url: { required: true },
            type: { required: true },
            user: { required: true },
            password: { required: true },
            port: { required: true},
            remoteFolder: { required: true}
        },
        messages: {
            rfc: { required: "Campo necesario" },
            url: { required: "Campo necesario" },
            type: { required: "Campo necesario" },
            user: { required: "Campo necesario" },
            password: { required: "Campo necesario" },
            port: { required: "Campo necesario" },
            remoteFolder: { required: "Campo necesario" }
        }
    });
    
    $("#submit").click(function (e) {
        e.preventDefault();
        if ($("#form").valid()) {
            $("#form").ajaxSubmit({
                cache: false,
                type: "post",
                dataType: "json",
                url: "/archivo/ajax/nuevo-ftp",
                success: function (res) {
                    if(res.success === true) {
                        window.location.replace("/archivo/index/ftp");
                    }
                }
            });
        }
    });
    
    $("#rfc").on("input", function (evt) {
        $("#errors").html("");
        var input = $(this);
        var start = input[0].selectionStart;
        $(this).val(function (_, val) {
            return val.toUpperCase();
        });
        input[0].selectionStart = input[0].selectionEnd = start;
    });
    
});