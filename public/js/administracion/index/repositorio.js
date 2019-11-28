/**
 * programmed by Jvaldez at gmail
 * 2015.dic.04
 */

$(document).ready(function () {

    $('#dp4').datepicker({
        calendarWeeks: true,
        autoclose: true,
        language: 'es'
    }).on('changeDate', function (ev) {
        $('#fechaIni').val($('#dp4').data('date'));
        $('#dp4').datepicker('hide');
    });
    $('#dp5').datepicker({
        calendarWeeks: true,
        autoclose: true,
        language: 'es'
    }).on('changeDate', function (ev) {
        $('#fechaFin').val($('#dp5').data('date'));
        $('#dp5').datepicker('hide');
    });

});
