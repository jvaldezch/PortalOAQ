<?php

class Archivo_IndexController extends Zend_Controller_Action {

    protected $_session;
    protected $_soapClient;
    protected $_config;
    protected $_appconfig;
    protected $_redirector;
    protected $_arch;

    public function init() {
        $this->_appconfig = new Application_Model_ConfigMapper();
        $this->_redirector = $this->_helper->getHelper("Redirector");
        $this->view->headLink(array("rel" => "icon shortcut", "href" => "/favicon.png"));
        $this->view->headLink()
                ->appendStylesheet("/js/common/bootstrap/css/bootstrap.min.css")
                ->appendStylesheet("/js/common/bootstrap/datepicker/css/datepicker.css")
                ->appendStylesheet("/css/fontawesome/css/fontawesome-all.min.css")
                ->appendStylesheet("/less/traffic-module.css?" . time());
        $this->view->headScript()
                ->appendFile("/js/common/jquery-1.9.1.min.js")
                ->appendFile("/js/common/bootstrap/js/bootstrap.min.js")
                ->appendFile("/js/common/bootstrap/datepicker/js/bootstrap-datepicker.js")
                ->appendFile("/js/common/jquery.form.min.js")
                ->appendFile("/js/common/jquery.validate.min.js")
                ->appendFile("/js/common/js.cookie.js")
                ->appendFile("/js/common/jquery.blockUI.js")
                ->appendFile("/js/common/principal.js?" . time());
        $this->_config = new Zend_Config_Ini(APPLICATION_PATH . "/configs/application.ini", APPLICATION_ENV);
        $context = stream_context_create(array(
            "ssl" => array(
                "verify_peer" => false,
                "verify_peer_name" => false,
                "allow_self_signed" => true
            )
        ));
        $this->_soapClient = new Zend_Soap_Client($this->_config->app->endpoint, array("stream_context" => $context));
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
        $this->_arch = NULL ? $this->_arch = new Zend_Session_Namespace("") : $this->_arch = new Zend_Session_Namespace("Navigation");
        $this->_arch->setExpirationSeconds($this->_appconfig->getParam("session-exp"));
        $news = new Application_Model_NoticiasInternas();
        $this->view->noticias = $news->obtenerTodos();
    }

    public function cuentasDeGastoAction() {
        $this->view->title = $this->_appconfig->getParam("title") . " Cuentas de gasto";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headLink()
                ->appendStylesheet("/v2/js/common/confirm/jquery-confirm.min.css")
                ->appendStylesheet("/js/common/toast/jquery.toast.min.css")
                ->appendStylesheet("/js/common/bootstrap/datepicker/css/datepicker.css")
                ->appendStylesheet("/easyui/themes/default/easyui.css")
                ->appendStylesheet("/easyui/themes/icon.css");
        $this->view->headScript()
                ->appendFile("/v2/js/common/confirm/jquery-confirm.min.js")
                ->appendFile("/js/common/toast/jquery.toast.min.js?")
                ->appendFile("/easyui/jquery.easyui.min.js")
                ->appendFile("/easyui/jquery.edatagrid.js")
                ->appendFile("/easyui/datagrid-filter.js")
                ->appendFile("/easyui/locale/easyui-lang-es.js")
                ->appendFile("/fullcalendar/lib/moment.min.js")
                ->appendFile("/js/common/bootstrap/bootstrap-datepicker/js/bootstrap-datepicker.js")
                ->appendFile("/js/common/bootstrap/bootstrap-datepicker/js/locales/bootstrap-datepicker.es.js")
                ->appendFile("/js/archivo/index/cuentas-de-gasto.js?" . time());
        $f = array(
            "*" => array("StringTrim", "StripTags"),
            "page" => "Digits",
            "size" => "Digits",
            "rfc" => array("StringToUpper"),
            "nombre" => array("StringToUpper"),
        );
        $v = array(
            "page" => array(new Zend_Validate_Int(), "default" => 1),
            "size" => array(new Zend_Validate_Int(), "default" => 30),
            "rfc" => array("NotEmpty", new Zend_Validate_Regex("/^[A-Z]{3,4}([0-9]{2})([0-9]{2})([0-9]{2})?[A-Z0-9]{3,4}/")),
            "fechaIni" => array(new Zend_Validate_Regex("/^\d{4}\-\d{2}\-\d{2}$/"), "default" => date("Y-m-") . "01"),
            "fechaFin" => array(new Zend_Validate_Regex("/^\d{4}\-\d{2}\-\d{2}$/"), "default" => date("Y-m-d")),
            "nombre" => "NotEmpty",
        );
        $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
        //$this->view->email = $this->_session->email;
        //$archive = new Archivo_Model_CuentasGastosMapper();
        $this->view->cofidi = array(
            "MME921204HZ4",
            "BAP060906LEA",
            "JMM931208JY9",
            "GAM950228IZ5",
            "GCM9010126L2",
            "SME751021B90",
        );
        $form = new Archivo_Form_CtaGastos();
        $form->populate(array(
            "rfc" => $input->rfc,
            "nombre" => $input->nombre,
            "fechaIni" => $input->fechaIni,
            "fechaFin" => $input->fechaFin,
        ));
        /*if ($input->isValid("rfc")) {
            $result = $archive->getAll($input->fechaIni, $input->fechaFin, $input->rfc);
        } else {
            $result = $archive->getAll($input->fechaIni, $input->fechaFin);
        }
        if (isset($result)) {
            $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_Array($result));
            $paginator->setItemCountPerPage($input->size);
            $paginator->setCurrentPageNumber($input->page);
            $this->view->paginator = $paginator;
        }*/
        $this->view->form = $form;
    }

