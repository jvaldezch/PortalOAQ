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
            $.ajax({url: "/vucem/ajax/solicitudes-edocuments", dataType: 'json', type: "POST",
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
    
    $(document.body).on("click", "#gearman-reload", function (e) {
        e.preventDefault();
    });
    
    $(document.body).on("click", "send-to-disk", function (e) {
        e.preventDefault();
        $.ajax({
            url: $(this).attr('href'),
            type: "get",
            success: function () {
            }
        });
    });
    
    $(document.body).on("click", ".save", function (e) {
        e.preventDefault();
        var id = $(this).data("id");
        $("#save_" + id).hide();
        $.ajax({url: "/vucem/post/guardar-edocument", cache: false, dataType: "json", type: "POST",
            data: {solicitud: $(this).data("solicitud"), id: $(this).data("id")},
            success: function (res) {
                if (res.success === true) {
                    $("#save_" + res.id).hide();
                } else if (res.success === false) {
                    alert(res.message);
                }
            }
        });
    });

    setTimeout(function () {
        $("#refresh").removeClass("disabled");
    }, 5 * 1000);

    $(document.body).on("click", "#refresh-hdd", function (e) {
        e.preventDefault();
        $.ajax({url: "/vucem/ajax/hdd-edocuments-background", dataType: 'json', type: "POST",
            success: function () {
            }
        });
    });

    $(document.body).on("click", ".deleteedoc", function (e) {
        e.preventDefault();
        var resp = confirm("¿Esta seguro que desea borrar la solicitud?");
        if (resp === true) {
            $.ajax({url: "/vucem/data/borrar-solicitud-edoc", type: "POST",
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
    
    $(document.body).on("click", ".openFile", function (ev) {
        ev.preventDefault();        
        var id = $(this).data("id");
        window.open("/vucem/get/ver-edocument?id=" + id, "viewFile", "toolbar=0,location=0,menubar=0,height=550,width=880,scrollbars=yes");
    });
    
});

function enviaraPedimento(uuid, solicitud) {
    $.ajax({url: "/vucem/data/enviar-a-pedimento", type: "POST",
        data: {uuid: uuid, solicitud: solicitud},
        success: function (data) {
            var obj = jQuery.parseJSON(data);
            alert(obj.message);
        }
    });
}

function downloadAllEdocuments() {
    var checkedValues = $("input[name=files]:checkbox:checked").map(function () {
        return this.value;
    }).get();
    if (typeof checkedValues[0] !== "undefined" && checkedValues[0] !== null) {
        $.ajax({url: "/vucem/data/descarga-edocuments", type: "POST",
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
    $.ajax({url: "/vucem/ajax/consultar-solicitud-edocument", cache: false, dataType: "json", type: "POST",
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
        $.ajax({url: "/vucem/ajax/borrar-edocument", cache: false, dataType: "json", type: "POST",
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
    