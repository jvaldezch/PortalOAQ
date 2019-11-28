<?php

/**
 *  php /var/www/workers/monitor_worker.php
 */
require_once 'monitor.php';
ini_set("soap.wsdl_cache_enabled", 0);

echo "Iniciando\n";
$gmworker = new GearmanWorker();
$gmworker->addServer('127.0.0.1', 4730);

$gmworker->addFunction("actualizar_pedimento", "actualizar_pedimento_fn");
$gmworker->addFunction("actualizar_estatus", "actualizar_estatus_fn");
$gmworker->setTimeout(10000);

print "Esperando...\n";
while ($gmworker->work()) {
    if ($gmworker->returnCode() != GEARMAN_SUCCESS) {
        echo "return_code: " . $gmworker->returnCode() . "\n";
        break;
    }
}

function actualizar_pedimento_fn($job) {
    $db = new Monitor();
    $workload = $job->workload();
    $array = unserialize($workload);
    try {
        if (!empty($array)) {
            echo "PEDIMENTO >> Patente: {$array["patente"]}, aduana {$array["aduana"]}, referencia {$array["referencia"]}\n";
            if(!($wsdl = $db->obtenerWsdl($array['patente'], $array['aduana']))) {
                echo "NO WSDL >> Patente: {$array["patente"]}, aduana {$array["aduana"]}\n";
            }
            if (isset($wsdl)) {
                $soap = new SoapClient($wsdl, array('exceptions' => true, 'trace' => true, 'cache_wsdl' => 0));
                $data = $soap->monitorReferencia($array['referencia']);
                if (isset($data) && !empty($data)) {
                    if (isset($data['pedimento']) && $data['pedimento'] != '') {
                        $db->actualizarEmbarque($array['id'], $data);
                    } else {
                        echo "NO PEDIMENTO >> Referencia: {$array["referencia"]}\n";
                    }
                }
            }
        }
    } catch (Exception $ex) {
        echo "Exception found: {$ex->getMessage()}\n";
    }
}

function actualizar_estatus_fn($job) {
    $db = new Monitor();
    $workload = $job->workload();
    $array = unserialize($workload);
    try {
        if (!empty($array)) {
            echo "ESTATUS >> Patente: {$array["patente"]}, aduana {$array["aduana"]}, referencia {$array["referencia"]}\n";
            if(!($wsdl = $db->obtenerWsdl($array['patente'], $array['aduana']))) {
                echo "NO WSDL >> Patente: {$array["patente"]}, aduana {$array["aduana"]}\n";
            }
            if (isset($wsdl)) {
                $soap = new SoapClient($wsdl, array('exceptions' => true, 'trace' => true, 'cache_wsdl' => 0));
                $data = $soap->monitorEstatusReferencia($array["referencia"]);
                if (isset($data->estatus)) {
                    if($data->estatus != $array["estatus"]) {
                        $arr = array(
                            'idEmbarque' => $array['id'],
                            'estatus' => $data->estatus,
                        );
                        $db->agregarEstatus($arr);
                    } else {
                        echo "ESTATUS SIN CAMBIO >> Referencia: {$array["referencia"]}\n";
                    }
                }
            }
        }
    } catch (Exception $ex) {
        echo "Exception found: {$ex->getMessage()}\n";
    }
}
