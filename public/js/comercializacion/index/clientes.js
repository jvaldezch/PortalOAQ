/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$(document).ready(function () {
    
    $('#nombre').typeahead({
        source: function (query, process) {
            return $.ajax({
                url: '/comercializacion/index/json-customers-by-name',
                type: 'get',
                data: {name: query},
                dataType: 'json',
                success: function (res) {
                    return  process(res);
                }
            });
        }
    });
    
    $("#nombre, #rfc").on('input', function (evt) {
        $("#errors").html("");
        var input = $(this);
        var start = input[0].selectionStart;
        $(this).val(function (_, val) {
            return val.toUpperCase();
        });
        input[0].selectionStart = input[0].selectionEnd = start;
    });
    
});