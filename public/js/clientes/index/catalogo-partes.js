$(document).ready(function () {
    $("#load-parts").click(function (e) {
        e.preventDefault();
        $("#parts-frame").attr("src", '/operaciones/data/ver-partes?rfc=' + $("#clientes").val() + '&tipo=' + $("#tipo").val() + "&page=1" + "&descripcion=" + $("#descripcion").val());
    });
    $("#clear-search").click(function (e) {
        e.preventDefault();
        $("#filters").trigger('reset');
    });
    $("#btn-search").click(function (e) {
        e.preventDefault();
        $("#parts-frame").attr("src", '/operaciones/data/ver-partes?rfc=' + $("#clientes").val() + '&tipo=' + $("#tipo").val() + "&page=1&descripcion=" + $("#descripcion").val());
    });
    $('#descripcion').keypress(function (event) {
        if (event.which === 13) {
            $("#btn-search").trigger("click");
            return;
        }
        event.stopPropagation();
    });
    function nextPage(page) {
        $("#parts-frame").attr("src", '/operaciones/data/ver-partes?rfc=' + $("#clientes").val() + '&tipo=' + $("#tipo").val() + "&page=" + page + "&descripcion=" + $("#descripcion").val());
    }
});