<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;

class OAQ_WorkerVucemSender {

    protected $conn;
    protected $channel;

    function __construct($user) {
        $host = (APPLICATION_ENV == "production") ? "192.168.200.11" : "127.0.0.1";
        $this->conn = new AMQPStreamConnection($host, 5672, $user, $this->_password($user));
        $this->channel = $this->conn->channel();
    }
    
    private function _password($user) {
        switch ($user) {
            case "admin":
                return "rabbitmq11!";
            case "pedimentos":
                return "pedis2017!";
            case "emails":
                return "email2017!";
            case "coves":
                return "cove2017!";
            case "edocs":
                return "edoc2017!";
            case "ftp":
                return "ftp2017!";
            default:
                break;
        }
    }

    public function estadoPedimento($arr) {
        $this->channel->queue_declare("estadoPedimento", false, true, false, false);
        $msg = new AMQPMessage($arr, array("delivery_mode" => 2));
        $this->channel->basic_publish($msg, "", "estadoPedimento");
    }
    
    public function testing($id) {
        $this->channel->queue_declare("testing", false, true, false, false);
        $msg = new AMQPMessage($id, array("delivery_mode" => 2));
        $this->channel->basic_publish($msg, "", "testing");
    }
    
    public function queueTesting() {
        $channel = $this->channel->basic_get("testing", false, true, false, false);
        if(isset($channel)) {
            return $channel->delivery_info["message_count"] + 1;
        }
        return null;
    }

    public function __destruct() {
        $this->channel->close();
        $this->conn->close();
    }

}
