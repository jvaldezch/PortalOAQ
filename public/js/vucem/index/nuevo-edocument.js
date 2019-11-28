$(document).ready(function () {
    var Current;
    $("#bootstrap-firmante").change(function () {
        if ($("#bootstrap-firmante").val() !== '') {
            Current = $("#bootstrap-firmante").val();
            var url = baseurl + "/vucem/data/obtener-aduanas?rfc=" + $("#bootstrap-firmante").val();
            if (url.indexOf('#') === 0) {
                $(url).modal('open');
            } else {
                $.get(url, function (data) {
                    $('<div class="modal hide fade" style="width: 450px; margin-left: -275px">' + data + '</div>')
                            .modal()
                            .on('hidden', function () {
                                $(this).remove();
                            });
                }).success(function () {
                    $('input:text:visible:first').focus();
                    $.unblockUI();
                    $(".blockUI").fadeOut("slow");
                });
            }
        }
    });
    $("#bootstrap-submit").click(function (e) {
        e.preventDefault();
        $(".form-horizontal").submit();
    });
    $('#bootstrap-Referencia').keyup(function () {
        this.value = this.value.toUpperCase();
    });
    $('#bootstrap-Pedimento').keyup(function (e) {
        if (/\D/g.test(this.value)) {
            this.value = this.value.replace(/\D/g, '');
        }
    });
    $('input, textarea').keyup(function () {
        this.value = this.value.toUpperCase();
    });
});