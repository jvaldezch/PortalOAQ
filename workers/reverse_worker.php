<?php

$gmc = new GearmanClient();
$gmc->addServer('127.0.0.1', 4730);

$gmc->setCreatedCallback("reverse_created");
$gmc->setDataCallback("reverse_data");
$gmc->setStatusCallback("reverse_status");
$gmc->setCompleteCallback("reverse_complete");
$gmc->setFailCallback("reverse_fail");

# set some arbitrary application data
$data['jvaldez'] = 'chavarin';

$array = array(
    'REF0001',
    'REF1001',
    'REF2001',
);

foreach ($array as $item) {
    $task = $gmc->addTask("reverse", $item, null);
}

# run the tasks in parallel (assuming multiple workers)
if (!$gmc->runTasks()) {
    echo "ERROR " . $gmc->error() . "\n";
    exit;
}

echo "DONE\n";

function reverse_created($task) {
    echo "CREATED: " . $task->jobHandle() . "\n";
}

function reverse_status($task) {
    echo "STATUS: " . $task->jobHandle() . " - " . $task->taskNumerator() .
    "/" . $task->taskDenominator() . "\n";
}

function reverse_complete($task) {
    echo "COMPLETE: " . $task->jobHandle() . ", " . $task->data() . "\n";
}

function reverse_fail($task) {
    echo "FAILED: " . $task->jobHandle() . "\n";
}

function reverse_data($task) {
    echo "DATA: " . $task->data() . "\n";
}
