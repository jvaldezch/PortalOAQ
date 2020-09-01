/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

var arr = [];

Number.prototype.formatMoney = function (c, d, t) {
    var n = this,
            c = isNaN(c = Math.abs(c)) ? 2 : c,
            d = d == undefined ? "." : d,
            t = t == undefined ? "," : t,
            s = n < 0 ? "-" : "",
            i = String(parseInt(n = Math.abs(Number(n) || 0).toFixed(c))),
            j = (j = i.length) > 3 ? j % 3 : 0;
    return "$ " + s + (j ? i.substr(0, j) + t : "") + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + t) + (c ? d + Math.abs(n - i).toFixed(c).slice(2) : "");
};

$(document).ready(function () {

    var params = ["buscar", "size", "page", "aduana"];

    $(document.body).on("click", "#selectAll", function () {
        var checkboxes = $("input[class=singleRequest]");
        if ($(this).is(':checked')) {
            checkboxes.prop('checked', true);
        } else {
            checkboxes.prop('checked', false);
        }
    });

    $(document.body).on("click", "#approve", function () {
        var ids = [];
        var boxes = $("input[class=singleRequest]:checked");
        if ((boxes).size() === 0) {
            if (confirm("No ha seleccionado un archivo ¿desea incluir todos?")) {
                crearArchivoZip(0);
            }
        }
        if ((boxes).size() > 0) {
        }
    });

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

    $("#modal").jqm({
        modal: true
    });

    $(document.body).on("click", ".singleRequest", function () {
        var uniqueNames = [];
        var uri = "";
        arr.push($(this).data("id"));
        $.each(arr, function (i, el) {
            if ($.inArray(el, uniqueNames) === -1)
                uniqueNames.push(el);
        });
        $.each(uniqueNames, function (i, el) {
            uri = uri + el + ",";
        });
    });

    var ids = [];
    
    $(document.body).on("click", "#multiple", function () {
        arr = [];
        $("#infoIds > tbody").html("");
        var boxes = $(".singleRequest:checked");
        if ((boxes).size() === 0) {
            $.alert({title: "Advertencia", type: "orange", content: "No ha seleccionado ninguna referencia.", boxWidth: "350px", useBootstrap: false});
            return false;
        }
        ids = [];
        $(boxes).each(function () {
            ids.push($(this).data("id"));
        });
        $.confirm({title: "Múltiples depósitos", escapeKey: "cerrar", boxWidth: "650px", useBootstrap: false,
            buttons: {
                guardar: {
                    btnClass: "btn-green",
                    action: function () {
                        if ($("#form-files").valid()) {
                            $("#form-files").ajaxSubmit({url: "/administracion/ajax/multiples-depositos", dataType: "json", type: "POST",
                                success: function (res) {
                                    if (res.success === true) {
                                        window.location.href = "/administracion/index/solicitudes-anticipo";
                                    }
                                }
                            });
                            return true;
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
                return $.ajax({url: "/administracion/get/solicitudes-multiples", dataType: "json", method: "POST",
                    data: {ids: ids}
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
        window.location.replace(loc);
    });

    $("#applyFilter").on("click", function () {
        if ($("#aduanas").val() !== "") {
            addUrlParameter("aduana", $("#aduanas").val());
        } else {
            removeUrlParameter("aduana", document.URL);
        }
    });

    $(document.body).on("input", "#buscar", function () {
        var input = $(this);
        var start = input[0].selectionStart;
        $(this).val(function (_, val) {
            return val.toUpperCase();
        });
        input[0].selectionStart = input[0].selectionEnd = start;
    });

});