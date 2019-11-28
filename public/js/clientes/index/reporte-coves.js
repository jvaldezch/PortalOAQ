$(document).ready(function() {
    
    $("#example").dataTable( {
        "sDom": "<'traffic-cols'<'traffic-col-50'l><'traffic-col-50'f><'traffic-clear-5'>t<'traffic-clear-5'><'traffic-col-50'i><'traffic-col-50'p><'traffic-clear-5'>>",
        "sPaginationType": "bootstrap",
        "oLanguage": {
                "sLengthMenu": "_MENU_ registros por página"
        },
        "iDisplayLength" : 25,
        "bStateSave": true,
        "aaSorting": [],
        "bSort": false
    });
    
    $("#fechaIni, #fechaFin").datepicker({
        calendarWeeks: true,
        autoclose: true,
        language: 'es',
        format: 'yyyy-mm-dd'
    });
    
    $(document.body).on("click", "#submit", function (evt) {
        evt.preventDefault();
        window.location.href = "/clientes/data/reporte-coves-excel?" + "fechaIni=" + $("#fechaIni").val() + "&fechaFin=" + $("#fechaFin").val();
    });
    
});

