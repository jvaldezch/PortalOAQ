/**
 * programmed by Jvaldez at gmail
 * 2015.may.14
 */


function mensajeAlerta(mensaje) {
    $.alert({title: "Alerta", type: "red", typeAnimated: true, useBootstrap: false, boxWidth: "250px",
        content: mensaje
    });
}

function deleteRequest(id) {
    var r = confirm("¿Está seguro que desea eleminar la solicitud");
    if (r === true) {
        $.ajax({
            url: "/trafico/data/delete-request",
            cache: false,
            type: "post",
            dataType: "json",
            data: {id: id},
            success: function (res) {
                if (res.success === true) {
                    document.location.href = "/trafico/index/ultimas-solicitudes";
                }
            }
        });
    }
}

function savePdf(id) {
    $.ajax({
        url: "/trafico/get/guardar-solicitud", cache: false, dataType: "json", type: "GET",
        data: {id: id},
        success: function (res) {
            if (res.success === true) {
                document.location.href = "/archivo/get/descargar-archivo?id=" + res.id;
            }
        }
    });
}

$(document).ready(function () {

    var params = ["buscar", "size", "page", "aduana"];

    params.forEach(function (entry) {
        var param = getUrlParameter(entry);
        if (param) {
            $(".traffic-pagination a").each(function () {
                var href = $(this).attr("href");
                if (href && href.indexOf(entry + "=") === -1) {
                    $(this).attr("href", href + "&" + entry + "=" + param);
                }
            });
        }
    });
    
    var filters = ["depositado", "pendiente", "warning", "complementos"];
    
    filters.forEach(function (entry) {
        var param = getUrlParameter(entry);
        if (param) {
            $("#" + entry).prop("checked", true);
        }
        $("#" + entry).on("click", function () {
            if ($(this).is(":checked")) {
                addUrlParameter(entry, "true");
            } else {
                removeUrlParameter(entry, document.URL);
            }
        });
    });

    $("#applyFilter").on("click", function () {
        if ($("#aduanas").val() !== "") {
            addUrlParameter("aduana", $("#aduanas").val());
        } else {
            removeUrlParameter("aduana", document.URL);
        }
    });

    $("#pagination-size").on("change", function () {
        var href = window.location.href;
        var loc = "";
        if (href.match(/[&\?]/) === null) {
            loc = href + "?size=" + $(this).val();
        } else {
            if (href.match(/size=[0-9]/) !== null) {
                loc = href.replace(/size=[0-9]{2}/, "size=" + $(this).val());
            } else {
                loc = href + "&size=" + $(this).val();
            }
        }
        window.location.href = loc;
    });

    $("#form-layouts").validate({
        errorPlacement: function (error, element) {
            $(element)
                    .closest("form")
                    .find("label[for='" + element.attr("id") + "']")
                    .append(error);
        },
        rules: {
            layout: "required"
        },
        messages: {
            layout: "SELECCIONAR LAYOUT."
        }
    });

    $("#fecha-inicio, #fecha-fin").datepicker({
        calendarWeeks: true,
        autoclose: true,
        language: "es",
        format: "yyyy-mm-dd"
    });

    $("#buscar").on("input", function (evt) {
        var input = $(this);
        var start = input[0].selectionStart;
        $(this).val(function (_, val) {
            return val.toUpperCase();
        });
        input[0].selectionStart = input[0].selectionEnd = start;
    });
    
    $("#reporteModal").jqm({
        ajax: "@href",
        modal: true,
        trigger: "#reporte"
    });
    
    $(document.body).on("click", "#closeModal", function (ev) {
        ev.preventDefault();
        $("#reporteModal").jqmHide();
    });

    $(document.body).on('click', '.todas-solicitudes', function (ev) {
        var checkboxes = $("input[class=solicitud]");
        if ($(this).is(':checked')) {
            checkboxes.prop('checked', true);
        } else {
            checkboxes.prop('checked', false);
        }
    });
    
    var ids = [];
    
    $(document.body).on('click', '.descargar', function (ev) {
        var boxes = $('iinput[class=solicitud]');
        if ((boxes).size() === 0) {
            mensajeAlerta('Usted no ha seleccionado nada.');
        }
        if ((boxes).size() > 0) {
            $(boxes).each(function () {
                ids.push($(this).data('id'));
            });
            $.ajax({url: '/trafico/get/trafico-tmp-crear', cache: false, dataType: 'json', type: 'GET',
                data: {ids: ids},
                success: function (res) {
                    if (res.success === true) {
                    }
                }
            });
        }
    });    
    
});