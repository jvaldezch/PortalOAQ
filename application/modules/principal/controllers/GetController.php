<?php

class Principal_GetController extends Zend_Controller_Action {

    protected $_session;
    protected $_config;
    protected $_appconfig;

    public function init() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_appconfig = new Application_Model_ConfigMapper();
        $this->_redirector = $this->_helper->getHelper("Redirector");
        $this->_config = new Zend_Config_Ini(APPLICATION_PATH . "/configs/application.ini", APPLICATION_ENV);
        $this->_logger = Zend_Registry::get("logDb");
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

    public function misSolicitudesAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        try {
            $mppr = new Principal_Model_UsuariosSolicitudes();
            $arr = $mppr->misSolicitudes($this->_session->username);
            $this->_helper->json(array("success" => true, "result" => $arr));
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }
    
    public function editarSolicitudAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
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
                $mppr = new Principal_Model_UsuariosSolicitudes();
                $arr = $mppr->obtener($input->id);
                if ($arr["idSolicitud"] == 1) {
                    
                }
                $this->_helper->json(array("success" => true, "html" => $view->render("editar-solicitud.phtml")));
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public function imprimirSolicitudAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
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
                $mppr = new Principal_Model_UsuariosSolicitudes();
                $arr = $mppr->obtener($input->id);
                if ($arr["idSolicitud"] == 1) {
                    $array = array(
                        "prefijoDocumento" => "SOLICITUDEQUIPO_",
                        "nombreDocumento" => "SOLICITUD DE EQUIPO DE COMPUTO",
                        "versionDocumento" => "",
                        "direccion" => "Mariano Perrusquia No. 102, interior 7. San Angel. CP 76030, Querétaro, Qro.\nTeléfonos: +52 (442)216 0870 y 216 0750 / Fax: +52(442)216 0836",
                        "de" => array(
                            "nombre" => "Nombre",
                            "posicion" => "Posición",
                            "departamento" => "Departamento"
                        ),
                        "para" => array(
                            "nombre" => "Nombre",
                            "posicion" => "Posición",
                            "departamento" => "Departamento"
                        ),
                        "empresa" => "ORGANIZACIÓN ADUANAL DE QUERÉTARO S.C.",
                    );
                    $print = new OAQ_Imprimir_SolicitudEquipo($array, "P", "pt", "LETTER");
                    $print->crear();
                    $print->set_filename($array["prefijoDocumento"] . ".pdf");
                    $print->Output($print->get_filename(), "I");
                }
                if ($arr["idSolicitud"] == 2) {
                    $array = array(
                        "prefijoDocumento" => "CREDENCIALESACCESO_",
                        "nombreDocumento" => "SOLICITUD DE CREDENCIALES DE ACCESO",
                        "versionDocumento" => "",
                        "direccion" => "Mariano Perrusquia No. 102, interior 7. San Angel. CP 76030, Querétaro, Qro.\nTeléfonos: +52 (442)216 0870 y 216 0750 / Fax: +52(442)216 0836",
                        "de" => array(
                            "nombre" => "Nombre",
                            "posicion" => "Posición",
                            "departamento" => "Departamento"
                        ),
                        "para" => array(
                            "nombre" => "Nombre",
                            "posicion" => "Posición",
                            "departamento" => "Departamento"
                        ),
                        "empresa" => "ORGANIZACIÓN ADUANAL DE QUERÉTARO S.C.",
                    );
                    $print = new OAQ_Imprimir_SolicitudCredenciales($array, "P", "pt", "LETTER");
                    $print->crear();
                    $print->set_filename($array["prefijoDocumento"] . ".pdf");
                    $print->Output($print->get_filename(), "I");
                }
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public function descargarArchivoAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
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
                $folder = realpath(APPLICATION_PATH . "/../public/sgc2015/");
                $mapper = new Rrhh_Model_IsoArchivos();
                $arr = $mapper->obtener($i->id);
                if (($d = $this->_buscarParent($arr["carpeta"]))) {
                    $folder .= $d . DIRECTORY_SEPARATOR . $arr["carpeta"];
                }
                if (!isset($d) && $arr["carpeta"] !== "") {
                    $folder .= DIRECTORY_SEPARATOR . $arr["carpeta"];
                }
                if (isset($arr)) {
                    $filename = $folder . DIRECTORY_SEPARATOR . $arr["archivo"];
                    if (file_exists($filename)) {
                        header("Cache-Control: public");
                        header("Content-Description: File Transfer");
                        header("Content-Disposition: attachment; filename=\"" . $this->_convert($arr["nombreArchivo"]) . "\"");
                        header("Content-length: " . filesize($filename));
                        header("Content-Transfer-Encoding: binary");
                        header("Content-Type: binary/octet-stream");
                        readfile($filename);
                    } else {
                        throw new Exception("File not found [" . $filename . "]");
                    }
                } else {
                    throw new Exception("No data found");
                }
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public function descargarArchivoOeaAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
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
                $folder = realpath(APPLICATION_PATH . "/../public/oea/");
                $mapper = new Rrhh_Model_OeaArchivos();
                $arr = $mapper->obtener($i->id);
                if (($d = $this->_buscarParentOea($arr["carpeta"]))) {
                    $folder .= $d . DIRECTORY_SEPARATOR . $arr["carpeta"];
                }
                if (!isset($d) && $arr["carpeta"] !== "") {
                    $folder .= DIRECTORY_SEPARATOR . $arr["carpeta"];
                }
                if (isset($arr)) {
                    $filename = $folder . DIRECTORY_SEPARATOR . $arr["archivo"];
                    if (file_exists($filename)) {
                        header("Cache-Control: public");
                        header("Content-Description: File Transfer");
                        header("Content-Disposition: attachment; filename=\"" . $this->_convert($arr["nombreArchivo"]) . "\"");
                        header("Content-length: " . filesize($filename));
                        header("Content-Transfer-Encoding: binary");
                        header("Content-Type: binary/octet-stream");
                        readfile($filename);
                    } else {
                        throw new Exception("File not found [" . $filename . "]");
                    }
                } else {
                    throw new Exception("No data found");
                }
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    protected function _convert($value) {
        return mb_convert_encoding(preg_replace(array("/\s+/"), "_", utf8_decode($value)), "UTF-8", "ISO-8859-1");
    }

