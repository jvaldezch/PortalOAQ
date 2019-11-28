<?php
defined("APPLICATION_ENV") || define("APPLICATION_ENV", (getenv("APPLICATION_ENV") ? getenv("APPLICATION_ENV") : "production"));
/**
 *  php /var/www/workers/edoc_worker.php
 */
require_once 'mysql.php';
$config = new Zend_Config_Ini(realpath(dirname(__FILE__) . "/../application/configs/application.ini"), APPLICATION_ENV);
echo "Iniciando\n";
$gmworker = new GearmanWorker();
$gmworker->addServer('127.0.0.1', 4730);
$gmworker->addFunction("edoc", "edoc_fn");
$gmworker->addFunction("edocres", "edocres_fn");
$gmworker->addFunction("edocreq", "edocreq_fn");
$gmworker->setTimeout(75000);

print "Esperando...\n";
while ($gmworker->work()) {
    if ($gmworker->returnCode() != GEARMAN_SUCCESS) {
        echo "return_code: " . $gmworker->returnCode() . "\n";
        break;
    }
}

function consultarRespuestaEdoc($uuid, $username, $password, $solicitud, $certificado, $cadena, $firma) {
    $db = new Db();
    $context = stream_context_create(array(
        "ssl" => array(
            "verify_peer" => false,
            "verify_peer_name" => false,
            "allow_self_signed" => true
        )
    ));
    $client = new Zend_Soap_Client("https://192.168.200.5/casaws/zfsoapcurl?wsdl", array("stream_context" => $context));
    while (1) {
        sleep(10);
        $result = $client->respuestaEdoc($uuid, $username, $password, $solicitud, $certificado, $cadena, $firma);
        echo "Consultando respuesta \n";
        if (isset($result["edocument"]) && isset($result["numeroDeTramite"]) && isset($result["cadenaOriginal"])) {
            echo "Regreso Edoc \n";
            $db->actualizarEdoc($uuid, $solicitud, 2, html_entity_decode($result["respuesta"]), $result["edocument"], $result["numeroDeTramite"]);
            $cmd = "curl -k -X DELETE -G 'https://127.0.0.1/automatizacion/vucem/print-edoc' -d 'uuid={$uuid}' -d 'solicitud={$solicitud}' -d 'save=true'";
            echo $cmd . "\n";
            shell_exec($cmd);
            break;
        } elseif (isset($result["error"])) {
            $db->actualizarEdoc($uuid, $solicitud, 0, html_entity_decode($result["respuesta"]));
            break;
        }
    }
    echo "Termino consulta de Edoc.\n";
    return false;
}

function edocreq_fn($job) {
    $db = new Db();
    $context = stream_context_create(array(
        "ssl" => array(
            "verify_peer" => false,
            "verify_peer_name" => false,
            "allow_self_signed" => true
        )
    ));
    $workload = $job->workload();
    $array = unserialize($workload);
    echo "Id repositorio: " . $array["id"] . "\n";
    echo "Solicitud archivo: " . $array["file"] . "\n";
    echo "RFC: " . $array["solicitante"] . "\n";
    if (file_exists($array["file"])) {
        $data = htmlentities(file_get_contents($array["file"]));
        $client = new Zend_Soap_Client("https://192.168.200.5/casaws/zfsoapcurl?wsdl", array("stream_context" => $context));
        $filename = basename($array["file"]);
        $uuid = substr($filename, 0, -4);
        $result = $client->runCurl($uuid, $data);
        echo "Solicitud Edoc: " . $result["solicitud"] . "\n";
        if (is_int($result["solicitud"])) {
            $rfc = $db->obtenerDetalleFirmante($array["solicitante"]);
            $added = $db->nuevaSolicitud($array["solicitante"], $array["patente"], $array["aduana"], $array["pedimento"], $array["referencia"], $array["uuid"], null, $rfc['cer'], $array["cadena"], $array["firma"], $array["base64"], $array["tipoArchivo"], $array["subTipoArchivo"], $array["nombreArchivo"], $array["hash"], $array["username"], $array["email"], null);
            echo "Solicitud Id: " . $added . "\n";
            $db->actualizarEdocSolicitud($added, $result["solicitud"]);
            $db->actualizarEdocRes($added, html_entity_decode($result["respuesta"]));
            $cadena = "|{$rfc["rfc"]}|{$result["solicitud"]}|";
            $pkeyid = openssl_get_privatekey(base64_decode($rfc['spem']), $rfc['spem_pswd']);
            $signature = '';
            if (isset($rfc["sha"]) && $rfc["sha"] == 'sha256') {
                openssl_sign($cadena, $signature, $pkeyid, OPENSSL_ALGO_SHA256);
            } else {
                openssl_sign($cadena, $signature, $pkeyid);
            }
            $res = consultarRespuestaEdoc($array["uuid"], $array["solicitante"], $rfc["ws_pswd"], $result["solicitud"], $rfc['cer'], $cadena, base64_encode($signature));
        } else {
            echo "No se obtuvo solicitud.\n";
        }
        return true;
    }
    echo "Tarea finalizada.\n";
    return false;
}

function edoc_fn($job) {
    $workload = $job->workload();
    $array = unserialize($workload);
    $context = stream_context_create(array(
        "ssl" => array(
            "verify_peer" => false,
            "verify_peer_name" => false,
            "allow_self_signed" => true
        )
    ));
    if (file_exists($array["file"])) {
        echo "Id repositorio: " . $array["id"] . "\n";
        echo "Solicitud archivo: " . $array["file"] . "\n";
        $data = htmlentities(file_get_contents($array["file"]));
        $client = new Zend_Soap_Client("https://192.168.200.5/casaws/zfsoapcurl?wsdl", array("stream_context" => $context));
        $filename = basename($array["file"]);
        $uuid = substr($filename, 0, -4);
        $result = $client->runCurl($uuid, $data);
        echo "Solicitud Edoc: " . $result["solicitud"] . "\n";
        if (is_int($result["solicitud"])) {
            return serialize($result);
        }
        return true;
    }
    return false;
}

function edocres_fn($job) {
    $db = new Db();
    $workload = $job->workload();
    $array = unserialize($workload);
    $context = stream_context_create(array(
        "ssl" => array(
            "verify_peer" => false,
            "verify_peer_name" => false,
            "allow_self_signed" => true
        )
    ));
    $client = new Zend_Soap_Client("https://192.168.200.5/casaws/zfsoapcurl?wsdl", array("stream_context" => $context));
    while (1) {
        sleep(10);
        $result = $client->respuestaEdoc($array["uuid"], $array["username"], $array["password"], $array["numOperacion"], $array["certificado"], $array["cadenaOriginal"], $array["firma"]);
        echo "Consultando respuesta \n";
        if (isset($result["edocument"]) && isset($result["numeroDeTramite"]) && isset($result["cadenaOriginal"])) {
            echo "Regreso Edoc \n";
            $db->actualizarEdoc($array["uuid"], $array["solicitud"], 2, html_entity_decode($result["respuesta"]), $result["edocument"], $result["numeroDeTramite"]);
            $cmd = "curl -k -X DELETE -G 'https://127.0.0.1/automatizacion/vucem/print-edoc' -d 'uuid={$array["uuid"]}' -d 'solicitud={$array["solicitud"]}' -d 'save=true'";
            echo $cmd . "\n";
            shell_exec($cmd);
            break;
        } elseif (isset($result["error"])) {
            $db->actualizarEdoc($array["uuid"], $array["solicitud"], 0, html_entity_decode($result["respuesta"]));
            break;
        }
        return true;
    }
    echo "Termino consulta de Edoc.";
    return false;
}
