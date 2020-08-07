window.descargaXmlpedimento = function () {
  $.ajax({
    url: "/trafico/pedimentos/descarga-xml",
    cache: false,
    dataType: "json",
    data: {
      idTrafico: $("#idTrafico").val(),
    },
    type: "GET",
    success: function (res) {
      if (res.success === true) {
        if (res.message) {
          $.alert({
            title: "Advertencia",
            type: "orange",
            content: res.message,
            boxWidth: "350px",
            useBootstrap: false,
          });
        }
        loadFiles();
      } else {
        $.alert({
          title: "Error",
          type: "red",
          content: res.message,
          boxWidth: "350px",
          useBootstrap: false,
        });
      }
    },
  });
};

window.descargaXmlCove = function (id, cove) {
  $.ajax({
    url: "/trafico/pedimentos/descarga-xml-cove",
    cache: false,
    dataType: "json",
    data: {
      idTrafico: $("#idTrafico").val(),
      id: id,
      cove: cove,
    },
    type: "GET",
    success: function (res) {
      if (res.success === true) {
        if (res.message) {
          $.alert({
            title: "Advertencia",
            type: "orange",
            content: res.message,
            boxWidth: "350px",
            useBootstrap: false,
          });
        }
        loadFiles();
      } else {
        $.alert({
          title: "Error",
          type: "red",
          content: res.message,
          boxWidth: "350px",
          useBootstrap: false,
        });
      }
    },
  });
};

window.deleteEdocument = function (id) {
  $.ajax({
    url: "/trafico/pedimentos/remover-xml-edocument",
    cache: false,
    dataType: "json",
    data: {
      idTrafico: $("#idTrafico").val(),
      id: id,
    },
    type: "GET",
    success: function (res) {
      if (res.success === true) {
        if (res.message) {
          $.alert({
            title: "Advertencia",
            type: "orange",
            content: res.message,
            boxWidth: "350px",
            useBootstrap: false,
          });
        }
        $(`#eds-consulta-${id}`).html("");
      } else {
        $.alert({
          title: "Error",
          type: "red",
          content: res.message,
          boxWidth: "350px",
          useBootstrap: false,
        });
      }
    },
  });
};

window.descargaManualEdocument = function () {
  let edocument = $("#numEdocument").val();
  $.ajax({
    url: "/trafico/pedimentos/descarga-xml-edocument",
    cache: false,
    dataType: "json",
    data: {
      idTrafico: $("#idTrafico").val(),
      edocument: edocument,
    },
    type: "GET",
    success: function (res) {
      if (res.success === true) {
        if (res.message) {
          $.alert({
            title: "Advertencia",
            type: "orange",
            content: res.message,
            boxWidth: "350px",
            useBootstrap: false,
          });
        }
        $("#numEdocument").val("");
        if (res.results.length > 0) {
          $("#eds-consulta").html("");
          console.log(res.results);
          for (var prop in res.results) {
            let o = res.results[prop];

            var tr = document.createElement("tr");

            var c1 = document.createElement("td");
            var n1 = document.createTextNode(o.edocument);
            c1.appendChild(n1);
            tr.appendChild(c1);

            var c2 = document.createElement("td");
            var n2 = document.createTextNode(o.mensaje ? o.mensaje : '');
            c2.appendChild(n2);
            tr.appendChild(c2);

            var c3 = document.createElement("td");
            var n3 = document.createTextNode(o.creado);
            c3.appendChild(n3);
            tr.appendChild(c3);

            var c4 = document.createElement("td");
            var n4 = document.createTextNode(o.actualizado ? o.actualizado : '');
            c4.appendChild(n4);
            tr.appendChild(c4);

            var c5 = document.createElement("td");
            var i = document.createElement("i");
            i.setAttribute("class", "fas fa-trash-alt");
            i.setAttribute("onclick", `deleteEdocument(${o.id});`);
            i.setAttribute("style", "cursor: pointer");
            c5.appendChild(i);
            tr.appendChild(c5);

            $("#eds-consulta").append(tr);
          }
        }
      } else {
        $.alert({
          title: "Error",
          type: "red",
          content: res.message,
          boxWidth: "350px",
          useBootstrap: false,
        });
      }
    },
  });
};

$(document).on("input", "#numEdocument", function () {
  let input = $(this);
  let start = input[0].selectionStart;
  $(this).val(function (_, val) {
    return val.toUpperCase();
  });
  input[0].selectionStart = input[0].selectionEnd = start;
});
