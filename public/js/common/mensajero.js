/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function setViewed(id, idTrafico) {
    $.post("/trafico/post/leer-mensaje", {id: id, idTrafico: idTrafico}, function(res) {
        if(res.success === true) {
            $("[data-id='" + idTrafico +  "']").attr("src", "/images/icons/message.png");
        }
    });
}

function asignarmeOperacion(idTrafico) {
    $.post("/trafico/post/asignarme-operacion", {idTrafico: idTrafico}, function(res) {
        if(res.success === true) {
            
        }
    });
}

$(document).ready(function () {
    
    $(document.body).on("click", "#allMessages", function (ev) {
        ev.preventDefault();
        $.confirm({
            title: "Todos los mensajes sin leer",
            escapeKey: "cerrar",
            buttons: {                
                cerrar: {
                    btnClass: "btn-red",
                    action: function(){}
                }
            },
            content: function () {
                var self = this;
                return $.ajax({
                    url: "/trafico/get/mensajes",
                    dataType: "json",
                    method: "get"
                }).done(function (res) {
                    var html = "";
                    if(res.success === true) {
                        html = res.html;
                    }
                    self.setContent(html);
                }).fail(function () {
                    self.setContent("Something went wrong.");
                });
            }
        });
    });
    
    $(document.body).on("click", ".mensajeroEnTrafico", function (ev) {
        ev.preventDefault();        
        var idTrafico = $(this).data("id");
        console.log(idTrafico);
        $.confirm({title: "Mensajero Interno", escapeKey: "cerrar",
            buttons: {
                reclamar: {
                    text: "Reclamar operación",
                    btnClass: "btn-blue",                    
                    action: function(){
                        asignarmeOperacion(idTrafico);
                    }                    
                },
                cerrar: {
                    btnClass: "btn-red",
                    action: function(){}
                }
            },
            content: function () {
                var self = this;
                return $.ajax({url: "/trafico/get/mensajero-en-trafico?idTrafico=" + idTrafico, dataType: "json", method: "GET"
                }).done(function (res) {
                    var html = "";
                    if(res.success === true) {
                        html = res.html;
                    }
                    self.setContent(html);
                }).fail(function () {
                    self.setContent("Something went wrong.");
                });
            }
        });
    });
    
    $(document.body).on("click", ".mensajero", function (ev) {
        ev.preventDefault();
        var idTrafico = $(this).data("id");
        console.log(idTrafico);
        $.confirm({title: "Mensajero Interno", escapeKey: "cerrar",
            buttons: {
                reclamar: {
                    text: "Reclamar operación",
                    btnClass: "btn-blue",                    
                    action: function(){
                        asignarmeOperacion($("#idTrafico").val());
                    }                    
                },
                cerrar: {
                    btnClass: "btn-red",
                    action: function(){}
                }
            },
            content: function () {
                var self = this;
                return $.ajax({url: "/trafico/get/mensajero?idTrafico=" + idTrafico, dataType: "json", method: "GET"
                }).done(function (res) {
                    var html = "";
                    if(res.success === true) {
                        html = res.html;
                    }
                    self.setContent(html);
                }).fail(function () {
                    self.setContent("Something went wrong.");
                });
            }
        });
    });
    
    $(document.body).on("click", "#sendMessage", function (ev) {
        ev.preventDefault();
        if ($("#formMessage").valid()) {
            $("#formMessage").ajaxSubmit({
                url: "/trafico/post/nuevo-mensaje",
                type: "post",
                dataType: "json",
                success: function (res) {
                    if (res.success === true) {
                        $("#attachedFilesm").empty();
                        $("#formMessage")[0].reset();
                        $(".rTable").find(".rTableRow:last").before('<div class="rTableRow"><div class="rTableCell" style="width: 150px">' + res.de + '</div><div class="rTableCell">' + res.mensaje + '</div><div class="rTableHead" style="width: 160px">' + res.fecha + '</div></div>');
                    }
                }
            });
        }
    });
    
    $(document.body).on("click", "#attachm", function () {
        $("#filesm").click();
    });
    
    $(document.body).on("change", "#formMessage #filesm", function () {
        var filename = $("#filesm").val().replace(/C:\\fakepath\\/i, '');
        $("#attachedFilesm").append('<img src="/images/icons/attachment.gif"><span style="font-size: 11px">' + filename + '</span>');
    });
    
    $(document).on("input", "#mensaje", function() {
        var input = $(this);
        var start = input[0].selectionStart;
        $(this).val(function (_, val) {
            return val.toUpperCase();
        });
        input[0].selectionStart = input[0].selectionEnd = start;
    });

});