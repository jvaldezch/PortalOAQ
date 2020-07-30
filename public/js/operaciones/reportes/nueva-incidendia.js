$("#form_incidence").validate({
  errorPlacement: function (error, element) {
    $(element)
      .closest("form")
      .find("#" + element.attr("id"))
      .after(error);
  },
  errorElement: "span",
  errorClass: "traffic-error",
  rules: {
    idAduana: "required",
    idCliente: "required",
    idTipoError: "required",
    pedimento: "required",
    referencia: "required",
    fecha: "required",
  },
  messages: {
    idAduana: "Campo necesario",
    idCliente: "Campo necesario",
    idTipoError: "Campo necesario",
    pedimento: "Campo necesario",
    referencia: "Campo necesario",
    fecha: "Campo necesario",
  },
});

$("#fecha").datepicker({
  calendarWeeks: true,
  autoclose: true,
  language: "es",
  format: "yyyy-mm-dd",
});

$(document).on(
  "input",
  "#responsable, #comentarios, #observaciones, #referencia",
  function () {
    var input = $(this);
    var start = input[0].selectionStart;
    $(this).val(function (_, val) {
      return val.toUpperCase();
    });
    input[0].selectionStart = input[0].selectionEnd = start;
  }
);
