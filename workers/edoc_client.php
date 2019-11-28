<?php
/**
 *  php edoc_client.php 348 9
 *  php /var/www/workers/edoc_client.php 438 9
 */
require_once 'mysql.php';
$db = new Db();
$gmc = new GearmanClient();
$gmc->addServer('127.0.0.1', 4730);

$gmc->setCreatedCallback("edoc_created");
$gmc->setDataCallback("edoc_data");
$gmc->setStatusCallback("edoc_status");
$gmc->setCompleteCallback("edoc_complete");
$gmc->setFailCallback("edoc_fail");

# set some arbitrary application data
$id = $argv[1];    // array serializado
$file = $argv[2];    // array serializado

if(isset($id)) {
    $array = array(
        'id' => $id,
        'file' => $file,
        );
}

$task = $gmc->addTask("edoc", serialize($array));
# run the tasks in parallel (assuming multiple workers)
if (!$gmc->runTasks()) {
    echo "ERROR " . $gmc->error() . "\n";
    exit;
}

echo "DONE\n";

function edoc_created($task) {
    echo "CREATED: " . $task->jobHandle() . "\n";
}

function edoc_status($task) {
    echo "STATUS: " . $task->jobHandle() . " - " . $task->taskNumerator() .
    "/" . $task->taskDenominator() . "\n";
}

function edoc_complete($task) {
    echo "COMPLETE: " . $task->jobHandle() . ", " . $task->data() . "\n";
}

function edoc_fail($task) {
    echo "FAILED: " . $task->jobHandle() . "\n";
}

function edoc_data($task) {
    global $db;
    $data = unserialize($task->data());
    if($data["task"] === 'envio') {
        $db->updateJob($data["id"], $data["estatus"]);
    } elseif($data["task"] === 'repo') {
        $db->updateRepo($data["id"]);
    }
}
