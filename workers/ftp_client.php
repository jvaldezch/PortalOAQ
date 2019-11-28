<?php

/**
 *  php ftp_client.php 348 9
 *  php /var/www/workers/ftp_client.php 438 9
 */
require_once 'mysql.php';
$db = new Db();
$gmc = new GearmanClient();
$gmc->addServer('127.0.0.1', 4730);

$gmc->setCreatedCallback("ftp_created");
$gmc->setDataCallback("ftp_data");
$gmc->setStatusCallback("ftp_status");
$gmc->setCompleteCallback("ftp_complete");
$gmc->setFailCallback("ftp_fail");

# set some arbitrary application data
$id = $argv[1];    // id de repositorio
$ftp = $argv[2];    // id de ftp

if (isset($id)) {
    $array = array(
        'idRepo' => $id,
        'idFtp' => $ftp,
    );
}

$task = $gmc->addTask("ftp", serialize($array));
# run the tasks in parallel (assuming multiple workers)
if (!$gmc->runTasks()) {
    echo "ERROR " . $gmc->error() . "\n";
    exit;
}

echo "DONE\n";

function ftp_created($task) {
//    echo "CREATED: " . $task->jobHandle() . "\n";
}

function ftp_status($task) {
//    echo "STATUS: " . $task->jobHandle() . " - " . $task->taskNumerator() . "/" . $task->taskDenominator() . "\n";
}

function ftp_complete($task) {
//    echo "COMPLETE: " . $task->jobHandle() . ", " . $task->data() . "\n";
}

function ftp_fail($task) {
//    echo "FAILED: " . $task->jobHandle() . "\n";
}

function ftp_data($task) {
    global $db;
    $data = unserialize($task->data());
    if ($data["task"] === 'envio') {
        $db->updateJob($data["id"], $data["estatus"]);
    } elseif ($data["task"] === 'repo') {
        $db->updateRepo($data["id"]);
    }
}
