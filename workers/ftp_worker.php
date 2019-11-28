<?php
defined("APPLICATION_ENV") || define("APPLICATION_ENV", (getenv("APPLICATION_ENV") ? getenv("APPLICATION_ENV") : "production"));
/**
 *  php /var/www/workers/ftp_worker.php
 *  su - www-data -c 'php /var/www/workers/ftp_worker.php'
 */
require_once "mysql.php";
$config = new Zend_Config_Ini(realpath(dirname(__FILE__) . "/../application/configs/application.ini"), APPLICATION_ENV);
echo "Starting\n";
$gmworker = new GearmanWorker();
$gmworker->addServer('127.0.0.1', 4730);
$gmworker->addFunction("ftp", "ftp_fn");
$gmworker->addFunction("enviar", "enviar_fn");
$gmworker->addFunction("validador", "validador_fn");
$gmworker->addFunction("validadorplus", "validadorplus_fn");
$gmworker->addFunction("validadorpago", "validadorpago_fn");
$gmworker->addFunction("validadorpagoplus", "validadorpagoplus_fn");
$gmworker->addFunction("revisarvalidacion", "revisarvalidacion_fn");
$gmworker->setTimeout(15000);

print "Waiting for job...\n";
while ($gmworker->work()) {
    if ($gmworker->returnCode() != GEARMAN_SUCCESS) {
        echo "return_code: " . $gmworker->returnCode() . "\n";
        break;
    }
}

function enviar_fn($job) {
    $workload = $job->workload();
    $array = unserialize($workload);
    $context = stream_context_create(array(
        "ssl" => array(
            "verify_peer" => false,
            "verify_peer_name" => false,
            "allow_self_signed" => true
        )
    ));
    $db = new Db();
    $search = $db->nombreArchivo($array["idRepo"]);
    $result = '';
    if ($search !== false) {
        $result .= "Encontrado: {$search["ubicacion"]} \n";
        if (file_exists(utf8_encode($search["ubicacion"]))) {
            $conn = $db->ftp($array["idFtp"]);
            $idEnvio = $db->initJob($array["idRepo"], $search["referencia"], $search["nom_archivo"], $conn["url"], isset($array["rfc"]) ? $array["rfc"] : null, filesize(utf8_encode($search["ubicacion"])));
            if (!isset($array["pedimento"])) {
                $wsData = new Zend_Soap_Client("https://127.0.0.1/webservice/service/data?wsdl", array("compression" => SOAP_COMPRESSION_ACCEPT, "stream_context" => $context));
                try {
                    $pedimento = $wsData->obtenerPedimento($array["patente"], $array["aduana"], $array["referencia"]);
                } catch (Exception $e) {
                    return;
                }
                if ($pedimento !== false) {
                    $array["pedimento"] = $pedimento["pedimento"];
                }
            }
            echo "RFC : {$array["rfc"]}, patente: {$array["patente"]}, aduana {$array["aduana"]}, pedimento {$array["pedimento"]} referencia {$array["referencia"]}\n";
            if ($idEnvio) {
                $job->sendData(serialize(array('id' => $idEnvio, 'estatus' => 'INICIANDO', 'task' => 'envio')));
                $conn_id = ftp_connect($conn["url"], $conn["port"]);
                $login_result = ftp_login($conn_id, $conn["user"], $conn["password"]);
                if ((!$conn_id) || (!$login_result)) {
                    $db->updateJob($idEnvio, "NO SE PUDO CONECTAR AL FTP");
                    return;
                }
                ftp_chdir($conn_id, $conn["remoteFolder"]);
                if ($conn["remoteFolder"] != '/') {
                    $remoteDir = $conn["remoteFolder"] . DIRECTORY_SEPARATOR . $array["pedimento"] . "_" . $search["referencia"];
                } else {
                    $remoteDir = DIRECTORY_SEPARATOR . $array["pedimento"] . "_" . $search["referencia"];
                }
                if (!@ftp_chdir($conn_id, $remoteDir)) {
                    ftp_mkdir($conn_id, $remoteDir);
                }
                ftp_chdir($conn_id, $remoteDir);
                if (!preg_match('/.pdf$/i', utf8_encode($search["nom_archivo"])) && !preg_match('/.xml$/i', utf8_encode($search["nom_archivo"])) && !preg_match('/.xls$/i', utf8_encode($search["nom_archivo"])) && !preg_match('/.xlsx$/i', utf8_encode($search["nom_archivo"])) && !preg_match('/.doc$/i', utf8_encode($search["nom_archivo"])) && !preg_match('/.docx$/i', utf8_encode($search["nom_archivo"]))) {
                    $remoteFile = utf8_encode($search["nom_archivo"]) . '.pdf';
                } else {
                    $remoteFile = utf8_encode($search["nom_archivo"]);
                }
                $uploaded = ftp_put($conn_id, utf8_encode(basename($search["ubicacion"])), utf8_encode($search["ubicacion"]), FTP_BINARY);
                echo "FTP Upload: " . $remoteDir . DIRECTORY_SEPARATOR . basename($search["ubicacion"]) . "\n";
                echo "Size: " . filesize($search["ubicacion"]) . "\n\n";
                if ($uploaded) {
                    $db->updateJob($idEnvio, "ENVIADO");
                    $db->updateRepo($array["idRepo"]);
                }
                ftp_close($conn_id);
            }
        } else {
            echo "No se encontro archivo " . utf8_encode($search["ubicacion"]);
            $db->missing($array["idRepo"]);
        }
    }
    return $result;
}

