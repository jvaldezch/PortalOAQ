$(document).ready(function () {
    
    jQuery.validator.addMethod("greaterThan",
            function (value, element, params) {
                if (!/Invalid|NaN/.test(new Date(value))) {
                    return new Date(value) > new Date($(params).val());
                }
                return isNaN(value) && isNaN($(params).val())
                        || (Number(value) > Number($(params).val()));
            }, 'Fecha menor a {0}');

    $("#report").validate({
        errorPlacement: function (error, element) {
            $(element)
                    .closest("form")
                    .find("#" + element.attr("id"))
                    .after(error);
        },
        errorElement: "span",
        errorClass: "inlineError",
        rules: {
            idAduana: {required: true},
            idCliente: {required: true},
            tipo: {required: true},
            fechaFin: { greaterThan: "#fechaIni" }
        },
        messages: {
            idAduana: {required: " [No se ha seleccionado aduana.]"},
            idCliente: {required: " [No se ha seleccionado cliente.]"},
            tipo: {required: " [No se ha seleccionado tipo de reporte.]"}
        }
    });
    
    $('#fechaIni, #fechaFin').datepicker({
        calendarWeeks: true,
        autoclose: true,
        language: 'es',
        format: 'yyyy-mm-dd'
    });
    $("#fechaFin").rules('add', { greaterThan: "#fechaIni" });
    
    $(document.body).on('click', '#submit', function (ev) {
        ev.preventDefault();
        if ($("#report").valid()) {
            if ($("input[name=tipo]:checked").val() !== "cargoquin" && $("input[name=tipo]:checked").val() !== "cnh") {
                var url = "idAduana=" + $("#idAduana").val() + "&idCliente=" + $("#idCliente").val() + "&tipo=" + $("input[name=tipo]:checked").val() + "&fechaIni=" + $("#fechaIni").val() + "&fechaFin=" + $("#fechaFin").val();
                window.open("/clientes/get/reporte?" + url, "_blank", "toolbar=0,location=0,menubar=0,height=550,width=800,scrollbars=yes");
            }
        }
    });
    
    $(document.body).on('click', '#upload', function (ev) {
        ev.preventDefault();
        $.confirm({title: "Plantilla Anexo 24", escapeKey: "cerrar", boxWidth: "380px", useBootstrap: false, type: "blue",
            buttons: {
                subir: {
                    btnClass: "btn-blue",
                    action: function () {
                        if ($('#uploadForm').valid()) {
                            $("#uploadForm").ajaxSubmit({url: "/operaciones/post/subir-plantilla", dataType: "json", type: "POST",
                                success: function (res) {
                                    if (res.success === true) {
                                    } else {
                                        $.alert({title: "Error", type: "red", content: res.message, boxWidth: "450px", useBootstrap: false});
                                    }
                                }
                            });
                        } else {
                            return false;
                        }
                    }
                },
                cerrar: {
                    btnClass: "btn-red",
                    action: function () {}
                }
            },
            content: function () {
                var self = this;
                return $.ajax({url: "/operaciones/get/subir-plantilla", dataType: "json", method: "GET"
                }).done(function (res) {
                    var html = "";
                    if (res.success === true) {
                        html = res.html;
                    }
                    self.setContent(html);
                }).fail(function () {
                    self.setContent("Something went wrong.");
                });
            }
        });
    });

    /*jQuery.validator.addMethod("greaterThan",
            function (value, element, params) {
                if (!/Invalid|NaN/.test(new Date(value))) {
                    return new Date(value) > new Date($(params).val());
                }
                return isNaN(value) && isNaN($(params).val())
                        || (Number(value) > Number($(params).val()));
            }, 'Fecha menor a {0}');

    $("#report").validate({
        errorPlacement: function (error, element) {
            $(element)
                    .closest("form")
                    .find("label[for='" + element.attr("id") + "']")
                    .append(error);
        },
        errorElement: "span",
        errorClass: "inlineError",
        rules: {
            aduana: {required: true},
            rfc: {required: true},
            tipo: {required: true},
            fechaFin: { greaterThan: "#fechaIni" }
        },
        messages: {
            aduana: {required: " [No se ha seleccionado aduana.]"},
            rfc: {required: " [No se ha seleccionado cliente.]"},
            tipo: {required: " [No se ha seleccionado tipo de reporte.]"}
        }
    });

    $('#fechaIni, #fechaFin').datepicker({
        calendarWeeks: true,
        autoclose: true,
        language: 'es',
        format: 'yyyy-mm-dd'
    });
    $("#fechaFin").rules('add', { greaterThan: "#fechaIni" });

    $("#submit").on('click', function (evt) {
        evt.preventDefault();
        if ($("#report").valid()) {
            var aa = String($("#aduana").val());
            var aduana = aa.split(',');
            if ($("input[name=tipo]:checked").val() !== "cargoquin" && $("input[name=tipo]:checked").val() !== "cnh") {
                var url = "patente=" + aduana[0] + "&aduana=" + aduana[1] + "&rfc=" + $("#rfc").val() + "&tipo=" + $("input[name=tipo]:checked").val() + "&fechaIni=" + $("#fechaIni").val() + "&fechaFin=" + $("#fechaFin").val();
                window.open("/clientes/data/reportes?" + url, "_blank", "toolbar=0,location=0,menubar=0,height=550,width=800,scrollbars=yes");
            } else if($("input[name=tipo]:checked").val() === "cnh") {
                var url = "patente=" + aduana[0] + "&aduana=" + aduana[1] + "&rfc=" + $("#rfc").val() + "&tipo=cnh&fechaIni=" + $("#fechaIni").val() + "&fechaFin=" + $("#fechaFin").val();
                window.open("/automatizacion/reportes/reportes?" + url, "_blank", "toolbar=0,location=0,menubar=0,height=550,width=800,scrollbars=yes");
            } else if($("input[name=tipo]:checked").val() === "cargoquin") {
                var url = "patente=" + aduana[0] + "&aduana=" + aduana[1] + "&rfc=" + $("#rfc").val() + "&tipo=cargoquin&fechaIni=" + $("#fechaIni").val() + "&fechaFin=" + $("#fechaFin").val();
                window.open("/automatizacion/reportes/reportes?" + url, "_blank", "toolbar=0,location=0,menubar=0,height=550,width=800,scrollbars=yes");
            }
            return true;
        }
        return false;
    });*/

});