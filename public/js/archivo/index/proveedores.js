
function crearArchivoZip(ids) {
    $.ajax({
        url: '/archivo/index/crear-zip-proveedores',
        type: 'post',
        data: {ids: ids, pdf: $("#includepdf").is(':checked') ? 1 : 0, all: $("#selectall").is(':checked') ? 1 : 0},
        dataType: 'json',
        success: function (res) {
            window.location = '/archivo/index/download-created-zip?filename=' + res;
        }
    });
}

$(document).ready(function () {
    
    $('#fechaIni').datepicker({
        format: 'yyyy-mm-dd',
        language: "es",
        todayBtn: true,
        todayHighlight: true
    });

    $('#fechaFin').datepicker({
        format: 'yyyy-mm-dd',
        language: "es",
        todayBtn: true,
        todayHighlight: true
    });

    $('#rfcCliente').keyup(function () {
        this.value = this.value.toUpperCase();
    });

    $(document.body).on('click', '#selectAll', function () {
        $('.cuentas').attr('checked', this.checked);
    });
    
    $('#form').validate({
        errorPlacement: function (error, element) {
            $(element)
                    .closest("form")
                    .find("#" + element.attr("id"))
                    .after(error);
        },
        errorElement: "span",
        errorClass: "errorlabel",
        rules: {
            rfc: {required: true}
        },
        messages: {
            rfc: "SE REQUIERE"
        }
    });
    
    $(document.body).on('click', '#submit', function (ev) {
        ev.preventDefault();
        if ($('#form').valid()) {
            
        }
    });

    $('#downloadZip').click(function () {
        var ids = [];
        var boxes = $('input[name=cuentas]:checked');
        if ((boxes).size() == 0) {
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
    
});
