/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

let dg;

window.formatLink = function (val, row) {
    return `<a href="/manifestacion/index/editar?id=${row.id}">${row.referencia}</a>`;
}

$(document).ready(function () {

    var today = new Date();
    var dd = today.getDate();
    var mm = today.getMonth() + 1;
    var yyyy = today.getFullYear();

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
        url: "/manifestacion/get/todas",
        updateUrl: "/manifestacion/post/actualizar",
        queryParams: {
            fechaInicio: dateini,
            fechaFin: dateend
        },
        onClickRow: function (index, row) { },
        onBeginEdit: function (index, row) {
        },
        onBeforeEdit: function (index, row) { },
        onAfterEdit: function (index, row) {
        },
        onCancelEdit: function (index, row) {
        },
        onAdd: function (index, row) { },
        onRowContextMenu: function (e, index, row) {
        },
        remoteFilter: true,
        toolbar: [
            {
                text: "Guardar",
                iconCls: "icon-save",
                handler: function () {
                    $("#dg").edatagrid("saveRow");
                }
            },
            {
                text: "Cancelar",
                iconCls: "icon-undo",
                handler: function () {
                    $("#dg").edatagrid("cancelRow");
                }
            },
            {
                text: "Act.",
                iconCls: "icon-reload",
                handler: function () {
                    $("#dg").edatagrid("reload");
                }
            }
        ],
        frozenColumns: [
            [
                { field: "patente", width: 50, title: "Patente" },
                { field: "aduana", width: 50, title: "Aduana" },
                { field: "pedimento", width: 80, title: "Pedimento" },
                {
                    field: "referencia", width: 120, title: "Referencia",
                    formatter: formatLink
                },
            ]
        ],
        columns: [
            [
                { field: "nombreCliente", width: 320, title: "Nombre Cliente" },
                { field: "vinculacion", width: 120, title: "EDocument" },
                { field: "edocument", width: 120, title: "EDocument" },
                { field: "numOperacion", width: 120, title: "Num. Operacion" },
                { field: "tipoManifestacion", width: 120, title: "Tip. mani." },
                { field: "regimenAduanero", width: 120, title: "Regimen" },
                { field: "totalPrecioPagado", width: 120, title: "P. pagado" },
                { field: "totalPrecioPorPagar", width: 120, title: "P. X pagar" },
                { field: "totalIncrementables", width: 120, title: "Tot. Incre." },
                { field: "totalDecrementables", width: 120, title: "Tot. Decre." },
                { field: "valorAduana", width: 120, title: "Val. aduana" },
                { field: "fechaModulacion", width: 120, title: "F. modulacion" },
                { field: "creado", width: 120, title: "Creado" },
                { field: "actualizado", width: 120, title: "Actualizado" },
            ]
        ]
    });

    dg.edatagrid("enableFilter", []);

    var fields = [
        'vinculacion',
        'tipoManifestacion',
        'regimenAduanero',
        'totalPrecioPagado',
        'totalPrecioPorPagar',
        'totalIncrementables',
        'totalDecrementables',
        'valorAduana',
        'fechaModulacion',
        'creado',
        'actualizado'
    ];

    $.each(fields, function (index, value) {
        $(".datagrid-editable-input[name='" + value + "']").hide();
    });

});