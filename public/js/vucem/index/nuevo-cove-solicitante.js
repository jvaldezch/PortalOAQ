$(document).ready(function () {

    var Current;

    $("#firmante").change(function () {        
        if ($("#firmante").val() !== '') {
            Current = $("#firmante").val();
            var url = "/vucem/data/obtener-aduanas?rfc=" + $("#firmante").val();
            if (url.indexOf('#') === 0) {
                $(url).modal('open');
            } else {
                $.get(url, function (data) {
                    $('<div class="modal hide fade" style="width: 450px; margin-left: -275px">' + data + '</div>')
                            .modal()
                            .on('hidden', function () {
                                $(this).remove();
                            });
                }).success(function () {
                    $('input:text:visible:first').focus();
                    $.unblockUI();
                    $(".blockUI").fadeOut("slow");
                    if ($("#firmante").val() == 'MALL640523749') {
                        $('#tipoFigura').val(1);
                        $.cookie('portalCoveFigura', 1, { expires: 7, path: '/' });
                    }
                });
            }
        }
    });

    $("#tipoOperacion").change(function () {
        Cookies.set('portalTipoOperacion', $(this).val(), { expires: 7, path: '' });
    });

    $("#form").validate({
        errorPlacement: function (error, element) {
            $(element)
                    .closest("form")
                    .find("label[for='" + element.attr("id") + "']")
                    .append(error);
        },
        errorElement: "span",
        errorClass: "traffic-error-span",
        rules: {
            firmante: "required",
            Patente: "required",
            Aduana: "required",
            tipoOperacion: "required",
            tipoFigura: "required"
        },
        messages: {
            firmante: "[Es necesario el firmante]",
            Patente: "[Es necesario la patente]",
            Aduana: "[Es necesario la aduana]",
            tipoOperacion: "[Es necesario el tipo de operación]",
            tipoFigura: "[Es necesario el tipo de figura]"
        }
    });

    $("#submit").one('click', function (e) {
        e.preventDefault();
        if ($("#form").valid()) {
            $("#form").submit();
            return true;
        }
    });
});