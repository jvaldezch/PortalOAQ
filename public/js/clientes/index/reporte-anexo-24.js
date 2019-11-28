function verReporte(idCliente, idAduana, tipo) {
    var year = $("#selectyear_" + idAduana).val();
    var mes = $("#selectmonth_" + idAduana).val();
    
    int_d = new Date(year, parseInt(mes) - 1, 1);
    end_d = new Date(year, parseInt(mes), 0);   
    
    var fechaIni = int_d.toISOString().substring(0, 10);
    var fechaFin = end_d.toISOString().substring(0, 10);
    window.open("/clientes/get/reporte?idAduana="+ idAduana + "&idCliente="+ idCliente + "&tipo="+ tipo + "&fechaIni="+ fechaIni + "&fechaFin=" + fechaFin, '_blank', 'toolbar=0,location=0,menubar=0,height=550,width=800,scrollbars=yes');
}
function exportReport(patente, aduana, tipo, rfc) {
    window.open("/clientes/data/excel-reporte-anexo?patente=" + patente + "&aduana=" + aduana + "&year=" + $("#selectyear_" + patente + "_" + aduana).val() + "&month=" + $("#selectmonth_" + patente + "_" + aduana).val() + "&rfc=" + rfc + "&tipo=" + tipo + "&version=2", '_blank', 'toolbar=0,location=0,menubar=0,height=550,width=800,scrollbars=yes');
}

function cnh(patente, aduana, tipo, rfc) {
    var y = $("#selectyear_" + patente + "_" + aduana).val();
    var m = $("#selectmonth_" + patente + "_" + aduana).val();
    var date = new Date();
    var firstDay = new Date(y, m - 1, 1);
    var lastDay = new Date(y, m, 0);
    window.open("/automatizacion/reportes/reportes?patente=" + patente + "&aduana=" + aduana +  "&rfc=" + rfc + "&tipo=" + tipo + "&fechaIni=" + firstDay.toLocaleFormat('%Y-%m-%d') + "&fechaFin=" + lastDay.toLocaleFormat('%Y-%m-%d'), '_blank', 'toolbar=0,location=0,menubar=0,height=550,width=800,scrollbars=yes');
}