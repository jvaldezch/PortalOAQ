/**
 * programmed by Jvaldez at gmail
 * 2015.may.14
 */

var loadMsg = '<span style="font-family: sans-serif; font-size: 12px">Por favor espere... <div class="traffic-icon traffic-loader"></div></span>';

function cargarDatos(id, url) {
    $(id).show();
    $(id).html(loadMsg);
    $.ajax({
        url: url,
        type: "post",
        dataType: "json",
        data: {id: $("#idUsuario").val()},
        timeout: 3000,
        success: function (res) {
            if (res.success === true) {
                $(id).html(res.html);
                if (res.todas !== undefined) {
                    if (res.todas === true) {
                        $("#formRepositorio #allCustoms").prop("checked", true);
                    }
                }
            }
        }
    });
}

function currentActive(current) {
    if (Cookies.get('active') === '#vucem') {
        cargarDatos("#usuarioSellos", "/usuarios/ajax/obtener-sellos");
    }
    if (Cookies.get('active') === '#traffic') {
        cargarDatos("#usuarioTraficos", "/usuarios/ajax/obtener-aduanas-trafico");
    }
    if (Cookies.get('active') === '#validator') {
        cargarDatos("#usuarioValidador", "/usuarios/ajax/obtener-validador-asignado");
    }
    if (Cookies.get('active') === '#repository') {
        cargarDatos("#usuarioRepositorio", "/usuarios/ajax/obtener-repositorios");
    }
    if (Cookies.get('active') === '#inhouseTab') {
        cargarDatos("#inhouseRfc", "/usuarios/post/obtener-clientes");
    }
    if (Cookies.get('active') === '#warehouse') {
        cargarDatos("#usuarioBodegas", "/usuarios/post/obtener-bodegas");
    }
}

$(document).ready(function () {
    
    var arr = [];
    $("#traffic-tabs li a").each(function() {        
        arr.push($(this).attr("href"));
    });
    
    $("#traffic-tabs li a").on('click', function () {
        var href = $(this).attr('href');
        Cookies.set('active', href);
        currentActive(Cookies.get('active'));
    });

    if (Cookies.get('active') !== undefined && jQuery.inArray(Cookies.get("active"), arr) !== -1) {
        $("a[href='" + Cookies.get('active') + "']").tab('show');
        currentActive(Cookies.get('active'));
    } else {
        $("a[href='#information']").tab('show');
        Cookies.set('active', "#information");
        currentActive(Cookies.get('active'));
    }

});