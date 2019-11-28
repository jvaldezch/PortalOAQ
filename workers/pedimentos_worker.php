<?php

/**
 *  php /var/www/workers/pedimentos_worker.php
 */
require_once 'mysql.php';
$db = new Db();
ini_set("soap.wsdl_cache_enabled", 0);

echo "Iniciando\n";
$gmworker = new GearmanWorker();
$gmworker->addServer('127.0.0.1', 4730);

$gmworker->addFunction("pedimentows", "pedimentows_fn");
$gmworker->setTimeout(155000);

print "Esperando...\n";
while ($gmworker->work()) {
    if ($gmworker->returnCode() != GEARMAN_SUCCESS) {
        echo "return_code: " . $gmworker->returnCode() . "\n";
        break;
    }
}

function pedimentows_fn($job) {
    global $db;
    $workload = $job->workload();
    $array = unserialize($workload);
    Zend_Debug::dump($array, "SOLICITUD");
    $xml = solicitudPedimentoCompleto($array["rfc"], $array["pass"], $array["patente"], $array["aduana"], $array["pedimento"]);
    $respuesta = vucemPedimento("ConsultarPedimentoCompletoService", $xml);
    $arrayResp = xmlToArray($respuesta);
    if (isset($arrayResp['Body']['consultarPedimentoCompletoRespuesta']['pedimento']['importadorExportador']['fechas'])) {
        $dates = $arrayResp['Body']['consultarPedimentoCompletoRespuesta']['pedimento']['importadorExportador']['fechas'];
        $fechas = array();
        foreach ($dates as $item) {
            $fechas[(int) $item['tipo']['clave']] = $item['fecha'];
        }
        if (isset($fechas[2])) {
            $fechaPago = $fechas[2];
        }
    }
    if (isset($arrayResp['Body']['consultarPedimentoCompletoRespuesta']['pedimento']['encabezado']['rfcAgenteAduanalSocFactura'])) {
        $rfcSociedad = $arrayResp['Body']['consultarPedimentoCompletoRespuesta']['pedimento']['encabezado']['rfcAgenteAduanalSocFactura'];
    }
    if (isset($arrayResp['Body']['consultarPedimentoCompletoRespuesta']['pedimento']['encabezado']['curpApoderadomandatario'])) {
        $curp = $arrayResp['Body']['consultarPedimentoCompletoRespuesta']['pedimento']['encabezado']['curpApoderadomandatario'];
    }
    Zend_Debug::dump($arrayResp['Body']['consultarPedimentoCompletoRespuesta']['pedimento']['encabezado']['rfcAgenteAduanalSocFactura']);
    $folder = '/home/samba-share/expedientes/pedimentos' . DIRECTORY_SEPARATOR . $array["aduana"];
    if (!file_exists($folder)) {
        mkdir($folder, 0777, true);
    }
    if (!isset($respuesta)) {
        return;
    }
    if (isset($arrayResp['Body']['consultarPedimentoCompletoRespuesta']['tieneError'])) {
        if ($arrayResp['Body']['consultarPedimentoCompletoRespuesta']['tieneError'] == 'false') {
            $numeroOperacion = $arrayResp['Body']['consultarPedimentoCompletoRespuesta']['numeroOperacion'];
            $partidas = $arrayResp['Body']['consultarPedimentoCompletoRespuesta']['pedimento']['partidas'];

            $pedimentoXml = $array["aduana"] . '-' . $array["patente"] . '-' . $array["pedimento"] . '_' . $numeroOperacion;
            if (!file_exists($folder . DIRECTORY_SEPARATOR . $pedimentoXml . '.xml')) {
                $fh = fopen($folder . DIRECTORY_SEPARATOR . $pedimentoXml . '.xml', 'w');
                fwrite($fh, $respuesta);
                fclose($fh);
                if (file_exists($folder . DIRECTORY_SEPARATOR . $pedimentoXml . '.xml')) {
                    if (!($db->verificarPedimento($array["patente"], $array["aduana"], $array["pedimento"]))) {
                        $db->nuevoPedimento($array["rfc"], $curp, $array["patente"], $array["aduana"], $array["pedimento"], isset($fechaPago) ? $fechaPago : null, $folder . DIRECTORY_SEPARATOR . $pedimentoXml . '.xml', isset($rfcSociedad) ? $rfcSociedad : null, isset($numeroOperacion) ? $numeroOperacion : null);
                    }
                }
            }
            if (is_array($partidas)) {
                foreach ($partidas as $value) {
                    $partsol = pedimentoPartida($array["rfc"], $array["pass"], $array["patente"], $array["aduana"], $array["pedimento"], $numeroOperacion, $value);
                    $partres = vucemPedimento("ConsultarPartidaService", $partsol);
                    if (isset($partres)) {
                        $partidaXml = $folder . DIRECTORY_SEPARATOR . $pedimentoXml . '_' . $value . '.xml';
                        if (!file_exists($partidaXml)) {
                            $fh = fopen($partidaXml, 'w');
                            fwrite($fh, $partres);
                            fclose($fh);
                        }
                        if (file_exists($partidaXml)) {
                            if (!($db->verificarPartida($array["patente"], $array["aduana"], $array["pedimento"], $value))) {
                                $db->nuevaPartida($array['rfc'], isset($numeroOperacion) ? $numeroOperacion : null, $array["patente"], $array["aduana"], $array["pedimento"], $value, $partidaXml);
                            }
                        }
                    }
                    unset($partidaXml);
                }
            } else {
                $partsol = pedimentoPartida($array["rfc"], $array["pass"], $array["patente"], $array["aduana"], $array["pedimento"], $numeroOperacion, 1);
                $partres = vucemPedimento("ConsultarPartidaService", $partsol);
                if (isset($partres)) {
                    $partidaXml = $folder . DIRECTORY_SEPARATOR . $pedimentoXml . '_1.xml';
                    if (!file_exists($partidaXml)) {
                        $fh = fopen($partidaXml, 'w');
                        fwrite($fh, $partres);
                        fclose($fh);
                        if (file_exists($partidaXml)) {
                            if (!($db->verificarPartida($array["patente"], $array["aduana"], $array["pedimento"], 1))) {
                                $db->nuevaPartida($array['rfc'], isset($numeroOperacion) ? $numeroOperacion : null, $array["patente"], $array["aduana"], $array["pedimento"], 1, $partidaXml);
                            }
                        }
                    }
                    unset($partidaXml);
                }
            }
        } else {
            Zend_Debug::dump($arrayResp);
        }
    } else {
        echo "NO HAY RESPUESTA \n";
    }
}

