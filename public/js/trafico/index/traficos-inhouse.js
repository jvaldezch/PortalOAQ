/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function zeroPad(num, places) {
  var zero = places - num.toString().length + 1;
  return Array(+(zero > 0 && zero)).join("0") + num;
}

$(document).ready(function() {
  var dg = $("#dg").edatagrid();

  dg.edatagrid({
    pagination: true,
    singleSelect: true,
    striped: true,
    rownumbers: true,
    fitColumns: false,
    pageSize: 20,
    idField: "id",
    url: "/trafico/crud/traficos",
    updateUrl: "/trafico/crud/trafico-actualizar",
    rowStyler: function(index, row) {},
    onClickRow: function(index, row) {},
    onBeginEdit: function(index, row) {},
    onBeforeEdit: function(index, row) {},
    onAfterEdit: function(index, row) {
      if (row.fechaPago != "") {
        row.estatus = 2;
      }
      if (row.fechaLiberacion != "") {
        row.estatus = 3;
      }
      row.editing = false;
      $(this).datagrid("refreshRow", index);
    },
    onCancelEdit: function(index, row) {
      row.editing = false;
      $(this).datagrid("refreshRow", index);
    },
    onAdd: function(index, row) {},
    onRowContextMenu: function(e, index, row) {
      e.preventDefault();
      $("#mm").menu("show", {
        left: e.pageX,
        top: e.pageY
      });
    },
    remoteFilter: true,
    toolbar: [
      {
        text: "Guardar",
        iconCls: "icon-save",
        handler: function() {
          $("#dg").edatagrid("saveRow");
        }
      },
      {
        text: "Cancelar",
        iconCls: "icon-undo",
        handler: function() {
          $("#dg").edatagrid("cancelRow");
        }
      },
      {
        text: "Actualizar",
        iconCls: "icon-reload",
        handler: function() {
          $("#dg").edatagrid("reload");
        }
      }
    ],
    frozenColumns: [
      [
        {
          field: "estatus",
          width: 20,
          title: "",
          formatter: formatEstatus
        },
        {
          field: "imex",
          width: 30,
          checkbox: false,
          title: "",
          formatter: formatImpo
        },
        {
          field: "msg",
          width: 30,
          checkbox: false,
          title: "",
          formatter: formatMensajero
        },
        { field: "patente", width: 50, title: "Patente" },
        { field: "aduana", width: 50, title: "Aduana" },
        { field: "pedimento", width: 80, title: "Pedimento" },
        {
          field: "referencia",
          width: 100,
          title: "Referencia",
          formatter: formatLink
        }
      ]
    ],
    columns: [
      [
        { field: "cvePedimento", width: 40, title: "Cve." },
        { field: "nombreCliente", width: 300, title: "Nombre Cliente" },
        { field: "nombre", width: 120, title: "Usuario" },
        {
          field: "fechaEta",
          width: 90,
          title: "F. ETA",
          editor: { type: "datetimebox" },
          options: { required: false, validType: "datetime" }
        },
        {
          field: "fechaEnvioDocumentos",
          width: 105,
          title: "F. Envio Doctos.",
          editor: { type: "datetimebox" },
          options: { required: false, validType: "datetime" }
        },
        {
          field: "fechaPago",
          width: 95,
          title: "F. Pago",
          editor: { type: "datetimebox" },
          options: { required: false, validType: "datetime" }
        },
        {
          field: "fechaLiberacion",
          width: 95,
          title: "F. Liberación",
          editor: { type: "datetimebox" },
          options: { required: false, validType: "datetime" }
        },
        {
          field: "fechaFacturacion",
          width: 100,
          title: "F. Facturación",
          editor: { type: "datetimebox" },
          options: { required: false, validType: "datetime" }
        },
        {
          field: "blGuia",
          width: 150,
          title: "BL/Guía",
          editor: { type: "text" }
        },
        {
          field: "contenedorCaja",
          width: 150,
          title: "Cont./Caja",
          editor: { type: "text" }
        },
        {
          field: "idPlanta",
          width: 150,
          title: "Planta",
          formatter: function(val, row) {
            return row.descripcionPlanta;
          },
          editor: {
            type: "combobox",
            options: {
              valueField: "id",
              textField: "descripcion",
              panelWidth: 250,
              panelHeight: 90
            }
          }
        },
        {
          field: "fechaInstruccionEspecial",
          width: 90,
          title: "Justificación",
          formatter(value, row) {
            if (row.fechaInstruccionEspecial !== null) {
              return "Si";
            }
          }
        },
        {
          field: "diasDespacho",
          width: 100,
          title: "Días Despacho",
          formatter(value, row) {
            if (row.fechaLiberacion !== null) {
              return value;
            }
          }
        },
        {
          field: "cumplimientoAdministrativo",
          width: 100,
          title: "Cump. Admin.",
          formatter:function(value, row){
            if (row.cumplimientoAdministrativo == 1) {
              return 'Si';
            }
            if (row.cumplimientoAdministrativo == 0) {
              return 'No';
            }
          },
          editor:{
            type:'combobox',
            options:{
              panelHeight:'auto',
              valueField: 'value',
              textField: 'name',
              data:[
                {
                  value: '0',
                  name: 'No'
                },
                {
                  value: '1',
                  name: 'Si'
                }
              ]
            }
          }
        },
        {
          field: "cumplimientoOperativo",
          width: 100,
          title: "Cump. Ope.",
          formatter:function(value, row){
            if (row.cumplimientoOperativo == 1) {
              return 'Si';
            }
            if (row.cumplimientoOperativo == 0) {
              return 'No';
            }
          },
          editor:{
            type:'combobox',
            options:{
              panelHeight:'auto',
              valueField: 'value',
              textField: 'name',
              data:[
                {
                  value: '0',
                  name: 'No'
                },
                {
                  value: '1',
                  name: 'Si'
                }
              ]
            }
          }
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

  initGeneral();

  var today = new Date();
  var dd = today.getDate();
  var mm = today.getMonth() + 1;
  var yyyy = today.getFullYear();

  var dateini;
  var dateend;

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

  $("#dateini").datebox({
    value: dateini,
    required: true,
    showSeconds: false,
    onChange:  function(newValue) {
        Cookies.set('dateini', newValue);
        dg.edatagrid('reload');
    }
  });

  $("#dateend").datebox({
    value: dateend,
    required: true,
    showSeconds: false,
    onChange:  function(newValue) {
        Cookies.set('dateend', newValue);
        dg.edatagrid('reload');
    }
  });
  
});
