<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;

class OAQ_WorkerSender {

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

    public function enviarEmail($idNotificacion) {
        $this->channel->queue_declare("enviarEmail", false, true, false, false);
        $msg = new AMQPMessage($idNotificacion, array("delivery_mode" => 2));
        $this->channel->basic_publish($msg, "", "enviarEmail");
    }
    
    public function imprimirPedimento($patente, $aduana, $pedimento) {
        $this->channel->queue_declare("imprimirPedimento", false, true, false, false);
        $message = serialize(["patente" => $patente, "aduana" => $aduana, "pedimento" => $pedimento]);
        $msg = new AMQPMessage($message, array("delivery_mode" => 2));
        $this->channel->basic_publish($msg, "", "imprimirPedimento");
    }

    public function __destruct() {
        $this->channel->close();
        $this->conn->close();
    }

}
