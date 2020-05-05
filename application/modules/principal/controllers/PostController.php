<?php

class Principal_PostController extends Zend_Controller_Action {

    protected $_session;
    protected $_svucem;
    protected $_config;
    protected $_appconfig;
    protected $_redirector;
    protected $_logger;

    public function init() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_appconfig = new Application_Model_ConfigMapper();
        $this->_redirector = $this->_helper->getHelper("Redirector");
        $this->_config = new Zend_Config_Ini(APPLICATION_PATH . "/configs/application.ini", APPLICATION_ENV);
        $this->_logger = Zend_Registry::get("logDb");
        $contextSwitch = $this->_helper->getHelper("contextSwitch");
        $contextSwitch->addActionContext("mis-datos", "json")
                ->initContext();
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

    public function misDatosAction() {
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
                    "telefono" => array("NotEmpty"),
                    "extension" => array("NotEmpty"),
                );
                $input = new Zend_Filter_Input($f, $v, $request->getPost());
                if ($input->isValid("telefono") && $input->isValid("extension")) {
                    $mapper = new Principal_Model_UsuariosDirectorio();
                    $arr = array(
                        "telefono" => $input->telefono,
                        "extension" => $input->extension,
                    );
                    if ($mapper->actualizar($this->_session->id, $arr)) {
                        $this->_helper->json(array("success" => true));
                    }
                } else {
                    throw new Exception("Invalid input!");
                }
            } else {
                throw new Exception("Invalid request type!");
            }
        } catch (Exception $e) {
            $this->_helper->json(array("success" => false, "message" => $e->getMessage()));
        }
    }

    public function nuevaSolicitudAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "idSolicitud" => "Digits",
                );
                $v = array(
                    "idSolicitud" => array("NotEmpty"),
                );
                $input = new Zend_Filter_Input($f, $v, $request->getPost());
                if ($input->isValid("idSolicitud")) {
                    $mppr = new Principal_Model_UsuariosSolicitudes();
                    $arr = array(
                        "idSolicitud" => $input->idSolicitud,
                        "estatus" => 0,
                        "creado" => date("Y-m-d H:i:s"),
                        "creadoPor" => $this->_session->username,
                    );
                    if ($mppr->agregar($arr)) {
                        $this->_helper->json(array("success" => true));
                    }
                } else {
                    throw new Exception("Invalid input!");
                }
            } else {
                throw new Exception("Invalid request type!");
            }
        } catch (Exception $e) {
            $this->_helper->json(array("success" => false, "message" => $e->getMessage()));
        }
    }
    
    public function guardarSolicitudAction() {
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
                    "data" => array("NotEmpty"),
                );
                $input = new Zend_Filter_Input($f, $v, $request->getPost());
                if ($input->isValid("data")) {
                    
                } else {
                    throw new Exception("Invalid input!");
                }
            } else {
                throw new Exception("Invalid request type!");
            }
        } catch (Exception $e) {
            $this->_helper->json(array("success" => false, "message" => $e->getMessage()));
        }
    }
    
    public function borrarSolicitudAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "id" => "Digits",
                );
                $v = array(
                    "id" => array("NotEmpty"),
                );
                $input = new Zend_Filter_Input($f, $v, $request->getPost());
                if ($input->isValid("id")) {
                    $mppr = new Principal_Model_UsuariosSolicitudes();
                    if ($mppr->borrar($input->id)) {
                        $this->_helper->json(array("success" => true));
                    }
                } else {
                    throw new Exception("Invalid input!");
                }
            } else {
                throw new Exception("Invalid request type!");
            }
        } catch (Exception $e) {
            $this->_helper->json(array("success" => false, "message" => $e->getMessage()));
        }
    }

    public function actividadGuardarAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "titulo" => "StringToUpper",
                    "idDepto" => "Digits",
                    "idActividad" => "Digits",
                    "idCliente" => "Digits",
                    "tipoActividad" => "Digits",
                    "totalTickets" => "Digits",
                );
                $v = array(
                    "titulo" => "NotEmpty",
                    "idDepto" => array("NotEmpty", new Zend_Validate_Int()),
                    "idActividad" => array("NotEmpty", new Zend_Validate_Int()),
                    "idCliente" => array("NotEmpty", new Zend_Validate_Int()),
                    "tipoActividad" => array("NotEmpty", new Zend_Validate_Int()),
                    "totalTickets" => array("NotEmpty", new Zend_Validate_Int()),
                    "totalEnvios" => "NotEmpty",
                    "saldoFinal" => "NotEmpty",
                    "expedientesFacturados" => "NotEmpty",
                    "expedientesArchivados" => "NotEmpty",
                    "facturasCanceladas" => "NotEmpty",
                    "pedimentosModulados" => "NotEmpty",
                    "pedimentosPagados" => "NotEmpty",
                    "cantidadVerdes" => "NotEmpty",
                    "cantidadRojos" => "NotEmpty",
                    "quejas" => "NotEmpty",
                    "consultas" => "NotEmpty",
                    "visitas" => "NotEmpty",
                    "llamadas" => "NotEmpty",
                    "documentos" => "NotEmpty",
                    "multas" => "NotEmpty",
                    "duracion" => "NotEmpty",
                    "observaciones" => "NotEmpty",
                );
                $input = new Zend_Filter_Input($f, $v, $request->getPost());
                if ($input->isValid("idActividad")) {
                    $mppr = new Principal_Model_UsuariosActividades();
                    $arr = array(
                        "titulo" => mb_strtoupper(html_entity_decode($input->titulo)),
                        "idDepto" => $input->idDepto,
                        "idActividad" => $input->tipoActividad,
                        "idCliente" => $input->idCliente,
                        "totalTickets" => $input->totalTickets,
                        "totalEnvios" => $input->totalEnvios,
                        "saldoFinal" => $input->saldoFinal,
                        "expedientesFacturados" => $input->expedientesFacturados,
                        "facturasCanceladas" => $input->facturasCanceladas,
                        "expedientesArchivados" => $input->expedientesArchivados,
                        "pedimentosModulados" => $input->pedimentosModulados,
                        "pedimentosPagados" => $input->pedimentosPagados,
                        "cantidadVerdes" => $input->cantidadVerdes,
                        "cantidadRojos" => $input->cantidadRojos,
                        "quejas" => $input->quejas,
                        "consultas" => $input->consultas,
                        "visitas" => $input->visitas,
                        "llamadas" => $input->llamadas,
                        "documentos" => $input->documentos,
                        "multas" => $input->multas,
                        "duracion" => $input->duracion,
                        "observaciones" => html_entity_decode($input->observaciones),
                    );
                    $mppr->update($input->idActividad, $arr);
                    $this->_helper->json(array("success" => true));
                } else {
                    throw new Exception("Invalid input!");
                }
            } else {
                throw new Exception("Invalid request type!");
            }
        } catch (Exception $e) {
            $this->_helper->json(array("success" => false, "message" => $e->getMessage()));
        }
    }
    
    public function actividadBorrarAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $f = array(
                    "id" => array("StringTrim", "StripTags", "Digits"),
                );
                $v = array(
                    "id" => array("NotEmpty", new Zend_Validate_Int()),
                );
                $input = new Zend_Filter_Input($f, $v, $request->getPost());
                $mppr = new Principal_Model_UsuariosActividades();
                if ($input->isValid("id")) {
                    if ($mppr->borrar($input->id) == true) {
                        $this->_helper->json(array("success" => true));
                    }
                } else {
                    throw new Exception("Invalid input!");
                }
            } else {
                throw new Exception("Invalid request type!");
            }
        } catch (Exception $e) {
            $this->_helper->json(array("success" => false, "message" => $e->getMessage()));
        }
    }
    
    public function actividadAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "id" => "Digits",
                    "titulo" => "StringToUpper",
                );
                $v = array(
                    "id" => array("NotEmpty", new Zend_Validate_Int()),
                    "titulo" => "NotEmpty",
                    "fecha" => array(new Zend_Validate_Regex("/^\d{4}\-\d{2}\-\d{2}$/")),
                );
                $input = new Zend_Filter_Input($f, $v, $request->getPost());
                $mppr = new Principal_Model_UsuariosActividades();
                if (!$input->isValid("id")) {
                    $arr = array(
                        "idUsuario" => $this->_session->id,
                        "titulo" => mb_strtoupper(html_entity_decode($input->titulo)),
                        "fecha" => $input->fecha,
                        "creado" => date("Y-m-d H:i:s"),
                    );
                    if ($mppr->agregar($arr) == true) {
                        $this->_helper->json(array("success" => true));
                    }
                } elseif ($input->isValid("id")) {
                    $arr = array(
                        "titulo" => mb_strtoupper(html_entity_decode($input->titulo)),
                        "actualizado" => date("Y-m-d H:i:s"),
                    );
                    if ($mppr->actualizar($input->id, $arr) == true) {
                        $this->_helper->json(array("success" => true));
                    }
                } else {
                    throw new Exception("Invalid input!");
                }
            } else {
                throw new Exception("Invalid request type!");
            }
        } catch (Exception $e) {
            $this->_helper->json(array("success" => false, "message" => $e->getMessage()));
        }
    }

    public function enviarFormatoQuejaAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {

                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "name" => "StringToUpper",
                    "area" => "StringToUpper",
                    "office" => "StringToUpper",
                    "othertext" => "StringToUpper",
                    "about" => "StringToUpper",
                    "tellus" => "StringToUpper",
                    "how" => "StringToUpper",
                    "matter" => "StringToLower"
                );
                $v = array(
                    "name" => "NotEmpty",
                    "area" => "NotEmpty",
                    "office" => "NotEmpty",
                    "othertext" => "NotEmpty",
                    "about" => "NotEmpty",
                    "tellus" => "NotEmpty",
                    "how" => "NotEmpty",
                    "matter" => "NotEmpty",
                );
                $input = new Zend_Filter_Input($f, $v, $request->getPost());

                $view = new Zend_View();
                $view->setScriptPath(APPLICATION_PATH . "/../library/Templates/");

                $emails = new OAQ_EmailsTraffic();
                $emails->setSubject("OAQ te escucha " . $input->fecha);
                if (APPLICATION_ENV == "production") {
                    $emails->addTo("dlopez@oaq.com.mx", "David López Rosales");
                    $emails->addTo("daniela.gomez@oaq.com.mx", "Daniela Gomez");
                    $emails->addBcc("ti.jvaldez@oaq.com.mx", "Jaime E. Valdez");
                } else if (APPLICATION_ENV == "staging" || APPLICATION_ENV == "development") {
                    $emails->addTo("ti.jvaldez@oaq.com.mx", "Jaime E. Valdez");
                }

                $mppr = new Principal_Model_OaqTeEscucha();

                $arr = array(
                    "nombre" => $input->name,
                    "area" => $input->area,
                    "oficina" => $input->office,
                    "tema" => $input->matter,
                    "otro" => $input->othertext,
                    "detalle" => $input->office,
                    "implicacion" => $input->tellus,
                    "como" => $input->how,
                    "usuario" => $this->_session->username
                );

                $mppr->agregar($arr);

                $view->name = $input->name;
                $view->area = $input->area;
                $view->office = $input->office;

                $matter = '';
                if ($input->matter == 'ambience') {
                    $matter = 'Ambiente de trabajo';
                }
                if ($input->matter == 'load') {
                    $matter = 'Carga de trabajo';
                }
                if ($input->matter == 'extensive') {
                    $matter = 'Jornadas de trabajo extensas';
                }
                if ($input->matter == 'ledaership') {
                    $matter = 'Liderazgo';
                }
                if ($input->matter == 'violence') {
                    $matter = 'Violencia laboral (Acoso psicológico, hostigamiento, malos tratos)';
                }
                if ($input->matter == 'other') {
                    $matter = $input->othertext;
                }
                $view->matter = $matter;
                $view->about = $input->about;
                $view->tellus = $input->tellus;
                $view->how = $input->how;

                $emails->contenidoPersonalizado($view->render("oaq-te-escucha.phtml"));
                if ($emails->send()) {
                    $this->_helper->json(array("success" => true));
                }

            } else {
                throw new Exception("Invalid request type!");
            }
        } catch (Exception $e) {
            $this->_helper->json(array("success" => false, "message" => $e->getMessage()));
        }
    }

    public function enviarPedimentoAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {

                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "idTrafico" => "Digits",
                );
                $v = array(
                    "idTrafico" => "NotEmpty",
                );
                $input = new Zend_Filter_Input($f, $v, $request->getPost());
                if ($input->isValid("idTrafico")) {
                    $trafico = new OAQ_Trafico(array("idTrafico" => $input->idTrafico, "usuario" => $this->_session->username, "idUsuario" => $this->_session->id));

                    $aduanet = new Aduanet_Pedimentos();
                    if($aduanet->login() === true) {
                        $r = $aduanet->pedimento();
                        if ($r) {
                            $aduanet->agregarFacturas();
                        }
                    }

                    $this->_helper->json(array("success" => true));

                } else {
                    throw new Exception("Invalid input!");
                }
            } else {
                throw new Exception("Invalid request type!");
            }
        } catch (Exception $e) {
            $this->_helper->json(array("success" => false, "message" => $e->getMessage()));
        }
    }

}
