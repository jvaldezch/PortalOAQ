/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$(document).ready(function () {

    $("#format-listen").validate({
        errorElement: "span",
        errorClass: "format-listen-error",
        rules: {
            "name": {
                required: true
            },
            "area": {
                required: true
            },
            "office": {
                required: true
            },
            "matter": {
                required: true
            },
            "about": {
                required: true
            },
            "tell-us": {
                required: true
            },
            "how": {
                required: true
            }
        },
        messages: {
            "name": {
                required: " Campo necesario"
            },
            "area": {
                required: " Campo necesario"
            },
            "office": {
                required: " Campo necesario"
            },
            "matter": {
                required: " Campo necesario"
            },
            "about": {
                required: " Campo necesario"
            },
            "tell-us": {
                required: " Campo necesario"
            },
            "how": {
                required: " Campo necesario"
            }
        }
    });

    $(document).on("input", "#name, #area, #office, #other-text, #about, #tell-us, #how", function() {
        let input = $(this);
        let start = input[0].selectionStart;
        $(this).val(function (_, val) {
            return val.toUpperCase();
        });
        input[0].selectionStart = input[0].selectionEnd = start;
    });

    $(document.body).on("click", "#submit", function (e) {
        e.preventDefault();
        if ($("#format-listen").valid()) {
            $("#formFiles").ajaxSubmit({type: "post", url: "/principal/post/enviar-formato-queja",
                beforeSend: function () {
                },
                success: function () {
                }
            });

        }
    });
    
});

