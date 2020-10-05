<?php

class Operaciones_ReportesController extends Zend_Controller_Action
{

    protected $_session;
    protected $_config;
    protected $_appconfig;
    protected $_redirector;

    public function init()
    {
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
            ->appendFile("/js/common/jquery.form.min.js")
            ->appendFile("/js/common/jquery.validate.min.js")
            ->appendFile("/js/common/additional-methods.min.js")
            ->appendFile("/js/common/js.cookie.js")
            ->appendFile("/js/common/jquery.blockUI.js")
            ->appendFile("/js/common/mensajero.js?" . time())
            ->appendFile("/js/common/principal.js?" . time());
        $this->_config = new Zend_Config_Ini(APPLICATION_PATH . "/configs/application.ini", APPLICATION_ENV);
    }

    public function preDispatch()
    {
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
    }

    public function incidenciasAction()
    {
        $this->view->title = $this->_appconfig->getParam('title') . " Incidencias";
        $this->view->headMeta()->appendName('description', '');
        $this->view->headLink()
            ->appendStylesheet("/v2/js/common/confirm/jquery-confirm.min.css")
            ->appendStylesheet("/js/common/toast/jquery.toast.min.css")
            ->appendStylesheet("/easyui/themes/metro/easyui.css")
            ->appendStylesheet("/easyui/themes/icon.css")
            ->appendStylesheet("/easyui/themes/color.css")
            ->appendStylesheet("/js/common/contentxmenu/jquery.contextMenu.min.css");
        $this->view->headScript()
            ->appendFile("/v2/js/common/confirm/jquery-confirm.min.js")
            ->appendFile("/easyui/jquery.easyui.min.js")
            ->appendFile("/easyui/jquery.edatagrid.js")
            ->appendFile("/easyui/datagrid-filter.js")
            ->appendFile("/easyui/locale/easyui-lang-es.js")
            ->appendFile("/js/common/contentxmenu/jquery.contextMenu.min.js")
            ->appendFile("/js/common/bootstrap/bootstrap-datepicker/js/bootstrap-datepicker.js")
            ->appendFile("/js/common/bootstrap/bootstrap-datepicker/js/locales/bootstrap-datepicker.es.js")
            ->appendFile("/js/common/toast/jquery.toast.min.js?")
            ->appendFile("/js/operaciones/reportes/incidencias.js?" . time());
    }

