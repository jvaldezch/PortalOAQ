
/*function crearArchivoZip(ids) {
    $.ajax({
        url: "/archivo/index/crear-zip",
        type: "post",
        data: {ids: ids, pdf: $("#includepdf").is(":checked") ? 1 : 0},
        dataType: "json",
        success: function (res) {
            window.location = "/archivo/index/download-created-zip?filename=" + res;
        }
    });
}*/

/*function sendCofidi(folio, email) {
    $.ajax({
        url: "/automatizacion/email/enviar-cofidi?factura=" + folio + "&email=" + email,
        type: "GET",
        dataType: "json",
        success: function (res) {
            if (res.success === true) {
                alert("Se ha enviado un email a su correo.");
            } else {
                alert(res.error);
            }
        }
    });
}*/

Date.prototype.yyyymmdd = function () {
    var mm = this.getMonth() + 1; // getMonth() is zero-based
    var dd = this.getDate();

    return [this.getFullYear(),
        (mm > 9 ? '' : '0') + mm,
        (dd > 9 ? '' : '0') + dd
    ].join('-');
};

function fecha(value) {
    var date = new Date(value);
    return date.yyyymmdd();
}

Array.prototype.remove = function() {
    var what, a = arguments, L = a.length, ax;
    while (L && this.length) {
        what = a[--L];
        while ((ax = this.indexOf(what)) !== -1) {
            this.splice(ax, 1);
        }
    }
    return this;
};

var dg;

var ids = [];
var fechaIni;
var fechaFin;
var rfc;

window.changeDownloadLink = function () {
    fechaIni = $("#fechaIni").val();
    fechaFin = $("#fechaFin").val();
    rfc = $("#rfc").val();
    $("#downloadZip").attr("href", "/archivo/get/descargar-cuentas-de-gastos?fechaIni=" + fechaIni + "&fechaFin=" + fechaFin + "&rfcCliente=" + rfc + "&ids=" + ids.join(","));
};

$(document).ready(function () {
    
    dg = $("#dg").edatagrid();
    
    dg.edatagrid({
        pagination: true,
        singleSelect: false,
        striped: true,
        rownumbers: true,
        fitColumns: false,
        pageSize: 20,
        idField: "id",
        method: 'GET',
        url: "/archivo/get/reporte-cuentas-de-gastos",
        queryParams: {
            fechaIni: $("#fechaIni").val(),
            fechaFin: $("#fechaFin").val(),
            rfcCliente: $("#rfc").val()
	},
        onClickRow: function (index, row) {},
        onLoadSuccess: function () {
            changeDownloadLink();
        },
        onCancelEdit: function (index, row) {
            row.editing = false;
            $(this).datagrid("refreshRow", index);
        },
        onAdd: function (index, row) {},
        remoteFilter: true,
        frozenColumns: [
            [
                {field: 'id', checkbox: true, hidden: false},                
                {field: "aduana", width: 90, title: "Aduana", 
                    formatter: function(val, row){
                        return row.aduana + "-"  + row.patente;
                    }},
                {field: "referencia", width: 90, title: "Referencia"},
                {field: "folio", width: 70, title: "Folio"},
                {field: "fecha", width: 80, title: "Fecha",
                formatter: function(val, row){
                        return fecha(row.fecha);
                    }}
            ]
        ],
        columns: [
            [
                {field: "emisor_rfc", width: 160, title: "RFC Emisor"},
                {field: "receptor_nombre", width: 250, title: "Cliente"},
                {field: "nom_archivo", width: 200, title: "Nom. archivo",
                formatter: function(val, row) {
                        return '<a href="/archivo/get/descargar-archivo?id=' + row.id + '">' + row.nom_archivo + '</a>';
                    }}
            ]
        ]
    });
    
    $(document.body).on("input", "#rfc, #nombre", function (evt) {
        var input = $(this);
        var start = input[0].selectionStart;
        $(this).val(function (_, val) {
            return val.toUpperCase();
        });
        input[0].selectionStart = input[0].selectionEnd = start;
    });

    $(document.body).on("click", "#selectall", function () {
        $(".cuentas").attr("checked", this.checked);
    });

    /*$(document.body).on("click", "#downloadZip", function () {
        var ids = [];
        var boxes = $("input[name=cuentas]:checked");
        if ((boxes).size() === 0) {
            if (confirm("No ha seleccionado un archivo ¿desea incluir todos?")) {
                crearArchivoZip(0);
            }
        }
        if ((boxes).size() > 0) {
            $(boxes).each(function () {
                ids.push($(this).val());
            });
            crearArchivoZip(ids);
        }
    });*/
    
    $(document.body).on("click", "input[name=id]", function () {
        var value = $(this).val();
        if ($(this).prop('checked')) {
            ids.push($(this).val());
        } else {
            ids.remove($(this).val());            
        }
        changeDownloadLink();
    });
    
    $("input[name='fechaIni']").datepicker({
        format: "yyyy-mm-dd",
        language: "es",
        todayBtn: true,
        todayHighlight: true,
        startDate: "2013-01-08"
    });

    $("input[name='fechaFin']").datepicker({
        format: "yyyy-mm-dd",
        language: "es",
        todayBtn: true,
        todayHighlight: true
    });

    $("#nombre").typeahead({
        source: function (query, process) {
            return $.ajax({
                url: "/trafico/get/clientes",
                type: "get",
                data: {name: query},
                dataType: "json",
                success: function (res) {
                    return process(res);
                }
            });
        }
    }).change(function () {
        $("#rfc").val("");
    });
    
    $(document.body).on("change", "#nombre", function () {
        $.ajax({
            url: "/trafico/get/rfc-de-cliente",
            type: "get",
            data: {name: $("#nombre").val()},
            dataType: "json",
            success: function (res) {
                if (res) {
                    $("#rfc").val(res[0]["rfc"]);
                    $("#submit").removeAttr("disabled");
                }
            }
        });
    });

    /*$(document.body).on("click", "[data-toggle='modal']", function (e) {
        e.preventDefault();
        var url = $(this).attr("href");
        if (url.indexOf("#") === 0) {
            $(url).modal("open");
        } else {
            $.get(url, function (data) {
                $("<div class=\"modal hide fade\" style=\"width: 750px; margin-left: -375px\">" + data + "</div>")
                        .modal()
                        .on("hidden", function () {
                            $(this).remove();
                        });
            }).success(function () {
                $("input:text:visible:first").focus();
            });
        }
    });*/
    
});