<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;

class OAQ_WorkerReceiver {

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
            case "vucem":
                return "vucem2017!";
            default:
                break;
        }
    }

    public function listenEmails() {
        list(,, $consumerCount) = $this->channel->queue_declare("enviarEmail", false, true, false, false);
        if ($consumerCount > $this->consumerCount) {
            exit;
        }
        $this->channel->basic_qos(null, 1, null);
        $this->channel->basic_consume("enviarEmail", "", false, false, false, false, array($this, "procesar"));
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

    public function listenPedimentos() {
        list(,, $consumerCount) = $this->channel->queue_declare("imprimirPedimento", false, true, false, false);
        if ($consumerCount > $this->consumerCount) {
            exit;
        }
        $this->channel->basic_qos(null, 1, null);
        $this->channel->basic_consume("imprimirPedimento", "", false, false, false, false, array($this, "pedimento"));
        while (count($this->channel->callbacks)) {
            $this->channel->wait();
        }
        $this->channel->close();
        $this->conn->close();
    }

    public function pedimento(AMQPMessage $msg) {
        $arr = unserialize($msg->body);
        if ($this->imprimirPedimento($arr["patente"], $arr["aduana"], $arr["pedimento"])) {
            $msg->delivery_info["channel"]->basic_ack($msg->delivery_info["delivery_tag"]);
        } else {
            $this->log->logEntry("Error", "Error found while processing {$arr["patente"]}-{$arr["aduana"]}-{$arr["pedimento"]}", "localhost", "RabbitMQ");
        }
    }

    public function procesar(AMQPMessage $msg) {
        if ($this->enviarEmail((int) $msg->body)) {
            $msg->delivery_info["channel"]->basic_ack($msg->delivery_info["delivery_tag"]);
        } else {
            $this->log->logEntry("Error", "Error found while sending {$msg->body}", "localhost", "RabbitMQ");
        }
    }

    private function imprimirPedimento($patente, $aduana, $pedimento) {
        try {
            $this->log->logEntry("Message arrived", "Imprimir Pedimento: {$patente}-{$aduana}-{$pedimento}", "localhost", "RabbitMQ");
            $misc = new OAQ_Misc();
            $db = $misc->sitawin($patente, $aduana);
            if (!isset($db)) {
                $this->log->logEntry("Warning", "No sytem found for {$patente}-{$aduana}", "localhost", "RabbitMQ");
                return;
            }
            $arr = $db->wsDetallePedimento($pedimento, $aduana);
            if (!isset($arr)) {
                $this->log->logEntry("Warning", "No data found for {$patente}-{$aduana}-{$pedimento}", "localhost", "RabbitMQ");
                return;
            }
            $basic = $db->pedimentoDatosBasicos($pedimento);
            if (isset($basic) && !empty($basic)) {
                $misc = new OAQ_Misc();
                $arr = $db->pedimentoCompleto($pedimento);
                if (!($path = $misc->nuevoDirectorio($this->appConfig->getParam("expdest"), $patente, $aduana, trim($arr["referencia"])))) {
                    $this->log->logEntry("Warning", "Unable to create directory " . $this->appConfig->getParam("expdest") . "/" . $patente . "/" . $aduana . "/" . trim($arr["referencia"]) . "", "localhost", "RabbitMQ");
                }
                $print = new OAQ_Print();
                $print->set_dir($path);
                $print->set_data($arr);
                $print->printPedimentoSitawin();
                $this->_agregarArchivoRepositorio($patente, $aduana, $pedimento, $arr["rfcCliente"], 32, trim($arr["referencia"]), $path, $print->get_filename());
                unset($arr);
                $arr = $db->pedimentoSimplicado($pedimento);
                $print->clearData();
                $print->set_data($arr);
                $print->printPedimentoSimplificadoSitawin();
                $this->_agregarArchivoRepositorio($patente, $aduana, $pedimento, $arr["rfcCliente"], 33, trim($arr["referencia"]), $path, $print->get_filename());
                return true;
            } else {
                $this->log->logEntry("No data found", "No basic data found for {$patente}-{$aduana}-{$pedimento}", "localhost", "RabbitMQ");
            }
        } catch (Exception $ex) {
            $this->log->logEntry("Exception found", (string) $ex->getMessage(), "localhost", "RabbitMQ");
            return;
        }
    }

    private function _agregarArchivoRepositorio($patente, $aduana, $pedimento, $rfcCliente, $tipoArchivo, $referencia, $ubicacion, $nombreArchivo) {
        if (file_exists($ubicacion . DIRECTORY_SEPARATOR . $nombreArchivo)) {
            $mapper = new Archivo_Model_Repositorio();
            $table = new Archivo_Model_Table_Repositorio();
            $table->setPatente($patente);
            $table->setAduana($aduana);
            $table->setPedimento($pedimento);
            $table->setReferencia($referencia);
            $table->setRfc_cliente($rfcCliente);
            $table->setTipo_archivo($tipoArchivo);
            $table->setUbicacion($ubicacion . DIRECTORY_SEPARATOR . $nombreArchivo);
            $table->setNom_archivo($nombreArchivo);
            $mapper->findFile($table);
            if (null === ($table->getId())) {
                $table->setCreado(date("Y-m-d H:i:s"));
                $mapper->save($table);
            }
        }
    }

    private function enviarEmail($id) {
        try {
            if (isset($id)) {
                $tbl = new Trafico_Model_NotificacionesMapper();
                $arr = $tbl->obtener($id);
                $emails = new OAQ_EmailsTraffic();
                $mapperContacts = new Trafico_Model_ContactosMapper();
                $users = new Usuarios_Model_UsuariosMapper();
                $para = $mapperContacts->para();
                $emails->addTo($para["email"], $para["email"]);
                if (isset($arr["para"])) {
                    $to = $users->getEmailById($arr["para"]);
                    $emails->addCc($to["email"], ucwords($to["nombre"]));
                }
                //$this->log->logEntry("Message arrived", "Enviar correo " . $id, "localhost", "RabbitMQ");
                if ($arr["tipo"] == "pago") {
                    /*$traffics = new Trafico_Model_TraficosMapper();
                    $traffic = new Trafico_Model_Table_Traficos();
                    $traffic->setId($arr["idTrafico"]);
                    $traffics->find($traffic);
                    if (null !== ($traffic->getId())) {
                        $to = $users->getEmailById($traffic->getIdUsuario());
                        if (isset($to) && !empty($to)) {
                            $emails->addCc($to["email"], ucwords($to["nombre"]));
                            $emails->addCc("everardo.martinez@oaq.com.mx", "Everardo Martinez");
                        } else {
                            $emails->addTo("everardo.martinez@oaq.com.mx", "Everardo Martinez");
                        }
                        $emails->setSubject("[" . $traffic->getPatente() . "-" . $traffic->getAduana() . "] Pago de pedimento: " . $traffic->getAduana() . "-" . $traffic->getPatente() . "-" . $traffic->getPedimento());
                        if ((int) $traffic->getAduana() == 240) {
                            $emails->addCc("pedro.rosales@tdqro.com", "Pedro Rosales");
                            $emails->addCc("grodriguez@tdqro.com", "Gerardo Rodriguez");
                            $emails->addCc("alejandro.sanchez@oaq.com.mx", "Alejandro Sanchez");
                        } elseif ((int) $traffic->getAduana() == 640) {
                            
                        }
                        $emails->addCc("cinthya.geha@oaq.com.mx", "Cinthya Geha");
                        $emails->addCc("karen.olvera@oaq.com.mx", "Karen Olvera");
                        $emails->pagoPedimento($traffic->getPatente(), $traffic->getAduana(), $traffic->getPedimento(), $traffic->getReferencia());
                    }*/
                } elseif ($arr["tipo"] == "nueva-solicitud") {
                    $emails->setSubject("[" . $arr["patente"] . "-" . $arr["aduana"] . "] Solicitud de anticipo ref: " . $arr["referencia"] . " ped. " . $arr["pedimento"]);
                    $this->_addContacts($emails, $mapperContacts->avisoSolicitud());
                    $emails->addCc("soporte@oaq.com.mx", "Soporte OAQ");
                    $emails->nuevaSolicitud($arr["contenido"]);
                } elseif ($arr["tipo"] == "deposito-solicitud") {
                    $emails->setSubject("[" . $arr["patente"] . "-" . $arr["aduana"] . "] Deposito de solicitud de anticipo ref: " . $arr["referencia"] . " ped. " . $arr["pedimento"]);
                    $this->_addContacts($emails, $mapperContacts->avisoGeneralDeposito());
                    $emails->avisoDeposito($arr["contenido"]);
                } elseif (preg_match('/^notificacion-comentario/', $arr["tipo"])) {
                    if (preg_match('/^notificacion-comentario$/', $arr["tipo"])) {
                        $emails->setSubject("[" . $arr["patente"] . "-" . $arr["aduana"] . "] Se agrego comentario ref: " . $arr["referencia"] . " ped. " . $arr["pedimento"]);
                    } else if (preg_match('/^notificacion-comentario-solicitud$/', $arr["tipo"])) {
                        $emails->setSubject("[" . $arr["patente"] . "-" . $arr["aduana"] . "] Se agrego comentario a solicitud ref: " . $arr["referencia"] . " ped. " . $arr["pedimento"]);
                    }
                    if (in_array($arr["aduana"], array(240, 800)) && in_array($arr["patente"], array(3589, 3574))) {
                        $this->_addContacts($emails, $mapperContacts->avisoComentario(7));
                    }
                    if (in_array($arr["aduana"], array(240)) && in_array($arr["patente"], array(3107))) {
                        $this->_addContacts($emails, $mapperContacts->avisoComentario(37));
                    }
                    if (in_array($arr["aduana"], array(240, 800)) && in_array($arr["patente"], array(3574))) {
                        $this->_addContacts($emails, $mapperContacts->avisoComentario(6));
                    }
                    $this->_addContacts($emails, $mapperContacts->avisoGeneralComentarios());
                    if (preg_match('/^notificacion-comentario-solicitud$/', $arr["tipo"])) {
                        $this->_addContacts($emails, $mapperContacts->avisoComentarioSolicitud(0));
                    }
                    $emails->avisoComentario($arr["contenido"]);
                }
                $this->_addBccContacts($emails, $mapperContacts->avisoSistemas());
                $emails->send();
                return true;
            } else {
                $this->log->logEntry("Exception found", "Not set Id.", "localhost", "RabbitMQ");
            }
        } catch (Exception $ex) {
            $this->log->logEntry("Exception found", (string) $ex->getMessage(), "localhost", "RabbitMQ");
            return;
        }
    }
    
    protected function _addContacts(OAQ_EmailsTraffic $emails, $contactos) {
        if (isset($contactos) && !empty($contactos)) {
            foreach ($contactos as $item) {
                $emails->addCc($item["email"], $item["nombre"]);
            }
        }
    }
    
    protected function _addBccContacts(OAQ_EmailsTraffic $emails, $contactos) {
        if (isset($contactos) && !empty($contactos)) {
            foreach ($contactos as $item) {
                $emails->addBcc($item["email"], $item["nombre"]);
            }
        }
    }

    /**
     * Process incoming request to generate pdf invoices and send them through 
     * email.
     */
    public function listen() {
        $connection = new AMQPStreamConnection("192.168.200.11", 5672, "admin", "rabbitmq11!");
        $channel = $connection->channel();
        $channel->queue_declare(
                "invoice_queue", #queue
                false, #passive
                true, #durable, make sure that RabbitMQ will never lose our queue if a crash occurs
                false, #exclusive - queues may only be accessed by the current connection
                false               #auto delete - the queue is deleted when all consumers have finished using it
        );

        /**
         * don"t dispatch a new message to a worker until it has processed and 
         * acknowledged the previous one. Instead, it will dispatch it to the 
         * next worker that is not still busy.
         */
        $channel->basic_qos(
                null, #prefetch size - prefetch window size in octets, null meaning "no specific limit"
                1, #prefetch count - prefetch window in terms of whole messages
                null    #global - global=null to mean that the QoS settings should apply per-consumer, global=true to mean that the QoS settings should apply per-channel
        );

        /**
         * indicate interest in consuming messages from a particular queue. When they do 
         * so, we say that they register a consumer or, simply put, subscribe to a queue.
         * Each consumer (subscription) has an identifier called a consumer tag
         */
        $channel->basic_consume(
                "invoice_queue", #queue
                "", #consumer tag - Identifier for the consumer, valid within the current channel. just string
                false, #no local - TRUE: the server will not send messages to the connection that published them
                false, #no ack, false - acks turned on, true - off.  send a proper acknowledgment from the worker, once we"re done with a task
                false, #exclusive - queues may only be accessed by the current connection
                false, #no wait - TRUE: the server will not respond to the method. The client should not wait for a reply method
                array($this, "process") #callback
        );

        while (count($channel->callbacks)) {
            $this->log->logEntry("example", "Waiting for incoming messages", "localhost", "RabbitMQ");
            $channel->wait();
        }

        $channel->close();
        $connection->close();
    }

    /**
     * process received request
     * 
     * @param AMQPMessage $msg
     */
    public function process(AMQPMessage $msg) {

        $this->generatePdf()->sendEmail();
        $this->log->logEntry("example", "Message arrived: " . (string) $msg->body, "localhost", "RabbitMQ");

        /**
         * If a consumer dies without sending an acknowledgement the AMQP broker 
         * will redeliver it to another consumer or, if none are available at the 
         * time, the broker will wait until at least one consumer is registered 
         * for the same queue before attempting redelivery
         */
        $msg->delivery_info["channel"]->basic_ack($msg->delivery_info["delivery_tag"]);
    }

    /**
     * Generates invoice"s pdf
     * 
     * @return WorkerReceiver
     */
    private function generatePdf() {
        /**
         * Mocking a pdf generation processing time.  This will take between
         * 2 and 5 seconds
         */
        sleep(mt_rand(2, 5));
        return $this;
    }

    /**
     * Sends email
     * 
     * @return WorkerReceiver
     */
    private function sendEmail() {
        /**
         * Mocking email sending time.  This will take between 1 and 3 seconds
         */
        sleep(mt_rand(1, 3));
        return $this;
    }

}
