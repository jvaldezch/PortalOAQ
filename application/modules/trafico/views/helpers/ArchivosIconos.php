<?php

class Zend_View_Helper_ArchivosIconos extends Zend_View_Helper_Abstract {

    public function archivosIconos($idArchivo, $tipoArchivo, $delete = null, $nombreArchivo = null) {
        $restricted = array(22, 27);
        $html = '<div id="icon_' . $idArchivo . '" style="font-size:1.2em; color: #2f3b58; float: left; margin-right: 5px; margin-bottom: 3px">';
        if (isset($nombreArchivo) && !in_array($tipoArchivo, array(21, 22, 27, 28, 56))) {
            if (preg_match('/.pdf$/', $nombreArchivo)) {
                $html .= '<i class="fab fa-uniregistry" onclick="enviarEdocument(' . $idArchivo . ');" style="cursor: pointer" title="Transmitir a VUCEM"></i>&nbsp;';
            }
        }
        if (!in_array($tipoArchivo, $restricted)) {
            $html .= '<i class="fas fa-pencil-alt" onclick="parent.editarArchivo(' . $idArchivo . ');"></i>&nbsp;';
        }
        $html .= '<i class="fas fa-trash-alt" onclick="parent.borrarArchivo(' . $idArchivo . ');"></i>';
        return $html . '</div>';
    }

}
