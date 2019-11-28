/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

$(document).ready(function () {

    $("#forgotPassword").validate({
        errorPlacement: function (error, element) {
            $(element)
                    .closest("form")
                    .find("#" + element.attr("id"))
                    .before(error);
        },
        errorElement: "span",
        errorClass: "error",
        rules: {
            username: "required"
        },
        messages: {
            username: "Se requiere usuario."
        }
    });

    $(document.body).on("click", "#submit", function (e) {
        e.preventDefault();
        if ($("#forgotPassword").valid()) {
            $("#forgotPassword").ajaxSubmit({
                cache: false,
                url: "/default/ajax/recover-password",
                type: "post",
                dataType: "json",
                timeout: 3000,
                success: function (res) {
                    if (res.success === true) {
                    } else {
                        if (res.username) {
                            $("#username").before("<span class=\"error\">" + res.username + "</span>");
                        }
                    }
                }
            });
        }
    });
});

