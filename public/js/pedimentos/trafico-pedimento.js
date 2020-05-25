window.procesarFacturas = function () {
  alert("Funcionalidad pendiente.");
};

window.procesarPartidas = function () {
  alert("Funcionalidad pendiente.");
};

window.recargarPartidas = function () {
  alert("Funcionalidad pendiente.");
};

window.configuracionPartidas = function () {
  alert("Funcionalidad pendiente.");
};

let fields = {
  fraccion: "Fracción",
  dfraccion1: "Descripción",
  igi: "IGI",
  unidad: "Unidad",
  ivai: "IVA I",
  ivaiff: "IVA FF",
  restricci1: "REST. 1",
  restricci2: "REST. 2",
  restricci3: "REST. 3",
  restricci4: "REST. 4",
  restricci5: "REST. 5",
  restricci6: "REST. 6",
  restricci7: "REST. 7",
  restricci8: "REST. 8",
  restricci9: "REST. 9",
  cuotas1: "CUOTAS 1",
  cuotas2: "CUOTAS 2",
  cuotas3: "CUOTAS 3",
  cuotas4: "CUOTAS 4",
  cuotas5: "CUOTAS 5",
  cuotas6: "CUOTAS 6",
  cuotas7: "CUOTAS 7",
  cuotas8: "CUOTAS 8",
  cuotas9: "CUOTAS 9",
  textimp1: "TEXT IMP 1",
  textimp2: "TEXT IMP 2",
  textimp3: "TEXT IMP 3",
  textimp4: "TEXT IMP 4",
  textimp5: "TEXT IMP 5",
  textimp6: "TEXT IMP 6",
  textimp7: "TEXT IMP 7",
  textimp8: "TEXT IMP 8",
  textimp9: "TEXT IMP 9",
  tlceua: "tlceua",
  tlccan: "tlccan",
  tlccol: "tlccol",
  tlcven: "tlcven",
  tlcbol: "tlcbol",
  tlccri: "tlccri",
  tlccos: "tlccos",
  tlcnic: "tlcnic",
  tlcisr: "tlcisr",
  tlccce: "tlccce",
  tlcgua: "tlcgua",
  tlcsal: "tlcsal",
  tlchon: "tlchon",
  tlcsui: "tlcsui",
  tlcnor: "tlcnor",
  tlcisl: "tlcisl",
  tlclie: "tlclie",
  tlcchi: "tlcchi",
  tlcuru: "tlcuru",
  tlcjap: "tlcjap",
  ppsi: "ppsi",
  ppsii: "ppsii",
  ppsiii: "ppsiii",
  ppsiv: "ppsiv",
  ppsv: "ppsv",
  ppsvi: "ppsvi",
  ppsvii: "ppsvii",
  ppsviii: "ppsviii",
  ppsix: "ppsix",
  ppsx: "ppsx",
  ppsxi: "ppsxi",
  ppsxii: "ppsxii",
  ppsxiii: "ppsxiii",
  ppsxiv: "ppsxiv",
  ppsxv: "ppsxv",
  ppsxvi: "ppsxvi",
  ppsxvii: "ppsxvii",
  ppsxviii: "ppsxviii",
  ppsxix: "ppsxix",
  ppsxx: "ppsxx",
  ppsxxi: "ppsxxi",
  ppsxxii: "ppsxxii",
  ppsxxiii: "ppsxxiii",
  observai: "observai",
  restricce3: "restricce3",
  restricce4: "restricce4",
  restricce5: "restricce5",
  restricce6: "restricce6",
  restricce7: "restricce7",
  restricce8: "restricce8",
  restricce9: "restricce9",
  dcapitulo1: "dcapitulo1",
  dcapitulo2: "dcapitulo2",
  textpai1: "textpai1",
  textpai2: "textpai2",
  textpai3: "textpai3",
  textpai4: "textpai4",
  textpai5: "textpai5",
  textpai6: "textpai6",
  textpai7: "textpai7",
  textpai8: "textpai8",
  textpai9: "textpai9",
  ovservai: "ovservai",
  seccion: "seccion",
  dseccion: "dseccion",
  capitulo: "capitulo",
  dcapitulo: "dcapitulo",
  partida: "partida",
  dpartida1: "dpartida1",
  dpartida2: "dpartida2",
  subpart: "subpart",
  dsubpart1: "dsubpart1",
  dsubpart2: "dsubpart2",
  dfraccion2: "dfraccion2",
  ige: "ige",
  ivae: "ivae",
  ivaeff: "ivaeff",
  restricce1: "restricce1",
  restricce2: "restricce2",
  anexos1: "anexos1",
  anexos2: "anexos2",
  anexo18: "anexo18",
  cuposi: "cuposi",
  cupose: "cupose",
  observa: "observa",
  observai1: "observai1",
  observai2: "observai2",
  observai3: "observai3",
  observai4: "observai4",
  observae: "observae",
  ppsii_a: "ppsii_a",
  ppsii_b: "ppsii_b",
  ppsxv_a: "ppsxv_a",
  ppsxv_b: "ppsxv_b",
  ppsxxiii_a: "ppsxxiii_a",
  fecha_act: "fecha_act",
  ppsiib: "ppsiib",
  ppsxvb: "ppsxvb",
  ppsxxiiib: "ppsxxiiib",
  restrici10: "restrici10",
  restrici11: "restrici11",
  restrici12: "restrici12",
  restrici13: "restrici13",
};

window.consultaFraccion = function (fraccion) {
  $.ajax({
    url: "http://199.167.184.210:5004/api/tarifa",
    type: "GET",
    data: {
      fraccion: fraccion,
    },
    success: function (res) {
      if (res.success === true) {
        let r = res.response;

        let divR = document.createElement("div");
        divR.setAttribute("style", "max-height: 500px; overflow-y: auto");

        let table = document.createElement("table");
        table.setAttribute("class", "traffic-table traffic-table-left");

        for (const [key, value] of Object.entries(fields)) {
          var tr = document.createElement("tr");
          var th1 = document.createElement("th");
          var td2 = document.createElement("td");

          th1.innerHTML = value;
          td2.innerHTML = r[key];

          tr.appendChild(th1);
          tr.appendChild(td2);

          table.appendChild(tr);
        }
        divR.append(table);

        $.confirm({
          title: `Tarifa fracción ${fraccion}`,
          escapeKey: "cerrar",
          boxWidth: "80%",
          useBootstrap: false,
          type: "blue",
          closeIcon: true,
          buttons: {
            cerrar: { action: function () {} },
          },
          content: divR,
        });
      }
    },
  });
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
        url: `/pedimento/get/cargar-partidas?idTrafico=${$(
          "#idTrafico"
        ).val()}&idPedimento=${$("#idPedimento").val()}`,
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
