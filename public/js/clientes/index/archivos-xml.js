$(document).ready(function () {
    var elementsText = ["bootstrap-rfc", 'bootstrap-nombre'];
    $.each(elementsText, function (index, value) {
        $('#' + value).keyup(function () {
            $(this).val($(this).val().toUpperCase());
        });
    });

    $('#selectall').click(function () {
        $('.cuentas').attr('checked', this.checked);
    });

    $('#downloadzip').click(function () {
        var ids = [];
        var boxes = $('input[name=cuentas]:checked');
        if ((boxes).size() === 0) {
            if (confirm('No ha seleccionado un archivo ¿desea incluir todos?')) {
                crearArchivoZip(0);
            }
        }
        if ((boxes).size() > 0) {
            $(boxes).each(function () {
                ids.push($(this).val());
            });
            crearArchivoZip(ids);
        }
    });

    function crearArchivoZip(ids) {
        $.ajax({
            url: '/archivo/index/crear-zip',
            type: 'post',
            data: {ids: ids, pdf: $("#includepdf").is(':checked') ? 1 : 0},
            dataType: 'json',
            success: function (res) {
                window.location = '/archivo/index/download-created-zip?filename=' + res;
            }
        });
    }

    $("input[name='fechaIni']").datepicker({
        format: 'yyyy-mm-dd',
        language: "es"
    });
    $("input[name='fechaFin']").datepicker({
        format: 'yyyy-mm-dd',
        language: "es"
    });

    $('[data-toggle="modal"]').click(function (e) {
        e.preventDefault();
        var url = $(this).attr('href');
        if (url.indexOf('#') === 0) {
            $(url).modal('open');
        } else {
            $.get(url, function (data) {
                $('<div class="modal hide fade" style="width: 750px; margin-left: -375px">' + data + '</div>')
                        .modal()
                        .on('hidden', function () {
                            $(this).remove();
                        });
            }).success(function () {
                $('input:text:visible:first').focus();
            });
        }
    });
});