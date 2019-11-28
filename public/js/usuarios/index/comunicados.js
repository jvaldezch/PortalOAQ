$(document).ready(function () {

    $(document.body).on("click", "#selectAll", function () {
        $("input:checkbox").not(this).prop("checked", this.checked);
    });
    
    $(document.body).on("click", "#submit", function (ev) {
        ev.preventDefault();
        var checkValues = $(".sendEmail:checked").map(function () {
            return $(this).attr("id");
        }).get();        
        console.log(checkValues);
        if (checkValues.length === 0) {
            alert("No ha seleccionado ningun usuario");
            return false;
        }
        if ($("#form").valid()) {
            $("#form").ajaxSubmit({
                url: "/usuarios/post/enviar-comunicado",
                type: "post",
                dataType: "json",
                success: function (res) {
                    if (res.success === false) {
                        alert(res.message);
                    }
                }
            });
            return true;
        }
        return false;
    });
    
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
            from: {required: true},
            subject: {required: true},
            body: {required: true}
        },
        messages: {
            from: {
                required: "Campo necesario"
            },
            subject: {
                required: "Campo necesario"
            },
            body: {
                required: "Campo necesario"
            }
        }
    });

});