    protected function _buscarParentOea($directorio) {
        $rel = new Rrhh_Model_OeaRelCarpetas();
        $p = $rel->obtenerParent($directorio);
        if ($p["previo"]) {
            return $this->_buscarParent($p["previo"]) . DIRECTORY_SEPARATOR . $p["previo"];
        }
        return;
    }

    protected function _buscarParent($directorio) {
        $rel = new Rrhh_Model_IsoRelCarpetas();
        $p = $rel->obtenerParent($directorio);
        if ($p["previo"]) {
            return $this->_buscarParent($p["previo"]) . DIRECTORY_SEPARATOR . $p["previo"];
        }
        return;
    }

    protected function _buscarParentArray($directorio) {
        $rel = new Rrhh_Model_IsoRelCarpetas();
        $p = $rel->obtenerParentArray($directorio);
        if ($p["previo"]) {
            return array_merge_recursive($this->_buscarParentArray($p["previo"]), array("directorio" => $directorio, "nombreCarpeta" => $p["nombreCarpeta"]));
        }
        $folders = new Rrhh_Model_IsoCarpetas();
        $p = $folders->obtener($directorio);
        return array("directorio" => $p["carpeta"], "nombreCarpeta" => $p["nombreCarpeta"]);
    }
    
    public function misActividadesAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
            );
            $v = array(
                "fecha" => array(new Zend_Validate_Regex("/^\d{4}\-\d{2}\-\d{2}$/")),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("fecha")) {
                $mppr = new Principal_Model_UsuariosActividades();
                $arr = $mppr->obtenerPorFecha($this->_session->id, $input->fecha);
                if (!empty($arr)) {
                    $html = '';
                    foreach ($arr as $item) {
                        $html .= '<tr style="border: 1px #f3f3f3 solid" class="activityRow_' . $item["id"] . '">'
                                . '<td style="padding: 3px; cursor: pointer" class="activityRow" data-id="' . $item["id"] . '">' . mb_strtoupper($item["titulo"]) . '</td>'
                                . '<td style="width: 20px; text-align: right"><img data-id="' . $item["id"] . '" src="/images/icons/small_delete.png" class="deleteActivity" style="cursor: pointer"/></td>'
                                . '</tr>';
                    }
                    $this->_helper->json(array("success" => true, "html" => $html));
                } else {
                    $this->_helper->json(array("success" => true, "html" => '<tr style="border: 1px #f3f3f3 solid"><td colspan="2" style="padding: 3px"><em>No hay actividades.</em></td></tr>'));
                }
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => true, "html" => $ex->getMessage()));
        }
    }
    
    public function formularioDepartamentoAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        try {
            $view = new Zend_View();
            $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/get/");
            $view->setHelperPath(realpath(dirname(__FILE__)) . "/../views/helpers/");
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "idDepto" => "Digits",
            );
            $v = array(
                "idDepto" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());            
            if ($input->isValid("idDepto")) {
                $emp = new Rrhh_Model_Empleados();
                $usr = $emp->obtenerPorUsuario($this->_session->id);
                if (isset($usr["idEmpresa"])) {
                    $mpp = new Rrhh_Model_EmpresaDepartamentos();
                    $dpts = $mpp->obtener($usr["idEmpresa"]);
                    $view->empresas = $dpts;
                    $cust = new Trafico_Model_ClientesMapper();
                    $cts = $cust->obtenerPorEmpresa($usr["idEmpresa"]);
                    $view->clientes = $cts;
                    $mppa = new Rrhh_Model_EmpresaDeptoActividades();
                    $acts = $mppa->obtener($usr["idPuesto"]);
                    $view->misActividades = $acts;
                }
                $view->idDepto = $input->idDepto;
                $this->_helper->json(array("success" => true, "html" => $view->render("formulario-departamento.phtml")));
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => true, "html" => $ex->getMessage()));
        }
    }
    
    public function actividadDetalleAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
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
                $mppr = new Principal_Model_UsuariosActividades();
                $arr = $mppr->obtener($input->id);
                if (!empty($arr)) {
                    $array = array(
                        "success" => true,
                        "titulo" => mb_strtoupper($arr["titulo"]),
                        "idDepto" => $arr["idDepto"]
                    );
                    if ($arr["idDepto"] != null) {
                        $view = new Zend_View();
                        $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/get/");
                        $emp = new Rrhh_Model_Empleados();
                        $usr = $emp->obtenerPorUsuario($arr["idUsuario"]);
                        if (isset($usr["idEmpresa"])) {
                            $mpp = new Rrhh_Model_EmpresaDepartamentos();
                            $dpts = $mpp->obtener($usr["idEmpresa"]);
                            $view->empresas = $dpts;
                            $cust = new Trafico_Model_ClientesMapper();
                            $cts = $cust->obtenerPorEmpresa($usr["idEmpresa"]);
                            $view->clientes = $cts;
                            $mppa = new Rrhh_Model_EmpresaDeptoActividades();
                            $acts = $mppa->obtener($usr["idPuesto"]);
                            $view->misActividades = $acts;
                        }
                        $view->idDepto = $arr["idDepto"];
                        if(isset($arr["idActividad"])) {
                            $array["tipoActividad"] = $arr["idActividad"];
                        }
                        $arra = array(
                            "idCliente",
                            "totalTickets",
                            "totalEnvios",
                            "saldoFinal",
                            "expedientesFacturados",
                            "expedientesArchivados",
                            "facturasCanceladas",
                            "pedimentosModulados",
                            "pedimentosPagados",
                            "cantidadVerdes",
                            "cantidadRojos",
                            "quejas",
                            "consultas",
                            "visitas",
                            "llamadas",
                            "documentos",
                            "multas",
                            "duracion",
                            "observaciones",
                        );
                        foreach($arra as $k => $v) {
                            if(isset($arr[$v])) {
                                $array[$v] = $arr[$v];
                            }
                        }
                        $array["html"] = $view->render("formulario-departamento.phtml");
                    }
                    $this->_helper->json($array);
                } else {
                    $this->_helper->json(array("success" => false));                    
                }
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => true, "html" => $ex->getMessage()));
        }
    }
    
    public function liveStatsCustomsAction() {
        try {
            $mppr = new Application_Model_Estadisticas();
            $fechaIni = date("Y-m-d") . " 07:00:01";
            $fechaFin = date("Y-m-d") . " 20:59:59";
            $stats = array(
                0 => $mppr->liberadosPorHoraPorAduana($fechaIni, $fechaFin, 1),
                1 => $mppr->liberadosPorHoraPorAduana($fechaIni, $fechaFin, 2),
                2 => $mppr->liberadosPorHoraPorAduana($fechaIni, $fechaFin, 7),
            );
            $this->_helper->json(array("success" => true, "stats" => $stats));
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }
    
    public function liveStatsAction() {
        try {
            $mppr = new Application_Model_Estadisticas();
            $fechaIni = date("Y-m-d") . " 07:00:01";
            $fechaFin = date("Y-m-d") . " 20:59:59";
            $stats = array(
                0 => $mppr->covesPorHora($fechaIni, $fechaFin),
                1 => $mppr->edocsPorHora($fechaIni, $fechaFin),
                2 => $mppr->expedientesPorHora($fechaIni, $fechaFin),
                3 => $mppr->pagadosPorHora($fechaIni, $fechaFin),
                4 => $mppr->liberadosPorHora($fechaIni, $fechaFin),
                5 => $mppr->transmitidosPorHora($fechaIni, $fechaFin),
                6 => $mppr->firmasPorHora($fechaIni, $fechaFin),
                7 => $mppr->pagosPorHora($fechaIni, $fechaFin),
            );
            $this->_helper->json(array("success" => true, "stats" => $stats));
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }
    
    public function estadisticasAction() {
        try {
            $mppr = new Application_Model_Estadisticas();
            $stats = array(
                array("key" => "coves", "value" => $mppr->coves()), "color" => "#0177c1",
                array("key" => "edocs", "value" => $mppr->edocs(), "color" => "#0587d8"),
                array("key" => "expedientes", "value" => $mppr->expedientes(), "color" => "#0ecad1"),
                array("key" => "pagados", "value" => $mppr->pagados(), "color" => "#45abcd"),
                array("key" => "liberados", "value" => $mppr->liberados(), "color" => "#75cd45"),
                array("key" => "transmitidos", "value" => $mppr->transmitidos(), "color" => "#feaf20"),
                array("key" => "firmas", "value" => $mppr->firmas(), "color" => "#e39a17"),
                array("key" => "pagos", "value" => $mppr->pagos(), "color" => "#c78918"),
                array("key" => "ftp", "value" => $mppr->ftp(), "color" => "#45abcd"),
                array("key" => "terminal", "value" => $mppr->terminal(), "color" => "#45abcd"),
                array("key" => "facturacion", "value" => $mppr->facturacion(), "color" => "#75cd45"),
            );
            $this->_helper->json(array("success" => true, "stats" => $stats));
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }
    
    public function misDocumentosAction() {
        try {
            $view = new Zend_View();
            $view->setScriptPath(realpath(dirname(__FILE__)) . '/../views/scripts/get/');
            $view->setHelperPath(realpath(dirname(__FILE__)) . '/../views/helpers/');
            $mppr = new Rrhh_Model_Empleados();
            $arr = $mppr->obtenerPorUsuario($this->_session->id);
            if (!empty($arr)) {
                $repo = new Rrhh_Model_RepositorioEmpleados();
                $archivos = $repo->archivosEmpleado($arr['id']);
                $view->archivos = $archivos;
            }
            $this->_helper->json(array('success' => true, 'html' => $view->render('mis-documentos.phtml')));
        } catch (Exception $ex) {
            $this->_helper->json(array('success' => false, 'message' => $ex->getMessage()));
        }
    }

}
