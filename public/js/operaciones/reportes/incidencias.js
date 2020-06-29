var dg;

Date.prototype.yyyymmdd = function () {
  var mm = this.getMonth() + 1; // getMonth() is zero-based
  var dd = this.getDate();

  return [
    this.getFullYear(),
    (mm > 9 ? "" : "0") + mm,
    (dd > 9 ? "" : "0") + dd,
  ].join("-");
};

window.formatDate = function (value) {
  var date = new Date(value);
  return date.yyyymmdd();
};

$(document).ready(function () {
  dg = $("#dg").edatagrid();

  dg.edatagrid({
    pagination: true,
    singleSelect: true,
    striped: true,
    rownumbers: true,
    fitColumns: false,
    pageSize: 20,
    idField: "id",
    method: "GET",
    url: "/operaciones/reportes/obtener-incidencias",
    updateUrl: "/operaciones/reportes/actualizar-incidencia",
    rowStyler: function (index, row) {},
    queryParams: {},
    onClickRow: function (index, row) {},
    onBeginEdit: function (index, row) {},
    onBeforeEdit: function (index, row) {},
    onAfterEdit: function (index, row) {},
    onCancelEdit: function (index, row) {
      row.editing = false;
      $(this).datagrid("refreshRow", index);
    },
    onAdd: function (index, row) {},
    onRowContextMenu: function (e, index, row) {},
    remoteFilter: true,
    toolbar: [
      {
        text: "Guardar",
        iconCls: "icon-save",
        handler: function () {
          $("#dg").edatagrid("saveRow");
        },
      },
      {
        text: "Cancelar",
        iconCls: "icon-undo",
        handler: function () {
          $("#dg").edatagrid("cancelRow");
        },
      },
      {
        text: "Actualizar",
        iconCls: "icon-reload",
        handler: function () {
          $("#dg").edatagrid("reload");
        },
      },
    ],
    frozenColumns: [
      [
        {
          field: "edit",
          width: 24,
          title: "",
          formatter: function (val, row) {
            return (
              '<i class="fas fa-pencil-alt" data-id="' +
              row.id +
              '" style="cursor: pointer"></i>'
            );
          },
        },
        {
          field: "trash",
          width: 24,
          title: "",
          formatter: function (val, row) {
            return (
              '<i class="fas fa-trash-alt" data-id="' +
              row.id +
              '" style="cursor: pointer"></i>'
            );
          },
        },
        { field: "patente", width: 60, title: "Patente" },
        { field: "aduana", width: 60, title: "Aduana" },
        { field: "pedimento", width: 90, title: "Pedimento" },
        { field: "referencia", width: 100, title: "Referencia" },
      ],
    ],
    columns: [
      [
        {
          field: "fecha",
          width: 80,
          title: "Fecha",
          formatter: function (value, row) {
            if (value) {
              return formatDate(value);
            }
          },
        },
        { field: "nombre", width: 350, title: "Cliente" },
        { field: "tipoError", width: 90, title: "Tipo Error" },
        { field: "acta", width: 90, title: "Acta" },
        { field: "multa", width: 120, title: "Multa" },
        {
          field: "pagada",
          width: 60,
          title: "Pagada",
          formatter: function (value, row) {
            if (parseInt(value) === 1) {
              return "Si";
            } else {
              return "No";
            }
          },
        },
        { field: "responsable", width: 250, title: "Responsable" },
        { field: "observaciones", width: 250, title: "Observaciones" },
        { field: "comentarios", width: 250, title: "Comentarios" },
      ],
    ],
  });

  dg.edatagrid("enableFilter", []);

  $(document.body).on("click", ".fa-pencil-alt", function (ev) {
    ev.preventDefault();
    var id = $(this).data("id");
    $.confirm({
      title: "Editar incidencia",
      escapeKey: "cerrar",
      boxWidth: "550px",
      useBootstrap: false,
      type: "green",
      buttons: {
        guardar: {
          btnClass: "btn-green",
          action: function () {
            if ($("#form_incidence").valid()) {
              $("#form_incidence").ajaxSubmit({
                url: "/operaciones/reportes/actualizar-incidencia",
                dataType: "json",
                type: "POST",
                success: function (res) {
                  if (res.success === true) {
                    dg.edatagrid("reload");
                  } else {
                    $.alert({
                      title: "Error",
                      type: "red",
                      content: res.message,
                      boxWidth: "350px",
                      useBootstrap: false,
                    });
                    return false;
                  }
                },
              });
            } else {
              return false;
            }
          },
        },
        cerrar: { action: function () {} },
      },
      content: function () {
        var self = this;
        return $.ajax({
          url: "/operaciones/reportes/editar-incidencia?id=" + id,
          method: "get",
        })
          .done(function (res) {
            self.setContent(res.html);
          })
          .fail(function () {
            self.setContent("Something went wrong.");
          });
      },
    });
  });

  $(document.body).on("click", ".fa-trash-alt", function (ev) {
    ev.preventDefault();
    var id = $(this).data("id");
    $.confirm({
      title: '<i class="fas fa-exclamation-triangle"></i> Confirmar',
      escapeKey: "cerrar",
      boxWidth: "350px",
      useBootstrap: false,
      type: "red",
      buttons: {
        si: {
          btnClass: "btn-red",
          action: function () {
            $.ajax({
              url: "/operaciones/reportes/borrar-incidencia",
              dataType: "json",
              type: "POST",
              data: { id: id },
              success: function (res) {
                if (res.success === true) {
                  dg.edatagrid("reload");
                  return true;
                }
              },
            });
          },
        },
        no: { action: function () {} },
      },
      content:
        "<p>¿Está seguro que desea eliminar la carta de instrucción?</p>",
    });
  });

  $(document.body).on("click", "#nuevaIncidencia", function (ev) {
    ev.preventDefault();
    $.confirm({
      title: "Nueva incidencia",
      escapeKey: "cerrar",
      boxWidth: "550px",
      useBootstrap: false,
      type: "green",
      buttons: {
        agregar: {
          btnClass: "btn-green",
          action: function () {
            if ($("#form_incidence").valid()) {
              $("#form_incidence").ajaxSubmit({
                url: "/operaciones/reportes/agregar-incidencia",
                dataType: "json",
                type: "POST",
                success: function (res) {
                  if (res.success === true) {
                    dg.edatagrid("reload");
                  } else {
                    $.alert({
                      title: "Error",
                      type: "red",
                      content: res.message,
                      boxWidth: "350px",
                      useBootstrap: false,
                    });
                    return false;
                  }
                },
              });
            } else {
              return false;
            }
          },
        },
        cerrar: { action: function () {} },
      },
      content: function () {
        var self = this;
        return $.ajax({
          url: "/operaciones/reportes/nueva-incidencia",
          method: "get",
        })
          .done(function (res) {
            self.setContent(res.html);
          })
          .fail(function () {
            self.setContent("Something went wrong.");
          });
      },
    });
  });

  $(document.body).on("click", ".fa-paper-plane", function (ev) {
    ev.preventDefault();
    var id = $(this).data("id");
    $.confirm({
      title: "Enviar a tráfico",
      escapeKey: "cerrar",
      boxWidth: "550px",
      useBootstrap: false,
      type: "blue",
      buttons: {
        confirmar: { btnClass: "btn-blue", action: function () {} },
        cerrar: { action: function () {} },
      },
      content: function () {
        var self = this;
        return $.ajax({
          url: "/operaciones/get/enviar-carta",
          method: "get",
        })
          .done(function (res) {
            self.setContent(res.html);
          })
          .fail(function () {
            self.setContent("Something went wrong.");
          });
      },
    });
  });

  $(document.body).on("click", ".fa-print", function (ev) {
    ev.preventDefault();
    var id = $(this).data("id");
    $.confirm({
      title: "Imprimir",
      escapeKey: "cerrar",
      boxWidth: "550px",
      useBootstrap: false,
      type: "green",
      buttons: {
        imprimir: { btnClass: "btn-green", action: function () {} },
        cerrar: { action: function () {} },
      },
      content: function () {
        var self = this;
        return $.ajax({
          url: "/operaciones/get/imprimir-carta",
          method: "get",
        })
          .done(function (res) {
            self.setContent(res.html);
          })
          .fail(function () {
            self.setContent("Something went wrong.");
          });
      },
    });
  });

  $(document.body).on("click", "#subirFacturas", function (ev) {
    ev.preventDefault();
    $.confirm({
      title: "Subir layout de facturas",
      escapeKey: "cerrar",
      boxWidth: "550px",
      useBootstrap: false,
      type: "green",
      buttons: {
        subir: {
          btnClass: "btn-green",
          action: function () {
            if ($("#layout_upload").valid()) {
              $("#layout_upload").ajaxSubmit({
                url: "/operaciones/post/subir-facturas",
                dataType: "json",
                type: "POST",
                success: function (res) {
                  if (res.success === true) {
                    $.toast({
                      text: "<strong>Guardado</strong>",
                      bgColor: "green",
                      stack: 3,
                      position: "bottom-right",
                    });
                    return true;
                  }
                },
              });
            }
            return false;
          },
        },
        cerrar: { action: function () {} },
      },
      content: function () {
        var self = this;
        return $.ajax({
          url: "/operaciones/get/subir-facturas",
          method: "get",
        })
          .done(function (res) {
            self.setContent(res.html);
          })
          .fail(function () {
            self.setContent("Something went wrong.");
          });
      },
    });
  });

  $(document.body).on("click", "#subirCatalogo", function (ev) {
    ev.preventDefault();
    $.confirm({
      title: "Subir layout de facturas",
      escapeKey: "cerrar",
      boxWidth: "550px",
      useBootstrap: false,
      type: "green",
      buttons: {
        subir: {
          btnClass: "btn-green",
          action: function () {
            if ($("#catalog_upload").valid()) {
              $("#catalog_upload").ajaxSubmit({
                url: "/operaciones/post/subir-catalogo",
                dataType: "json",
                type: "POST",
                success: function (res) {
                  if (res.success === true) {
                    $.toast({
                      text: "<strong>Guardado</strong>",
                      bgColor: "green",
                      stack: 3,
                      position: "bottom-right",
                    });
                    return true;
                  }
                },
              });
            }
            return false;
          },
        },
        cerrar: { action: function () {} },
      },
      content: function () {
        var self = this;
        return $.ajax({
          url: "/operaciones/get/subir-facturas",
          method: "get",
        })
          .done(function (res) {
            self.setContent(res.html);
          })
          .fail(function () {
            self.setContent("Something went wrong.");
          });
      },
    });
  });

  $.each(
    [
      "edit",
      "trash",
      "patente",
      "aduana",
      "pedimento",
      "referencia",
      "cliente",
      "fecha",
      "comentarios",
      "observaciones",
      "acta",
      "multa",
      "pagada",
      "responsable",
      "tipoError",
    ],
    function (index, value) {
      $(".datagrid-editable-input[name='" + value + "']").hide();
    }
  );
});
