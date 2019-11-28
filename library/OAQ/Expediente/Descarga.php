<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class OAQ_Expediente_Descarga {
    
    protected $arr;

    protected function _year($pedimento) {
        $year = "00";
        if (preg_match('/^3/', (string) $pedimento)) {
            $year = "13";
        }
        if (preg_match('/^4/', (string) $pedimento)) {
            $year = "14";
        }
        if (preg_match('/^5/', (string) $pedimento)) {
            $year = "15";
        }
        if (preg_match('/^6/', (string) $pedimento)) {
            $year = "16";
        }
        if (preg_match('/^7/', (string) $pedimento)) {
            $year = "17";
        }
        if (preg_match('/^8/', (string) $pedimento)) {
            $year = "18";
        }
        if (preg_match('/^9/', (string) $pedimento)) {
            $year = "19";
        }
        return $year;
    }
    
    public function prefijosGunderson($tipoArchivo) {
        if (in_array($tipoArchivo, array(1, 23, 24, 442))) { // Pedimiento Original
            return 2;
        } elseif (in_array($tipoArchivo, array(33))) { //Pedimento simplicado
            return 16;
        } elseif (in_array($tipoArchivo, array(3, 34, 170))) { // Facturas de mercancia
            return 6;
        } elseif (in_array($tipoArchivo, array(21, 22))) { // COVE pdf/xml
            return 11;
        } elseif (in_array($tipoArchivo, array(26, 27, 28, 56))) { // Edocuments
            return 12;
        } elseif (in_array($tipoArchivo, array(38))) { // Packing list
            return 7;
        } elseif (in_array($tipoArchivo, array(4))) { // Carta instrucciones
            return 17;
        } elseif (in_array($tipoArchivo, array(35, 14))) { // Certificado de origen
            return 9;
        } elseif (in_array($tipoArchivo, array(2))) { // Cuenta de gastos
            return 1;
        } elseif (in_array($tipoArchivo, array(59))) { // Cuenta de gastos
            return 38;
        } elseif (in_array($tipoArchivo, array(40))) { // Facturas de terceros
            return 22;
        } elseif (in_array($tipoArchivo, array(10))) { // Manifestacion de valor
            return 24;
        } elseif (in_array($tipoArchivo, array(11))) { // Hoja de calculo
            return 25;
        } elseif (in_array($tipoArchivo, array(18))) { // Hoja de calculo
            return 27;
        } elseif (in_array($tipoArchivo, array(55))) { // Carta 3.1.7
            return 26;
        } elseif (in_array($tipoArchivo, array(63))) { // Doda
            return 28;
        } elseif (in_array($tipoArchivo, array(31))) { // Solicitud de anticipo
            return 30;
        } elseif (in_array($tipoArchivo, array(90, 64))) { // Shippers export
            return 31;
        } elseif (in_array($tipoArchivo, array(62))) { // Nota de revision
            return 36;
        } elseif (in_array($tipoArchivo, array(57))) { // Relacion de documentos
            return 37;
        } elseif (in_array($tipoArchivo, array(32))) { // Relacion de documentos
            return 39;
        } else {
            return null;
        }
        return null;
        /*switch ((int) $tipoArchivo) {
            case 1:
                return '2';
            default:
                return null;
        }*/
    }
    
    public function zipFilename($patente, $aduana, $pedimento, $referencia, $rfcCliente) {
        if ($rfcCliente == "GCO980828GY0") {
            return $this->_year($pedimento) . "-" . $aduana . "-" . $patente . "-" . $pedimento . "_" . $referencia . ".zip";
        } else if ($rfcCliente == "ADM111215BS6") {
            return $patente . "_" . $pedimento . "_" . $referencia . ".zip";
        } else {
            return $aduana . "-" . $patente . "-" . $pedimento . "_" . $referencia . ".zip";
        }
    }

    public function filename($patente, $aduana, $pedimento, $nombre, $tipoArchivo, $rfcCliente, $row = null) {
        $prefijos = new Archivo_Model_RepositorioPrefijos();
        if (!($prefijo = $prefijos->obtenerPrefijo($tipoArchivo))) {
            $prefijo = "";
        }
        if ($rfcCliente == "GCO980828GY0") {
            if (($pre = $this->prefijosGunderson($tipoArchivo))) {
                $sf = "";
                if ($pre == 1) {
                    $sf = "-" . $row['folio'] . '-OAQ';
                }
                if ($pre == 21) {
                    $sf = "-" . $row['edocument'];
                }
                $f = pathinfo($nombre);
                return $this->_year($pedimento) . "-" . $aduana . "-" . $patente . "-" . $pedimento . "-" . $pre . $sf . '_' . $f['filename'] . '.' . $f['extension'];
            } else {
                return $this->_year($pedimento) . "-" . $aduana . "-" . $patente . "-" . $pedimento . "_" . $prefijo . str_replace($prefijo, "", $nombre);
            }
        } else if ($rfcCliente == "ADM111215BS6") {
            return $patente . "-" . $pedimento . "_" . $prefijo . $nombre;
        } else {
            return $prefijo . $nombre;
        }
    }

}
