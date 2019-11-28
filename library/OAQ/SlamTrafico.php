<?php

/**
 * Clase para la conectividad con la base de datos de SLAM y el web service que provee de datos al dashboard
 *
 * @author Jaime E. Valdez <jvaldezch@gmail.com>
 */
class OAQ_SlamTrafico {

    protected $_wsdl;
    protected $_soapClient;
    protected $_config;

    function __construct($wsdl) {
        $context = stream_context_create(array(
            "ssl" => array(
                "verify_peer" => false,
                "verify_peer_name" => false,
                "allow_self_signed" => true
            )
        ));
        $this->_config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini', APPLICATION_ENV);
        $this->_soapClient = new Zend_Soap_Client($wsdl, array("stream_context" => $context));
        $this->_wsdl = $wsdl;
    }

    /**
     * 
     * @param String $fechaIni
     * @param String $fechaFin
     * @return array
     */
    public function getSlamReferencesByDate($fechaIni, $fechaFin, $aduana) {

        try {
            $param = array(
                'fechaIni' => $fechaIni,
                'fechaFin' => $fechaFin,
            );
            if ($aduana != '240' && $aduana != '430') {
                $param['rfc'] = 'OAQ030623UL8';
            }
            $result = $this->_soapClient->Periodo($param);

            $array = json_decode(json_encode((array) simplexml_load_string($result->PeriodoResult)), 1);

            return $array;
        } catch (Exception $e) {
            Zend_Debug::dump($this->_wsdl);
            echo "<b>Exception found at " . __METHOD__ . ":</b> " . $e->getMessage();
            die();
        }
    }

    /**
     * 
     * @param String $reference
     * @param int $$aduana
     * @return array
     */
    public function getSlamReferenceDetail($referencia, $aduana) {
        try {
            $param = array(
                'referencia' => $referencia,
                'Operacion' => 'IMPO',
            );
            if ($aduana != '240') {
                $param['rfc'] = 'OAQ030623UL8';
            }
            $result = $this->_soapClient->xmlReferencia($param);

            if ($result) {
                return json_decode(json_encode((array) simplexml_load_string($result->xmlReferenciaResult)), 1);
            }
        } catch (Exception $e) {
            echo $e->getMessage();
            die();
        }
    }

    public function getStatusChange($reference, $aduana, $wsdl) {
        try {
            $context = stream_context_create(array(
                "ssl" => array(
                    "verify_peer" => false,
                    "verify_peer_name" => false,
                    "allow_self_signed" => true
                )
            ));
            $soapClient = new Zend_Soap_Client($wsdl, array("stream_context" => $context));
            $param = array(
                'referencia' => $reference,
                'Operacion' => 'IMPO',
            );
            if ($aduana != '240') {
                $param['rfc'] = 'OAQ030623UL8';
            }
            $result = $soapClient->xmlReferencia($param);
            if ($result) {
                $info = json_decode(json_encode((array) simplexml_load_string($result->xmlReferenciaResult)), 1);
                $fecha = $this->fixDate($info['BODEGA']['FechaEntrada'], $aduana);
                $data = array(
                    'estatus' => $info['TRAFICO']['Estatus'],
                    'bl_guia' => (is_array($info['BODEGA']['BillOfLading'])) ? null : $info['BODEGA']['BillOfLading'],
                    'seccion' => (is_array($info['BODEGA']['Seccion'])) ? null : $info['BODEGA']['Seccion'],
                    'fecha_entrada' => (is_array($info['BODEGA']['FechaEntrada'])) ? null : $fecha,
                );
                unset($info);
                unset($soapClient);
                return $data;
            }
            unset($soapClient);
            return NULL;
        } catch (Exception $e) {
            echo $e->getMessage();
            die();
        }
    }

    public function fixDate($date, $aduana) {
        if ($aduana == '240') {
            $remove = str_replace(array(' PM', ' AM'), '', $date);
            $datetime = explode(' ', $remove);
            list($month, $day, $year) = explode('/', $datetime[0]);
            return $year . '-' . str_pad($month, 2, 0, STR_PAD_LEFT) . '-' . str_pad($day, 2, 0, STR_PAD_LEFT) . ' ' . $datetime[1];
        } else if ($aduana == '470' || $aduana == '160') {
            $remove = str_replace(array(' p.m.', ' a.m.'), '', $date);
            $datetime = explode(' ', $remove);
            list($day, $month, $year) = explode('/', $datetime[0]);
            return $year . '-' . str_pad($month, 2, 0, STR_PAD_LEFT) . '-' . str_pad($day, 2, 0, STR_PAD_LEFT) . ' ' . $datetime[1];
        }
    }

}