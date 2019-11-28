<?php
defined("APPLICATION_ENV") || define("APPLICATION_ENV", (getenv("APPLICATION_ENV") ? getenv("APPLICATION_ENV") : "production"));
ini_set("soap.wsdl_cache_enabled", 0);
/**
 *  php /var/www/workers/trafico_worker.php
 */
require_once 'mysql.php';
$db = new Db();
$config = new Zend_Config_Ini(realpath(dirname(__FILE__) . "/../application/configs/application.ini"), APPLICATION_ENV);
echo "Iniciando\n";
$gmworker = new GearmanWorker();
$gmworker->addServer('127.0.0.1', 4730);
$gmworker->addFunction("anexo", "anexo_fn");
$gmworker->addFunction("detalle", "detalle_fn");
$gmworker->addFunction("pagados", "pagados_fn");
$gmworker->addFunction("repositorio", "repositorio_fn");
$gmworker->addFunction("automatizacion", "automatizacion_fn");
$gmworker->addFunction("pdfpedimento", "pdfpedimento_fn");
$gmworker->setTimeout(155000);
//$gmworker->setTimeout(5000);

print "Esperando...\n";
while ($gmworker->work()) {
    if ($gmworker->returnCode() != GEARMAN_SUCCESS) {
        echo "return_code: " . $gmworker->returnCode() . "\n";
        break;
    }
}

function pdfpedimento_fn($job) {
    global $db;
    $workload = $job->workload();
    $array = unserialize($workload);
    $context = stream_context_create(array(
        "ssl" => array(
            "verify_peer" => false,
            "verify_peer_name" => false,
            "allow_self_signed" => true
        )
    ));
    if (isset($array) && !empty($array)) {
        $wsData = new Zend_Soap_Client("https://127.0.0.1/webservice/service/data?wsdl", array('compression' => SOAP_COMPRESSION_ACCEPT, "stream_context" => $context));
        $referencia = $wsData->obtenerReferencia($array["patente"], $array["aduana"], $array["pedimento"]);
        if (file_exists($array["filename"])) {
            if (isset($referencia) && $referencia !== false) {
                if (isset($referencia["trafico"]) && $referencia["trafico"] != '') {
                    $path = $db->crearDirectorio($referencia["patente"], $referencia["aduana"], $referencia["trafico"]);
                    if ($path !== false) {
                        if (preg_match('/_SIMP/i', $array["filename"])) {
                            $filename = 'PS_' . $referencia["referencia"] . '_' . $referencia["trafico"] . '.' . strtolower(pathinfo($array["filename"], PATHINFO_EXTENSION));
                        } else {
                            $filename = 'PED_' . $referencia["referencia"] . '_' . $referencia["trafico"] . '.' . strtolower(pathinfo($array["filename"], PATHINFO_EXTENSION));
                        }
                        if (!file_exists($path . DIRECTORY_SEPARATOR . $filename)) {
                            if (copy($array["filename"], $path . DIRECTORY_SEPARATOR . $filename)) {
                                if (file_exists($path . DIRECTORY_SEPARATOR . $filename)) {
                                    if (preg_match('/_SIMP/i', $array["filename"])) {
                                        $added = $db->agregarArchivoRepositorio(15, $referencia["trafico"], $referencia["patente"], $referencia["aduana"], $filename, $path . DIRECTORY_SEPARATOR . $filename, "Auto", null, $referencia["rfcCliente"]);
                                    } else {
                                        $added = $db->agregarArchivoRepositorio(1, $referencia["trafico"], $referencia["patente"], $referencia["aduana"], $filename, $path . DIRECTORY_SEPARATOR . $filename, "Auto", null, $referencia["rfcCliente"]);
                                    }
                                    if ($added === true) {
                                        unlink($array["filename"]);
                                    }
                                }
                            }
                        } else {
                            echo "El archivo ya existe.";
                        }
                    } else {
                        echo "No se pudo crear el directorio.";
                    }
                }
            }
        }
    }
    Zend_Debug::dump($path . DIRECTORY_SEPARATOR . $filename);
}

