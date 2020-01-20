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
            "tellus": {
                required: true
            },
            "how": {
                required: true
            },
            "othertext": {
                required: function(element) {
                    return ($("input[name='matter']:checked").val() === 'other');
                }
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
            "tellus": {
                required: " Campo necesario"
            },
            "how": {
                required: " Campo necesario"
            },
            "othertext": {
                required: " Campo necesario"
            }
        }
    });

    $(document).on("input", "#name, #area, #office, #othertext, #about, #tellus, #how", function() {
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
            $("#format-listen").ajaxSubmit({type: "post", url: "/principal/post/enviar-formato-queja",
                beforeSend: function () {
                    $("#format-listen").LoadingOverlay("show", {color: "rgba(255, 255, 255, 0.9)"});
                },
                success: function (res) {
                    $("#format-listen").LoadingOverlay("hide");
                    if (res.success === true) {
                        $('#format-listen')[0].reset();
                        $.toast({text: "<strong>Información enviada. ¡Gracias!</strong>", bgColor: "green", stack : 3, position : "bottom-right"});
                    }
                }
            });

        }
    });

    $(document.body).on("change", "input[name=matter]", function (e) {
        let v = $(this).val();
        if (v === 'other') {
            $("#othertext").prop("disabled", false)
        } else {
            $("#othertext").attr("disabled", "true")
        }
    });
    
});