    public function readFileAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $misc = new OAQ_Misc();
        $archive = new Archivo_Model_CuentasGastosMapper();
        $id = $misc->myDecrypt($this->_request->getParam("id"));
        $type = $this->_request->getParam("tipo");
        $file = $archive->getFilePath($id);
        if ($type == "pdf") {
            header("Content-type: application/pdf");
            header("Content-disposition: attachment; filename=" . str_replace(".xml", "", $file["nom_archivo"] . ".pdf"));
            $filecontent = file_get_contents($file["ubicacion_pdf"]);
        } else if ($type == "xml") {
            header("Content-type: Content-type: text/xml; charset=utf-8");
            header("Content-disposition: attachment; filename=" . $file["nom_archivo"]);
            $filecontent = file_get_contents($file["ubicacion_xml"]);
        }
        echo $filecontent;
    }
    
    public function indexAction() {
        $this->view->title = $this->_appconfig->getParam("title") . " Expediente Digital";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headScript()
                ->appendFile("/js/archivo/index/index.js?" . time())
                ->appendFile("/js/common/js.cookie.js")
                ->appendFile("/js/common/bootstrap/bootstrap-datepicker/js/bootstrap-datepicker.js")
                ->appendFile("/js/common/bootstrap/bootstrap-datepicker/js/locales/bootstrap-datepicker.es.js");
        $request = new Zend_Controller_Request_Http();
        $filtro = filter_var($request->getCookie("filtro"), FILTER_VALIDATE_INT);
        
        if ($request->getCookie("fecha-fin")) {
            $fecha_fin = filter_var($request->getCookie("fecha-fin"),FILTER_VALIDATE_REGEXP,array("options"=>array("regexp"=> '/^\d{4}-\d{2}-\d{2}$/')));
            $this->view->fecha_fin = $fecha_fin;
        } else {
            $fecha_fin = date("Y-m-d");
            setcookie('fecha-fin', $fecha_fin, time() + (3600 * 24 * 5), '/');
        }
        
        if ($request->getCookie("fecha-inicio")) {
            $fecha_inicio = filter_var($request->getCookie("fecha-inicio"),FILTER_VALIDATE_REGEXP,array("options"=>array("regexp"=> '/^\d{4}-\d{2}-\d{2}$/')));
            $this->view->fecha_inicio = $fecha_inicio;
        } else {
            $fecha_inicio = date('Y-m-d', strtotime('-90 day', strtotime($fecha_fin)));
            setcookie('fecha-inicio', $fecha_inicio, time() + (3600 * 24 * 5), '/');
        }
        
        $this->view->fecha_inicio = $fecha_inicio;
        $this->view->fecha_fin = $fecha_fin;
        
        $f = array(
            "*" => array("StringTrim", "StripTags"),
            "page" => "Digits",
            "size" => "Digits",
            "aduana" => "Digits",
            "patente" => "Digits",
            "referencia" => "StringToUpper",
            "rfcCliente" => "StringToUpper",
        );
        $v = array(
            "aduana" => array(new Zend_Validate_Int()),
            "patente" => array(new Zend_Validate_Int()),
            "pedimento" => array("NotEmpty"),
            "page" => array(new Zend_Validate_Int(), "default" => 1),
            "size" => array(new Zend_Validate_Int(), "default" => 25),
            "referencia" => array(new Zend_Validate_Regex("/^[-_a-zA-Z0-9.\/]+$/")),
            "rfcCliente" => array(new Zend_Validate_Regex("/^[-_a-zA-Z0-9.\/]+$/")),
        );
        $i = new Zend_Filter_Input($f, $v, $this->_request->getParams());
        $form = new Archivo_Form_Referencias();
        $repo = new Archivo_Model_RepositorioIndex();
        $referencias = new OAQ_Referencias();
        $res = $referencias->restricciones($this->_session->id, $this->_session->role);
        
        $search = array(
            $i->isValid("patente") ? $i->patente : null, 
            $i->isValid("aduana") ? $i->aduana : null, 
            $i->isValid("pedimento") ? str_pad($i->pedimento, 7, '0', STR_PAD_LEFT) : null, 
            $i->isValid("referencia") ? $i->referencia : null, 
            $i->isValid("rfcCliente") ? $i->rfcCliente : null, 
            $fecha_inicio, 
            $fecha_fin
        );

        if ($this->_session->role !== "inhouse" && $this->_session->role !== "proveedor" && $this->_session->role !== "corresponsal") {
            if (!empty($res["idsAduana"])) {
                $select = $repo->paginatorSelect($filtro, $search, $res["idsAduana"], $res["rfcs"]);
            }
        } else {
            if ($this->_session->role == "corresponsal") {
                $referencias = new OAQ_Referencias();
                $res = $referencias->restriccionesAduanas($this->_session->id, $this->_session->role);
                $select = $repo->paginatorSelectCorresponsales($filtro, $search, $res["aduanas"]);
            }
            if ($this->_session->role == "proveedor") {
                $this->view->disableUpload = true;
            }
            if(!empty($res["rfcs"])) {
                $select = $repo->paginatorSelect($filtro, $search, null, $res["rfcs"]);
            } else {
                $this->view->error = 'Su usuario no tiene RFC asignado(s), favor de comunicarse con <a href="mailto:soporte@oaq.com.mx">soporte@oaq.com.mx</a>';
            }
        }
        if (isset($select) && !empty($select)) {
            $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_DbSelect($select));
            $paginator->setCurrentPageNumber($i->page);
            $paginator->setItemCountPerPage($i->size);
            $this->view->paginator = $paginator;
        }
        $form->populate(array(
            "patente" => $i->patente,
            "aduana" => $i->aduana,
            "pedimento" => str_pad($i->pedimento, 7, '0', STR_PAD_LEFT),
            "referencia" => $i->referencia,
            "rfcCliente" => $i->rfcCliente,
        ));
        $this->view->rol = $this->_session->role;
        $this->view->form = $form;
    }

    public function referenciasAction() {
        $this->view->title = $this->_appconfig->getParam("title") . " Repositorio de referencias";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headScript()
                ->appendFile("/js/archivo/index/referencias.js?" . time());
        $request = new Zend_Controller_Request_Http();
        $revisadosOp = $request->getCookie("revisadosOp");
        $revisadosAdm = $request->getCookie("revisadosAdm");
        $revisados = $request->getCookie("revisados");
        $completos = $request->getCookie("completos");
        $f = array(
            "*" => array("StringTrim", "StripTags"),
            "page" => "Digits",
            "size" => "Digits",
            "aduana" => "Digits",
            "patente" => "Digits",
            "referencia" => "StringToUpper",
        );
        $v = array(
            "aduana" => array(new Zend_Validate_Int()),
            "patente" => array(new Zend_Validate_Int()),
            "pedimento" => array("NotEmpty"),
            "page" => array(new Zend_Validate_Int(), "default" => 1),
            "size" => array(new Zend_Validate_Int(), "default" => 25),
            "referencia" => array(new Zend_Validate_Regex("/^[-_a-zA-Z0-9.]+$/")),
        );
        $i = new Zend_Filter_Input($f, $v, $this->_request->getParams());
        $form = new Archivo_Form_Referencias();
        $arch = new Archivo_Model_RepositorioMapper();
        $model = new Application_Model_UsuariosAduanasMapper();
        $aduanas = $model->aduanasUsuario($this->_session->id, $i->patente, $i->aduana);
        $inh = new Usuarios_Model_UsuarioInhouse();
        if(!in_array($this->_session->role, array("inhouse"))) {
            if (isset($aduanas) && !empty($aduanas)) {
                if ($aduanas["patente"][0] != "0" && $aduanas["aduana"][0] != "0") {
                    if (isset($i->referencia) && $i->referencia != "") {
                        $result = $arch->search($i->referencia, $aduanas["patente"], $aduanas["aduana"]);
                    } elseif (isset($i->pedimento) && $i->pedimento != "") {
                        $result = $arch->searchByDocument($i->pedimento, $aduanas["patente"], $aduanas["aduana"]);
                    } else {
                        $select = $arch->paginatorSelect($aduanas["patente"], $aduanas["aduana"], $revisados, $revisadosOp, $revisadosAdm, $completos);
                    }
                } else {
                    if (isset($i->referencia) && $i->referencia != "") {
                        $result = $arch->search($i->referencia);
                    } elseif (isset($i->pedimento) && $i->pedimento != "") {
                        $result = $arch->searchByDocument($i->pedimento);
                    } else {
                        $select = $arch->paginatorSelect(null, null, $revisados, $revisadosOp, $revisadosAdm, $completos);
                    }
                }
            } else {
                $this->view->error = "NO TIENE ADUANAS ASIGNADAS: ENVIAR UN CORREO A <a href=\"mailto:soporte@oaq.com.mx\" style=\"color: #00FF00;\">soporte@oaq.com.mx</a> SOLICITANDO LA ACTIVACIÓN.";
            }
        } else { // inhouse
            $rfcs = $inh->obtenerRfcClientes($this->_session->id);
            if(isset($rfcs) && !empty($rfcs)) {
                if (isset($i->referencia) && $i->referencia != "") {
                    $result = $arch->searchInhouse($i->referencia, $rfcs);
                } elseif (isset($i->pedimento) && $i->pedimento != "") {
                    $result = $arch->searchByDocumentInhouse($i->pedimento, $rfcs);
                } else {
                    $select = $arch->paginatorSelectInhouse($rfcs, $revisados, $completos);                
                }           
            } else {
                $this->view->error = "NO TIENE RFC DE CLIENTE ASIGNADO: ENVIAR UN CORREO A <a href=\"mailto:soporte@oaq.com.mx\" style=\"color: #0000ff;\">soporte@oaq.com.mx</a>.";
            }
        }
        if (isset($select) && !empty($select)) {
            $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_DbSelect($select));
            $paginator->setCurrentPageNumber($i->page);
            $paginator->setItemCountPerPage($i->size);
            $this->view->paginator = $paginator;
        }
        if (isset($result) && !empty($result)) {
            $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_Array($result));
            $paginator->setCurrentPageNumber($i->page);
            $paginator->setItemCountPerPage($i->size);
            $this->view->paginator = $paginator;
        }
        $form->populate(array(
            "patente" => $i->patente,
            "aduana" => $i->aduana,
            "pedimento" => str_pad($i->pedimento, 7, '0', STR_PAD_LEFT),
            "referencia" => $i->referencia,
        ));
        $this->view->rol = $this->_session->role;
        $this->view->form = $form;
    }

    public function modificarExpedienteAction() {
        $this->view->title = $this->_appconfig->getParam("title") . " Modificar expediente";
        $this->view->headMeta()->appendName("description", "");
    }
    
    public function modificarReferenciaAction() {
        $this->view->title = $this->_appconfig->getParam("title") . " Modificar referencia";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headLink()
                ->appendStylesheet("/css/jquery.selectBoxIt.css")
                ->appendStylesheet("/css/nuevo-estilo.css?")
                ->appendStylesheet("/less/traffic-module.css?" . time());
        $this->view->headScript()
                ->appendFile("/js/common/jquery.form.min.js")
                ->appendFile("/js/common/jquery.validate.min.js")
                ->appendFile("/js/common/additional-methods.min.js")
                ->appendFile("/js/common/jquery-ui-1.9.2.min.js")
                ->appendFile("/js/common/jquery.selectBoxIt.min.js")
                ->appendFile("/js/archivo/index/modificar-referencia.js?" . time());
        $gets = $this->_request->getParams();
        $model = new Archivo_Model_RepositorioMapper();
        $info = $model->getInfo($gets["patente"], $gets["ref"]);
        $this->view->data = array(
            "patente" => $gets["patente"],
            "aduana" => $gets["aduana"],
            "referencia" => $gets["ref"],
            "pedimento" => isset($info["pedimento"]) ? str_pad($info["pedimento"], 7, '0', STR_PAD_LEFT) : null,
            "rfcCliente" => isset($info["rfc_cliente"]) ? $info["rfc_cliente"] : null,
        );
    }

    public function subirArchivosAction() {
        $this->view->title = $this->_appconfig->getParam("title") . " Subir archivos";
        $this->view->headMeta()->appendName("description", "");

        $nav = NULL ? $nav = new Zend_Session_Namespace("") : $nav = new Zend_Session_Namespace("Navigation");
        $upload = NULL ? $upload = new Zend_Session_Namespace("") : $upload = new Zend_Session_Namespace("Upload");

        $this->view->headLink()->setContainer(
                new Zend_View_Helper_Placeholder_Container()
        );
        $this->view->headScript()->exchangeArray(array());
        $this->view->headLink()
                ->appendStylesheet("/css/general/stylesheet.css")
                ->appendStylesheet("/bootstrap/css/bootstrap.min.css")
                ->appendStylesheet("/js/jquery-upload/css/jquery.fileupload-ui.css")
                ->appendStylesheet($this->_appconfig->getParam("main-css"))
                ->appendStylesheet($this->_appconfig->getParam("bootstrap-css"));
        $this->view->headLink(array("rel" => "icon shortcut", "href" => "/favicon.png"));
        $this->view->headScript()->appendFile("/js/jquery-upload/js/jquery-1.10.2.min.js")
                ->appendFile("/js/jquery-upload/js/vendor/jquery.ui.widget.js")
                ->appendFile("/js/jquery-upload/js/tmpl.min.js")
                ->appendFile("/js/jquery-upload/js/load-image.min.js")
                ->appendFile("/js/jquery-upload/js/canvas-to-blob.min.js")
                ->appendFile("/js/jquery-upload/js/bootstrap.min.js")
                ->appendFile("/js/jquery-upload/js/jquery.fileupload.js")
                ->appendFile("/js/jquery-upload/js/jquery.fileupload-process.js")
                ->appendFile("/js/jquery-upload/js/jquery.fileupload-image.js")
                ->appendFile("/js/jquery-upload/js/jquery.fileupload-audio.js")
                ->appendFile("/js/jquery-upload/js/jquery.fileupload-video.js")
                ->appendFile("/js/jquery-upload/js/jquery.fileupload-validate.js")
                ->appendFile("/js/jquery-upload/js/jquery.fileupload-ui.js")
                ->appendFile("/js/jquery-upload/js/main.js")
                ->appendFile("/js/date.js")
                ->appendFile("/js/principal.js");

        $ref = $this->_request->getParam("ref");
        $pat = $this->_request->getParam("patente");
        $adu = $this->_request->getParam("aduana");
        $y = $this->_request->getParam("year");

        $docs = new Archivo_Model_DocumentosMapper();
        $d = $docs->getAll();

        $this->view->documentos = $d;
        $this->view->referencia = $ref;
        $this->view->patente = $pat;
        $this->view->aduana = $adu;
        $this->view->year = $y;
        $this->view->referer = $nav->referer;

        if (!isset($upload->tmpDir)) {
            $upload->tmpDir = sha1($ref . time());
        }
    }

    public function descargarArchivoAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $misc = new OAQ_Misc();
        $id = $misc->myDecrypt($this->_request->getParam("id"));
        $tipo = $this->_request->getParam("type", null);

        $rep = new Archivo_Model_RepositorioMapper();
        $file = $rep->getFileById($id);

        if (isset($tipo) && $tipo == "pdf") {
            header("Content-type: application/x-pdf");
            header("Content-disposition: attachment; filename=" . str_replace(".xml", "", $file["nom_archivo"]) . ".pdf");
            $filecontent = file_get_contents($file["ubicacion_pdf"]);
            echo $filecontent;
        } else {
            header($misc->fileHeader($file["nom_archivo"]));
            header("Content-disposition: attachment; filename=" . $file["nom_archivo"]);

            if (file_exists($file["ubicacion"] . DIRECTORY_SEPARATOR . $file["nom_archivo"])) {
                $filecontent = file_get_contents($file["ubicacion"] . DIRECTORY_SEPARATOR . $file["nom_archivo"]);
                echo $filecontent;
                exit;
            }
            if (file_exists($file["ubicacion"])) {
                $filecontent = file_get_contents($file["ubicacion"]);
                echo $filecontent;
                exit;
            }
        }
    }

    public function crearZipProveedoresAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $search = NULL ? $search = new Zend_Session_Namespace("") : $search = new Zend_Session_Namespace("OAQCtaGastos");

        $archive = new Archivo_Model_RepositorioMapper();
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            if ($data["ids"] == "0") {
                if (!isset($search->arch_rfc) && isset($search->arch_fini) && isset($search->arch_ffin)) {
                    $result = $archive->getXmlPaths($search->arch_fini, $search->arch_ffin);
                } if (isset($search->arch_rfc) && isset($search->arch_fini) && isset($search->arch_ffin)) {
                    $result = $archive->getXmlPaths($search->arch_fini, $search->arch_ffin, $search->arch_rfc, $search->arch_rfcc);
                }
            } else {
                $ids = array();
                foreach ($data["ids"] as $item) {
                    $ids[] = $item;
                }
                $result = $archive->getXmlPathsLinux(null, null, null, null, $ids);
            }

            $misc = new OAQ_Misc();
            $zipName = "Facturas_" . date("Y-m-d", time()) . "_" . $misc->alphaID(time()) . ".zip";
            $zipFilename = realpath("/tmp") . DIRECTORY_SEPARATOR . $zipName;

            if (!empty($result)) {
                $zip = new ZipArchive();
                if ($zip->open($zipFilename, ZIPARCHIVE::CREATE) !== TRUE) {
                    return null;
                }
                foreach ($result as $file) {
                    if (file_exists($file)) {
                        $zip->addFile($file, basename($file));
                    }
                }
                $zip->close();
                if (file_exists($zipFilename)) {
                    echo Zend_Json::encode($zipFilename);
                }
            }
        }
    }

    public function crearZipAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $search = NULL ? $search = new Zend_Session_Namespace("") : $search = new Zend_Session_Namespace("OAQCtaGastos");
        $archive = new Archivo_Model_CuentasGastosMapper();
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            if ($data["ids"] == "0") {
                if (!isset($search->arch_rfc) && isset($search->arch_fini) && isset($search->arch_ffin)) {
                    $result = $archive->getXmlPaths($search->arch_fini, $search->arch_ffin);
                } if (isset($search->arch_rfc) && isset($search->arch_fini) && isset($search->arch_ffin)) {
                    $result = $archive->getXmlPaths($search->arch_fini, $search->arch_ffin, $search->arch_rfc);
                }
            } else {
                $ids = array();
                foreach ($data["ids"] as $item) {
                    $ids[] = $item;
                }
                $result = $archive->getXmlPaths(null, null, null, $ids);
            }
            if (!file_exists("/tmp/zipcuentas")) {
                mkdir("/tmp/zipcuentas", 0777, true);
            }
            if (!empty($result)) {
                $misc = new OAQ_Misc();
                $zipName = "Cuentas_" . date("Y-m-d", time()) . "_" . $misc->alphaID(time()) . ".zip";
                $zipFilename = "/tmp/zipcuentas" . DIRECTORY_SEPARATOR . $zipName;
                $created = $misc->createZipFile($result, $zipFilename);
                if ($created == true) {
                    echo Zend_Json::encode($zipFilename);
                }
            }
        }
    }

    public function downloadCreatedZipAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $filename = $this->_request->getParam("filename");

        header("Content-type: application/zip");
        header('Content-Disposition: attachment; filename="' . basename($filename) . '"');
        header("Cache-Control: no-store, no-cache, must-revalidate");
        header("Pragma: no-cache");
        header("Content-length: " . filesize(realpath($filename)));
        header("Expires: 0");
        readfile(realpath($filename));
        unlink($filename);
    }

    public function enviarEmailAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(false);
        $search = NULL ? $search = new Zend_Session_Namespace("") : $search = new Zend_Session_Namespace("OAQCtaGastos");
        if ($search->sum == true) {
            $emails[] = array(
                "nombre" => "David Lopez",
                "email" => "david.lopez@oaq.com.mx",
            );
            $this->view->emails = $emails;
            $search->emails = $emails;
        }
    }

    public function movimientosReferenciaAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(false);

        $ref = $this->_request->getParam("ref", null);
        if ($ref) {
            $sica = new OAQ_Sica();
            $movs = $sica->referenceDetails($ref);

            $this->view->movimientos = $movs;
        }
    }

    public function loadFileAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(false);

        $id = $this->_request->getParam("id", null);
        if ($id) {
            $misc = new OAQ_Misc();
            $id = $misc->myDecrypt($id);

            $archive = new Archivo_Model_RepositorioMapper();

            $fileinfo = $archive->getFileById($id);
            if (file_exists($fileinfo["ubicacion"])) {
                $binary = fread(fopen($fileinfo["ubicacion"], "r"), filesize($fileinfo["ubicacion"]));
                $this->view->pdf = base64_encode($binary);
            } elseif (file_exists($fileinfo["ubicacion_pdf"])) {
                $binary = fread(fopen($fileinfo["ubicacion_pdf"], "r"), filesize($fileinfo["ubicacion_pdf"]));
                $this->view->pdf = base64_encode($binary);
            }
        }
    }

    public function loadFileRepoAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(false);
        error_reporting(E_ALL);
        ini_set("display_errors", 1);
        $id = $this->_request->getParam("id", null);
        if ($id) {
            $archive = new Archivo_Model_RepositorioMapper();
            $fileinfo = $archive->getFileById($id);
            if (file_exists($fileinfo["ubicacion"])) {
                $ext = pathinfo(basename($fileinfo["ubicacion"]), PATHINFO_EXTENSION);
                if (preg_match("/pdf/i", $ext)) {
                    $binary = file_get_contents($fileinfo["ubicacion"]);
                    $this->view->pdf = base64_encode($binary);
                    unset($binary);
                } elseif (preg_match("/xml/i", $ext)) {
                    header("Content-Type:text/xml");
                    $binary = fread(fopen($fileinfo["ubicacion"], "r"), filesize($fileinfo["ubicacion"]));
                    echo $this->_cleanXml($binary);
                    $this->view->xml = true;
                    unset($binary);
                } elseif (preg_match("/xlsx/i", $ext)) {
                    header("Content-Type: application/vnd.ms-excel; charset=utf-8");
                    header("Content-Disposition: attachment;filename=\"" . basename($fileinfo["ubicacion"]) . "\"");
                    $binary = fread(fopen($fileinfo["ubicacion"], "r"), filesize($fileinfo["ubicacion"]));
                    echo $this->_cleanXml($binary);
                    $this->view->xml = true;
                    unset($binary);
                }
            }
        }
    }

    protected function _cleanXml($xml) {
        return preg_replace("#<soapenv:Header(.*?)>(.*?)</soapenv:Header>#is", "", $xml);
    }

    public function proveedoresAction() {
        $this->view->title = $this->_appconfig->getParam("title") . " Facturas proveedores";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headLink()
                ->appendStylesheet("/js/common/bootstrap/datepicker/css/datepicker.css")
                ->appendStylesheet("/css/jquery.timepicker.css");
        $this->view->headScript()
                ->appendFile("/js/common/bootstrap/bootstrap-datepicker/js/bootstrap-datepicker.js")
                ->appendFile("/js/common/bootstrap/bootstrap-datepicker/js/locales/bootstrap-datepicker.es.js")
                ->appendFile("/js/archivo/index/proveedores.js?" . time());
        $form = new Archivo_Form_Proveedores();
        $f = array(
            "*" => array("StringTrim", "StripTags"),
            "page" => array("Digits"),
            "size" => array("Digits"),
            "folio" => array("Digits"),
            "rfc" => array("StringToUpper"),
            "rfcCliente" => array("StringToUpper"),
        );
        $v = array(
            "page" => array("NotEmpty", new Zend_Validate_Int(), "default" => 1),
            "size" => array(new Zend_Validate_Int(), "default" => 20),
            "folio" => array("NotEmpty", new Zend_Validate_Int()),
            "fechaIni" => array("NotEmpty", new Zend_Validate_Regex("/^\d{4}\-\d{2}\-\d{2}$/")),
            "fechaFin" => array("NotEmpty", new Zend_Validate_Regex("/^\d{4}\-\d{2}\-\d{2}$/")),
            "rfc" => "NotEmpty",
            "rfcCliente" => "NotEmpty",
        );
        $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
        $mppr = new Archivo_Model_RepositorioMapper();
        $form->populate(array(
            "rfc" => $input->isValid("rfc") ? $input->rfc : "",
            "rfcCliente" => $input->isValid("rfcCliente") ? $input->rfc : "",
            "folio" => $input->isValid("folio") ? $input->folio : "",
            "fechaIni" => $input->isValid("fechaIni") ? $input->fechaIni : date("Y-m" . "-01"),
            "fechaFin" => $input->isValid("fechaFin") ? $input->fechaFin : date("Y-m-d"),
        ));
        $result = $mppr->obtenerArchivosProveedor($input->rfc, $input->fechaIni, $input->fechaFin, $input->folio, $input->rfcCliente);
        if (isset($result)) {
            $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_Array($result));
            $paginator->setItemCountPerPage($input->size);
            $paginator->setCurrentPageNumber($input->page);
            $this->view->paginator = $paginator;
        }
        $this->view->form = $form;
    }
    
    public function nuevoRepositorioAction() {
        $this->view->title = $this->_appconfig->getParam("title") . " Archivos expediente";
        $this->view->headLink()
                ->appendStylesheet("/v2/js/common/confirm/jquery-confirm.min.css");
        $this->view->headMeta()->appendName("description", "");
        $this->view->headScript()
                ->appendFile("/v2/js/common/confirm/jquery-confirm.min.js")
                ->appendFile("/js/common/jquery-ui-1.9.2.min.js")
                ->appendFile("/js/archivo/index/nuevo-repositorio.js?" . time());
        if (in_array($this->_session->role, array("proveedor", "cliente", "inhouse"))) {
            $this->_helper->redirector->gotoUrl("/archivo");
        }
        $model = new Application_Model_UsuariosAduanasMapper();
        $patentes = $model->patentesUsuario($this->_session->id);
        $form = new Archivo_Form_NuevaReferencia(array("patentes" => $patentes, "id" => $this->_session->id));
        $this->view->form = $form;
        $this->view->id = $this->_session->id;
    }

    public function verArchivosReferenciaAction() {
        $this->view->title = $this->_appconfig->getParam("title") . " Archivos archivos de referencia";
        $this->view->headScript()->appendFile("/js/jquery.validate.min.js");

        $referencia = $this->_request->getParam("ref", null);
        $patente = $this->_request->getParam("patente", null);
        $aduana = $this->_request->getParam("aduana", null);
        if (isset($referencia) && isset($patente) && isset($aduana)) {
            $this->_arch->referencia = $this->_request->getParam("ref", null);
            $this->_arch->patente = $this->_request->getParam("patente", null);
            $this->_arch->aduana = $this->_request->getParam("aduana", null);
        }
        $form = new Archivo_Form_Clientes();
        if ($patente == 3589) {
            if ($aduana == 640 || $aduana == 646) {
                if ($aduana == 640) {
                    $db = new OAQ_Sitawin(true, "192.168.200.5", "sa", "adminOAQ123", "SITAW3010640", 1433, "Pdo_Mssql");
                } elseif ($aduana == 646) {
                    $db = new OAQ_Sitawin(true, "192.168.0.253", "sa", "sqlcointer", "SITAW3589640", 1433, "Pdo_Mssql");
                }
                $rfcCliente = $db->rfcReferencia($referencia);
                if (isset($rfcCliente)) {
                    $form->populate(array(
                        "rfc" => $rfcCliente["RFCCTE"],
                        "nombre" => $rfcCliente["NOMCLI"],
                    ));
                }
            }
        }

        $this->view->patente = $this->_arch->patente;
        $this->view->aduana = $this->_arch->aduana;
        $this->view->referencia = $this->_arch->referencia;
        $this->view->form = $form;
    }

    public function pedimentosPagadosAction() {
        $this->view->title = $this->_appconfig->getParam("title") . " Pedimentos Pagados (M3)";
        $this->view->headMeta()->appendName("description", "");
        $rfc = $this->_request->getParam("rfc", null);
        $patente = $this->_request->getParam("patente", null);
        $aduana = $this->_request->getParam("aduana", null);
        $pedimento = $this->_request->getParam("pedimento", null);
        $fechaIni = $this->_request->getParam("fechaIni", null);
        $fechaFin = $this->_request->getParam("fechaFin", null);
        $archivom3 = $this->_request->getParam("archivom3", null);
        $archivo = $this->_request->getParam("archivo", null);
        $form = new Archivo_Form_PedimentosPagados();
        $val = new OAQ_ArchivosM3();
        if (isset($rfc)) {
            if ((isset($archivom3) && $archivom3 === "") && (isset($pedimento) && $pedimento === "")) {
                $pagados = $val->pagados($rfc, $patente, $aduana, $fechaIni, $fechaFin, null);
                $this->view->pagados = $pagados;
            } elseif (isset($archivom3) && $archivom3 !== "") {

                if (isset($archivo) && $archivo == "validacion") {
                    $pagados = $val->searchM3($archivom3);
                    $this->view->validacion = true;
                } elseif (isset($archivo) && $archivo == "respuesta") {
                    $pagados = $val->searchPre($archivom3);
                    $this->view->respuesta = true;
                } elseif (isset($archivo) && $archivo == "pago") {
                    $pagados = $val->searchPaid($archivom3);
                    $this->view->paid = true;
                }
                $this->view->pagados = $pagados;
            } elseif (isset($pedimento) && $pedimento !== "") {
                $pagados = $val->pagados($rfc, $patente, $aduana, null, null, $pedimento);
                $this->view->pagados = $pagados;
            }
            $form->populate(array(
                "rfc" => $rfc,
                "fechaIni" => $fechaIni,
                "fechaFin" => $fechaFin,
                "archivom3" => $archivom3,
                "archivo" => $archivo,
                "pedimento" => $pedimento,
            ));
        }
        $this->view->module = "automatizacion";
        $this->view->controller = "index";
        $this->view->form = $form;
    }

    public function setFileTypeAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        if (!$this->getRequest()->isXmlHttpRequest()) {
            throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
        }
        $gets = $this->_request->getParams();
        if (isset($this->_arch->archivos[$gets["alpha"]])) {
            $this->_arch->archivos[$gets["alpha"]]["type"] = $gets["type"];
        }
    }

    public function analisisM3Action() {
        $this->view->title = $this->_appconfig->getParam("title") . " Analisis M3";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headScript()
                ->appendFile("/js/common/typeahead.min.js")
                ->appendFile("/js/common/jquery.fileDownload.js")
                ->appendFile("/js/archivo/index/analisis-m3.js?" . time());
        $form = new Archivo_Form_AnalisisM3();
        $this->view->form = $form;
    }

    public function nuevaReferenciaAction() {
        $this->view->title = $this->_appconfig->getParam("title") . " Archivos expediente";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headLink()
                ->appendStylesheet("/js/common/toast/jquery.toast.min.css")
                ->appendStylesheet("/css/jquery.qtip.min.css");
        $this->view->headScript()
                ->appendFile("/js/common/toast/jquery.toast.min.js?")
                ->appendFile("/js/common/jquery.qtip.min.js")
                ->appendFile("/js/common/jquery-ui-1.9.2.min.js")
                ->appendFile("/js/archivo/index/nueva-referencia.js?" . time());
        $model = new Application_Model_UsuariosAduanasMapper();
        $patentes = $model->patentesUsuario($this->_session->id);
        $form = new Archivo_Form_NuevaReferencia(array("patentes" => $patentes, "id" => $this->_session->id));
        $this->view->form = $form;
        $this->view->id = $this->_session->id;
    }
    
    public function expedienteAction() {
        $this->view->title = $this->_appconfig->getParam("title") . " Expediente";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headLink()
                ->appendStylesheet("/css/jquery.qtip.min.css")
                ->appendStylesheet("/v2/js/common/confirm/jquery-confirm.min.css")
                ->appendStylesheet("/js/common/toast/jquery.toast.min.css")
                ->appendStylesheet("/js/common/contentxmenu/jquery.contextMenu.min.css");
        $this->view->headScript()
                ->appendFile("/js/common/jquery.form.min.js")
                ->appendFile("/js/common/jquery.validate.min.js")
                ->appendFile("/js/common/jquery.qtip.min.js")
                ->appendFile("/v2/js/common/confirm/jquery-confirm.min.js")
                ->appendFile("/js/common/toast/jquery.toast.min.js?")
                ->appendFile("/js/common/contentxmenu/jquery.contextMenu.min.js")
                ->appendFile("/js/archivo/index/expediente.js?" . time());
        $f = array(
            "*" => array("StringTrim", "StripTags"),
        );
        $v = array(
            "id" => array("NotEmpty", new Zend_Validate_Int()),
        );
        $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
        if ($input->isValid("id")) {
            $referencias = new OAQ_Referencias();
            $res = $referencias->restricciones($this->_session->id, $this->_session->role);
            $mapper = new Archivo_Model_RepositorioIndex();
            $form = new Archivo_Form_ArchivosExpediente();
            $arr = $mapper->datos($input->id, $res["idsAduana"], $res["rfcs"]);
            $form->populate(array(
                "patente" => $arr["patente"],
                "aduana" => $arr["aduana"],
                "pedimento" => $arr["pedimento"],
                "referencia" => $arr["referencia"],
                "rfc_cliente" => $arr["rfcCliente"],
            ));
            if (isset($arr["idTrafico"]) && $arr["idTrafico"] !== null) {                
                $this->view->idTrafico = $arr["idTrafico"];
            }
            $this->view->form = $form;
            $this->view->id = $input->id;
            $repo = new Archivo_Model_RepositorioMapper();
            if (!in_array($this->_session->role, array("inhouse", "cliente", "proveedor"))) {
                if (!empty($arr)) {
                    $files = $repo->countFilesByReferenceUsers($arr["referencia"], $arr["patente"], $arr["aduana"]);
                } else {
                    $this->view->error = "El expediente no tiene datos o usted no cuenta con los permisos para consultar.";
                }
            } else {
                if ($this->_session->role == "proveedor") {
                    $this->view->disableUpload = true;
                }
                if ($this->_session->role == "inhouse") {
                    $this->view->noFtp = true;                    
                }
                $files = $repo->countFilesByReferenceCustomers($arr["referencia"], $arr["patente"], $arr["aduana"]);
            }
            if (empty($files)) {
                $this->view->empty = true;
            }
            if (strtoupper(substr(PHP_OS, 0, 3)) === "WIN") {
                
            } else {
                $directory = $this->_appconfig->getParam("expdest") . DIRECTORY_SEPARATOR . $arr["patente"] . DIRECTORY_SEPARATOR . $arr["aduana"] . DIRECTORY_SEPARATOR . $arr["referencia"];
                if (file_exists($directory)) {
                    $salida = shell_exec('du -h ' . $directory);
                    $array = explode(" ", preg_replace('/\s+/', ' ', trim($salida)));
                    if (isset($array[0])) {
                        $quantity = substr($array[0], 0, -1);
                        $measure = substr($array[0], -1);
                        if (isset($quantity) && isset($measure)) {
                            if ((int) $quantity > 25 && strtoupper($measure) == "M") {
                                if ($measure == "M") {
                                    $measure = "Mb";
                                } elseif ($measure == "K") {
                                    $measure = "Kb";
                                }
                                $this->view->downloadZip = array(
                                    "size" => $quantity . " " . $measure,
                                    "message" => "Tamaño del expediente: "
                                );
                            }
                        }
                    }
                }
            }
            if ($this->_session->role == "super") {
                $this->view->reload = true;
            }
        }
    }

    public function archivosExpedienteAction() {
        $this->view->title = $this->_appconfig->getParam("title") . " Archivos expediente";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headLink()
                ->appendStylesheet("/css/jquery.qtip.min.css")
                ->appendStylesheet("/css/jquery.selectBoxIt.css")
                ->appendStylesheet("/css/jqModal.css");
        $this->view->headScript()
                ->appendFile("/js/common/jquery.qtip.min.js")                
                ->appendFile("/js/common/additional-methods.min.js")
                ->appendFile("/js/common/jquery-ui-1.9.2.min.js")
                ->appendFile("/js/common/jqModal.js")
                ->appendFile("/js/archivo/index/archivos-expediente.js?" . time());
        $f = array(
            "*" => array("StringTrim", "StripTags", "StringToUpper"),
            "patente" => "Digits",
            "aduana" => "Digits",
            "pedimento" => "Digits",
        );
        $v = array(
            "*" => "NotEmpty",
        );
        $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
        if ($input->isValid("patente") || $input->isValid("aduana") || $input->isValid("pedimento") || $input->isValid("referencia")) {
            $gotIt = true;
        }
        $model = new Archivo_Model_RepositorioMapper();
        $info = $model->getInfo($input->patente, $input->ref);
        $in = new Zend_Filter_Input(array("*" => array("StringTrim", "StripTags", "StringToUpper"), "pedimento" => "Digits",), array("*" => "NotEmpty"), $info);
        if (!$in->isValid()) {
            $this->view->loadExternal = true;
        } else {
            if (($sinRfc = $model->sinPedimentoORfcCliente($input->patente, $input->ref))) {
                $model->actualizarPedimento($input->ref, $info["pedimento"], $info["rfc_cliente"]);
            }
        }
        if (isset($gotIt) && isset($input->pedimento) && isset($input->rfc_cliente)) {
            if (isset($gotIt) && ($input->pedimento != "" && $input->rfc_cliente != "")) {
                $referencia["referencia"] = $input->ref;
                $referencia["pedimento"] = $input->pedimento;
                $referencia["rfcCliente"] = $input->rfc_cliente;
            }
        }
        $this->view->gets = $input->getEscaped();
        $form = new Archivo_Form_ArchivosExpediente();
        if ((isset($referencia["pedimento"]) && $referencia["pedimento"] != "") || (isset($referencia["rfcCliente"]) && $referencia["rfcCliente"] != "")) {
            $model->actualizarPedimento($input->ref, $referencia["pedimento"], $referencia["rfcCliente"]);
        }
        $data = array(
            "patente" => $input->patente,
            "aduana" => $input->aduana,
            "pedimento" => ($info["pedimento"] !== null) ? $info["pedimento"] : ((isset($referencia) && $referencia !== false) ? $referencia["pedimento"] : ""),
            "rfc_cliente" => ($info["rfc_cliente"] !== null && $info["rfc_cliente"] != "") ? $info["rfc_cliente"] : ((isset($referencia) && $referencia !== false) ? $referencia["rfcCliente"] : ""),
            "referencia" => $input->ref,
        );
        $form->populate($data);
        $this->view->form = $form;
        if (isset($input->pedimento) && $input->pedimento != "") {
            $form->populate(array("pedimento" => $input->pedimento));
        }
        if (isset($input->rfc_cliente) && $input->rfc_cliente != "") {
            $form->populate(array("rfc_cliente" => $input->rfc_cliente));
        }
    }

    public function digitalizacionMasivaAction() {
        $this->view->title = $this->_appconfig->getParam("title") . " Digitalización masiva";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headScript()
                ->appendFile("/js/archivo/index/digitalizacion-masiva.js?" . time());
        $mapper = new Archivo_Model_RepositorioTemporalMapper();
        $rows = $mapper->fetchAll();
        $this->view->data = $rows;
    }

    public function archivosValidacionAction() {
        $this->view->title = $this->_appconfig->getParam("title") . " Archivos de validación";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headLink()
                ->appendStylesheet("/js/common/bootstrap/bootstrap-datepicker/css/datepicker.css")
                ->appendStylesheet("/css/jqModal.css");
        $this->view->headScript()
                ->appendFile("/js/common/bootstrap/bootstrap-datepicker/js/bootstrap-datepicker.js")
                ->appendFile("/js/common/bootstrap/bootstrap-datepicker/js/locales/bootstrap-datepicker.es.js")
                ->appendFile("/js/common/jqModal.js")
                ->appendFile("/js/archivo/index/archivos-validacion.js?" . time());
        $f = array(
            "page" => array("StringTrim", "StripTags", "Digits"),
            "aduana" => array("StringTrim", "StripTags", "Digits"),
            "fecha" => array("StringTrim", "StripTags"),
            "pedimento" => array("StringTrim", "StripTags", "Digits"),
        );
        $v = array(
            "page" => array(new Zend_Validate_Int(), "default" => "1"),
            "aduana" => new Zend_Validate_Int(),
            "fecha" => array(new Zend_Validate_Regex("/^\d{4}\-\d{2}\-\d{2}$/")),
            "pedimento" => new Zend_Validate_Int(),
        );
        $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
        $mapper = new Automatizacion_Model_ArchivosValidacionMapper();
        $mod = new Trafico_Model_TraficoAduanasMapper();
        $form = new Archivo_Form_ArchivosValidacion();

        if ($input->isValid("aduana") && $input->isValid("fecha")) {
            $adu = $mod->obtenerAduana($input->aduana);
            if ($adu !== false) {
                $arr = $mapper->archivosValidacion("m3", $input->fecha, $adu["patente"], $adu["aduana"]);
                $form->populate(array(
                    "aduana" => $input->aduana
                ));
            } else {
                $arr = $mapper->archivosValidacion("m3", $input->fecha);
            }
        } else {
            $arr = $mapper->archivosValidacion("m3", date("Y-m-d"));
        }

        if (isset($arr) && !empty($arr)) {
            $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_Array($arr));
            $paginator->setItemCountPerPage(50);
            $paginator->setCurrentPageNumber($input->page);
            $this->view->paginator = $paginator;
        }
        if ($input->isValid("fecha")) {
            $form->populate(array(
                "fecha" => $input->fecha,
                "pedimento" => $input->isValid("pedimento") ? $input->pedimento : null,
            ));
        } else {
            $form->populate(array(
                "fecha" => date("Y-m-d"),
            ));
        }
        $this->view->form = $form;
    }
    
    public function ftpAction() {
        $this->view->title = $this->_appconfig->getParam("title") . " FTP M3";
        $this->view->headMeta()->appendName("description", "");
        $f = array(
            "*" => array("StringTrim", "StripTags"),
            "page" => "Digits",
            "size" => "Digits",
        );
        $v = array(
            "page" => array(new Zend_Validate_Int(), "default" => 1),
            "size" => array(new Zend_Validate_Int(), "default" => 15),
        );
        $i = new Zend_Filter_Input($f, $v, $this->_request->getParams());
        $mapper = new Archivo_Model_FtpMapper();
        $arr = $mapper->fetchAll();
        if (isset($arr) && !empty($arr)) {
            $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_Array($arr));
            $paginator->setItemCountPerPage($i->size);
            $paginator->setCurrentPageNumber($i->page);
            $this->view->paginator = $paginator;
        }
    }

    public function reportesAction() {
        $this->view->title = $this->_appconfig->getParam("title") . " Reportes de expediente digital";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headLink()
                ->appendStylesheet("/css/jquery.selectBoxIt.css")
                ->appendStylesheet("/js/common/bootstrap/datepicker/css/datepicker.css");
        $this->view->headScript()
                ->appendFile("/js/common/jquery-ui-1.9.2.min.js")
                ->appendFile("/js/common/jquery.selectBoxIt.min.js")
                ->appendFile("/js/common/bootstrap/bootstrap-datepicker/js/bootstrap-datepicker.js")
                ->appendFile("/js/common/bootstrap/bootstrap-datepicker/js/locales/bootstrap-datepicker.es.js")
                ->appendFile("/js/archivo/index/reportes.js?" . time());
    }

    public function nuevoFtpAction() {
        $this->view->title = $this->_appconfig->getParam("title") . " NUEVO FTP";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headScript()
                ->appendFile("/js/archivo/index/nuevo-ftp.js?" . time());
        $form = new Trafico_Form_NuevoFtp();
        $this->view->form = $form;
    }

}
