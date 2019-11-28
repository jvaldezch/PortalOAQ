<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;

class OAQ_Workers_ArchivosValidacionSender {

    protected $conn;
    protected $channel;

    function __construct() {
        $host = (APPLICATION_ENV == "production") ? "192.168.200.11" : "127.0.0.1";
        $this->conn = new AMQPStreamConnection($host, 5672, "validacion", "validacion2017!");
        $this->channel = $this->conn->channel();
    }

    public function archivosDePago($id) {
        $this->channel->queue_declare("archivosDePago", false, true, false, false);
        $msg = new AMQPMessage($id, array("delivery_mode" => 2));
        $this->channel->basic_publish($msg, "", "archivosDePago");
    }

    public function __destruct() {
        $this->channel->close();
        $this->conn->close();
    }

}
