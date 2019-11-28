/**
 * programmed by Jvaldez at gmail
 * 2015.may.14
 */

$(document).ready(function () {

    $(document.body).on("click", ".document", function () {
        var boxes = $("input[class=document]:checked");
        var ids = [];
        $(boxes).each(function () {
            ids.push($(this).data("id"));
        });
        $.ajax({
            url: "/usuarios/post/actualizar-documentos",
            cache: false,
            type: "post",
            dataType: "json",
            data: {idUsuario: $("#idUsuario").val(), ids: ids},
            success: function (res) {
                if (res.success === true) {

                }
            }
        });
    });
    
    $.ajax({
        url: "/usuarios/get/obtener-documentos",
        cache: false,
        type: "get",
        dataType: "json",
        data: {idUsuario: $("#idUsuario").val()},
        success: function (res) {
            if (res.success === true) {
                $.each(res.ids, function (index, value) {
                    $('.document[data-id="' + value + '"]').prop('checked', true);
                });
            }
        }
    });

});