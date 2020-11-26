
$(document).ready(function () {

    function onScanSuccess(qrMessage) {
        // handle the scanned code as you like
        let obj = JSON.parse(qrMessage);
        if (obj.id_bulto) {
            window.location.href = `/mobile/bodega/editar-bulto?id=${obj.id_bulto}`;
        }
    }
    
    function onScanFailure(error) {
        // handle scan failure, usually better to ignore and keep scanning
    }
    
    let html5QrcodeScanner = new Html5QrcodeScanner(
        "reader", { fps: 10, qrbox: 250 }, /* verbose= */ true);
    html5QrcodeScanner.render(onScanSuccess, onScanFailure);

});