/*
 **
 * 
 */

var optionsprod = {
    url: "/vucem/data/put-invoice-product",
    type: "post",
    dataType: "json",
    timeout: 3000,
    success: updateIframe
};
$.validator.setDefaults({
    submitHandler: function () {
        $('#invoice-products').ajaxSubmit(optionsprod);
    }
});

function updateIframe(responseText, statusText, xhr, $form) {
    if (responseText.success === true) {
        $("#ID_PROD").val("");
        $("#invoice-products").trigger("reset");
        document.getElementById("iframe").contentDocument.location.reload(true);
        new $.Zebra_Dialog("<strong>Se ha actualizado el registro del producto adecuadamente.</strong>", {
            'buttons': false,
            'modal': false,
            'position': ['right - 20', 'top + 70'],
            'title': 'Factura actualizada',
            'custom_class': 'infodialog',
            'auto_close': 4000
        });
    } else {
        alert(responseText.message);
    }
}

function editProduct(fact, prod) {
    $("#ID_FACT").val(fact);
    $("#ID_PROD").val(prod);
    $.ajax({
        url: '/vucem/data/get-invoice-product?idfact=' + fact + '&idprod=' + prod,
        cache: false,
        dataType: 'json'
    }).done(function (data) {
        if (data.success === true) {
            $("#DESC_COVE").val(data.DESC_COVE);
            $("#CODIGO").val(data.CODIGO);
            $("#MONVAL").val(data.MONVAL);
            $("#ORDEN").val(data.ORDEN);
            $("#UMC").val(data.UMC);
            $("#UMT").val(data.UMT);
            $("#CANTFAC").val(data.CANTFAC);
            $("#CANTTAR").val(data.CANTTAR);
            $("#CANTFAC").val(data.CANTFAC);
            $("#PARTE").val(data.PARTE);
            $("#PREUNI").val(data.PREUNI);
            $("#VALCOM").val(data.VALCOM);
            $("#VALDLS").val(data.VALDLS);
            $("#VALCEQ").val(data.VALCEQ);
            $("#MARCA").val(data.MARCA);
            $("#MODELO").val(data.MODELO);
            $("#NUMSERIE").val(data.NUMSERIE);
            $("#SUBMODELO").val(data.SUBMODELO);
            $("#UMC_OMA").val(data.UMC_OMA);
        } else {
            alert(data.message);
        }
    });
}

function deleteProduct(fact, prod) {
    var r = confirm("¿Esta seguro que desea borrar el producto?");
    if (r === true) {
        $.ajax({
            url: '/vucem/data/delete-invoice-product',
            cache: false,
            type: 'post',
            dataType: 'json',
            data: {fact: fact, prod: prod}
        }).done(function (data) {
            if (data.success === true) {
                document.getElementById("products-frame").contentDocument.location.reload(true);
                new $.Zebra_Dialog('<strong>' + data.message + '</strong>', {
                    'buttons': false,
                    'modal': false,
                    'position': ['right - 20', 'top + 70'],
                    'title': 'Producto',
                    'custom_class': 'infodialog',
                    'auto_close': 4000
                });
            }
        });
    }
}

var cal_obj = null;
var format = '%Y-%m-%d';
var elem;
function show_cal(el) {
    elem = el;
    if (cal_obj)
        return;
    var text_field = document.getElementById(el);
    cal_obj = new RichCalendar();
    cal_obj.start_week_day = 0;
    cal_obj.show_time = false;
    cal_obj.user_onchange_handler = cal_on_change;
    cal_obj.user_onclose_handler = cal_on_close;
    cal_obj.user_onautoclose_handler = cal_on_autoclose;
    cal_obj.parse_date(text_field.value, format);
    cal_obj.show_at_element(text_field, "adj_right-bottom");
}

function cal_on_change(cal, object_code) {
    if (object_code === 'day') {
        document.getElementById(elem).value = cal.get_formatted_date(format);
        cal.hide();
        cal_obj = null;
    }
}

