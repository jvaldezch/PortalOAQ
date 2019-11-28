<?php
require_once 'Net/Gearman/Client.php';

$client = new Net_Gearman_Client('localhost:4730');

$task = new Net_Gearman_Task('Reverse', range(1,5));
$task->type = Net_Gearman_Task::JOB_BACKGROUND;

$set = new Net_Gearman_Set();
$set->addTask($task);

$client->someBackgroundJob(array(
    'userid' => 5555,
    'action' => 'new-comment'
));
$client->runSet($set);
$client->run();
