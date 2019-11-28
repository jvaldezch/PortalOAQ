/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$(document).ready(function () {
    
    $("#customersList").dataTable({
        "sDom": "<'traffic-cols'<'traffic-col-50'l><'traffic-col-50'f><'traffic-clear-5'>t<'traffic-clear-5'><'traffic-col-50'i><'traffic-col-50'p><'traffic-clear-5'>>",
        "sPaginationType": "bootstrap",
        "oLanguage": {
            "sLengthMenu": "_MENU_ registros por página"
        },
        "iDisplayLength": 30,
        "aaSorting": [[30, "desc"]]
    });
    
    $("#file").jqm({
        ajax: "@href",
        modal: true,
        trigger: ".openFile"
    });
    
    $("#fecha").datepicker({
        calendarWeeks: true,
        autoclose: true,
        language: "es",
        format: "yyyy-mm-dd",
        endDate: "+0d"
    });
    
    $(document.body).on("click", ".openFileTab", function (ev) {
        ev.preventDefault();
        var wdw = window.open($(this).attr("href"), "ArchivoM3", "width=950,height=480"); 
    });
    
});