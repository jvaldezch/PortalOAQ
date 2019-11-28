<?php
defined("APPLICATION_ENV") || define("APPLICATION_ENV", (getenv("APPLICATION_ENV") ? getenv("APPLICATION_ENV") : "production"));
error_reporting(E_ERROR | E_WARNING | E_PARSE);
ini_set("soap.wsdl_cache_enabled", 0);
/**
 *  Trigger: https://192.168.0.246/automatizacion/index/enviar-m3?year=2015&mes=1&rfc=ARB820712U77,CCO030908FU8,CCO0309098N8,CIN0309091D3,CME950209J18,CME930831D89,SME751021B90,RHM720412B61,FDQ7904066U0,DAM980101SR0
 *  Command: su - www-data -c 'php /var/www/portalprod/workers/m3_worker.php'
 */
require_once "ftp.php";
$config = new Zend_Config_Ini(realpath(dirname(__FILE__) . "/../application/configs/application.ini"), APPLICATION_ENV);
echo "Iniciando\n";
$gmworker = new GearmanWorker();
$gmworker->addServer('127.0.0.1', 4730);
$gmworker->addFunction("m3_enviar", "m3_enviar_fn");
$gmworker->setTimeout(20000);

print "Esperando...\n";
while ($gmworker->work()) {
    if ($gmworker->returnCode() != GEARMAN_SUCCESS) {
        echo "return_code: " . $gmworker->returnCode() . "\n";
        break;
    }
}

function m3_enviar_fn($job) {
    $ftp = new Ftp();
    $workload = $job->workload();
    $array = unserialize($workload);
    try {
        if (isset($array['rfc'])) {
            echo "RFC: {$array['rfc']}\n";
            if ($ftp->getServerCredentials($array['rfc']) === true) {
                echo "Connection available.\n";
                $rfc = $array['rfc'];
                unset($array['rfc']);
                foreach ($array as $item) {
                    if (isset($item['idArchivoPago'])) {
                        $uploaded = $ftp->uploadToFtp("archivos_m3_pagos", $item['idArchivoPago'], $item['nombre'], $rfc);
                        if ($uploaded === true) {
                            echo "UPLOADED: {$item['nombre']}\n";
                        }
                        if (isset($item["archivosValidacion"])) {
                            foreach ($item["archivosValidacion"] as $val) {
                                $uploaded = $ftp->uploadToFtp("archivos_m3_prevalidacion", $val['id'], $val['nombre'], $rfc);
                                if ($uploaded === true) {
                                    echo "UPLOADED: {$val['nombre']}\n";
                                }
                            }
                        }
                        if (isset($item["archivosM3"])) {
                            foreach ($item["archivosM3"] as $val) {
                                $uploaded = $ftp->uploadToFtp("archivos_m3", $val['id'], $val['nombre'], $rfc);
                                if ($uploaded === true) {
                                    echo "UPLOADED: {$val['nombre']}\n";
                                }
                            }
                        }
                        if (isset($item["archivosResultado"])) {
                            foreach ($item["archivosResultado"] as $val) {
                                $uploaded = $ftp->uploadToFtp("archivos_m3_e", $val['id'], $val['nombre'], $rfc);
                                if ($uploaded === true) {
                                    echo "UPLOADED: {$val['nombre']}\n";
                                }
                            }
                        }
                    }
                }
            }
        }
    } catch (Exception $ex) {
        echo "Exception found: {$ex->getMessage()}\n";
    }
}
