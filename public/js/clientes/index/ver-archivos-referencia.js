$(document).ready(function () {
    loadFiles();
});
function loadFiles() {
    $.ajax({
        url: "/clientes/data/referencia-cargar-archivos",
        data: {referencia: $("#referencia").val(), patente: $("#patente").val(), aduana: $("#aduana").val()},
        context: document.body
    }).done(function (data) {
        $("#files").html(data);
    });
}
$("body").on("click", ".openfile", function (e) {
    e.preventDefault();
    window.open($(this).attr("href"), "_blank", "toolbar=0,location=0,menubar=0,height=550,width=800,scrollbars=yes");
});

function cargarValidacion(patente, aduana, pedimento) {
    $.ajax({
        url: "/clientes/data/archivos-validacion",
        type: "post",
        data: {patente: patente, aduana: aduana, pedimento: pedimento},
        dataType: "json",
        success: function (res) {
            if (res.success === true) {
                $("#archivos-validacion").html(res.html);
            }
        }
    });
}