/**
 * programmed by Jvaldez at gmail
 * 2015.dic.04
 */

$(document).ready(function () {

    $("#policy").validate({
        errorPlacement: function (error, element) {
            $(element)
                    .closest("form")
                    .find("label[for='" + element.attr("id") + "']")
                    .append(error);
        },
        errorElement: "span",
        errorClass: "traffic-error",
        rules: {
            tipoArchivo: {required: true},
            tipoPoliza: {required: true},
            fecha: {required: true},
            archivo: {required: true},
            factura: {
                required: function (element) {
                    return $('input[name="tipo"]:checked').val() === '3';
                }
            }
        },
        messages: {
            tipoArchivo: {required: "Se requiere."},
            tipoPoliza: {required: "Se requiere."},
            fecha: {required: "Se requiere."},
            factura: {required: "Se requiere."},
            archivo: {required: "Se requiere."}
        }
    });

    $("#submit").click(function (e) {
        e.preventDefault();
        if ($("#policy").valid()) {
            $("#policy").ajaxSubmit({
                type: 'post',
                dataType: 'json',
                success: function (res) {
                    if (res.success === true) {
                        window.location.href = '/administracion/index/repositorio';
                    }
                }
            });
        }
    });

    $('#fecha').datepicker({
        calendarWeeks: true,
        autoclose: true,
        language: 'es',
        format: 'yyyy-mm-dd',
        onSelect: function(dateText, inst) { 
            $('#fecha').val(dateText);
        }
    });

    $("input:radio[name=tipoPoliza]").on('click', function () {
        $(this).attr('checked', 'checked');
        $('#submit').attr('disabled', 'disabled');
        $('#tipoArchivo')
                .attr('disabled', 'disabled')
                .find('option')
                .remove()
                .end()
                .append('<option value="">---</option>')
                .val("");
        $.ajax({
            url: "/administracion/data/tipo-archivo-poliza",
            type: "post",
            dataType: "json",
            data: {id: $(this).val()},
            timeout: 3000,
            success: function (res) {
                if (res.success === true) {
                    $.each(res.values, function (i, value) {
                        $('#tipoArchivo').append($('<option>').text(value).attr('value', i));
                    });
                    $('#tipoArchivo').removeAttr("disabled");
                    $('#submit').removeAttr("disabled");
                }
            }
        });
    });

});
