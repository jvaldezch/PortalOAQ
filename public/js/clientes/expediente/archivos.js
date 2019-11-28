/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$(document).ready(function () {

    $(document.body).on("click",".image-link",function (ev) {
        ev.preventDefault();
        var w = window.open("/clientes/expediente/read-image?id=" + $(this).data("id"), 'Trafico Image ' + $(this).data("id"), 'toolbar=0,location=0,menubar=0,height=750,width=950,scrollbars=yes');
        w.focus();
        return false;
    });
    
});