function ftp_fn($job) {
    $workload = $job->workload();
    $array = unserialize($workload);
    $result = "Id Repositorio: {$array["idRepo"]}\n";
    $db = new Db();
    $search = $db->nombreArchivo($array["idRepo"]);
    if ($search !== false) {
        $result .= "Encontrado: {$search["ubicacion"]} \n";
        if (file_exists($search["ubicacion"])) {
            $conn = $db->ftp($array["idFtp"]);
            $idEnvio = $db->initJob($array["idRepo"], $search["referencia"], $search["nom_archivo"], $conn["url"]);
            if ($idEnvio) {
                $job->sendData(serialize(array('id' => $idEnvio, 'estatus' => 'INICIANDO', 'task' => 'envio')));
                $conn_id = ftp_connect($conn["url"], $conn["port"]);
                $login_result = ftp_login($conn_id, $conn["user"], $conn["password"]);
                if ((!$conn_id) || (!$login_result)) {
                    $job->sendData(serialize(array('id' => $idEnvio, 'estatus' => 'NO SE PUEDE CONECTAR FTP', 'task' => 'envio')));
                    return;
                }
                ftp_chdir($conn_id, $conn["remoteFolder"]);
                if (!@ftp_chdir($conn_id, $search["referencia"])) {
                    ftp_mkdir($conn_id, $search["referencia"]);
                }
                ftp_chdir($conn_id, $conn["remoteFolder"] . DIRECTORY_SEPARATOR . $search["referencia"]);
                if (!preg_match('/.pdf$/i', $search["nom_archivo"]) && !preg_match('/.xml$/i', $search["nom_archivo"])) {
                    $remoteFile = $search["nom_archivo"] . '.pdf';
                } else {
                    $remoteFile = $search["nom_archivo"];
                }
                $uploaded = ftp_put($conn_id, $remoteFile, $search["ubicacion"], FTP_BINARY);
                if ($uploaded) {
                    $job->sendData(serialize(array('id' => $idEnvio, 'estatus' => 'ENVIADO', 'task' => 'envio')));
                    $job->sendData(serialize(array('id' => $array["idRepo"], 'task' => 'repo')));
                }
                ftp_close($conn_id);
            }
        }
    }
    sleep(3);
    return $result;
}

