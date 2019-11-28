<?php

class Automatizacion_FtpController extends Zend_Controller_Action {

    protected $_config;

    public function init() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_config = new Zend_Config_Ini(APPLICATION_PATH . "/configs/application.ini", APPLICATION_ENV);
    }

    public function enviarExpedienteAction() {
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "id" => "Digits",
            );
            $v = array(
                "id" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $i = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($i->isValid("id")) {
                $index = new Archivo_Model_RepositorioIndex();
                $arr = $index->datos($i->id);
                if (count($arr)) {
                    $misc = new OAQ_Misc();
                    if (isset($arr["rfcCliente"])) {
                        $ftp = new Automatizacion_Model_FtpMapper();
                        $servers = $ftp->getByType("expedientes", $arr["rfcCliente"]);
                        if (empty($servers)) {
                            $this->_helper->json(array("success" => false, "message" => "No hay servidor FTP para RFC: {$arr["rfcCliente"]}, favor de crear un ticket en nuestros sistemas."));
                        } else {
                            $ftp = new OAQ_Ftp(array(
                                "host" => $servers[0]["url"],
                                "port" => $servers[0]["port"],
                                "username" => $servers[0]["user"],
                                "password" => $servers[0]["password"],
                            ));
                            if (true !== ($conn = $ftp->connect())) {
                                $this->_helper->json(array("success" => false, "message" => $conn));
                            }
                            $ftp->disconnect();
                        }
                        $repo = new Archivo_Model_RepositorioMapper();
                        $client = new GearmanClient();
                        $client->addServer("127.0.0.1", 4730);
                        foreach ($servers as $server) {
                            $refNoEnviadas = $repo->referenciasParaEnviar($arr["rfcCliente"], $arr["referencia"]);
                            if (empty($refNoEnviadas)) {
                                continue;
                            }
                            foreach ($refNoEnviadas as $referencia) {
                                $noEnviado = $repo->archivosDeReferencia($arr["referencia"], null, true);
                                if (!empty($noEnviado)) {
                                    foreach ($noEnviado as $notSent) {
                                        $array = array(
                                            "idRepo" => $notSent["id"],
                                            "idFtp" => $server["id"],
                                            "rfc" => $i->rfc,
                                            "patente" => $referencia["patente"],
                                            "aduana" => $referencia["aduana"],
                                            "pedimento" => $referencia["pedimento"],
                                            "referencia" => $referencia["referencia"],
                                        );
                                        $client->addTaskBackground("enviar", serialize($array));
                                    }
                                    $client->runTasks();
                                }
                            }
                            if (!empty($refNoEnviadas)) {
                                $arr = array();
                                foreach ($refNoEnviadas as $item) {
                                    $arr[] = array(
                                        "patente" => $item["patente"],
                                        "aduana" => $item["aduana"],
                                        "pedimento" => $item["pedimento"],
                                        "referencia" => $item["referencia"],
                                        "rfc_cliente" => $item["rfc_cliente"],
                                    );
                                }
                                $misc->newBackgroundWorker("ftp_worker", 1);
                                $this->_helper->json(array("success" => true, "data" => $arr));
                            } else {
                                $this->_helper->json(array("success" => false, "message" => "No se encontraron archivos para enviar"));
                            }
                        }
                    }
                }
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function enviarRepositorioFtpAction() {
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "id" => "Digits",
            );
            $v = array(
                "id" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("id")) {
                $ftpMppr = new Archivo_Model_FtpMapper();
                $idx = new Archivo_Model_RepositorioIndex();
                $arr = $idx->datos($input->id);
                if (!empty($arr)) {
                    $ftp = $ftpMppr->buscar($arr["rfcCliente"], "expedientes");
                    if (!empty($ftp)) {
                        $sender = new OAQ_WorkerFtpSender("ftp");
                        $sender->ftpExpedientes($input->id);
                        $this->_helper->json(array("success" => true, "message" => "Los archivos serán sincronizados en los proximos minutos. Gracias."));
                    } else {
                        throw new Exception("No existe FTP configurado en nuestro sistema para el cliente con RFC {$arr["rfcCliente"]}, si lo necesita favor de crear un ticket de soporte. Le recordamos que la configuración de este servicio no es automático.");
                    }
                } else {
                    throw new Exception("No se encontro información del expediente.");
                }
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function listenFtpAction() {
        $worker = new OAQ_WorkerFtpReceiver("ftp");
        $worker->listenFtp();
    }

    public function listenAction() {
        $f = array(
            "*" => array("StringTrim", "StripTags"),
            "id" => "Digits",
        );
        $v = array(
            "id" => array("NotEmpty", new Zend_Validate_Int()),
        );
        $i = new Zend_Filter_Input($f, $v, $this->_request->getParams());
        if ($i->isValid("id")) {
            $index = new Archivo_Model_RepositorioIndex();
            $arr = $index->datos((int) $i->id);
            $ftp = new Automatizacion_Model_FtpMapper();
            $server = $ftp->obtenerDatosFtp($arr["rfcCliente"]);
            $folderName = $arr["pedimento"] . "_" . $arr["referencia"];
            if (isset($server["nombreCarpeta"]) && $server["nombreCarpeta"] !== null) {
                eval($server["nombreCarpeta"]);
            }
            if (isset($server["transferMode"]) && $server["transferMode"] == "passive") {
                //$this->ftp->setPassive();
            }
            var_dump($server);
            var_dump($folderName);
        }
    }

    /**
     * /automatizacion/ftp/enviar-expedientes-cliente?id=121&fecha=2018-07-02
     * 
     * @throws Exception
     */
    public function enviarExpedientesClienteAction() {
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "id" => "Digits",
            );
            $v = array(
                "id" => array("NotEmpty", new Zend_Validate_Int()),
                "fecha" => array(new Zend_Validate_Regex("/^\d{4}\-\d{2}\-\d{2}$/")),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("id")) {
                $mppr = new Trafico_Model_ClientesMapper();
                $row = $mppr->datosCliente($input->id);
                if (!empty($row)) {
                    
                    $mdl = new Automatizacion_Model_FtpMapper();
                    $repo = new Archivo_Model_RepositorioMapper();
                    $server = $mdl->getByType("expedientes", $row["rfc"]);

                    if (!empty($server[0])) {
                        
                        $ftp = new OAQ_Ftp(array(
                            "host" => $server[0]["url"],
                            "port" => $server[0]["port"],
                            "username" => $server[0]["user"],
                            "password" => $server[0]["password"],
                        ));
                        
                        if (true !== ($conn = $ftp->connect())) {
                            $this->_helper->json(array("success" => false, "message" => $conn));
                        }
                        
                        $trafficos = new Trafico_Model_TraficosMapper();
                        $arr = $trafficos->traficosClientes($input->id, $row["rfc"], $input->isValid("fecha") ? $input->fecha : date("Y-m-d"));
                        
                        $prefijos = new Archivo_Model_RepositorioPrefijos();
                        
                        $uploaded = array();
                        if (!empty($arr)) {
                            
                            for($i = 0; $i < count($arr); $i++) {
                                $uploaded[$i] = array(
                                    "patente" => $arr[$i]["patente"],
                                    "aduana" => $arr[$i]["aduana"],
                                    "referencia" => $arr[$i]["referencia"],
                                    "archivos" => array(),
                                );
                                
                                $array = $repo->archivosNoEnviadosFtp($arr[$i]["patente"], $arr[$i]["aduana"], $arr[$i]["referencia"], $arr[$i]["rfcCliente"]);
                                
                                if (!empty($array)) {
                                    $path = $server[0]["remoteFolder"] . $ftp->estructuraDirectorio($input->id, $arr[$i]);
                                    $ftp->createRecursiveRemoteFolder($path);
                                    if ($ftp->changeRemoteDirectory($path)) {
                                        foreach ($array as $file) {
                                            if (!($prefijo = $prefijos->obtenerPrefijo($file["tipo_archivo"]))) {
                                                $prefijo = "";
                                            }
                                            if ($prefijo !== "" && preg_match("/^{$prefijo}/", $file["nom_archivo"])) {
                                                $prefijo = "";
                                            }
                                            if (in_array($arr[$i]["rfcCliente"], array("STE071214BE7"))) {
                                                $fup = $ftp->upload($file["ubicacion"], $prefijo);
                                            } else {
                                                $fup = $ftp->upload($file["ubicacion"]);
                                            }
                                            if ($fup) {
                                                $uploaded[$i]["archivos"][] = $fup;
                                            }
                                        }
                                    }
                                }
                            }
                            
                        }
                        
                        $ftp->disconnect();
                        $this->_helper->json(array("success" => true, "message" => $uploaded));
                    } else {
                        throw new Exception("No FTP server found for " . $row["rfc"]);
                    }
                } else {
                    throw new Exception("No record found on database.");
                }
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }
    
    public function envioManualAction() {
        
        $frontController = Zend_Controller_Front::getInstance();
        $frontController->setParam('disableOutputBuffering', true);
        
        ob_implicit_flush(true); ob_flush(); ob_end_flush();
        
        ob_clean();

        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "id" => "Digits",
            );
            $v = array(
                "id" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("id")) {
                
                $prefijos = new Archivo_Model_RepositorioPrefijos();
                
                $trafico = new OAQ_Trafico(array("idTrafico" => $input->id));
                $arr = $trafico->obtenerDatos();
                
                $customer_mppr = new Trafico_Model_ClientesMapper();
                $customer = $customer_mppr->datosCliente($arr['idCliente']);
                
                $server_mppr = new Automatizacion_Model_FtpMapper();
                $server = $server_mppr->obtenerDatosFtp($customer["rfc"]);
                
                $ftp = new OAQ_Ftp(array(
                    "host" => $server["url"],
                    "port" => $server["port"],
                    "username" => $server["user"],
                    "password" => $server["password"],
                ));
                
                $status_array = array();
                
                if (true !== ($conn = $ftp->connect())) {
                    sleep(1);
                    ob_clean();
                    $status_array['connected'] = false;
                    echo json_encode(array("success" => true, "results" => $status_array)) . "\n";
                    ob_flush(); flush();
                    
                    exit();
                }                
                
                sleep(1);
                ob_clean();
                $status_array['connected'] = true;
                echo json_encode(array("success" => true, "results" => $status_array)) . "\n";
                ob_flush(); flush();
                
                if ($arr) {
                        
                    $repo_mppr = new Archivo_Model_RepositorioMapper();
                    $files_arr = $repo_mppr->archivosNoEnviadosFtp($arr['patente'], $arr['aduana'], $arr['referencia'], $arr['rfcCliente']);
                    
                    for($i = 0; $i < count($files_arr); $i++) {
                        sleep(1);
                        if (file_exists($files_arr[$i]['ubicacion'])) {
                            
                            $path = $server["remoteFolder"] . $ftp->estructuraDirectorio($trafico->getIdCliente(), $arr);
                            
                            $ftp->createRecursiveRemoteFolder($path);
                            if ($ftp->changeRemoteDirectory($path)) {
                                    if (!($prefijo = $prefijos->obtenerPrefijo($files_arr[$i]["tipo_archivo"]))) {
                                        $prefijo = "";
                                    }
                                    if ($prefijo !== "" && preg_match("/^{$prefijo}/", $files_arr[$i]["nom_archivo"])) {
                                        $prefijo = "";
                                    }
                                    if (in_array($files_arr[$i]["rfcCliente"], array("STE071214BE7"))) {
                                        $fup = $ftp->upload($files_arr[$i]["ubicacion"], $prefijo);
                                    } else {
                                        $fup = $ftp->upload($files_arr[$i]["ubicacion"]);
                                    }
                                    if ($fup) {
                                        $repo_mppr->ftpSent($files_arr[$i]['id']);
                                        $status_array['ftp_file_' . $files_arr[$i]['id']] = 1;
                                    }
                            }
                            
                            //$status_array['ftp_file_' . $file['id']] = 1;
                            
                            
                        } else {
                            $status_array['ftp_file_' . $files_arr[$i]['id']] = 4;
                        }
                        echo json_encode(array("success" => true, "results" => $status_array)) . "\n";
                        ob_flush(); flush();
                    }
                    
                }
                
                $ftp->disconnect();
                
                sleep(1);
                ob_clean();
                $status_array['disconnected'] = true;
                echo json_encode(array("success" => true, "results" => $status_array)) . "\n";
                ob_flush(); flush();
                
                exit();
                
                /*$array = array();
                
                sleep(1);
                ob_clean();
                $array['estatus'] = "connected";
                echo json_encode(array("success" => true, "results" => $array)) . "\n";
                //echo "<p>Connected</p>";
                ob_flush(); flush();
                
                
                sleep(5);
                ob_clean();
                $array['ftp_file_2324315'] = 1;
                echo json_encode(array("success" => true, "results" => $array)) . "\n";
                //echo "<p>Disconnected</p>";
                ob_flush(); flush();
                
                
                sleep(5);
                $array['ftp_file_2324977'] = 1;
                echo json_encode(array("success" => true, "results" => $array)) . "\n";
                //echo "<p>Disconnected</p>";
                ob_flush(); flush();*/
                
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

}
