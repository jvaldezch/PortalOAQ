/**
 * programmed by Jvaldez at gmail
 * 2015.may.14
 */



function updateM3(res, statusText, xhr, $form) {
    if(res.success === true) {
        $("#m3").html(res.m3);
        $("#prev").html(res.prev);
        $("#pago").html(res.pago);
    }
}
function viewFile(id, type) {
    $.ajax({
        url: '/archivo/ajax/get-file-content',
        type: "post",
        cache: false,
        dataType: 'json',
        data: {id: id, type: type}
    }).done(function (res) {
        if(res.success === true) {
            $("#filepreview").html(res.html);
            $("#link").html(res.link);
        }
    });
}
function downloadM3(id, type) {
    $.fileDownload('/archivo/index/get-file-content?id=' + id + '&type=' + type + '&download=true')
            .fail(function () {
                alert('¡No se pudo descargar el archivo!');
            });
}
function downloadPaid(id, type) {
    $.fileDownload('/archivo/index/get-file-content?id=' + id + '&type=' + type + '&download=true')
            .fail(function () {
                alert('¡No se pudo descargar el archivo!');
            });
}
function downloadPrev(id, type) {
    $.fileDownload('/archivo/index/get-file-content?id=' + id + '&type=' + type + '&download=true')
            .fail(function () {
                alert('¡No se pudo descargar el archivo!');
            });
}

$(document).ready(function () {
    $("#analysis").validate({
        errorPlacement: function (error, element) {
            $(element)
                    .closest("form")
                    .find("label[for='" + element.attr("id") + "']")
                    .append(error);
        },
        errorElement: "span",
        errorClass: "traffic-error",
        rules: {
            pedimento: {
                required: true,
                minlength: 7,
                digits: true
            },
            patente: {
                required: true,
                minlength: 4,
                digits: true
            }
        },
        messages: {
            pedimento: {
                required: "Proporcionar el pedimento",
                minlength: "Minimo 7 digitos",
                digits: "Pedimento deben ser solo números"
            },
            patente: {
                required: "Proporcionar patente",
                minlength: "Minimo 4 digitos",
                digits: "Patente deben ser solo números"
            }
        }
    });

    var optionsinvd = {
        url: "/archivo/ajax/analisis-m3",
        type: "post",
        dataType: "json",
        timeout: 5000,
        success: updateM3
    };

    $("#do-analysis").click(function (e) {
        e.preventDefault();
        if ($("#analysis").valid()) {
            $("#analysis").ajaxSubmit(optionsinvd);
        }
    });
    
});
