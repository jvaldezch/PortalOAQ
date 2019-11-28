<?php

/**
 * Administración, validación de archivos M3
 *
 * @author Jaime E. Valdez <jvaldezch@gmail.com>
 */
class OAQ_Data {

    function __construct() {
        
    }

    /**
     *
     * @param integer $patente
     * @param integer $aduana
     * @param integer $pedimento
     * @return array|Exception
     */
    public function obtenerReferencia($patente, $aduana, $pedimento) {
        ini_set("soap.wsdl_cache_enabled", 0);
        $con = new Application_Model_WsWsdl();
        $referencia = array();
        $context = stream_context_create(array(
            "ssl" => array(
                "verify_peer" => false,
                "verify_peer_name" => false,
                "allow_self_signed" => true
            )
        ));
        if ($patente == 3589 && preg_match('/64/', $aduana)) {
            if (($wsdl = $con->getWsdl(3589, 640, "sitawin"))) {
                $soapSitawin = new Zend_Soap_Client($wsdl, array("stream_context" => $context));
            }
            $referencia = $soapSitawin->basicoPedimento($patente, 640, $pedimento);
            if ($referencia === false) {
                $referencia = $soapSitawin->basicoPedimento($patente, 646, $pedimento);
            }
        }
        if ($patente == 3589 && preg_match('/24/', $aduana)) {
            if (($wsdl = $con->getWsdl(3589, 240, "aduanet"))) {
                $soapSitawin = new Zend_Soap_Client($wsdl, array("stream_context" => $context));
            }
            $referencia = $soapSitawin->basicoPedimento($patente, 240, $pedimento);
        }
        if ($patente == 3589 && preg_match('/37/', $aduana)) {
            if (($wsdl = $con->getWsdl(3589, 370, "casa"))) {
                $soapSitawin = new Zend_Soap_Client($wsdl, array("stream_context" => $context));
            }
            $referencia = $soapSitawin->basicoPedimento($patente, 370, $pedimento);
        }
        if ($patente == 3574 && preg_match('/16/', $aduana)) {
            if (($wsdl = $con->getWsdl(3574, 160, "casa"))) {
                $soapSitawin = new Zend_Soap_Client($wsdl, array("stream_context" => $context));
            }
            $referencia = $soapSitawin->basicoPedimento($patente, 160, $pedimento);
        }
        if ($patente == 3933 && preg_match('/43/', $aduana)) {
            if (($wsdl = $con->getWsdl(3933, 430, "aduanet"))) {
                $soapSitawin = new Zend_Soap_Client($wsdl, array("stream_context" => $context));
            }
            $referencia = $soapSitawin->basicoPedimento(3933, 430, $pedimento);
        }
        if ($patente == 3574 && preg_match('/47/', $aduana)) {
            if (($wsdl = $con->getWsdl(3574, 470, "casa"))) {
                $soapSitawin = new Zend_Soap_Client($wsdl, array("stream_context" => $context));
            }
            $referencia = $soapSitawin->basicoPedimentoSecundario($patente, 470, $pedimento);
        }
        if ($patente == 3574 && preg_match('/24/', $aduana)) {
            if (($wsdl = $con->getWsdl(3574, 240, "aduanet"))) {
                $soapSitawin = new Zend_Soap_Client($wsdl, array("stream_context" => $context));
            }
            $referencia = $soapSitawin->basicoPedimentoSecundario($patente, 240, $pedimento);
        }
        if(isset($referencia) && is_array($referencia)) {
            return $referencia;
        } else {
            throw new Exception("No se encontro {$aduana}-{$patente}-{$pedimento}");
        }
    }
    
