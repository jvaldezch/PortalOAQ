/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


$(document).ready(function () {

    $("#form-extras").validate({
        errorPlacement: function (error, element) {
            $(element)
                    .closest("form")
                    .find("#" + element.attr("id"))
                    .after(error);
        },
        errorElement: "span",
        errorClass: "traffic-error",
        rules: {
            cvePed: "required",
            fechaEta: "required",
            fechaAlmacenaje: "required"
        },
        messages: {
            cvePed: "Seleccionar clave de pedimento.",
            fechaEta: "Especifique fecha ETA.",
            fechaAlmacenaje: "Especifique fecha libre almacenaje."
        }
    });

    $(document.body).on("click", "#peca", function () {
        if ($(this).is(":checked")) {
            $(this).val(1);
        } else {
            $(this).val(0);
        }
    });

    $(document.body).on("click", "#send-request", function (ev) {            
        ev.preventDefault();
        var id = $(this).data("id");
        $.confirm({
            title: "Confirmar", type: "orange", content: "¿Desea enviar la solicitud de anticipo?", escapeKey: "cerrar", boxWidth: "350px", useBootstrap: false,
            buttons: {
                si: {
                    btnClass: "btn-blue",
                    action: function () {
                        $.ajax({
                            url: "/trafico/post/enviar-solicitud",
                            cache: false,
                            type: "post",
                            dataType: "json",
                            data: {id: id},
                            success: function (res) {
                                if (res.success === true) {
                                    window.location.href = "/trafico/index/ultimas-solicitudes";
                                }
                            }
                        });
                    }
                },
                no: function () {}
            }
        });
    });
    
    $(document.body).on("click", "#save-request", function (ev) {            
        ev.preventDefault();
        $.confirm({
            title: "Confirmar", type: "green", content: "¿Desea guardar los cambios actuales?", escapeKey: "cerrar", boxWidth: "350px", useBootstrap: false,
            buttons: {
                si: {
                    btnClass: "btn-green",
                    action: function () {
                        if (!$("#form-extras").valid()) {
                            $("html, body").animate({
                                scrollTop: $("#pageTitle").offset().top - 50
                            }, 500);
                        } else {
                            $("#form-extras").ajaxSubmit({
                                type: "post",
                                url: "/trafico/data/guardar-solicitud",
                                dataType: "json",
                                success: function(res) {
                                    if(res.success === true) {
                                        location.replace("/trafico/index/editar-solicitud?id=" + $("#idSolicitud").val() + "&aduana=" + $("#aduana").val());
                                    }
                                }
                            });
                        }
                    }
                },
                no: function () {}
            }
        });
    });

    $("#fechaEta, #fechaAlmacenaje").datepicker({
        calendarWeeks: true,
        autoclose: true,
        language: "es",
        format: "yyyy-mm-dd"
    });

    $(document.body).on("input", "#bl, #numFactura, #mercancia", function () {
        $("#errors").html("");
        var input = $(this);
        var start = input[0].selectionStart;
        $(this).val(function (_, val) {
            return val.toUpperCase();
        });
        input[0].selectionStart = input[0].selectionEnd = start;
    });

    $(document.body).on("input", "input[name^='conceptos']", function () {
        this.value = this.value.replace(/[^0-9\.]/g,"");
    });

    $(document.body).on("focusout", "input[name^='conceptos'], #anticipo", function () {
        if(parseFloat($(this).val()) !== 0) {
            $(this).addClass("input-fill");                
        }
        var subtotal = 0;
        $("input[name^='conceptos']").each(function( index ) {
            var val = $(this).val();
            subtotal += parseFloat(val.replace(/,/g,""));
        });
        $("#subtotal").val(subtotal);
        $("#total").val((subtotal) - parseFloat($("#anticipo").val()));            

        $("#total").number(true, 2 );
        $("#anticipo").number(true, 2 );
        $("#subtotal").number(true, 2 );
    });
    
}); 