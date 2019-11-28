<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
ini_set("soap.wsdl_cache_enabled", 0);
defined("APPLICATION_ENV") || define("APPLICATION_ENV", (getenv("APPLICATION_ENV") ? getenv("APPLICATION_ENV") : "production"));
/**
 *  php /var/www/portalprod/workers/edocs_worker.php
 *  su - www-data -c 'php /var/www/portalprod/workers/edocs_worker.php'
 */
require_once "mysql.php";
require realpath(dirname(__FILE__) . "/../library/tcpdf/backgroundtcpdf.php");
$db = new Db();
$config = new Zend_Config_Ini(realpath(dirname(__FILE__) . "/../application/configs/application.ini"), APPLICATION_ENV);
echo "Iniciando\n";
$gmworker = new GearmanWorker();
$gmworker->addServer('127.0.0.1', 4730);

$gmworker->addFunction("edoc_enviaredocs", "enviaredocs_fn");
$gmworker->addFunction("edoc_saveedoc", "saveedoc_fn");
$gmworker->addFunction("edoc_savecove", "savecove_fn");
$gmworker->addFunction("edoc_revisaredocs", "revisaredocs_fn");
$gmworker->addFunction("actualizarsita", "actualizarsita_fn");
$gmworker->setTimeout(155000);

print "Esperando...\n";
while ($gmworker->work()) {
    if ($gmworker->returnCode() != GEARMAN_SUCCESS) {
        echo "return_code: " . $gmworker->returnCode() . "\n";
        break;
    }
}

function saveedoc_fn($job) {
    global $db;
    $workload = $job->workload();
    $array = unserialize($workload);
    if (isset($array["solicitud"]) && isset($array["uuid"])) {
        $retval = null;
        $cmd = 'curl -s -k -X "https://127.0.0.1/automatizacion/vucem/print-edoc?uuid=' . $array["uuid"] . '&solicitud=' . $array["solicitud"] . '&save=true" > /dev/null';
        system($cmd, $retval);
        echo "SAVING EDOC {$array["uuid"]}\n";
        return true;
    }
    return false;
}

function savecove_fn($job) {
    global $db;
    $workload = $job->workload();
    $array = unserialize($workload);
    if (isset($array["solicitud"]) && isset($array["id"])) {
        $retval = null;
        $cmd = 'curl -s -k -X "https://127.0.0.1/automatizacion/vucem/print-cove?id=' . $array["id"] . '&solicitud=' . $array["solicitud"] . '&save=true" > /dev/null';
        system($cmd, $retval);
        echo "SAVING COVE {$array["solicitud"]}\n";
        return true;
    }
    return false;
}