function automatizacion_fn($job) {
    global $db;
    $workload = $job->workload();
    $array = unserialize($workload);
    $url = "https://127.0.0.1/automatizacion/ws/pedimentos-pagados?rfc={$array["rfc"]}&patente={$array["patente"]}&aduana={$array["aduana"]}&year={$array["year"]}&month={$array["month"]}";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    curl_setopt($ch, CURLOPT_POST, 0);
    curl_setopt($ch, CURLOPT_HTTPGET, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    $output = curl_exec($ch);
    curl_close($ch);
    unset($ch);
    $detalle = "https://127.0.0.1/automatizacion/ws/gearman-detalle?rfc={$array["rfc"]}&patente={$array["patente"]}&aduana={$array["aduana"]}&year={$array["year"]}&month={$array["month"]}";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $detalle);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    curl_setopt($ch, CURLOPT_POST, 0);
    curl_setopt($ch, CURLOPT_HTTPGET, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    $output = curl_exec($ch);
    curl_close($ch);
    unset($ch);
    $anexo = "https://127.0.0.1/automatizacion/ws/gearman-anexo?rfc={$array["rfc"]}&patente={$array["patente"]}&aduana={$array["aduana"]}&year={$array["year"]}&month={$array["month"]}";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $anexo);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    curl_setopt($ch, CURLOPT_POST, 0);
    curl_setopt($ch, CURLOPT_HTTPGET, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    $output = curl_exec($ch);
    curl_close($ch);
    unset($ch);
}

function repositorio_fn($job) {
    global $db;
    $workload = $job->workload();
    $array = unserialize($workload);
    $context = stream_context_create(array(
        "ssl" => array(
            "verify_peer" => false,
            "verify_peer_name" => false,
            "allow_self_signed" => true
        )
    ));
    if (isset($array)) {
        $wsdl = $db->buscarWsdl($array["patente"], $array["aduana"]);
        if (isset($wsdl)) {
            $soap = new Zend_Soap_Client($wsdl, array("stream_context" => $context));
            if (preg_match('/64/', $array["aduana"])) {
                $rfc = $soap->buscarClienteReferencia($array["referencia"], $array["patente"], 640);
                if ($rfc === false) {
                    $rfc = $soap->buscarClienteReferencia($array["referencia"], $array["patente"], 646);
                }
            }
            if (isset($rfc["rfc"]) && !empty($rfc["rfc"])) {
                $updated = $db->actualizarRfcReferencia($array["patente"], $array["referencia"], $rfc["rfc"], $rfc["pedimento"]);
            } else {
                $updated = $db->actualizarRfcReferencia($array["patente"], $array["referencia"], "N/D", null);
            }
        }
    }
}

function anexo_fn($job) {
    global $db;
    $workload = $job->workload();
    $array = unserialize($workload);
    $url = $db->buscarWsdlSistema($array["patente"], $array["aduana"], "casa");
    if (!isset($url)) {
        $url = $db->buscarWsdlSistema($array["patente"], $array["aduana"], "sitawin");
    }
    if (!isset($url)) {
        $url = $db->buscarWsdlSistema($array["patente"], $array["aduana"], "aduanet");
    }
    if (isset($url)) {
        $wsdl = str_replace('?wsdl', '', $url);
    } else {
        echo "No WSDL found";
        return;
    }
    echo "ANEXO: {$array["aduana"]}-{$array["patente"]}-{$array["pedimento"]} REFERENCIA {$array["referencia"]} TIPO OP. {$array["tipoOperacion"]}\n";
    if (($db->verificarAnexo($array["patente"], $array["aduana"], $array["pedimento"])) == false) {
        if (preg_match('/^470/', $array["aduana"])) {
            $result = anexo24ExtendidoPedimentoSecundario($wsdl, $array["patente"], $array["aduana"], $array["pedimento"]);
        } else {
            $result = anexo24ExtendidoPedimento($wsdl, $array["patente"], $array["aduana"], $array["pedimento"]);
            $slam = $db->buscarWsdlSistema($array["patente"], $array["aduana"], "slam");
            if (isset($slam)) {
                $slamWsdl = str_replace('?wsdl', '', $slam);
                if (isset($slamWsdl)) {
                    if ($array["patente"] == 3574 && $array["aduana"] == 240 && $array["tipoOperacion"] == "EXP") {
                        $pre = facturasDeReferencia($slamWsdl, 'facturasDeReferenciaExp', $array["referencia"]);
                    } else {
                        $pre = facturasDeReferencia($slamWsdl, "facturasDeReferencia", $array["referencia"]);                        
                    }
                    if (!isset($pre) || $pre == false) {
                        $pre = facturasDeReferencia($slamWsdl, 'facturasDeReferenciaExp', $array["referencia"]);
                    }
                    $result = arrayToDatabase($pre);
                }
            }
        }
        if ($array["patente"] == 3574 && ($array["aduana"] == 160)) {
            $slam = $db->buscarWsdlSistema($array["patente"], $array["aduana"], 'slam');
            if (isset($slam)) {
                $slamWsdl = str_replace('?wsdl', '', $slam);
                if (isset($slamWsdl)) {
                    $pre = facturasDeReferencia($slamWsdl, "facturasDeReferencia", $array["referencia"]);
                    if (!isset($pre) || $pre == false) {
                        $pre = facturasDeReferencia($slamWsdl, "facturasDeReferenciaExp", $array["referencia"]);
                    }
                }
            }
        }
        if ($array["patente"] == 3574 && ($array["aduana"] == 800)) {
            $slam = $db->buscarWsdlSistema($array["patente"], $array["aduana"], "slam");
            if (isset($slam)) {
                $slamWsdl = str_replace("?wsdl", "", $slam);
                if (isset($slamWsdl)) {
                    $pre = facturasDeReferencia($slamWsdl, "facturasDeReferencia", $array["referencia"]);
                    if (!isset($pre) || $pre == false) {
                        $pre = facturasDeReferencia($slamWsdl, "facturasDeReferenciaExp", $array["referencia"]);
                    }
                    $result = arrayToDatabase($pre);
                    if (!isset($result["Productos"]) || !is_array($result["Productos"])) {
                        $pre = facturasDeReferencia($slamWsdl, "facturasDeReferenciaExp", $array["referencia"]);
                        $result = arrayToDatabase($pre);
                    }
                }
            }
        }
        if ($result === false && $array["patente"] == 3574 && ($array["aduana"] == 240)) {
            if ($array["tipoOperacion"] == "IMP") {
                $pre = facturasDeReferencia("https://162.253.186.242:8443/zfsoapslam", "facturasDeReferencia", $array["referencia"]);
                $result = arrayToDatabase($pre);
            } elseif ($array["tipoOperacion"] == "EXP") {
                $pre = facturasDeReferencia("https://216.251.67.218:8443/zfsoapslam", "facturasDeReferenciaExp", $array["referencia"]);
                $result = arrayToDatabase($pre);
            }
        }
        if ($result === false && preg_match('/^64/', $array["aduana"])) {
            $result = anexo24ExtendidoPedimento($wsdl, $array["patente"], 640, $array["pedimento"]);
            if ($result === false) {
                $result = anexo24ExtendidoPedimento($wsdl, $array["patente"], 646, $array["pedimento"]);
            }
            if ($result === false) {
                $result = anexo24ExtendidoPedimento($wsdl, $array["patente"], 645, $array["pedimento"]);
            }
        }
        if (isset($result) && $result !== false && $result !== null) {
            foreach ($result as $anexoped) {
                $db->agregarAnexo($array["operacion"], $array["tipoOperacion"], $array["patente"], $array["aduana"], $array["pedimento"], $array["referencia"], $anexoped);
                unset($anexoped);
            }
            echo "ANEXO: {$array["aduana"]}-{$array["patente"]}-{$array["pedimento"]} " . count($result) . " ELEMENTS ADDED.\n";
        }
    }
}

function detalle_fn($job) {
    global $db;
    $workload = $job->workload();
    $array = unserialize($workload);
    $context = stream_context_create(array(
        "ssl" => array(
            "verify_peer" => false,
            "verify_peer_name" => false,
            "allow_self_signed" => true
        )
    ));
    $wsdl = $db->buscarWsdlSistema($array["patente"], $array["aduana"], 'casa');
    if (!isset($wsdl)) {
        $wsdl = $db->buscarWsdlSistema($array["patente"], $array["aduana"], 'sitawin');
    }
    if (!isset($wsdl)) {
        $wsdl = $db->buscarWsdlSistema($array["patente"], $array["aduana"], 'aduanet');
    }
    if (isset($wsdl)) {
        try {
            $soap = new Zend_Soap_Client($wsdl, array("compression" => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_DEFLATE, "stream_context" => $context));
        } catch (Exception $e) {
            echo 'Web service is unavilable, please try again later';
        }
        if (($db->verificarDetalle($array["patente"], $array["aduana"], $array["pedimento"])) == false) {
            if ($array["patente"] == 3574 && preg_match('/^47/', $array["aduana"])) {
                $detalle = $soap->detallePedimentoSecundario($array["patente"], $array["aduana"], $array["pedimento"]);
            } else {
                $detalle = $soap->detallePedimento($array["patente"], $array["aduana"], $array["pedimento"]);
            }
            if ($detalle === false && preg_match('/^64/', $array["aduana"])) {
                $detalle = $soap->detallePedimento($array["patente"], 640, $array["pedimento"]);
                if ($detalle === false) {
                    $detalle = $soap->detallePedimento($array["patente"], 646, $array["pedimento"]);
                }
                if ($detalle === false) {
                    $detalle = $soap->detallePedimento($array["patente"], 645, $array["pedimento"]);
                }
            }
            $res = "DETALLE: {$array["aduana"]}-{$array["patente"]}-{$array["pedimento"]}";
            if ($detalle !== false) {
                $db->agregarDetalle($array["operacion"], $array["tipoOperacion"], $array["patente"], $array["aduana"], $array["pedimento"], $array["referencia"], $detalle);
                echo $res . " >> DATA FOUND\n";
            } else {
                echo $res . " >> NO DATA\n";
            }
        } else {            
            echo "OPERTAION ALREADY HAVE DATA\n";
        }
    } else {
        echo "NO WSDL FOUND FOR CUSTOM {$array["aduana"]}\n";
    }
}

function pagados_fn($job) {
    global $db;
    $workload = $job->workload();
    $array = unserialize($workload);
    $cmd = "curl -k -m 600 --connect-timeout 600 --request GET \"https://127.0.0.1/automatizacion/ws/pedimentos-pagados?patente={$array["patente"]}&aduana={$array["aduana"]}&rfc={$array["rfc"]}&fecha={$array["fecha"]}\"";
    echo exec($cmd);
    echo $cmd;
    $cmd = "curl -k -m 600 --connect-timeout 600 --request GET \"https://127.0.0.1/automatizacion/ws/gearman-detalle?rfc={$array["rfc"]}&patente={$array["patente"]}&aduana={$array["aduana"]}&fecha={$array["fecha"]}\"";
    echo exec($cmd);
    echo $cmd;
    $cmd = "curl -k -m 600 --connect-timeout 600 --request GET \"https://127.0.0.1/automatizacion/ws/gearman-anexo?rfc={$array["rfc"]}&patente={$array["patente"]}&aduana={$array["aduana"]}&fecha={$array["fecha"]}\"";
    echo $cmd;
    echo exec($cmd);
}

function makeCurlAction($wsdl, $envelope, $action) {
    $headers = array(
        "Accept-Encoding: gzip,deflate",
        "Content-Type: text/xml;charset=UTF-8",
        "SOAPAction: \"{$wsdl}#{$action}\"",
        "Content-length: " . strlen($envelope),
        "Keep-Alive: 500",
        "Connection: Keep-Alive",
        "User-Agent: Apache-HttpClient/4.1.1 (java 1.5)",
        "Cache-Control: max-age=0"
    );
    $soap_do = curl_init();
    curl_setopt($soap_do, CURLOPT_URL, $wsdl);
    curl_setopt($soap_do, CURLOPT_CONNECTTIMEOUT, 500);
    curl_setopt($soap_do, CURLOPT_TIMEOUT, 500);
    curl_setopt($soap_do, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($soap_do, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($soap_do, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($soap_do, CURLOPT_POSTFIELDS, $envelope);
    curl_setopt($soap_do, CURLOPT_HTTPAUTH, CURLAUTH_NTLM);
    curl_setopt($soap_do, CURLOPT_HTTPHEADER, $headers);
    $result = curl_exec($soap_do);
    if ($result === false) {
        return 'Curl error: ' . curl_error($soap_do);
    }
    return $result;
}

function anexo24ExtendidoPedimento($wsdl, $patente, $aduana, $pedimento) {
    $envelope = '<soapenv:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:zfs="' . $wsdl . '">
<soapenv:Header/>
<soapenv:Body>
  <zfs:anexo24ExtendidoPedimento>
     <patente>' . $patente . '</patente>
     <aduana>' . $aduana . '</aduana>
     <pedimento>' . $pedimento . '</pedimento>
  </zfs:anexo24ExtendidoPedimento>
</soapenv:Body>
</soapenv:Envelope>';
    $result = makeCurlAction($wsdl, $envelope, "anexo24ExtendidoPedimento");
    $array = xmlToArray($result);
    $data = array();
    if (isset($array["Body"]["anexo24ExtendidoPedimentoResponse"]["return"]["item"])) {
        if (count($array["Body"]["anexo24ExtendidoPedimentoResponse"]["return"]["item"]) > 1) {
            foreach ($array["Body"]["anexo24ExtendidoPedimentoResponse"]["return"]["item"] as $k => $v) {
                foreach ($v as $param) {
                    foreach ($param as $value) {
                        if (!isset($data[$k][$value["key"]])) {
                            $data[$k][$value["key"]] = is_array($value["value"]) ? null : $value["value"];
                        }
                    }
                }
            }
        } elseif (count($array["Body"]["anexo24ExtendidoPedimentoResponse"]["return"]["item"]) == 1) {
            foreach ($array["Body"]["anexo24ExtendidoPedimentoResponse"]["return"]["item"]["item"] as $param) {
                if (!isset($data[0][$param["key"]])) {
                    $data[0][$param["key"]] = is_array($param["value"]) ? null : $param["value"];
                }
            }
        }
    } else {
        return false;
    }
    return $data;
}

function anexo24ExtendidoPedimentoSecundario($wsdl, $patente, $aduana, $pedimento) {
    $envelope = '<soapenv:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:zfs="' . $wsdl . '">
<soapenv:Header/>
<soapenv:Body>
  <zfs:anexo24ExtendidoPedimentoSecundario>
     <patente>' . $patente . '</patente>
     <aduana>' . $aduana . '</aduana>
     <pedimento>' . $pedimento . '</pedimento>
  </zfs:anexo24ExtendidoPedimentoSecundario>
</soapenv:Body>
</soapenv:Envelope>';
    $result = makeCurlAction($wsdl, $envelope, "anexo24ExtendidoPedimentoSecundario");
    $array = xmlToArray($result);
    $data = array();
    if (isset($array["Body"]["anexo24ExtendidoPedimentoSecundarioResponse"]["return"]["item"])) {
        if (count($array["Body"]["anexo24ExtendidoPedimentoSecundarioResponse"]["return"]["item"]) > 1) {
            foreach ($array["Body"]["anexo24ExtendidoPedimentoSecundarioResponse"]["return"]["item"] as $k => $v) {
                foreach ($v as $param) {
                    foreach ($param as $value) {
                        if (!isset($data[$k][$value["key"]])) {
                            $data[$k][$value["key"]] = is_array($value["value"]) ? null : $value["value"];
                        }
                    }
                }
            }
        } elseif (count($array["Body"]["anexo24ExtendidoPedimentoSecundarioResponse"]["return"]["item"]) == 1) {
            foreach ($array["Body"]["anexo24ExtendidoPedimentoSecundarioResponse"]["return"]["item"]["item"] as $param) {
                if (!isset($data[0][$param["key"]])) {
                    $data[0][$param["key"]] = is_array($param["value"]) ? null : $param["value"];
                }
            }
        }
    } else {
        return false;
    }
    return $data;
}

function xmlToArray($xml) {
    try {
        $clean = str_replace(array('ns2:', 'ns1:', 'ns3:', 'xs:', 'ns9:', 'ns8:', 'S:', 'wsu:', 'wsse:', 'ns3:', 'wsu:', 'SOAP-ENV:', 'soapenv:', 'env:', 'oxml:', '<![CDATA[', ']]>'), '', $xml);

        if (preg_match('/html/i', $clean)) {
            return null;
        }
        $xmlClean = simplexml_load_string($clean);
        unset($clean);
        return @json_decode(@json_encode($xmlClean), 1);
    } catch (Exception $e) {
        throw new Exception($e->getMessage());
    }
}

function facturasDeReferencia($wsdl, $service, $referencia) {
    $envelope = '<soapenv:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:zfs="' . $wsdl . '">
<soapenv:Header/>
<soapenv:Body>
  <zfs:' . $service . ' soapenv:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/">
     <referencia xsi:type="xsd:string" xs:type="type:string" xmlns:xs="http://www.w3.org/2000/XMLSchema-instance">' . $referencia . '</referencia>
  </zfs:' . $service . '>
</soapenv:Body>
</soapenv:Envelope>';

    $result = makeCurlAction($wsdl, $envelope, $service);
    $array = xmlToArray($result);
    $data = array();
    if (isset($array["Body"][$service . "Response"]["return"]["item"])) {
        if (count($array["Body"][$service . "Response"]["return"]["item"]) > 1) {
            foreach ($array["Body"][$service . "Response"]["return"]["item"] as $k => $v) {
                foreach ($v as $param) {
                    foreach ($param as $value) {
                        if (!isset($data[$k][$value["key"]]) && $value["key"] != 'Productos') {
                            $data[$k][$value["key"]] = is_array($value["value"]) ? null : $value["value"];
                        } elseif (!isset($data[$k][$value["key"]]) && $value["key"] == 'Productos') {
                            if (isset($value["value"]["item"])) {
                                foreach ($value["value"]["item"] as $z => $prod) {
                                    if (isset($prod["item"])) {
                                        foreach ($prod["item"] as $r) {
                                            $data[$k][$value["key"]][$z][$r["key"]] = $r["value"];
                                        }
                                    } else {
                                        foreach ($prod as $r) {
                                            $data[$k][$value["key"]][$z][$r["key"]] = $r["value"];
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        } elseif (count($array["Body"][$service . "Response"]["return"]["item"]) == 1) {
            foreach ($array["Body"][$service . "Response"]["return"]["item"]["item"] as $param) {
                if (!isset($data[0][$param["key"]]) && $param["key"] != 'Productos') {
                    $data[0][$param["key"]] = is_array($param["value"]) ? null : $param["value"];
                } elseif (!isset($data[0][$param["key"]]) && $param["key"] == 'Productos') {
                    if (isset($param["value"]["item"]) && is_array($param["value"]["item"])) {
                        foreach ($param["value"]["item"] as $k => $prod) {
                            if (isset($prod["item"])) {
                                foreach ($prod["item"] as $r) {
                                    if (!is_array($r["value"])) {
                                        $data[0][$param["key"]][$k][$r["key"]] = $r["value"];
                                    }
                                }
                            } else {
                                foreach ($prod as $r) {
                                    if (!is_array($r["value"])) {
                                        $data[0][$param["key"]][$k][$r["key"]] = $r["value"];
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    } else {
        return false;
    }
    return $data;
}

function arrayToDatabase($data) {
    if (is_array($data) && isset($data) && !empty($data)) {
        foreach ($data as $item) {
            if (isset($item["Productos"])) {
                foreach ($item["Productos"] as $p) {
                    if ($item["Divisa"] != 'USD') {
                        $item["ValorFacturaUsd"] = $item["FactorMonExt"] * $item["ValorFactura"];
                    } else {
                        $item["ValorFacturaUsd"] = $item["ValorFactura"];
                    }
                    $p["PrecioUnitario"] = ($p["Total"] / $p["Cantidad"]);
                    $p["ValorMonExt"] = $p["Total"];
                    $temp['numFactura'] = value(array('NumFactura'), $item);
                    $temp['cove'] = value(array('cove', 'Cove'), $item);
                    $temp['ordenFactura'] = value(array('ordenFactura', 'OrdenFactura'), $item);
                    $temp['ordenCaptura'] = value(array('ordenCaptura', 'OrdenCaptura'), $item);
                    $temp['fechaFactura'] = value(array('fechaFactura', 'FechaFactura'), $item, true);
                    $temp['incoterm'] = value(array('incoterm', 'Incoterm'), $item);
                    $temp['valorFacturaUsd'] = value(array('valorFacturaUsd', 'ValorFacturaUsd'), $item);
                    $temp['valorFacturaMonExt'] = value(array('valorFacturaMonExt', 'ValorFacturaMonExt', 'ValorFactura'), $item);
                    $temp['taxId'] = value(array('taxId', 'TaxId'), $item);
                    $temp['cveProveedor'] = value(array('cveProveedor', 'CveProveedor'), $item);
                    $temp['nomProveedor'] = value(array('nomProveedor', 'NomProveedor'), $item);
                    $temp['paisFactura'] = value(array('paisFactura', 'PaisFactura'), $item);
                    $temp['factorMonExt'] = value(array('factorMonExt', 'FactorMonExt'), $item);
                    $temp['divisa'] = value(array('divisa', 'Divisa'), $item);
                    // productos
                    $temp['numParte'] = value(array('numParte', 'NumParte'), $p);
                    $temp['descripcion'] = value(array('descripcion', 'Descripcion'), $p);
                    $temp['fraccion'] = value(array('fraccion', 'Fraccion', 'NumFraccion'), $p);
                    $temp['ordenCaptura'] = value(array('ordenCaptura', 'OrdenCaptura'), $p);
                    $temp['ordenFraccion'] = value(array('ordenFraccion', 'OrdenFraccion', 'OrdenPedimento'), $p);
                    $temp['valorMonExt'] = value(array('valorMonExt', 'ValorMonExt'), $p);
                    $temp['valorAduanaMXN'] = value(array('valorAduanaMXN'), $p);
                    $temp['cantUMC'] = value(array('cantUMC', 'CantUMC', 'Cantidad'), $p);
                    $temp['abrevUMC'] = value(array('abrevUMC'), $p);
                    $temp['cantUMT'] = value(array('cantUMT', 'CantUMT', 'CantidadTarifa'), $p);
                    $temp['umc'] = value(array('umc', 'UMC'), $p);
                    $temp['umt'] = value(array('umt', 'UMT'), $p);
                    $temp['abrevUMT'] = value(array('abrevUMT'), $p);
                    $temp['cantOMA'] = value(array('cantOMA'), $p);
                    $temp['oma'] = value(array('oma'), $p);
                    $temp['umc'] = value(array('umc', 'UMC'), $p);
                    $temp['paisOrigen'] = value(array('paisOrigen', 'PaisOrigen'), $p);
                    $temp['paisVendedor'] = value(array('paisVendedor', 'PaisVendedor'), $p);
                    $temp['tasaAdvalorem'] = value(array('tasaAdvalorem', 'TasaAdvalorem', 'ADV'), $p);
                    $temp['formaPagoAdvalorem'] = value(array('formaPagoAdvalorem'), $p);
                    $temp['umc'] = value(array('umc', 'UMC'), $p);
                    $temp['iva'] = value(array('iva', 'IVA'), $p);
                    $temp['ieps'] = value(array('ieps', 'IEPS'), $p);
                    $temp['isan'] = value(array('isan', 'ISAN'), $p);
                    $temp['tlc'] = value(array('tlc', 'TLC'), $p);
                    $temp['tlcan'] = value(array('tlcan', 'TLCAN'), $p);
                    $temp['tlcue'] = value(array('tlcue', 'TLCUE'), $p);
                    $temp['prosec'] = value(array('prosec', 'PROSEC'), $p);
                    $temp['observacion'] = value(array('observacion', 'Observacion'), $p);
                    $temp['patenteOrig'] = value(array('patenteOrig', 'PatenteOriginal'), $p);
                    $temp['aduanaOrig'] = value(array('aduanaOrig', 'AduanaOriginal'), $p);
                    $temp['pedimentoOrig'] = value(array('pedimentoOrig', 'PedimentoOriginal'), $p);
                    $array[] = $temp;
                    unset($temp);
                }
            } // if
        }
    } else {
        return false;
    }
    if (isset($array)) {
        return $array;
    } else {
        return false;
    }
}

function value($values, $array, $date = null) {
    if (is_array($values)) {
        foreach ($values as $value) {
            if (isset($array[$value])) {
                if (isset($date) && $date === true) {
                    return date('Y-m-d H:i:s', strtotime($array[$value]));
                }
                return $array[$value];
            }
        }
        return null;
    }
}
