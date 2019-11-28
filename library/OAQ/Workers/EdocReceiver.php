<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;

class OAQ_Workers_EdocReceiver {

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
        $this->conn = new AMQPStreamConnection($host, 5672, "edocs", "edoc2017!");
        $this->channel = $this->conn->channel();
        $this->mapper = new Vucem_Model_VucemTmpEdocsMapper();
        $this->services = new OAQ_Servicios();
        $this->res = new OAQ_Respuestas();
    }

    public function listenEdocs() {
        list(,, $consumerCount) = $this->channel->queue_declare("edocs", false, true, false, false);
        if ($consumerCount > $this->consumerCount) {
            exit;
        }
        $this->channel->basic_qos(null, 1, null);
        $this->channel->basic_consume("edocs", "", false, false, false, false, array($this, "procesar"));
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

    public function procesar(AMQPMessage $msg) {
        if ($this->edoc((int) $msg->body)) {
            $msg->delivery_info["channel"]->basic_ack($msg->delivery_info["delivery_tag"]);
            return true;
        } else {
            $this->log->logEntry("Error", "Error found while sending {$msg->body}", "localhost", "RabbitMQ");
            return true;
        }
    }

    private function edoc($id) {
        try {
            $this->log->logEntry("Message arrived", "New EDOC to process: {$id}", "localhost", "RabbitMQ");
            $arr = $this->mapper->obtenerArchivo($id);
            $firmante = new Vucem_Model_VucemFirmanteMapper();
            $this->sello = $firmante->obtenerDetalleFirmante($arr["firmante"], null, $arr["patente"], $arr["aduana"]);
            $this->data["usuario"] = array(
                "username" => $this->sello["rfc"],
                "password" => $this->sello["ws_pswd"],
                "certificado" => $this->sello["cer"],
                "key" => openssl_get_privatekey(base64_decode($this->sello["spem"]), $this->sello["spem_pswd"]),
                "new" => isset($this->sello["sha"]) ? true : false,
            );
            if (!empty($arr)) {
                $sent = false;
                for ($x = 0; $x <= 10; $x++) {
                    if ((int) $arr["estatus"] == 1 && $sent == false) {
                        $sent = $this->enviarEdoc($arr);
                        if ($sent == false) {
                            return true;
                        }
                    }
                    sleep(30);
                    if ($sent == true) {
                        $res = $this->consultaRespuesta($this->mapper->obtenerArchivo($id));   
                        if ($res == true) {
                            return true;
                        }
                    }
                }                
            }
            return true;
        } catch (Exception $ex) {
            $this->log->logEntry("Exception found", (string) $ex->getMessage(), "localhost", "RabbitMQ");
            return true;
        }
    }

    private function consultaRespuesta($arr) {
        $xml = new OAQ_Xml(false, true);
        $this->data["consulta"] = array(
            "operacion" => $arr["numOperacion"],
        );
        $xml->consultaEstatusOperacionEdocument($this->data);
        $this->_saveToDisk(dirname($arr["nomArchivo"]), "EDOC_" . $arr["tipoArchivo"] . "_CONRESP_" . $arr["hash"] . ".xml", $xml->getXml());
        $this->services->setXml($xml->getXml());
        $this->services->consultaEdocument();
        $resp = $this->res->analizarRespuesta($this->services->getResponse());
        $this->mapper->ultimaRespuesta($arr["id"], json_encode($resp));
        if (isset($resp) && !empty($resp)) {
            if ($resp["error"] == false && isset($resp["edocument"])) {
                $this->mapper->edocument($arr["id"], $resp["edocument"]);
                $this->mapper->estatus($arr["id"], 3);
                if (isset($arr["idEdoc"])) {
                    $this->_actualizarEdoc($arr["idEdoc"], $resp["edocument"]);
                    $this->_guardarEdoc($arr["idEdoc"], $arr);
                }
                return true;
            } elseif($resp["error"] == false && !isset($resp["edocument"])) {
                return true;
            } else {
                $this->mapper->estatus($arr["id"], 4);
                return true;
            }
        }
        return false;
    }

    private function enviarEdoc($arr) {
        if (count($this->sello)) {
            $xml = new OAQ_Xml(false, true);
            $this->data["archivo"] = array(
                "idTipoDocumento" => $arr["tipoArchivo"],
                "correoElectronico" => (APPLICATION_ENV == "production") ? "vucem@oaq.com.mx" : "soporte@oaq.com.mx",
                "archivo" => base64_encode(file_get_contents($arr["nomArchivo"])),
                "nombreDocumento" => pathinfo($arr["nomArchivo"], PATHINFO_FILENAME),
                "rfcConsulta" => $arr["rfcConsulta"],
                "hash" => $arr["hash"],
            );
            $xml->xmlEdocument($this->data);
            $this->_saveToDisk(dirname($arr["nomArchivo"]), "EDOC" . "_" . $arr["tipoArchivo"] . "_" . $arr["hash"] . ".xml", $xml->getXml());
            $this->services->setXml($xml->getXml());
            $this->services->consumirServicioEdocument();
            $this->mapper->enviado($arr["id"]);
            $resp = $this->res->analizarRespuesta($this->services->getResponse());
            $this->mapper->ultimaRespuesta($arr["id"], json_encode($resp));
            if (isset($resp) && !empty($resp)) {
                if (isset($resp["numeroOperacion"])) {
                    $this->mapper->numOperacion($arr["id"], $resp["numeroOperacion"]);
                    $this->mapper->estatus($arr["id"], 2);
                    if (($idEdoc = $this->_agregarEdoc($arr, $resp["numeroOperacion"], $xml->get_firma(), $xml->get_cadena()))) {
                        $this->mapper->update($arr["id"], array("idEdoc" => $idEdoc));
                    }
                    return true;
                } else {
                    $this->mapper->error($arr["id"]);
                    $this->mapper->estatus($arr["id"], 4);
                    return true;
                }
            }
        } else {
            return false;
        }
    }

    protected function _guardarEdoc($idEdoc, $arr) {
        $mppr = new Vucem_Model_VucemEdocMapper();
        $mppri = new Vucem_Model_VucemEdocIndex();
        if (($data = $mppr->obtener($idEdoc))) {
            $misc = new OAQ_Misc();
            $sello = new Vucem_Model_VucemFirmanteMapper();
            $data["titulo"] = "EDOC_" . $arr["tipoArchivo"] . "_ACUSE_" . $data["edoc"];
            $files = new OAQ_VucemArchivos(array(
                "id" => $idEdoc,
                "solicitud" => $arr["numOperacion"],
                "dir" => $misc->nuevoDirectorio($this->appConfig->getParam("expdest"), $arr["patente"], $arr["aduana"], $arr["referencia"]),
                "data" => $data,
                "sello" => $sello->obtenerDetalleFirmante($arr["firmante"]),
                "username" => $arr["usuario"],
                "archivoOriginal" => $arr["nomArchivo"],
            ));
            if($files->guardarEdoc()) {
                $mppr->update($idEdoc, array('expediente' => 1));
                $mppri->update($idEdoc, array('expediente' => 1));
                $this->_crearRepositorioIndex($arr, $sello->obtenerDetalleFirmante($arr["firmante"]));
                return true;
            }
        }
    }
    
    protected function _crearRepositorioIndex($arr, $sello) {
        $mppr = new Archivo_Model_RepositorioIndex();
        if (isset($arr["idTrafico"]) && $arr["idTrafico"] != null) {
            $id = $mppr->buscarPorTrafico($arr["idTrafico"]);
        } else {
            $id = $mppr->buscar($arr["patente"], $arr["aduana"], $arr["referencia"], $arr["pedimento"]);
        }
        if (!isset($id)) {
            $referencias = new OAQ_Referencias();
            if ($sello["figura"] == 1) {
                $referencias->crearRepositorio($arr["patente"], $arr["aduana"], $arr["referencia"], $arr["usuario"], $arr["rfcConsulta"], $arr["pedimento"]);
            } else {
                $referencias->crearRepositorio($arr["patente"], $arr["aduana"], $arr["referencia"], $arr["usuario"], $arr["firmante"], $arr["pedimento"]);                
            }
        } else {
            return true;
        }
    }

    protected function _actualizarEdoc($id, $edocument) {
        try {
            $mppr = new Vucem_Model_VucemEdocMapper();
            $mppr->update($id, array(
                "edoc" => $edocument,
                "estatus" => 2,
                "actualizado" => date('Y-m-d H:i:s')
            ));
            $mppri = new Vucem_Model_VucemEdocIndex();
            $mppri->update($id, array(
                "edoc" => $edocument,
                "estatus" => 2,
                "actualizado" => date('Y-m-d H:i:s')
            ));
        } catch (Exception $ex) {
            $this->log->logEntry("DB Exception", (string) $ex->getMessage(), "localhost", "RabbitMQ");
        }
    }
    
    protected function _agregarEdoc($arr, $numeroOperacion, $firma, $cadena) {
        try {
            $mppr = new Vucem_Model_VucemEdocMapper();
            $mppri = new Vucem_Model_VucemEdocIndex();
            $array = array(
                "rfc" => $arr["firmante"],
                "patente" => $arr["patente"],
                "aduana" => $arr["aduana"],
                "pedimento" => $arr["pedimento"],
                "referencia" => $arr["referencia"],
                "uuid" => "",
                "solicitud" => $numeroOperacion,
                "certificado" => "",
                "cadena" => $cadena,
                "firma" => $firma,
                "rfcConsulta" => $arr["rfcConsulta"],
                "tipoDoc" => $arr["tipoArchivo"],
                "subTipoArchivo" => $arr["subTipoArchivo"],
                "nomArchivo" => basename($arr["nomArchivo"]),
                "hash" => $arr["hash"],
                "usuario" => $arr["usuario"],
                "email" => (APPLICATION_ENV == "production") ? "vucem@oaq.com.mx" : "soporte@oaq.com.mx",
                "estatus" => 1,
                "edoc" => null,
                "archivo" => "",
                "respuesta" => null,
                "enviado" => date("Y-m-d H:i:s"),
            );
            if (($id = $mppr->add($array))) {
                $arrayi = array(
                    "id" => $id,
                    "rfc" => $arr["firmante"],
                    "patente" => $arr["patente"],
                    "aduana" => $arr["aduana"],
                    "pedimento" => $arr["pedimento"],
                    "referencia" => $arr["referencia"],
                    "solicitud" => $numeroOperacion,
                    "tipoDoc" => $arr["tipoArchivo"],
                    "subTipoArchivo" => $arr["subTipoArchivo"],
                    "nomArchivo" => basename($arr["nomArchivo"]),
                    "usuario" => $arr["usuario"],
                    "estatus" => 1,
                    "edoc" => null,
                    "size" => filesize($arr["nomArchivo"]),
                    "enviado" => date("Y-m-d H:i:s"),
                );
                $mppri->add($arrayi);
                return $id;
            }
            return;
        } catch (Exception $ex) {
            $this->log->logEntry("DB Exception", (string) $ex->getMessage(), "localhost", "RabbitMQ");
        }
    }

    protected function _saveToDisk($dir, $filename, $content) {
        try {
            if ($dir !== null) {
                file_put_contents($dir . DIRECTORY_SEPARATOR . $filename, $content);
            } else {
                throw new Exception("Directory is not set.");
            }
        } catch (Exception $e) {
            throw new Exception("Exception on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

}
