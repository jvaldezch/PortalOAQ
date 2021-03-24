<?php

class Vucem_IndexController extends Zend_Controller_Action {

    protected $_session;
    protected $_svucem;
    protected $_soapClient;
    protected $_config;
    protected $_appconfig;
    protected $_redirector;
    protected $_logger;

    public function init() {
        $this->_appconfig = new Application_Model_ConfigMapper();
        $this->_redirector = $this->_helper->getHelper('Redirector');
        $this->view->headLink(array('rel' => 'icon shortcut', 'href' => '/favicon.png'));        
        $this->view->headLink()
                ->appendStylesheet("/js/common/bootstrap/css/bootstrap.min.css")
                ->appendStylesheet("/js/common/bootstrap/datepicker/css/datepicker.css")
                ->appendStylesheet("/css/DT_bootstrap.css")
                ->appendStylesheet("/css/fontawesome/css/fontawesome-all.min.css")
                ->appendStylesheet("/less/traffic-module.css?" . time());
        $this->view->headScript()
                ->appendFile("/js/common/jquery-1.9.1.min.js")
                ->appendFile("/js/common/bootstrap/js/bootstrap.min.js")
                ->appendFile("/js/common/bootstrap/datepicker/js/bootstrap-datepicker.js")
                ->appendFile("/js/common/jquery.form.min.js")
                ->appendFile("/js/common/jquery.validate.min.js")
                ->appendFile("/js/common/jquery.dataTables.min.js")
                ->appendFile("/js/common/js.cookie.js")
                ->appendFile("/js/common/jquery.blockUI.js")
                ->appendFile("/js/common/DT_bootstrap.js")
                ->appendFile("/js/common/mensajero.js?" . time())
                ->appendFile("/js/common/principal.js?" . time());
        $this->_config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini', APPLICATION_ENV);
        $this->_logger = Zend_Registry::get("logDb");
        $this->view->vucemWs = $this->_config->app->vucem;
        $ajaxContext = $this->_helper->getHelper('contextSwitch');
        $ajaxContext->addActionContext('verificar-vucem', array('json'))
                ->addActionContext('enviar-vucem', array('json'))
                ->initContext();
    }

    public function preDispatch() {
        $this->_session = NULL ? $this->_session = new Zend_Session_Namespace("") : $this->_session = new Zend_Session_Namespace($this->_config->app->namespace);
        if ($this->_session->authenticated == true) {
            $session = new OAQ_Session($this->_session, $this->_appconfig);
            $session->actualizar();
            $session->actualizarSesion(Zend_Controller_Front::getInstance()->getRequest()->getRequestUri());
        } else {
            $this->getResponse()->setRedirect($this->_appconfig->getParam("link-logout"));
        }
        $mapper = new Application_Model_MenuAccesos();
        $this->view->menu = $mapper->obtenerPorRol($this->_session->idRol);
        $this->view->username = $this->_session->username;
        $this->view->rol = $this->_session->role;
        $this->_svucem = NULL ? $this->_svucem = new Zend_Session_Namespace('') : $this->_svucem = new Zend_Session_Namespace('OAQVucem');
        $this->_svucem->setExpirationSeconds($this->_appconfig->getParam("session-exp"));
        $news = new Application_Model_NoticiasInternas();
        $this->view->noticias = $news->obtenerTodos();
    }

    public function indexAction() {
        $this->view->title = $this->_appconfig->getParam("title") . " COVE";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headLink()
                ->appendStylesheet("/v2/js/common/confirm/jquery-confirm.min.css")
                ->appendStylesheet("/css/DT_bootstrap.css");
        $this->view->headScript()
                ->appendFile("/v2/js/common/confirm/jquery-confirm.min.js")
                ->appendFile("/js/vucem/index/index.js?" . time());
        $gets = $this->_request->getParams();
        $this->view->cove = isset($gets["cove"]) ? $gets["cove"] : null;
        $this->view->referencia = isset($gets["referencia"]) ? $gets["referencia"] : null;
        $this->view->pedimento = isset($gets["pedimento"]) ? $gets["pedimento"] : null;
        $this->view->factura = isset($gets["factura"]) ? $gets["factura"] : null;
        $sys = new Application_Model_SystemsMapper();
        $getSys = new Usuarios_Model_SisPedimentosMapper();
        $idSys = $sys->getMySystem($this->_session->id, $this->_session->username, "sispedimentos");
        $ped = $getSys->getMySystemData($idSys);
        if (!isset($this->_svucem->sysname) || $idSys != $this->_svucem->idSistema) {
            $this->_svucem->idSistema = $ped["id"];
            $this->_svucem->sysname = $ped["nombre"];
            $this->_svucem->sysdirip = $ped["direccion_ip"];
            $this->_svucem->sysdb = $ped["dbname"];
            $this->_svucem->sysuser = $ped["usuario"];
            $this->_svucem->syspwd = $ped["pwd"];
            $this->_svucem->sysport = $ped["puerto"];
            $this->_svucem->systype = $ped["tipo"];
            $this->_svucem->username = $this->_session->username;
        }
        if (isset($this->_svucem)) {
            unset($this->_svucem->patente);
            unset($this->_svucem->aduana);
            unset($this->_svucem->solicitante);
            unset($this->_svucem->tipoOperacion);
            unset($this->_svucem->tipoFigura);
            unset($this->_svucem->relacionFacturas);
            unset($this->_svucem->email);
            unset($this->_svucem->facturas);
            unset($this->_svucem->newInvoice);
            unset($this->_svucem->productList);
            unset($this->_svucem->adenda);
            unset($this->_svucem->adendaCove);
        }
        $coves = new Vucem_Model_VucemSolicitudesMapper();
        if (isset($gets["cove"]) || isset($gets["referencia"]) || isset($gets["pedimento"]) || isset($gets["factura"])) {
            $result = $coves->buscarSolicitudes(
                    ($this->_session->role != "super" && $this->_session->role != "gerente") ? $this->_svucem->username : null, ($gets["cove"] != '') ? trim($gets["cove"]) : null, ($gets["referencia"] != '') ? trim($gets["referencia"]) : null, ($gets["pedimento"] != '') ? trim($gets["pedimento"]) : null, ($gets["factura"] != '') ? trim($gets["factura"]) : null
            );
        } else {
            if ($this->_session->role == "corresponsal") {
                $referencias = new OAQ_Referencias();
                $res = $referencias->restriccionesAduanas($this->_session->id, $this->_session->role);
                if (!empty($res["aduanas"])) {
                    $result = $coves->obtenerSolicitudesCorresponsal($res["aduanas"]);
                }
            } else {
                $result = $coves->obtenerSolicitudes(($this->_session->role != "super" && $this->_session->role != "gerente") ? $this->_svucem->username : null );
            }
        }
        if (!empty($result)) {
            $this->view->result = $result;
        }
    }

