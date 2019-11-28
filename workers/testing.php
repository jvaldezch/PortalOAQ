<?php

require_once "mysql.php";
$db = new Db();
$arr = $db->cofidiEmails(0);

var_dump($arr);
