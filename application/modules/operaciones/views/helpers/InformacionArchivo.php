<?php

class Zend_View_Helper_InformacionArchivo extends Zend_View_Helper_Abstract {

    public function informacionArchivo($nombreArchivo, $idArchivo) {
        if (preg_match("/M[0-9]{7}.([0-9]{3})/i", $nombreArchivo)) {
            $html = '<div class="rTable"><div class="rTableRow">';
            $mapper = new Automatizacion_Model_ArchivosValidacionPedimentosMapper();
            $arr = $mapper->informacionArchivo($idArchivo);
            if (isset($arr) && !empty($arr)) {
                foreach ($arr as $v) {
                    $html .= '<div class="rTableRow"><div class="rTableCell">' . $v["pedimento"] . '</div><div class="rTableCell">' . $v["pedimentoDesistir"] . '</div><div class="rTableCell">' . $v["cveDoc"] . '</div><div class="rTableCell">' . $v["rfcCliente"] . '</div><div class="rTableCell">' . $v["rfcSociedad"] . '</div></div>';
                }
            }
            return $html . "</div>";
        } elseif (preg_match("/A[0-9]{7}.([0-9]{3})/i", $nombreArchivo)) {
            $html = '<div class="rTable"><div class="rTableRow">';
            $mapper = new Automatizacion_Model_ArchivosValidacionPagosMapper();
            $arr = $mapper->informacionArchivo($idArchivo);
            if (isset($arr) && !empty($arr)) {
                foreach ($arr as $v) {
                    $html .= '<div class="rTableRow"><div class="rTableCell">' . $v["pedimento"] . '</div><div class="rTableCell">' . $v["rfcImportador"] . '</div><div class="rTableCell">' . $v["caja"] . '</div><div class="rTableCell">' . $v["numOperacion"] . '</div><div class="rTableCell">' . $v["firmaBanco"] . '</div></div>';
                }
            }
            return $html . "</div>";
        } elseif (preg_match("/M[0-9]{7}.err/i", $nombreArchivo)) {
            $html = '<div class="rTable"><div class="rTableRow">';
            $mapper = new Automatizacion_Model_ArchivosValidacionFirmasMapper();
            $arr = $mapper->informacionArchivo($idArchivo);
            if (isset($arr) && !empty($arr)) {
                foreach ($arr as $v) {
                    $html .= '<div class="rTableRow"><div class="rTableCell">' . $v["pedimento"] . '</div><div class="rTableCell">' . $v["firma"] . '</div></div>';
                }
            }
            return $html . "</div>";
        }
        return "";
    }

}
