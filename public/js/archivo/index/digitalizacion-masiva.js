/**
 * programmed by Jvaldez at gmail
 * 2015.may.14
 */

function reloadFiles() {
    $.ajax({
        cache: false,
        type: "post",
        dataType: "json",
        url: "/archivo/ajax/reload-files",
        success: function (res) {
            if (res.success === true) {
                window.location.href = "/archivo/index/digitalizacion-masiva";
            }
        }
    });
}

function temporalFile(id, action) {
    if (action === "save") {
        if ($("#formrow_" + id).valid()) {
            $("#formrow_" + id).ajaxSubmit({
                url: "/archivo/ajax/temporal-file",
                method: "post",
                dataType: "json",
                success: function (res) {
                    if (res.success === true) {
                        window.location.href = "/archivo/index/digitalizacion-masiva";
                    }
                }
            });
            return false;
        } else {
            return false;
        }
    }
    $.ajax({
        type: "post",
        dataType: "json",
        url: "/archivo/ajax/temporal-file",
        data: {id: id, action: action},
        success: function (res) {
            if (res.html) {
                $("#row_" + res.id).html(res.html);
                validateForm("#formrow_" + res.id);
            }
            if (res.success === true) {
                window.location.href = "/archivo/index/digitalizacion-masiva";
            }
        }
    });
}

function validateForm(id) {    
    $("table.traffic-table").find("th").each(function(index, el) {        
        var theWidth = $("table.traffic-table").find("th").eq(index).width();
        $(id).find("td").eq(index).width(theWidth + "px");
    });
}

$(document).ready(function () {

    $("#bulk").validate({
        errorPlacement: function (error, element) {
            $(element)
                    .closest("form")
                    .find("label[for=\"" + element.attr("id") + "\"]")
                    .append(error);
        },
        errorElement: "span",
        errorClass: "traffic-error",
        rules: {
            "files[]": {
                required: true
            },
            type: {
                required: true                
            }
        },
        messages: {
            "files[]": {
            },
            type: {
                required: "Seleccione el tipo de archivo"
            }
        }
    });

    $("#submit").click(function (e) {
        e.preventDefault();
        if ($("#bulk").valid()) {
            $("#bulk").ajaxSubmit({
                type: "post",
                dataType: "json",
                success: function (res) {
                    if (res.success === true) {
                        window.location.href = "/archivo/index/digitalizacion-masiva";
                    }
                }
            });
        }
    });

    $("#checkall").change(function () {
        $(".checkfiles").prop("checked", $(this).prop("checked"));
    });

    $("#sendselected").one("click", function () {
        $(this).prop("disabled", true).addClass("disabled");
        var checkedValues = $(".checkfiles:checked").map(function () {
            return this.value;
        }).get();
        $.each(checkedValues, function (key, value) {
            $.ajax({
                cache: false,
                type: "post",
                dataType: "json",
                data: {id: value},
                url: "/archivo/ajax/send-file",
                success: function (res) {
                    if (res.success === true) {
                        $("#file_" + res.id).html(res.html);
                    }
                }
            });
        });
    });
    
    $("#deleteselected").on("click", function () {
        $(this).prop("disabled", true).addClass("disabled");
        var checkedValues = $(".checkfiles:checked").map(function () {
            return this.value;
        }).get();
        $.each(checkedValues, function (key, value) {
            $.ajax({
                cache: false,
                type: "post",
                dataType: "json",
                data: {id: value},
                url: "/archivo/ajax/delete-files",
                success: function (res) {
                    if (res.success === true) {
                        $("#file_" + res.id).hide();
                    }
                }
            });
        });
    });

});
