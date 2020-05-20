window.procesarFacturas = function () {
    alert("Funcionalidad pendiente.");
};

window.procesarPartidas = function () {
    alert("Funcionalidad pendiente.");
};

window.verPartidas = function () {
  $.confirm({
    title: "Partidas de facturas",
    escapeKey: "cerrar",
    boxWidth: "80%",
    useBootstrap: false,
    type: "blue",
    closeIcon: true,
    buttons: {
      cerrar: { action: function () {} },
    },
    content: function () {
      let self = this;
      return $.ajax({
        url: `/pedimento/get/cargar-partidas?idTrafico=${$("#idTrafico").val()}&idPedimento=${$("#idPedimento").val()}`, 
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
};

$(document).ready(function () {
  $(document.body).on("click", "#agrupar-partidas", function (ev) {
    ev.preventDefault();
    $.ajax({
      url: "/pedimento/get/agrupar-partidas",
      type: "POST",
      data: {
        idPedimento: $("#idPedimento").val(),
        idTrafico: $("#idTrafico").val(),
      },
      beforeSend: function () {
        $("#pedimento-partidas").LoadingOverlay("show", {
          color: "rgba(255, 255, 255, 0.9)",
        });
      },
      success: function (res) {
        $("#pedimento-partidas").LoadingOverlay("hide");
        if (res.success === true) {
        }
      },
    });
  });

  $(document.body).on("click", "#send-aduanet", function (ev) {
    ev.preventDefault();
    $.ajax({
      url: "/principal/post/enviar-pedimento",
      type: "POST",
      data: { idTrafico: $("#idTrafico").val() },
      success: function (res) {
        if (res.success === true) {
        }
      },
    });
  });

  $(document.body).on("click", "#send-aduanet", function (ev) {
    ev.preventDefault();
    $.ajax({
      url: "/principal/post/csv-aduanet",
      type: "POST",
      data: { idTrafico: $("#idTrafico").val() },
      success: function (res) {
        if (res.success === true) {
        }
      },
    });
  });

  $(document.body).on("change", "#destinoOrigen", function () {
    $.ajax({
      url: "/pedimento/post/actualizar",
      type: "POST",
      data: {
        idPedimento: $("#idPedimento").val(),
        name: $(this).attr("name"),
        value: $(this).val(),
      },
      success: function (res) {
        if (res.success === true) {
        }
      },
    });
  });

  $(document.body).on(
    "change",
    "#tipoCambio, #destinoOrigen, #pesoBruto, #aduanaDespacho, #regimen, #transEntrada, #transArribo, #transSalida",
    function () {
      $.ajax({
        url: "/pedimento/post/actualizar",
        type: "POST",
        data: {
          idPedimento: $("#idPedimento").val(),
          name: $(this).attr("name"),
          value: $(this).val(),
        },
        success: function (res) {
          if (res.success === true) {
          }
        },
      });
    }
  );
});
