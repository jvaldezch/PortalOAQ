<?php
$client= new GearmanClient();
$client->addServer("127.0.0.1",4730);
print $client->do("reverse", "Hello World!");
