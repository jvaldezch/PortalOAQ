<?php

class Zend_View_Helper_ArchivosIconos extends Zend_View_Helper_Abstract {

    public function archivosIconos($idArchivo, $tipoArchivo, $delete = null) {
        $restricted = array(22, 27);
        $html = '<div id=\"icon_{$idArchivo}\">';
        if (in_array($tipoArchivo, $restricted)) {
            return '';
        } else {
            $html .= "<div class=\"traffic-icon traffic-icon-edit\" onclick=\"editarArchivo('{$idArchivo}');\"></div>";
            if (isset($delete)) {
                $html .= "<div class=\"traffic-icon traffic-icon-delete\" onclick=\"borrarArchivo('{$idArchivo}');\"></div>";
            }
        }
        return $html . '</div>';
    }

}
