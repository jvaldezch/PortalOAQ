$(document).ready(function () {
    
    $("#form").validate({
        errorPlacement: function (error, element) {
            $(element)
                    .closest("form")
                    .find("label[for='" + element.attr("id") + "']")
                    .append(error);
        },
        errorElement: "span",
        rules: {
            cadena: "required"
        },
        messages: {
            cadena: " (Proporcione cadena)"
        }
    });
    
    $(document.body).on("click", "#updateWs", function (e) {
        e.preventDefault();
        $.ajax({
            url: "/usuarios/ajax/actualizar-ws",
            cache: false,
            type: "post",
            dataType: "json",
            data: {id: $("#id").val(), ws: $("#ws").val()},
            success: function (res) {
                if (res.success === true) {
                    window.location.href = "/usuarios/index/editar-fiel?id=" + $("#id").val();
                }
            }
        });
    });

    $("#update").click(function (e) {
        e.preventDefault();
        if ($("#form").valid()) {
            $("#form").ajaxSubmit({
                dataType: "json",
                success: function (res) {
                    if (res.success === true) {
                        $("#firma").val(res.firma);
                    }
                }
            });
        }
    });
    
    $("#changeCert").on("click",function (e) {
        e.preventDefault();
    });

    $("#cadena").on('input', function (evt) {
        var input = $(this);
        var start = input[0].selectionStart;
        $(this).val(function (_, val) {
            return val.toUpperCase();
        });
        input[0].selectionStart = input[0].selectionEnd = start;
    });
    
    $(document.body).on('change', '#cadenaOriginal', function() {
        $.ajax({url: "/usuarios/get/obtener-firma", cache: false, dataType: "json", type: "POST",
            data: {idFiel: $("#id").val(), cadena: $(this).val()},
            success: function (res) {
                if (res.success === true) {
                    $("#firmaDigital").val(res.firma);
                }
            }
        });
    });
    
    $(document.body).on("click", "#delete", function() {
        var id = $("#id").val();
        $.confirm({title: "Confirmar", content: "¿Está seguro de que desea eliminar el sello?", escapeKey: "cerrar", boxWidth: "250px", useBootstrap: false,
            buttons: {
                si: {
                    btnClass: "btn-blue",
                    action: function () {
                        $.post("/usuarios/ajax/borrar-sello", {id: id})
                                .done(function (res) {
                                    if (res.success === true) {
                                        window.location.href = "/usuarios/index/fiel";
                                    }
                                });
                    }
                },
                no: function () {}
            }
        });
    });
    
    $("input[name='crypt']").on("click",function() {
        var r = confirm("¿Está seguro que desea cambiar el tipo de encriptación?");
        if (r === true) {
            $.ajax({
                url: '/usuarios/ajax/encriptacion-sello',
                cache: false,
                type: 'post',
                dataType: 'json',
                data: { id: $("#id").val(), crypt: $(this).val()},
                success: function (res) {
                    if (res.success === true) {
                        window.location.href = '/usuarios/index/editar-fiel?id=' + $("#id").val();
                    }
                }
            });
        }
    });
});