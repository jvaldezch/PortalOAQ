/**
 * programmed by Jvaldez at gmail
 * 2015.may.14
 */


$(document).ready(function () {

    $("#table").dataTable({
        "sDom": "<'traffic-cols'<'traffic-col-50'l><'traffic-col-50'f><'traffic-clear-5'>t<'traffic-clear-5'><'traffic-col-50'i><'traffic-col-50'p><'traffic-clear-5'>>",
        "sPaginationType": "bootstrap",
        "oLanguage": {
            "sLengthMenu": "_MENU_ registros por página"
        },
        "iDisplayLength": 10,
        "aaSorting": [[10, "desc"]]
    });
    
});