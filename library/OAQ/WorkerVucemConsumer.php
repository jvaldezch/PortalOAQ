<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;

class OAQ_WorkerVucemConsumer {

    protected $log;
    protected $conn;
    protected $channel;
    protected $appConfig;
    protected $consumerCount = 1;
    protected $timeout = 180;

    function __construct($user) {
        $this->appConfig = new Application_Model_ConfigMapper();
        $this->log = new Application_Model_LogMapper();
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

    public function testing() {
        list(,, $consumerCount) = $this->channel->queue_declare("testing", false, true, false, false);
        if ($consumerCount > $this->consumerCount) {
            exit;
        }
        $this->channel->basic_qos(null, 1, null);
        $this->channel->basic_consume("testing", "", false, false, false, false, array($this, "creatingTesting"));
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
    
    public function creatingTesting(AMQPMessage $msg) {
        $result = $this->creandoUnTesting($msg->body);
        if ($result === true) {
            $msg->delivery_info["channel"]->basic_ack($msg->delivery_info["delivery_tag"]);
        }
    }
    
    private function creandoUnTesting($body) {
        try {
            $this->log->logEntry("Testing", "New call " . microtime() . " id " . $body, "localhost", "RabbitMQ");
            sleep(5);
            return true;
        } catch (Exception $ex) {
            $this->log->logEntry("Exception found", (string) $ex->getMessage(), "localhost", "RabbitMQ");
            return;
        }
    }
    
    public function estadoPedimento() {
        list(,, $consumerCount) = $this->channel->queue_declare("estadoPedimento", false, true, false, false);
        if ($consumerCount > $this->consumerCount) {
            exit;
        }
        $this->channel->basic_qos(null, 1, null);
        $this->channel->basic_consume("estadoPedimento", "", false, false, false, false, array($this, "estado"));
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
    
    public function estado(AMQPMessage $msg) {
        $result = $this->descargaPedimentoCompleto($msg->body);
        if ($result === true) {
            $msg->delivery_info["channel"]->basic_ack($msg->delivery_info["delivery_tag"]);
        }
    }

    private function descargaPedimentoCompleto($idTrafico) {
        try {
            $ts = new Trafico_Model_TraficosMapper();
            $t = new Trafico_Model_Table_Traficos();
            $t->setId($idTrafico);
            $ts->find($t);
            if ($t->getPatente() == 3589) {
                $this->log->logEntry("Message arrived", "PED " . $t->getPatente() . "-" . $t->getAduana() . "-" . $t->getPedimento() . " REF " . $t->getReferencia(), "localhost", "RabbitMQ");
                $mapper = new Vucem_Model_VucemPedimentosMapper();
                if (!($id = $mapper->verificar($t->getPatente(), $t->getAduana(), $t->getPedimento()))) {
                    $misc = new OAQ_Misc();
                    $misc->set_baseDir($this->appConfig->getParam("expdest"));
                    $path = $misc->nuevoDirectorioExpediente($t->getPatente(), $t->getAduana(), $t->getReferencia());
                    $firmantes = new Vucem_Model_VucemFirmanteMapper();
                    $sello = $firmantes->obtenerDetalleFirmante("MALL640523749");
                    $xml = new OAQ_XmlPedimentos();
                    $data["usuario"] = array(
                        "username" => $sello["rfc"],
                        "password" => $sello["ws_pswd"],
                        "certificado" => null,
                        "key" => null,
                        "new" => null,
                    );
                    $xml->set_patente($t->getPatente());
                    $xml->set_aduana($t->getAduana());
                    $xml->set_pedimento($t->getPedimento());
                    $xml->set_array($data);
                    $xml->consultaPedimentoCompleto();
                    $serv = new OAQ_Servicios();
                    $serv->setXml($xml->getXml());
                    file_put_contents($path . DIRECTORY_SEPARATOR . "PEDCONSULTA_" . $t->getAduana() . "-" . $t->getPatente() . "-" . $t->getPedimento() . ".xml", $xml->getXml());
                    $serv->consumirPedimento();
                    $res = new OAQ_Respuestas();
                    $resp = $res->analizarRespuestaPedimento($serv->getResponse());
                    if ($resp["error"] == false && isset($resp["numeroOperacion"])) {
                        $mapper = new Vucem_Model_VucemPedimentosMapper();
                        $filepath = $path . DIRECTORY_SEPARATOR . "PED_" . $t->getAduana() . "-" . $t->getPatente() . "-" . $t->getPedimento() . ".xml";
                        file_put_contents($filepath, $this->_formatXmlString($serv->getResponse()));
                        $this->log->logEntry("Completed", "PED " . $t->getPatente() . "-" . $t->getAduana() . "-" . $t->getPedimento() . " OP " . $resp["numeroOperacion"], "localhost", "RabbitMQ");
                        $id = $mapper->agregar($idTrafico, $t->getPatente(), $t->getAduana(), $t->getPedimento(), $resp["numeroOperacion"], $resp["partidas"], $filepath);
                        $this->descargaEstadoPedimento($id);
                        return true;
                    } else {
                        $this->log->logEntry("Error consulting", "PED " . $t->getPatente() . "-" . $t->getAduana() . "-" . $t->getPedimento() . " REF " . $t->getReferencia(), "localhost", "RabbitMQ");
                        return true;
                    }
                } else {
                    $this->descargaEstadoPedimento($id);
                    $this->log->logEntry("Completed", $t->getPatente() . "-" . $t->getAduana() . "-" . $t->getPedimento() . " already exists!", "localhost", "RabbitMQ");
                    return true;
                }
            }
        } catch (Exception $ex) {
            $this->log->logEntry("Exception found", (string) $ex->getMessage(), "localhost", "RabbitMQ");
            return;
        }
    }

    private function descargaEstadoPedimento($id) {
        try {
            $mapper = new Vucem_Model_VucemPedimentosMapper();
            $mapperest = new Vucem_Model_VucemPedimentosEstado();
            if (($arr = $mapper->obtener($id))) {
                $ts = new Trafico_Model_TraficosMapper();
                $t = new Trafico_Model_Table_Traficos();
                $t->setId($arr["idTrafico"]);
                $ts->find($t);
                if($mapperest->verificar($id)) {
                    $this->log->logEntry("Status Exists", $t->getPatente() . "-" . $t->getAduana() . "-" . $t->getPedimento(), "localhost", "RabbitMQ");
                    return true;
                }
                if ($t->getPatente() == 3589) {
                    $misc = new OAQ_Misc();
                    $misc->set_baseDir($this->appConfig->getParam("expdest"));
                    $path = $misc->nuevoDirectorioExpediente($t->getPatente(), $t->getAduana(), $t->getReferencia());
                    $firmantes = new Vucem_Model_VucemFirmanteMapper();
                    $sello = $firmantes->obtenerDetalleFirmante("MALL640523749");
                    $data["usuario"] = array(
                        "username" => $sello["rfc"],
                        "password" => $sello["ws_pswd"],
                        "certificado" => null,
                        "key" => null,
                        "new" => null,
                    );
                    $xml = new OAQ_XmlPedimentos(null, true);
                    $xml->set_patente($arr["patente"]);
                    $xml->set_aduana($arr["aduana"]);
                    $xml->set_pedimento($arr["pedimento"]);
                    $xml->set_numeroOperacion($arr["numeroOperacion"]);
                    $xml->set_array($data);
                    $xml->consultaEstadoPedimento();
                    file_put_contents($path . DIRECTORY_SEPARATOR . "EDOCONSULTA_" . $t->getAduana() . "-" . $t->getPatente() . "-" . $t->getPedimento() . ".xml", $xml->getXml());
                    $serv = new OAQ_Servicios();
                    $serv->setXml($xml->getXml());
                    $serv->consultaEstadoPedimento();
                    $resp = new OAQ_Respuestas();
                    $res = $resp->analizarRespuestaPedimento($serv->getResponse());
                    file_put_contents($path . DIRECTORY_SEPARATOR . "EDORESP_" . $t->getAduana() . "-" . $t->getPatente() . "-" . $t->getPedimento() . ".xml", $this->_formatXmlString($serv->getResponse()));
                    if ($res["error"] == false && isset($res["numeroPrevalidador"])) {
                        $mapperest = new Vucem_Model_VucemPedimentosEstado();
                        if(!($mapperest->verificar($id))) {
                            foreach ($res["estados"] as $item) {
                                $mapperest->agregar($id, $res["numeroPrevalidador"], $res["fechaEstado"], $item["estado"], $item["descripcionEstado"], $item["subEstado"], $item["descripcionSubEstado"]);
                            }
                        }
                        $this->log->logEntry("Status Completed", "PED " . $t->getPatente() . "-" . $t->getAduana() . "-" . $t->getPedimento(), "localhost", "RabbitMQ");
                        return true;
                    } else {
                        $this->log->logEntry("Status Incompleted", "PED " . $t->getPatente() . "-" . $t->getAduana() . "-" . $t->getPedimento(), "localhost", "RabbitMQ");
                        return true;                        
                    }
                }
            }
        } catch (Exception $ex) {
            $this->log->logEntry("Exception found", (string) $ex->getMessage(), "localhost", "RabbitMQ");
            return;
        }
    }
    
    protected function _formatXmlString($xmlString) {
        $xml = preg_replace('/(>)(<)(\/*)/', "$1\n$2$3", $xmlString);
        $token = strtok($xml, "\n");
        $result = '';
        $pad = 0;
        $matches = array();
        while ($token !== false) :
            if (preg_match('/.+<\/\w[^>]*>$/', $token, $matches)) :
                $indent = 0;
            elseif (preg_match('/^<\/\w/', $token, $matches)) :
                $pad--;
                $indent = 0;
            elseif (preg_match('/^<\w[^>]*[^\/]>.*$/', $token, $matches)) :
                $indent = 1;
            else :
                $indent = 0;
            endif;
            $line = str_pad($token, strlen($token) + $pad, ' ', STR_PAD_LEFT);
            $result .= $line . "\n";
            $token = strtok("\n");
            $pad += $indent;
        endwhile;
        return $result;
    }

}
