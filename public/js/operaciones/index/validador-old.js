
function showResponse(res, statusText, xhr, $form) {
    var obj = jQuery.parseJSON(res);
    if (obj.success === true) {
        $('#files').html(obj.html);
        $('#activity').html(obj.log);
    } else {
        $('#files').html(obj.html);
        $('#activity').html("");
    }
}

function loadFile(id) {
    $.ajax({
        url: '/operaciones/data/obtener-archivo-log',
        cache: false,
        type: 'post',
        dataType: 'json',
        data: {id: id},
        success: function (res) {
            if (res.success === true) {
                $("#file-content").html(base64_decode(res.html));
                $("#descarga").html(res.descarga);
            }
        }
    });
}

function validarArchivo(id) {
    $.ajax({
        url: '/operaciones/data/validar-archivo-m3',
        cache: false,
        type: 'post',
        dataType: 'json',
        data: {id: id},
        beforeSend: function () {
            $("#imgm_" + id).hide();
        },
        success: function (res) {
            if (res.success === false) {
                alert(res.message);
            }
        }
    });
}

function pagarArchivo(id) {
    $.ajax({
        url: '/operaciones/data/pagar-archivo',
        cache: false,
        type: 'post',
        dataType: 'json',
        data: {id: id},
        beforeSend: function () {
            $("#imgp_" + id).hide();
        }
    });
}

function revisarArchivo(id) {
    $.ajax({
        url: '/operaciones/data/revisar-archivo',
        cache: false,
        type: 'post',
        dataType: 'json',
        data: {id: id},
        beforeSend: function () {
            $("#imgr_" + id).hide();
        }
    });
}

function saveToDisk(id) {
    $.ajax({
        url: '/operaciones/data/guardar-en-disco',
        cache: false,
        type: 'post',
        dataType: 'json',
        data: {id: id},
    });
}

$(document).ready(function () {
    
    $("#file-content").linedtextarea();

    $("#patente").change(function () {
        $.ajax({
            url: '/operaciones/data/get-customs',
            cache: false,
            type: 'post',
            dataType: 'json',
            data: {patente: $(this).val()}
        }).done(function (data) {
            if (data.success === true) {
                $("#divcustoms").html(data.html);
            }
        });
    });

    $("#form-validation").validate({
        rules: {
            patente: "required",
            aduana: "required",
            pedimento: {
                required: true,
                minlength: 7,
                maxlength: 7
            }
        },
        messages: {
            patente: "Seleccionar patente.",
            aduana: "Seleccionar aduana.",
            pedimento: {
                required: "Proporcionar numero de pedimento",
                minlength: "Pedimento minimo de 7 digitos",
                maxlength: "Pedimento maximo de 7 digitos"
            }
        }
    });

    $("#load-files").click(function (e) {
        e.preventDefault();
        if ($("#form-validation").valid()) {
            $("#form-validation").ajaxSubmit({
                success: showResponse
            });
        }
    });

});

//function actualizarActividad() {
//    $("#form-validation").ajaxSubmit({
//        success: showResponse
//    });
//}

function base64_decode(data) {

    var b64 = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=';
    var o1, o2, o3, h1, h2, h3, h4, bits, i = 0,
            ac = 0,
            dec = '',
            tmp_arr = [];

    if (!data) {
        return data;
    }

    data += '';

    do { // unpack four hexets into three octets using index points in b64
        h1 = b64.indexOf(data.charAt(i++));
        h2 = b64.indexOf(data.charAt(i++));
        h3 = b64.indexOf(data.charAt(i++));
        h4 = b64.indexOf(data.charAt(i++));

        bits = h1 << 18 | h2 << 12 | h3 << 6 | h4;

        o1 = bits >> 16 & 0xff;
        o2 = bits >> 8 & 0xff;
        o3 = bits & 0xff;

        if (h3 == 64) {
            tmp_arr[ac++] = String.fromCharCode(o1);
        } else if (h4 == 64) {
            tmp_arr[ac++] = String.fromCharCode(o1, o2);
        } else {
            tmp_arr[ac++] = String.fromCharCode(o1, o2, o3);
        }
    } while (i < data.length);

    dec = tmp_arr.join('');

    return dec.replace(/\0+$/, '');
}

function base64_encode(data) {

    var b64 = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=';
    var o1, o2, o3, h1, h2, h3, h4, bits, i = 0,
            ac = 0,
            enc = '',
            tmp_arr = [];

    if (!data) {
        return data;
    }

    do { // pack three octets into four hexets
        o1 = data.charCodeAt(i++);
        o2 = data.charCodeAt(i++);
        o3 = data.charCodeAt(i++);

        bits = o1 << 16 | o2 << 8 | o3;

        h1 = bits >> 18 & 0x3f;
        h2 = bits >> 12 & 0x3f;
        h3 = bits >> 6 & 0x3f;
        h4 = bits & 0x3f;

        // use hexets to index into b64, and append result to encoded string
        tmp_arr[ac++] = b64.charAt(h1) + b64.charAt(h2) + b64.charAt(h3) + b64.charAt(h4);
    } while (i < data.length);

    enc = tmp_arr.join('');

    var r = data.length % 3;

    return (r ? enc.slice(0, r - 3) : enc) + '==='.slice(r || 3);
}