function enviaredocs_fn($job) {
    global $db;
    $workload = $job->workload();
    $array = unserialize($workload);
    if (isset($array["firmante"])) {
        $firmante = $db->obtenerDetalleFirmante($array["firmante"], $array["patente"], $array["aduana"]);
        $noExtension = substr($array["name"], 0, -4);
        if (!file_exists($array["filename"])) {
            return false;
        }
        $base64 = base64_encode(file_get_contents($array["filename"]));
        $hash = sha1_file($array["filename"]);
        $pkeyid = openssl_get_privatekey(base64_decode($firmante['spem']), $firmante['spem_pswd']);
        $cadena = cadenaEdocument($firmante["rfc"], $array["email"], $array["tipoArchivo"], $noExtension, $array["rfc"], $hash);
        $signature = "";
        if (isset($firmante["sha"]) && $firmante["sha"] == 'sha256') {
            openssl_sign($cadena, $signature, $pkeyid, OPENSSL_ALGO_SHA256);
        } else {
            openssl_sign($cadena, $signature, $pkeyid);
        }
        $firma = base64_encode($signature);
        $xml = envioEdocument($firmante["rfc"], $firmante["ws_pswd"], $array["email"], $array["tipoArchivo"], $noExtension, $array["rfc"], $base64, $firmante['cer'], $cadena, $firma);
        if (!file_exists("/tmp/edoctmp")) {
            mkdir("/tmp/edoctmp", 0777, true);
        }
        file_put_contents("/tmp/edoctmp" . DIRECTORY_SEPARATOR . $array["uuid"] . ".xml", $xml);
        if (($db->veriricarEdoc($firmante["rfc"], $array["patente"], $array["aduana"], $cadena, $hash))) {
            return false;
        }
        $added = $db->nuevaSolicitudEdoc($firmante["rfc"], $array["patente"], $array["aduana"], $array["pedimento"], $array["referencia"], $array["uuid"], null, $firmante['cer'], $cadena, $firma, $base64, $array["tipoArchivo"], $array["subTipoArchivo"], $noExtension . '.pdf', $hash, $array["username"], $array["email"], $array["rfc"]);
        if ($added) {
            $db->indexEdoc($added, $firmante["rfc"], $array["patente"], $array["aduana"], $array["pedimento"], $array["referencia"], null, $array["tipoArchivo"], $array["subTipoArchivo"], $noExtension . ".pdf", filesize($array["filename"]), $array["username"]);
            $sent = vucemServicio($xml, $array["urlvucem"]);
            $string = stringInsideTags($sent, "S:Envelope");
            if (empty($string)) {
                $string = stringInsideTags($sent, "env:Envelope");
            }
            if (isset($string[0])) {
                $xmlInden = "<?xml version= \"1.0\"?><S:Envelope xmlns:S=\"http://schemas.xmlsoap.org/soap/envelope/\">" . $string[0] . "</S:Envelope>";
            } else {
                $xmlInden = "<?xml version= \"1.0\"?><S:Envelope xmlns:S=\"http://schemas.xmlsoap.org/soap/envelope/\">" . $string . "</S:Envelope>";
            }
            $sentArray = vucemXmlToArray($xmlInden);
            $db->actualizarEdocRespuesta($added, $xmlInden);
            if (isset($sentArray["Body"]["registroDigitalizarDocumentoServiceResponse"])) {
                $respuesta = $sentArray["Body"]["registroDigitalizarDocumentoServiceResponse"];
                if ($respuesta["respuestaBase"]["tieneError"] == "false") {
                    $update = $db->actualizarEdocSolicitud($added, $respuesta["acuse"]["numeroOperacion"]);
                    $db->indexEdocSolicitud($added, $respuesta["acuse"]["numeroOperacion"]);
                } else {
                    $update = $db->actualizarEdocEstatus($added, 0);
                    $db->indexEdocEstatus($added, 0);
                }
            } else {
                $update = $db->actualizarEdocEstatus($added, 0);
                $db->indexEdocEstatus($added, 0);
            }
        }
    }
}

function enviarvucem_fn($job) {
    global $db;
    $workload = $job->workload();
}

