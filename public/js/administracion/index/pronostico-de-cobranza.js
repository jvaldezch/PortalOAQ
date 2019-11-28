$(document).ready(function () {

    $("input[name='fechaIni']").datepicker({
        format: "yyyy-mm-dd",
        language: "es",
        autoclose: true
    });

    var elementsText = ["rfc", "nombre"];
    $.each(elementsText, function (index, value) {
        $("#" + value).keyup(function () {
            $(this).val($(this).val().toUpperCase());
        });
    });

    $("#nombre").typeahead({
        source: function (query, process) {
            return $.ajax({
                url: "/trafico/get/clientes",
                type: "get",
                data: {name: query},
                dataType: "json",
                success: function (res) {
                    return process(res);
                }
            });
        }
    }).change(function () {
        $("#rfc").val("");
    });
    
    $(document.body).on("change", "#nombre", function () {
        $.ajax({
            url: "/trafico/get/rfc-de-cliente",
            type: "get",
            data: {name: $("#nombre").val()},
            dataType: "json",
            success: function (res) {
                if (res) {
                    $("#rfc").val(res[0]["rfc"]);
                }
            }
        });
    });
    
    $(document.body).on("click", "#submit", function () {
        $("#form").submit();
    });
    
    $(document.body).on("submit", "#form", function (ev) {
        if (!$("#form").valid()) {
            ev.preventDefault();
        }
    });

    $("[data-toggle='modal']").click(function (e) {
        e.preventDefault();
        var url = $(this).attr("href");
        if (url.indexOf("#") === 0) {
            $(url).modal("open");
        } else {
            $.get(url, function (data) {
                $("<div class='modal hide fade' style='width: 750px; margin-left: -375px'>" + data + "</div>")
                        .modal()
                        .on("hidden", function () {
                            $(this).remove();
                        });
            }).success(function () {
                $("input:text:visible:first").focus();
            });
        }
    });

});