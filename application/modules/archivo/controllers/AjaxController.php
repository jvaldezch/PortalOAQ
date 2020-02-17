<?php

class Archivo_AjaxController extends Zend_Controller_Action {

    protected $_session;
    protected $_config;
    protected $_appconfig;
    protected $_arch;

    public function init() {
        $this->_helper->layout->setLayout('bootstrap');
        $this->_appconfig = new Application_Model_ConfigMapper();
        $this->_config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini', APPLICATION_ENV);
    }

    public function preDispatch() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        if (!$this->getRequest()->isXmlHttpRequest()) {
            throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
        }
        $this->_session = NULL ? $this->_session = new Zend_Session_Namespace('') : $this->_session = new Zend_Session_Namespace($this->_config->app->namespace);
        if ($this->_session->authenticated == true) {
            $session = new OAQ_Session($this->_session, $this->_appconfig);
            $session->actualizar();
            $session->actualizarSesion();
        } else {
            $this->getResponse()->setRedirect($this->_appconfig->getParam('link-logout'));
        }
        $this->_arch = NULL ? $this->_arch = new Zend_Session_Namespace('') : $this->_arch = new Zend_Session_Namespace('Navigation');
        $this->_arch->setExpirationSeconds($this->_appconfig->getParam('session-exp'));
    }

    public function fileTypesAction() {
        $repo = new Archivo_Model_RepositorioMapper();
        $docs = new Archivo_Model_DocumentosMapper();
        $id = $this->getRequest()->getParam('id', null);
        $d = $docs->getAll();
        $type = $repo->getFileType($id);
        $html = '<select id="select_' . $id . '" class="traffic-select">';
        foreach ($d as $doc) {
            $html .= '<option value="' . $doc["id"] . '"'
                    . (($doc["id"] == $type) ? ' selected="selected"' : '')
                    . '>'
                    . $doc["nombre"]
                    . '</option>';
        }
        $html .= '</select>';
        echo $html;
    }

    public function changeFileTypeAction() {
        $sat = new OAQ_SATValidar();
        $repo = new Archivo_Model_RepositorioMapper();
        $id = $this->getRequest()->getParam('id', null);
        $type = $this->getRequest()->getParam('type', null);
        $updated = $repo->changeFileType($id, $type);
        if ($type == 29 || $type == 2) {
            $file = $repo->obtenerInfo($id);
            $filename = $repo->getFilePathById($id);
            $basename = basename($filename);
            if (preg_match('/.xml$/i', $basename)) {
                $xmlArray = $sat->satToArray(html_entity_decode(file_get_contents($filename)));
                if (isset($xmlArray["Addenda"]["operacion"])) {
                    $adenda = $sat->parametrosAdenda($xmlArray["Addenda"]["operacion"]);
                }
                $emisor = $sat->obtenerGenerales($xmlArray["Emisor"]);
                $receptor = $sat->obtenerGenerales($xmlArray["Receptor"]);
                $complemento = $sat->obtenerComplemento($xmlArray["Complemento"]);
                $data = array(
                    'tipo_archivo' => $type,
                    'emisor_rfc' => $emisor["rfc"],
                    'emisor_nombre' => $emisor["razonSocial"],
                    'receptor_rfc' => $receptor["rfc"],
                    'receptor_nombre' => $receptor["razonSocial"],
                    'folio' => $xmlArray["@attributes"]["folio"],
                    'fecha' => date('Y-m-d H:i:s', strtotime($xmlArray["@attributes"]["fecha"])),
                    'uuid' => $complemento["uuid"],
                    'observaciones' => isset($adenda["observaciones"]) ? $adenda["observaciones"] : null,
                );
                unset($xmlArray);
                $updated = $repo->actualizarFactura($id, $data);
                if ($updated) {
                    if (($idd = $repo->searchFileByName($file["patente"], $file["aduana"], pathinfo($basename, PATHINFO_FILENAME) . '.pdf'))) {
                        $repo->actualizarFactura($idd, $data);
                    }
                }
            }
        }
        $model = new Archivo_Model_DocumentosMapper();
        if ($updated) {
            $icons = "<img onclick=\"editarArchivo('" . $id . "');\" src=\"" . $this->view->baseUrl() . "/images/icons/small_edit.png\" style=\"cursor: pointer;\" />&nbsp;"
                    . "<img src=\"" . $this->view->baseUrl() . "/images/icons/small_delete.png\" onclick=\"borrarArchivo('" . $id . "');\" style=\"cursor: pointer;\" />";
            $repo->modificado($id, $this->_session->username);
            echo Zend_Json_Encoder::encode(array('success' => true, 'type' => $model->tipoDocumento($type), 'icons' => $icons));
            return false;
        } else {
            echo Zend_Json_Encoder::encode(array('success' => false));
            return false;
        }
    }

    public function verifyFileTypeAction() {
        $repo = new Archivo_Model_RepositorioMapper();
        $id = $this->getRequest()->getParam('id', null);
        $type = $repo->getFileType($id);
        if ($type >= 168 && $type <= 445) {
            echo Zend_Json_Encoder::encode(array('success' => true));
            return false;
        } else {
            echo Zend_Json_Encoder::encode(array('success' => false));
            return false;
        }
    }

    public function cancelEditAction() {
        try {
            $id = $this->getRequest()->getParam('id', null);
            $type = $this->getRequest()->getParam('type', null);
            $model = new Archivo_Model_DocumentosMapper();
            if (isset($id)) {
                $icons = "<a onclick=\"editarArchivo('{$id}');\"><div class=\"traffic-icon traffic-icon-edit\"></div></a>"
                        . "<a onclick=\"borrarArchivo('{$id}');\"><div class=\"traffic-icon traffic-icon-delete\"></div></a>";
                echo Zend_Json_Encoder::encode(array('success' => true, 'type' => $model->tipoDocumento($type), 'icons' => $icons));
                return false;
            } else {
                echo Zend_Json_Encoder::encode(array('success' => false));
                return false;
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function newFileUploadAction() {
        try {
            if (!file_exists('/home/samba-share/expedientes')) {
                mkdir('/home/samba-share/expedientes');
            }
            $folder = '/home/samba-share/expedientes' . DIRECTORY_SEPARATOR . $this->_arch->patente;
            if (!file_exists('/home/samba-share/expedientes' . DIRECTORY_SEPARATOR . $this->_arch->patente)) {
                mkdir('/home/samba-share/expedientes' . DIRECTORY_SEPARATOR . $this->_arch->patente);
            }
            if (!file_exists('/home/samba-share/expedientes' . DIRECTORY_SEPARATOR . $this->_arch->patente . DIRECTORY_SEPARATOR . $this->_arch->aduana)) {
                mkdir('/home/samba-share/expedientes' . DIRECTORY_SEPARATOR . $this->_arch->patente . DIRECTORY_SEPARATOR . $this->_arch->aduana);
            }
            if (!file_exists('/home/samba-share/expedientes' . DIRECTORY_SEPARATOR . $this->_arch->patente . DIRECTORY_SEPARATOR . $this->_arch->aduana . DIRECTORY_SEPARATOR . $this->_arch->referencia)) {
                mkdir('/home/samba-share/expedientes' . DIRECTORY_SEPARATOR . $this->_arch->patente . DIRECTORY_SEPARATOR . $this->_arch->aduana . DIRECTORY_SEPARATOR . $this->_arch->referencia);
            }
            $folder = '/home/samba-share/expedientes' . DIRECTORY_SEPARATOR . $this->_arch->patente . DIRECTORY_SEPARATOR . $this->_arch->aduana . DIRECTORY_SEPARATOR . $this->_arch->referencia;
            $request = $this->getRequest();
            if ($request->isPost()) {
                $data = $request->getPost();
                $adapter = new Zend_File_Transfer_Adapter_Http();
                $adapter->setDestination($folder)
                        ->addValidator('Extension', false, 'pdf,xml');
                if ($adapter->isValid()) {
                    $info = $adapter->getFileInfo();
                    if (!$adapter->receive()) {
                        return false;
                    }
                    $misc = new OAQ_Misc();
                    $noExtension = $misc->formatURL(substr($info["file"]["name"], 0, -4));
                    if (preg_match('/.pdf/i', $info["file"]["name"])) {
                        $newFile = $info["file"]["destination"] . DIRECTORY_SEPARATOR . $noExtension . '.pdf';
                    }
                    if (preg_match('/.xml/i', $info["file"]["name"])) {
                        $newFile = $info["file"]["destination"] . DIRECTORY_SEPARATOR . $noExtension . '.xml';
                    }
                    if (!rename($info["file"]["destination"] . DIRECTORY_SEPARATOR . $info["file"]["name"], $newFile)) {
                        throw new Exception("Rename failed!");
                    }
                    $repo = new Archivo_Model_RepositorioMapper();
                    $added = $repo->addNewFile(99, null, $this->_arch->referencia, $this->_arch->patente, $this->_arch->aduana, $noExtension, $newFile, $this->_session->username, null, isset($data["rfc-hidden"]) ? strtoupper(trim($data["rfc-hidden"])) : null);
                    if ($added === true) {
                        $this->_helper->json(array("success" => true));
                    }
                } else {
                    $this->_helper->json(array("success" => false));
                }
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function removeFileAction() {
        $id = $this->getRequest()->getParam("id", null);
        $repo = new Archivo_Model_RepositorioMapper();
        if ($id) {
            $path = $repo->getFilePathById($id);
            if (file_exists($path)) {
                unlink($path);
            }
            $removed = $repo->removeFileById($id);
            if ($removed === true) {
                $this->_helper->json(array("success" => true));
            } else {
                $this->_helper->json(array("success" => false));
            }
        } else {
            $this->_helper->json(array("success" => false));
        }
    }

    public function enviarVucemAction() {
        $misc = new OAQ_Misc();
        $vucem = new OAQ_VucemEnh();
        $repo = new Archivo_Model_RepositorioMapper();
        $edocMapper = new Vucem_Model_VucemEdocMapper();
        $firmante = new Vucem_Model_VucemFirmanteMapper();
        $id = $this->getRequest()->getParam('id', null);
        $solicitante = $this->getRequest()->getParam('rfc', null);
        $pedimento = $this->getRequest()->getParam('pedimento', null);
        $archivo = $repo->getFileById($id);
        if ($archivo) {
            if (file_exists($archivo["ubicacion"])) {
                $file = substr(basename($archivo["ubicacion"]), 0, -4);
                $noExtension = $misc->formatURL($file);
                $base64 = base64_encode(file_get_contents($archivo["ubicacion"]));
                $hash = sha1_file($archivo["ubicacion"]);
                $rfc = $firmante->obtenerDetalleFirmante($solicitante);
                $pkeyid = openssl_get_privatekey(base64_decode($rfc['spem']), $rfc['spem_pswd']);
                $cadena = $vucem->cadenaEdocument($solicitante, 'vucem@oaq.mx', $archivo["tipo_archivo"], $noExtension, "OAQ030623UL8", $hash);
                $signature = "";
                if (isset($rfc["sha"]) && $rfc["sha"] == 'sha256') {
                    openssl_sign($cadena, $signature, $pkeyid, OPENSSL_ALGO_SHA256);
                } else {
                    openssl_sign($cadena, $signature, $pkeyid);
                }
                $firma = base64_encode($signature);
                $uuid = $misc->getUuid($solicitante . $archivo["id"] . $archivo["patente"] . $archivo["aduana"] . $archivo["referencia"] . $hash . $noExtension);
                $folder = '/tmp' . DIRECTORY_SEPARATOR . 'envio-edocs';
                if (!file_exists($folder . DIRECTORY_SEPARATOR . $uuid . '.xml')) {
                    $xml = $vucem->envioEdocument($solicitante, $rfc["ws_pswd"], 'vucem@oaq.mx', $archivo["tipo_archivo"], $noExtension, "OAQ030623UL8", $base64, $rfc['cer'], $cadena, $firma);
                    // vucem
                    if (!file_exists($folder)) {
                        mkdir($folder, 0777, true);
                    }
                    $domxml = new DOMDocument('1.0');
                    $domxml->preserveWhiteSpace = false;
                    $domxml->formatOutput = true;
                    $domxml->loadXML($xml);
                    $domxml->save($folder . DIRECTORY_SEPARATOR . $uuid . '.xml');
                    unset($xml);
                }
                if (file_exists($folder . DIRECTORY_SEPARATOR . $uuid . '.xml')) {

                    $gm = new Application_Model_GearmanMapper();
                    $workerName = "edoc_worker.php";
                    $workerPath = $gm->getProcessPath($workerName);
                    if (isset($workerPath)) {
                        if (file_exists($workerPath)) {
                            $process = new Archivo_Model_PidMapper();
                            for ($i = 0; $i < $maxProcess; $i++) {
                                if (!($pids = $process->checkRunnigProcess($workerName))) {
                                    $newPid = shell_exec(sprintf('%s > /dev/null 2>&1 & echo $!', "php " . $workerPath));
                                    $process->addNewProcess($newPid, $workerName, "php " . $workerPath);
                                } else {
                                    foreach ($pids as $k => $p) {
                                        if (!$misc->isRunning($p['pid'])) {
                                            echo "{$p['pid']} is not runnig.\n";
                                            $process->deleteProcess($p['pid']);
                                            unset($pids[$k]);
                                        } else {
                                            echo "{$p['pid']} is runnig.\n";
                                        }
                                    }
                                    if (count($pids) < $maxProcess) {
                                        $newPid = shell_exec(sprintf('%s > /dev/null 2>&1 & echo $!', "php " . $workerPath));
                                        $process->addNewProcess($newPid, $workerName, "php " . $workerPath);
                                    }
                                }
                            }
                        } else {
                            return false;
                        }
                    } else {
                        return false;
                    }

                    $client = new GearmanClient();
                    $client->addServer('127.0.0.1', 4730);
                    $array = array(
                        'id' => $archivo["id"],
                        'file' => $folder . DIRECTORY_SEPARATOR . $uuid . '.xml',
                        'solicitante' => $solicitante,
                        'patente' => $archivo["patente"],
                        'aduana' => $archivo["aduana"],
                        'pedimento' => str_pad($pedimento, 7, '0', STR_PAD_LEFT),
                        'referencia' => $archivo["referencia"],
                        'uuid' => $uuid,
                        'hash' => $hash,
                        'cadena' => $cadena,
                        'firma' => $firma,
                        'base64' => $base64,
                        'tipoArchivo' => $archivo["tipo_archivo"],
                        'subTipoArchivo' => $archivo["sub_tipo_archivo"],
                        'nombreArchivo' => $noExtension . '.pdf',
                        'username' => $this->_session->username,
                        'email' => 'vucem@oaq.mx',
                    );
                    $client->addTaskBackground("edocreq", serialize($array));
                    $client->runTasks();
                }
            }
        }
    }

    protected function isRunning($pid) {
        try {
            $result = shell_exec(sprintf('ps %d', $pid));
            if (count(preg_split("/\n/", $result)) > 2) {
                return true;
            }
        } catch (Exception $e) {
            
        }
        return false;
    }
    
    public function actualizarExpedienteDatosAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception('Not an AJAX request detected');
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $flt = array(
                    "*" => array("StringTrim", "StripTags"),
                    "npatente" => "Digits",
                    "naduana" => "Digits",
                    "nreferencia" => "StringToUpper",
                    "nrfc" => "StringToUpper",
                );
                $vld = array(
                    "npatente" => array("NotEmpty", new Zend_Validate_Int(), array("stringLength", array("min" => 4, "max" => 4))),
                    "naduana" => array("NotEmpty", new Zend_Validate_Int(), array("stringLength", array("min" => 3, "max" => 3))),
                    "npedimento" => array("NotEmpty", array("stringLength", array("min" => 7, "max" => 7))),
                    "nreferencia" => array("NotEmpty", new Zend_Validate_Regex("/^[-_a-zA-Z0-9.\/]+$/")),
                    "oreferencia" => array("NotEmpty", new Zend_Validate_Regex("/^[-_a-zA-Z0-9.\/]+$/")),
                    "nrfc" => array("NotEmpty", new Zend_Validate_Regex("/^[a-zA-Z0-9]+$/")),
                );
                $input = new Zend_Filter_Input($flt, $vld, $request->getPost());
                if ($input->isValid("npatente") && $input->isValid("naduana") && $input->isValid("npedimento") && $input->isValid("nreferencia") && $input->isValid("nrfc") && $input->isValid("oreferencia")) {
                    $mapper = new Archivo_Model_RepositorioMapper();
                    $updated = $mapper->actualizarArchivos($input->npatente, $input->naduana, str_pad($input->npedimento, 7, '0', STR_PAD_LEFT), $input->nrfc, $input->nreferencia, $input->oreferencia, $this->_session->username);
                    if($updated) {
                        $this->_helper->json(array("success" => true, "href" => "/archivo/index/archivos-expediente?ref={$input->nreferencia}&patente={$input->npatente}&aduana={$input->naduana}"));
                    }
                    $this->_helper->json(array("success" => false));
                } else {
                    throw new Exception("Invalid input!");
                }
            } else {
                throw new Exception("Invalid request type!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function actualizarDatosExpedienteAction() {
        if (!$this->getRequest()->isXmlHttpRequest()) {
            throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
        }
        $request = $this->getRequest();
        if ($request->isPost()) {
            $post = $request->getPost();
            $filters = array(
                "*" => array("StringTrim", "StripTags"),
            );
            $input = new Zend_Filter_Input($filters, null, $post);
            if ($input->isValid()) {
                $data = $input->getEscaped();
                try {                    
                    $context = stream_context_create(array(
                        "ssl" => array(
                            "verify_peer" => false,
                            "verify_peer_name" => false,
                            "allow_self_signed" => true
                        )
                    ));
                    $con = new Application_Model_WsWsdl();
                    if ($data["patente"] == 3589 && preg_match('/64/', $data["aduana"])) {
                        if (($wsdl = $con->getWsdl(3589, 640, "sitawin"))) {
                            $soap = new Zend_Soap_Client($wsdl, array("stream_context" => $context));
                            $referencia = $soap->basicoReferencia($data["patente"], 640, $data["referencia"]);
                            if ($referencia === false) {
                                $referencia = $soap->basicoReferencia($data["patente"], 646, $data["referencia"]);
                            }
                            if ($referencia === false) {
                                $referencia = $soap->basicoReferencia($data["patente"], 645, $data["referencia"]);
                            }
                        }
                    }
                    if ($data["patente"] == 3589 && preg_match('/24/', $data["aduana"])) {
                        if (($wsdl = $con->getWsdl(3589, 240, "sitawin"))) {
                            $soap = new Zend_Soap_Client($wsdl, array("stream_context" => $context));
                            $referencia = $soap->basicoReferencia(3589, 240, $data["referencia"]);
                        }
                    }
                    if ($data["patente"] == 3574 && preg_match('/16/', $data["aduana"])) {
                        if (($wsdl = $con->getWsdl(3574, 160, "casa"))) {
                            $soap = new Zend_Soap_Client($wsdl, array("stream_context" => $context));
                            $referencia = $soap->basicoReferencia($data["patente"], 160, $data["referencia"]);
                        }
                    }
                    if ($data["patente"] == 3574 && preg_match('/24/', $data["aduana"])) {
                        if (($wsdl = $con->getWsdl(3574, 240, "aduanet"))) {
                            $soap = new Zend_Soap_Client($wsdl, array("stream_context" => $context));
                            $referencia = $soap->basicoReferencia($data["patente"], 240, $data["referencia"]);
                        }
                    }
                    if ($data["patente"] == 3589 && preg_match('/37/', $data["aduana"])) {
                        if (($wsdl = $con->getWsdl(3589, 370, "casa"))) {
                            $soap = new Zend_Soap_Client($wsdl, array("stream_context" => $context));
                            $referencia = $soap->basicoReferencia($data["patente"], 370, $data["referencia"]);
                        }
                    }
                    if ($data["patente"] == 3933 && preg_match('/43/', $data["aduana"])) {
                        if (($wsdl = $con->getWsdl(3933, 430, "aduanet"))) {
                            $soap = new Zend_Soap_Client($wsdl, array("stream_context" => $context));
                            $referencia = $soap->basicoReferencia(3933, 430, $data["referencia"]);
                        }
                    }
                    if ($data["patente"] == 3574 && preg_match('/47/', $data["aduana"])) {
                        if (($wsdl = $con->getWsdl(3574, 470, "casa"))) {
                            $soap = new Zend_Soap_Client($wsdl, array("stream_context" => $context));
                            $referencia = $soap->basicoReferenciaSecundario(3574, 470, $data["referencia"]);
                        }
                    }
                    if (isset($referencia) && !empty($referencia)) {
                        $this->_helper->json(array('success' => true, 'pedimento' => $referencia["pedimento"], 'rfcCliente' => $referencia["rfcCliente"]));
                    }
                } catch (Exception $ex) {
                    $exception = $ex->getMessage();
                    if (preg_match('/SOAP-ERROR: Parsing WSDL: Couldn/i', $exception)) {
                        $this->_helper->json(array('success' => false, 'html' => "<p><strong>[{$data["aduana"]}-{$data["patente"]}]:</strong> Lo sentimos el proveedor/corresponsal esta  presentando problemas de conectividad, favor de informar el indidente. Será necesario llenar los datos de forma manual. <br><strong>Problema con: </strong><em>{$wsdl}</em></p>"));
                    } else {
                        $this->_helper->json(array('success' => false, 'html' => "<p><strong>Error encontrado: </strong>$exception <br><strong>Informar a: </strong><a href=\"mailto:soporte@oaq.com.mx\">soporte@oaq.com.mx</a></p>"));
                    }
                    return false;
                }
            }
        }
    }

    public function bulkUploadAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $flt = array(
                    "*" => array("StringTrim", "StripTags"),
                    'type' => "Digits",
                );
                $vld = array(
                    "type" => array("NotEmpty", new Zend_Validate_Int()),
                );
                $input = new Zend_Filter_Input($flt, $vld, $request->getPost());
                if (!$input->isValid("type")) {
                    throw new Exception("Type not specified!");
                }
                $directory = "/tmp/archivos";
                if (!file_exists($directory)) {
                    mkdir($directory, 0777, true);
                }
                $upload = new Zend_File_Transfer_Adapter_Http();
                $upload->addValidator("Count", false, array("min" => 1, "max" => 20))
                        ->addValidator("Size", false, array('min' => '1kB', 'max' => '6MB'))
                        ->addValidator("Extension", false, array("extension" => "pdf", "case" => false));
                $upload->setDestination("/tmp/archivos");
                $files = $upload->getFileInfo();
                $uploaded = array();
                foreach ($files as $fieldname => $fileinfo) {
                    if (($upload->isUploaded($fieldname)) && ($upload->isValid($fieldname))) {
                        $upload->receive($fieldname);
                        if (file_exists($directory . DIRECTORY_SEPARATOR . $fileinfo["name"])) {
                            $uploaded[] = $this->_parseName($directory . DIRECTORY_SEPARATOR . $fileinfo["name"], null, $input->type);
                        }
                    }
                }
                if (isset($uploaded) && !empty($uploaded)) {
                    $mapper = new Archivo_Model_RepositorioTemporalMapper();
                    foreach ($uploaded as $item) {
                        $archivo = new Archivo_Model_RepositorioTemporal($item);
                        if (!($mapper->find($archivo))) {
                            $mapper->save($archivo);
                        }
                    }
                }
            }
            $this->_helper->json(array('success' => true));
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function sendFileAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $post = $request->getPost();
                $misc = new OAQ_Misc();
                $repo = new Archivo_Model_RepositorioMapper();
                $mapper = new Archivo_Model_RepositorioTemporalMapper();
                if (($array = $mapper->get($post["id"]))) {
                    if (!$repo->searchFileByName($array["patente"], $array["aduana"], $array["archivo"])) {
                        $folder = $misc->crearDirectorio($array["patente"], $array["aduana"], $array["referencia"]);
                        if (file_exists($folder)) {
                            if (copy($array["ubicacion"], $folder . DIRECTORY_SEPARATOR . $array["archivo"])) {
                                if (file_exists($folder . DIRECTORY_SEPARATOR . $array["archivo"])) {
                                    $added = $repo->addNewFile($array["tipoArchivo"], $array["subTipoArchivo"], $array["referencia"], $array["patente"], $array["aduana"], $array["archivo"], $folder . DIRECTORY_SEPARATOR . $array["archivo"], $this->_session->username, null, $array["rfcCliente"], $array["pedimento"]);
                                    if (isset($added) && $added === true) {
                                        $mapper->delete($post["id"]);
                                        unlink($array["ubicacion"]);
                                    }
                                }
                            }
                        }
                        if (isset($added) && $added === true) {
                            $this->_helper->json(array("success" => true, "id" => $post["id"], "html" => "Copiado."));
                        }
                    } else {
                        $this->_helper->json(array("success" => true, "id" => $post["id"], "html" => "Ya existe en repositorio."));
                    }
                }
                $this->_helper->json(array("success" => true, "id" => $post["id"], "html" => "Enviado."));
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }
    
    public function deleteFilesAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $flt = array(
                    "*" => array("StringTrim", "StripTags"),
                    "id" => "Digits",
                );
                $vld = array(
                    "id" => array("NotEmpty", new Zend_Validate_Int()),
                );
                $input = new Zend_Filter_Input($flt, $vld, $request->getPost());
                if ($input->isValid("id")) {
                    $mapper = new Archivo_Model_RepositorioTemporalMapper();
                    $file = $mapper->get($input->id);
                    if (file_exists($file["ubicacion"]) && is_readable($file["ubicacion"])) {
                        unlink($file["ubicacion"]);
                        $stmt = $mapper->delete($input->id);
                        if ($stmt === true) {
                            $this->_helper->json(array("success" => true, "id" => $input->id));
                        }
                    } else {
                        $stmt = $mapper->delete($input->id);
                        if ($stmt === true) {
                            $this->_helper->json(array("success" => true, "id" => $input->id));
                        }
                    }
                } else {
                    throw new Exception("Invalid input!");
                }
            } else {
                throw new Exception("Invalid request type!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    protected $pedimento;
    protected $rfcCliente;

    function getPedimento() {
        return $this->pedimento;
    }

    function getRfcCliente() {
        return $this->rfcCliente;
    }

    function setPedimento($pedimento) {
        $this->pedimento = $pedimento;
    }

    function setRfcCliente($rfcCliente) {
        $this->rfcCliente = $rfcCliente;
    }
    
    public function reloadFilesAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $mapper = new Archivo_Model_RepositorioTemporalMapper();
                $arr = $mapper->withNoInformation(9999);
                if (isset($arr) && !empty($arr)) {
                    foreach ($arr as $item) {
                        $files[] = $this->_parseName($item["ubicacion"], $item["id"]);
                    }
                    if (isset($files) && !empty($files)) {
                        foreach ($files as $file) {
                            $row = new Archivo_Model_RepositorioTemporal($file);
                            if (($mapper->findId($row)) == true) {
                                $mapper->save($row);
                            }
                        }
                        $this->_helper->json(array('success' => true));
                    } else {
                        $this->_helper->json(array('success' => false));
                    }
                } else {
                    throw new Exception("Nothing to process!");
                }
            } else {
                throw new Exception("Invalid request type!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    protected function _fileInformacion($patente, $aduana, $referencia) {
        try {
            $this->setPedimento(null);
            $this->setRfcCliente(null);
            $con = new Application_Model_WsWsdl();
            if ($patente == 3589 && preg_match('/64/', $aduana)) {
                $db = new OAQ_Sitawin(true, '192.168.0.253', 'sa', 'sqlcointer', 'SITAW3589640', 1433, 'Pdo_Mssql');
                $ref = $db->datosPedimento($referencia);
                if (!$ref) {
                    $db = new OAQ_Sitawin(true, "192.168.200.5", "sa", "adminOAQ123", "SITAW3010640", 1433, "Pdo_Mssql");
                    $ref = $db->datosPedimento($referencia);
                }
                if (!$ref) {
                    $db = new OAQ_Sitawin(true, "192.168.200.5", "sa", "adminOAQ123", "SITAW3589640R", 1433, "Pdo_Mssql");
                    $ref = $db->datosPedimento($referencia);
                }
            }
            if ($patente == 3589 && preg_match('/24/', $aduana)) {
                $db = new OAQ_Sitawin(true, '192.168.0.253', 'sa', 'sqlcointer', 'SITAW3589240', 1433, 'Pdo_Mssql');
                $ref = $db->datosPedimento($referencia);
                if (!$ref) {
                    $db = new OAQ_AduanetM3(true, 'localhost', 'root', 'mysql11!', 'SAAIWEB', 3306);
                    $ref = $db->basicoReferencia($referencia);
                }
            }
            if (isset($ref) && !empty($ref)) {
                $this->setPedimento($ref["pedimento"]);
                $this->setRfcCliente($ref["rfcCliente"]);
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    protected function _parseName($filename, $id = null, $type = 17) {
        try {
            $ref = explode('_', basename($filename));
            $referencia = $ref[0];
            if (strpos($referencia, '-') !== false && !preg_match('/^MI4/i', $referencia) && !preg_match('/^COI/i', $referencia) && !preg_match('/^GPI/i', $referencia)) {
                $referencia = substr($referencia, 0, strrpos($referencia, '-'));
            }
            if (preg_match('/^Q13/i', $referencia) || preg_match('/^OAQ/i', $referencia) || preg_match('/^QRO/i', $referencia) || preg_match('/^QM/i', $referencia) || preg_match('/^Q15/i', $referencia) || preg_match('/^Q14/i', $referencia) || preg_match('/^Q16/i', $referencia) || preg_match('/^Q17/i', $referencia)) {
                $patente = 3589;
                $aduana = 640;
            }
            if (preg_match('/^14TQ/i', $referencia)) {
                $patente = 3589;
                $aduana = 240;
            }
            if (preg_match('/^GPI/i', $referencia)) {
                $patente = 3574;
                $aduana = 470;
            }
            if (preg_match('/^MI4/i', $referencia) || preg_match('/^MI5/i', $referencia) || preg_match('/^ME4/i', $referencia) || preg_match('/^M14/i', $referencia)) {
                $patente = 3574;
                $aduana = 160;
            }
            if (preg_match('/^COI/i', $referencia)) {
                $patente = 3574;
                $aduana = 470;
            }
            if (isset($patente) && isset($aduana)) {
                $this->_fileInformacion($patente, $aduana, $referencia);
                return array(
                    'id' => isset($id) ? $id : null,
                    'archivo' => basename($filename),
                    'ubicacion' => $filename,
                    'referencia' => $referencia,
                    'patente' => $patente,
                    'aduana' => $aduana,
                    'pedimento' => $this->getPedimento(),
                    'rfcCliente' => $this->getRfcCliente(),
                    'usuario' => $this->_session->username,
                    'tipoArchivo' => $type,
                );
            } else {
                return array(
                    'id' => isset($id) ? $id : null,
                    'archivo' => basename($filename),
                    'ubicacion' => $filename,
                    'referencia' => $referencia,
                    'patente' => 9999,
                    'aduana' => 999,
                    'pedimento' => null,
                    'rfcCliente' => null,
                    'usuario' => $this->_session->username,
                    'tipoArchivo' => $type,
                );
            }
            return array();
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public function temporalFileAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $post = $request->getPost();
                $flt = array(
                    "*" => array("StringTrim", "StripTags"),
                    "id" => "Digits",
                    "action" => "StringToLower"
                );
                $vld = array(
                    "id" => array('NotEmpty', new Zend_Validate_Int()),
                    "action" => new Zend_Validate_InArray(array("delete", "edit", "cancel", "save")),
                );
                $input = new Zend_Filter_Input($flt, $vld, $request->getPost());
                if ($input->isValid("id") && $input->isValid("action")) {
                    $mapper = new Archivo_Model_RepositorioTemporalMapper();
                    $file = $mapper->get($input->id);
                    if ($input->action == "delete") {
                        if (file_exists($file["ubicacion"]) && is_readable($file["ubicacion"])) {
                            unlink($file["ubicacion"]);
                            $stmt = $mapper->delete($input->id);
                            if ($stmt === true) {
                                $this->_helper->json(array('success' => true));
                            }
                        } else {
                            $stmt = $mapper->delete($input->id);
                            if ($stmt === true) {
                                $this->_helper->json(array('success' => true));
                            }
                        }
                    } elseif ($input->action == "edit") {
                        if (isset($file["id"])) {
                            $html = "<td colspan=\"9\" style=\"margin:0; padding:0\">";
                            $html .= "<form id=\"formrow_{$file["id"]}\" method=\"post\" action=\"/archivo/ajax/temporal-file\" enctype=\"application/x-www-form-urlencoded\" >";
                            $html .= "<table style=\"margin:0; padding:0\">";
                            $html .= "<input type=\"hidden\" name=\"id\" value=\"{$file["id"]}\">";
                            $html .= "<input type=\"hidden\" name=\"action\" value=\"save\">";
                            $html .= "<td style=\"border-left: 0\">&nbsp;</td>";
                            $html .= "<td><input type=\"text\" name=\"patente\" style=\"width: 40px\" value=\"{$file["patente"]}\"></td>";
                            $html .= "<td><input type=\"text\" name=\"aduana\" style=\"width: 40px\" value=\"{$file["aduana"]}\"></td>";
                            $html .= "<td><input type=\"text\" name=\"pedimento\" style=\"width: 60px\" value=\"{$file["pedimento"]}\"></td>";
                            $html .= "<td><input type=\"text\" name=\"referencia\" style=\"width: 80px\" value=\"{$file["referencia"]}\"></td>";
                            $html .= "<td><input type=\"text\" name=\"rfcCliente\" style=\"width: 120px\" value=\"{$file["rfcCliente"]}\"></td>";
                            $html .= "<td><input type=\"text\" name=\"tipoArchivo\" style=\"width: 150px\" value=\"" . $this->view->tipoArchivo($file["tipoArchivo"]) . "\" disabled=\"disabled\"></td>";
                            $html .= "<td><input type=\"text\" name=\"archivo\" style=\"width: 250px\" value=\"{$file["archivo"]}\" disabled=\"disabled\"></td>";
                            $html .= "<td style=\"border-right: 0\"><img class=\"btn-icons\" src=\"/images/icons/cancel.png\" onclick=\"temporalFile({$input->id}, 'cancel');\" style=\"cursor: pointer\" /><div style=\"width: 8px; display: inline-block\"></div><img class=\"btn-icons\" src=\"/images/icons/disk.png\" onclick=\"temporalFile({$input->id}, 'save');\" style=\"cursor: pointer\" /></td>";
                            $html .= "</table>";
                            $html .= "</form>";
                            $html .= "</td>";
                        }
                        $this->_helper->json(array('success' => false, 'id' => $input->id, 'html' => $html));
                    } elseif ($input->action == "cancel") {
                        if (isset($file["id"])) {
                            $html = "<td>&nbsp;</td>";
                            $html .= "<td>{$file["patente"]}</td>";
                            $html .= "<td>{$file["aduana"]}</td>";
                            $html .= "<td>{$file["pedimento"]}</td>";
                            $html .= "<td>{$file["referencia"]}</td>";
                            $html .= "<td>{$file["rfcCliente"]}</td>";
                            $html .= "<td>" . $this->view->tipoArchivo($file["tipoArchivo"]) . "</td>";
                            $html .= "<td>{$file["archivo"]}</td>";
                            $html .= "<td>";
                            if ($file["patente"] == 9999 && $file["aduana"] == 999) {
                                $html .= "<img class=\"btn-icons\" src=\"/images/icons/small_edit.png\" onclick=\"temporalFile({$file["id"]}, 'edit');\"  style=\"cursor: pointer\"/><div style=\"width: 8px; display: inline-block\"></div>";
                            }
                            $html .= "<img class=\"btn-icons\" src=\"/images/icons/small_delete.png\" onclick=\"temporalFile({$file["id"]}, 'delete');\" style=\"cursor: pointer\"/>";
                            $html .= "</td>";
                        }
                        $this->_helper->json(array('success' => false, 'id' => $input->id, 'html' => $html));
                    } elseif ($input->action == "save") {
                        $table = new Archivo_Model_RepositorioTemporal();
                        if (isset($post["id"])) {
                            $table->setId($post["id"]);
                            $mapper->getFile($table);
                            if (isset($post["pedimento"])) {
                                $table->setPedimento($post["pedimento"]);
                            }
                            if (isset($post["rfcCliente"])) {
                                $table->setRfcCliente($post["rfcCliente"]);
                            }
                            if (isset($post["patente"])) {
                                $table->setAduana($post["patente"]);
                            }
                            if (isset($post["aduana"])) {
                                $table->setAduana($post["aduana"]);
                            }
                            if (isset($post["referencia"])) {
                                $table->setReferencia($post["referencia"]);
                            }
                            $mapper->save($table);
                            $this->_helper->json(array('success' => false));
                        }
                    } else {
                        
                    }
                    $this->_helper->json(array('success' => false));
                } else {
                    throw new Exception("Invalid input!");
                }
            } else {
                throw new Exception("Invalid request type!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function obtenerAduanasAction() {
        try {
            $request = $this->getRequest();
            $f = array("*" => array("StringTrim", "StripTags"), "id" => "Digits", "patente" => "Digits");
            $v = array("*" => "NotEmpty", "id" => array("NotEmpty", new Zend_Validate_Int()), "patente" => array("Alnum", array("stringLength", array("min" => 4, "max" => 4))));
            $input = new Zend_Filter_Input($f, $v, $request->getPost());
            if ($input->isValid("id") && $input->isValid("patente")) {
                $html = $this->view->aduanas("aduana", $input->patente, null, $input->id);
                $this->_helper->json(array("success" => true, "html" => $html));
            } else {
                throw new Exception("Invalid request type!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function descargaArchivoValidacionAction() {
        try {
            $request = $this->getRequest();
            if ($request->isPost()) {
                $filters = array(
                    '*' => array('StringTrim', 'StripTags'),
                );
                $input = new Zend_Filter_Input($filters, null, $request->getPost());
                if ($input->isValid("arr")) {
                    $mapper = new Automatizacion_Model_ArchivosValidacionMapper();
                    $files = $mapper->filesContent($input->arr);
                    $zipfile = "/tmp" . DIRECTORY_SEPARATOR . sha1(time()) . ".zip";
                    $zip = new ZipArchive;
                    $res = $zip->open($zipfile, ZipArchive::CREATE);
                    if ($res === true) {
                        foreach ($files as $file) {
                            $zip->addFromString($file["archivoNombre"], base64_decode($file["contenido"]));
                        }
                        $zip->close();
                        $this->_helper->json(array('success' => true, "filename" => pathinfo($zipfile, PATHINFO_FILENAME)));
                    }
                    $this->_helper->json(array('success' => false));
                } else {
                    throw new Exception("Invalid input!");
                }
            } else {
                throw new Exception("Invalid request type!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }
    
    public function nuevoFtpAction() {
        if (!$this->getRequest()->isXmlHttpRequest()) {
            throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
        }
        try {
            $request = $this->getRequest();
            if ($request->isPost()) {
                $flt = array(
                    "*" => array("StringTrim", "StripTags"),
                    "type" => "StringToLower",
                    "rfc" => "StringToUpper",
                    "port" => "Digits",
                );
                $vld = array(
                    "*" => "NotEmpty",
                    "type" => new Zend_Validate_InArray(array("m3", "expedientes")),
                    "rfc" => array(new Zend_Validate_Regex('/^[-_a-zA-Z0-9.]+$/')),
                    "port" => array(new Zend_Validate_Int()),
                );
                $input = new Zend_Filter_Input($flt, $vld, $request->getPost());
                if ($input->isValid("type") && $input->isValid("rfc") && $input->isValid("port")) {
                    $mapper = new Archivo_Model_FtpMapper();
                    $table = new Archivo_Model_Table_Ftp($input->getEscaped());
                    $table->setActive(1);
                    if(null == ($mapper->find($table))) {
                        $mapper->save($table);
                        $this->_helper->json(array("success" => true));
                    } else {
                        $this->_helper->json(array("success" => false, "message" => "Ftp ya existe."));
                    }
                    $this->_helper->json(array("success" => false));
                } else {
                    throw new Exception("Invalid input!");
                }
            } else {
                throw new Exception("Invalid request type!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }
    
    public function archivosExpedienteAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "patente" => "Digits",
                    "aduana" => "Digits",
                    "referencia" => "StringToUpper",
                );
                $v = array(
                    "*" => "NotEmpty",
                    "patente" => array(new Zend_Validate_Int()),
                    "aduana" => array(new Zend_Validate_Int()),
                    "referencia" => array(new Zend_Validate_Regex('/^[-_a-zA-Z0-9.\/]+$/')),
                );
                $input = new Zend_Filter_Input($f, $v, $request->getPost());
                if ($input->isValid("patente") && $input->isValid("aduana") && $input->isValid("referencia")) {
                    $view = new Zend_View();
                    $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/ajax/");
                    $view->setHelperPath(realpath(dirname(__FILE__)) . "/../views/helpers/");
                    $model = new Archivo_Model_RepositorioMapper();
                    if(!in_array($this->_session->role, array("inhouse", "cliente"))) {
                        $files = $model->getFilesByReferenceUsers($input->referencia, $input->patente, $input->aduana);
                    } else {
                        $files = $model->getFilesByReferenceCustomers($input->referencia, $input->patente, $input->aduana);                        
                    }
                    if($this->_session->role == "super") {
                        $view->canDelete = true;
                    }
                    $view->files = $files;
                    $this->_helper->json(array("success" => true, "html" => $view->render("archivos-expediente.phtml")));
                }
            } else {
                throw new Exception("Invalid request type!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }
    
    public function archivosValidacionAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                );
                $v = array(
                    "patente" => new Zend_Validate_Int(),
                    "aduana" => new Zend_Validate_Int(),
                    "pedimento" => new Zend_Validate_Int()
                );
                $i = new Zend_Filter_Input($f, $v, $request->getPost());
                if ($i->isValid("patente") && $i->isValid("aduana") && $i->isValid("pedimento")) {
                    $view = new Zend_View();
                    $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/data/");
                    $val = new Automatizacion_Model_ArchivosValidacionPedimentosMapper();
                    $validacion = $val->obtenerUltimo($i->patente, $i->pedimento, $i->aduana);
                    if (isset($validacion) && !empty($validacion)) {
                        $view->archivom = $validacion;
                    }
                    $pre = new Automatizacion_Model_ArchivosValidacionFirmasMapper();
                    $firma = $pre->obtenerUltimo($i->patente, $i->pedimento);
                    if (isset($firma) && !empty($firma)) {
                        $view->archivov = $firma;
                    }
                    $pag = new Automatizacion_Model_ArchivosValidacionPagosMapper();
                    $pago = $pag->findFile($i->patente, $i->aduana, $i->pedimento);
                    if (isset($pago) && !empty($pago)) {
                        $view->archivop = $pago;
                    }
                    $this->_helper->json(array("success" => true, "html" => $view->render("register.phtml")));
                } else {
                    throw new Exception("Invalid input!");
                }
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function borrarExpedienteAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "patente" => "Digits",
                    "aduana" => "Digits",
                    "referencia" => "StringToUpper",
                );
                $v = array(
                    "*" => "NotEmpty",
                    "patente" => array(new Zend_Validate_Int()),
                    "aduana" => array(new Zend_Validate_Int()),
                    "referencia" => array(new Zend_Validate_Regex('/^[-_a-zA-Z0-9.\/]+$/')),
                );
                $input = new Zend_Filter_Input($f, $v, $request->getPost());
                if ($input->isValid("patente") && $input->isValid("aduana") && $input->isValid("referencia")) {
                    $model = new Archivo_Model_Repositorio();
                    if (isset($input->arr) && is_array($input->arr)) {
                        if ($model->borrarExpediente($input->arr)) {
                            $model->borrarVacio($input->patente, $input->aduana, $input->referencia);
                            $this->_helper->json(array("success" => true));
                        }
                        $this->_helper->json(array("success" => false));
                    } else {
                        $model->borrarVacio($input->patente, $input->aduana, $input->referencia);
                        $this->_helper->json(array("success" => true));
                    }
                    $this->_helper->json(array("success" => false));
                }
            } else {
                throw new Exception("Invalid request type!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }
    
    public function guardarChecklistAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $r = $this->getRequest();
            if ($r->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "idTrafico" => "Digits",
                    "patente" => "Digits",
                    "aduana" => "Digits",
                    "completo" => "Digits",
                    "revisionOperaciones" => "Digits",
                    "revisionAdministracion" => "Digits",
                    "referencia" => "StringToUpper",
                );
                $v = array(
                    "*" => "NotEmpty",                    
                    "idTrafico" => array(new Zend_Validate_Int()),
                    "patente" => array(new Zend_Validate_Int()),
                    "aduana" => array(new Zend_Validate_Int()),
                    "completo" => array("NotEmpty", new Zend_Validate_Int()),
                    "revisionOperaciones" => array("NotEmpty", new Zend_Validate_Int()),
                    "revisionAdministracion" => array("NotEmpty", new Zend_Validate_Int()),
                    "referencia" => array(new Zend_Validate_Regex('/^[-_a-zA-Z0-9.\/]+$/')),
                );
                $i = new Zend_Filter_Input($f, $v, $r->getPost());
                if ($i->isValid("patente") && $i->isValid("aduana") && $i->isValid("referencia")) {                    
                    $checklist = new OAQ_Checklist();
                    $data = $r->getPost();
                    if($i->isValid("idTrafico")) {
                        unset($data["idTrafico"]);                        
                    }
                    unset($data["patente"]);
                    unset($data["aduana"]);
                    unset($data["pedimento"]);
                    unset($data["referencia"]);
                    unset($data["observaciones"]);
                    if(isset($data["completo"])) {
                        unset($data["completo"]);
                    }
                    if(isset($data["revisionOperaciones"])) {
                        unset($data["revisionOperaciones"]);
                    }
                    if(isset($data["revisionAdministracion"])) {
                        unset($data["revisionAdministracion"]);
                    }
                    $row = new Archivo_Model_Table_ChecklistReferencias();
                    $table = new Archivo_Model_ChecklistReferencias();
                    $rev = $checklist->revision($this->_session->username, $this->_session->nombre, "elaboro", $this->_session->role);
                    if($i->isValid("idTrafico")) {
                        $row->setIdTrafico($i->idTrafico);                        
                    }
                    $row->setPatente($i->patente);
                    $row->setAduana($i->aduana);
                    $row->setReferencia($i->referencia);
                    $row->setPedimento(str_pad($i->pedimento, 7, '0', STR_PAD_LEFT));
                    $row->setObservaciones($i->observaciones);
                    $table->find($row);
                    if ($i->isValid("completo")) {
                        if($i->completo == 1) {
                            $index->actualizarChecklist($i->idRepo, array("revisionAdministracion" => 1, "revisionOperaciones" => 1, "completo" => 1, "modificado" => date("Y-m-d H:i:s"), "modificadoPor" => $this->_session->username));
                            $row->setRevisionOperaciones(1);
                            $row->setRevisionAdministracion(1);
                            $row->setCompleto(1);                            
                            $row->setFechaCompleto(date("Y-m-d H:i:s"));                            
                        }
                    } else {
                        if ($i->isValid("revisionOperaciones")) {
                            $row->setRevisionOperaciones($i->revisionOperaciones);
                            $row->setFechaRevisionOperaciones(date("Y-m-d H:i:s"));
                        }
                        if ($i->isValid("revisionAdministracion")) {
                            $row->setRevisionAdministracion($i->revisionAdministracion);
                            $row->setFechaRevisionAdministracion(date("Y-m-d H:i:s"));
                        }
                    }
                    if (null === ($row->getId())) {
                        $row->setChecklist(json_encode($data));
                        $row->setRevision(json_encode($rev));
                        $row->setCreado(date("Y-m-d H:i:s"));
                        $table->save($row);
                        $this->_helper->json(array("success" => true, "message" => "added"));
                    } else {                        
                        $current = json_decode($row->getChecklist(), true);
                        $currentRev = json_decode($row->getRevision(), true);
                        $checklist->setNew($data);
                        $checklist->setCurrent($current);
                        $save = $checklist->actualizarChecklist();                        
                        $saveRev = $checklist->actualizarRevision($currentRev, $rev);
                        $row->setRevision(json_encode($saveRev));
                        $row->setChecklist(json_encode($save));
                        $row->setObservaciones($i->observaciones);
                        $row->setCompleto($i->completo);
//                        if ($i->isValid("completo")) {
//                            if($i->completo == 1) {
//                                $row->setCompleto(1);
//                                $row->setRevisionOperaciones(1);
//                                $row->setRevisionAdministracion(1);
//                            }
//                        } else {
//                            $row->setRevisionOperaciones($i->revisionOperaciones);
//                            $row->setRevisionAdministracion($i->revisionAdministracion);
//                        }
                        $row->setActualizado(date("Y-m-d H:i:s"));
                        $table->update($row);
                        $this->_helper->json(array("success" => true, "message" => "updated"));
                    }
                    $this->_helper->json(array("success" => false));
                } else {
                    throw new Exception("Invalid input!");
                }
            } else {
                throw new Exception("Invalid request type!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

}
