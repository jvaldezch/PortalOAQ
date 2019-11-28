$("a#send").click(function(event) {
    event.preventDefault();
    $.ajax({
        url: "/archivo/index/send-email",
        cache: false
    }).done(function(html) {
        $('#closeModal').trigger('click');
    });
});