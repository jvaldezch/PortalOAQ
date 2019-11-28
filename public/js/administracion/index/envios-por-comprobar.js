/**
 * 
 * @param {type} param
 */
$(document).ready(function () {
    
    $("input[name='fechaIni']").datepicker({
        format: 'yyyy-mm-dd',
        language: "es",
        autoclose: true
    });
    
    $(document.body).on("click", "#submit", function () {
        $("#form").submit();
    });
    
    $(document.body).on("submit", "#form", function (ev) {
        if (!$("#form").valid()) {
            ev.preventDefault();
        }
    });
    
});