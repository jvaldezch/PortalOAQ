var elementsText = ["rfc", 'nombre'];
$.each(elementsText, function(index, value) {
    $('#' + value).keyup(function() {
        $(this).val($(this).val().toUpperCase());
    });
});

// http://eternicode.github.io/bootstrap-datepicker/#i18n
$("input[name='fechaIni']").datepicker({
    format: 'yyyy-mm-dd',
    language: "es",
    todayBtn: true,
    todayHighlight: true,
    autoclose: true
});
$("input[name='fechaFin']").datepicker({
    format: 'yyyy-mm-dd',
    language: "es",
    todayBtn: true,
    todayHighlight: true,
    autoclose: true
});

$('#nombre').typeahead({
    source: function(query, process) {
        return $.ajax({
            url: '/comercializacion/index/json-customers-by-name',
            type: 'get',
            data: {name: query},
            dataType: 'json',
            success: function(res) {
                return process(res);
            }
        });
    }
}).change(function() {
    $("#rfc").val('');
});

$('#nombre').change(function() {
    $.ajax({
        url: '/comercializacion/index/json-customer-rfc-by-name',
        type: 'get',
        data: {name: $("#nombre").val()},
        dataType: 'json',
        success: function(res) {
            if (res) {
                $("#rfc").val(res);
                $("#submit").removeAttr("disabled");
            }
        }
    });
});

$('[data-toggle="modal"]').click(function(e) {
    e.preventDefault();
    var url = $(this).attr('href');
    if (url.indexOf('#') == 0) {
        $(url).modal('open');
    } else {
        $.get(url, function(data) {
            $('<div class="modal hide fade" style="width: 750px; margin-left: -375px">' + data + '</div>')
                    .modal()
                    .on('hidden', function() {
                        $(this).remove();
                    });
        }).success(function() {
            $('input:text:visible:first').focus();
        });
    }
});

$("#submit").click(function(e) {
    e.preventDefault();
});
function viewReport() {
    window.open("/operaciones/index/reporte-pedimentos?rfc=" + $("#rfc").val() + "&fechaIni=" + $("#fechaIni").val() + "&fechaFin=" + $("#fechaFin").val() + "&aduana="+ $("#aduana").val(), '_blank', 'toolbar=0,location=0,menubar=0,height=550,width=1024,scrollbars=yes');
}