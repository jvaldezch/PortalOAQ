let dg;

let dateini;
let dateend;

window.zeroPad = function (num, places) {
    let zero = places - num.toString().length + 1;
    return Array(+(zero > 0 && zero)).join("0") + num;
}

window.formatLink = function (val, row) {
    return '<a href="/archivo/cuentas/ver-folio?id=' + row.id + '">' + row.folio + '</a>';
}

$(document).ready(function () {

    let today = new Date();
    let dd = today.getDate();
    let mm = today.getMonth() + 1;
    let yyyy = today.getFullYear();

    if (Cookies.get("dateini") !== undefined) {
        dateini = Cookies.get("dateini");
    } else {
        dateini = yyyy + "-" + zeroPad(mm, 2) + "-01";
        Cookies.set("dateini", dateini);
    }

    if (Cookies.get("dateend") !== undefined) {
        dateend = Cookies.get("dateend");
    } else {
        dateend = yyyy + "-" + zeroPad(mm, 2) + "-" + zeroPad(dd, 2);
        Cookies.set("dateend", dateend);
    }

    dg = $("#dg").edatagrid();

    dg.edatagrid({
        pagination: true,
        singleSelect: true,
        striped: true,
        rownumbers: true,
        fitColumns: false,
        pageSize: 20,
        idField: "id",
        url: "/archivo/get/cuentas-gastos",
        queryParams: {
            fechaInicio: dateini,
            fechaFin: dateend
        },
        onClickRow: function (index, row) { },
        remoteFilter: true,
        toolbar: [

        ],
        frozenColumns: [
            [
                {
                    field: "rfcCliente",
                    width: 120,
                    title: "RFC",
                },
                { field: "idTrafico", width: 70, title: "ID Trafico" },
                { field: "patente", width: 50, title: "Patente" },
                { field: "aduana", width: 50, title: "Aduana" },
                { field: "pedimento", width: 80, title: "Pedimento" },
                {
                    field: "referencia",
                    width: 100,
                    title: "Referencia"
                },
                {
                    field: "folio", width: 90, title: "Folio SICA",
                    formatter: formatLink
                }
            ]
        ],
        columns: [
            [
                { field: "nomCliente", width: 320, title: "Nombre Cliente" },
                { field: "referenciaFactura", width: 150, title: "Ref. Fact." },
                {
                    field: "fechaPago",
                    width: 130,
                    title: "F. Pago",
                    editor: { type: "datetimebox" },
                    options: { required: false, validType: "datetime" }
                },
                {
                    field: "fechaFacturacion",
                    width: 130,
                    title: "F. Facturación",
                    editor: { type: "datebox" },
                    options: { required: false, validType: "date" }
                },
                {
                    field: "caja",
                    width: 100,
                    title: "Caja",
                    editor: { type: "text" }
                },
                {
                    field: "observaciones",
                    width: 250,
                    title: "Observaciones",
                    editor: { type: "text" }
                }
            ]
        ]
    });

    dg.edatagrid("enableFilter", []);

    $.each(['imex', 'msg', 'coves', 'rfcCliente', 'caja', 'edocuments', 'upl', 'ie', 'estatusExpediente', 'cvePedimento', 'fechaPago', 'estatus', 'fechaEtd', 'fechaLiberacion', 'fechaEntrada', 'fechaPresentacion', 'fechaFacturacion', 'fechaEta', 'fechaRevalidacion', 'fechaPrevio', 'fechaDespacho', 'fechaEtaAlmacen', 'fechaEnvioProforma', 'fechaEnvioDocumentos', 'fechaNotificacion', 'fechaDeposito', 'fechaCitaDespacho', 'fechaProformaTercero', 'fechaArriboTransfer', 'fechaSolicitudTransfer', 'fechaVistoBueno', 'facturas', 'cantidadFacturas', 'cantidadPartes', 'almacen', 'fechaVistoBuenoTercero', 'fechaComprobacion', 'tipoCarga', 'fechaEir', 'fechaInstruccionEspecial', 'idPlanta', 'diasDespacho', 'estatusRepositorio', 'observaciones', 'cumplimientoAdministrativo', 'cumplimientoOperativo', 'ccConsolidado', 'semaforo'], function (index, value) {
        $(".datagrid-editable-input[name='" + value + "']").hide();
    });

});