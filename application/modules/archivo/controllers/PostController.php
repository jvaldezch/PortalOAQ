<?php

class Archivo_PostController extends Zend_Controller_Action {

    protected $_session;
    protected $_config;
    protected $_appconfig;
    protected $_firephp;

    public function init() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_appconfig = new Application_Model_ConfigMapper();
        $this->_config = new Zend_Config_Ini(APPLICATION_PATH . "/configs/application.ini", APPLICATION_ENV);
        $this->_firephp = Zend_Registry::get("firephp");
    }

    public function preDispatch() {
        $this->_session = NULL ? $this->_session = new Zend_Session_Namespace("") : $this->_session = new Zend_Session_Namespace($this->_config->app->namespace);
        if ($this->_session->authenticated == true) {
            $session = new OAQ_Session($this->_session, $this->_appconfig);
            $session->actualizar();
            $session->actualizarSesion();
        } else {
            $this->getResponse()->setRedirect($this->_appconfig->getParam("link-logout"));
        }
    }

    public function archivosDeExpedienteAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "id" => array("Digits"),
                );
                $v = array(
                    "id" => array("NotEmpty", new Zend_Validate_Int()),
                );
                $input = new Zend_Filter_Input($f, $v, $request->getPost());
                if ($input->isValid("id")) {
                    $view = new Zend_View();
                    $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/post/");
                    $view->setHelperPath(realpath(dirname(__FILE__)) . "/../views/helpers/");
                    $referencias = new OAQ_Referencias();
                    $res = $referencias->restricciones($this->_session->id, $this->_session->role);
                    $index = new Archivo_Model_RepositorioIndex();
                    $arr = $index->datos($input->id, $res["idsAduana"], $res["rfcs"]);
                    if (count($arr)) {
                        
                        $model = new Archivo_Model_RepositorioMapper();
                        if (!in_array($this->_session->role, array("inhouse", "cliente", "proveedor"))) {
                            
                            $files = $model->getFilesByReferenceUsers($arr["referencia"], $arr["patente"], $arr["aduana"]);
                            
                        } else if (in_array($this->_session->role, array("proveedor"))) {
                            if ($this->_session->role == "proveedor") {
                                $view->disableUpload = true;
                            }
                            $files = $model->obtener($arr["referencia"], $arr["patente"], $arr["aduana"], json_decode($res["documentos"]));
                        } else if (in_array($this->_session->role, array("inhouse"))) {
                            $files = $model->obtener($arr["referencia"], $arr["patente"], $arr["aduana"], json_decode($res["documentos"]));
                        } else if (in_array($this->_session->role, array("cliente"))) {
                            $files = $model->getFilesByReferenceCustomers($arr["referencia"], $arr["patente"], $arr["aduana"]);
                        }
                        
                        if ($this->_session->role == "super") {
                            $view->canDelete = true;
                        }
                        $view->files = $files;
                        
                        // validacion
                        if (!in_array($this->_session->role, array("inhouse", "cliente", "proveedor"))) {
                            $val = new OAQ_ArchivosValidacion();
                            if (isset($arr["pedimento"])) {
                                $view->validacion = $val->archivosDePedimento($arr["patente"], $arr["aduana"], $arr["pedimento"]);
                            }
                        }
                        // complementos
                        $complementos = $model->obtenerComplementos($arr["referencia"], $arr["patente"], $arr["aduana"]);
                        if (!empty($complementos)) {
                            $view->complementos = $complementos;
                        }                      
                        $this->_helper->json(array("success" => true, "html" => $view->render("archivos-de-expediente.phtml")));
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

    public function borrarAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "id" => array("Digits"),
                    "idRepo" => array("Digits"),
                );
                $v = array(
                    "id" => array("NotEmpty", new Zend_Validate_Int()),
                    "idRepo" => array("NotEmpty", new Zend_Validate_Int()),
                );
                $input = new Zend_Filter_Input($f, $v, $request->getPost());
                if ($input->isValid("id")) {
                    $repo = new Archivo_Model_RepositorioMapper();
                    $path = $repo->getFilePathById($input->id);
                    if (file_exists($path)) {
                        unlink($path);
                    }
                    if ($repo->removeFileById($input->id)) {
                        $index = new Archivo_Model_RepositorioIndex();
                        $index->modificacion($input->idRepo, $this->_session->username);
                        $this->_helper->json(array("success" => true));
                    } else {
                        $this->_helper->json(array("success" => true));
                    }
                }
                $this->_helper->json(array("success" => false, "message" => "Invalid input!"));
            } else {
                throw new Exception("Invalid request type!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function guardarAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "id" => array("Digits"),
                    "idRepo" => array("Digits"),
                    "tipo" => array("Digits"),
                );
                $v = array(
                    "id" => array("NotEmpty", new Zend_Validate_Int()),
                    "idRepo" => array("NotEmpty", new Zend_Validate_Int()),
                    "tipo" => array("NotEmpty", new Zend_Validate_Int()),
                );
                $input = new Zend_Filter_Input($f, $v, $request->getPost());
                if ($input->isValid("id") && $input->isValid("tipo")) {
                    $sat = new OAQ_SATValidar();
                    $repo = new Archivo_Model_RepositorioMapper();
                    $model = new Archivo_Model_DocumentosMapper();
                    if ($repo->changeFileType($input->id, $input->tipo) == true) {
                        $index = new Archivo_Model_RepositorioIndex();
                        $index->modificacion($input->idRepo, $this->_session->username);
                    }
                    if ($input->tipo == 29 || $input->tipo == 2) {
                        $file = $repo->obtenerInfo($input->id);
                        $filename = $repo->getFilePathById($input->id);
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
                                "tipo_archivo" => $input->tipo,
                                "emisor_rfc" => $emisor["rfc"],
                                "emisor_nombre" => $emisor["razonSocial"],
                                "receptor_rfc" => $receptor["rfc"],
                                "receptor_nombre" => $receptor["razonSocial"],
                                "folio" => $xmlArray["@attributes"]["folio"],
                                "fecha" => date('Y-m-d H:i:s', strtotime($xmlArray["@attributes"]["fecha"])),
                                "uuid" => $complemento["uuid"],
                                "observaciones" => isset($adenda["observaciones"]) ? $adenda["observaciones"] : null,
                            );
                            unset($xmlArray);
                            $updated = $repo->actualizarFactura($input->id, $data);
                            if ($updated) {
                                if (($idd = $repo->searchFileByName($file["patente"], $file["aduana"], pathinfo($basename, PATHINFO_FILENAME) . ".pdf"))) {
                                    $repo->actualizarFactura($idd, $data);
                                }
                            }
                        }
                    }
                    $this->_helper->json(array("success" => true, "id" => $input->id, "tipo" => $model->tipoDocumento($input->tipo)));
                }
            } else {
                throw new Exception("Invalid request type!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function cancelarAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "id" => array("Digits"),
                );
                $v = array(
                    "id" => array("NotEmpty", new Zend_Validate_Int()),
                );
                $input = new Zend_Filter_Input($f, $v, $request->getPost());
                if ($input->isValid("id")) {
                    $repo = new Archivo_Model_RepositorioMapper();
                    $tipo = $repo->getFileType($input->id);
                    $model = new Archivo_Model_DocumentosMapper();
                    $this->_helper->json(array("success" => true, "tipo" => $model->tipoDocumento($tipo)));
                }
            } else {
                throw new Exception("Invalid request type!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function tiposDeArchivoAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "id" => array("Digits"),
                );
                $v = array(
                    "id" => array("NotEmpty", new Zend_Validate_Int()),
                );
                $input = new Zend_Filter_Input($f, $v, $request->getPost());
                if ($input->isValid("id")) {
                    $repo = new Archivo_Model_RepositorioMapper();
                    $mapper = new Archivo_Model_DocumentosMapper();
                    $arr = $mapper->getAll();
                    $html = new V2_Html();
                    $html->select("traffic-select-large", "select_" . $input->id);
                    $html->addSelectOption("", "---");
                    $tipo = $repo->getFileType($input->id);
                    foreach ($arr as $item) {
                        if ($tipo == $item["id"]) {
                            $html->addSelectOption($item["id"], $item["nombre"], true);
                        } else {
                            $html->addSelectOption($item["id"], $item["nombre"]);
                        }
                    }
                    $this->_helper->json(array("success" => true, "id" => $input->id, "html" => $html->getHtml()));
                }
            } else {
                throw new Exception("Invalid request type!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function checklistAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "id" => array("Digits"),
                    "idTrafico" => array("Digits"),
                );
                $v = array(
                    "id" => array("NotEmpty", new Zend_Validate_Int()),
                    "idTrafico" => array("NotEmpty", new Zend_Validate_Int()),
                );
                $input = new Zend_Filter_Input($f, $v, $request->getPost());
                $index = new Archivo_Model_RepositorioIndex();
                if ($input->isValid("id")) {
                    $idRepo = $input->id;
                }
                if (!$input->isValid("id") && $input->isValid("idTrafico")) {
                    $arri = $index->buscarPorTrafico($input->idTrafico);
                    if ($arri) {
                        $idRepo = $arri;
                    } else {
                        $tra = new Trafico_Model_TraficosMapper();
                        $ar = $tra->obtenerPorId($input->idTrafico);
                        $index->agregarDesdeTrafico($input->idTrafico, $ar["idAduana"], $ar["rfcCliente"], $ar["patente"], $ar["aduana"], $ar["pedimento"], $ar["referencia"], $this->_session->username);
                    }
                }
                $view = new Zend_View();
                $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/post/");
                $view->setHelperPath(realpath(dirname(__FILE__)) . "/../views/helpers/");
                $arr = $index->datos($idRepo);
                if (count($arr)) {
                    $view->idRepo = $idRepo;
                    $view->idTrafico = $arr["idTrafico"];
                    $view->patente = $arr["patente"];
                    $view->aduana = $arr["aduana"];
                    $view->pedimento = $arr["pedimento"];
                    $view->referencia = $arr["referencia"];
                    $row = new Archivo_Model_Table_ChecklistReferencias();
                    $table = new Archivo_Model_ChecklistReferencias();
                    $row->setPatente($arr["patente"]);
                    $row->setAduana($arr["aduana"]);
                    $row->setReferencia($arr["referencia"]);
                    $row->setPedimento($arr["pedimento"]);
                    $table->find($row);
                    $model = new Trafico_Model_TraficoAduanasMapper();
                    $idAduana = $model->idAduana($arr["patente"], $arr["aduana"]);
                    if (null !== ($row->getId())) {
                        $view->data = json_decode($row->getChecklist());
                        $view->observaciones = $row->getObservaciones();
                        $view->completo = $row->getCompleto();
                        $view->revOp = $row->getRevisionOperaciones();
                        $view->revAdm = $row->getRevisionAdministracion();
                    }
                    if (isset($idAduana)) {
                        $checklist = new OAQ_Checklist();
                        if ($row->getCreado()) {
                            $view->preguntas = $checklist->obtenerChecklist($this->_session->role, $row->getCreado());
                        } else {
                            $view->preguntas = $checklist->obtenerChecklist($this->_session->role, date("Y-m-d"));
                        }
                    }
                    if ($input->isValid("idTrafico")) {
                        $traffic = new OAQ_Trafico(array("idTrafico" => $input->idTrafico));
                        $view->idTrafico = $input->idTrafico;
                        $view->cvePedimento = $traffic->getClavePedimento();
                    }
                    $view->admin = true;
                    $view->operacion = true;
                    $view->administracion = true;                    

                    $repo = new Archivo_Model_RepositorioMapper();
                    $tipos = $repo->obtenerTiposArchivosReferencia($arr["referencia"]);
                    if (isset($tipos)) {
                        $view->tipos = $tipos;
                    }
                    $log = new Archivo_Model_ChecklistReferenciasBitacora();
                    $logs = $log->obtener($arr["patente"], $arr["aduana"], $arr["pedimento"], $arr["referencia"]);
                    if (isset($logs) && !empty($logs)) {
                        $view->bitacora = $logs;
                    }
                }
                $this->_helper->json(array("success" => true, "html" => $view->render("checklist.phtml")));
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
                    "idRepo" => "Digits",
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
                    "idRepo" => array(new Zend_Validate_Int()),
                    "idTrafico" => array(new Zend_Validate_Int()),
                    "patente" => array(new Zend_Validate_Int()),
                    "aduana" => array(new Zend_Validate_Int()),
                    "completo" => array("NotEmpty", new Zend_Validate_Int()),
                    "revisionOperaciones" => array("NotEmpty", new Zend_Validate_Int()),
                    "revisionAdministracion" => array("NotEmpty", new Zend_Validate_Int()),
                    "referencia" => array(new Zend_Validate_Regex('/^[-_a-zA-Z0-9.\/]+$/')),
                );
                $i = new Zend_Filter_Input($f, $v, $r->getPost());
                if ($i->isValid("patente") && $i->isValid("aduana") && $i->isValid("referencia") && $i->isValid("idRepo")) {
                    $index = new Archivo_Model_RepositorioIndex();
                    
                    $log = new Archivo_Model_ChecklistReferenciasBitacora();
                    
                    $checklist = new OAQ_Checklist();
                    $data = $r->getPost();
                    if ($i->isValid("idTrafico")) {
                        unset($data["idTrafico"]);
                    }
                    unset($data["patente"]);
                    unset($data["aduana"]);
                    unset($data["pedimento"]);
                    unset($data["referencia"]);
                    unset($data["observaciones"]);
                    if (isset($data["completo"])) {
                        unset($data["completo"]);
                    }
                    if (isset($data["revisionOperaciones"])) {
                        unset($data["revisionOperaciones"]);
                    }
                    if (isset($data["revisionAdministracion"])) {
                        unset($data["revisionAdministracion"]);
                    }
                    $row = new Archivo_Model_Table_ChecklistReferencias();
                    $table = new Archivo_Model_ChecklistReferencias();
                    
                    $rev = $checklist->revision($this->_session->username, $this->_session->nombre, "elaboro", $this->_session->role);
                    if ($i->isValid("idTrafico")) {
                        
                        $row->setIdTrafico($i->idTrafico);
                        
                        $trafico = new OAQ_Trafico(array("idTrafico" => $i->idTrafico, "idUsuario" => $this->_session->id));
                        
                    }
                    $row->setPatente($i->patente);
                    $row->setAduana($i->aduana);
                    $row->setReferencia($i->referencia);
                    $row->setPedimento(str_pad($i->pedimento, 7, '0', STR_PAD_LEFT));
                    $row->setObservaciones($i->observaciones);
                    $table->find($row);
                    
                    
                    if ($i->isValid("completo")) {
                        if ($i->completo == 1) {
                            $index->actualizarChecklist($i->idRepo, array("revisionAdministracion" => 1, "revisionOperaciones" => 1, "completo" => 1, "modificado" => date("Y-m-d H:i:s"), "modificadoPor" => $this->_session->username));
                            $row->setRevisionOperaciones(1);
                            $row->setRevisionAdministracion(1);
                            $row->setCompleto(1);
                            $row->setFechaCompleto(date("Y-m-d H:i:s"));
                            $log->agregar(array(
                                'patente' => $i->patente,
                                'aduana' => $i->aduana,
                                'pedimento' => str_pad($i->pedimento, 7, '0', STR_PAD_LEFT),
                                'referencia' => $i->referencia,
                                'usuario' => $this->_session->username,
                                'bitacora' => "EXPEDIENTE COMPLETO",
                                'creado' => date("Y-m-d H:i:s"),
                            ));
                            if (isset($trafico)) {
                                $array = array(
                                    "idRepositorio" => $i->idRepo,
                                    "revisionAdministracion" => 1,
                                    "revisionOperaciones" => 1,
                                    "completo" => 1,
                                );
                                $trafico->actualizar($array);
                            }
                        }
                    } else {
                        if ($i->isValid("revisionOperaciones")) {
                            $index->actualizarChecklist($i->idRepo, array("revisionOperaciones" => 1, "modificado" => date("Y-m-d H:i:s"), "modificadoPor" => $this->_session->username));
                            $row->setRevisionOperaciones($i->revisionOperaciones);
                            $row->setFechaRevisionOperaciones(date("Y-m-d H:i:s"));                            
                            $log->agregar(array(
                                'patente' => $i->patente,
                                'aduana' => $i->aduana,
                                'pedimento' => str_pad($i->pedimento, 7, '0', STR_PAD_LEFT),
                                'referencia' => $i->referencia,
                                'usuario' => $this->_session->username,
                                'bitacora' => "REVISADO POR OPERACIONES",
                                'creado' => date("Y-m-d H:i:s"),
                            ));
                            if (isset($trafico)) {
                                $trafico->actualizar(array("idRepositorio" => $i->idRepo, "revisionOperaciones" => 1));
                            }
                        }
                        if ($i->isValid("revisionAdministracion")) {
                            $index->actualizarChecklist($i->idRepo, array("revisionAdministracion" => 1, "modificado" => date("Y-m-d H:i:s"), "modificadoPor" => $this->_session->username));
                            $row->setRevisionAdministracion($i->revisionAdministracion);
                            $row->setFechaRevisionAdministracion(date("Y-m-d H:i:s"));
                            $log->agregar(array(
                                'patente' => $i->patente,
                                'aduana' => $i->aduana,
                                'pedimento' => str_pad($i->pedimento, 7, '0', STR_PAD_LEFT),
                                'referencia' => $i->referencia,
                                'usuario' => $this->_session->username,
                                'bitacora' => "REVISADO POR ADMINISTRACIÓN",
                                'creado' => date("Y-m-d H:i:s"),
                            ));
                            if (isset($trafico)) {
                                $trafico->actualizar(array("idRepositorio" => $i->idRepo, "revisionAdministracion" => 1));
                            }
                        }
                    }
                    if (null === ($row->getId())) {
                        $row->setChecklist(json_encode($data));
                        $row->setRevision(json_encode($rev));
                        $row->setCreado(date("Y-m-d H:i:s"));
                        $table->save($row);
                        $this->_helper->json(array("success" => true, "message" => "added"));
                    } else {
                        if ($i->isValid("revisionOperaciones")) {
                            $index->actualizarChecklist($i->idRepo, array("revisionOperaciones" => 1, "modificado" => date("Y-m-d H:i:s"), "modificadoPor" => $this->_session->username));
                            if (isset($trafico)) {
                                $trafico->actualizar(array("idRepositorio" => $i->idRepo, "revisionOperaciones" => 1));
                            }
                        }
                        if ($i->isValid("revisionAdministracion")) {
                            $index->actualizarChecklist($i->idRepo, array("revisionAdministracion" => 1, "modificado" => date("Y-m-d H:i:s"), "modificadoPor" => $this->_session->username));
                            if (isset($trafico)) {
                                $trafico->actualizar(array("idRepositorio" => $i->idRepo, "revisionAdministracion" => 1));
                            }
                        }
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

    public function subirArchivosExpedienteAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "id" => array("Digits"),
                );
                $v = array(
                    "id" => array("NotEmpty", new Zend_Validate_Int()),
                );
                $input = new Zend_Filter_Input($f, $v, $request->getPost());
                if ($input->isValid("id")) {
                    $index = new Archivo_Model_RepositorioIndex();
                    $arr = $index->datos($input->id);
                    if (count($arr)) {
                        $misc = new OAQ_Misc();
                        $misc->set_baseDir($this->_appconfig->getParam("expdest"));
                        $model = new Archivo_Model_RepositorioMapper();
                        $upload = new Zend_File_Transfer_Adapter_Http();
                        $upload->addValidator("Count", false, array("min" => 1, "max" => 15))
                                ->addValidator("Size", false, array("min" => "1", "max" => "20MB"));
                        if (($path = $misc->nuevoDirectorioExpediente($arr["patente"], $arr["aduana"], $misc->trimUpper($arr["referencia"])))) {
                            $upload->setDestination($path);
                        }
                        $files = $upload->getFileInfo();
                        foreach ($files as $fieldname => $fileinfo) {
                            if (($upload->isUploaded($fieldname)) && ($upload->isValid($fieldname))) {
                                if (preg_match('/\.(pdf|xml|xls|xlsx|doc|docx|zip|bmp|tif|jpe?g|bmp|png|msg)(?:[\?\#].*)?$/i', $fileinfo["name"])) {
                                    $tipoArchivo = $misc->tipoArchivo(basename($fileinfo["name"]));                                    
                                    if ($tipoArchivo == 99) {
                                        unlink($fileinfo["name"]);
                                        continue;
                                    }                                    
                                    $filename = $misc->formatFilename($fileinfo["name"], false);
                                    $verificar = $model->verificarArchivo($arr["patente"], $misc->trimUpper($arr["referencia"]), $filename);
                                    if ($verificar == false) {
                                        $upload->receive($fieldname);
                                        if (($misc->renombrarArchivo($path, $fileinfo["name"], $filename))) {
                                            if (file_exists($path . DIRECTORY_SEPARATOR . $filename)) {
                                                $model->nuevoArchivo($tipoArchivo, null, $arr["patente"], $arr["aduana"], str_pad($arr["pedimento"], 7, '0', STR_PAD_LEFT), $misc->trimUpper($arr["referencia"]), $filename, $path . DIRECTORY_SEPARATOR . $filename, $this->_session->username, $arr["rfcCliente"]);
                                            }
                                        }
                                    } else {
                                        $errors[] = array(
                                            "filename" => $fileinfo["name"],
                                            "errors" => array("errors" => "El archivo ya existe."),
                                        );
                                    }
                                } else {
                                    if (preg_match('/^m[0-9]{7}.[0-9]{3}$/i', $fileinfo["name"]) || preg_match('/^m[0-9]{7}.err$/i', $fileinfo["name"]) || preg_match('/^a[0-9]{7}.[0-9]{3}$/i', $fileinfo["name"]) ||  preg_match('/^e[0-9]{7}.[0-9]{3}$/i', $fileinfo["name"])) {
                                        $upload->receive($fieldname);
                                        $insert = array(
                                            "tipo_archivo" => 9999,
                                            "sub_tipo_archivo" => null,
                                            "patente" => $arr["patente"],
                                            "aduana" => $arr["aduana"],
                                            "pedimento" => str_pad($arr["pedimento"], 7, '0', STR_PAD_LEFT),
                                            "referencia" => $misc->trimUpper($arr["referencia"]),
                                            "nom_archivo" => trim($fileinfo["name"]),
                                            "ubicacion" => $path . DIRECTORY_SEPARATOR . trim($fileinfo["name"]),
                                            "rfc_cliente" => $arr["rfcCliente"],
                                            "creado" => date("Y-m-d H:i:s"),
                                            "usuario" => $this->_session->username,
                                        );
                                        if (preg_match('/^m[0-9]{7}.[0-9]{3}$/i', $fileinfo["name"])) {
                                            $insert["tipo_archivo"] = 1010;
                                        }
                                        if (preg_match('/^m[0-9]{7}.err$/i', $fileinfo["name"])) {
                                            $insert["tipo_archivo"] = 1020;
                                        }
                                        if (preg_match('/^a[0-9]{7}.[0-9]{3}$/i', $fileinfo["name"])) {
                                            $insert["tipo_archivo"] = 1030;
                                        }
                                        if (preg_match('/^e[0-9]{7}.([0-9]{3})$/i', $fileinfo["name"])) {
                                            $insert["tipo_archivo"] = 1030;
                                        }                                       
                                        if(!($model->verificarArchivo($arr["patente"], $misc->trimUpper($arr["referencia"]), $fileinfo["name"]))) {
                                            $this->_firephp->info($insert);
                                            $model->agregar($insert);
                                        } else {                                            
                                            $errors[] = array(
                                                "filename" => $fileinfo["name"],
                                                "errors" =>  "File exists",
                                            );
                                        }
                                    } else {
                                        $errors[] = array(
                                            "filename" => $fileinfo["name"],
                                            "errors" =>  "No rule matched",
                                        );
                                    }
                                }
                            } else {
                                $error = $upload->getErrors();
                                $errors[] = array(
                                    "filename" => $fileinfo["name"],
                                    "errors" => $error,
                                );
                            }
                        }
                        $index->modificacion($input->id, $this->_session->username);
                        $this->_helper->json(array("success" => true, "errors" => $errors));
                    } else {
                        $this->_helper->json(array("success" => false));
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

    public function editarExpedienteAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "id" => array("Digits"),
                );
                $v = array(
                    "id" => array("NotEmpty", new Zend_Validate_Int()),
                );
                $input = new Zend_Filter_Input($f, $v, $request->getPost());
                if ($input->isValid("id")) {
                    $view = new Zend_View();
                    $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/post/");
                    $view->setHelperPath(realpath(dirname(__FILE__)) . "/../views/helpers/");
                    $model = new Application_Model_UsuariosAduanasMapper();
                    $patentes = $model->patentesUsuario($this->_session->id);
                    $form = new Archivo_Form_NuevaReferencia(array("patentes" => $patentes, "id" => $input->id));
                    $form->rfc_cliente->setAttrib("style", "width: 100px");
                    $index = new Archivo_Model_RepositorioIndex();
                    $arr = $index->datos($input->id);
                    $view->id = $input->id;
                    $view->patente = $arr["patente"];
                    $view->aduana = $arr["aduana"];
                    if (count($arr)) {
                        $form->populate(array(
                            "patente" => $arr["patente"],
                            "rfc_cliente" => $arr["rfcCliente"],
                            "referencia" => $arr["referencia"],
                            "pedimento" => $arr["pedimento"],
                        ));
                    }
                    $view->form = $form;
                    $this->_helper->json(array("success" => true, "html" => $view->render("editar-expediente.phtml")));
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

    public function moverExpedienteAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "id" => array("Digits"),
                );
                $v = array(
                    "id" => array("NotEmpty", new Zend_Validate_Int()),
                );
                $input = new Zend_Filter_Input($f, $v, $request->getPost());
                if ($input->isValid("id")) {
                    $view = new Zend_View();
                    $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/post/");
                    $view->setHelperPath(realpath(dirname(__FILE__)) . "/../views/helpers/");
                    $model = new Application_Model_UsuariosAduanasMapper();
                    $patentes = $model->patentesUsuario($this->_session->id);
                    $formSource = new Archivo_Form_NuevaReferencia(array("patentes" => $patentes, "id" => $input->id));
                    $formSource->rfc_cliente->setAttrib("style", "width: 100px");
                    $formDestiny = new Archivo_Form_NuevaReferencia(array("patentes" => $patentes, "id" => $input->id));
                    $formDestiny->rfc_cliente->setAttrib("style", "width: 100px");
                    $index = new Archivo_Model_RepositorioIndex();
                    $arr = $index->datos($input->id);
                    $view->id = $input->id;
                    $view->patente = $arr["patente"];
                    $view->aduana = $arr["aduana"];
                    if (count($arr)) {
                        $formSource->populate(array(
                            "patente" => $arr["patente"],
                            "rfc_cliente" => $arr["rfcCliente"],
                            "referencia" => $arr["referencia"],
                            "pedimento" => str_pad($arr["pedimento"], 7, '0', STR_PAD_LEFT),
                        ));
                        $formSource->patente->setAttrib("disabled", "true");
                        $formSource->rfc_cliente->setAttrib("disabled", "true");
                        $formSource->referencia->setAttrib("disabled", "true");
                        $formSource->pedimento->setAttrib("disabled", "true");
                        $formDestiny->populate(array(
                            "patente" => $arr["patente"],
                            "rfc_cliente" => $arr["rfcCliente"],
                            "referencia" => $arr["referencia"],
                            "pedimento" => str_pad($arr["pedimento"], 7, '0', STR_PAD_LEFT),
                        ));
                    }
                    $view->formSource = $formSource;
                    $view->formDestiny = $formDestiny;
                    $repo = new Archivo_Model_RepositorioMapper();
                    if (!in_array($this->_session->role, array("inhouse", "cliente"))) {
                        $files = $repo->countFilesByReferenceUsers($arr["referencia"], $arr["patente"], $arr["aduana"]);
                    } else {
                        $files = $repo->countFilesByReferenceCustomers($arr["referencia"], $arr["patente"], $arr["aduana"]);
                    }
                    if (empty($files)) {
                        $view->empty = true;
                    }
                    $this->_helper->json(array("success" => true, "html" => $view->render("mover-expediente.phtml")));
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

    public function actualizarRepositorioAction() {
        try {
            $request = $this->getRequest();
            if ($request->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "id" => "Digits",
                    "patente" => "Digits",
                    "aduana" => "Digits",
                    "referencia" => "StringToUpper",
                    "rfc_cliente" => "StringToUpper",
                );
                $v = array(
                    "id" => array("NotEmpty"),
                    "aduana" => array("NotEmpty", array("stringLength", array("min" => 3, "max" => 3))),
                    "patente" => array("NotEmpty", array("stringLength", array("min" => 4, "max" => 4))),
                    "pedimento" => array("NotEmpty", array("stringLength", array("min" => 7, "max" => 7))),
                    "referencia" => array("NotEmpty"),
                    "rfc_cliente" => array("NotEmpty"),
                );
                $input = new Zend_Filter_Input($f, $v, $request->getPost());
                if ($input->isValid("id") && $input->isValid("patente") && $input->isValid("aduana") && $input->isValid("pedimento") && $input->isValid("referencia") && $input->isValid("rfc_cliente")) {
                    $model = new Archivo_Model_RepositorioIndex();
                    $old = $model->datos($input->id);
                    $arr = $model->archivosDeRepositorio($old["patente"], $old["aduana"], $old["referencia"]);
                    if (!($model->buscar($input->patente, $input->aduana, $input->referencia))) {
                        $row = array(
                            "patente" => $input->patente,
                            "aduana" => $input->aduana,
                            "pedimento" => str_pad($input->pedimento, 7, '0', STR_PAD_LEFT),
                            "referencia" => $input->referencia,
                            "rfcCliente" => $input->rfc_cliente,
                            "modificado" => date("Y-m-d H:i:s"),
                            "modificadoPor" => $this->_session->username,
                        );
                        if ($model->update($input->id, $row)) {
                            if (count($arr)) {
                                $rrow = array(
                                    "patente" => $input->patente,
                                    "aduana" => $input->aduana,
                                    "pedimento" => str_pad($input->pedimento, 7, '0', STR_PAD_LEFT),
                                    "referencia" => $input->referencia,
                                    "rfc_cliente" => $input->rfc_cliente,
                                    "modificado" => date("Y-m-d H:i:s"),
                                    "modificadoPor" => $this->_session->username,
                                );
                                foreach ($arr as $item) {
                                    $model->actualizarArchivo($item["id"], $rrow);
                                }
                            }
                            $this->_helper->json(array("success" => true, "id" => $input->id));
                        } else {
                            $this->_helper->json(array("success" => false, "message" => "Unable to update"));
                        }
                    } else {
                        $row = array(
                            "pedimento" => str_pad($input->pedimento, 7, '0', STR_PAD_LEFT),
                            "rfcCliente" => $input->rfc_cliente,
                            "modificado" => date("Y-m-d H:i:s"),
                            "modificadoPor" => $this->_session->username,
                        );
                        if ($model->update($input->id, $row)) {
                            if (count($arr)) {
                                $rrow = array(
                                    "pedimento" => str_pad($input->pedimento, 7, '0', STR_PAD_LEFT),
                                    "rfc_cliente" => $input->rfc_cliente,
                                    "modificado" => date("Y-m-d H:i:s"),
                                    "modificadoPor" => $this->_session->username,
                                );
                                foreach ($arr as $item) {
                                    $model->actualizarArchivo($item["id"], $rrow);
                                }
                            }
                        }
                        $this->_helper->json(array("success" => true, "id" => $input->id));
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

    public function validarRepositorioAction() {
        try {
            $request = $this->getRequest();
            if ($request->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "id" => "Digits",
                    "patente" => "Digits",
                    "aduana" => "Digits",
                    "pedimento" => "StringToUpper",
                    "referencia" => "StringToUpper",
                    "rfc_cliente" => "StringToUpper",
                );
                $v = array(
                    "*" => "NotEmpty",
                    "aduana" => array("NotEmpty", new Zend_Validate_Int(), new Zend_Validate_StringLength(array("min" => 3, "max" => 3))),
                    "patente" => array("NotEmpty", new Zend_Validate_Int(), new Zend_Validate_StringLength(array("min" => 4, "max" => 4))),
                    "pedimento" => array("NotEmpty", new Zend_Validate_StringLength(array("min" => 7, "max" => 7))),
                    "referencia" => array("NotEmpty", new Zend_Validate_Regex("/^[-_a-zA-Z0-9.\/]+$/")),
                    "rfc_cliente" => array("NotEmpty", new Zend_Validate_Regex("/^[-_a-zA-Z0-9.]+$/")),
                );
                $input = new Zend_Filter_Input($f, $v, $request->getPost());
                if ($input->isValid("id") && $input->isValid("patente") && $input->isValid("aduana") && $input->isValid("pedimento") && $input->isValid("referencia") && $input->isValid("rfc_cliente")) {

                    $pedimento = str_pad($input->pedimento, 7, '0', STR_PAD_LEFT);

                    $referencias = new OAQ_Referencias(array("patente" => $input->patente, "aduana" => $input->aduana, "referencia" => $input->referencia, "usuario" => $this->_session->username, "rfcCliente" => $input->rfc_cliente, "pedimento" => $pedimento));
                    if (($id = $referencias->nuevoRepositorioIndex())) {
                        $this->_helper->json(array("success" => true, "id" => $id));
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

    public function validarReferenciaAction() {
        try {
            $request = $this->getRequest();
            if ($request->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "id" => "Digits",
                    "patente" => "Digits",
                    "aduana" => "Digits",
                    "referencia" => "StringToUpper",
                    "rfc_cliente" => "StringToUpper",
                );
                $v = array(
                    "*" => "NotEmpty",
                    "aduana" => array("Alnum", array("stringLength", array("min" => 3, "max" => 3))),
                    "patente" => array("Alnum", array("stringLength", array("min" => 4, "max" => 4))),
                    "pedimento" => array("Alnum", array("stringLength", array("min" => 7, "max" => 7))),
                    "referencia" => array("NotEmpty", new Zend_Validate_Regex("/^[-_a-zA-Z0-9.\/]+$/")),
                    "rfc_cliente" => array("NotEmpty", new Zend_Validate_Regex("/^[-_a-zA-Z0-9.]+$/")),
                );
                $input = new Zend_Filter_Input($f, $v, $request->getPost());
                if ($input->isValid("id") && $input->isValid("patente") && $input->isValid("aduana") && $input->isValid("pedimento") && $input->isValid("referencia") && $input->isValid("rfc_cliente")) {
                    $model = new Archivo_Model_RepositorioMapper();
                    $found = $model->buscarReferencia($input->patente, $input->aduana, $input->referencia);
                    if ($found === false) {
                        $adu = new Trafico_Model_TraficoAduanasMapper();
                        if ($adu->idAduana($input->patente, $input->aduana)) {
                            $referencias = new OAQ_Referencias(array("patente" => $input->patente, "aduana" => $input->aduana, "referencia" => $input->referencia, "usuario" => ucfirst($this->_session->username), "rfcCliente" => $input->rfc_cliente, "pedimento" => $input->pedimento));
                            $referencias->nuevoRepositorioIndex();
                            $model->addNewFile(9999, null, $input->referencia, $input->patente, $input->aduana, "", "", $this->_session->username, null, $input->rfc_cliente, $input->pedimento);
                            $this->_helper->json(array("success" => true, "patente" => $input->patente, "aduana" => $input->aduana, "ref" => $input->referencia, "pedimento" => $input->pedimento, "rfc_cliente" => $input->rfc_cliente));
                        } else {
                            $this->_helper->json(array("success" => false, "message" => "La aduana que selecciono no se encuentra en nuestra base de datos, enviar correo a soporte@oaq.com.mx"));
                        }
                    }
                    $this->_helper->json(array("success" => true, "patente" => $input->patente, "aduana" => $input->aduana, "ref" => $input->referencia));
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

    public function moverRepositorioAction() {
        try {
            $request = $this->getRequest();
            if ($request->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "id" => "Digits",
                    "patente" => "Digits",
                    "aduana" => "Digits",
                    "referencia" => "StringToUpper",
                    "rfc_cliente" => "StringToUpper",
                );
                $v = array(
                    "*" => "NotEmpty",
                    "id" => array("NotEmpty", new Zend_Validate_Int()),
                    "aduana" => array("Alnum", array("stringLength", array("min" => 3, "max" => 3))),
                    "patente" => array("Alnum", array("stringLength", array("min" => 4, "max" => 4))),
                    "pedimento" => array("Alnum", array("stringLength", array("min" => 7, "max" => 7))),
                    "referencia" => array("NotEmpty", new Zend_Validate_Regex("/^[-_a-zA-Z0-9.\/]+$/")),
                    "rfc_cliente" => array("NotEmpty", new Zend_Validate_Regex("/^[-_a-zA-Z0-9.]+$/")),
                );
                $input = new Zend_Filter_Input($f, $v, $request->getPost());
                if ($input->isValid("id") && $input->isValid("patente") && $input->isValid("aduana") && $input->isValid("pedimento") && $input->isValid("referencia") && $input->isValid("rfc_cliente")) {
                    $mapper = new Archivo_Model_RepositorioIndex();
                    if (($id = $mapper->buscar($input->patente, $input->aduana, $input->referencia, str_pad($input->pedimento, 7, '0', STR_PAD_LEFT)))) {
                        $old = $mapper->datos($input->id);
                        $new = $mapper->datos($id);
                        $arr = $mapper->archivosDeRepositorio($old["patente"], $old["aduana"], $old["referencia"]);
                        if (!empty($arr)) {
                            foreach ($arr as $item) {
                                if ((int) $item["tipo_archivo"] !== 9999) {
                                    $mapper->actualizarArchivo($item["id"], array("patente" => $new["patente"], "aduana" => $new["aduana"], "pedimento" => str_pad($new['pedimento'], 7, '0', STR_PAD_LEFT), "referencia" => $new["referencia"], "rfc_cliente" => $new["rfcCliente"], "modificado" => date("Y-m-d H:i:s"), "modificadoPor" => $this->_session->username));
                                } else {
                                    $mapper->borrarEnRepositorio($item["id"]);
                                }
                            }
                            $mapper->borrar($input->id);
                            $this->_helper->json(array("success" => true, "id" => $id));
                        }
                    } else {
                        throw new Exception("La referencia destino no existe.");
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

    public function borrarRepositorioAction() {
        try {
            $request = $this->getRequest();
            if ($request->isPost()) {
                $f = array("*" => array("StringTrim", "StripTags"), "id" => "Digits");
                $v = array("id" => array("NotEmpty", new Zend_Validate_Int()),);
                $input = new Zend_Filter_Input($f, $v, $request->getPost());
                if ($input->isValid("id")) {
                    $mapper = new Archivo_Model_RepositorioIndex();
                    if ($mapper->borrar($input->id)) {
                        $this->_helper->json(array("success" => true));
                    }
                    throw new Exception("No se puedo borrar!");
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

    public function obtenerFacturasTerminalAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "page" => array("Digits"),
                    "rows" => array("Digits"),
                );
                $v = array(
                    "page" => array(new Zend_Validate_Int(), "default" => 1),
                    "rows" => array(new Zend_Validate_Int(), "default" => 10),
                    "fechaInicio" => array("NotEmpty", new Zend_Validate_Regex("/^\d{4}\-\d{2}\-\d{2}$/")),
                    "fechaFin" => array("NotEmpty", new Zend_Validate_Regex("/^\d{4}\-\d{2}\-\d{2}$/")),
                    "switchData" => "NotEmpty",
                    "filterRules" => "NotEmpty",
                );
                $input = new Zend_Filter_Input($f, $v, $request->getPost());
                if ($input->isValid("page") && $input->isValid("rows")) {
                    $noData = filter_var($input->switchData, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                    $terminal = new OAQ_TerminalLogistics();
                    $terminal->todas($input->page, $input->rows, $input->filterRules, $input->fechaInicio, $input->fechaFin, $noData);
                    $this->_helper->json(array(
                        "total" => $terminal->get_total(),
                        "rows" => $terminal->get_rows(),
                    ));
                }
            }
            $this->_helper->json(array("success" => false));
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function mvhcEstatusAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $f = array(
                    "id" => array(new Zend_Filter_StringTrim(), new Zend_Filter_StripTags(), new Zend_Filter_Digits()),
                    "estatus" => array(new Zend_Filter_StringTrim(), new Zend_Filter_StripTags(), new Zend_Filter_Digits()),
                );
                $v = array(
                    "id" => array("NotEmpty", new Zend_Validate_Int()),
                    "estatus" => array("NotEmpty", new Zend_Validate_Int()),
                );
                $input = new Zend_Filter_Input($f, $v, $request->getPost());
                if ($input->isValid("id")) {                    
                    $log = new Archivo_Model_ChecklistReferenciasBitacora();                    
                    $mppr = new Archivo_Model_RepositorioIndex();
                    
                    $arr = $mppr->datos($input->id);
                    
                    if ((int) $input->estatus === 0) {
                        $mppr->update($input->id, array("mvhcCliente" => null, "mvhcFirmada" => null));                        
                    } else if ((int) $input->estatus === 1) {
                        $mppr->update($input->id, array("mvhcCliente" => 1, "mvhcFirmada" => null));                        
                        $log->agregar(array(
                            'patente' => $arr["patente"],
                            'aduana' => $arr["aduana"],
                            'pedimento' => str_pad($arr["pedimento"], 7, '0', STR_PAD_LEFT),
                            'referencia' => $arr["referencia"],
                            'usuario' => $this->_session->username,
                            'bitacora' => "MV/HC EN POSESIÓN DE CLIENTE",
                            'creado' => date("Y-m-d H:i:s"),
                        ));
                    } else if ((int) $input->estatus === 2) {
                        $mppr->update($input->id, array("mvhcCliente" => 1, "mvhcFirmada" => 1));
                        $log->agregar(array(
                            'patente' => $arr["patente"],
                            'aduana' => $arr["aduana"],
                            'pedimento' => str_pad($arr["pedimento"], 7, '0', STR_PAD_LEFT),
                            'referencia' => $arr["referencia"],
                            'usuario' => $this->_session->username,
                            'bitacora' => "MV/HC FIRMADA POR CLIENTE",
                            'creado' => date("Y-m-d H:i:s"),
                        ));
                    }
                    $this->_helper->json(array("success" => true));
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
    
    public function enviarEmailAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $f = array(
                    "id" => array(new Zend_Filter_StringTrim(), new Zend_Filter_StripTags(), new Zend_Filter_Digits()),
                );
                $v = array(
                    "id" => array("NotEmpty", new Zend_Validate_Int()),
                    "data" => array("NotEmpty"),
                );
                $input = new Zend_Filter_Input($f, $v, $request->getPost());
                if ($input->isValid("id") && $input->isValid("data")) {
                    $json = json_decode(html_entity_decode($input->data), true);
                    if (!empty($json)) {
                        $mapper = new Archivo_Model_RepositorioIndex();
                        $arr = $mapper->datos($input->id);
                        if (!empty($arr)) {
                            $emails = new OAQ_EmailsTraffic();
                            $view = new Zend_View();
                            $view->setScriptPath(APPLICATION_PATH . "/../library/Templates/");

                            $view->patente = $arr["patente"];
                            $view->aduana = $arr["aduana"];
                            $view->pedimento = str_pad($arr["pedimento"], 7, '0', STR_PAD_LEFT);
                            $view->referencia = $arr["referencia"];

                            if (!empty($json["emails"]) && !empty($json["archivos"])) {
                                $cont = new Trafico_Model_ContactosCliMapper();
                                $arrCont = $cont->obtenerPorArregloId($json["emails"]);
                                $mapper = new Archivo_Model_Repositorio();
                                $arrFiles = $mapper->obtenerPorArregloId($json["archivos"]);
                                if (APPLICATION_ENV == "production") {
                                    if (!empty($arrCont)) {
                                        foreach ($arrCont as $contact) {
                                            $emails->addTo($contact["email"], $contact["nombre"]);
                                        }
                                    } else {
                                        $emails->addTo("soporte@oaq.com.mx", "Soporte OAQ");
                                    }
                                } else {
                                    $emails->addTo("soporte@oaq.com.mx", "Soporte OAQ");
                                }
                                if (!empty($arrFiles)) {
                                    foreach ($arrFiles as $file) {
                                        $emails->addAttachment($file["ubicacion"]);
                                    }
                                }
                            }
                            $emails->contenidoPersonalizado($view->render("enviar-pedimento.phtml"));
                            $emails->setSubject("Pedimento pagado " . $arr["aduana"] . "-" . $arr["patente"] . "-" . $arr["pedimento"] . " " . $arr["referencia"]);
                            $emails->send();
                            $this->_helper->json(array("success" => true));
                        }
                    } else {
                        throw new Exception("No data recieved!");
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

}
