<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;

class OAQ_WorkerFtpReceiver {

    protected $log;
    protected $ftp;
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
            case "vucem":
                return "vucem2017!";
            default:
                break;
        }
    }

    public function listenFtp() {
        list(,, $consumerCount) = $this->channel->queue_declare("ftpExpedientes", false, true, false, false);
        if ($consumerCount > $this->consumerCount) {
            exit;
        }
        $this->channel->basic_qos(null, 1, null);
        $this->channel->basic_consume("ftpExpedientes", "", false, false, false, false, array($this, "procesar"));
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
        if ($this->ftp((int) $msg->body)) {
            $msg->delivery_info["channel"]->basic_ack($msg->delivery_info["delivery_tag"]);
        } else {
            $this->log->logEntry("Error", "Error found while sending {$msg->body}", "localhost", "RabbitMQ");
        }
    }

    private function ftp($id) {
        try {
            $this->log->ftpLog($id, __METHOD__, "Se prepara envío por FTP", "localhost", "RabbitMQ");
            $index = new Archivo_Model_RepositorioIndex();
            $arr = $index->datos((int) $id);
            if (count($arr)) {
                if (isset($arr["rfcCliente"])) {
                    $ftp = new Automatizacion_Model_FtpMapper();
                    $server = $ftp->obtenerDatosFtp($arr["rfcCliente"]);
                    if (empty($server)) {
                        $this->log->ftpLog($id, __METHOD__, "El cliente con RFC {$arr["rfcCliente"]} no cuenta con FTP configurado.", "localhost", "RabbitMQ");
                        return true;
                    }
                    $mapper = new Archivo_Model_RepositorioMapper();
                    $files = $mapper->archivosNoEnviadosFtp($arr["patente"], $arr["aduana"], $arr["referencia"]);
                    if (count($files)) {
                        $this->ftp = new OAQ_Ftp(array(
                            "host" => $server["url"],
                            "port" => $server["port"],
                            "username" => $server["user"],
                            "password" => $server["password"],
                        ));
                        try {
                            $conn = $this->ftp->connect();
                        } catch (Exception $ex) {
                            $this->log->ftpLog($id, __METHOD__, $ex->getMessage() . " cliente: " . $arr["rfcCliente"] . " idExpediente: " . $id, "localhost", "RabbitMQ");
                            $index->update($id, array("ftp" => 4));
                            return true;
                        }
                        if ($conn == true) {
                            if (!$this->ftp->changeRemoteDirectory($server["remoteFolder"])) {
                                $this->log->ftpLog($id, __METHOD__, "No se puede cambiar a carpeta {$arr["remoteFolder"]}.", "localhost", "RabbitMQ");
                                $this->ftp->disconnect();
                                return true;
                            }
                            $folderName = $arr["pedimento"] . "_" . $arr["referencia"];
                            if (isset($server["nombreCarpeta"]) && $server["nombreCarpeta"] !== null) {
                                eval($server["nombreCarpeta"]);
                            }
                            if (isset($server["transferMode"]) && $server["transferMode"] == "passive") {
                                $this->ftp->setPassive();
                            }
                            $this->log->ftpLog($id, __METHOD__, "Conectado al FTP correctamente.", "localhost", "RabbitMQ");
                            if (!$this->ftp->checkFolder($server["remoteFolder"] . "/" . (string) $folderName)) {
                                if (!$this->ftp->makeDirectory($server["remoteFolder"] . "/" . (string) $folderName)) {
                                    $this->log->ftpLog($id, __METHOD__, "No se puede crear carpeta {$folderName}.", "localhost", "RabbitMQ");
                                    $this->ftp->disconnect();
                                    return true;
                                }
                            }
                            if (!$this->ftp->changeRemoteDirectory($server["remoteFolder"] . "/" . (string) $folderName)) {
                                $this->log->ftpLog($id, __METHOD__, "No se puede cambiar a carpeta creada {$folderName}.", "localhost", "RabbitMQ");
                                $this->ftp->disconnect();
                                return true;
                            }
                            $sent = 0;
                            $mppr = new Archivo_Model_RepositorioPrefijos();
                            foreach ($files as $file) {
                                if (file_exists($file["ubicacion"])) {
                                    if ($arr["rfcCliente"] != "STE071214BE7") {
                                        if ($this->ftp->upload($file["ubicacion"])) {
                                            $mapper->ftpSent($file["id"]);
                                            $sent = $sent + 1;
                                            $this->log->agregar(array(
                                                "idExpediente" => $id,
                                                "origen" => __METHOD__,
                                                "mensaje" => basename($file["ubicacion"]),
                                                "estatus" => 1,
                                                "isFile" => 1,
                                                "ip" => "localhost",
                                                "creado" => date("Y-m-d H:i:s"),
                                                "usuario" => "RabbitMQ",
                                            ));
                                            //$this->log->ftpLog($id, __METHOD__, basename($file["ubicacion"]), "localhost", "RabbitMQ");
                                        } else {
                                            $this->log->agregar(array(
                                                "idExpediente" => $id,
                                                "origen" => __METHOD__,
                                                "mensaje" => basename($file["ubicacion"]),
                                                "estatus" => 0,
                                                "isFile" => 1,
                                                "ip" => "localhost",
                                                "creado" => date("Y-m-d H:i:s"),
                                                "usuario" => "RabbitMQ",
                                            ));
                                        }
                                    } else {                                        
                                        $prefix = $mppr->obtenerPrefijo($file["tipo_archivo"]);
                                        switch ((int) $file["tipo_archivo"]) {
                                            case 33:
                                                $prefix = "PS_";
                                                break;
                                        }
                                        if ($this->ftp->upload($file["ubicacion"], $prefix)) {
                                            $mapper->ftpSent($file["id"]);
                                            $sent = $sent + 1;
                                            $this->log->agregar(array(
                                                "idExpediente" => $id,
                                                "origen" => __METHOD__,
                                                "mensaje" => basename($file["ubicacion"]),
                                                "estatus" => 1,
                                                "isFile" => 1,
                                                "ip" => "localhost",
                                                "creado" => date("Y-m-d H:i:s"),
                                                "usuario" => "RabbitMQ",
                                            ));
                                            //$this->log->ftpLog($id, __METHOD__, basename($file["ubicacion"]), "localhost", "RabbitMQ");
                                        } else {
                                            $this->log->agregar(array(
                                                "idExpediente" => $id,
                                                "origen" => __METHOD__,
                                                "mensaje" => basename($file["ubicacion"]),
                                                "estatus" => 0,
                                                "isFile" => 1,
                                                "ip" => "localhost",
                                                "creado" => date("Y-m-d H:i:s"),
                                                "usuario" => "RabbitMQ",
                                            ));
                                        }
                                    }
                                }
                            }
                            if ($sent != 0) {
                                $this->log->ftpLog($id, __METHOD__, "Se sincronizaron {$sent} archivo(s).", "localhost", "RabbitMQ");
                            } else {
                                $this->log->ftpLog($id, __METHOD__, "No se enviaron archivos nuevos.", "localhost", "RabbitMQ");
                            }
                        }
                        $this->ftp->disconnect();
                        return true;
                    } else {
                        $this->log->ftpLog($id, __METHOD__, "No hay archivos para enviar.", "localhost", "RabbitMQ");
                        return true;
                    }
                }
            } else {
                $this->log->ftpLog($id, __METHOD__, "No hay datos.", "localhost", "RabbitMQ");
                return true;
            }
            return true;
        } catch (Exception $ex) {
            $this->log->ftpLog($id, __METHOD__, (string) $ex->getMessage(), "localhost", "RabbitMQ");
            return true;
        }
    }

}
