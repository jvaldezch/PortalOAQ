<?php

class Usuarios_GetController extends Zend_Controller_Action
{

    protected $_session;
    protected $_config;
    protected $_appconfig;

    public function init()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_appconfig = new Application_Model_ConfigMapper();
        $this->_redirector = $this->_helper->getHelper("Redirector");
        $this->_config = new Zend_Config_Ini(APPLICATION_PATH . "/configs/application.ini", APPLICATION_ENV);
        $this->_logger = Zend_Registry::get("logDb");
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

    public function obtenerFirmaAction()
    {
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "idFiel" => "Digits",
            );
            $v = array(
                "idFiel" => array("NotEmpty", new Zend_Validate_Int()),
                "cadena" => "NotEmpty",
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("idFiel") && $input->isValid("cadena")) {
                $mppr = new Vucem_Model_VucemFirmanteMapper();
                $rfc = $mppr->obtenerDetalleFirmanteId($input->idFiel);
                $pkeyid = openssl_get_privatekey(base64_decode($rfc["spem"]), $rfc["spem_pswd"]);
                if (isset($rfc["sha"]) && $rfc["sha"] == "sha256") {
                    openssl_sign($input->cadena, $signature, $pkeyid, OPENSSL_ALGO_SHA256);
                } else {
                    openssl_sign($input->cadena, $signature, $pkeyid);
                }
                openssl_free_key($pkeyid);
                $this->_helper->json(array("success" => true, "firma" => base64_encode($signature)));
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    /**
     * /usuarios/get/descarga-edocument?idFiel=121&edocument=04361706CXZ36
     * @throws Exception
     */
    public function descargaEdocumentAction()
    {
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "idFiel" => "Digits",
            );
            $v = array(
                "idFiel" => array("NotEmpty", new Zend_Validate_Int()),
                "edocument" => "NotEmpty",
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("idFiel") && $input->isValid("edocument")) {
                $mapper = new Vucem_Model_VucemFirmanteMapper();
                $sello = $mapper->obtenerDetalleFirmanteId($input->idFiel);
                $xml = new OAQ_Xml(false, false, true);
                $xml->documentoDigitalizado($sello["rfc"], $sello["ws_pswd"], $input->edocument);
                $xmlh = trim(preg_replace("/<\\?xml.*\\?>/", '', $xml->getXml(), 1));

                Zend_Debug::dump($xml->getXml());
                /*$servicios = new OAQ_Servicios();
                $servicios->setXml($xmlh);
                $servicios->descargaEdocument();
                $resp = $servicios->getResponse();*/

                /*$dom = new DOMDocument;
                $dom->preserveWhiteSpace = FALSE;
                $dom->loadXML($resp);
                $dom->formatOutput = TRUE;
                $response = trim(preg_replace("/<\\?xml.*\\?>/",'', $dom->saveXml(),1));
                Zend_Debug::dump($response);
                
                if (!empty($resp)) {
                    file_put_contents($this->_appconfig->getParam("tmpDir") . DIRECTORY_SEPARATOR . "EDOC_DIG_" . $input->edocument . ".xml", $response);
                }*/

                $ejem = new OAQ_RespuestasEjemplos();
                Zend_Debug::dump($ejem->ejemploRespuesta(37));

                $respuestas = new OAQ_VucemRespuestas();
                $r = $respuestas->analizarRespuestaEdocument($ejem->ejemploRespuesta(37));
                Zend_Debug::dump($r);
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function obtenerDocumentosAction()
    {
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "idUsuario" => "Digits",
            );
            $v = array(
                "idUsuario" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("idUsuario")) {
                $mppr = new Usuarios_Model_UsuariosDocumentos();
                $arr = $mppr->obtener($input->idUsuario);
                if (!empty($arr)) {
                    $this->_helper->json(array("success" => true, "ids" => json_decode($arr["documentos"])));
                }
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function actividadesUsuariosAction()
    {
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
                $arr = $mppr->obtenerTodasPorFecha($input->fecha);
                if (!empty($arr)) {
                    $html = '';
                    foreach ($arr as $item) {
                        $html .= '<tr style="border: 1px #f3f3f3 solid" class="activityRow_' . $item["id"] . '">'
                            . '<td>' . $item["nombreUsuario"] . '</td>'
                            . '<td style="padding: 3px; cursor: pointer" class="activityRow" data-id="' . $item["id"] . '"><strong>' . mb_strtoupper($item["titulo"]) . '</strong><br>' . $item["observaciones"] . '</td>'
                            . '<td>' . date('Y-m-d', strtotime($item["fecha"])) . '</td>'
                            . '<td>' . $item["creado"] . '</td>'
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

    public function agregarEquipoAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        try {
            $f = array(
                "*" => array(new Zend_Filter_StringTrim(), new Zend_Filter_StripTags()),
                "idUsuario" => array(new Zend_Filter_Digits())
            );
            $v = array(
                "idUsuario" => array(new Zend_Validate_Int(), new Zend_Validate_NotEmpty()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("idUsuario")) {
                $view = new Zend_View();
                $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/get/");
                $view->idUsuario = $input->idUsuario;
                $this->_helper->json(array("success" => true, "html" => $view->render("agregar-equipo.phtml")));
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => true, "html" => $ex->getMessage()));
        }
    }

    public function editarEquipoAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        try {
            $f = array(
                "*" => array(new Zend_Filter_StringTrim(), new Zend_Filter_StripTags()),
                "id" => array(new Zend_Filter_Digits())
            );
            $v = array(
                "id" => array(new Zend_Validate_Int(), new Zend_Validate_NotEmpty()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("id")) {
                $view = new Zend_View();
                $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/get/");
                $view->id = $input->id;

                $mppr = new Usuarios_Model_UsuarioEquipos();
                $row = $mppr->obtenerEquipo($input->id);

                $this->_helper->json(array("success" => true, "html" => $view->render("editar-equipo.phtml")));
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => true, "html" => $ex->getMessage()));
        }
    }

    public function imprimirComprobanteAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        try {
            $f = array(
                "*" => array(new Zend_Filter_StringTrim(), new Zend_Filter_StripTags()),
                "id" => array(new Zend_Filter_Digits())
            );
            $v = array(
                "id" => array(new Zend_Validate_Int(), new Zend_Validate_NotEmpty()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("id")) {

                $arr = array(
                    "actualizacion" => "10/04/2013",
                    "title_logo" => "pdf_logo.jpg",
                    "autor" => "Jaime E. Valdez",
                    "version" => "1.0",
                    "filename" => "COMPROBANTE_.pdf",
                );                

                $print = new OAQ_Imprimir_EntregaEquipo($arr, "P", "pt", "LETTER");
                $print->set_filename($arr['filename']);
                $print->Create();
                $print->Output($arr['filename'], "I");

            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => true, "html" => $ex->getMessage()));
        }
    }
}
