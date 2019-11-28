<?php

$worker = new GearmanWorker();
$worker->addServer("127.0.0.1", 4730);
$worker->addFunction("reverse", "my_reverse_function");
while ($worker->work());

function my_reverse_function($job) {
    return strrev($job->workload());
}