function cal_on_close(cal) {
    if (window.confirm('Are you sure to close the calendar?')) {
        cal.hide();
        cal_obj = null;
    }
}

function cal_on_autoclose(cal) {
    cal_obj = null;
}

var optionsinvd = {
    url: "/vucem/data/put-invoice-data",
    type: "post",
    dataType: "json",
    timeout: 3000,
    success: updateIdFact
};

function updateIdFact(data, statusText, xhr, $form) {
    if (data.success === true) {
        $("#IdFact").val(data.uuid);
        $("#ID_FACT").val(data.uuid);
        $("#invoice-products :input").removeAttr("disabled");
        if (data.uuid) {
            window.location.replace("/vucem/index/agregar-nueva-factura?uuid=" + data.uuid);
        }
    }
}

function llenarDatosEmisor(data) {
    $("#ProTaxID").val(data.rfc);
    $("#ProIden").val(data.identificador);
    $("#CvePro").val(data.cvepro);
    $("#ProNombre").val(data.razon_soc);
    $("#ProCalle").val(data.calle);
    $("#ProColonia").val(data.colonia);
    $("#ProNumExt").val(data.numext);
    $("#ProNumInt").val(data.numint);
    $("#ProMun").val(data.municipio);
    $("#ProEdo").val(data.estado);
    $("#ProCP").val(data.cp);
    $("#ProPais").val(data.pais);
}