function solicitudPedimentoCompleto($rfc, $pwd, $patente, $aduana, $pedimento) {
    $xml = "<soapenv:Envelope xmlns:soapenv=\"http://schemas.xmlsoap.org/soap/envelope/\" xmlns:con=\"http://www.ventanillaunica.gob.mx/pedimentos/ws/oxml/consultarpedimentocompleto\" xmlns:com=\"http://www.ventanillaunica.gob.mx/pedimentos/ws/oxml/comunes\">
        <soapenv:Header>
            <wsse:Security soapenv:mustUnderstand=\"1\" xmlns:wsse=\"http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd\">
                <wsse:UsernameToken>
                    <wsse:Username>{$rfc}</wsse:Username>
                    <wsse:Password Type=\"http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordText\">{$pwd}</wsse:Password>
                </wsse:UsernameToken>
        </wsse:Security></soapenv:Header>
        <soapenv:Body>
           <con:consultarPedimentoCompletoPeticion>
              <con:peticion>
                 <com:aduana>{$aduana}</com:aduana>
                 <com:patente>{$patente}</com:patente>
                 <com:pedimento>{$pedimento}</com:pedimento>
              </con:peticion>
           </con:consultarPedimentoCompletoPeticion>
        </soapenv:Body>
     </soapenv:Envelope>";
    return $xml;
}

function pedimentoPartida($rfc, $pwd, $patente, $aduana, $pedimento, $numOperacion, $partida) {
    $xml = "<soapenv:Envelope xmlns:soapenv=\"http://schemas.xmlsoap.org/soap/envelope/\" xmlns:con=\"http://www.ventanillaunica.gob.mx/pedimentos/ws/oxml/consultarpartida\" xmlns:com=\"http://www.ventanillaunica.gob.mx/pedimentos/ws/oxml/comunes\">
        <soapenv:Header>
                     <wsse:Security soapenv:mustUnderstand=\"1\" xmlns:wsse=\"http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd\">
                             <wsse:UsernameToken>
                                     <wsse:Username>{$rfc}</wsse:Username>
                                     <wsse:Password Type=\"http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordText\">{$pwd}</wsse:Password>
                             </wsse:UsernameToken>
                     </wsse:Security>
        </soapenv:Header>
        <soapenv:Body>
           <con:consultarPartidaPeticion>
              <con:peticion>
                 <com:aduana>{$aduana}</com:aduana>
                 <com:patente>{$patente}</com:patente>
                 <com:pedimento>{$pedimento}</com:pedimento>
                 <con:numeroOperacion>{$numOperacion}</con:numeroOperacion>
                 <con:numeroPartida>{$partida}</con:numeroPartida>
              </con:peticion>
           </con:consultarPartidaPeticion>
        </soapenv:Body>
     </soapenv:Envelope>";
    return $xml;
}

function vucemPedimento($servicio, $xml) {
    // ConsultarPedimentoCompletoService
    // ListarPedimentosService        
    // ConsultarPartidaService
    try {
        $headers = array(
            "Content-type: text/xml; charset=UTF-8",
            "Accept: text/xml",
            "Cache-Control: no-cache",
            "Pragma: no-cache",
            "Content-length: " . strlen($xml) . "");
        $url = "https://www.ventanillaunica.gob.mx/ventanilla-ws-pedimentos/" . $servicio;
        $soap = curl_init();
        curl_setopt($soap, CURLOPT_URL, $url);
        curl_setopt($soap, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($soap, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($soap, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($soap, CURLOPT_POST, true);
        curl_setopt($soap, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($soap, CURLOPT_POSTFIELDS, $xml);
        $result = curl_exec($soap);
        curl_close($soap);
        return $result;
    } catch (Exception $e) {
        echo "<b>Exception on " . __METHOD__ . "</b>" . $e->getMessage();
        die();
    }
}

function xmlToArray($xml) {
    try {
//        Zend_Debug::dump($xml);
        $clean = str_replace(array('ns2:', 'ns1:', 'ns3:', 'xs:', 'ns9:', 'ns8:', 'S:', 'wsu:', 'wsse:', 'ns3:', 'wsu:', 'SOAP-ENV:', 'soapenv:', 'env:', 'oxml:', '<![CDATA[', ']]>'), '', $xml);

        if (preg_match('/html/i', $clean)) {
            return null;
        }
        $xmlClean = simplexml_load_string($clean);
        unset($clean);
        return @json_decode(@json_encode($xmlClean), 1);
    } catch (Exception $e) {
        echo "<b>Exception on " . __METHOD__ . "</b>" . $e->getMessage();
        die();
    }
}
