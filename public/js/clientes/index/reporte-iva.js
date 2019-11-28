$(document).ready(function () {
    
    $(document.body).on("click", "#submit", function (ev) {
        ev.preventDefault();
        if ($('#form').valid()) {
            window.open("/clientes/get/reporte-iva?aduana=" + $('#aduana').val() + "&year=" + $('#year').val() + "&mes=" + $('#mes').val(), '_blank', 'toolbar=0,location=0,menubar=0,height=550,width=800,scrollbars=yes');
        }
    });
    
});
