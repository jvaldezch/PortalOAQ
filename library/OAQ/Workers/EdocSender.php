<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;

class OAQ_Workers_EdocSender {

    protected $conn;
    protected $channel;

    function __construct() {
        $host = (APPLICATION_ENV == "production") ? "192.168.200.11" : "127.0.0.1";
        $this->conn = new AMQPStreamConnection($host, 5672, "edocs", "edoc2017!");
        $this->channel = $this->conn->channel();
    }

    public function edocs($idRepo) {
        $this->channel->queue_declare("edocs", false, true, false, false);
        $msg = new AMQPMessage($idRepo, array("delivery_mode" => 2));
        $this->channel->basic_publish($msg, "", "edocs");
    }

    public function __destruct() {
        $this->channel->close();
        $this->conn->close();
    }

}
