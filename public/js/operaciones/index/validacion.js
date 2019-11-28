$(document).ready(function () {
    $("#patente").change(function () {
        $.ajax({
            url: "/operaciones/data/get-customs",
            cache: false,
            type: "post",
            dataType: "json",
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
            aduana: "required"
        },
        messages: {
            patente: "Seleccionar patente.",
            aduana: "Seleccionar aduana."
        }
    });
    $("#send-file").click(function (e) {
        if ($("#archivo-m").val()) {
            $.ajax({
                url: "/operaciones/data/send-file",
                cache: false,
                type: "post",
                dataType: "json",
                data: {patente: $("#patente").val(), aduana: $("#aduana").val(), archivo: $("#archivo-m").val()}
            });
        } else {
            alert("No ha seleccionado archivo.");
        }
    });
    
    $("#send-paid").click(function (e) {
        if ($("#archivo-e").val()) {
            $.ajax({
                url: "/operaciones/data/send-paid",
                cache: false,
                type: "post",
                dataType: "json",
                data: {patente: $("#patente").val(), aduana: $("#aduana").val(), archivo: $("#archivo-e").val()}
            });
        } else {
            alert("No ha seleccionado archivo archivo de pago.");
        }
    });
    $("#load-files").click(function (e) {
        e.preventDefault();
        if ($("#form-validation").valid()) {
            $("#form-validation").ajaxSubmit({
                dataType: "json",
                success: function (res) {
                    if (res.success === true) {
                        $("#files-m").html(res.htmlm);
                        $("#files-r").html(res.htmlr);
                        $("#files-p").html(res.htmlp);
                        $("#files-k").html(res.htmlk);
                        $("#files-e").html(res.htmle);
                    }
                }
            });
        }
    });
    $(document.body).on("click", "#archivo-m", function () {
        if ($("#archivo-m").val()) {
            verArchivo($("#patente").val(), $("#aduana").val(), $("#archivo-m").val(), "#content-m");
        }
    });
    $(document.body).on("click", "#archivo-r", function () {
        if ($("#archivo-r").val()) {
            verArchivo($("#patente").val(), $("#aduana").val(), $("#archivo-r").val(), "#content-r");
        }
    });
    $(document.body).on("click", "#archivo-p", function () {
        if ($("#archivo-p").val()) {
            verArchivo($("#patente").val(), $("#aduana").val(), $("#archivo-p").val(), "#content-p");
        }
    });
    $(document.body).on("click", "#archivo-k", function () {
        if ($("#archivo-k").val()) {
            verArchivo($("#patente").val(), $("#aduana").val(), $("#archivo-k").val(), "#validacion-respuesta");
        }
    });
    $(document.body).on("click", "#content-m", function () {
        obtenerArchivo($(this).val(), "#validacion-respuesta");
    });
    function verArchivo(pat, adu, arch, id) {
        $.ajax({
            url: "/operaciones/data/get-file",
            cache: false,
            type: "post",
            dataType: "json",
            data: {patente: pat, aduana: adu, archivo: arch},
            success: function (res) {
                if (res.success === true) {
                    $(id).html(res.html);
                }
            }
        });
    }
});

function obtenerArchivo(filename, id) {
    $.ajax({
        url: "/operaciones/data/obtener-archivo",
        cache: false,
        type: "post",
        dataType: "json",
        data: {filename: filename},
        success: function (res) {
            if (res.success === true) {
                $(id).html(base64_decode(res.html));
            } else {
                return false;
            }
        }
    });
}

function base64_decode(data) {

    var b64 = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";
    var o1, o2, o3, h1, h2, h3, h4, bits, i = 0,
            ac = 0,
            dec = "",
            tmp_arr = [];

    if (!data) {
        return data;
    }

    data += "";

    do { // unpack four hexets into three octets using index points in b64
        h1 = b64.indexOf(data.charAt(i++));
        h2 = b64.indexOf(data.charAt(i++));
        h3 = b64.indexOf(data.charAt(i++));
        h4 = b64.indexOf(data.charAt(i++));

        bits = h1 << 18 | h2 << 12 | h3 << 6 | h4;

        o1 = bits >> 16 & 0xff;
        o2 = bits >> 8 & 0xff;
        o3 = bits & 0xff;

        if (h3 === 64) {
            tmp_arr[ac++] = String.fromCharCode(o1);
        } else if (h4 === 64) {
            tmp_arr[ac++] = String.fromCharCode(o1, o2);
        } else {
            tmp_arr[ac++] = String.fromCharCode(o1, o2, o3);
        }
    } while (i < data.length);

    dec = tmp_arr.join("");

    return dec.replace(/\0+$/, "");
}

function base64_encode(data) {

    var b64 = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";
    var o1, o2, o3, h1, h2, h3, h4, bits, i = 0,
            ac = 0,
            enc = "",
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

    enc = tmp_arr.join("");

    var r = data.length % 3;

    return (r ? enc.slice(0, r - 3) : enc) + "===".slice(r || 3);
}