$(document).ready(function () {

    $('input').focusout(function () {
        this.value = this.value.toLocaleUpperCase();
    });
    if ($("#IdFact").val() === '') {
        $("#invoice-products :input").attr("disabled", "disabled");
    }
    
    $("#save-header").click(function (e) {
        e.preventDefault();
        $.ajax({
            url: "/vucem/ajax/guardar-encabezado-factura",
            cache: false,
            dataType: "json",
            type: "post",
            data: { numFactura: $("#NumFactura").val(), idFact: $("#IdFact").val()},
            success: function(res) {
                if (res.success === true) {
                    window.location.replace("/vucem/index/agregar-nueva-factura?uuid=" + res.idFact);
                }
            }
        });
    });
    
    $("#load-ws").click(function (e) {
        e.preventDefault();
        if ($("#Referencia").val() === '' || $("#NumFactura").val() === '') {
            alert("Debe proporcionar referencia y factura");
            return;
        }
        $.blockUI({
            centerY: 0,
            css: {top: '10px', left: '', right: '10px'},
            message: '<h4 style="padding: 10px 10px; background-color: #fff; border:0; color: #000;"><img src="/images/loader.gif" style="margin-right:5px" /> Por favor espere... <a onclick="$.unblockUI();" style="float:right; font-size: 11px; cursor:pointer;">[x]</a></h4>',
            baseZ: 2000
        });
        $.ajax({
            url: '/vucem/data/import-from-ws?&patente=' + $("#Patente").val() + '&aduana=' + $("#Aduana").val() + '&referencia=' + $("#Referencia").val() + '&numfactura=' + $("#NumFactura").val() + '&tipo=' + $("#TipoOperacion").val() + '&ajuste=' + $("#FactFacAju").val() + "&certificado=" + $("#CertificadoOrigen").val() + "&subdiv=" + $("#Subdivision").val() + "&numexportador=" + $("#NumExportador").val(),
            cache: false,
            dataType: 'json'
        }).done(function (data) {
            if (data.success === true) {
                $.unblockUI();
                if (data.IdFact) {
                    window.location.replace("/vucem/index/agregar-nueva-factura?uuid=" + data.IdFact);
                }
            } else {
                alert(data.message);
                $.unblockUI();
            }
        });
    });

    $('input, textarea').bind('change', function () {
        this.value = this.value.toLocaleUpperCase();
    });
    
    $("#invoice-data").validate({
        rules: {
            Pedimento: {
                required: true,
                minlength: 7,
                digits: true
            },
            Referencia: {
                required: true,
                minlength: 7
            },
            FechaFactura: {
                required: true,
                dateISO: true
            },
            NumFactura: "required",
            CteRfc: "required",
            CteIden: "required",
            CteNombre: "required",
            CteCalle: "required",
            CtePais: "required",
            ProTaxID: "required",
            ProIden: "required",
            ProCalle: "required",
            ProNombre: "required",
            ProPais: "required"
        },
        messages: {
            Pedimento: {
                required: "Proporcionar el pedimento",
                minlength: "Minimo 7 digitos",
                digits: "Pedimento deben ser solo números"
            },
            Referencia: {
                required: "Proporcionar referencia",
                minlength: "Minimo 7 digitos"
            },
            FechaFactura: {
                required: "Fecha de factura necesaria",
                dateISO: "La fecha no es válida"
            },
            NumFactura: "Proporcionar la factura",
            CteRfc: "Identificador es obligatorio",
            CteIden: "Tipo identificador de destinatario es obligatorio",
            CteNombre: "Nombre del destinatario es obligatorio",
            CteCalle: "Debe especificar la calle del destinatario",
            CtePais: "Debe especificar el país del destinatario",
            ProTaxID: "Identificador es obligatorio",
            ProIden: "Tipo identificador de emisor es obligatorio",
            ProNombre: "Nombre del emisor obligatorio",
            ProCalle: "Debe especificar la calle del emisor",
            ProPais: "Debe especificar el país del emisor"
        }
    });
    $("#invoice-products").validate({
        rules: {
            PARTE: {
                required: true
            }
        },
        messages: {
            PARTE: {
                required: "Número de parte es necesario."
            }
        }
    });

    $("#CANTFAC, #PREUNI, #VALCOM").focusout(function () {
        if ($(this).val() !== '' && $(this).attr('id') !== 'PREUNI') {
            var valor = parseFloat($(this).val());
            $(this).val(valor.toFixed(4));
        } else if ($(this).val() !== '' && $(this).attr('id') === 'PREUNI') {
            var valor = parseFloat($(this).val());
            $(this).val(valor.toFixed(6));
        }
        if ($("#CANTFAC").val() !== '' && $("#VALCOM").val() !== '' && $("#PREUNI").val() === '') {
            var unit = $("#CANTFAC").val() / $("#VALCOM").val();
            $("#PREUNI").val(unit.toFixed(6));
        }
        if ($("#CANTFAC").val() !== '' && $("#VALCOM").val() === '' && $("#PREUNI").val() !== '') {
            var valcom = $("#CANTFAC").val() * $("#PREUNI").val();
            $("#VALCOM").val(valcom.toFixed(4));
        }
    });
    
    $("#VALCEQ").focusout(function () {
        if ($(this).val() !== '' && $(this).attr('id') !== 'PREUNI') {
            var valor = parseFloat($(this).val());
            $(this).val(valor.toFixed(6));
        }
    });
    
    $("#MONVAL").change(function () {
        if ($(this).val() === 'USD') {
            $("#VALDLS").val($("#VALCOM").val());
            $("#VALCEQ").val('1.00');
        }
    });
    
    $("#VALCEQ").focusout(function () {
        if ($("#MONVAL").val() !== 'USD' && $("#VALCEQ").val() !== '' && $("#VALDLS").val() !== '') {
            var equiv = $("#VALCEQ").val() * $("#VALCOM").val();
            $("#VALDLS").val(equiv.toFixed(4));
        }
    });
    
    $("#UMC").change(function () {
        switch ($(this).val()) {
            case '6':
                $("#UMC_OMA").val('C62_1');
        }
    });
    
    $('#CteRfc').typeahead({
        remote: '/vucem/data/obtener-destinatarios-enh?query=%QUERY' + '&patente=' + $("#Patente").val() + '&aduana=' + $("#Aduana").val() + "&tipo=" + $("#TipoOperacion").val() + "&taxid=" + $("#ProTaxID").val(),
        minLength: 3,
        limit: 25,
        hint: true,
        highlight: true
    }).bind('focusout', function () {
        $.ajax({
            url: '/vucem/data/detalle-destinatario-enh?rfc=' + $(this).val() + '&patente=' + $("#Patente").val() + '&aduana=' + $("#Aduana").val() + "&tipo=" + $("#TipoOperacion").val() + "&taxid=" + $("#ProTaxID").val(),
            cache: false,
            dataType: 'json'
        }).done(function (data) {
            if ($("#TipoOperacion").val() === 'TOCE.IMP') {
                $("#CteIden").val(data.identificador);
                $("#CveCli").val(data.cvecte);
                $("#CteNombre").val(data.razon_soc);
                $("#CteCalle").val(data.calle);
                $("#CteColonia").val(data.colonia);
                $("#CteNumExt").val(data.numext);
                $("#CteNumInt").val(data.numint);
                $("#CteMun").val(data.municipio);
                $("#CteEdo").val(data.estado);
                $("#CteCP").val(data.cp);
                $("#CtePais").val(data.pais);
            }
        });
    });

    $('#ProNombre').typeahead({
        classNames: {
            hint: 'tt-hint-prov',
            selectable: 'tt-hint-prov',
            input: 'tt-hint-prov'
        },
        remote: '/vucem/ajax/obtener-proveedores?query=%QUERY' + '&patente=' + $("#Patente").val() + '&aduana=' + $("#Aduana").val() + "&tipo=" + $("#TipoOperacion").val() + "&emi=" + $("#ProTaxID").val() + "&dest=" + $("#CteRfc").val(),
        minLength: 3,
        hint: true,
        highlight: true
    }).bind('focusout', function () {
        $.ajax({
            url: '/vucem/ajax/obtener-detalle-proveedor?nom=' + $("#ProNombre").val() + "&tipo=" + $("#TipoOperacion").val() + "&dest=" + $("#CteRfc").val(),
            cache: false,
            dataType: 'json'
        }).done(function (data) {
            if ($("#TipoOperacion").val() === 'TOCE.IMP') {
                llenarDatosEmisor(data);
            }
        });
    });

    $('#ProTaxID').typeahead({
        remote: '/vucem/data/obtener-emisores?query=%QUERY' + '&patente=' + $("#Patente").val() + '&aduana=' + $("#Aduana").val() + "&tipo=" + $("#TipoOperacion").val() + "&taxid=" + $("#ProTaxID").val(),
        minLength: 3,
        limit: 25,
        hint: true,
        highlight: true
    }).bind('focusout', function () {
        $.ajax({
            url: '/vucem/data/detalle-emisor?rfc=' + $(this).val() + '&patente=' + $("#Patente").val() + '&aduana=' + $("#Aduana").val() + "&tipo=" + $("#TipoOperacion").val() + "&taxid=" + $("#ProTaxID").val(),
            cache: false,
            dataType: 'json'
        }).done(function (data) {
            if ($("#TipoOperacion").val() === 'TOCE.IMP') {
                $("#ProIden").val(data.identificador);
                $("#CvePro").val(data.cvepro);
//                $("#CteRfc").val(data.rfc);
                $("#ProNombre").val(data.razon_soc);
                $("#ProCalle").val(data.calle);
                $("#ProColonia").val(data.colonia);
                $("#ProNumExt").val(data.numext);
                $("#ProNumInt").val(data.numint);
                $("#ProMun").val(data.municipio);
                $("#ProEdo").val(data.estado);
                $("#ProCP").val(data.cp);
                $("#ProPais").val(data.pais);
            }
        });
    });

    $("#invoice-save").click(function (e) {
        e.preventDefault();
        if ($("#IdFact").val() !== '') {
            if ($("#invoice-data").valid()) {
                $("#invoice-data").ajaxSubmit(optionsinvd);
            }
        } else {
            if ($("#invoice-data").valid()) {
                $("#invoice-data").ajaxSubmit(optionsinvd);
            }
        }
    });

}); // ready


/** UPPER CASE INPUT */
/*$("#ProNombre").on('input', function (evt) {
 var input = $(this);
 var start = input[0].selectionStart;
 $(this).val(function (_, val) {
 return val.toUpperCase();
 });
 input[0].selectionStart = input[0].selectionEnd = start;
 });*/