function validadorpago_fn($job) {
    require_once '/var/www/portalprod/library/OAQ/ArchivosM3.php';
    $functions = new OAQ_ArchivosM3();
    $workload = $job->workload();
    $array = unserialize($workload);
    $db = new Db();
    if (isset($array["id"])) {
        $file = $db->archivoValidacionEnviado($array["id"]);
        if (isset($file) && isset($file["patente"]) && isset($file["aduana"])) {
            $directorio = $db->directorioValidador($file["patente"], $file["aduana"]);
            if (isset($directorio)) {
                $server = $db->validador($file["patente"], $file["aduana"]);
                if (!($conn_id = connectFtp($server))) {
                    die("No server connection.");
                }
                if (ftp_directory_exists($conn_id, $server["carpeta"]) !== false) {
                    ftp_chdir($conn_id, $server["carpeta"]);
                }
                $ext = pathinfo($file["nomArchivo"], PATHINFO_EXTENSION);
                $a = "a" . trim(substr($file["nomArchivo"], 1, 7)) . "." . $ext;
                $i = 0;
                $pago = false;
                $error = false;
                while (1) {
                    $buff = ftp_nlist($conn_id, '.');
                    if (!empty($buff)) {
                        $pagos = preg_grep('/' . $a . '/i', $buff);
                        foreach ($pagos as $item) {
                            if (!file_exists($directorio . DIRECTORY_SEPARATOR . strtolower($item))) {
                                ftp_get($conn_id, $directorio . DIRECTORY_SEPARATOR . strtolower($item), $item, FTP_BINARY);
                                echo "Se descargo " . $item . "\n";
                                if (file_exists($directorio . DIRECTORY_SEPARATOR . strtolower($item))) {
                                    $data = $functions->analizarArchivo(strtolower($item), file_get_contents($directorio . DIRECTORY_SEPARATOR . strtolower($item)));
                                }
                            } else {
                                echo "El archivo ya existe " . $directorio . DIRECTORY_SEPARATOR . strtolower($item) . "\n";
                                $data = $functions->analizarArchivo(strtolower($item), file_get_contents($directorio . DIRECTORY_SEPARATOR . strtolower($item)));
                                $pago = true;
                            }
                            if (isset($data)) {
                                if (!empty($data)) {
                                    if (isset($data[0]["patente"]) && isset($data[0]["aduana"])) {
                                        if (isset($data[0]["aduana"]) == '64') {
                                            $data[0]["aduana"] = '640';
                                        }
                                        if (!($id = $db->verificarArchivoPago(basename($item), sha1_file($directorio . DIRECTORY_SEPARATOR . strtolower($item))))) {
                                            $id = $db->agregarArchivoPago($data[0]["patente"], $data[0]["aduana"], strtolower($item), file_get_contents($directorio . DIRECTORY_SEPARATOR . strtolower($item)), sha1_file($directorio . DIRECTORY_SEPARATOR . strtolower($item)));
                                            foreach ($data as $item) {
                                                if (!($db->verificarPago($id, $item["patente"], $item["aduana"], $item["pedimento"]))) {
                                                    $db->agregarPago($id, $item);
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                    if ($pago == true) {
                        echo "Pago completado\n";
                        ftp_close($conn_id);
                        return true;
                    }
                    if ($i == 6) {
                        echo "Tiempo de espera superado.\n";
                        ftp_close($conn_id);
                        return false;
                    }
                    $i++;
                    sleep(30);
                } /** while * */
            }
        }
    }
}

function revisarvalidacion_fn($job) {
    require_once '/var/www/portalprod/library/OAQ/ArchivosM3.php';
    $workload = $job->workload();
    $array = unserialize($workload);
    $db = new Db();
    if (isset($array["id"])) {
        $con = $db->validadorLog($array["id"]);
        if (isset($con) && $con !== false) {
            $dir = $db->directorioValidador($con["patente"], $con["aduana"]);
            $ext = pathinfo($con["archivo"], PATHINFO_EXTENSION);
            $m = "M" . trim(substr($con["archivo"], 1, 7)) . ".err";
            $k = "k" . trim(substr($con["archivo"], 1, 7)) . "." . $ext;
            $server = $db->validador($con["patente"], $con["aduana"]);
            if (!($conn_id = connectFtp($server))) {
                $db->validadorActividad($con["patente"], $con["aduana"], $con["pedimento"], $con["referencia"], "SIN CONEXIÓN AL VALIDADOR.");
                ftp_close($conn_id);
                return false;
            }
            if (($db->enviado($array["id"])) == 1) {
                ftp_close($conn_id);
                return true;
            }
            if (($up = ftp_put($conn_id, $con["archivo"], $dir . DIRECTORY_SEPARATOR . $con["archivo"], FTP_BINARY))) {
                $db->validadorActividad($con["patente"], $con["aduana"], $con["pedimento"], $con["referencia"], "VOLVIO A SUBIR {$con["archivo"]}");
                $db->validadorLogEnviado($array["id"]);
            }
            for ($i = 0; $i < 25; $i++) {
                if (!isset($respuesta) && !isset($validado)) {
                    $db->validadorActividad($con["patente"], $con["aduana"], $con["pedimento"], $con["referencia"], "REV. INTENTO " . ($i + 1) . " DE 25");
                    sleep(45);
                } elseif (isset($respuesta) && !isset($validado)) {
                    $db->validadorActividad($con["patente"], $con["aduana"], $con["pedimento"], $con["referencia"], "REV. INTENTO " . ($i + 1) . " DE 25");
                    sleep(45);
                }
                if (!isset($respuesta)) {
                    $res = ftp_size($conn_id, $k);
                    if ($res > 0) {
                        $db->validadorActividad($con["patente"], $con["aduana"], $con["pedimento"], $con["referencia"], "SE ENCONTRO RESPUESTA {$k}");
                        if (file_exists($dir . DIRECTORY_SEPARATOR . $k)) {
                            $contenido = file_get_contents($dir . DIRECTORY_SEPARATOR . $k);
                        } else {
                            if (downloadFile($array, $con, $conn_id, $server["carpeta"], $dir, $k) == true) {
                                $db->validadorActividad($con["patente"], $con["aduana"], $con["pedimento"], $con["referencia"], "DESCARGO RESPUESTA {$k}");
                                $contenido = file_get_contents($dir . DIRECTORY_SEPARATOR . $k);
                            }
                        }
                        if (!($db->verificarValidadorLog($con["patente"], $con["aduana"], $con["pedimento"], $k))) {
                            $db->agregarValidadorLog($con["patente"], $con["aduana"], $con["pedimento"], $con["referencia"], $k, base64_encode($contenido), $array["username"]);
                        }
                        if (strpos($contenido, "ERRORES") !== false) {
                            $db->validadorActividad($con["patente"], $con["aduana"], $con["pedimento"], $con["referencia"], "TIENE ERROR {$con["archivo"]}");
                            $db->validadorLogError($array["id"]);
                            $error = true;
                        }
                        $respuesta = true;
                    }
                }
                if (isset($respuesta) && !isset($error)) {
                    $val = ftp_size($conn_id, $m);
                    if ($val > 0) {
                        if (file_exists($dir . DIRECTORY_SEPARATOR . strtolower($m))) {
                            $firma = file_get_contents($dir . DIRECTORY_SEPARATOR . strtolower($m));
                        } else {
                            if (downloadFile($array, $con, $conn_id, $server["carpeta"], $dir, $m) == true) {
                                $db->validadorActividad($con["patente"], $con["aduana"], $con["pedimento"], $con["referencia"], "DESCARGO FIRMA " . strtolower($m));
                                $firma = file_get_contents($dir . DIRECTORY_SEPARATOR . strtolower($m));
                            }
                        }
                        if (!($db->verificarValidadorLog($con["patente"], $con["aduana"], $con["pedimento"], strtolower($m)))) {
                            $db->validadorActividad($con["patente"], $con["aduana"], $con["pedimento"], $con["referencia"], "OBTUVO FIRMA " . strtolower($m));
                            $db->agregarValidadorLog($con["patente"], $con["aduana"], $con["pedimento"], $con["referencia"], strtolower($m), base64_encode($firma), $array["username"]);
                            $db->validadorLogValidado($array["id"]);
                        }
                        $validado = true;
                    }
                }
                if (isset($respuesta) && $respuesta == true && isset($validado) && $validado == true) {
                    $db->validadorActividad($con["patente"], $con["aduana"], $con["pedimento"], $con["referencia"], "SE COMPLETO VALIDACION " . $con["archivo"]);
                    ftp_close($conn_id);
                    return true;
                }
                if (isset($respuesta) && $respuesta == true && isset($error) && $error == true) {
                    $db->validadorActividad($con["patente"], $con["aduana"], $con["pedimento"], $con["referencia"], "SE DETECTO ERROR DE VALIDACION");
                    ftp_close($conn_id);
                    return false;
                }
            } // for
            $db->validadorLogAgotado($array["id"]);
            $db->validadorActividad($con["patente"], $con["aduana"], $con["pedimento"], $con["referencia"], "SE AGOTO EL TIEMPO DE ESPERA REV.");
            ftp_close($conn_id);
            return true;
        }
    }
}

function validadorplus_fn($job) {
    require_once '/var/www/portalprod/library/OAQ/ArchivosM3.php';
    $workload = $job->workload();
    $array = unserialize($workload);
    $db = new Db();
    if (isset($array["id"])) {
        $con = $db->validadorLog($array["id"]);
        if (isset($con) && $con !== false) {
            $dir = $db->directorioValidador($con["patente"], $con["aduana"]);
            $ext = pathinfo($con["archivo"], PATHINFO_EXTENSION);
            $m = "M" . trim(substr($con["archivo"], 1, 7)) . ".err";
            $k = "k" . trim(substr($con["archivo"], 1, 7)) . "." . $ext;
            $server = $db->validador($con["patente"], $con["aduana"]);
            if (!($conn_id = connectFtp($server))) {
                $db->validadorActividad($con["patente"], $con["aduana"], $con["pedimento"], $con["referencia"], "SIN CONEXIÓN AL VALIDADOR.");
                ftp_close($conn_id);
                return false;
            }
            $db->validadorActividad($con["patente"], $con["aduana"], $con["pedimento"], $con["referencia"], "SE PREPARA PARA ENVIO {$con["archivo"]}");
            if (($db->enviado($array["id"])) == 1) {
                ftp_close($conn_id);
                return true;
            }
            if (file_exists($dir . DIRECTORY_SEPARATOR . $con["archivo"])) {
                if (!($db->enviadoValidadorLog($array['id']))) {
                    if (($up = ftp_put($conn_id, $con["archivo"], $dir . DIRECTORY_SEPARATOR . $con["archivo"], FTP_BINARY))) {
                        $db->validadorActividad($con["patente"], $con["aduana"], $con["pedimento"], $con["referencia"], "SUBIO {$con["archivo"]}");
                        $db->validadorLogEnviado($array["id"]);
                        $uploaded = true;
                    }
                    if (!$up) {
                        $db->validadorActividad($con["patente"], $con["aduana"], $con["pedimento"], $con["referencia"], "NO CONEXIÓN CON VALIDADOR");
                        return false;
                    }
                } else {
                    $uploaded = true;
                }
                if ($uploaded == true) {
                    for ($i = 0; $i < 30; $i++) {
                        if (!isset($respuesta) && !isset($validado)) {
                            $db->validadorActividad($con["patente"], $con["aduana"], $con["pedimento"], $con["referencia"], "INTENTO " . ($i + 1) . " DE 30");
                            sleep(45);
                        } elseif (isset($respuesta) && !isset($validado)) {
                            $db->validadorActividad($con["patente"], $con["aduana"], $con["pedimento"], $con["referencia"], "INTENTO " . ($i + 1) . " DE 30");
                            sleep(45);
                        }
                        if (!isset($respuesta)) {
                            $res = ftp_size($conn_id, $k);
                            if ($res > 0) {
                                $db->validadorActividad($con["patente"], $con["aduana"], $con["pedimento"], $con["referencia"], "SE ENCONTRO RESPUESTA {$k}");
                                if (file_exists($dir . DIRECTORY_SEPARATOR . $k)) {
                                    $contenido = file_get_contents($dir . DIRECTORY_SEPARATOR . $k);
                                } else {
                                    if (downloadFile($array, $con, $conn_id, $server["carpeta"], $dir, $k) == true) {
                                        $db->validadorActividad($con["patente"], $con["aduana"], $con["pedimento"], $con["referencia"], "DESCARGO RESPUESTA {$k}");
                                        $contenido = file_get_contents($dir . DIRECTORY_SEPARATOR . $k);
                                    }
                                }
                                if (!($db->verificarValidadorLog($con["patente"], $con["aduana"], $con["pedimento"], $k))) {
                                    $db->agregarValidadorLog($con["patente"], $con["aduana"], $con["pedimento"], $con["referencia"], $k, base64_encode($contenido), $array["username"]);
                                }
                                if (strpos($contenido, "ERRORES") !== false) {
                                    $db->validadorActividad($con["patente"], $con["aduana"], $con["pedimento"], $con["referencia"], "TIENE ERROR {$con["archivo"]}");
                                    $db->validadorLogError($array["id"]);
                                    $error = true;
                                }
                                $respuesta = true;
                            }
                        }
                        if (isset($respuesta) && !isset($error)) {
                            $val = ftp_size($conn_id, $m);
                            if ($val > 0) {
                                if (file_exists($dir . DIRECTORY_SEPARATOR . $m)) {
                                    $firma = file_get_contents($dir . DIRECTORY_SEPARATOR . $m);
                                } else {
                                    if (downloadFile($array, $con, $conn_id, $server["carpeta"], $dir, $m) == true) {
                                        $db->validadorActividad($con["patente"], $con["aduana"], $con["pedimento"], $con["referencia"], "DESCARGO FIRMA " . $m);
                                        $firma = file_get_contents($dir . DIRECTORY_SEPARATOR . $m);
                                    }
                                }
                                if (!($db->verificarValidadorLog($con["patente"], $con["aduana"], $con["pedimento"], $m))) {
                                    $db->validadorActividad($con["patente"], $con["aduana"], $con["pedimento"], $con["referencia"], "OBTUVO FIRMA " . $m);
                                    $db->agregarValidadorLog($con["patente"], $con["aduana"], $con["pedimento"], $con["referencia"], $m, base64_encode($firma), $array["username"]);
                                    $db->validadorLogValidado($array["id"]);
                                }
                                $validado = true;
                            }
                        }
                        if (isset($respuesta) && $respuesta == true && isset($validado) && $validado == true) {
                            $db->validadorActividad($con["patente"], $con["aduana"], $con["pedimento"], $con["referencia"], "SE COMPLETO VALIDACION " . $con["archivo"]);
                            ftp_close($conn_id);
                            return true;
                        }
                        if (isset($respuesta) && $respuesta == true && isset($error) && $error == true) {
                            $db->validadorActividad($con["patente"], $con["aduana"], $con["pedimento"], $con["referencia"], "SE DETECTO ERROR DE VALIDACION");
                            ftp_close($conn_id);
                            return false;
                        }
                    } // for
                    $db->validadorLogAgotado($array["id"]);
                    $db->validadorActividad($con["patente"], $con["aduana"], $con["pedimento"], $con["referencia"], "SE AGOTO EL TIEMPO DE ESPERA AL VALIDAR");
                    ftp_close($conn_id);
                    return true;
                }
            } // archivo no existe
        }
    }
}

function validadorpagoplus_fn($job) {
    require_once '/var/www/portalprod/library/OAQ/ArchivosM3.php';
    $workload = $job->workload();
    $array = unserialize($workload);
    $db = new Db();
    if (isset($array["id"])) {
        $con = $db->validadorLog($array["id"]);
        if (isset($con) && $con !== false) {
            $dir = $db->directorioValidador($con["patente"], $con["aduana"]);
            $server = $db->validador($con["patente"], $con["aduana"]);
            if (!($conn_id = connectFtp($server))) {
                $db->validadorActividad($con["patente"], $con["aduana"], $con["pedimento"], $con["referencia"], "SIN CONEXION AL VALIDADOR.");
                ftp_close($conn_id);
                return false;
            }
            $ext = pathinfo($con["archivo"], PATHINFO_EXTENSION);
            $a = "A" . trim(substr($con["archivo"], 1, 7)) . "." . $ext;
            $db->validadorActividad($con["patente"], $con["aduana"], $con["pedimento"], $con["referencia"], "PREPARANDO ENVIO {$con["archivo"]}");
            if (file_exists($dir . DIRECTORY_SEPARATOR . $con["archivo"])) {
                if (!($db->enviadoValidadorLog($array['id']))) {
                    if (($up = ftp_put($conn_id, $con["archivo"], $dir . DIRECTORY_SEPARATOR . $con["archivo"], FTP_BINARY))) {
                        $db->validadorActividad($con["patente"], $con["aduana"], $con["pedimento"], $con["referencia"], "SUBIO {$con["archivo"]}");
                        $db->validadorLogEnviado($array["id"]);
                        $uploaded = true;
                    }
                } else {
                    $uploaded = true;
                }
                if ($uploaded == true) {
                    for ($i = 0; $i < 30; $i++) {
                        if (isset($pagado)) {
                            break;
                        } else {
                            $db->validadorActividad($con["patente"], $con["aduana"], $con["pedimento"], $con["referencia"], "INTENTO " . ($i + 1) . " DE 30");
                            sleep(45);
                        }
                        $res = ftp_size($conn_id, $a);
                        if ($res > 0) {
                            $db->validadorActividad($con["patente"], $con["aduana"], $con["pedimento"], $con["referencia"], "SE ENCONTRO PAGO {$a}");
                            if (file_exists($dir . DIRECTORY_SEPARATOR . $a)) {
                                $contenido = file_get_contents($dir . DIRECTORY_SEPARATOR . $a);
                            } else {
                                if (downloadFile($array, $con, $conn_id, $server["carpeta"], $dir, $a) == true) {
                                    $db->validadorActividad($con["patente"], $con["aduana"], $con["pedimento"], $con["referencia"], "DESCARGO PAGO {$a}");
                                    $contenido = file_get_contents($dir . DIRECTORY_SEPARATOR . $a);
                                }
                            }
                            if (!($db->verificarValidadorLog($con["patente"], $con["aduana"], $con["pedimento"], $a))) {
                                $db->agregarValidadorLog($con["patente"], $con["aduana"], $con["pedimento"], $con["referencia"], $a, base64_encode($contenido), $array["username"]);
                                $db->validadorLogPagado($array["id"]);
                                $pagado = true;
                                break;
                            }
                        }
                        if (isset($pagado) && $pagado == true) {
                            $db->validadorActividad($con["patente"], $con["aduana"], $con["pedimento"], $con["referencia"], "SE COMPLETO PAGO {$a}");
                            ftp_close($conn_id);
                            return true;
                        }
                    } // for
                    if (isset($pagado) && $pagado == true) {
                        $db->validadorActividad($con["patente"], $con["aduana"], $con["pedimento"], $con["referencia"], "SE COMPLETO PAGO {$a}");
                        ftp_close($conn_id);
                        return true;
                    }
                    $db->validadorActividad($con["patente"], $con["aduana"], $con["pedimento"], $con["referencia"], "SE AGOTO TIEMPO DE ESPERA");
                    ftp_close($conn_id);
                    return false;
                } // uploaded
            } else {
                $db->validadorActividad($con["patente"], $con["aduana"], $con["pedimento"], $con["referencia"], "NO SE ENCONTRO ARCHIVO {$con["archivo"]}");
            }
        }
    }
}

function validador_fn($job) {
    require_once '/var/www/oaqintranet/library/OAQ/ArchivosM3.php';
    $functions = new OAQ_ArchivosM3();
    $workload = $job->workload();
    $array = unserialize($workload);

    $db = new Db();
    if (isset($array["id"])) {
        $file = $db->archivoValidacionEnviado($array["id"]);
        if (isset($file) && isset($file["patente"]) && isset($file["aduana"])) {
            $directorio = $db->directorioValidador($file["patente"], $file["aduana"]);
            if (isset($directorio)) {
                $server = $db->validador($file["patente"], $file["aduana"]);
                if (!($conn_id = connectFtp($server))) {
                    die("No server connection.");
                }
                if (ftp_directory_exists($conn_id, $server["carpeta"]) !== false) {
                    ftp_chdir($conn_id, $server["carpeta"]);
                }
                $ext = pathinfo($file["nomArchivo"], PATHINFO_EXTENSION);
                $m = "M" . trim(substr($file["nomArchivo"], 1, 7)) . ".err";
                $k = "k" . trim(substr($file["nomArchivo"], 1, 7)) . "." . $ext;
                $i = 0;
                $respuesta = false;
                $validacion = false;
                $error = false;
                while (1) {
                    if (!file_exists($directorio . DIRECTORY_SEPARATOR . $m)) {
                        if (downloadFile($array, $file, $conn_id, $server["carpeta"], $directorio, $m) == true) {
                            echo "Se descargo " . $m . "\n";
                            $validacion = true;
                            if (file_exists($directorio . DIRECTORY_SEPARATOR . $m)) {
                                $data = $functions->analizarArchivo($m, file_get_contents($directorio . DIRECTORY_SEPARATOR . $m));
                                if (!empty($array)) {
                                    if (!($db->verificarFirmas($array["id"]))) {
                                        foreach ($data as $item) {
                                            $db->agregarFrima($array["id"], $item["pedimento"], $item["firma"]);
                                        }
                                    }
                                }
                            }
                        }
                    }
                    if (!file_exists($directorio . DIRECTORY_SEPARATOR . $k)) {
                        if (downloadFile($array, $file, $conn_id, $server["carpeta"], $directorio, $k) == true) {
                            echo "Se descargo " . $k . "\n";
                            $respuesta = true;
                            if (strpos(file_get_contents($directorio . DIRECTORY_SEPARATOR . $k), "ERRORES") !== false) {
                                $error = true;
                                $db->actualizarArchivoValidacion($array["id"], 1);
                                echo "Se encontraron errores.\n";
                                return false;
                            }
                        }
                    }
                    if ($respuesta == true && $validacion == true) {
                        echo "Validación completada\n";
                        ftp_close($conn_id);
                        return true;
                    } elseif ($respuesta == true && $error == true) {
                        echo "Se encontraron errores en la validación\n";
                        ftp_close($conn_id);
                        return false;
                    } elseif ($validacion == true && file_exists($directorio . DIRECTORY_SEPARATOR . $k)) {
                        echo "Validación completada\n";
                        return true;
                    }
                    if ($i == 20) {
                        echo "Tiempo de espera superado.\n";
                        ftp_close($conn_id);
                        return false;
                    }
                    $i++;
                    echo "En espera, intento 1 de {$i}.\n";
                    sleep(30);
                } /** while * */
            }
        }
    }
}

function downloadFile($array, $file, $conn_id, $remoteFolder, $localFolder, $filename) {
    $db = new Db();
    if (!file_exists($localFolder . DIRECTORY_SEPARATOR . $filename)) {
        $res = ftp_size($conn_id, $filename);
        if ($res > 0) {
            ftp_get($conn_id, $localFolder . DIRECTORY_SEPARATOR . $filename, $remoteFolder . DIRECTORY_SEPARATOR . $filename, FTP_BINARY);
            if (file_exists($localFolder . DIRECTORY_SEPARATOR . $filename)) {
                if (($content = fileData($localFolder, $filename))) {
                    $db->nuevoArchivoValidacion($array["id"], $file["patente"], $file["aduana"], $filename, $content["content"], $content["hash"], 0, "gearman");
                }
                return true;
            } else {
                echo "No se pudo descargar " . $filename . "\n";
                return false;
            }
        } else {
            return false;
        }
    } else {
        return true;
    }
}

function fileData($directorio, $filename) {
    if (file_exists($directorio . DIRECTORY_SEPARATOR . $filename)) {
        return array(
            'content' => base64_encode(file_get_contents($directorio . DIRECTORY_SEPARATOR . $filename)),
            'hash' => sha1_file($directorio . DIRECTORY_SEPARATOR . $filename),
        );
    } else {
        return null;
    }
}

function connectFtp($server) {
    $conn_id = ftp_connect($server["host"], $server["puerto"]);
    $login_result = ftp_login($conn_id, $server["usuario"], $server["password"]);
    if ((!$conn_id) || (!$login_result)) {
        return false;
    }
    return $conn_id;
}

function ftp_directory_exists($ftp, $dir) {
    // Get the current working directory
    $origin = ftp_pwd($ftp);
    // Attempt to change directory, suppress errors
    if (@ftp_chdir($ftp, $dir)) {
        // If the directory exists, set back to origin
        ftp_chdir($ftp, $origin);
        return true;
    }
    // Directory does not exist
    return false;
}
