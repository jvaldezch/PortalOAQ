<?php

defined("APPLICATION_ENV") || define("APPLICATION_ENV", (getenv("APPLICATION_ENV") ? getenv("APPLICATION_ENV") : "development"));
/**
 *  php /var/www/workers/trafico_worker.php
 */
require_once 'mysql.php';
$db = new Db();
ini_set("soap.wsdl_cache_enabled", 0);
$config = new Zend_Config_Ini(realpath(dirname(__FILE__) . "/../application/configs/application.ini"), APPLICATION_ENV);
echo "Iniciando\n";
$gmworker = new GearmanWorker();
$gmworker->addServer("127.0.0.1", 4730);
$gmworker->addFunction("cofidienviar", "cofidienviar_fn");
$gmworker->addFunction("ftpenviar", "ftpenviar_fn");
if (APPLICATION_ENV !== "production") {
    $gmworker->setTimeout(90000);
} else {
    $gmworker->setTimeout(90000);
}
print "Esperando...\n";
while ($gmworker->work()) {
    if ($gmworker->returnCode() != GEARMAN_SUCCESS) {
        echo "return_code: " . $gmworker->returnCode() . "\n";
        break;
    }
}

function cofidienviar_fn($job) {
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
    $soap = new Zend_Soap_Client("https://192.168.200.5/casaws/zfsoapsica?wsdl", array(
        "compression" => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_DEFLATE, 
        "stream_context" => $context)
            );
    $invoice = $soap->folioCdfi($array["cuentaDeGastos"]);
    $tmpDir = "/tmp/cofidi";
    if (!file_exists($tmpDir)) {
        mkdir($tmpDir, 0777, true);
    }
    if (!empty($invoice)) {
        $filename = $tmpDir . DIRECTORY_SEPARATOR . $invoice["filename"];
        file_put_contents($filename, html_entity_decode($invoice["xml"]));
        $xmlArray = satToArray(html_entity_decode($invoice["xml"]));
        if (isset($xmlArray["Complemento"][0]["@attributes"]["UUID"])) {
            $uuid = $xmlArray["Complemento"][0]["@attributes"]["UUID"];
        } elseif (isset($xmlArray["Complemento"]["@attributes"]["UUID"])) {
            $uuid = $xmlArray["Complemento"]["@attributes"]["UUID"];
        } else {
            $uuid = "";
        }
    } else {
        echo "No se encontro XML.\n";
    }
    $pdf = $soap->searchPdf($array["cuentaDeGastos"]);
    if (!empty($pdf)) {
        $pdfFilename = $tmpDir . DIRECTORY_SEPARATOR . $pdf["filename"];
        if (!file_exists($pdfFilename)) {
            file_put_contents($pdfFilename, base64_decode($pdf["content"]));
        }
    }
    $emails = $db->cofidiEmails(0);
    $config = array(
        "host" => "smtp.gmail.com",
        "port" => 587,
        "ssl" => "tls",
        "auth" => "login",
        "username" => "cofidi.envio@gmail.com",
        "password" => "F4ctC0f1d1#",
    );
    $transport = new Zend_Mail_Transport_Smtp("smtp.gmail.com", $config);
    $mail = new Zend_Mail("UTF-8");
    $mail->setFrom("cofidi.envio@gmail.com");
    $mail->addTo("red.cofidi.inbox@ateb.com.mx");
    if (isset($emails) && !empty($emails)) {
        foreach ($emails as $m) {
            $mail->addCc($m["email"]);
        }
    }
    $privacy = "<p class=MsoNormal><b><span style='font-size:7.0pt;font-family:\"Arial\",\"sans-serif\";
        mso-fareast-font-family:\"Times New Roman\";color:#777777'>AVISO:</span></b><span
        style='font-size:7.0pt;font-family:\"Arial\",\"sans-serif\";mso-fareast-font-family:
        \"Times New Roman\";color:#777777'> Este mensaje (incluyendo cualquier archivo
        anexo) es confidencial y personal. En caso de que usted lo reciba por error
        favor de notificar a soporte@oaq.mx regresando este correo electrónico y
        eliminando este mensaje de su sistema. Cualquier uso o difusión en forma
        total o parcial del mensaje así como la distribución o copia de este
        comunicado está estrictamente prohibida. Favor de tomar en cuenta que los
        e-mails son susceptibles de cambio.<br>
        <br>
        <em><b><span style='font-family:\"Arial\",\"sans-serif\"'>DISCLAIMER:</span></b></em><em><span
        style='font-family:\"Arial\",\"sans-serif\"'> This message (including any
        attachments) is confidential and may be privileged. If you have received it
        by mistake please notify soporte@oaq.mx by returning this e-mail and delete
        this message from your system. Any unauthorized use or dissemination of this
        message in whole or in part and distribution or copying of this communication
        is strictly prohibited. Please note that e-mails are susceptible to change.</span></em><o:p></o:p></span></p>";
    $p = ' style="font-family: sans-serif; font-size: 12px; margin:0; padding:0"';
    $th = ' style="font-family: sans-serif; font-size: 12px; margin:0; padding: 2px 5px; border: 1px #777 solid; background: #e5e5e5"';
    $td = ' style="font-family: sans-serif; font-size: 12px; margin:0; padding: 2px 5px; border: 1px #777 solid; text-align: center"';
    $enviado = "<table style=\"border-collapse:collapse;\">";
    $enviado .= "<tr>";
    $enviado .= "<th{$th}>Cuenta de Gastos</th>";
    $enviado .= "<th{$th}>Fecha</th>";
    $enviado .= "<th{$th}>Patente</th>";
    $enviado .= "<th{$th}>Aduana</th>";
    $enviado .= "<th{$th}>Pedimento</th>";
    $enviado .= "<th{$th}>Referencia</th>";
    $enviado .= "<th{$th}>Nombre de Archivo</th>";
    $enviado .= "<th{$th}>UUID</th>";
    $enviado .= "<th{$th}>Usuario</th>";
    $enviado .= "</tr>";
    $enviado .= "<tr>";
    $enviado .= "<td{$td}>" . $array["cuentaDeGastos"] . "</td>";
    $enviado .= "<td{$td}>" . $array["fecha"] . "</td>";
    $enviado .= "<td{$td}>" . $array["patente"] . "</td>";
    $enviado .= "<td{$td}>" . $array["aduana"] . "</td>";
    $enviado .= "<td{$td}>" . $array["pedimento"] . "</td>";
    $enviado .= "<td{$td}>" . $array["referencia"] . "</td>";
    $enviado .= "<td{$td}>";
    if (file_exists($filename)) {
        $enviado .= basename($filename);
    }
    if (file_exists($pdfFilename)) {
        $enviado .= '<br>' . basename($pdfFilename);
    }
    $enviado .= "</td>";
    $enviado .= "<td{$td}>" . $uuid . "</td>";
    $enviado .= "<td{$td}>" . $array["usuario"] . "</td>";
    $enviado .= "</tr>";
    $enviado .= "</table>";
    $mail->setBodyHtml("<p{$p}><strong>{$array["razonSocial"]}</strong></p>
        <p{$p}>{$enviado}</p>
        <br>
        <p{$p}>-- Email generado de forma automática, no responder. --</p>
        {$privacy}");
    $mail->setSubject($array["asunto"]);
    if (file_exists($filename)) {
        $xmlAttach = file_get_contents($filename);
        $attach = $mail->createAttachment($xmlAttach);
        $attach->type = "application/octet-stream";
        $attach->disposition = Zend_Mime::DISPOSITION_INLINE;
        $attach->encoding = Zend_Mime::ENCODING_BASE64;
        $attach->filename = basename($filename);
        unset($attach);
    }
    if (file_exists($pdfFilename)) {
        $zipAttach = file_get_contents($pdfFilename);
        $attach = $mail->createAttachment($zipAttach);
        $attach->type = "application/octet-stream";
        $attach->disposition = Zend_Mime::DISPOSITION_INLINE;
        $attach->encoding = Zend_Mime::ENCODING_BASE64;
        $attach->filename = basename($pdfFilename);
        $mail->setBodyText("-- Email generado de forma automática, no responder. --");
    }
    $sent = true;
    try {
        $mail->send($transport);
        if ($array["debug"] == true) {
            echo "Email enviado de folio: {$array["cuentaDeGastos"]} RFC: {$xmlArray["Receptor"]["@attributes"]["rfc"]}\n";
        }
    } catch (Exception $e) {
        $sent = false;
    }
}

function ftpenviar_fn($job) {
    global $db;
    $workload = $job->workload();
    $array = unserialize($workload);
}

function satToArray($xml) {
    $clean = str_replace(array('ns2:', 'xsi:', 'sat:', 'cfd:', 'cfdi:', 'tfd:', 'xmlns:', 'ns3:', 'ns9:', 'ns8:', 'S:', 'wsu:', 'wsse:', 'ns3:', 'wsu:', 'soapenv:', 'soap:', 'oxml:', '<![CDATA[', ']]>'), '', $xml);
    $xmlClean = simplexml_load_string($clean);
    unset($clean);
    return @json_decode(@json_encode($xmlClean), 1);
}
