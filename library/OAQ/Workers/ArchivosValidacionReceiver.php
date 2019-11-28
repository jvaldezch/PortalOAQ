<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;

class OAQ_Workers_ArchivosValidacionReceiver {

    protected $log;
    protected $conn;
    protected $channel;
    protected $appConfig;
    protected $consumerCount = 1;
    protected $timeout = 180;
    protected $mapper;
    protected $sello;
    protected $services;
    protected $res;
    protected $data;

    function __construct() {
        $this->appConfig = new Application_Model_ConfigMapper();
        $this->log = new Application_Model_LogMapper();
        $host = (APPLICATION_ENV == "production") ? "192.168.200.11" : "127.0.0.1";
        $this->conn = new AMQPStreamConnection($host, 5672, "validacion", "validacion2017!");
        $this->channel = $this->conn->channel();
        $this->mapper = new Vucem_Model_VucemTmpEdocsMapper();
        $this->services = new OAQ_Servicios();
        $this->res = new OAQ_Respuestas();
    }

    public function listenPagos() {
        list(,, $consumerCount) = $this->channel->queue_declare("archivosDePago", false, true, false, false);
        if ($consumerCount > $this->consumerCount) {
            exit;
        }
        $this->channel->basic_qos(null, 1, null);
        $this->channel->basic_consume("archivosDePago", "", false, false, false, false, array($this, "procesarPago"));
        $starttime = time();
        while (count($this->channel->callbacks)) {
            $this->channel->wait();
            $now = time() - $starttime;
            if ($now > $this->timeout) {
                break;
            }
        }
        $this->channel->close();
        $this->conn->close();
    }

    public function procesarPago(AMQPMessage $msg) {
        if ($this->pagoPedimento((int) $msg->body)) {
            $msg->delivery_info["channel"]->basic_ack($msg->delivery_info["delivery_tag"]);
        } else {
            $this->log->logEntry("Error", "Error found while sending {$msg->body}", "localhost", "RabbitMQ");
        }
    }
    
    private function pagoPedimento($id) {
        try {
            $mapper = new Operaciones_Model_ValidadorRespuestas();
            $m3 = new Automatizacion_Model_ArchivosValidacionMapper();
            $ped = new Automatizacion_Model_ArchivosValidacionPedimentosMapper();
            $firmas = new Automatizacion_Model_ArchivosValidacionFirmasMapper();
        
            $arr = $mapper->obtenerContenido($id);
            $archivos = new OAQ_ArchivosValidacion();
            $archivos->set_contenido(base64_decode($arr["contenido"]));
            $archivos->analizaArchivoPago();
            $data = $archivos->get_data();
            $traffics = new Trafico_Model_TraficosMapper();
            foreach ($data as $item) {
                $arr = array();
                $firma = $firmas->ultimaFirma($item["patente"], $item["pedimento"]);
                if (isset($firma) && !empty($firma)) {
                    $arr["firmaValidacion"] = $firma["firma"];
                }
                if (isset($item["firmaBanco"])) {
                    $arr["firmaBanco"] = $item["firmaBanco"];
                }
                $p = $ped->obtenerUltimo($item["patente"], $item["pedimento"]);
                if (isset($p) && !empty($p)) {
                    $con = $m3->fileContent($p["idArchivoValidacion"]);
                    if (isset($con) && !empty($con)) {
                        $archivos->set_contenido(base64_decode($con["contenido"]));
                        $archivos->extraerPedimento($item["pedimento"]);
                        $archM3 = $archivos->get_data();
                    }
                } 
                if (($id = $traffics->verificar($item["patente"], str_pad($item["aduana"], 3, "0", STR_PAD_RIGHT), $item["pedimento"]))) {
                    $trafico = new OAQ_Trafico(array("idTrafico" => $id, "idUsuario" => 0));
                    $trafico->actualizarDesdeSitawin();
                    $trafico->actualizarFecha(2, $item["fechaPago"]);
                    $traffics->actualizarEstatus($id, 2);
                    if (isset($archM3) && isset($archM3["fechaPresentacion"])) {
                        $trafico->actualizarFecha(5, $archM3["fechaPresentacion"]);
                    }
                    $traffics->actualizarDatosTrafico($id, $arr);
                }
            }
            return true;
        } catch (Exception $ex) {
            $this->log->logEntry("Exception found", (string) $ex->getMessage(), "localhost", "RabbitMQ");
            return;
        }
    }

}
