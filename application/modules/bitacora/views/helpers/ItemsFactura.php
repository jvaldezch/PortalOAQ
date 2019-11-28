<?php

class Zend_View_Helper_ItemsFactura extends Zend_View_Helper_Abstract {

    public function itemsFactura($idGuia, $idFactura) {
        $mppr = new Webservice_Model_TraficoBitacoraItems();
        $arr = $mppr->obtenerItems($idGuia, $idFactura);
        if (!empty($arr)) {
            return $arr;
        }
        return;
    }
    
    public function fotosItem($idGuia, $idFactura, $idItem) {
        $mppr = new Webservice_Model_TraficoBitacoraFotos();
        $arr = $mppr->obtenerFotos($idGuia, $idFactura, $idItem);
        if (!empty($arr)) {
            return $arr;
        }
        return;
    }
    
    public function verImagen($filename) {
        if (file_exists($filename)) {
            return 'data: '.mime_content_type($filename).';base64,' . base64_encode(file_get_contents($filename));
        }
    }

}