    /**
     *
     * @param integer $patente
     * @param integer $aduana
     * @param string $referencia
     * @return array|Exception
     */
    public function obtenerPedimento($patente, $aduana, $referencia) {
        ini_set("soap.wsdl_cache_enabled", 0);
        $pedimento = array();
        $con = new Application_Model_WsWsdl();
        $context = stream_context_create(array(
            "ssl" => array(
                "verify_peer" => false,
                "verify_peer_name" => false,
                "allow_self_signed" => true
            )
        ));
        if ($patente == 3589 && preg_match('/64/', $aduana)) {
            if (($wsdl = $con->getWsdl(3589, 640, "aduanet"))) {
                $soapSitawin = new Zend_Soap_Client($wsdl, array("stream_context" => $context));
            }
            $pedimento = $soapSitawin->basicoReferencia($patente, 640, $referencia);
            if ($pedimento === false) {
                $pedimento = $soapSitawin->basicoReferencia($patente, 646, $referencia);
            }
        }
        if ($patente == 3589 && preg_match('/24/', $aduana)) {
            if (($wsdl = $con->getWsdl(3589, 240, "aduanet"))) {
                $soapSitawin = new Zend_Soap_Client($wsdl, array("stream_context" => $context));
            }
            $pedimento = $soapSitawin->basicoReferencia($patente, 240, $referencia);
        }
        if ($patente == 3589 && preg_match('/37/', $aduana)) {
            if (($wsdl = $con->getWsdl(3589, 370, "casa"))) {
                $soapSitawin = new Zend_Soap_Client($wsdl, array("stream_context" => $context));
            }
            $pedimento = $soapSitawin->basicoReferencia($patente, 370, $referencia);
        }
        if ($patente == 3574 && preg_match('/16/', $aduana)) {
            if (($wsdl = $con->getWsdl(3574, 160, "casa"))) {
                $soapSitawin = new Zend_Soap_Client($wsdl, array("stream_context" => $context));
            }
            $pedimento = $soapSitawin->basicoReferencia($patente, 160, $referencia);
        }
        if ($patente == 3933 && preg_match('/43/', $aduana)) {
            if (($wsdl = $con->getWsdl(3933, 430, "aduanet"))) {
                $soapSitawin = new Zend_Soap_Client($wsdl, array("stream_context" => $context));
            }
            $referencia = $soapSitawin->basicoReferencia(3933, 430, $referencia);
        }
        if ($patente == 3574 && preg_match('/47/', $aduana)) {
            if (($wsdl = $con->getWsdl(3574, 470, "casa"))) {
                $soapSitawin = new Zend_Soap_Client($wsdl, array("stream_context" => $context));
            }
            $referencia = $soapSitawin->basicoReferenciaSecundario(3574, 470, $referencia);
        }
        if ($patente == 3574 && preg_match('/24/', $aduana)) {
            if (($wsdl = $con->getWsdl(3574, 240, "aduanet"))) {
                $soapSitawin = new Zend_Soap_Client($wsdl, array("stream_context" => $context));
            }
            $referencia = $soapSitawin->basicoReferenciaSecundario(3574, 470, $referencia);
        }
        if(isset($pedimento) && is_array($pedimento)) {
            return $pedimento;
        } else {
            throw new Exception("No se encontro {$aduana}-{$patente}-{$pedimento}");
        }
    }
    
    /**
     *
     * @param integer $patente
     * @param integer $aduana
     * @param string $referencia
     * @return array|Exception
     */
    public function archivos($patente, $aduana, $referencia) {
        ini_set("soap.wsdl_cache_enabled", 0);
        $archivos = array();
        $context = stream_context_create(array(
            "ssl" => array(
                "verify_peer" => false,
                "verify_peer_name" => false,
                "allow_self_signed" => true
            )
        ));
        if ($patente == 3574 && preg_match('/16/', $aduana)) {
            $soapSitawin = new Zend_Soap_Client("https://proexi.ddns.net:8443/zfsoapcasa.php?wsdl", array("stream_context" => $context));
            $archivos = $soapSitawin->archivos($referencia);
        }
        if (isset($archivos) && is_array($archivos)) {
            return $archivos;
        } else {
            throw new Exception("No se encontro {$aduana}-{$patente}-{$referencia}");
        }
    }

    /**
     *
     * @param integer $patente
     * @param integer $aduana
     * @param string $archivo
     * @return array|Exception
     */
    public function transmitirArchivo($patente, $aduana, $archivo) {
        ini_set("soap.wsdl_cache_enabled", 0);
        $context = stream_context_create(array(
            "ssl" => array(
                "verify_peer" => false,
                "verify_peer_name" => false,
                "allow_self_signed" => true
            )
        ));
        if ($patente == 3574 && preg_match('/16/', $aduana)) {
            try {
                $ws = new Zend_Soap_Client("https://proexi.ddns.net:8443/zfsoapcasa.php?wsdl", array("stream_context" => $context));
                $content = $ws->transmitirM3($archivo);
            } catch (Exception $e) {
                throw new Exception($e->getMessage());
            }
        }
        if (isset($content)) {
            if(!preg_match('/El archivo no existe/i', $content)) {
                return $content;
            } else {
                return null;
            }
        } else {
            throw new Exception("No se encontro {$aduana}-{$patente}-{$archivo}");
        }
    }

}
