<?php

class Zend_View_Helper_FileSize extends Zend_View_Helper_Abstract {

    public function fileSize($location) {
        $fileSize = filesize($location) / 1024 / 1024;
        if ($fileSize >= 2) {
            return '&asymp; <i class="fas fa-exclamation-triangle" style="color: red" title="El archivo ha supera el tamaño permitido por los Servicios Web de VUCEM."></i><em style="color: red">' . number_format($fileSize, 2, '.', ',') . ' mb</em>';
        } else {
            return '&asymp; <em>' . number_format($fileSize, 2, '.', ',') . ' mb</em>';
        }
    }

}
