<?php

class Trafico_GetController extends Zend_Controller_Action
{

    protected $_session;
    protected $_config;
    protected $_appconfig;
    protected $_firephp;

    public function init()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_appconfig = new Application_Model_ConfigMapper();
        $this->_redirector = $this->_helper->getHelper("Redirector");
        $this->_config = new Zend_Config_Ini(APPLICATION_PATH . "/configs/application.ini", APPLICATION_ENV);
        $this->_logger = Zend_Registry::get("logDb");
        $this->_firephp = Zend_Registry::get("firephp");
    }

    public function preDispatch()
    {
        $this->_session = NULL ? $this->_session = new Zend_Session_Namespace("") : $this->_session = new Zend_Session_Namespace($this->_config->app->namespace);
        if ($this->_session->authenticated == true) {
            $session = new OAQ_Session($this->_session, $this->_appconfig);
            $session->actualizar();
            $session->actualizarSesion();
        } else {
            $this->getResponse()->setRedirect($this->_appconfig->getParam("link-logout"));
        }
    }

    public function ayudaDocumentosFiscalAction()
    {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $m = new Archivo_Model_DocumentosFiscalMapper();
            $view = new Zend_View();
            $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/data/");
            $view->documentos = $m->obtenerTodos();
            echo $view->render("documentos-fiscal.phtml");
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public function reporteTraficoAction()
    {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $view = new Zend_View();
            $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/data/");
            $mapper = new Trafico_Model_TraficoAduanasMapper();
            $table = new Application_Model_UsuariosAduanasMapper();
            if (in_array($this->_session->role, array("super", "trafico_operaciones", "trafico", "super_admon"))) {
                $view->filters = $mapper->obtenerTodas();
            } else {
                $arr = $table->traficoAduanasUsuario($this->_session->id);
                $view->filters = $mapper->obtenerTodas($arr);
            }
            echo $view->render("reporte-trafico.phtml");
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public function reportesTraficoAction()
    {
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "tipoReporte" => "Digits",
                "idAduana" => "Digits",
                "page" => array("Digits"),
                "size" => array("Digits"),
            );
            $v = array(
                "page" => array(new Zend_Validate_Int(), "default" => 1),
                "size" => array(new Zend_Validate_Int(), "default" => 20),
                "tipoReporte" => array("NotEmpty", new Zend_Validate_Int()),
                "idAduana" => array("NotEmpty", new Zend_Validate_Int()),
                "fechaInicio" => array("NotEmpty", new Zend_Validate_Regex("/^\d{4}\-\d{2}\-\d{2}$/")),
                "fechaFin" => array("NotEmpty", new Zend_Validate_Regex("/^\d{4}\-\d{2}\-\d{2}$/")),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("tipoReporte") && $input->isValid("idAduana") && $input->isValid("fechaInicio") && $input->isValid("fechaFin")) {
                $excel = new OAQ_ExcelReportes();
                if ($input->tipoReporte == 1) {
                    $traffics = new Trafico_Model_TraficosMapper();
                    $arr = $traffics->reporte($input->idAduana, $input->fechaInicio, $input->fechaFin);
                    $titulos = array("ADUANA", "CLIENTE", "ESQUEMA DE PAGO", "REFERENCIA", "PATENTE", "PEDIMENTO", "CLAVE", "T.OP.", "CARGA", "ARRIBO", "REVALIDADO", "PREVIO", "DEPÃ“SITO", "ENTRADA", "NOTIFICACION", "PRESENTACIÃ“N", "CITA DESPACHO", "FECHA PAGO", "LIBERACIÃ“N", "DÃ�AS EN PROCESO");
                    $this->_helper->layout()->disableLayout();
                    $this->_helper->viewRenderer->setNoRender(true);
                    $excel->setTitles($titulos);
                    $excel->setData($arr);
                    $excel->setFilename("TRAFICOS_" . date("Ymd-His") . ".xlsx");
                    $excel->layoutAnexo24Clientes();
                }
                if ($input->tipoReporte == 5) {
                    $traffics = new Trafico_Model_TraficosMapper();
                    $arr = $traffics->reporte($input->idAduana, $input->fechaInicio, $input->fechaFin, true);
                    $titulos = array("ADUANA", "CLIENTE", "ESQUEMA DE PAGO", "REFERENCIA", "PATENTE", "PEDIMENTO", "CLAVE", "T.OP.", "CARGA", "ARRIBO", "REVALIDADO", "PREVIO", "DEPÃ“SITO", "ENTRADA", "NOTIFICACION", "PRESENTACIÃ“N", "CITA DESPACHO", "FECHA PAGO", "LIBERACIÃ“N", "DÃ�AS EN PROCESO");
                    $this->_helper->layout()->disableLayout();
                    $this->_helper->viewRenderer->setNoRender(true);
                    $excel->setTitles($titulos);
                    $excel->setData($arr);
                    $excel->setFilename("TRAFICOS_POR_LIBERAR_" . date("Ymd-His") . ".xlsx");
                    $excel->layoutAnexo24Clientes();
                }
                if ($input->tipoReporte == 2) {
                    $traffics = new Trafico_Model_TraficosMapper();
                    $arr = $traffics->reporteCandados($input->idAduana, $input->fechaInicio, $input->fechaFin);
                    $titulos = array("SELLO", "CLIENTE", "TRAFICO", "PEDIMENTO", "CAJA", "FECHA", "ROJO", "VERDE");
                    $excel->setTitles($titulos);
                    $excel->setData($arr);
                    $excel->setFilename("CANDADOS_" . date("Ymd-His") . ".xlsx");
                    $excel->layoutAnexo24Clientes();
                }
            }
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public function reporteClientesAction()
    {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $view = new Zend_View();
            $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/data/");
            $mapper = new Trafico_Model_TraficoAduanasMapper();
            $table = new Application_Model_UsuariosAduanasMapper();
            if (in_array($this->_session->role, array("super", "trafico_operaciones", "trafico", "super_admon"))) {
                $view->filters = $mapper->obtenerTodas();
            } else {
                $arr = $table->traficoAduanasUsuario($this->_session->id);
                $view->filters = $mapper->obtenerTodas($arr);
            }
            echo $view->render("reporte-clientes.phtml");
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public function reporteTraficoSolicitudesAction()
    {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $view = new Zend_View();
            $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/data/");
            $mapper = new Trafico_Model_TraficoAduanasMapper();
            $table = new Application_Model_UsuariosAduanasMapper();
            if (in_array($this->_session->role, array("super", "trafico_operaciones", "trafico", "super_admon"))) {
                $view->filters = $mapper->obtenerTodas();
            } else {
                $arr = $table->traficoAduanasUsuario($this->_session->id);
                $view->filters = $mapper->obtenerTodas($arr);
            }
            echo $view->render("reporte-trafico-solicitudes.phtml");
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public function excelReporteTraficoAction()
    {
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "idAduana" => "Digits",
                "pagados" => "Digits",
            );
            $v = array(
                "idAduana" => array("NotEmpty", new Zend_Validate_Int()),
                "pagados" => array("NotEmpty", new Zend_Validate_Int()),
                "fechaInicio" => array("NotEmpty", new Zend_Validate_Regex("/^\d{4}\-\d{2}\-\d{2}$/")),
                "fechaFin" => array("NotEmpty", new Zend_Validate_Regex("/^\d{4}\-\d{2}\-\d{2}$/")),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("fechaInicio") && $input->isValid("fechaFin")) {
                $traffics = new Trafico_Model_TraficosMapper();
                $arr = $traffics->reporte($input->idAduana, $input->fechaInicio, $input->fechaFin);
                $excel = new OAQ_ExcelReportes();
                $excel->setTitles(["ADUANA", "CLIENTE", "ESQUEMA DE PAGO", "REFERENCIA", "PATENTE", "PEDIMENTO", "CLAVE", "T.OPERACION", "CARGA", "ARRIBO", "REVALIDADO", "PREVIO", "DEPÃ“SITO", "ENTRADA", "NOTIFICACION", "PRESENTACIÃ“N", "CITA DESPACHO", "FECHA PAGO", "LIBERACIÃ“N", "DÃ�AS EN PROCESO"]);
                $excel->setData($arr);
                $excel->setFilename("REPORTE_PEDIMENTOS_" . date("Ymd") . ".xlsx");
                $excel->layoutClientes();
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public function excelReporteTraficoSolicitudesAction()
    {
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "idAduana" => "Digits",
                "depositado" => "Digits",
                "complementos" => "Digits",
            );
            $v = array(
                "idAduana" => array("NotEmpty", new Zend_Validate_Int()),
                "depositado" => array("NotEmpty", new Zend_Validate_Int()),
                "complementos" => array("NotEmpty", new Zend_Validate_Int()),
                "fechaInicio" => array("NotEmpty", new Zend_Validate_Regex("/^\d{4}\-\d{2}\-\d{2}$/")),
                "fechaFin" => array("NotEmpty", new Zend_Validate_Regex("/^\d{4}\-\d{2}\-\d{2}$/")),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("fechaInicio") && $input->isValid("fechaFin")) {
                $model = new Trafico_Model_TraficoSolicitudesMapper();
                $arr = $model->reporteSolicitudes($input->idAduana, $input->fechaInicio, $input->fechaFin, $input->depositado, $input->complementos);
                $excel = new OAQ_ExcelReportes();
                $excel->setTitles(["ADUANA", "CLIENTE", "ESQUEMA DE PAGO", "REFERENCIA", "COMPLEMENTO", "PATENTE", "PEDIMENTO", "CLAVE", "T.OPERACION", "CARGA", "FECHA LIBRE ALMACENAJE", "FECHA ETA", "SUBTOTAL", "ANTICIPO"]);
                $excel->setData($arr);
                $excel->setFilename("REPORTE_SOLICITUDES_" . date("Ymd") . ".xlsx");
                $excel->layoutClientes();
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public function checklistClientesAction()
    {
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
                $view = new Zend_View();
                $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/get/");
                $mapper = new Archivo_Model_ChecklistClientesCampos();
                $rows = $mapper->getGeneric();
                $view->preguntas = $rows;
                $view->idCliente = $input->id;
                $model = new Archivo_Model_ChecklistClientes();
                $arr = $model->obtener($input->id);
                if (count($arr) > 0) {
                    $view->observaciones = $arr["observaciones"];
                    $view->completo = $arr["completo"];
                    $view->data = json_decode($arr["checklist"]);
                }
                echo $view->render("checklist-clientes.phtml");
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public function imprimirChecklistClientesAction()
    {
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "id" => "Digits",
                "download" => "StringToLower",
            );
            $v = array(
                "id" => array("NotEmpty", new Zend_Validate_Int()),
                "download" => array("NotEmpty", new Zend_Validate_InArray(array(true, false)))
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("id")) {
                $clientes = new Trafico_Model_ClientesMapper();
                $cliente = $clientes->datosCliente($input->id);
                $mapper = new Archivo_Model_ChecklistClientesCampos();
                $preguntas = $mapper->getGeneric();
                $model = new Archivo_Model_ChecklistClientes();
                $arr = $model->obtener($input->id);
                $data = array(
                    "empresa" => "ORGANIZACIÓN ADUANAL DE QUERÉTARO S.C.",
                    "nombreCliente" => $cliente["nombre"],
                    "documento" => "SGC 19",
                    "preguntas" => $preguntas,
                    "checklist" => json_decode($arr["checklist"]),
                    "observaciones" => $arr["observaciones"],
                );
                $print = new OAQ_PrintChecklistCliente($data, "P", "pt", "LETTER");
                if (strtoupper(substr(PHP_OS, 0, 3)) === "WIN") {
                    $diretory = "d:/Tmp/php_archivos";
                } else {
                    $diretory = "/tmp/archivos";
                }
                if (!file_exists($diretory)) {
                    mkdir($diretory, 0777);
                }
                $print->set_dir($diretory);
                $print->set_filename("CHECKLISTCLIENTE_" . $cliente["rfc"] . ".pdf");
                $print->printChecklist();
                if (filter_var($input->download, FILTER_VALIDATE_BOOLEAN) == false) {
                    $print->Output($print->get_filename(), "I");
                } else {
                    $print->Output($print->get_filename(), "F");
                }
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public function tarifaClientesAction()
    {
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
                $view = new Zend_View();
                $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/data/");
                $view->setHelperPath(realpath(dirname(__FILE__)) . "/../views/helpers/");
                echo $view->render("tarifa-clientes.phtml");
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public function agregarAduanaClienteAction()
    {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "id" => "Digits",
            );
            $v = array(
                "id" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $i = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($i->isValid("id")) {
                $model = new Trafico_Model_TraficoAduanasMapper();
                $mapper = new Trafico_Model_TraficoCliAduanasMapper();
                $arr = $mapper->clienteAduanas($i->id);
                $view = new Zend_View();
                $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/data/");
                $view->setHelperPath(realpath(dirname(__FILE__)) . "/../views/helpers/");
                $view->idCliente = $i->id;
                if (isset($arr) && !empty($arr)) {
                    $view->activas = $arr;
                }
                $view->aduanas = $model->obtenerActivas();
                echo $view->render("agregar-aduana-cliente.phtml");
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function obtenerClientesAction()
    {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $f = array(
                "idAduana" => array("StringTrim", "StripTags", "Digits"),
            );
            $v = array(
                "idAduana" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("idAduana")) {
                $mapper = new Trafico_Model_TraficoCliAduanasMapper();
                $rows = $mapper->clientesAduana($input->idAduana);
                foreach ($rows as $item) {
                    $data[] = array("id" => $item["idCliente"], "nombre" => $item["nombre"]);
                }
                $this->_helper->json(array("success" => true, "rows" => $data));
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function obtenerClavesPedimentoAction()
    {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $tbl = new Trafico_Model_CvePedimentos();
            $arr = $tbl->obtener();
            if (isset($arr) && !empty($arr)) {
                $data = array();
                $data[""] = "---";
                foreach ($arr as $item) {
                    $data[$item["clave"]] = $item["clave"];
                }
            }
            $this->_helper->json(array("success" => true, "rows" => $data));
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function obtenerTarifaConceptoAction()
    {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "id" => "Digits",
            );
            $v = array(
                "id" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $i = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($i->isValid("id")) {
                $mapper = new Trafico_Model_TarifaConceptos();
                $arr = $mapper->obtener($i->id);
                $this->_helper->json(array("success" => true, "modoCalculo" => $arr["modoCalculo"]));
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function obtenerTarifaAction()
    {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "id" => "Digits",
            );
            $v = array(
                "id" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $i = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($i->isValid("id")) {
                $mapper = new Trafico_Model_Tarifas();
                $arr = $mapper->obtenerTarifa($i->id);
                $this->_helper->json(array("success" => true, "tarifa" => $arr["tarifa"]));
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function imprimirTarifaAction()
    {
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
                $customers = new Trafico_Model_ClientesMapper();
                $mapper = new Trafico_Model_Tarifas();
                $arr = $mapper->obtenerTarifa($i->id);
                if (isset($_GET["debug"])) {
                    if (filter_var($_GET["debug"], FILTER_VALIDATE_BOOLEAN) == true) {
                        var_dump($arr);
                        $json = json_decode($arr["tarifa"], true);
                        var_dump($json);
                        return;
                    }
                }
                $json = json_decode($arr["tarifa"], true);
                $cliente = $customers->datosCliente($json["idCliente"]);
                $json["empresa"] = $this->_appconfig->getParam("empresa");
                $json["razonSocial"] = $cliente["nombre"];
                $json["rfc"] = $cliente["rfc"];
                $print = new OAQ_PrintTarifa($json, "P", "pt", "LETTER");
                if (strtoupper(substr(PHP_OS, 0, 3)) === "WIN") {
                    $diretory = "D:/Tmp/php_archivos";
                } else {
                    $diretory = "/tmp/archivos";
                }
                if (!file_exists($diretory)) {
                    mkdir($diretory, 0777);
                }
                $print->set_dir($diretory);
                $print->set_filename("TARIFA_" . date("Y-m-d_His") . ".pdf");
                $print->printTarifa();
                if (filter_var($i->download, FILTER_VALIDATE_BOOLEAN) == false) {
                    $print->Output($print->get_filename(), "I");
                } else {
                    $print->Output($print->get_filename(), "F");
                }
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public function traficoTmpSeleccionarAction()
    {
        try {
            $f = array(
                "id" => array("StringTrim", "StripTags", "Digits"),
            );
            $v = array(
                "id" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("id")) {
                $mppr = new Trafico_Model_TraficosTmp();
                $arr = $mppr->seleccionar($input->id);
                if (!empty($arr)) {
                    $this->_helper->json(array("success" => true, "row" => $arr));
                }
                $this->_helper->json(array("success" => false));
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function traficoTmpTodosAction()
    {
        try {
            $view = new Zend_View();
            $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/get/");
            $mppr = new Trafico_Model_TraficosTmp();
            $arr = $mppr->obtener($this->_session->username);
            if (!empty($arr)) {
                $view->data = $arr;
            }
            $this->_helper->json(array("success" => true, "html" => $view->render("traficos-tmp-todos.phtml")));
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function mensajeroEnTraficoAction()
    {
        $this->_helper->viewRenderer->setNoRender(false);
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "idTrafico" => "Digits",
            );
            $v = array(
                "idTrafico" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("idTrafico")) {
                $view = new Zend_View();
                $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/get/");
                $model = new Trafico_Model_TraficosMapper();
                $traffic = $model->obtenerPorId($input->idTrafico);
                $mapper = new Application_Model_Mensajes();
                $mensajes = new Application_Model_MensajesFijos();
                $arr = $mapper->obtenerMensajes($input->idTrafico);
                $view->arr = $arr;
                $view->mensajes = $mensajes->obtenerMensajes();
                $view->idTrafico = $input->idTrafico;
                $view->idUsuarioDe = $this->_session->id;
                $view->idUsuarioPara = $traffic["idUsuario"];
                $this->_helper->json(array("success" => true, "html" => $view->render("mensajero-en-trafico.phtml")));
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => true, "message" => $ex->getMessage()));
        }
    }

    public function alertasDeSolicitudesAction()
    {
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
            );
            $v = array(
                "ids" => array("NotEmpty"),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("ids")) {
                $view = new Zend_View();
                $view->setHelperPath(realpath(dirname(__FILE__)) . "/../views/helpers/");
                $mppr = new Trafico_Model_TraficosMapper();
                $mpprs = new Trafico_Model_TraficoSolicitudesMapper();
                $arr = $mppr->seleccionar($input->ids);
                $sts = [];
                foreach ($arr as $item) {
                    $e = $mpprs->obtenerEstatus($item["pedimento"], $item["referencia"]);
                    if (!empty($e)) {
                        $sts[] = array(
                            "idTrafico" => $item["id"],
                            "estatus" => $view->estatus($e["autorizada"], $e["tramite"], $e["deposito"], $e["autorizadaHsbc"], $e["autorizadaBanamex"]),
                        );
                    }
                }
                if (!empty($sts)) {
                    $this->_helper->json(array("success" => true, "ids" => $sts));
                } else {
                    $this->_helper->json(array("success" => false));
                }
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => true, "message" => $ex->getMessage()));
        }
    }

    public function alertasDeMensajesAction()
    {
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
            );
            $v = array(
                "ids" => array("NotEmpty"),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("ids")) {
                $mapper = new Application_Model_Mensajes();
                $arr = $mapper->cantidadMensajes($this->_session->id, $input->ids);
                if (!empty($arr)) {
                    $this->_helper->json(array("success" => true, "ids" => $arr));
                } else {
                    $this->_helper->json(array("success" => false));
                }
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => true, "message" => $ex->getMessage()));
        }
    }

    public function mensajeroAction()
    {
        $this->_helper->viewRenderer->setNoRender(false);
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "idTrafico" => "Digits",
            );
            $v = array(
                "idTrafico" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("idTrafico")) {
                $view = new Zend_View();
                $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/get/");
                $model = new Trafico_Model_TraficosMapper();
                $traffic = $model->obtenerPorId($input->idTrafico);
                $mapper = new Application_Model_Mensajes();
                $mensajes = new Application_Model_MensajesFijos();
                $arr = $mapper->obtenerMensajes($input->idTrafico);
                $view->arr = $arr;
                $msgf = $mensajes->obtenerMensajes($traffic["idAduana"]);
                if (empty($msgf)) {
                    $msgf = $mensajes->obtenerMensajes(0);
                }
                $view->mensajes = $msgf;
                $view->idTrafico = $input->idTrafico;
                $view->idUsuarioDe = $this->_session->id;
                $view->idUsuarioPara = $traffic["idUsuario"];
                $this->_helper->json(array("success" => true, "html" => $view->render("mensajero.phtml")));
            }
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public function mensajesAction()
    {
        $this->_helper->viewRenderer->setNoRender(false);
        try {
            $models = new Trafico_Model_TraficoUsuAduanasMapper();
            if (in_array($this->_session->role, array("trafico", "super"))) {
                $customs = $models->aduanasDeUsuario();
            } else {
                $customs = $models->aduanasDeUsuario($this->_session->id);
            }
            if (isset($customs) && !empty($customs)) {
                $aduanas = array();
                foreach ($customs as $item) {
                    $aduanas[] = $item["id"];
                }
            }
            $view = new Zend_View();
            $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/get/");
            $mapper = new Application_Model_Mensajes();
            $arr = $mapper->obtenerMensajesSinLeer($aduanas);
            $view->arr = $arr;
            $this->_helper->json(array("success" => true, "html" => $view->render("mensajes.phtml")));
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public function subirTarifaFirmadaAction()
    {
        $this->_helper->viewRenderer->setNoRender(false);
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "idCliente" => "Digits",
                "idTarifa" => "Digits",
            );
            $v = array(
                "idCliente" => array("NotEmpty", new Zend_Validate_Int()),
                "idTarifa" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("idCliente") && $input->isValid("idTarifa")) {
                $view = new Zend_View();
                $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/get/");
                $view->idCliente = $input->idCliente;
                $view->idTarifa = $input->idTarifa;
                $this->_helper->json(array("success" => true, "html" => $view->render("subir-tarifa-firmada.phtml")));
            }
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public function facturasPedimentoAction()
    {
        $this->_helper->viewRenderer->setNoRender(false);
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "idTrafico" => "Digits",
            );
            $v = array(
                "idTrafico" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("idTrafico")) {
                $view = new Zend_View();
                $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/get/");
                $view->idTrafico = $input->idTrafico;
                $misc = new OAQ_Misc();
                $mapper = new Trafico_Model_TraficosMapper();
                $arr = $mapper->obtenerPorId($input->idTrafico);
                if (isset($arr["patente"]) && isset($arr["aduana"])) {
                    $db = $misc->sitawinTrafico($arr["patente"], $arr["aduana"]);
                    if (isset($db)) {
                        $facturas = $db->obtenerFacturas($arr["referencia"]);
                        if (!isset($facturas) || empty($facturas)) {
                            $facturas = $db->obtenerFacturasRemesas($arr["pedimento"]);
                        }
                        if (isset($facturas) && !empty($facturas)) {
                            $view->arr = $facturas;
                        }
                    }
                }
                $this->_helper->json(array("success" => true, "html" => $view->render("facturas-pedimento.phtml")));
            }
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public function semaforoAction()
    {
        $this->_helper->viewRenderer->setNoRender(false);
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "idTrafico" => "Digits",
            );
            $v = array(
                "idTrafico" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("idTrafico")) {
                $view = new Zend_View();
                $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/get/");
                $view->idTrafico = $input->idTrafico;
                $mapper = new Trafico_Model_TraficosMapper();
                $arr = $mapper->obtenerPorId($input->idTrafico);
                $view->semaforo = $arr["semaforo"];
                $view->observaciones = $arr["observacionSemaforo"];
                $this->_helper->json(array("success" => true, "html" => $view->render("semaforo.phtml")));
            }
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public function erroresAction()
    {
        $this->_helper->viewRenderer->setNoRender(false);
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "idTrafico" => "Digits",
            );
            $v = array(
                "idTrafico" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("idTrafico")) {
                $view = new Zend_View();
                $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/get/");
                $view->idTrafico = $input->idTrafico;
                $mapper = new Trafico_Model_TraficosMapper();
                $arr = $mapper->obtenerPorId($input->idTrafico);

                $er = new Operaciones_Model_IncidenciaTipoError();
                $view->tipoError = $er->obtener();

                $this->_helper->json(array("success" => true, "html" => $view->render("errores.phtml")));
            }
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public function importarPlantillaAction()
    {
        $this->_helper->viewRenderer->setNoRender(false);
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "idTrafico" => "Digits",
            );
            $v = array(
                "idTrafico" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("idTrafico")) {
                $view = new Zend_View();
                $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/get/");
                $view->idTrafico = $input->idTrafico;
                $this->_helper->json(array("success" => true, "html" => $view->render("importar-plantilla.phtml")));
            }
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public function agregarContactoAction()
    {
        $this->_helper->viewRenderer->setNoRender(false);
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "idCliente" => "Digits",
            );
            $v = array(
                "idCliente" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("idCliente")) {
                $view = new Zend_View();
                $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/get/");
                $form = new Trafico_Form_NuevoContactoCli(array("idCliente" => $input->idCliente));
                $view->form = $form;
                $this->_helper->json(array("success" => true, "html" => $view->render("agregar-contacto.phtml")));
            }
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public function vucemPreviewAction()
    {
        $this->_helper->viewRenderer->setNoRender(false);
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "idFactura" => "Digits",
            );
            $v = array(
                "idFactura" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("idFactura")) {
                $mppr = new Trafico_Model_TraficoFacturasMapper();
                $arr = $mppr->detalleFactura($input->idFactura);
                if (isset($arr["archivoCove"]) && $arr["archivoCove"] !== null) {
                    if (file_exists($arr["archivoCove"])) {
                        $this->view->contenido = file_get_contents($arr["archivoCove"]);
                    }
                } else {
                    $this->view->contenido = "No hay archivo XML";
                }
            }
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public function clientesAction()
    {
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
            );
            $v = array(
                "name" => "NotEmpty",
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("name")) {
                $mapper = new Trafico_Model_ClientesMapper();
                $arr = $mapper->buscarCliente($input->name);
                $this->_helper->json($arr);
            }
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public function rfcDeClienteAction()
    {
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
            );
            $v = array(
                "name" => "NotEmpty",
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("name")) {
                $mapper = new Trafico_Model_ClientesMapper();
                $arr = $mapper->rfcDeCliente(html_entity_decode($input->name));
                $this->_helper->json($arr);
            }
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public function imprimirFormatoSalidaAction()
    {
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "id" => "Digits"
            );
            $v = array(
                "id" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("id")) {
                $mapper = new Trafico_Model_TraficosMapper();
                $arr = $mapper->obtenerPorId($input->id);
                $data = array(
                    "prefijoDocumento" => "FMTSAL_",
                    "nombreDocumento" => "Formato de salida de aeropuerto",
                    "versionDocumento" => "SGC 77",
                    "referencia" => $arr["referencia"],
                    "pedimento" => $arr["pedimento"],
                    "nombreCliente" => $arr["nombreCliente"],
                    "empresa" => "ORGANIZACIÓN ADUANAL DE QUERÉTARO S.C.",
                );
                $trafico = new OAQ_Trafico(array("idTrafico" => $input->id, "idUsuario" => $this->_session->id));

                try {
                    $row = $trafico->obtenerDatosDesdeSistema();
                } catch (Exception $ex) {
                    $arr = $trafico->obtenerDatos();
                    $data["bultos"] = $arr['bultos'];
                    $data["pesoBruto"] = $arr['pesoBruto'];
                    $data["guias"] = $arr['blGuia'];
                }

                if (!empty($row)) {
                    if (isset($row["bultos"])) {
                        $data["bultos"] = $row["bultos"];
                    }
                    if (isset($row["pesoBruto"])) {
                        $data["pesoBruto"] = $row["pesoBruto"];
                    }
                    if (is_array($row["guias"])) {
                        if (isset($row["guias"]) && !empty($row["guias"])) {
                            $data["guias"] = "";
                            foreach ($row["guias"] as $value) {
                                $data["guias"] .= preg_replace("/\s+/", "", $value["guia"]) . ", ";
                            }
                        }
                    } else {
                        if (isset($row["guias"])) {
                            $data["guias"] = $row["guias"];
                        }
                    }
                }
                $print = new OAQ_Imprimir_FormatoSalida($data, "P", "pt", "LETTER");
                $print->formatoSalida();
                $print->Output($print->get_filename(), "I");
            }
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public function imprimirOrdenDeRemisionAction()
    {
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "idTrafico" => "Digits",
                "idRemision" => "Digits"
            );
            $v = array(
                "idTrafico" => array("NotEmpty", new Zend_Validate_Int()),
                "idRemision" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("idTrafico") && $input->isValid("idRemision")) {
                $mdl = new Trafico_Model_OrdenRemision();
                $contactos = new Trafico_Model_OrdenRemisionContactos();
                $rem = $mdl->obtener($input->idTrafico);
                $mapper = new Trafico_Model_TraficosMapper();
                $arr = $mapper->obtenerPorId($input->idTrafico);
                $csts = new Trafico_Model_TraficoAduanasMapper();
                $adu = $csts->infoAduana($arr["patente"], $arr["aduana"]);
                $data = array(
                    "prefijoDocumento" => "ORDENDEREMISION_",
                    "nombreDocumento" => "Orden de Remisión",
                    "versionDocumento" => "",
                    "direccion" => "HÃ©roe de Nacataz 3745, Altos, Col. Centro, Nuevo Laredo, Tamaulipas. Tel.: +52 (867) 712-5149",
                    "referencia" => $arr["referencia"],
                    "pedimento" => $arr["pedimento"],
                    "patente" => $arr["patente"],
                    "nombreCliente" => $arr["nombreCliente"],
                    "empresa" => "ORGANIZACIÓN ADUANAL DE QUERÉTARO S.C.",
                    "instrucciones" => $rem["instrucciones"],
                    "transfer" => $rem["transfer"],
                    "lineaTransportista" => $rem["lineaTransportista"],
                    "elaboro" => $rem["elaboro"],
                    "caja" => $rem["caja"],
                    "aduanaDespacho" => $adu["nombre"],
                    "pedimentoSimplificado" => $rem["pedimentoSimplificado"],
                    "relacionDocumentos" => $rem["relacionDocumentos"],
                    "manifiesto" => $rem["manifiesto"],
                    "inBond" => $rem["inBond"],
                    "bl" => $rem["bl"],
                    "contactos" => $contactos->obtener(),
                );
                $misc = new OAQ_Misc();
                if ($arr["patente"] == 3589 && ($arr["aduana"] == 240 || $arr["aduana"] == 640)) {
                    /* $db = $misc->sitawinTrafico($arr["patente"], $arr["aduana"]);
                      if (isset($db)) {
                      $b = $db->infoPedimentoBasicaReferencia($arr["referencia"]);
                      }
                      if (isset($b) && !empty($b)) {
                      if (isset($b["bultos"])) { $data["bultos"] = $b["bultos"]; }
                      if (isset($b["pesoBruto"])) { $data["pesoBruto"] = $b["pesoBruto"]; }
                      if (isset($b["guias"]) && !empty($b["guias"])) {
                      $data["guias"] = "";
                      foreach ($b["guias"] as $value) {
                      $data["guias"] .= preg_replace("/\s+/", "", $value["guia"]) . ", ";
                      }
                      }
                      } */
                }
                $print = new OAQ_Imprimir_OrdenDeRemision($data, "P", "pt", "LETTER");
                $print->ordenDeRemision();
                $print->set_filename("ORDENDEREMISION_{$arr["referencia"]}.pdf");
                $print->Output($print->get_filename(), "I");
            }
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public function actualizarSelloAction()
    {
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "id" => "Digits"
            );
            $v = array(
                "id" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("id")) {
                $mapper = new Trafico_Model_SellosClientes();
                $arr = $mapper->obtenerSello($input->id);
                $sat = new OAQ_SATValidar();
                $fechas = $sat->fechasDeCertificado($arr["certificado"]);
                if (!empty($fechas)) {
                    $mapper->actualizarFechasVencimiento($input->id, $fechas["valido_desde"], $fechas["valido_hasta"]);
                }
                $this->_helper->json(array("success" => true, "html" => date("d/m/Y", strtotime($fechas["valido_desde"])) . " - " . date("d/m/Y", strtotime($fechas["valido_hasta"]))));
            }
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public function misMensajesAction()
    {
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "idTrafico" => "Digits"
            );
            $v = array(
                "idTrafico" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("idTrafico")) {
                $mapper = new Application_Model_Mensajes();
                $count = $mapper->contarMisMensajes($input->idTrafico, $this->_session->id);
                $this->_helper->json(array("success" => true, "cantidad" => $count));
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function actualizarDesdeServicioAction()
    {
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "id" => array("Digits"),
                "sistema" => array("StringToLower"),
            );
            $v = array(
                "id" => array("NotEmpty", new Zend_Validate_Int()),
                "sistema" => array("NotEmpty"),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("id") && $input->isValid("sistema")) {
                $trafico = new OAQ_Trafico(array("idTrafico" => $input->id, "idUsuario" => $this->_session->id));
                $trafico->actualizarDesdeServicio($input->sistema);
                $this->_helper->json(array("success" => true));
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function actualizarDesdeSistemaAction()
    {
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "id" => array("Digits"),
            );
            $v = array(
                "id" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("id")) {
                $trafico = new OAQ_Trafico(array("idTrafico" => $input->id, "idUsuario" => $this->_session->id));
                $trafico->actualizarDesdeSistema();
                $this->_helper->json(array("success" => true, "id" => $input->id));
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function ordenRemisionAction()
    {
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "idTrafico" => array("Digits"),
            );
            $v = array(
                "idTrafico" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("idTrafico")) {
                $mapper = new Trafico_Model_OrdenRemision();
                $view = new Zend_View();
                $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/get/");
                $view->idTrafico = $input->idTrafico;
                if ($mapper->verificar($input->idTrafico)) {
                    $arr = $mapper->obtener($input->idTrafico);
                    $view->caja = $arr["caja"];
                    $view->transfer = $arr["transfer"];
                    $view->instrucciones = $arr["instrucciones"];
                    $view->lineaTransportista = $arr["lineaTransportista"];
                    $view->elaboro = $arr["elaboro"];
                    $view->pedimentoSimplificado = $arr["pedimentoSimplificado"];
                    $view->relacionDocumentos = $arr["relacionDocumentos"];
                    $view->manifiesto = $arr["manifiesto"];
                    $view->inBond = $arr["inBond"];
                    $view->bl = $arr["bl"];
                } else {
                    $view->pedimentoSimplificado = 0;
                    $view->relacionDocumentos = 0;
                    $view->manifiesto = 0;
                    $view->inBond = 0;
                    $view->bl = 0;
                    $view->elaboro = mb_strtoupper($this->_session->nombre);
                }
                $this->_helper->json(array("success" => true, "html" => $view->render("orden-remision.phtml")));
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function nuevaPlantaAction()
    {
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "idCliente" => array("Digits"),
                "idPlanta" => array("Digits"),
            );
            $v = array(
                "idCliente" => array("NotEmpty", new Zend_Validate_Int()),
                "idPlanta" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("idCliente")) {
                $view = new Zend_View();
                $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/get/");
                $view->idCliente = $input->idCliente;
                if ($input->isValid("idPlanta")) {
                    $mppr = new Trafico_Model_ClientesPlantas();
                    $arr = $mppr->obtener($input->idCliente, $input->idPlanta);
                    if (isset($arr) && !empty($arr)) {
                        $view->idPlanta = $input->idPlanta;
                        $view->clave = $arr["clave"];
                        $view->ubicacion = $arr["ubicacion"];
                        $view->descripcion = $arr["descripcion"];
                    }
                }
                $this->_helper->json(array("success" => true, "html" => $view->render("nueva-planta.phtml")));
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function obtenerFechaAction()
    {
        try {
            $f = array(
                "idTrafico" => array("StringTrim", "StripTags", "Digits"),
                "tipoFecha" => array("StringTrim", "StripTags", "Digits"),
            );
            $v = array(
                "idTrafico" => array("NotEmpty", new Zend_Validate_Int()),
                "tipoFecha" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("tipoFecha")) {
                $view = new Zend_View();
                $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/get/");
                $view->idTrafico = $input->idTrafico;
                $view->tipoFecha = $input->tipoFecha;
                $this->_helper->json(array("success" => true, "html" => $view->render("obtener-fecha.phtml")));
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function verEdocumentAction()
    {
        error_reporting(E_ALL & E_NOTICE);
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(false);
        $this->view->headLink()
            ->appendStylesheet("/less/traffic-module.css?" . time());
        try {
            $this->view->headScript()
                ->appendFile("/js/common/jquery-1.9.1.min.js");
            $f = array(
                "idFactura" => array("StringTrim", "StripTags", "Digits"),
                "idTrafico" => array("StringTrim", "StripTags", "Digits"),
                "type" => array("StringTrim", "StripTags", "StringToLower"),
                "cove" => array("StringTrim", "StripTags", "StringToUpper"),
            );
            $v = array(
                "idFactura" => array("NotEmpty", new Zend_Validate_Int()),
                "idTrafico" => array("NotEmpty", new Zend_Validate_Int()),
                "type" => array("NotEmpty"),
                "cove" => array("NotEmpty"),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("idFactura") && $input->isValid("idTrafico") && $input->isValid("cove") && $input->isValid("type")) {
                $view = new Zend_View();
                $view->setScriptPath(realpath(dirname(__FILE__)) . '/../views/scripts/get/');
                $mppr = new Trafico_Model_VucemMapper();
                $row = $mppr->obtenerPorFactura($input->idFactura);
                if (isset($row)) {
                    $vucem = new OAQ_TraficoVucem();
                    $trafico = new OAQ_Trafico(array("idTrafico" => $input->idTrafico, "usuario" => $this->_session->username, "idUsuario" => $this->_session->id));
                    $vucem->setPatente($trafico->getPatente());
                    $vucem->setAduana($trafico->getAduana());
                    $vucem->setPedimento($trafico->getPedimento());
                    $vucem->setReferencia($trafico->getReferencia());
                    $xml = $vucem->enviarCove($row["id"], $input->idTrafico, $input->idFactura, false);
                    if ($xml) {
                        $lib = new OAQ_VucemEnh();
                        $array = $lib->vucemXmlToArray($xml);
                        unset($array['Header']);
                        if (isset($array['Body']['solicitarRecibirCoveServicio']['comprobantes'])) {
                            $this->view->idFactura = $input->idFactura;
                            $this->view->cove = $input->cove;
                            $view->data = $array['Body']['solicitarRecibirCoveServicio']['comprobantes'];
                            $view->data['cove'] = '';
                            $view->data['emisor']['tipoIdentificador'] = $this->view->identificadorDesc($view->data['emisor']['tipoIdentificador']);
                            $this->view->content = $view->render('edocument-cove.phtml');
                        }
                    }
                }
            }
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    /**
     * http://localhost:8090/trafico/get/imprimir-edocument?id=84449&idTrafico=61090
     * http://localhost:8090/trafico/facturas/vucem-preview?id=84449&idTrafico=61090
     * 
     */
    public function imprimirEdocumentAction()
    {
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "id" => "Digits",
                "idTrafico" => "Digits",
            );
            $v = array(
                "id" => array("NotEmpty", new Zend_Validate_Int()),
                "idTrafico" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("id") && $input->isValid("idTrafico")) {

                $mppr = new Trafico_Model_VucemMapper();
                $arr = $mppr->obtenerVucem($input->id);
                $vucem = new OAQ_TraficoVucem();
                $misc = new OAQ_Misc();

                $trafico = new OAQ_Trafico(array("idTrafico" => $input->idTrafico, "usuario" => $this->_session->username, "idUsuario" => $this->_session->id));
                if (isset($arr["idArchivo"])) {

                    $data = $vucem->_armarEdocument($input->id, $arr["idTrafico"], $arr["idArchivo"], $arr["tipoDocumento"]);

                    $data["edoc"] = $arr["edocument"];
                    $data["patente"] = $trafico->getPatente();
                    $data["aduana"] = $trafico->getAduana();
                    $data["referencia"] = $trafico->getReferencia();
                    $data["pedimento"] = $trafico->getPedimento();
                    $data["numTramite"] = $arr["numeroOperacion"];
                    $data["actualizado"] = date("Y-m-d H:i:s");
                    $data['titulo'] = "ED_" . $trafico->getAduana() . '-' . $trafico->getPatente() . '-' . $trafico->getPedimento() . '_' . $arr["tipoDocumento"] . '_' . $arr["nombreArchivo"];

                    if (APPLICATION_ENV == 'production') {
                        $directory = $this->appConfig->getParam("expdest");
                    } else {
                        $directory = "D:\\xampp\\tmp\\expedientes";
                    }

                    $sello = $vucem->_obtenerSello($input->id);

                    $directory = $misc->nuevoDirectorio($directory, $trafico->getPatente(), $trafico->getAduana(), $trafico->getReferencia());

                    $xml_filename = "ED" . $data["edoc"] . "_" . $trafico->getAduana() . '-' . $trafico->getPatente() . '-' . $trafico->getPedimento() . '_' . $arr["tipoDocumento"] . "_" . preg_replace('/\..+$/', '.xml', $arr["nombreArchivo"]);
                    $pdf_filename = "ED" . $data["edoc"] . "_" . $trafico->getAduana() . '-' . $trafico->getPatente() . '-' . $trafico->getPedimento() . '_' . $arr["tipoDocumento"] . "_" . preg_replace('/\..+$/', '', $arr["nombreArchivo"]);

                    $ed["archivo"] = array(
                        "idTipoDocumento" => $arr["tipoDocumento"],
                        "nombreDocumento" => $arr["nombreArchivo"],
                        "archivo" => base64_encode(file_get_contents($directory . DIRECTORY_SEPARATOR . $arr["nombreArchivo"])),
                        "hash" => sha1_file($directory . DIRECTORY_SEPARATOR . $arr["nombreArchivo"]),
                        "correoElectronico" => "soporte@oaq.com.mx",
                        "rfcConsulta" => isset($rfcConsulta) ? $rfcConsulta : null
                    );
                    $ed["usuario"] = array(
                        "username" => $sello["rfc"],
                        "password" => $sello["ws_pswd"],
                        "certificado" => $sello["cer"],
                        "key" => openssl_get_privatekey(base64_decode($sello["spem"]), $sello["spem_pswd"]),
                        "new" => null,
                    );

                    $print = new OAQ_Imprimir_ImprimirEdocument2019($data, "P", "pt", "LETTER");
                    $print->Create();
                    $print->Output($directory . DIRECTORY_SEPARATOR . $pdf_filename, "I");
                }
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function imprimirCoveAction()
    {
        try {
            $f = array(
                "idFactura" => array("StringTrim", "StripTags", "Digits"),
                "cove" => array("StringTrim", "StripTags", "StringToUpper"),
            );
            $v = array(
                "idFactura" => array("NotEmpty", new Zend_Validate_Int()),
                "cove" => array("NotEmpty"),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("idFactura") && $input->isValid("cove")) {
                $mppr = new Trafico_Model_VucemMapper();
                $arr = $mppr->obtenerPorFactura($input->idFactura, $input->cove);
                if (isset($arr)) {
                    $vucem = new OAQ_TraficoVucem();
                    $trafico = new OAQ_Trafico(array("idTrafico" => $arr["idTrafico"], "usuario" => $this->_session->username, "idUsuario" => $this->_session->id));
                    $vucem->setPatente($trafico->getPatente());
                    $vucem->setAduana($trafico->getAduana());
                    $vucem->setPedimento($trafico->getPedimento());
                    $vucem->setReferencia($trafico->getReferencia());
                    $xml = $vucem->enviarCove($arr["id"], $arr["idTrafico"], $input->idFactura, false);

                    $misc = new OAQ_Misc();

                    $data = array(
                        'xml' => $xml,
                        'patente' => $trafico->getPatente(),
                        'aduana' => $trafico->getAduana(),
                        'pedimento' => $trafico->getPedimento(),
                        'referencia' => $trafico->getReferencia(),
                        'cove' => $input->cove,
                        'rfcConsulta' => null,
                        'actualizado' => date('Y-m-d H:i:s'),
                        'filename' => 'COVE_ACUSE_' . $trafico->getPatente() . '-' . $trafico->getAduana() . '-' . $trafico->getPedimento() . '_' . $trafico->getReferencia() . '_' . $input->cove . '_' . $misc->formatFilename($arr["numFactura"]),
                        'creado' => date('Y-m-d H:i:s'),
                    );

                    $print = new OAQ_Imprimir_CoveAcuse2019($data, "P", "pt", "LETTER");
                    $print->set_filename($data["filename"] . ".pdf");
                    $print->Create();
                    $print->Output($data["filename"] . ".pdf", "I");
                }
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function imprimirCoveDetalleAction()
    {
        try {
            $f = array(
                "idFactura" => array("StringTrim", "StripTags", "Digits"),
                "cove" => array("StringTrim", "StripTags", "StringToUpper"),
            );
            $v = array(
                "idFactura" => array("NotEmpty", new Zend_Validate_Int()),
                "cove" => array("NotEmpty"),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("idFactura") && $input->isValid("cove")) {
                $mppr = new Trafico_Model_VucemMapper();
                $arr = $mppr->obtenerPorFactura($input->idFactura, $input->cove);
                if (isset($arr)) {
                    $vucem = new OAQ_TraficoVucem();
                    $trafico = new OAQ_Trafico(array("idTrafico" => $arr["idTrafico"], "usuario" => $this->_session->username, "idUsuario" => $this->_session->id));
                    $vucem->setPatente($trafico->getPatente());
                    $vucem->setAduana($trafico->getAduana());
                    $vucem->setPedimento($trafico->getPedimento());
                    $vucem->setReferencia($trafico->getReferencia());
                    $xml = $vucem->enviarCove($arr["id"], $arr["idTrafico"], $input->idFactura, false);

                    $misc = new OAQ_Misc();

                    $data = array(
                        'xml' => $xml,
                        'patente' => $trafico->getPatente(),
                        'aduana' => $trafico->getAduana(),
                        'pedimento' => $trafico->getPedimento(),
                        'referencia' => $trafico->getReferencia(),
                        'cove' => $input->cove,
                        'rfcConsulta' => null,
                        'actualizado' => date('Y-m-d H:i:s'),
                        'filename' => 'COVE_DETALLE_' . $trafico->getAduana() . '-' . $trafico->getPatente() . '-' . $trafico->getPedimento() . '_' . $trafico->getReferencia() . '_' . $input->cove . '_' . $misc->formatFilename($arr["numFactura"]),
                        'creado' => date('Y-m-d H:i:s'),
                    );
                    $inv = new Trafico_Model_TraficoFacturasMapper();
                    $arri = $inv->detalleFactura($input->idFactura);

                    $print = new OAQ_Imprimir_CoveDetalle2019($data, "P", "pt", "LETTER");
                    $print->set_filename($data["filename"] . ".pdf");
                    $print->Create();
                    $print->Output($data["filename"] . ".pdf", "I");
                    $pdffilename = dirname($arri["archivoCove"]) . DIRECTORY_SEPARATOR . preg_replace('/\..+$/', '.' . 'pdf', basename($arri["archivoCove"]));
                    if (!file_exists($pdffilename)) {
                        if (file_exists(dirname($arri["archivoCove"]))) {
                            $print->Output($pdffilename, "F");
                            $trafico->agregarArchivoExpediente(22, $pdffilename, $arri["cove"]);
                        }
                    }
                }
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function vucemBitacoraAction()
    {
        try {
            $f = array(
                "idTrafico" => array("StringTrim", "StripTags", "Digits"),
            );
            $v = array(
                "idTrafico" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("idTrafico")) {
                $view = new Zend_View();
                $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/get/");
                $view->setHelperPath(realpath(dirname(__FILE__)) . "/../views/helpers/");

                $mppr = new Trafico_Model_VucemMapper();

                $trafico = new OAQ_Trafico(array("idTrafico" => $input->idTrafico));
                $keys = new Trafico_Model_CliSello();
                $id_sello_cli = $keys->obtenerDefault($trafico->getIdCliente());
                if ($id_sello_cli) {
                    $mppr->establecerSelloCliente($input->idTrafico, $id_sello_cli);
                }
                $arr = $mppr->obtener($input->idTrafico);

                if (isset($arr) && !empty($arr)) {
                    $view->results = $arr;
                }
                $view->idTrafico = $input->idTrafico;
                $this->_helper->json(array("success" => true, "html" => $view->render("vucem-bitacora.phtml")));
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function vucemFirmasAction()
    {
        try {
            $f = array(
                "idTrafico" => array("StringTrim", "StripTags", "Digits"),
            );
            $v = array(
                "idTrafico" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("idTrafico")) {
                $trafico = new OAQ_Trafico(array("idTrafico" => $input->idTrafico, "idUsuario" => $this->_session->id));
                $arr = array();
                if ($trafico->getPatente() !== null) {
                    $mppr = new Trafico_Model_SellosAgentes();
                    $rows = $mppr->obtener($trafico->getPatente());
                    if (!empty($rows)) {
                        $arr["agente"] = $rows;
                    }
                }
                $sell = false;
                if ($trafico->getIdCliente() !== null) {
                    $mppr = new Trafico_Model_SellosClientes();
                    $rows = $mppr->obtener($trafico->getIdCliente());
                    if (!empty($rows)) {
                        $arr["cliente"] = $rows;
                        $sell = false;
                    }
                }
                $vu = new Trafico_Model_VucemMapper();
                if (($config = $vu->obtenerConfig($input->idTrafico))) {
                    $arr['config'] = array(
                        'idSelloAgente' => $config["idSelloAgente"],
                        'idSelloCliente' => $config["idSelloCliente"],
                    );
                }
                $addrs = false;
                $mppr = new Trafico_Model_ClientesDom();
                if (($row = $mppr->obtener($trafico->getIdCliente())) !== false) {
                    if ($row['nombre'] !== null && $row['calle'] !== null) {
                        $addrs = true;
                    }
                }
                $this->_helper->json(array("success" => true, "results" => $arr, "address" => $addrs, "sellos" => $sell));
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function enviarVucemAction()
    {
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "id" => "Digits",
                "idTrafico" => "Digits",
            );
            $v = array(
                "id" => array("NotEmpty", new Zend_Validate_Int()),
                "idTrafico" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("id") && $input->isValid("idTrafico")) {

                $mppr = new Trafico_Model_VucemMapper();
                $arr = $mppr->obtenerVucem($input->id);
                $vucem = new OAQ_TraficoVucem();

                $trafico = new OAQ_Trafico(array("idTrafico" => $input->idTrafico, "usuario" => $this->_session->username, "idUsuario" => $this->_session->id));
                if (isset($arr["idFactura"])) {
                    $vucem->setPatente($trafico->getPatente());
                    $vucem->setAduana($trafico->getAduana());
                    $vucem->setPedimento($trafico->getPedimento());
                    $vucem->setReferencia($trafico->getReferencia());
                    $arr = $vucem->enviarCove($input->id, $input->idTrafico, $arr["idFactura"]);
                    if (!empty($arr)) {
                        if ($vucem->analizarEnvioCove($mppr, $input->idTrafico, $input->id, $this->_session->id, $arr)) {
                            $this->_helper->json(array("success" => true));
                        }
                    }
                }
                if (isset($arr["idArchivo"])) {

                    $vucem->setPatente($trafico->getPatente());
                    $vucem->setAduana($trafico->getAduana());
                    $vucem->setPedimento($trafico->getPedimento());
                    $vucem->setReferencia($trafico->getReferencia());

                    $arr = $vucem->enviarEdocument($input->id, $input->idTrafico, $arr["idArchivo"], $arr["tipoDocumento"]);

                    if (!empty($arr)) {
                        if ($vucem->analizarEnvioEdocument($mppr, $input->idTrafico, $input->id, $this->_session->id, $arr)) {
                            $this->_helper->json(array("success" => true));
                        }
                    } else {
                        $this->_helper->json(array("success" => false));
                    }
                }
            } else {
                throw new Exception("Invalid input");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function consultaRespuestaVucemAction()
    {
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
                $mppr = new Trafico_Model_TraficoVucemLog();
                $arr = $mppr->obtener($input->id);
                if (!empty($arr)) {
                    $trafico = new OAQ_Trafico(array("idTrafico" => $arr["idTrafico"], "usuario" => $this->_session->username, "idUsuario" => $this->_session->id));
                    $vucem = new OAQ_TraficoVucem();
                    $vucem->setPatente($trafico->getPatente());
                    $vucem->setAduana($trafico->getAduana());
                    $vucem->setPedimento($trafico->getPedimento());
                    $vucem->setReferencia($trafico->getReferencia());
                    $mpprv = new Trafico_Model_VucemMapper();
                    $arrv = $mpprv->obtenerVucem($arr["idVucem"]);
                    if (isset($arrv["idFactura"])) {
                        if ($vucem->consultaRespestaCove($mppr, $input->id, $arr["idVucem"], $arr["numeroOperacion"], $this->_session->username, $this->_session->id)) {
                            $this->_helper->json(array("success" => true));
                        }
                    }
                    if (isset($arrv["idArchivo"])) {
                        if ($vucem->consultaRespuestaEdocument($mppr, $input->id, $arr["idVucem"], $arr["numeroOperacion"], $this->_session->username)) {
                            $this->_helper->json(array("success" => true));
                        }
                    }
                }
            } else {
                throw new Exception("Invalid input");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage() . " " . $ex->getTraceAsString()));
        }
    }

    public function consultaDetalleLogAction()
    {
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
                $mppr = new Trafico_Model_TraficoVucemLog();
                $arr = $mppr->obtenerUltimo($input->id);
                if (!empty($arr)) {
                    $this->_helper->json(array("success" => true, "results" => $arr));
                }
            } else {
                throw new Exception("Invalid input");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function enviarEmailAction()
    {
        try {
            $f = array(
                "id" => array(new Zend_Filter_StringTrim(), new Zend_Filter_StripTags(), new Zend_Filter_Digits()),
            );
            $v = array(
                "id" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("id")) {
                $view = new Zend_View();
                $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/get/");

                $referencias = new OAQ_Referencias();
                $res = $referencias->restricciones($this->_session->id, $this->_session->role);
                $mapper = new Archivo_Model_RepositorioIndex();

                $model = new Trafico_Model_TraficosMapper();
                $arr = $model->obtenerPorId($input->id);
                $repo = new Archivo_Model_RepositorioMapper();
                $files = $repo->getFilesByReferenceUsers($arr["referencia"], $arr["patente"], $arr["aduana"], array(23, 32, 33));
                if (!empty($files)) {
                    $view->archivos = $files;
                }
                if (isset($arr["idCliente"])) {
                    $cont = new Trafico_Model_ContactosCliMapper();
                    $contacts = $cont->obtenerTodos($arr["idCliente"]);
                    (!empty($contacts)) ? $view->contacts = $contacts : null;
                }
                $view->id = $input->id;
                echo $view->render("enviar-email.phtml");
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Zend_Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function downloadPhotosAction()
    {
        try {
            $this->_helper->layout()->disableLayout();
            $this->_helper->viewRenderer->setNoRender(true);
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "id" => "Digits",
            );
            $v = array(
                "id" => array("Digits", new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("id")) {
                $mppr = new Trafico_Model_Imagenes();
                $files = $mppr->obtenerTodas($input->id);
                $zipName = 'FOTOS_' . md5(microtime()) . '.zip';
                if (APPLICATION_ENV == "production") {
                    $zipFilename = '/tmp' . DIRECTORY_SEPARATOR . $zipName;
                } else {
                    $zipFilename = 'C:\\wamp64\\tmp' . DIRECTORY_SEPARATOR . $zipName;
                }
                $zip = new ZipArchive();
                if ($zip->open($zipFilename, ZIPARCHIVE::CREATE) !== TRUE) {
                    return null;
                }
                foreach ($files as $file) {
                    $image_file = $file["carpeta"] . DIRECTORY_SEPARATOR . $file["imagen"];
                    if (file_exists($image_file)) {
                        $zip->addFile($image_file, basename($file["nombre"]));
                    }
                }
                $zip->close();
                if (file_exists($zipFilename)) {
                    if (!is_file($zipFilename)) {
                        header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
                        echo 'File not found';
                    } else if (!is_readable($zipFilename)) {
                        header($_SERVER['SERVER_PROTOCOL'] . ' 403 Forbidden');
                        echo 'File not readable';
                    }
                    header($_SERVER['SERVER_PROTOCOL'] . ' 200 OK');
                    header("Content-Type: application/zip");
                    header("Content-Transfer-Encoding: Binary");
                    header("Content-Length: " . filesize($zipFilename));
                    header("Content-Disposition: attachment; filename=\"" . basename($zipFilename) . "\"");
                    readfile($zipFilename);
                    if (APPLICATION_ENV == "production") {
                        unlink($zipFilename);
                    }
                }
            } else {
                throw new Exception("Invalid input");
            }
        } catch (Exception $ex) {
            Zend_Debug::Dump($ex->getMessage());
        }
    }

    public function verificarChecklistAction()
    {
        try {
            $f = array(
                'idTrafico' => array(new Zend_Filter_StringTrim(), new Zend_Filter_StripTags(), new Zend_Filter_Digits()),
            );
            $v = array(
                'idTrafico' => array('NotEmpty', new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid('idTrafico')) {
                $model = new Trafico_Model_TraficosMapper();
                $arr = $model->obtenerPorId($input->idTrafico);
                $check = new Archivo_Model_ChecklistReferencias();
                if (($status = $check->buscar($arr['patente'], $arr['aduana'], $arr['referencia']))) {
                    if ($status['completo'] == 1) {
                        $this->_helper->json(array('success' => true, 'status' => '<div class="semaphore-green"></div>'));
                    } else {
                        $html = '';
                        if ($status['revisionAdministracion'] == 1) {
                            $html .= '<div class="semaphore-blue"></div>';
                        }
                        if ($status['revisionOperaciones'] == 1) {
                            $html .= '<div class="semaphore-orange"></div>';
                        }
                        $this->_helper->json(array('success' => true, 'status' => $html));
                    }
                    $this->_helper->json(array('success' => false));
                } else {
                    $this->_helper->json(array('success' => false));
                }
            } else {
                throw new Exception('Invalid input!');
            }
        } catch (Exception $ex) {
            $this->_helper->json(array('success' => false, 'message' => $ex->getMessage()));
        }
    }

    public function verArchivosAction()
    {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $r = $this->getRequest();
            if ($r->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                );
                $v = array(
                    "idTrafico" => new Zend_Validate_Int()
                );
                $input = new Zend_Filter_Input($f, $v, $r->getPost());
                if ($input->isValid("idTrafico")) {
                    $view = new Zend_View();
                    $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/get/");
                    $view->setHelperPath(realpath(dirname(__FILE__)) . "/../views/helpers/");
                    $mppr = new Trafico_Model_TraficosMapper();
                    $array = $mppr->obtenerPorId($input->idTrafico);
                    $repo = new Archivo_Model_RepositorioMapper();

                    $referencias = new OAQ_Referencias();
                    $res = $referencias->restricciones($this->_session->id, $this->_session->role);

                    $trafico = new OAQ_Trafico(array("idTrafico" => $input->idTrafico, "usuario" => $this->_session->username, "idUsuario" => $this->_session->id));
                    $index = $trafico->verificarIndexRepositorios();
                    $view->idRepo = $index;

                    if (in_array($this->_session->role, array("super", "gerente", "trafico_ejecutivo", "trafico"))) {
                        $view->canDelete = true;
                        $archivos = $repo->obtenerArchivosReferencia($array["referencia"]);
                        $view->archivos = $archivos;
                    } else if (in_array($this->_session->role, array("inhouse"))) {
                        $archivos = $repo->obtener($array["referencia"], $array["patente"], $array["aduana"], json_decode($res["documentos"]));
                        $view->archivos = $archivos;
                    }

                    $val = new OAQ_ArchivosValidacion();
                    $view->validacion = $val->archivosDePedimento($array["patente"], $array["aduana"], $array["pedimento"]);
                    $this->_helper->json(array("success" => true, "html" => $view->render("ver-archivos.phtml"), "repos" => $index));
                }
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function cargarXmlAction()
    {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $f = array(
                "*" => array("StringTrim", "StripTags"),
            );
            $v = array(
                "idTrafico" => new Zend_Validate_Int()
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("idTrafico")) {
                $view = new Zend_View();
                $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/get/");
                $view->setHelperPath(realpath(dirname(__FILE__)) . "/../views/helpers/");
                $view->idTrafico = $input->idTrafico;
                $this->_helper->json(array("success" => true, "html" => $view->render("cargar-xml.phtml")));
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function importarCdfiAction()
    {
        $f = array(
            "*" => array("StringTrim", "StripTags"),
        );
        $v = array(
            "idTrafico" => new Zend_Validate_Int()
        );
        $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());

        $sat = new OAQ_SATValidar();
        $content = file_get_contents("D:\\wamp64\\tmp\\5218d857b87b81a57f6a3b47448829ece8409c7a.xml");

        $invoice = $sat->cdfiComercio($content);
        if ($invoice) {
            var_dump($invoice);

            $traffics = new Trafico_Model_TraficosMapper();
            $traffic = $traffics->obtenerPorId($input->idTrafico);
            var_dump($traffic);
        }
    }

    public function subirSelloAction()
    {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $f = array(
                "*" => array("StringTrim", "StripTags"),
            );
            $v = array(
                "idCliente" => new Zend_Validate_Int()
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("idCliente")) {
                $view = new Zend_View();
                $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/get/");
                $view->setHelperPath(realpath(dirname(__FILE__)) . "/../views/helpers/");
                $view->idCliente = $input->idCliente;
                $this->_helper->json(array("success" => true, "html" => $view->render("subir-sello.phtml")));
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function subirSelloAgenteAction()
    {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $f = array(
                "*" => array("StringTrim", "StripTags"),
            );
            $v = array(
                "idAgente" => new Zend_Validate_Int()
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("idAgente")) {

                $mppr = new Trafico_Model_Agentes();
                $arr = $mppr->obtener($input->idAgente);

                $view = new Zend_View();
                $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/get/");
                $view->setHelperPath(realpath(dirname(__FILE__)) . "/../views/helpers/");
                $view->idAgente = $input->idAgente;
                $view->patente = $arr['patente'];
                $this->_helper->json(array("success" => true, "html" => $view->render("subir-sello-agente.phtml")));
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function obtenerSellosClienteAction()
    {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $f = array(
                "*" => array("StringTrim", "StripTags"),
            );
            $v = array(
                "idCliente" => new Zend_Validate_Int()
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("idCliente")) {
                $view = new Zend_View();
                $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/get/");
                $view->setHelperPath(realpath(dirname(__FILE__)) . "/../views/helpers/");

                $view->idCliente = $input->idCliente;
                $mppra = new Trafico_Model_SellosAgentes();
                $sss = $mppra->obtener();
                $mpprc = new Trafico_Model_SellosClientes();
                $ssc = $mpprc->obtener($input->idCliente);
                if (isset($ssc)) {
                    $sss = array_merge($sss, $ssc);
                }
                $view->sellos = $sss;

                $this->_helper->json(array("success" => true, "html" => $view->render("obtener-sellos-cliente.phtml")));
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function obtenerSellosAgenteAction()
    {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $f = array(
                "*" => array("StringTrim", "StripTags"),
            );
            $v = array(
                "idAgente" => new Zend_Validate_Int()
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("idAgente")) {
                $view = new Zend_View();
                $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/get/");
                $view->setHelperPath(realpath(dirname(__FILE__)) . "/../views/helpers/");

                $view->idAgente = $input->idAgente;
                $mppr = new Trafico_Model_SellosAgentes();
                $arr = $mppr->obtenerSellos($input->idAgente);
                $view->sellos = $arr;

                $this->_helper->json(array("success" => true, "html" => $view->render("obtener-sellos-agente.phtml")));
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function obtenerSelloDefaultAction()
    {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $f = array(
                "*" => array("StringTrim", "StripTags"),
            );
            $v = array(
                "idCliente" => new Zend_Validate_Int()
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("idCliente")) {
                $mppr = new Trafico_Model_CliSello();
                $id = $mppr->obtenerDefault($input->idCliente);
                if (isset($id)) {

                    $this->_helper->json(array("success" => true, "id" => $id));
                }
                $this->_helper->json(array("success" => false));
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function actualizacionSelloClienteAction()
    {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "id" => "Digits",
                "idCliente" => "Digits",
            );
            $v = array(
                "id" => new Zend_Validate_Int(),
                "idCliente" => new Zend_Validate_Int()
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("idCliente") && $input->isValid("id")) {
                $view = new Zend_View();
                $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/get/");
                $view->setHelperPath(realpath(dirname(__FILE__)) . "/../views/helpers/");

                if (in_array($this->_session->role, array("comercializacion", "super"))) {
                    $view->download = true;
                }

                $mprr = new Trafico_Model_SellosLogs();
                $view->logs = $mprr->obtenerLog($input->idCliente);
                $view->idCliente = $input->idCliente;
                $view->id = $input->id;

                $cus = new Trafico_Model_ClientesMapper();
                $arr = $cus->datosCliente($input->idCliente);

                $mpp = new Vucem_Model_VucemFirmanteMapper();
                $firmas = $mpp->sellosPorRfc($arr["rfc"]);

                $view->firmantes = $firmas;

                $this->_helper->json(array("success" => true, "html" => $view->render("actualizacion-sello-cliente.phtml")));
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function mvhcEstatusObtenerAction()
    {
        try {
            $f = array(
                "id" => array(new Zend_Filter_StringTrim(), new Zend_Filter_StripTags(), new Zend_Filter_Digits()),
            );
            $v = array(
                "id" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("id")) {
                $traffic = new OAQ_Trafico(array("idTrafico" => $input->id));
                $idRepo = $traffic->verificarIndexRepositorios();
                if (isset($idRepo)) {
                    $mppr = new Archivo_Model_RepositorioIndex();
                    if (($arr = $mppr->datos($idRepo))) {
                        $this->_helper->json(array(
                            "success" => true,
                            "mvhcCliente" => $arr["mvhcCliente"],
                            "mvhcFirmado" => $arr["mvhcFirmada"],
                            "mvhcEnviada" => $arr["mvhcEnviada"],
                            "numGuia" => $arr["numGuia"],
                        ));
                    } else {
                        throw new Exception("No data found!");
                    }
                } else {
                    throw new Exception("No data found!");
                }
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Zend_Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function vucemGuardarAction()
    {
        try {
            $f = array(
                "id" => array(new Zend_Filter_StringTrim(), new Zend_Filter_StripTags(), new Zend_Filter_Digits()),
            );
            $v = array(
                "id" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("id")) {

                $vucem = new OAQ_TraficoVucem();

                $mppr = new Trafico_Model_VucemMapper();
                $arr = $mppr->obtenerVucem($input->id);

                if (isset($arr["idFactura"])) {
                    if ($vucem->guardarDetalleCoveXmlPdf($input->id, $this->_session->username, $this->_session->id)) {
                        $this->_helper->json(array("success" => true));
                    }
                }
                if (isset($arr["idArchivo"])) {
                    if ($vucem->guardarEdocumentXmlPdf($input->id, $this->_session->username, $this->_session->id)) {
                        $this->_helper->json(array("success" => true));
                    }
                }

                $this->_helper->json(array("success" => true));
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Zend_Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function cargarTiposEdocumentsAction()
    {
        try {
            $f = array(
                "idArchivo" => array(new Zend_Filter_StringTrim(), new Zend_Filter_StripTags(), new Zend_Filter_Digits()),
                "idTrafico" => array(new Zend_Filter_StringTrim(), new Zend_Filter_StripTags(), new Zend_Filter_Digits()),
            );
            $v = array(
                "idArchivo" => array("NotEmpty", new Zend_Validate_Int()),
                "idTrafico" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("idArchivo") && $input->isValid("idTrafico")) {

                $view = new Zend_View();
                $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/get/");
                $view->setHelperPath(realpath(dirname(__FILE__)) . "/../views/helpers/");

                $form = new Vucem_Form_MultiplesEDocuments();
                $view->idArchivo = $input->idArchivo;
                $view->idTrafico = $input->idTrafico;

                $repo = new Archivo_Model_Repositorio();
                $arr = $repo->obtenerPorArregloId($input->idArchivo);
                $view->archivo = $arr[0];

                $mppr = new Archivo_Model_DocumentosMapper();
                $view->documentos = $mppr->getAllEdocument();

                $this->_helper->json(array("success" => true, "html" => $view->render("cargar-tipos-edocuments.phtml")));
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Zend_Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function clientesCorresponsalAction()
    {
        try {
            $f = array(
                "idAduana" => array(new Zend_Filter_StringTrim(), new Zend_Filter_StripTags(), new Zend_Filter_Digits()),
            );
            $v = array(
                "idAduana" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("idAduana")) {

                $mppr = new Trafico_Model_TraficoAduanasMapper();
                $row = $mppr->aduana($input->idAduana);
                if (!empty($row)) {
                    $tbl = new Trafico_Model_TraficoCliAduanasMapper();
                    $arr = $tbl->clientesPorAduana($row["patente"], $row["aduana"]);

                    $this->_helper->json(array("success" => true, "result" => $arr));
                } else {
                    $this->_helper->json(array("success" => false));
                }
            } else {
                $this->_helper->json(array("success" => false, "message" => "Invalid input!"));
            }
        } catch (Zend_Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function descargaCarpetaExpedienteAction()
    {
        try {
            $f = array(
                "id" => array(new Zend_Filter_StringTrim(), new Zend_Filter_StripTags(), new Zend_Filter_Digits()),
            );
            $v = array(
                "id" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("id")) {
                $traffic = new OAQ_Trafico(array("idTrafico" => $input->id));
                $id = $traffic->verificarIndexRepositorios();
                if ($id) {
                    $this->_helper->json(array("success" => true, "id" => $id));
                } else {
                    $this->_helper->json(array("success" => false));
                }
            } else {
                $this->_helper->json(array("success" => false, "message" => "Invalid input!"));
            }
        } catch (Zend_Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function permalinkAction()
    {
        require_once 'random_compat/psalm-autoload.php';
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "id" => array("Digits"),
            );
            $v = array(
                "id" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("id")) {

                $view = new Zend_View();
                $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/get/");

                $traffic = new OAQ_Trafico(array("idTrafico" => $input->id));
                $id = $traffic->verificarIndexRepositorios();
                if ($id) {

                    $mppr = new Archivo_Model_RepositorioPermalinks();
                    if (!($validar = $mppr->verificar($id))) {
                        $validar = base64_encode(random_bytes(32));
                        $mppr->agregar($input->id, $validar, $this->_session->username);
                    }

                    if (APPLICATION_ENV == "production") {
                        $uri = "https://oaq.dnsalias.net/clientes/expediente?code=" . urlencode($validar);
                    } else {
                        $uri = "http://localhost:8090/clientes/expediente?code=" . urlencode($validar);
                    }
                    $view->uri = $uri;
                    if ($traffic->getIdCliente()) {
                        $cont = new Trafico_Model_ContactosCliMapper();
                        $contacts = $cont->obtenerTodos($traffic->getIdCliente());
                        (!empty($contacts)) ? $view->contacts = $contacts : null;
                    }
                    echo $view->render("permalink.phtml");
                } else {
                    echo "<p>No hay expediente.</p>";
                }
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Zend_Exception $e) {
            throw new Zend_Exception($e->getMessage());
        }
    }

    public function permalinkTraficoAction()
    {
        require_once 'random_compat/psalm-autoload.php';
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "id" => array("Digits"),
            );
            $v = array(
                "id" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("id")) {

                $view = new Zend_View();
                $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/get/");

                $traffic = new OAQ_Trafico(array("idTrafico" => $input->id));

                $mppr = new Archivo_Model_RepositorioPermalinks();
                if (!($validar = $mppr->verificarIdTrafico($input->id))) {
                    $validar = base64_encode(random_bytes(32));
                    $mppr->agregarIdTrafico($input->id, $validar, $this->_session->username);
                }

                if (APPLICATION_ENV == "production") {
                    $uri = "https://oaq.dnsalias.net/clientes/expediente/archivos?code=" . urlencode($validar);
                } else {
                    $uri = "http://localhost:8090/clientes/expediente/archivos?code=" . urlencode($validar);
                }

                $view->uri = $uri;
                if ($traffic->getIdCliente()) {
                    $cont = new Trafico_Model_ContactosCliMapper();
                    $contacts = $cont->obtenerTodos($traffic->getIdCliente());
                    (!empty($contacts)) ? $view->contacts = $contacts : null;
                }
                echo $view->render("permalink.phtml");
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Zend_Exception $e) {
            throw new Zend_Exception($e->getMessage());
        }
    }

    public function rawdataAction()
    {
        try {
            $f = array(
                "id" => array(new Zend_Filter_StringTrim(), new Zend_Filter_StripTags(), new Zend_Filter_Digits()),
            );
            $v = array(
                "id" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("id")) {

                $template = new OAQ_Trafico_Plantillas(array("idTrafico" => $input->id));
                $template->rawdata();
            } else {
                $this->_helper->json(array("success" => false, "message" => "Invalid input!"));
            }
        } catch (Zend_Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function descargaPlantillaCasaAction()
    {
        try {
            $f = array(
                "id" => array(new Zend_Filter_StringTrim(), new Zend_Filter_StripTags(), new Zend_Filter_Digits()),
            );
            $v = array(
                "id" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("id")) {

                $template = new OAQ_Trafico_Plantillas(array("idTrafico" => $input->id));
                $template->plantillaCasa();
            } else {
                $this->_helper->json(array("success" => false, "message" => "Invalid input!"));
            }
        } catch (Zend_Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function descargaPlantillaSlamAction()
    {
        try {
            $f = array(
                "id" => array(new Zend_Filter_StringTrim(), new Zend_Filter_StripTags(), new Zend_Filter_Digits()),
            );
            $v = array(
                "id" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("id")) {

                $template = new OAQ_Trafico_Plantillas(array("idTrafico" => $input->id));
                $template->plantillaSlam();
            } else {
                $this->_helper->json(array("success" => false, "message" => "Invalid input!"));
            }
        } catch (Zend_Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function subirArchivosAction()
    {
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
                        $view->idTrafico = $input->idTrafico;
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

    public function obtenerSoiaAction()
    {
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "id" => array("Digits"),
            );
            $v = array(
                "id" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("id")) {

                $traficos = new OAQ_Trafico(array("idTrafico" => $input->id));
                $arr = $traficos->obtenerDatos();

                $soia = new Trafico_Model_Soia();
                $array = $soia->obtener($arr['patente'], $arr['aduana'], $arr['pedimento']);

                if (!empty($array)) {
                    $this->_helper->json(array("success" => true, "result" => $array));
                }
                $this->_helper->json(array("success" => false));
            } else {
                $this->_helper->json(array("success" => false, "message" => "Invalid input!"));
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function soiaAction()
    {
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "id" => array("Digits"),
            );
            $v = array(
                "id" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("id")) {

                $view = new Zend_View();
                $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/get/");
                $view->setHelperPath(realpath(dirname(__FILE__)) . "/../views/helpers/");

                $misc = new OAQ_Misc();

                $traficos = new OAQ_Trafico(array("idTrafico" => $input->id));
                $arr = $traficos->obtenerDatos();

                $view->arr = $arr;

                $curl = curl_init();

                $uuid = $misc->getUuid(time());

                switch ($arr['pedimento']) {
                    case (preg_match('/^1/', $arr['pedimento']) ? true : false):
                        $year = 2011;
                        break;
                    case (preg_match('/^2/', $arr['pedimento']) ? true : false):
                        $year = 2012;
                        break;
                    case (preg_match('/^3/', $arr['pedimento']) ? true : false):
                        $year = 2013;
                        break;
                    case (preg_match('/^4/', $arr['pedimento']) ? true : false):
                        $year = 2014;
                        break;
                    case (preg_match('/^5/', $arr['pedimento']) ? true : false):
                        $year = 2015;
                        break;
                    case (preg_match('/^6/', $arr['pedimento']) ? true : false):
                        $year = 2016;
                        break;
                    case (preg_match('/^7/', $arr['pedimento']) ? true : false):
                        $year = 2017;
                        break;
                    case (preg_match('/^8/', $arr['pedimento']) ? true : false):
                        $year = 2018;
                        break;
                    case (preg_match('/^9/', $arr['pedimento']) ? true : false):
                        $year = 2019;
                        break;
                    default:
                        $year = 2020;
                }

                $aduana = '';
                switch ($traficos->getAduana()) {
                    case 10:
                        $aduana = 'ACAPULCO, ACAPULCO DE JUAREZ, GUERRERO';
                        break;
                    case 6660:
                        $aduana = 'ADUANA VIRTUAL PARA PREVALIDADORES CPN';
                        break;
                    case 470:
                        $aduana = 'AEROPUERTO INTERNAL. CD. DE MEXICO, D.F.';
                        break;
                    case 20:
                        $aduana = 'AGUA PRIETA, SON.';
                        break;
                    case 730:
                        $aduana = 'AGUASCALIENTES, AGS.';
                        break;
                    case 810:
                        $aduana = 'ALTAMIRA, TAMPS.';
                        break;
                    case 530:
                        $aduana = 'CANCUN, Q. ROO.';
                        break;
                    case 440:
                        $aduana = 'CD. ACUNA, COAH.';
                        break;
                    case 820:
                        $aduana = 'CD. CAMARGO, TAMPS.';
                        break;
                    case 60:
                        $aduana = 'CD. DEL CARMEN, CAMP.';
                        break;
                    case 370:
                        $aduana = 'CD. HIDALGO, CHIS.';
                        break;
                    case 70:
                        $aduana = 'CD. JUAREZ, CHIH.';
                        break;
                    case 340:
                        $aduana = 'CD. MIGUEL ALEMAN, TAMPS.';
                        break;
                    case 300:
                        $aduana = 'CD. REYNOSA, TAMPS.';
                        break;
                    case 670:
                        $aduana = 'CHIHUAHUA, CHIH.';
                        break;
                    case 80:
                        $aduana = 'COATZACOALCOS, VER.';
                        break;
                    case 800:
                        $aduana = 'COLOMBIA, N.L.';
                        break;
                    case 830:
                        $aduana = 'DOS BOCAS';
                        break;
                    case 110:
                        $aduana = 'ENSENADA, B.C.';
                        break;
                    case 480:
                        $aduana = 'GUADALAJARA, JAL.';
                        break;
                    case 840:
                        $aduana = 'GUANAJUATO, GTO';
                        break;
                    case 120:
                        $aduana = 'GUAYMAS, SON.';
                        break;
                    case 140:
                        $aduana = 'LA PAZ, B.C.S.';
                        break;
                    case 510:
                        $aduana = 'LAZARO CARDENAS, MICH.';
                        break;
                    case 160:
                        $aduana = 'MANZANILLO, COL.';
                        break;
                    case 170:
                        $aduana = 'MATAMOROS,TAMPS.';
                        break;
                    case 180:
                        $aduana = 'MAZATLAN, SIN.';
                        break;
                    case 190:
                        $aduana = 'MEXICALI, B.C.';
                        break;
                    case 200:
                        $aduana = 'MEXICO';
                        break;
                    case 520:
                        $aduana = 'MONTERREY, N.L.';
                        break;
                    case 220:
                        $aduana = 'NACO, SON.';
                        break;
                    case 230:
                        $aduana = 'NOGALES, SON.';
                        break;
                    case 240:
                        $aduana = 'NUEVO LAREDO, TAMPS.';
                        break;
                    case 250:
                        $aduana = 'OJINAGA, CHIH.';
                        break;
                    case 270:
                        $aduana = 'PIEDRAS NEGRAS, COAH.';
                        break;
                    case 280:
                        $aduana = 'PROGRESO, YUC.';
                        break;
                    case 750:
                        $aduana = 'PUEBLA, PUE.';
                        break;
                    case 260:
                        $aduana = 'PUERTO PALOMAS, CHIH.';
                        break;
                    case 640:
                        $aduana = 'QUERETARO, QRO.';
                        break;
                    case 310:
                        $aduana = 'SALINA CRUZ, OAX.';
                        break;
                    case 330:
                        $aduana = 'SAN LUIS RIO COLORADO, SON.';
                        break;
                    case 500:
                        $aduana = 'SONOYTA, SON.';
                        break;
                    case 50:
                        $aduana = 'SUBTENIENTE LOPEZ, Q. ROO.';
                        break;
                    case 380:
                        $aduana = 'TAMPICO, TAMPS.';
                        break;
                    case 390:
                        $aduana = 'TECATE, B.C.';
                        break;
                    case 400:
                        $aduana = 'TIJUANA, B.C.';
                        break;
                    case 650:
                        $aduana = 'TOLUCA, MEX.';
                        break;
                    case 460:
                        $aduana = 'TORREON, COAH.';
                        break;
                    case 420:
                        $aduana = 'TUXPAN, VER.';
                        break;
                    case 430:
                        $aduana = 'VERACRUZ, VER.';
                        break;
                }

                //$uri = "https://aplicacionesc.mat.sat.gob.mx/SOIANET/oia_consultarapd_cep.aspx?Patente={$arr['patente']}&NumDcto={$arr['pedimento']}&Secuencia=0&AnioPed={$year}&Aduana={$arr['aduana']}";
                // https://aplicacionesc.mat.sat.gob.mx/soianet/oia_consultarapd_cep.aspx?&pa=3589&dn=7015587&s=0&ap=2017&pad=640&ad=QUERETARO,%20QRO.
                // https://aplicacionesc.mat.sat.gob.mx/SOIANET/oia_consultarapd_cep.aspx?&pa=3589&dn=7015587&s=0&ap=2017&pad=640&ad=QUERETARO,%20QRO.
                $uri = "https://aplicacionesc.mat.sat.gob.mx/SOIANET/oia_consultarapd_cep.aspx?&pa={$arr['patente']}&dn={$arr['pedimento']}&s=0&ap={$year}&pad={$arr['aduana']}&ad=" . urlencode($aduana);
                $view->uri = $uri;

                curl_setopt_array($curl, array(
                    CURLOPT_URL => $uri,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => "",
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 30,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => "GET",
                    CURLOPT_POSTFIELDS => "",
                    CURLOPT_HTTPHEADER => array(
                        "Postman-Token: {$uuid}",
                        "cache-control: no-cache"
                    ),
                ));

                $response = curl_exec($curl);
                $err = curl_error($curl);

                curl_close($curl);

                if ($err) {
                    $view->result = "cURL Error #:" . $err;
                } else {
                    preg_match('#\<table id=\"tbConsultaRapidaD\".*?\/>(.+?)\<\/table\>#s', $response, $converted);
                    if (isset($converted[0])) {
                        $match = preg_replace("/<([a-z][a-z0-9]*)[^>]*?(\/?)>/i", '<$1$2>', $converted[0]);
                        $view->contenido = utf8_encode($match);
                    }
                }

                echo $view->render("soia.phtml");
            } else {
                $this->_helper->json(array("success" => false, "message" => "Invalid input!"));
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function enviarFtpAction()
    {
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "id" => array("Digits"),
            );
            $v = array(
                "id" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("id")) {

                $view = new Zend_View();
                $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/get/");
                $view->setHelperPath(realpath(dirname(__FILE__)) . "/../views/helpers/");

                $trafico = new OAQ_Trafico(array("idTrafico" => $input->id));
                $arr = $trafico->obtenerDatos();

                if ($arr) {

                    $mppr = new Trafico_Model_ClientesMapper();
                    $row = $mppr->datosCliente($arr['idCliente']);

                    $view->razon_social = $row['nombre'];
                    $view->rfc = $row['rfc'];

                    $mdl = new Automatizacion_Model_FtpMapper();
                    $server = $mdl->obtenerDatosFtp($row["rfc"]);

                    if ($server) {
                        $view->ftp_url = $server["url"];
                        $view->ftp = true;

                        $repo = new Archivo_Model_RepositorioMapper();
                        $array = $repo->archivosNoEnviadosFtp($arr['patente'], $arr['aduana'], $arr['referencia'], $arr['rfcCliente']);

                        if (!empty($array)) {
                            $view->archivos = $array;
                        }
                    }
                }

                $this->_helper->json(array("success" => true, "html" => $view->render("enviar-ftp.phtml")));
            } else {
                $this->_helper->json(array("success" => false, "message" => "Invalid input!"));
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function enviarArchivosFtpAction()
    {
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "id" => array("Digits"),
            );
            $v = array(
                "id" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("id")) {

                $view = new Zend_View();
                $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/get/");
                $view->setHelperPath(realpath(dirname(__FILE__)) . "/../views/helpers/");

                $this->_helper->json(array("success" => true));
            } else {
                $this->_helper->json(array("success" => false, "message" => "Invalid input!"));
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function buscarExpedienteIndexAction()
    {
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "id" => array("Digits"),
            );
            $v = array(
                "id" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("id")) {

                $trafico = new OAQ_Trafico(array("idTrafico" => $input->id, "idUsuario" => $this->_session->id));
                $index = new Archivo_Model_RepositorioIndex();

                if (($arr = $index->buscarDatos($trafico->getPatente(), $trafico->getAduana(), $trafico->getPedimento(), $trafico->getReferencia()))) {
                    $array = array(
                        "idRepositorio" => $arr['id'],
                        "revisionAdministracion" => $arr['revisionAdministracion'],
                        "revisionOperaciones" => $arr['revisionOperaciones'],
                        "completo" => $arr['completo'],
                    );
                    $trafico->actualizar($array);

                    $this->_helper->json(array("success" => true, "id" => $arr['id']));
                }
                $this->_helper->json(array("success" => true));
            } else {
                $this->_helper->json(array("success" => false, "message" => "Invalid input!"));
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function descargarExpedienteAction()
    {
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "id" => array("Digits"),
            );
            $v = array(
                "id" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("id")) {
                $trafico = new OAQ_Trafico(array("idTrafico" => $input->id));
                $zipFilename = $trafico->crearZip($this->_session->id, $this->_session->role);
                if (file_exists($zipFilename)) {
                    if (!is_file($zipFilename)) {
                        header($this->getRequest()->getServer('SERVER_PROTOCOL') . " 404 Not Found");
                        echo "File not found";
                    } else if (!is_readable($zipFilename)) {
                        header($this->getRequest()->getServer('SERVER_PROTOCOL') . " 403 Forbidden");
                        echo "File not readable";
                    }
                    header($this->getRequest()->getServer('SERVER_PROTOCOL') . " 200 OK");
                    header("Content-Type: application/zip");
                    header("Content-Transfer-Encoding: Binary");
                    header("Content-Length: " . filesize($zipFilename));
                    header("Content-Disposition: attachment; filename=\"" . basename($zipFilename) . "\"");
                    readfile($zipFilename);
                    unlink($zipFilename);
                }
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Zend_Exception $e) {
            throw new Zend_Exception($e->getMessage());
        }
    }

    public function descargaSolicitudesAction()
    {
        try {
            $f = array(
                "ids" => array(new Zend_Filter_StringTrim(), new Zend_Filter_StripTags()),
            );
            $v = array(
                "ids" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("ids")) {
                if (!empty($input->ids) && is_array($input->ids)) {
                    foreach ($input->ids as $id) {
                    }
                }
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Zend_Exception $e) {
            throw new Zend_Exception($e->getMessage());
        }
    }

    public function guardarSolicitudAction()
    {
        try {
            $f = array(
                "id" => array(new Zend_Filter_StringTrim(), new Zend_Filter_StripTags()),
            );
            $v = array(
                "id" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("id")) {
                $sto = new Trafico_Model_AlmacenMapper();
                $model = new Trafico_Model_TraficoSolicitudesMapper();
                $request = new Trafico_Model_TraficoSolicitudesMapper();
                $header = $request->obtener($input->id);
                $table = new Trafico_Model_TraficoSolDetalleMapper();
                $detalle = $table->obtener($input->id);
                $model = new Trafico_Model_TraficoSolConceptoMapper();
                $conceptos = $model->obtenerImpresion($input->id);
                $dbtable = new Trafico_Model_TraficoConceptosMapper();
                $bank = new Trafico_Model_TraficoBancosMapper();
                $concepts = $dbtable->obtener($header["idAduana"]);
                $chunk = array_chunk($concepts, 2, true);
                $rows = array();
                $total = 0;
                foreach ($chunk as $row) {
                    $roww = array();
                    foreach ($row as $k => $v) {
                        if (!isset($roww[0])) {
                            $roww[0] = trim($v);
                            if (isset($conceptos[$k])) {
                                $roww[1] = $conceptos[$k];
                                $total += $conceptos[$k];
                            } else {
                                $roww[1] = '';
                            }
                        } else {
                            $roww[2] = trim($v);
                            if (isset($conceptos[$k])) {
                                $roww[3] = $conceptos[$k];
                                $total += $conceptos[$k];
                            } else {
                                $roww[3] = '';
                            }
                        }
                    }
                    $rows[] = $roww;
                }
                $pre["header"] = $header;
                $pre["detalle"] = $detalle;
                $pre["conceptos"] = $rows;
                $pre["detalle"]["almacen"] = (isset($pre["detalle"]["almacen"])) ? $sto->obtenerNombreAlmacen($pre["detalle"]["almacen"]) : null;
                $pre["anticipo"] = $model->obtenerAnticipo($input->id);
                $pre["total"] = $total;
                $tbl = new Trafico_Model_TraficoBancosMapper();
                $banco = $tbl->obtenerBancoDefault((int) $header["idAduana"]);
                if (isset($banco) && !empty($banco)) {
                    $pre["banco"] = $banco;
                } else {
                    $pre["banco"] = array(
                        'nombre' => 'N/D',
                        'razonSocial' => '',
                        'cuenta' => '',
                        'clabe' => '',
                        'sucursal' => '',
                    );
                }
                require 'tcpdf/solicitud.php';
                if (isset($pre)) {
                    $pre["colors"]["line"] = array(5, 5, 5);
                    $pdf = new Trafico($pre, 'P', 'pt', 'LETTER');
                    $pdf->SolicitudAnticipo();
                    $misc = new OAQ_Misc();
                    if (APPLICATION_ENV == "production") {
                        $base_dir = "/home/samba-share/expedientes";
                    } else {
                        $base_dir = "D:\\xampp\\tmp\\expedientes";
                    }
                    if (($directory = $misc->createReferenceDir($base_dir, $header["patente"], $header["aduana"], $misc->trimUpper($header["referencia"])))) {
                        $filename = $directory . DIRECTORY_SEPARATOR . "SOL_" . $header["aduana"] . '_' . $header["patente"] . '_' . $header["pedimento"] . '_' . $header["referencia"] . '_' . $input->id . '.pdf';
                        if (file_exists($filename)) {
                            unlink($filename);
                        } else {
                            $pdf->Output($filename, 'F');
                            if (file_exists($filename)) {
                                $repo = new Archivo_Model_RepositorioMapper();
                                $arr = array(
                                    "tipo_archivo" => 31,
                                    "sub_tipo_archivo" => null,
                                    "patente" => $header["patente"],
                                    "aduana" => $header["aduana"],
                                    "pedimento" => $header["pedimento"],
                                    "referencia" => $misc->trimUpper($header["referencia"]),
                                    "nom_archivo" => trim(basename($filename)),
                                    "ubicacion" => $filename,
                                    "rfc_cliente" => $header["rfcCliente"],
                                    "creado" => date("Y-m-d H:i:s"),
                                    "usuario" => $this->_session->username,
                                );
                                if (!($idr = $repo->verificar($header["patente"], $misc->trimUpper($header["referencia"]), basename($filename)))) {
                                    if (($idr = $repo->agregar($arr))) {
                                        $this->_helper->json(array("success" => true, "id" => $idr));
                                    }
                                }
                                $this->_helper->json(array("success" => true, "id" => $idr));
                            } else {
                                $this->_helper->json(array("success" => false));
                            }
                        }
                    } else {
                        throw new Exception("Unable to create base directory.");
                    }
                }
            } else {
                throw new Exception("Invalid input.");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function pdfFacturaAction()
    {
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "idTrafico" => array("Digits"),
                "idFactura" => array("Digits"),
            );
            $v = array(
                "idTrafico" => array("NotEmpty", new Zend_Validate_Int()),
                "idFactura" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("idTrafico") && $input->isValid("idFactura")) {
                $view = new Zend_View();
                $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/get/");
                $view->setHelperPath(realpath(dirname(__FILE__)) . "/../views/helpers/");

                $mppr = new Trafico_Model_TraficoFacturasMapper();
                $arr = $mppr->detalleFactura($input->idFactura);

                $view->nombreProveedor = $arr["nombreProveedor"];
                $view->numeroFactura = $arr["numeroFactura"];
                $view->idTrafico = $input->idTrafico;
                $view->idFactura = $input->idFactura;

                $this->_helper->json(array("success" => true, "html" => $view->render("pdf-factura.phtml")));
            } else {
                $this->_helper->json(array("success" => false, "message" => "Invalid input!"));
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function editarFacturaOriginalAction()
    {
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "idTrafico" => array("Digits"),
                "id" => array("Digits"),
            );
            $v = array(
                "idTrafico" => array("NotEmpty", new Zend_Validate_Int()),
                "id" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("idTrafico") && $input->isValid("id")) {
                $view = new Zend_View();
                $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/get/");
                $view->setHelperPath(realpath(dirname(__FILE__)) . "/../views/helpers/");

                $model = new Archivo_Model_RepositorioMapper();
                $row = $model->getFileById($input->id);

                $view->idRepositorio = $input->id;
                $view->idTrafico = $input->idTrafico;
                $view->idFactura = $row["id_factura"];
                $view->numeroFactura = $row["folio"];
                $view->nombreProveedor = $row["emisor_nombre"];

                $this->_helper->json(array("success" => true, "html" => $view->render("editar-factura-original.phtml")));
            } else {
                $this->_helper->json(array("success" => false, "message" => "Invalid input!"));
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function vucemEnviarMultipleAction()
    {
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "idTrafico" => array("Digits"),
                "ids" => array("Digits"),
            );
            $v = array(
                "idTrafico" => array("NotEmpty", new Zend_Validate_Int()),
                "ids" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("idTrafico") && $input->isValid("ids")) {
                $view = new Zend_View();
                $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/get/");
                $view->setHelperPath(realpath(dirname(__FILE__)) . "/../views/helpers/");

                $arr = [];

                $mppr = new Trafico_Model_VucemMapper();

                if (is_array($input->ids)) {
                    foreach ($input->ids as $id) {
                        $row = $mppr->obtenerVucem($id);
                        $arr[] = $row;
                    }
                }

                $view->rows = $arr;

                $this->_helper->json(array("success" => true, "html" => $view->render("vucem-enviar-multiple.phtml")));
            } else {
                $this->_helper->json(array("success" => false, "message" => "Invalid input!"));
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function descargarSelloClienteAction()
    {
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

                if (!in_array($this->_session->role, array("comercializacion", "super"))) {
                    throw new Exception("Access denied!");
                }

                $mppr = new Vucem_Model_VucemFirmanteMapper();
                $sello = $mppr->obtenerDetalleFirmanteId($input->id);

                if (!empty($sello)) {

                    $keyName = "/tmp" . DIRECTORY_SEPARATOR . $sello["key_nom"];
                    $cerName = "/tmp" . DIRECTORY_SEPARATOR . $sello["cer_nom"];
                    $txtFilename = "/tmp" . DIRECTORY_SEPARATOR . $sello["rfc"] . ".txt";

                    $zipName = "/tmp" . DIRECTORY_SEPARATOR . str_replace(".key", ".zip", $sello["key_nom"]);

                    if (file_exists($txtFilename)) {
                        unlink($txtFilename);
                    }
                    $file = file_put_contents($txtFilename, "User: {$sello["rfc"]}\nPass: {$sello["spem_pswd"]}\nWS: {$sello["ws_pswd"]}", FILE_APPEND | LOCK_EX);

                    file_put_contents($keyName, base64_decode($sello["key"]));
                    file_put_contents($cerName, base64_decode($sello["cer"]));

                    $zip = new ZipArchive();

                    if ($zip->open($zipName, ZipArchive::CREATE) !== TRUE) {
                        exit("cannot open <$zipName>\n");
                    }
                    $zip->addFile($keyName, basename($keyName));
                    $zip->addFile($cerName, basename($cerName));
                    $zip->addFile($txtFilename, basename($txtFilename));
                    $zip->close();
                    header("Content-Type: application/zip");
                    header("Content-Disposition: attachment; filename=" . basename($zipName));
                    header("Content-Length: " . filesize($zipName));
                    readfile($zipName);
                } else {
                    throw new Exception("No key found!");
                }
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }
}