function revisaredocs_fn($job) {
    global $db;
    $workload = $job->workload();
    $array = unserialize($workload);
    if (isset($array["id"])) {
        $firmante = $db->obtenerDetalleFirmante($array["rfc"], $array["patente"], $array["aduana"]);
        $cadenaOriginal = "|{$array["rfc"]}|{$array["solicitud"]}|";
        $pkeyid = openssl_get_privatekey(base64_decode($firmante['spem']), $firmante['spem_pswd']);
        if (isset($firmante["sha"]) && $firmante["sha"] == "sha256") {
            openssl_sign($cadenaOriginal, $signature, $pkeyid, OPENSSL_ALGO_SHA256);
        } else {
            openssl_sign($cadenaOriginal, $signature, $pkeyid);
        }
        $xmlEstatus = estatusEDocument($array["rfc"], $firmante["ws_pswd"], $array["solicitud"], $firmante["cer"], $cadenaOriginal, base64_encode($signature));
        for ($index = 0; $index < 10; $index++) {
            $response = vucemServicio($xmlEstatus, $array["urlvucem"], 15);
            $string = stringInsideTags($response, "S:Envelope");
            if (isset($string[0])) {
                $xmlInden = "<?xml version= \"1.0\"?><S:Envelope xmlns:S=\"http://schemas.xmlsoap.org/soap/envelope/\">" . $string[0] . "</S:Envelope>";
            } else {
                $xmlInden = "<?xml version= \"1.0\"?><S:Envelope xmlns:S=\"http://schemas.xmlsoap.org/soap/envelope/\">" . $string . "</S:Envelope>";
            }
            $sentArray = vucemXmlToArray($xmlInden);
            if (isset($sentArray["Body"]["consultaDigitalizarDocumentoServiceResponse"])) {
                $respuesta = $sentArray["Body"]["consultaDigitalizarDocumentoServiceResponse"];
                if (isset($sentArray["Body"]) && $respuesta["respuestaBase"]["tieneError"] == "false") {
                    if (isset($respuesta["eDocument"]) && isset($respuesta["numeroDeTramite"])) {
                        $db->actualizarRespuestaEdoc($array["id"], 2, $xmlInden, $respuesta["eDocument"], $respuesta["numeroDeTramite"]);
                        $db->indexEdocRespuesta($array["id"], 2, $respuesta["eDocument"]);
                    }
                    if (isset($array["id"]) && isset($array["logo"]) && isset($array["addr1"]) && isset($respuesta["eDocument"])) {
                        $GLOBALS['addr1'] = $array["addr1"];
                        $GLOBALS['addr2'] = $array["addr2"];
                        $filename = printEdoc($array["id"], $array["logo"], $array["directory"], $respuesta["eDocument"]);
                        if ($filename !== false) {                            
                            $arr = $db->crearRepositorioSitawin($array["patente"], $array["aduana"], $array["referencia"], $array["username"]);
                            // GUARDAR ACUSE DEL EDOC
                            if (file_exists($filename)) {
                                if (!($db->checkIfFileExists($array["referencia"], $array["patente"], $array["aduana"], basename($filename)))) {
                                    $db->agregarArchivoRepositorio(27, null, $array["referencia"], $array["patente"], $array["aduana"], basename($filename), $filename, $array["username"], $respuesta["eDocument"], isset($arr["rfcCliente"]) ? $arr["rfcCliente"] : null, isset($arr["pedimento"]) ? $arr["pedimento"] : null);
                                }
                            }
                            // GUARDAR ARCHIVOS ORIGINAL
                            $digitalizado = $array["directory"] . DIRECTORY_SEPARATOR . $array["patente"] . DIRECTORY_SEPARATOR . $array["aduana"] . DIRECTORY_SEPARATOR . $array["referencia"] . DIRECTORY_SEPARATOR . $array["nomArchivo"];
                            if (!file_exists($digitalizado)) {
                                $file = $db->obtenerEdocDigitalizado($array["id"]);
                                file_put_contents($digitalizado, base64_decode($file["archivo"]));
                                $db->agregarArchivoRepositorio($file["tipoDoc"], $file["subTipoArchivo"], $array["referencia"], $array["patente"], $array["aduana"], $array["nomArchivo"], $digitalizado, $array["username"], $respuesta["eDocument"], isset($arr["rfcCliente"]) ? $arr["rfcCliente"] : null, isset($arr["pedimento"]) ? $arr["pedimento"] : null);
                            }
                        }
                        actualizarSitawin($array["patente"], $array["aduana"], $array["referencia"], $respuesta["eDocument"], $array["username"]);
                        return true;
                    }
                } elseif (isset($sentArray["Body"]) && $respuesta["respuestaBase"]["tieneError"] == "true") {
                    if (isset($respuesta["respuestaBase"]["error"]["mensaje"])) {
                        if (preg_match('/procesando/i', $respuesta["respuestaBase"]["error"]["mensaje"])) {
                            $db->addLog('/var/www/workers/edocs_worker.php', "SOLICITUD: " . $array["solicitud"] . " AUN NO TIENE RESPUESTA PROCESANDO", "127.0.0.1", $array["username"]);
                            sleep(20);
                        } elseif (preg_match('/archivo no cumple con las especificaciones/i', $respuesta["respuestaBase"]["error"]["mensaje"])) {
                            $db->actualizarRespuestaEdoc($array["id"], 0, $xmlInden);
                            $db->indexEdocRespuesta($array["id"], 0);
                            return false;
                        } else {
                            return false;
                        }
                    }
                    return false;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        }
    }
}

function actualizarsita_fn($job) {
    global $db;
    $workload = $job->workload();
    $array = unserialize($workload);
    if (isset($array["username"])) {
        $sistema = $db->sistemaPedimentos($array["username"]);
        if (isset($sistema) && $sistema !== false) {
            $sitawin = new Zend_Db_Adapter_Pdo_Mssql(array(
                'host' => $sistema["direccion_ip"],
                'username' => $sistema["usuario"],
                'password' => $sistema["pwd"],
                'dbname' => $sistema["dbname"],
                'port' => $sistema["puerto"],
                'pdoType' => 'dblib'
            ));
            $select = $sitawin->select()
                    ->from('SM3PED', array('NUM_REF'))
                    ->where('NUM_REF = ?', $array["referencia"]);
            $result = $sitawin->fetchRow($select);
            if ($result) {
                $verificar = $sitawin->select()
                        ->from('SM3CASOS')
                        ->where('NUM_REF = ?', $array["referencia"])
                        ->where("TIPCAS = 'ED'")
                        ->where('IDCASO = ?', $array["edoc"]);
                $existe = $sitawin->fetchRow($verificar);
                if (!$existe) {
                    $folio = $sitawin->select()
                            ->from('SM3CASOS')
                            ->where('NUM_REF = ?', $array["referencia"])
                            ->where('SUB = 0')
                            ->where('ORDEN = 0')
                            ->order('FOLIO DESC')
                            ->limit(1);
                    $consecutivo = $sitawin->fetchRow($folio);
                    if ($consecutivo) {
                        $sigFolio = ((int) $consecutivo["FOLIO"] + 1);
                        $data = array(
                            'NUM_REF' => $array["referencia"],
                            'SUB' => 0,
                            'ORDEN' => 0,
                            'TIPCAS' => 'ED',
                            'IDCASO' => $array["edoc"],
                            'IDCASO2' => '',
                            'IDCASO3' => '',
                            'FOLIO' => $sigFolio,
                        );
                        $stmt = $sitawin->insert('SM3CASOS', $data);
                        if($stmt) {
                            $db->indexEdocEnPedimento($array["referencia"], $array["edoc"]);
                        }
                    } else {
                        //
                    }
                } else {
                    //
                }
            } else {
                //
            }
        } else {
            //
        }
    }
}

function actualizarSitawin($patente, $aduana, $referencia, $edocument, $username) {
    $client = new GearmanClient();
    $client->addServer("127.0.0.1", 4730);
    $client->addTaskBackground("actualizarsita", serialize(array("patente" => $patente, "aduana" => $aduana, "referencia" => $referencia, "edoc" => $edocument, "username" => $username)));
    $client->runTasks();
}

function cadenaEdocument($rfcFirmante, $correoElectronico, $idTipoDocumento, $nombreDocumento, $rfcConsulta, $hash) {
    return "|{$rfcFirmante}|{$correoElectronico}|{$idTipoDocumento}|{$nombreDocumento}|{$rfcConsulta}|{$hash}|";
}

function envioEdocument($username, $password, $correoElectronico, $idTipoDocumento, $nombreDocumento, $rfcConsulta, $archivo, $certificado, $cadenaOriginal, $firma) {
    $xml = "<soapenv:Envelope xmlns:soapenv=\"http://schemas.xmlsoap.org/soap/envelope/\" xmlns:dig=\"http://www.ventanillaunica.gob.mx/aga/digitalizar/ws/oxml/DigitalizarDocumento\" xmlns:res=\"http://www.ventanillaunica.gob.mx/common/ws/oxml/respuesta\">
        <soapenv:Header>
             <wsse:Security soapenv:mustUnderstand=\"1\" xmlns:wsse=\"http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd\">
                     <wsse:UsernameToken>
                             <wsse:Username>{$username}</wsse:Username>
                             <wsse:Password Type=\"http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordText\">{$password}</wsse:Password>
                     </wsse:UsernameToken>
             </wsse:Security>
        </soapenv:Header>
        <soapenv:Body>
           <dig:registroDigitalizarDocumentoServiceRequest>
              <dig:correoElectronico>{$correoElectronico}</dig:correoElectronico>
              <dig:documento>
                 <dig:idTipoDocumento>{$idTipoDocumento}</dig:idTipoDocumento>
                 <dig:nombreDocumento>{$nombreDocumento}</dig:nombreDocumento>
                 <dig:rfcConsulta>{$rfcConsulta}</dig:rfcConsulta>
                 <dig:archivo>{$archivo}</dig:archivo>
              </dig:documento>
              <dig:peticionBase>
                 <res:firmaElectronica>
                    <res:certificado>{$certificado}</res:certificado>
                    <res:cadenaOriginal>{$cadenaOriginal}</res:cadenaOriginal>
                    <res:firma>{$firma}</res:firma>
                 </res:firmaElectronica>
              </dig:peticionBase>
           </dig:registroDigitalizarDocumentoServiceRequest>
        </soapenv:Body>
     </soapenv:Envelope>";
    return $xml;
}

function vucemServicio($xml, $url, $timeout = null) {
    try {
        $headers = array(
            "Content-type: text/xml; charset=UTF-8",
            "Accept: text/xml",
            "Cache-Control: no-cache",
            "Pragma: no-cache",
            "Content-length: " . strlen($xml) . "");
        $soap = curl_init();
        curl_setopt($soap, CURLOPT_URL, $url);
        curl_setopt($soap, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($soap, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($soap, CURLOPT_SSL_VERIFYHOST, 1);
        curl_setopt($soap, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($soap, CURLOPT_POST, true);
        curl_setopt($soap, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($soap, CURLOPT_POSTFIELDS, $xml);
        curl_setopt($soap, CURLOPT_TIMEOUT, isset($timeout) ? $timeout : 600);
        $result = curl_exec($soap);
        curl_close($soap);
        return $result;
    } catch (Exception $e) {
        echo "<b>Exception on " . __METHOD__ . "</b>" . $e->getMessage();
        return false;
    }
}

function vucemXmlToArray($xml) {
    try {
        $clean = str_replace(array('ns2:', 'ns3:', 'ns9:', 'ns8:', 'S:', 'wsu:', 'wsse:', 'ns3:', 'wsu:', 'soapenv:', 'env:', 'oxml:', '<![CDATA[', ']]>'), '', $xml);
        if (preg_match('/html/i', $clean)) {
            return null;
        }
        $xmlClean = simplexml_load_string($clean);
        unset($clean);
        return @json_decode(@json_encode($xmlClean), 1);
    } catch (Exception $e) {
        echo "<b>Exception on " . __METHOD__ . "</b>" . $e->getMessage();
        return false;
    }
}

function stringInsideTags($string, $tagname) {
    $pattern = "/<$tagname\b[^>]*>(.*?)<\/$tagname>/is";
    preg_match_all($pattern, $string, $matches);
    if (!empty($matches[1])) {
        return $matches[1];
    }
    return array();
}

function estatusEDocument($username, $password, $numeroOperacion, $certificado, $cadenaOriginal, $firma) {
    return "<soapenv:Envelope xmlns:soapenv=\"http://schemas.xmlsoap.org/soap/envelope/\" xmlns:dig=\"http://www.ventanillaunica.gob.mx/aga/digitalizar/ws/oxml/DigitalizarDocumento\" xmlns:res=\"http://www.ventanillaunica.gob.mx/common/ws/oxml/respuesta\">
   <soapenv:Header>
       <wsse:Security soapenv:mustUnderstand=\"1\" xmlns:wsse=\"http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd\">
               <wsse:UsernameToken>
                       <wsse:Username>{$username}</wsse:Username>
                       <wsse:Password Type=\"http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordText\">{$password}</wsse:Password>
               </wsse:UsernameToken>
       </wsse:Security>
  </soapenv:Header>
   <soapenv:Body>
      <dig:consultaDigitalizarDocumentoServiceRequest>
         <dig:numeroOperacion>{$numeroOperacion}</dig:numeroOperacion>
         <dig:peticionBase>
            <res:firmaElectronica>
               <res:certificado>{$certificado}</res:certificado>
               <res:cadenaOriginal>{$cadenaOriginal}</res:cadenaOriginal>
               <res:firma>{$firma}</res:firma>
            </res:firmaElectronica>
         </dig:peticionBase>
      </dig:consultaDigitalizarDocumentoServiceRequest>
   </soapenv:Body>
</soapenv:Envelope>";
}

function printEdoc($id, $logo, $directory, $edoc) {
    global $db;
    $data = $db->obtenerEdocPorId($id);
    if (!empty($data)) {
        $folder = $directory . DIRECTORY_SEPARATOR . $data["patente"] . DIRECTORY_SEPARATOR . $data["aduana"] . DIRECTORY_SEPARATOR . $data["referencia"];
        if (!file_exists($folder)) {
            if (!mkdir($folder, 0777, true)) {
                return false;
            }
        }
        $acuseEdoc = $folder . DIRECTORY_SEPARATOR . 'EDOC' . $edoc . '.pdf';
        if (!isset($data) || empty($data)) {
            return false;
        }
        $pdf = new MYPDFBACKGROUND(PDF_PAGE_ORIENTATION, PDF_UNIT, 'Letter', true, 'UTF-8', false);
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Jaime E. Valdez');
        $pdf->SetTitle('EDOC');
        $pdf->SetSubject('EDOC');
        $pdf->SetKeywords('EDOCUMENT');
        $pdf->setHeaderData($logo, "35", "COMPROBRANTE E-DOCUMENT", $data["edoc"], array(0, 0, 0), array(150, 150, 150));
        $pdf->setFooterData(array(0, 0, 0), array(0, 0, 0));
        $pdf->setHeaderFont(Array("pdfacourier", '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
        /*if (@file_exists(dirname(__FILE__) . '/lang/es.php')) {
            require_once(dirname(__FILE__) . '/lang/es.php');
            $pdf->setLanguageArray($l);
        }*/
        $pdf->setFontSubsetting(true);
        $pdf->SetFont('pdfacourier', '', 9);
        $pdf->AddPage();
        $thtitle = 'background-color: #e3e3e3; font-weight: bold; border: 1px #999999 solid;';
        $tdhl = 'background-color: none; font-weight: bold; width: 250px; border: 1px #999999 solid;';
        $tdn = 'background-color: none; border: 1px #999999 solid;';
        $date = date('d/m/Y H:i:s');
        $hora = date('h:i a', strtotime($data["enviado"]));
        $fecha = date('d/m/Y', strtotime($data["enviado"]));
        $rfcConsulta = isset($data["rfcConsulta"]) ? $data["rfcConsulta"] : 'OAQ030623UL8';

        $html = <<<EOD
<h3 style="text-align:center; line-height: 12px; margin:0; padding: 0;">ACUSE DIGITALIZACIÓN DE DOCUMENTOS</h3>
<p style="text-align:center; line-height: 12px; margin:0; padding: 0;"><strong>REFERENCIA:</strong> {$data["referencia"]}, <strong>PEDIMENTO:</strong> {$data["pedimento"]}</p>
<p style="text-align:right; line-height: 12px; margin:0; padding: 0;"><strong>FOLIO DE LA SOLICITUD:</strong> {$data["numTramite"]}</p>
<p style="text-align:justify; line-height: 12px; margin:0; padding: 0;"><strong>RFC FIRMANTE:</strong> {$data["rfc"]}</p>
<p style="text-align:justify; line-height: 12px; margin:0; padding: 0;">Siendo las {$hora} del {$fecha} se tiene por recibida y atendida la solicitud de registro de Documentos Digitalizados presentado a través de la ventanilla única (Web Service).</p>
<p><strong>DATOS DEL DOCUMENTO:</strong></p>
<table style="width:750px">
<tr>
<th style="{$thtitle} width:250px">OPERACIÓN</th>
<th style="{$thtitle}">REGISTRO DE DOCUMENTOS DIGITALIZADOS</th>
</tr>
<tr>
<td style="{$tdhl}">NÚMERO DE E-DOCUMENT</td>
<td style="{$tdn}">{$data["edoc"]}</td>
</tr>
<tr>
<td style="{$tdhl}">TIPO DE DOCUMENTO</td>
<td style="{$tdn}">{$data["tipoDoc"]}</td>
</tr>
<tr>
<td style="{$tdhl}">NOMBRE DEL DOCUMENTO</td>
<td style="{$tdn}">{$data["nomArchivo"]}</td>
</tr>
<tr>
<td style="{$tdhl}">RFC DE CONSULTA</td>
<td style="{$tdn}">{$rfcConsulta}</td>
</tr>
<tr>
<td style="{$tdhl}">CADENA ORIGINAL</td>
<td style="{$tdn}">{$data["hash"]}</td>
</tr>
<tr>
<td style="{$tdhl}">SELLO DIGITAL DEL SOLICITANTE (DEL DOCUMENTO)</td>
<td style="{$tdn}">{$data["cadena"]}</td>
</tr>
<tr>
<td style="{$tdhl}">LEYENDA</td>
<td style="{$tdn}">Tiene 90 días a partir de esta fecha para utilizar su documento digitalizado, si en ese tiempo no lo utiliza, será dado de baja del sistema de la Ventanilla Única.</td>
</tr>
</table>
<p><strong>DOCUMENTO IMPRESO CON FECHA DE:</strong> {$date}<p>
EOD;
        $pdf->writeHTML($html, true, false, true, false, '');
        $pdf->Output($acuseEdoc, 'F');
        if (file_exists($acuseEdoc)) {
            return $acuseEdoc;
        }
        return false;
    }
}
