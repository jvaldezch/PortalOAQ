<?php

class Zend_View_Helper_ArchivosIconos extends Zend_View_Helper_Abstract {

    public function archivosIconos($idArchivo, $tipoArchivo, $delete = null) {
        $restricted = array(22, 27);
        $html = '<div id="icon_' . $idArchivo . '" style="font-size:1.4em; color: #2f3b58; float: right; margin: 3px">';
        if (in_array($tipoArchivo, $restricted)) {
            return '';
        } else {
            $html .= '<i class="fas fa-pencil-alt" style="cursor: pointer" onclick="editarArchivo(' . $idArchivo . ');"></i>&nbsp;';
            if (isset($delete)) {
                $html .= '<i class="far fa-trash-alt" style="cursor: pointer" onclick="borrarArchivo(' . $idArchivo . ');"></i>';
            }
        }
        return $html . '</div>';
    }

}
