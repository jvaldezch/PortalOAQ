<?php

class Zend_View_Helper_ArchivosIconos extends Zend_View_Helper_Abstract {

    public function archivosIconos($idArchivo, $tipoArchivo, $delete = null) {
        $restricted = array(22, 27);
        $html = '<div id=\"icon_{$idArchivo}\">';
        if (!in_array($tipoArchivo, $restricted)) {
            $html .= "<div class=\"traffic-icon traffic-icon-edit\" onclick=\"parent.editarArchivo('{$idArchivo}');\"></div>";
        }
        if ($tipoArchivo > 99 && $tipoArchivo < 9999) {
            $html .= "<div class=\"traffic-icon traffic-icon-vucem\" onclick=\"enviarEdocument({$idArchivo});\"></div>";
        }
        $html .= "<div class=\"traffic-icon traffic-icon-delete\" onclick=\"borrarArchivo('{$idArchivo}');\"></div>";
        return $html . '</div>';
    }

}
