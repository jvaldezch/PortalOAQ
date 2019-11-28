<?php

/**
 *  php edoc_client.php 348 9
 *  php /var/www/workers/edoc_client.php 438 9
 */
require_once 'mysql.php';
$db = new Db();
$gmc = new GearmanClient();
$gmc->addServer('127.0.0.1', 4730);

$gmc->setCreatedCallback("trafico_created");
$gmc->setDataCallback("trafico_data");
$gmc->setStatusCallback("trafico_status");
$gmc->setCompleteCallback("trafico_complete");
$gmc->setFailCallback("trafico_fail");

# set some arbitrary application data
$id = $argv[1];    // array serializado
$file = $argv[2];    // array serializado

if (isset($id)) {
    $array = array(
        'id' => $id,
        'file' => $file,
    );
}

$task = $gmc->addTask("trafico", serialize($array));
if (!$gmc->runTasks()) {
    echo "ERROR " . $gmc->error() . "\n";
    exit;
}

echo "DONE\n";

function trafico_created($task) {
    echo "CREATED: " . $task->jobHandle() . "\n";
}

function trafico_status($task) {
    echo "STATUS: " . $task->jobHandle() . " - " . $task->taskNumerator() .
    "/" . $task->taskDenominator() . "\n";
}

function trafico_complete($task) {
    echo "COMPLETE: " . $task->jobHandle() . ", " . $task->data() . "\n";
}

function trafico_fail($task) {
    echo "FAILED: " . $task->jobHandle() . "\n";
}

function trafico_data($task) {
}