    public function obtenerIncidenciasAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "page" => array("Digits"),
                "rows" => array("Digits"),
            );
            $v = array(
                "page" => array(new Zend_Validate_Int(), "default" => 1),
                "rows" => array(new Zend_Validate_Int(), "default" => 20),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("page") && $input->isValid("rows")) {

                $mppr = new Operaciones_Model_Incidencias();

                $select = $mppr->incidenciasSelect();

                $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_DbSelect($select));
                $paginator->setCurrentPageNumber($input->page);
                $paginator->setItemCountPerPage($input->rows);

                $arr = array(
                    "total" => $paginator->getTotalItemCount(),
                    "rows" => iterator_to_array($paginator),
                    "paginator" => $paginator->getPages(),
                );
                $this->_helper->json($arr);
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function editarIncidenciaAction()
    {
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "id" => array("Digits"),
            );
            $v = array(
                "id" => array(new Zend_Validate_NotEmpty(), new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("id")) {
                $view = new Zend_View();
                $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/reportes/");

                $mpr = new Operaciones_Model_Incidencias();
                $arr = $mpr->obtener($input->id);
                $view->row = $arr;

                $referencias = new OAQ_Referencias();
                $res = $referencias->restricciones($this->_session->id, $this->_session->role);

                $this->_todosClientes = array("trafico", "super", "trafico_ejecutivo");
                $mppr = new Trafico_Model_TraficoUsuAduanasMapper();

                if (in_array($this->_session->role, array("trafico", "super", "trafico_ejecutivo"))) {
                    $customs = $mppr->aduanasDeUsuario();
                }

                $arr_aduanas = array();
                if (!empty($customs)) {
                    foreach ($customs as $item) {
                        if ((int) $item["patente"] !== 0) {
                            $arr_aduanas[$item["id"]] = $item["patente"] . "-" . $item["aduana"] . ": " . $item["nombre"];
                        }
                    }
                }
                $view->aduanas = $arr_aduanas;

                if (!empty($res["idsClientes"])) {
                    $rows = $referencias->obtenerClientes(null, $res["idsClientes"]);
                    if (!empty($rows)) {
                        $view->clientes = $rows;
                    }
                }
                $er = new Operaciones_Model_IncidenciaTipoError();
                $view->tipoError = $er->obtener();

                $this->_helper->json(array("success" => true, "html" => $view->render("editar-incidencia.phtml")));
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function nuevaIncidenciaAction()
    {
        try {
            $view = new Zend_View();
            $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/reportes/");
            $referencias = new OAQ_Referencias();
            $res = $referencias->restricciones($this->_session->id, $this->_session->role);

            $this->_todosClientes = array("trafico", "super", "trafico_ejecutivo");
            $mppr = new Trafico_Model_TraficoUsuAduanasMapper();

            if (in_array($this->_session->role, array("trafico", "super", "trafico_ejecutivo"))) {
                $customs = $mppr->aduanasDeUsuario();
            }

            $arr_aduanas = array();
            if (!empty($customs)) {
                foreach ($customs as $item) {
                    if ((int) $item["patente"] !== 0) {
                        $arr_aduanas[$item["id"]] = $item["patente"] . "-" . $item["aduana"] . ": " . $item["nombre"];
                    }
                }
            }
            $view->aduanas = $arr_aduanas;

            if (!empty($res["idsClientes"])) {
                $rows = $referencias->obtenerClientes(null, $res["idsClientes"]);
                if (!empty($rows)) {
                    $view->clientes = $rows;
                }
            }
            $er = new Operaciones_Model_IncidenciaTipoError();
            $view->tipoError = $er->obtener();

            if (!empty($res["idsClientes"])) {
            }
            $this->_helper->json(array("success" => true, "html" => $view->render("nueva-incidencia.phtml")));
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function agregarIncidenciaAction()
    {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "idAduana" => "Digits",
                    "idCliente" => "Digits",
                    "referencia" => "StringToUpper",
                    "responsable" => "StringToUpper",
                    "observaciones" => "StringToUpper",
                    "comentarios" => "StringToUpper",
                );
                $v = array(
                    "idAduana" => array("NotEmpty", new Zend_Validate_Int()),
                    "idCliente" => array("NotEmpty", new Zend_Validate_Int()),
                    "idTipoError" => "NotEmpty",
                    "pedimento" => "NotEmpty",
                    "referencia" => "NotEmpty",
                    "fecha" => "NotEmpty",
                    "multa" => "NotEmpty",
                    "acta" => "NotEmpty",
                    "pagada" => "NotEmpty",
                    "responsable" => "NotEmpty",
                    "observaciones" => "NotEmpty",
                    "comentarios" => "NotEmpty",
                );
                $input = new Zend_Filter_Input($f, $v, $request->getPost());
                if ($input->isValid("idAduana") && $input->isValid("idCliente") && $input->isValid("idTipoError")) {
                    $mppr = new Operaciones_Model_Incidencias();

                    $arr = array(
                        "idAduana" => $input->idAduana,
                        "idCliente" => $input->idCliente,
                        "idTipoError" => $input->idTipoError,
                        "pedimento" => str_pad($input->pedimento, 7, '0', STR_PAD_LEFT),
                        "referencia" => $input->referencia,
                        "fecha" => $input->fecha,
                        "multa" => $input->multa,
                        "acta" => $input->acta,
                        "pagada" => ($input->pagada) ? 1 : null,
                        "responsable" => $input->responsable,
                        "observaciones" => $input->observaciones,
                        "comentarios" => $input->comentarios,
                        "creada" => date("Y-m-d H:i:s"),
                        "creada_por" => $this->_session->username,
                    );
                    if (!($mppr->verificar($input->idAduana, $input->idCliente, $input->pedimento, $input->referencia))) {
                        if (($mppr->agregar($arr))) {
                            $this->_helper->json(array("success" => true));
                        }
                    } else {
                        throw new Exception("El número de carta ya existe.");
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

    public function actualizarIncidenciaAction()
    {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $f = array(
                    "*" => array(new Zend_Filter_StringTrim(), new Zend_Filter_StripTags()),
                    "id" => new Zend_Filter_Digits(),
                    "referencia" => new Zend_Filter_StringToUpper(),
                    "responsable" => new Zend_Filter_StringToUpper(),
                    "observaciones" => new Zend_Filter_StringToUpper(),
                    "comentarios" => new Zend_Filter_StringToUpper(),
                );
                $v = array(
                    "id" => new Zend_Validate_NotEmpty(),
                    "pedimento" => new Zend_Validate_NotEmpty(),
                    "referencia" => new Zend_Validate_NotEmpty(),
                    "fecha" => new Zend_Validate_NotEmpty(),
                    "multa" => new Zend_Validate_NotEmpty(),
                    "acta" => new Zend_Validate_NotEmpty(),
                    "pagada" => new Zend_Validate_NotEmpty(),
                    "responsable" => new Zend_Validate_NotEmpty(),
                    "observaciones" => new Zend_Validate_NotEmpty(),
                    "comentarios" => new Zend_Validate_NotEmpty(),
                );
                $input = new Zend_Filter_Input($f, $v, $request->getPost());
                if ($input->isValid("id")) {
                    $mppr = new Operaciones_Model_Incidencias();
                    $arr = array(
                        "pedimento" => $input->pedimento,
                        "referencia" => $input->referencia,
                        "fecha" => $input->fecha,
                        "multa" => $input->multa,
                        "acta" => $input->acta,
                        "pagada" => ($input->pagada) ? 1 : null,
                        "responsable" => $input->responsable,
                        "observaciones" => $input->observaciones,
                        "comentarios" => $input->comentarios,
                        "actualizada" => date("Y-m-d H:i:s"),
                        "actualizada_por" => $this->_session->username,
                    );
                    if (($mppr->actualizar($input->id, $arr))) {
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

    public function borrarIncidenciaAction()
    {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $f = array(
                    "*" => array(new Zend_Filter_StringTrim(), new Zend_Filter_StripTags()),
                    "id" => new Zend_Filter_Digits()
                );
                $v = array(
                    "id" => new Zend_Validate_NotEmpty()
                );
                $input = new Zend_Filter_Input($f, $v, $request->getPost());
                if ($input->isValid("id")) {
                    $mppr = new Operaciones_Model_Incidencias();
                    if (($mppr->borrar($input->id))) {
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
}
