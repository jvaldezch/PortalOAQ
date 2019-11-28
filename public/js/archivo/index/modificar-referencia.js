
$(document).ready(function () {
    
    $("#form").validate({
        errorPlacement: function (error, element) {
            $(element)
                    .closest("form")
                    .find("label[for=\"" + element.attr("name") + "\"]")
                    .append(error);
        },
        errorElement: "span",
        errorClass: "traffic-error",
        rules: {
            npatente: {required: true},
            naduana: {required: true},
            npedimento: {
                required: true,
                minlength: 7
            },
            nreferencia: {required: true},
            nrfc: {required: true}
        },
        messages: {
            npatente: {required: "Proporcionar patente"},
            naduana: {required: "Proporcionar aduana"},
            npedimento: {
                required: "Proporcionar pedimento",
                minlength: "Pedimento debe ser de 7 numeros"
            },
            nreferencia: {required: "Proporcionar referencia"},
            nrfc: {required: "Proporcionar RFC"}
        }
    });

    $(document).on("click", "#submit", function (e) {
        e.preventDefault();
        if ($("#form").valid()) {
            $("#form").ajaxSubmit({
                url: "/archivo/ajax/actualizar-expediente-datos",
                type: "post",
                dataType: "json",
                success: function (res) {
                    if (res.success === true) {
                        document.location = res.href;
                    } else {
                        alert("Ocurrio un error.");
                    }
                }
            });
        }
    });

    $("#nreferencia, #nrfc").on("input", function (evt) {
        var input = $(this);
        var start = input[0].selectionStart;
        $(this).val(function (_, val) {
            return val.toUpperCase();
        });
        input[0].selectionStart = input[0].selectionEnd = start;
    });
    
});