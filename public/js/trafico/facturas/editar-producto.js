
$("#frmProduct").validate({
    errorPlacement: function (error, element) {
        $(element)
            .closest("form")
            .find("#" + element.attr("id"))
            .after(error);
    },
    errorElement: "span",
    errorClass: "errorlabel",
    rules: {
        numParte: { required: true },
        fraccion: { required: true },
        descripcion: { required: true }
    },
    messages: {
        numParte: "SE REQUIERE",
        fraccion: "SE REQUIERE",
        descripcion: "SE REQUIERE"
    }
});

$(document.body).on("change, focusout", "#precioUnitario, #cantidadFactura", function (evt) {
    var precioUnitario = $("#precioUnitario").val();
    var cantidadFactura = $("#cantidadFactura").val();
    if (precioUnitario !== '' && cantidadFactura !== '') {
        var valorComercial = parseFloat(precioUnitario) * parseFloat(cantidadFactura);
        $("#valorComercial").val(valorComercial);
    }
});

$(document.body).on("change", "#fraccion", function (evt) {
    let v = $(this).val();
    if (v.length == 8) {
        $.ajax({url: '/trafico/facturas/nico',
            data: {fraccion: $(this).val()},
            success: function (res) {
                if (res.success) {
                    let r = res.results;
                    $("#fraccion_2020").val(r.tigie_2020);
                    $("#nico").val(r.nico);
                }
            }
        });
    }
});

$(document.body).on("input", "#marca, #modelo, #subModelo, #numSerie, #descripcion, #fraccion, #numParte, #observaciones", function (evt) {
    var input = $(this);
    var start = input[0].selectionStart;
    $(this).val(function (_, val) {
        return val.toUpperCase();
    });
    input[0].selectionStart = input[0].selectionEnd = start;
});

$.when(paises('#paisOrigen'), paises('#paisVendedor')).done(function () {
    $('#paisOrigen').val('<?= $this->paisOrigen ?>');
    $('#paisVendedor').val('<?= $this->paisVendedor ?>');
});