$(document).ready(function () {
    
    $('#example').dataTable({
        "sDom": "<'traffic-cols'<'traffic-col-50'l><'traffic-col-50'f><'traffic-clear-5'>t<'traffic-clear-5'><'traffic-col-50'i><'traffic-col-50'p><'traffic-clear-5'>>",
        "sPaginationType": "bootstrap",
        "oLanguage": {
            "sLengthMenu": "_MENU_ registros por página"
        },
        "iDisplayLength": 25,
        "bStateSave": true,
        "aaSorting": [],
        "bSort": false
    });
    
    $(document.body).on("click", "#refresh", function (e) {
        e.preventDefault();
        $.blockUI({
            centerY: 0,
            css: {top: '10px', left: '', right: '10px'},
            message: '<h4 style="padding: 10px 10px"><img src="/images/loader.gif" style="margin-right:5px" /> Por favor espere...</h4>',
            baseZ: 2000
        });
        if (!$(this).hasClass("disabled")) {
            $.ajax({
                url: "/vucem/get/consultar-edocuments",
                type: "post",
                dataType: "json",
                success: function (res) {
                    if (res.success === true) {
                        setTimeout($.unblockUI, 1000);
                        location.reload();
                    } else {
                        setTimeout($.unblockUI, 1000);
                    }
                }
            });
        }
    });
    
    $(document.body).on("click", ".enviarAPedimento", function (e) {
        e.preventDefault();
        var id = $(this).data("id");
        $.ajax({
            url: "/vucem/post/enviar-a-pedimento",
            type: "post",
            dataType: "json",
            data: {id: id},
            success: function (res) {
                if (res.success === true) {
                    $(".enviarAPedimento[data-id=" + id + "]").hide();
                    $.alert({title: "Ok", content: res.message, boxWidth: "250px", useBootstrap: false, type: "green"});
                } else {
                    $.alert({title: "Error", content: res.message, boxWidth: "250px", useBootstrap: false, type: "red"});
                }
            }
        });
    });
    
    $(document.body).on("click", ".enviarAExpediente", function (e) {
        e.preventDefault();
        var id = $(this).data("id");
        $.ajax({
            cache: false,
            url: "/vucem/post/guardar-edocument",
            type: "post",
            dataType: "json",
            data: {solicitud: $(this).data("solicitud"), id: id},
            success: function (res) {
                if (res.success === true) {
                    $(".enviarAExpediente[data-id=" + id + "]").hide();
                    $(this).hide();
                } else if (res.success === false) {
                    alert(res.message);
                }
            }
        });
    });

    setTimeout(function () {
        $("#refresh").removeClass("disabled");
    }, 5 * 1000);

    $(document.body).on("click", "#hdd", function (e) {
        e.preventDefault();
        $.ajax({
            url: "/vucem/ajax/hdd-edocuments-background",
            type: "post",
            dataType: 'json',
            success: function () {
            }
        });
    });

    $(document.body).on("click", ".deleteedoc", function (e) {
        e.preventDefault();
        var resp = confirm("¿Esta seguro que desea borrar la solicitud?");
        if (resp === true) {
            $.ajax({
                url: "/vucem/data/borrar-solicitud-edoc",
                type: "post",
                data: {uuid: $(this).attr("data")},
                success: function (data) {
                    var obj = jQuery.parseJSON(data);
                    if (obj.success === true) {
                        location.reload();
                    }
                }
            });
        }
    });
    
    $(".filename").qtip({
        content: {
            attr: "data-tooltip"
        }
    });
    
});

function downloadAllEdocuments() {
    var checkedValues = $("input[name=files]:checkbox:checked").map(function () {
        return this.value;
    }).get();
    if (typeof checkedValues[0] !== "undefined" && checkedValues[0] !== null) {
        $.ajax({
            url: "/vucem/data/descarga-edocuments",
            type: "post",
            data: {files: checkedValues},
            success: function (data) {
                var obj = jQuery.parseJSON(data);
                if (obj.success === true) {
                    window.location.href = "/vucem/data/descargar-edocuments?filename=" + encodeURIComponent(obj.filename);
                }
            }
        });
    } else {
        alert("No a seleccionado nada.");
    }
}

function checkVucem(id) {
    $.ajax({
        url: "/vucem/ajax/consultar-solicitud-edocument",
        type: "post",
        cache: false,
        dataType: "json",
        data: {id: id},
        success: function (res) {
            if (res.success === false) {
                alert(res.message);
            }
        }
    });
}

function borrarEdoc(id) {    
    var resp = confirm("¿Esta seguro que desea borrar el Edocument?");
    if (resp === true) {
        $.ajax({
            url: "/vucem/ajax/borrar-edocument",
            type: "post",
            cache: false,
            dataType: "json",
            data: {id: id},
            success: function (res) {
                if (res.success === true) {
                    $("#row_" + id).hide();
                }
            }
        });
    }
}

function selectAllEdocuments() {
    $("input[name=files]").each(function () {
        this.checked = true;
    });
}
function unselectAllEdocuments() {
    $("input[name=files]").each(function () {
        this.checked = false;
    });
}
    