    public function nuevoCoveSolicitanteAction() {
        $this->view->title = $this->_appconfig->getParam("title") . " Solicitante del COVE";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headScript()
                ->appendFile("/js/vucem/index/nuevo-cove-solicitante.js?" . time());
        $form = new Vucem_Form_Firmantes(array('username' => $this->_session->username));
        $form->populate(array(
            'email' => $this->_appconfig->getParam("vucem-email"),
        ));
        if (isset($this->_svucem->solicitante)) {
            $form->populate(array(
                'firmante' => $this->_svucem->solicitante,
                'Patente' => $this->_svucem->patente,
                'Aduana' => $this->_svucem->aduana,
                'tipoFigura' => $this->_svucem->tipoFigura,
                'tipoOperacion' => $this->_svucem->tipoOperacion,
                'relacionFacturas' => $this->_svucem->relacionFacturas,
            ));
        }
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            if ($form->isValid($data)) {
                $this->_svucem->patente = $data["Patente"];
                $this->_svucem->aduana = $data["Aduana"];
                $this->_svucem->solicitante = $data["firmante"];
                $this->_svucem->tipoOperacion = $data["tipoOperacion"];
                $this->_svucem->tipoFigura = $data["tipoFigura"];
                $this->_svucem->relacionFacturas = $data["relacionFacturas"];
                $this->_svucem->email = $this->_appconfig->getParam("vucem-email");
                $this->_logger->logEntry(
                        $this->_request->getModuleName() . ":" . $this->_request->getControllerName() . ":" . $this->_request->getActionName(), "SOLICITANTE COVE {$this->_svucem->patente} : {$this->_svucem->aduana} : {$this->_svucem->solicitante} : {$this->_svucem->tipoOperacion} : {$this->_svucem->tipoFigura} : {$this->_svucem->relacionFacturas}", $_SERVER['REMOTE_ADDR'], $this->_session->username);

                return $this->_redirector->gotoSimple('nuevo-cove-facturas', 'index', 'vucem');
            }
        }
        $this->view->form = $form;
    }

    public function nuevoCoveFacturasAction() {
        $this->view->title = $this->_appconfig->getParam("title")  . " Facturas del COVE";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headLink()
                ->appendStylesheet("/v2/js/common/confirm/jquery-confirm.min.css");
        $this->view->headScript()
                ->appendFile("/v2/js/common/confirm/jquery-confirm.min.js")
                ->appendFile("/js/common/bootstrap-datatable.js")
                ->appendFile("/js/vucem/index/nuevo-cove-facturas.js?" . time());
        $error = $this->_request->getParam("error", null);
        $numFactura = $this->_request->getParam("factura", null);
        $idfact = $this->_request->getParam("idfact", null);
        if (isset($error)) {
            if (preg_match('/seleccionar el RFC del solicitante/i', $error)) {
                $this->view->error = $error . ' <a href="/vucem/index/nuevo-cove-solicitante">Click aqui</a> para seleccionar.';
            } elseif (preg_match('/La factura no tiene/i', $error)) {
                $this->view->error = 'La factura <a href="/vucem/index/consultar-factura?factura=' . $idfact . '">' . $numFactura . '</a> no tiene mercancias, favor de revisar.';
            } elseif (preg_match('/Los sellos del firmante/i', $error)) {
                $this->view->error = $error;
            } elseif (preg_match('/No se han/i', $error)) {
                $this->view->error = $error;
            }
        }
        $mapper = new Vucem_Model_VucemTmpFacturasMapper();
        $arr = $mapper->obtenerTodas($this->_session->username);
        if(isset($arr) && !empty($arr)) {
            $this->view->data = $arr;
        }
        $sys = new Application_Model_SystemsMapper();
        $getSys = new Usuarios_Model_SisPedimentosMapper();
        $idSys = $sys->getMySystem($this->_session->id, $this->_session->username, "sispedimentos");
        $ped = $getSys->getMySystemData($idSys);
        if (!isset($this->_svucem->sysname) || $idSys != $this->_svucem->idSistema) {
            $this->_svucem->idSistema = $ped["id"];
            $this->_svucem->sysname = $ped["nombre"];
            $this->_svucem->sysdirip = $ped["direccion_ip"];
            $this->_svucem->sysdb = $ped["dbname"];
            $this->_svucem->sysuser = $ped["usuario"];
            $this->_svucem->syspwd = $ped["pwd"];
            $this->_svucem->sysport = $ped["puerto"];
            $this->_svucem->systype = $ped["tipo"];
            $this->_svucem->username = $this->_session->username;
        }
        $this->view->sysname = $this->_svucem->sysname;
        $this->view->info = 'ESTÁ CONECTADO A: ' . $this->_svucem->sysdirip . ' // ' . $this->_svucem->sysdb;
        if ($this->_svucem->uuidFactura) {
            unset($this->_svucem->uuidFactura);            
        }
    }
    
    /**
     * 
     * @return boolean
     */
    public function nuevaSolicitudAction() 
    {

        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $vucem = new OAQ_VucemEnh();
        $debug = $this->_request->getParam("debug", null);
        $misc = new OAQ_Misc();
        $firmante = new Vucem_Model_VucemFirmanteMapper();
        $vucemSol = new Vucem_Model_VucemSolicitudesMapper();
        $vucemFact = new Vucem_Model_VucemFacturasMapper();
        $vucemProd = new Vucem_Model_VucemProductosMapper();
        $tmpFact = new Vucem_Model_VucemTmpFacturasMapper();
        $rfcConsulta = array(
            "OAQ030623UL8",
        );
        $facturas = $tmpFact->obtenerFacturasParaEnvio($this->_session->username);
        if (!empty($facturas)) {
            $i = 1;
            foreach ($facturas as $k => $factura) {
                if (isset($factura["Productos"])) {
                    if (!(count($factura["Productos"] >= 1))) {
                        return $this->_helper->redirector->gotoUrl('/vucem/index/nuevo-cove-facturas?error=' . urlencode("La factura no tiene mercancia(s).") . '&idfact=' . $factura["IdFact"] . '&factura=' . urlencode($factura["NumFactura"]));
                    }
                } elseif (!isset($factura["Productos"])) {
                    return $this->_helper->redirector->gotoUrl('/vucem/index/nuevo-cove-facturas?error=' . urlencode("La factura no tiene mercancia(s).") . '&idfact=' . $factura["IdFact"] . '&factura=' . urlencode($factura["NumFactura"]));
                }
                if (isset($rfcConsulta)) {
                    unset($rfcConsulta);
                    if (($rfc = $this->_appconfig->getParam("rfc-consulta"))) {
                        $rfcConsulta = array(
                            $rfc,
                        );
                    }
                }
                if ($factura["Patente"] == 3920) {
                    $rfcConsulta[] = 'NOGI660213BI0';
                }
                if ($factura["Patente"] == 3574) {
                    $rfcConsulta[] = 'PEPJ561122765';
                }
                if ($vucem->addRfcsConsulta($factura["CteRfc"], $factura["CtePais"])) {
                    if ($factura["firmante"] != $factura["CteRfc"]) {
                        $rfcConsulta[] = $factura["CteRfc"];
                    }
                }
                if ($vucem->addRfcsConsulta($factura["ProTaxID"], $factura["ProPais"]) && !in_array($factura["ProTaxID"], $rfcConsulta)) {
                    if ($factura["firmante"] != $factura["ProTaxID"]) {
                        $rfcConsulta[] = $factura["ProTaxID"];
                    }
                }
                $gontor = array(
                    'PEM930903SH4', 'CCE001027PF6', 'MAT0903126W0', 'FCE1210012TA'
                );
                if (in_array($factura["CteRfc"], $gontor) || in_array($factura["ProTaxID"], $gontor)) {
                    $rfcConsulta[] = 'GTO910508AM7';
                }
                $rfc = $firmante->obtenerDetalleFirmante($factura["firmante"], null, $factura["Patente"], $factura["Aduana"]);
                if (!isset($rfc)) {
                    return $this->_helper->redirector->gotoUrl('/vucem/index/nuevo-cove-facturas?error=' . urlencode("Los sellos del firmante no existen " . $factura["firmante"]));
                }
                $pkeyid = openssl_get_privatekey(base64_decode($rfc["spem"]), $rfc["spem_pswd"]);
                if(isset($debug) && $debug = true) {
                    $comprobantes = $vucem->crearComprobante($factura, $factura["figura"], $factura["TipoOperacion"], $rfcConsulta, "soporte@oaq.com.mx", $rfc['cer'], $pkeyid, $factura["Patente"], isset($factura["adenda"]) ? $factura["adenda"] : null, $rfc["sha"]);
                } else {
                    if(APPLICATION_ENV === "production") {
                        $email = $this->_appconfig->getParam("vucem-email");
                    } else {
                        $email = "soporte@oaq.com.mx";
                    }
                    $comprobantes = $vucem->crearComprobante($factura, $factura["figura"], $factura["TipoOperacion"], $rfcConsulta, $email, $rfc['cer'], $pkeyid, $factura["Patente"], isset($factura["adenda"]) ? $factura["adenda"] : null, $rfc["sha"]);                    
                }
                $xmlFinal["xml"] = '<?xml version="1.0"?>' . $vucem->prepararEnvio($comprobantes["xml"], $rfc["rfc"], $rfc["ws_pswd"]);
                $xmlFinal["cert"] = $comprobantes["cert"];
                $xmlFinal["cadena"] = $comprobantes["cadena"];
                $xmlFinal["firma"] = $comprobantes["firma"];
                $orden = isset($factura["OrdenFact"]) ? $factura["OrdenFact"] : $i;
                $impExp = $factura["TipoOperacion"] . '-' . $factura["Patente"] . '-' . $factura["Aduana"] . '-' . $factura["Pedimento"] . '-' . $factura["Referencia"] . '-' . $factura["NumFactura"] . '-' . $orden . time();
                $uuid = $misc->getUuid($impExp);
                if(isset($debug) && $debug = true) {
                    header("Content-Type:text/xml;charset=windows-1252");
                    echo str_replace('&', '&amp;', html_entity_decode($vucem->htmlSpanish($xmlFinal["xml"]),ENT_QUOTES,"UTF-8"));
                    return false;
                }
                if (!($existe = $vucemSol->verificarSolicitud($uuid))) {
                    $idSolicitud = $vucemSol->nuevaSolicitud($factura["RelFact"], null, $xmlFinal["cert"], $xmlFinal["cadena"], $xmlFinal["firma"], $xmlFinal["xml"], $rfc["rfc"], $uuid, $impExp, $factura["TipoOperacion"], $factura["Patente"], $factura["Aduana"], $factura["Pedimento"], $factura["Referencia"], $factura["NumFactura"], $this->_session->username, $this->_session->email, isset($factura["Manual"]) ? 1 : null);
                    $idFact = $vucemFact->nuevaFactura($idSolicitud, $factura, $this->_session->username, isset($factura["Manual"]) ? 1 : null);
                    foreach ($factura["Productos"] as $prod) {
                        $vucemProd->nuevoProducto($idFact, $factura["IdFact"], $factura["Patente"], $factura["Aduana"], $factura["Pedimento"], $factura["Referencia"], $prod, $this->_session->username);
                    }
                    $referencias = new OAQ_Referencias(array("patente" => $factura["Patente"], "aduana" => $factura["Aduana"], "referencia" => $factura["Referencia"], "usuario" => "VucemCove"));
                    //$arr = $referencias->crearRepositorioSitawin();
                    $arr = $referencias->crearRepositorioRest($factura["Patente"], $factura["Aduana"], $factura["Referencia"]);
                    $this->_logger->logEntry("nuevaSolicitudAction", "SE BUSCO {$factura["Patente"]}-{$factura["Aduana"]}-{$factura["Referencia"]} Y SE OBUTVO {$arr["pedimento"]}-{$arr["rfcCliente"]} DE SISTEMA {$arr["sistema"]}", $_SERVER['REMOTE_ADDR'], $this->_session->username);
                    $responseXml = $vucem->enviarCoveVucem(str_replace("&", "&amp;", html_entity_decode($vucem->htmlSpanish($xmlFinal["xml"]), ENT_QUOTES, "UTF-8")), 'https://www.ventanillaunica.gob.mx/ventanilla/RecibirCoveService');
                    $resultVucem = $vucem->respuestaVucem($responseXml);
                    // AGREGAR LA SOLICITUDE NUEVO COVE A LA BASE DE DATOS
                    if (isset($resultVucem['operacion']) && $resultVucem['operacion'] != '') {
                        $vucemSol->actualizarSolicitudNueva($idSolicitud, $resultVucem['operacion'], $responseXml);
                        $update = $vucemFact->actualizarFactura($idSolicitud, $resultVucem['operacion']);
                        foreach ($factura["Productos"] as $prod) {
                            $vucemProd->actualizarProducto($factura["IdFact"], $resultVucem['operacion']);
                        }
                        if ($update) {
                            $tmpFact->borrarFactura($factura["id"], $factura["IdFact"], $this->_session->username);
                        }
                    }
                }
                unset($rfc);
                unset($pkeyid);
                unset($comprobantes);
                unset($xmlFinal);
                unset($impExp);
                unset($uuid);
                unset($responseXml);
                unset($resultVucem);
                unset($this->_svucem->facturas[$k]);
                $i++;
            }
            $this->_redirect("/vucem/index/index");
        } else {
            if(!isset($debug)) {
                return $this->_helper->redirector->gotoUrl("/vucem/index/nuevo-cove-facturas?error=" . urlencode("No se han seleccionado facturas."));
            }
        }
        if (isset($facturas)) {
            unset($facturas);
        }
    }

    public function consultarCoveEnviadoAction() {
        $this->view->title = $this->_appconfig->getParam("title")  . " Consulta el COVE enviado";
        $this->view->headMeta()->appendName("description", "");
        $fact = new Vucem_Model_VucemFacturasMapper();
        $vucemSol = new Vucem_Model_VucemSolicitudesMapper();
        $vucem = new OAQ_Vucem();
        $id = $this->_request->getParam('id');
        $debug = $this->_request->getParam('debug');
        if ($this->_session->role == 'super' || $this->_session->role == 'trafico_operaciones' || $this->_session->role == 'gerente') {
            $xml = $vucemSol->obtenerSolicitudPorId($id);
        } else {
            $xml = $vucemSol->obtenerSolicitudPorId($id, $this->_session->username);
        }
        if ($xml) {            
            $this->view->fechas = array(
                'enviado' => $xml['enviado'],
                'actualizado' => $xml['actualizado']
            );
            $xmlArray = $vucem->xmlStrToArray($xml["xml"]);
            if(isset($debug) && $debug == true) {
                unset($xmlArray["Header"]);
            }
            $res = new OAQ_VucemRespuestas();
            $resvu = $res->analizarRespuesta($xml["respuesta_vu"]);
            if(isset($resvu) && $resvu["error"] == true) {
                $firmante = $xmlArray["Header"]["Security"]["UsernameToken"]["Username"];
                $fir = new Vucem_Model_VucemFirmanteMapper();
                $sello = $fir->obtenerDetalleFirmante($firmante, null, $xml["patente"], $xml["aduana"]);
                if (isset($sello["validoHasta"])) {
                    if (time() > strtotime($sello["validoHasta"])) {
                        $this->view->selloNoVigente = array(
                            "rfc" => $firmante,
                            "vigencia" => $sello["validoHasta"],
                        );
                    }
                }
                $this->view->error = $resvu["messages"];
            }
            if(!isset($xmlArray) || empty($xmlArray) || $xmlArray == null) {
                return false;
            }
            if (strtotime($xml["creado"]) > strtotime('2014-06-09 11:00:00')) {
                array_walk_recursive($xmlArray, function (&$value) {
                    $value = utf8_decode($value);
                });
            }
            unset($xmlArray["Header"]);
            if ($xml["cove"] != '' && $xml["cove"] != null) {
                $this->view->cove = $xml["cove"];
            }
            $this->view->uuid = $fact->obtenerUuidFactura($id);
            $this->view->id = $id;
            $this->view->estatus = $xml["estatus"];
            $this->view->solicitud = $xml["solicitud"];
            $this->view->pedimento = $xml["pedimento"];
            $this->view->referencia = $xml["referencia"];
            if (isset($xmlArray["Body"]["solicitarRecibirCoveServicio"])) {
                $this->view->relfact = false;
                $this->view->data = $xmlArray["Body"]["solicitarRecibirCoveServicio"]["comprobantes"];
            } elseif (isset($xmlArray["Body"]["solicitarRecibirRelacionFacturasIAServicio"])) {
                $this->view->relfact = true;
                $this->view->data = $xmlArray["Body"]["solicitarRecibirRelacionFacturasIAServicio"]["comprobantes"];
            }
        }
    }

    public function verErrorCoveAction() {
        $this->view->title = $this->_appconfig->getParam("title")  . " Error en COVE";
        $this->view->headMeta()->appendName("description", "");
        $vucemSol = new Vucem_Model_VucemSolicitudesMapper();
        $vucem = new OAQ_Vucem();
        $id = $this->_request->getParam('id');
        $xml = $vucemSol->obtenerRespuestaVU($id);
        $xmlArray = $vucem->vucemXmlToArray($xml["respuesta_vu"]);

        if (isset($xmlArray["Body"]["solicitarConsultarRespuestaCoveServicioResponse"]["numeroOperacion"])) {
            $this->view->operacion = $xmlArray["Body"]["solicitarConsultarRespuestaCoveServicioResponse"]["numeroOperacion"];
        } elseif (isset($xml["solicitud"])) {
            $this->view->operacion = $xml["solicitud"];
        }
        if (isset($xmlArray["Body"]["solicitarConsultarRespuestaCoveServicioResponse"]["respuestasOperaciones"]["numeroFacturaORelacionFacturas"])) {
            $this->view->factura = $xmlArray["Body"]["solicitarConsultarRespuestaCoveServicioResponse"]["respuestasOperaciones"]["numeroFacturaORelacionFacturas"];
        } elseif (isset($xml["factura"])) {
            $this->view->factura = $xml["factura"];
        }

        if (isset($xmlArray["Body"]["solicitarConsultarRespuestaCoveServicioResponse"]["respuestasOperaciones"]["errores"]["mensaje"])) {
            $this->view->errores = $xmlArray["Body"]["solicitarConsultarRespuestaCoveServicioResponse"]["respuestasOperaciones"]["errores"]["mensaje"];
        } elseif (isset($xmlArray["Body"]["solicitarConsultarRespuestaCoveServicioResponse"]["leyenda"])) {
            $this->view->errores = $xmlArray["Body"]["solicitarConsultarRespuestaCoveServicioResponse"]["leyenda"];
        }
    }

    public function verErrorEdocAction() {
        $this->view->title = $this->_appconfig->getParam("title")  . " E-Document error";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headLink()
                ->appendStylesheet('/css/nuevo-estilo.css')
                ->appendStylesheet('/less/traffic-module.css');
        $vucem = new OAQ_Vucem();
        $vucemEdoc = new Vucem_Model_VucemEdocMapper();
        $id = $this->_request->getParam('id');
        $xml = $vucemEdoc->obtenerRespuestaVU($id);

        if ($xml["respuesta_vu"] != '' && $xml["respuesta_vu"] != NULL) {
            $xmlArray = $vucem->vucemXmlToArray($xml["respuesta_vu"]);
            $this->view->operacion = $xml["solicitud"];
            $this->view->errores = $xmlArray["Body"]["consultaDigitalizarDocumentoServiceResponse"]["respuestaBase"]["error"]["mensaje"];
        } elseif ($xml["respuesta"] != '' && $xml["respuesta"] != NULL) {
            $xmlArray = $vucem->vucemXmlToArray($xml["respuesta"]);
            if (isset($xmlArray["Body"])) {
                if (isset($xmlArray["Body"]["Fault"]["faultstring"])) {
                    $this->view->errores = $xmlArray["Body"]["Fault"]["faultstring"];
                    $mensajes = $xmlArray["Body"]["registroDigitalizarDocumentoServiceResponse"]["respuestaBase"]["error"]["mensaje"];
                }
                if (is_array($mensajes)) {
                    foreach ($mensajes as $m) {
                        $this->view->errores .= '<p>' . $m . '</p>';
                    }
                } else {
                    $this->view->errores = $mensajes;
                }
            }
        }
    }

    public function emailsVucemAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $storage = new Zend_Mail_Storage_Imap(array(
            'host' => $this->_config->app->vucemsmtp,
            'user' => $this->_config->app->vucememail,
            'password' => $this->_config->app->vucempass,
        ));
        $solicitudes = new Vucem_Model_VucemSolicitudesMapper();
        foreach ($storage as $msgId => $message):
            if ($message->hasFlag(Zend_Mail_Storage::FLAG_DELETED)) {
                $del[] = $msgId;
                continue;
            }
            preg_match('/<([^\\"]+)>/', $message->from, $email);
            if (isset($email[1])):
                if (preg_match("/mesa_servicio@ventanillaunica.gob.mx/i", (String) $email[1]) || preg_match("/notificaciones@ventanillaunica.gob.mx/i", (String) $email[1])) {
                    if (preg_match('/No. de petici/i', $message->subject)) {

                        $numOp = explode(':', str_replace(' ', '', $message->subject));
                        if ($solicitudes->buscarPeticion($numOp[1])) {

                            if ($message->isMultipart()) {
                                foreach (new RecursiveIteratorIterator($storage->getMessage($msgId)) as $part) {
                                    $formats = array(
                                        'text/plain',
                                    );
                                    $contentType = explode(';', $part->contentType);
                                    if (in_array(trim($contentType[0]), $formats)) {
                                        foreach ($contentType as $type) {
                                            if (preg_match('/^charset/', str_replace('"', '', ltrim($type)))) {
                                                $charset = explode('=', str_replace('"', '', ltrim($type)));
                                            }
                                        }
                                        foreach ($contentType as $type) {
                                            if (preg_match('/^name/', str_replace('"', '', ltrim($type)))) {
                                                $attach = str_replace('"', '', ltrim($type));
                                                $attachment = explode('=', $attach);
                                                if (preg_match('/.txt/i', $attachment[1])) {
                                                    if (preg_match('/UTF-8/i', $charset[1])) {
                                                        $content = utf8_encode(quoted_printable_decode($part->getContent()));
                                                    }
                                                    if (preg_match('/us-ascii/i', $charset[1])) {
                                                        $content = $part->getContent();
                                                    }
                                                    if (isset($content)) {
                                                        $xml = simplexml_load_string($content);
                                                        $xmlArray = @json_decode(@json_encode($xml), 1);

                                                        if ($xmlArray["respuestasOperaciones"]["contieneError"] == 'false') {
                                                            $solicitudes->actualizarSolicitud($xmlArray["numeroOperacion"], $content, $xmlArray["respuestasOperaciones"]["numeroFacturaORelacionFacturas"], $xmlArray["respuestasOperaciones"]["eDocument"], 2);
                                                        } elseif ($xmlArray["respuestasOperaciones"]["contieneError"] == 'true') {
                                                            $solicitudes->actualizarSolicitud($xmlArray["numeroOperacion"], $content, $xmlArray["respuestasOperaciones"]["numeroFacturaORelacionFacturas"], null, 0);
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            } // es multiparte
                        } // si se encuentra solicitud
                    }
                } // email de ventanilla
            endif;
        endforeach;
    }

    public function eDocumentsAction() {
        $this->view->title = $this->_appconfig->getParam("title")  . " E-Documents";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headScript()
                ->appendFile("/js/vucem/index/e-documents.js?" . time());
        $gets = $this->_request->getParams();
        $this->view->cove = isset($gets["edoc"]) ? $gets["edoc"] : null;
        $this->view->referencia = isset($gets["referencia"]) ? $gets["referencia"] : null;
        $this->view->pedimento = isset($gets["pedimento"]) ? $gets["pedimento"] : null;
        if (isset($this->_svucem->nvoEdocDir)) {
            if (file_exists($this->_svucem->nvoEdocDir)) {
                $iterator = new DirectoryIterator(realpath($this->_svucem->nvoEdocDir));
                foreach ($iterator as $fileinfo) {
                    if ($fileinfo->isFile()) {
                        unlink(realpath($this->_svucem->nvoEdocDir) . DIRECTORY_SEPARATOR . $fileinfo->getFilename());
                    }
                }
            }
            if (file_exists($this->_svucem->nvoEdocDir)) {
                rmdir($this->_svucem->nvoEdocDir);
            }
        }
        if (isset($this->_svucem->nvoEdocSol)) {
            unset($this->_svucem->nvoEdocSol);
            unset($this->_svucem->nvoEdocPed);
            unset($this->_svucem->nvoEdocPat);
            unset($this->_svucem->nvoEdocAdu);
            unset($this->_svucem->nvoEdocRef);
            unset($this->_svucem->nvoEdocRfc);
            unset($this->_svucem->nvoEdocDoc);
            unset($this->_svucem->nvoEdocDir);
        }
        if (isset($this->_svucem->edfiles)) {
            unset($this->_svucem->edReferencia);
            unset($this->_svucem->edAduana);
            unset($this->_svucem->edPedimento);
            unset($this->_svucem->edPatente);
            unset($this->_svucem->edFirmante);
            unset($this->_svucem->edfiles);
        }
        $edocs = new Vucem_Model_VucemEdocMapper();
        if (isset($gets["edoc"]) || isset($gets["referencia"]) || isset($gets["pedimento"])) {
            $result = $edocs->buscarSolicitudes(
                    ($this->_session->role != 'super' && $this->_session->role != 'trafico_operaciones') ? $this->_svucem->username : null, ($gets["edoc"] != '') ? trim($gets["edoc"]) : null, ($gets["referencia"] != '') ? trim($gets["referencia"]) : null, ($gets["pedimento"] != '') ? trim($gets["pedimento"]) : null, (isset($gets["factura"]) && $gets["factura"] != '') ? trim($gets["factura"]) : null
            );
        } else {
            if ($this->_session->role == "corresponsal") {
                $referencias = new OAQ_Referencias();
                $res = $referencias->restriccionesAduanas($this->_session->id, $this->_session->role);
                if (!empty($res["aduanas"])) {
                    $result = $edocs->obtenerEdocumentsCorresponsal($res["aduanas"]);
                }
            } else {
                $result = $edocs->obtenerSolicitudes(($this->_session->role != 'super' && $this->_session->role != 'trafico_operaciones') ? $this->_session->username : null);                
            }
        }
        if(in_array($this->_session->role, array("super"))) {
            $this->view->delete = true;
        }
        if (!empty($result)) {
            $this->view->result = $result;
        }
    }

    public function consultarEdocEnviadoAction() {
        $this->view->title = $this->_appconfig->getParam("title") . " E-Document enviado";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headLink()
                ->appendStylesheet("/v2/js/common/confirm/jquery-confirm.min.css");
        $this->view->headScript()
                ->appendFile("/v2/js/common/confirm/jquery-confirm.min.js")
                ->appendFile("/js/vucem/index/consultar-edoc-enviado.js?" . time());
        $f = array(
            "uuid" => array("StringTrim", "StripTags", "StringToLower"),
            "solicitud" => array("Digits", "StringTrim", "StripTags"),
        );
        $v = array(
            "uuid" => "NotEmpty",
            "solicitud" => array("NotEmpty", new Zend_Validate_Int()),
        );
        $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
        if ($input->isValid("solicitud")) {
            $mppr = new Vucem_Model_VucemEdocMapper();
            if ($this->_session->role == "super" || $this->_session->role == "trafico_operaciones" || $this->_session->role == 'gerente') {
                $arr = $mppr->obtenerEdocPorUuid($input->uuid, $input->solicitud);
                $this->view->data = $arr;
                $this->view->idEdoc = $arr["id"];
                $this->view->id = $input->uuid;
                $this->view->solicitud = $input->solicitud;
            } else {
                $arr = $mppr->obtenerEdocPorUuid($input->uuid, $input->solicitud);
                $this->view->data = $arr;
                $this->view->idEdoc = $arr["id"];
                $this->view->id = $input->uuid;
                $this->view->solicitud = $input->solicitud;
            }
        } else {
            throw new Exception("Invalid input!");
        }
    }

    public function cargarXmlAction() {
        $this->view->title = $this->_appconfig->getParam("title")  . " Cargar Xml";
        $this->view->headMeta()->appendName("description", "");
        $vucem = new OAQ_Vucem();
        $misc = new OAQ_Misc();
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $adapter = new Zend_File_Transfer_Adapter_Http();
            $adapter->setDestination('/tmp');
            $upload = new Zend_File_Transfer();
            $upload->addValidator('ExcludeExtension', false, array('php', 'exe', 'case' => true));
            $upload->addValidator('Extension', false, array('xml', 'case' => false));
            if (!$upload->isValid()) {
                $messages = $upload->getMessages();
                $this->view->errors = $messages;
            } else {
                $adapter->receive();
                $names = $upload->getFileName();
                $xml = file_get_contents($names);
                $xmlArray = $vucem->vucemXmlToArray($xml);
                unset($xmlArray['Body']['solicitarRecibirCoveServicio']['comprobantes']['firmaElectronica']);
                $cove = $xmlArray['Body']['solicitarRecibirCoveServicio']['comprobantes'];
                unset($xmlArray);
                $newFact = array();
                $newFact["Manual"] = true;
                $newFact["IdFact"] = $misc->getUuid(time());
                $newFact["CertificadoOrigen"] = $misc->arrayKeys($cove, "factura", "certificadoOrigen");
                $newFact["Subdivision"] = $misc->arrayKeys($cove, "factura", "subdivision");
                $newFact["NumExportador"] = $misc->arrayKeys($cove, "factura", "numeroExportadorAutorizado");
                $newFact["TipoOperacion"] = $misc->arrayKeys($cove, "tipoOperacion");
                $newFact["Patente"] = $this->_svucem->patente;
                $newFact["Aduana"] = $this->_svucem->aduana;
                $newFact["Pedimento"] = $data["pedimento"];
                $newFact["Referencia"] = $data["referencia"];
                $newFact["NumFactura"] = $misc->arrayKeys($cove, "numeroFacturaOriginal");
                $newFact["FechaFactura"] = $misc->arrayKeys($cove, "fechaExpedicion");
                $newFact["Observaciones"] = $misc->arrayKeys($cove, "observaciones");
                $newFact["CvePro"] = "XML";
                $newFact["ProIden"] = $vucem->tipoIdentificador($misc->arrayKeys($cove, "emisor", "identificacion"), $misc->arrayKeys($cove, "emisor", "pais"));
                $newFact["ProTaxID"] = $misc->arrayKeys($cove, "emisor", "identificacion");
                $newFact["ProNombre"] = $misc->arrayKeys($cove, "emisor", "nombre");
                $newFact["ProCalle"] = $misc->arrayKeys($cove, "emisor", "domicilio", "calle");
                $newFact["ProNumExt"] = $misc->arrayKeys($cove, "emisor", "domicilio", "numeroExterior");
                $newFact["ProNumInt"] = $misc->arrayKeys($cove, "emisor", "domicilio", "numeroInterior");
                $newFact["ProColonia"] = $misc->arrayKeys($cove, "emisor", "domicilio", "colonia");
                $newFact["ProLocalidad"] = $misc->arrayKeys($cove, "emisor", "domicilio", "localidad");
                $newFact["ProCP"] = $misc->arrayKeys($cove, "emisor", "domicilio", "codigoPostal");
                $newFact["ProMun"] = $misc->arrayKeys($cove, "emisor", "domicilio", "municipio");
                $newFact["ProEdo"] = $misc->arrayKeys($cove, "emisor", "domicilio", "entidadFederativa");
                $newFact["ProPais"] = $misc->arrayKeys($cove, "emisor", "domicilio", "pais");
                $newFact["CveCli"] = "XML";
                $newFact["CteIden"] = $vucem->tipoIdentificador($misc->arrayKeys($cove, "destinatario", "identificacion"), $misc->arrayKeys($cove, "destinatario", "pais"));
                $newFact["CteRfc"] = $misc->arrayKeys($cove, "destinatario", "identificacion");
                $newFact["CteNombre"] = $misc->arrayKeys($cove, "destinatario", "nombre");
                $newFact["CteCalle"] = $misc->arrayKeys($cove, "destinatario", "domicilio", "calle");
                $newFact["CteNumExt"] = $misc->arrayKeys($cove, "destinatario", "domicilio", "numeroExterior");
                $newFact["CteNumInt"] = $misc->arrayKeys($cove, "destinatario", "domicilio", "numeroInterior");
                $newFact["CteColonia"] = $misc->arrayKeys($cove, "destinatario", "domicilio", "colonia");
                $newFact["CteLocalidad"] = $misc->arrayKeys($cove, "destinatario", "domicilio", "localidad");
                $newFact["CteCP"] = $misc->arrayKeys($cove, "destinatario", "domicilio", "codigoPostal");
                $newFact["CteMun"] = $misc->arrayKeys($cove, "destinatario", "domicilio", "municipio");
                $newFact["CteEdo"] = $misc->arrayKeys($cove, "destinatario", "domicilio", "entidadFederativa");
                $newFact["CtePais"] = $misc->arrayKeys($cove, "destinatario", "domicilio", "pais");
                $newProd = array();
                if (isset($cove["mercancias"])) {
                    if (isset($cove["mercancias"]["descripcionGenerica"])) {
                        $newProd[] = array(
                            'ID_PROD' => $misc->getUuid(1 . $newFact["IdFact"]),
                            'CODIGO' => null,
                            'PARTE' => null,
                            'DESC_COVE' => $cove["mercancias"]["descripcionGenerica"],
                            'PAIORI' => null,
                            'PAICOM' => null,
                            'SUB' => null,
                            'CERTLC' => null,
                            'PREUNI' => $cove["mercancias"]["valorUnitario"],
                            'VALCOM' => $cove["mercancias"]["valorTotal"],
                            'MONVAL' => $cove["mercancias"]["tipoMoneda"],
                            'FACTAJU' => null,
                            'VALMN' => null,
                            'VALDLS' => $cove["mercancias"]["valorDolares"],
                            'UMC' => null,
                            'UMT' => null,
                            'CANTFAC' => $cove["mercancias"]["cantidad"],
                            'CAN_OMA' => $cove["mercancias"]["cantidad"],
                            'UMC_OMA' => $cove["mercancias"]["claveUnidadMedida"],
                        );
                    } else {
                        foreach ($cove["mercancias"] as $k => $merc) {
                            $newProd[] = array(
                                'ID_PROD' => $misc->getUuid($k . $newFact["IdFact"]),
                                'CODIGO' => null,
                                'PARTE' => null,
                                'DESC_COVE' => $merc["descripcionGenerica"],
                                'PAIORI' => null,
                                'PAICOM' => null,
                                'SUB' => null,
                                'CERTLC' => null,
                                'PREUNI' => $merc["valorTotal"],
                                'VALCOM' => $merc["valorTotal"],
                                'MONVAL' => $merc["tipoMoneda"],
                                'FACTAJU' => null,
                                'VALMN' => null,
                                'VALDLS' => $merc["valorDolares"],
                                'UMC' => null,
                                'UMT' => null,
                                'CANTFAC' => $merc["cantidad"],
                                'CAN_OMA' => $merc["cantidad"],
                                'UMC_OMA' => $merc["claveUnidadMedida"],
                            );
                        }
                    }
                }
                $tmpFact = new Vucem_Model_VucemTmpFacturasMapper();
                $tmpProd = new Vucem_Model_VucemTmpProductosMapper();
                $idTmpFact = $tmpFact->nuevaFactura($this->_svucem->solicitante, $this->_svucem->tipoFigura, $this->_svucem->patente, $this->_svucem->aduana, $newFact, $this->_session->username, 1);
                foreach ($newProd as $p) {
                    $tmpProd->nuevoProducto($idTmpFact, $newFact["IdFact"], $this->_svucem->patente, $this->_svucem->aduana, $newFact["Pedimento"], $newFact["Referencia"], $p, $this->_session->username);
                }
                $this->_redirector->goToUrl('/vucem/index/nuevo-cove-facturas');
            }
        }
    }

    public function agregarNuevaFacturaAction() {
        $this->view->title = $this->_appconfig->getParam("title")  . " Agregar nueva factura";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headLink()                
                ->appendStylesheet("/css/rich_calendar.css")
                ->appendStylesheet("/css/default/zebra_dialog.css");
        $this->view->headScript()
                ->appendFile("/js/common/typeahead.min.js")
                ->appendFile("/js/common/zebra_dialog.js")
                ->appendFile("/js/common/rich_calendar.js")
                ->appendFile("/js/common/rc_lang_en.js")
                ->appendFile("/js/common/domready.js")
                ->appendFile("/js/vucem/index/agregar-nueva-factura.js?" . time());
        $vucem = new OAQ_VucemEnh();
        $uuid = $this->_request->getParam('uuid', null);
        if (isset($uuid)) {
            $tmpFact = new Vucem_Model_VucemTmpFacturasMapper();
            if ($tmpFact->verify($uuid, $this->_session->username)) {
                $factura = $tmpFact->obtenerFactura($uuid, $this->_session->username);
                if ($factura["TipoOperacion"] == "TOCE.EXP") {
                    if (!isset($factura["ProIden"])) {
                        $factura["ProIden"] = $vucem->tipoIdentificador($factura["CteRfc"], $factura["CtePais"]);                        
                    }
                    if (!isset($factura["CteIden"])) {
                        $factura["CteIden"] = $vucem->tipoIdentificador($factura["ProTaxID"], $factura["ProPais"]);                        
                    }
                } else {
                    if (!isset($factura["CteIden"])) {
                        $factura["CteIden"] = $vucem->tipoIdentificador($factura["CteRfc"], $factura["CtePais"]);
                    }
                    if (!isset($factura["ProIden"])) {
                        $factura["ProIden"] = $vucem->tipoIdentificador($factura["ProTaxID"], $factura["ProPais"]);                        
                    }
                }
                $factura["FechaFactura"] = date('Y-m-d', strtotime($factura["FechaFactura"]));
                unset($factura["id"]);
                unset($factura["figura"]);
                unset($factura["adenda"]);
                unset($factura["Creado"]);
                unset($factura["Modificado"]);
                unset($factura["Usuario"]);
                unset($factura["Active"]);
                unset($factura["OrdenFact"]);
                unset($factura["OrdenFactCon"]);
                unset($factura["ValDls"]);
                unset($factura["ValExt"]);
                unset($factura["RelFact"]);
                unset($factura["CveImp"]);
                unset($factura["CvePro"]);
                $this->_svucem->uuidFactura = $factura["IdFact"];
                $this->view->uuidFactura = $factura["IdFact"];
            }
        }
        $this->view->patente = $this->_svucem->patente;
        $this->view->aduana = $this->_svucem->aduana;
        $this->view->tipoOperacion = $this->_svucem->tipoOperacion;
        $form = new Vucem_Form_NuevaFactura();
        $form->populate(array(
            "firmante" => $this->_svucem->solicitante,
            "TipoOperacion" => $this->_svucem->tipoOperacion,
            "Patente" => $this->_svucem->patente,
            "Aduana" => $this->_svucem->aduana,
            "FactFacAju" => $factura["FactorEquivalencia"],
        ));
        $misc = new OAQ_Misc();
        if (isset($factura)) {            
            $form->populate($factura); // ???????????????????????????????????????????????????
        } elseif (isset($this->_svucem->solicitante) && !isset($factura)) {
            $firmante = new Vucem_Model_VucemClientesMapper();
            $cliente = $firmante->datosCliente($this->_svucem->solicitante);
            if (isset($cliente) && !empty($cliente)) {
                if ($this->_svucem->tipoOperacion == "TOCE.EXP") {
                    $form->populate($misc->datosFacturaProveedor($cliente));
                } elseif ($this->_svucem->tipoOperacion == "TOCE.IMP") {
                    $form->populate($misc->datosFacturaCliente($cliente));
                }
            }
        }
        if (isset($this->_svucem->uuidFactura) && $this->_svucem->uuidFactura != '') {
            $tmpFact = new Vucem_Model_VucemTmpFacturasMapper();
            $factura = $tmpFact->obtenerFactura($this->_svucem->uuidFactura, $this->_session->username);
            if ($factura) {
                $factura["FechaFactura"] = date("Y-m-d", strtotime($factura["FechaFactura"]));
                if ($factura["TipoOperacion"] == "TOCE.EXP" && ($factura["Manual"] == "0" || $factura["Manual"] == null)) {
                    $form->populate($misc->populateArrayExpo($factura));
                } elseif($factura["TipoOperacion"] == "TOCE.IMP") {
                    $form->populate($misc->populateArrayImpo($factura));
                }
            } else {
                unset($this->_svucem->uuidFactura);
            }
        }
        $this->view->form = $form;
        $formprod = new Vucem_Form_AddNewProduct();
        $this->view->formprod = $formprod;
    }

    public function digitalizarDocumentosAction() {
        $this->view->title = $this->_appconfig->getParam("title")  . " Digitalizar documentos";
        $this->view->headMeta()
                ->appendName("description", "");
        $this->view->headLink()
                ->appendStylesheet("/css/jquery.selectBoxIt.css");
        $this->view->headScript()
                ->appendFile("/js/common/jquery-ui-1.9.2.min.js")
                ->appendFile("/js/common/jquery.selectBoxIt.min.js")
                ->appendFile("/js/vucem/index/digitalizar-documentos.js?" . time());
        $this->_svucem = NULL ? $this->_svucem = new Zend_Session_Namespace('') : $this->_svucem = new Zend_Session_Namespace('OAQVucem');
        if (!file_exists($this->_svucem->edtmp)) {
            $ed = '/tmp' . DIRECTORY_SEPARATOR . 'ed_' . md5(time());
            mkdir($ed, 0777, true);
            $this->_svucem->edtmp = $ed;
        }
        $form = new Vucem_Form_MultiplesEDocuments();
        $form->populate(array(
            'referencia' => (isset($this->_svucem->edReferencia)) ? $this->_svucem->edReferencia : null,
            'aduana' => (isset($this->_svucem->edAduana)) ? $this->_svucem->edAduana : null,
            'pedimento' => (isset($this->_svucem->edPedimento)) ? $this->_svucem->edPedimento : null,
            'patente' => (isset($this->_svucem->edPatente)) ? $this->_svucem->edPatente : null,
            'firmante' => (isset($this->_svucem->edFirmante)) ? $this->_svucem->edFirmante : null,
            'rfc' => (isset($this->_svucem->edRfcconsulta)) ? $this->_svucem->edRfcconsulta : null,
        ));
        $this->view->form = $form;
    }
    
    public function bitacoraAction() {
        $this->view->title = $this->_appconfig->getParam("title") . " Bitacora";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headLink()
                ->appendStylesheet("/css/jquery.qtip.min.css")
                ->appendStylesheet("/v2/js/common/confirm/jquery-confirm.min.css")
                ->appendStylesheet("/js/common/modal/magnific-popup.css")
                ->appendStylesheet("/js/common/highlight/styles/monokai.css")
                ->appendStylesheet("/js/common/bootstrap/datepicker/css/datepicker.css")
                ->appendStylesheet("/js/common/contentxmenu/jquery.contextMenu.min.css")
                ->appendStylesheet("/css/jquery.timepicker.css")
                ->appendStylesheet("/js/common/toast/jquery.toast.min.css");
        $this->view->headScript()
                ->appendFile("/js/common/jquery.timepicker.min.js")
                ->appendFile("/js/common/fakeLoader.min.js")
                ->appendFile("/v2/js/common/confirm/jquery-confirm.min.js")
                ->appendFile("/js/common/jqModal.js")
                ->appendFile("/js/common/jquery.qtip.min.js")
                ->appendFile("/js/common/bootstrap/bootstrap-datepicker/js/bootstrap-datepicker.js")
                ->appendFile("/js/common/bootstrap/bootstrap-datepicker/js/locales/bootstrap-datepicker.es.js")
                ->appendFile("/js/common/contentxmenu/jquery.contextMenu.min.js")
                ->appendFile("/js/common/toast/jquery.toast.min.js?")
                ->appendFile("/easyui/jquery.easyui.min.js")
                ->appendFile("/easyui/jquery.edatagrid.js")
                ->appendFile("/easyui/datagrid-filter.js");
        
        $f = array(
            "page" => array("Digits"),
            "rows" => array("Digits"),
        );
        $v = array(
            "page" => array(new Zend_Validate_Int(), "default" => 1),
            "rows" => array(new Zend_Validate_Int(), "default" => 20),
        );
        $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
        
        $mppr = new Trafico_Model_TraficoVucem();

        $sql = $mppr->ultimasOperaciones($input->page, $input->rows);

        $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_DbSelect($sql));
        $paginator->setCurrentPageNumber($input->page);
        $paginator->setItemCountPerPage($input->rows);
        $rows = (array) $paginator->getCurrentItems();
        
        if (!empty($rows)) {
            $this->view->result = $rows;
            $this->view->paginator = $paginator;
        }        
                        
    }

}
