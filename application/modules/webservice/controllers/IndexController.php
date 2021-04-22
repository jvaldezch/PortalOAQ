<?php

class Webservice_IndexController extends Zend_Controller_Action
{
    protected $_wsdl;
    protected $_wsdlped;
    protected $_wsdldata;
    protected $_salt = "dss78454";
    protected $_pepper = "oaq2013*";
    protected $_baseUrl = "https://oaq.dnsalias.net";
    protected $_config;

    public function init()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini', APPLICATION_ENV);
        $this->_wsdl = $this->_config->app->endpoint;
        $this->_wsdlped = $this->_config->app->wsdlped;
        $this->_wsdldata = $this->_config->app->wsdldata;
    }

    public function indexAction()
    {
        echo "This is the OAQ Web Service";
    }

    public function authenticateAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        try {
            if (isset($_GET["wsdl"])) {
                $this->authenticateWSDL();
            } else {
                $this->authenticateSOAP();
            }
        } catch (Exception $e) {
            throw new Zend_Exception($e->getMessage());
        }
    }

    private function authenticateWSDL()
    {
        $autodiscover = new Zend_Soap_AutoDiscover();
        $autodiscover->setClass("OAQ_Auth");
        $autodiscover->handle();
    }

    private function authenticateSOAP()
    {
        $soap = new Zend_Soap_Server($this->_wsdl);
        $soap->setClass("OAQ_Auth");
        $soap->handle();
    }

    public function pedimentosAduanaAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        try {
            if (isset($_GET['wsdl'])) {
                $this->pedimentosAduanaWSDL();
            } else {
                $this->pedimentosAduanaSOAP();
            }
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    private function pedimentosAduanaWSDL()
    {
        $autodiscover = new Zend_Soap_AutoDiscover();
        $autodiscover->setClass('OAQ_Pedimentos');
        $autodiscover->handle();
    }

    private function pedimentosAduanaSOAP()
    {
        $soap = new Zend_Soap_Server($this->_wsdlped);
        $soap->setClass('OAQ_Pedimentos');
        $soap->handle();
    }

    public function pedimentosAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $domtree = new DOMDocument('1.0', 'UTF-8');
        $xmlRoot = $domtree->appendChild($domtree->createElement("pedimentos"));
        $regex = "/^\d{4}\-\d{2}\-\d{2}$/";
        $rfc = filter_var($this->_getParam('rfc', null), FILTER_SANITIZE_STRING);
        $token = filter_var($this->_getParam('token', null), FILTER_SANITIZE_STRING);
        $fechaIni = filter_var($this->_getParam('fecha_ini', null), FILTER_VALIDATE_REGEXP, array("options" => array("regexp" => $regex)));
        $fechaFin = filter_var($this->_getParam('fecha_fin', null), FILTER_VALIDATE_REGEXP, array("options" => array("regexp" => $regex)));
        $aduana = filter_var($this->_getParam('aduana', null), FILTER_SANITIZE_NUMBER_INT);
        $tipo = filter_var($this->_getParam('tipo', null), FILTER_SANITIZE_STRING);

        if ($token && $token == sha1($this->_salt . $rfc . $this->_pepper)) {
            $arrErrors = array();
            if (!$rfc) {
                $arrErrors[] = array(
                    'param' => 'rfc',
                    'error' => 'No se espefifico el RFC.',
                );
                $errorFlag = true;
            } else if ($rfc && strlen($rfc) < 12) {
                $arrErrors[] = array(
                    'param' => 'rfc',
                    'error' => 'No cuenta con la longitud minima para ser un RFC.',
                );
                $errorFlag = true;
            } else if ($rfc && strlen($rfc) == 12) {
                $anexo24 = new OAQ_Anexo24($aduana);
                if (!$anexo24->validateCustomer($rfc, $aduana)) {
                    $arrErrors[] = array(
                        'param' => 'rfc',
                        'error' => 'No se encuentra el RFC en la base de datos.',
                    );
                    $errorFlag = true;
                }
            }

            if (!$aduana) {
                $arrErrors[] = array(
                    'param' => 'aduana',
                    'error' => 'Debe especificar una aduana.',
                );
                $errorFlag = true;
            } else {
                if (!isset($anexo24)) {
                    $anexo24 = new OAQ_Anexo24($aduana);
                }
                if (!$anexo24->valid) {
                    $arrErrors[] = array(
                        'param' => 'aduana',
                        'error' => "Aduana {$aduana} no disponible por el momento.",
                    );
                    $errorFlag = true;
                }
            }
            if (!$token) {
                $arrErrors[] = array(
                    'param' => 'token',
                    'error' => 'No se especifico el token.',
                );
                $errorFlag = true;
            }
            if (!$fechaIni) {
                $arrErrors[] = array(
                    'param' => 'fecha_ini',
                    'error' => 'No se especifico fecha de inicio o el formato no es el correcto (Ej. yyyy-mm-dd).',
                );
                $errorFlag = true;
            } else if ($fechaIni) {
                if (!$this->isValidMysqlDate($fechaIni)) {
                    $arrErrors[] = array(
                        'param' => 'fecha_ini',
                        'error' => 'Fecha de inicio no es válida.',
                    );
                    $errorFlag = true;
                }
            }
            if (!$fechaFin) {
                $arrErrors[] = array(
                    'param' => 'fecha_fin',
                    'error' => 'No se especifico fecha de fin o el formato no es el correcto (Ej. yyyy-mm-dd).',
                );
                $errorFlag = true;
            } else if ($fechaFin) {
                if (!$this->isValidMysqlDate($fechaFin)) {
                    $arrErrors[] = array(
                        'param' => 'fecha_fin',
                        'error' => 'Fecha de fin no es válida.',
                    );
                    $errorFlag = true;
                }
            }
            if ($this->isValidMysqlDate($fechaFin) && $this->isValidMysqlDate($fechaIni)) {
                if (strtotime($fechaIni) > strtotime($fechaFin)) {
                    $arrErrors[] = array(
                        'param' => 'fechas',
                        'error' => 'La fecha de inicio del reporte no puede ser mayor que la fecha fin.',
                    );
                    $errorFlag = true;
                }
                $datediff = strtotime($fechaFin) - strtotime($fechaIni);
                if (floor($datediff / (60 * 60 * 24)) > 31) {
                    $arrErrors[] = array(
                        'param' => 'fechas',
                        'error' => 'El periodo de consulta no puede ser mayor a 31 días.',
                    );
                    $errorFlag = true;
                }
                if (preg_match('/2012/', $fechaFin) || preg_match('/2012/', $fechaIni)) {
                    $arrErrors[] = array(
                        'param' => 'fechas',
                        'error' => 'El periodo de consulta solo debe ser del año 2013 en adelante.',
                    );
                    $errorFlag = true;
                }
            }
            if (!$tipo) {
                $arrErrors[] = array(
                    'param' => 'tipo',
                    'error' => 'Tipo de operación no especificado, dede ser I o E para importación o exportación respectivamente.',
                );
                $errorFlag = true;
            } else if ($tipo && strlen($tipo) != 1) {
                $arrErrors[] = array(
                    'param' => 'tipo',
                    'error' => 'El tipo de operación no es válida.',
                );
                $errorFlag = true;
            } else if ($tipo && strlen($tipo) == 1) {
                if (!preg_match('/E|I/i', $tipo)) {
                    $arrErrors[] = array(
                        'param' => 'tipo',
                        'error' => 'El tipo de operación no es válida.',
                    );
                    $errorFlag = true;
                }
            }
        } else {
            $arrErrors[] = array(
                'param' => 'token',
                'error' => 'Token no válido',
            );
            $errorFlag = true;
        }


        if (isset($errorFlag)) {
            $errors = $domtree->createElement("errores");
            $addErrorsChild = $xmlRoot->appendChild($errors);
            foreach ($arrErrors as $item) {
                $addErrorsChild->appendChild($domtree->createElement($item['param'], $item['error']));
            }
        } else {
            $pedimentos = $anexo24->getDataByPeriod($rfc, $fechaIni, $fechaFin, $tipo, $aduana);

            if (count($pedimentos) != 0) {
                unset($xmlRoot);
                unset($domtree);
                $domtree = new DOMDocument('1.0', 'UTF-8');
                $root = $domtree->createElement("pedimentos");
                $root->setAttribute('Cantidad', count($pedimentos));
                $xmlRoot = $domtree->appendChild($root);

                unset($item);
                foreach ($pedimentos as $item) {
                    $pedimento = $domtree->createElement("pedimento");
                    $pedimento->setAttribute("referencia", $item["Referencia"]);
                    $pedimento->setAttribute("pedimento", $item["Pedimento"]);
                    $addData = $xmlRoot->appendChild($pedimento);
                    $addData->appendChild($domtree->createElement("Operacion", $item["Operacion"]));
                    $addData->appendChild($domtree->createElement("FechaPago", $item["FechaPago"]));
                    $addData->appendChild($domtree->createElement("Aduana", $item["Aduana"]));
                    $addData->appendChild($domtree->createElement("Patente", $item["Patente"]));
                    $addData->appendChild($domtree->createElement("Pedimento", $item["Pedimento"]));
                    $addData->appendChild($domtree->createElement("TipoOpe", $item["TipoOpe"]));
                    $addData->appendChild($domtree->createElement("TipoCambio", $item["TipoCambio"]));
                    $addData->appendChild($domtree->createElement("IVA", $item["IVA"]));
                    $addData->appendChild($domtree->createElement("Clave", $item["Clave"]));
                    $addData->appendChild($domtree->createElement("Regimen", $item["Regimen"]));
                    $addData->appendChild($domtree->createElement("Fletes", $item["Fletes"]));
                    $addData->appendChild($domtree->createElement("Seguros", $item["Seguros"]));
                    $addData->appendChild($domtree->createElement("Embalajes", $item["Embalajes"]));
                    $addData->appendChild($domtree->createElement("Otros", $item["Otros"]));
                    $addData->appendChild($domtree->createElement("DTA", $item["DTA"]));
                    $valCom = ($item["ValorComercial"]) ? $item["ValorComercial"] : 0;
                    $addData->appendChild($domtree->createElement("ValorComercial", $valCom));
                    $valAdu = ($item["ValorAduana"]) ? $item["ValorAduana"] : 0;
                    $addData->appendChild($domtree->createElement("ValorAduana", $valAdu));
                    $obs = utf8_encode($item["Observaciones"]);
                    $addData->appendChild($domtree->createElement("Observaciones", $obs));
                    $addData->appendChild($domtree->createElement("Virtual", $item["Virtual"]));
                    $addData->appendChild($domtree->createElement("NotaInterna", $item["NotaInterna"]));
                    $addData->appendChild($domtree->createElement("Prevalidacion", $item["Prevalidacion"]));
                    $addData->appendChild($domtree->createElement("NomProveedor", utf8_encode($item["NomProveedor"])));
                    $addData->appendChild($domtree->createElement("Factura", $item["Factura"]));
                    $addData->appendChild($domtree->createElement("FechaFactura", $item["FechaFactura"]));
                    $addData->appendChild($domtree->createElement("FMoneda", $item["FMoneda"]));
                    $addData->appendChild($domtree->createElement("NumeroDeParte", $item["NumeroDeParte"]));
                    $fracc = substr($item["FraccionImportacion"], 0, 4) . "." . substr($item["FraccionImportacion"], 4, 2) . "." . substr($item["FraccionImportacion"], 6, 2);
                    $addData->appendChild($domtree->createElement("FraccionImportacion", $fracc));
                    $addData->appendChild($domtree->createElement("Tasa", $item["Tasa"]));
                    $addData->appendChild($domtree->createElement("TipoTasa", $item["TipoTasa"]));
                    $addData->appendChild($domtree->createElement("Unidad", $item["Unidad"]));
                    $addData->appendChild($domtree->createElement("Precio", $item["Precio"]));
                    $addData->appendChild($domtree->createElement("Cantidad", $item["Cantidad"]));
                    $addData->appendChild($domtree->createElement("Origen", $item["Origen"]));
                    $addData->appendChild($domtree->createElement("Vendedor", $item["Vendedor"]));
                    $addData->appendChild($domtree->createElement("Precio", $item["Precio"]));
                    $addData->appendChild($domtree->createElement("FormaPago", $item["FormaPago"]));
                    $addData->appendChild($domtree->createElement("Incoterm", $item["Incoterm"]));
                    $addData->appendChild($domtree->createElement("PagaTLC", $item["PagaTLC"]));
                    $addData->appendChild($domtree->createElement("PagaTLCUEM", $item["PagaTLCUEM"]));
                    $addData->appendChild($domtree->createElement("PagaTLCAELC", $item["PagaTLCAELC"]));
                    $addData->appendChild($domtree->createElement("JustTLC", $item["JustTLC"]));
                    $addData->appendChild($domtree->createElement("JustTLCUEM", $item["JustTLCUEM"]));
                    $addData->appendChild($domtree->createElement("JustTLCAELC", $item["JustTLCAELC"]));
                    $addData->appendChild($domtree->createElement("EB", $item["EB"]));
                    $addData->appendChild($domtree->createElement("MontoEB", $item["MontoEB"]));
                    $addData->appendChild($domtree->createElement("EnConsignacion", $item["EnConsignacion"]));
                    $addData->appendChild($domtree->createElement("NotaInterna2", $item["NotaInterna2"]));
                    $addData->appendChild($domtree->createElement("Revision", $item["Revision"]));
                    $addData->appendChild($domtree->createElement("Cove", $item["Cove"]));
                    //}
                }
            } else {
                $errors = $domtree->createElement("errores");
                $addErrorsChild = $xmlRoot->appendChild($errors);
                $addErrorsChild->appendChild($domtree->createElement('nulo', 'No existen pedimentos del tipo o periodo especificado.'));
            }
        }

        $output = $domtree->saveXML();
        Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer')->setNoRender(true);
        Zend_Layout::getMvcInstance()->disableLayout();
        $this->_response->setHeader('Content-Type', 'text/xml; charset=utf-8')
            ->setBody($output);
    }

    public function listadoPedimentosAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $domtree = new DOMDocument('1.0', 'UTF-8');
        $xmlRoot = $domtree->appendChild($domtree->createElement("pedimentos"));

        $regex = "/^\d{4}\-\d{2}\-\d{2}$/";
        $rfc = filter_var($this->_getParam('rfc', null), FILTER_SANITIZE_STRING);
        $token = filter_var($this->_getParam('token', null), FILTER_SANITIZE_STRING);
        $fechaIni = filter_var($this->_getParam('fecha_ini', null), FILTER_VALIDATE_REGEXP, array("options" => array("regexp" => $regex)));
        $fechaFin = filter_var($this->_getParam('fecha_fin', null), FILTER_VALIDATE_REGEXP, array("options" => array("regexp" => $regex)));
        $aduana = filter_var($this->_getParam('aduana', null), FILTER_SANITIZE_NUMBER_INT);
        $tipo = filter_var($this->_getParam('tipo', null), FILTER_SANITIZE_STRING);

        if ($token && $token == sha1($this->_salt . $rfc . $this->_pepper)) {
            $arrErrors = array();
            if (!$rfc) {
                $arrErrors[] = array(
                    'param' => 'rfc',
                    'error' => 'No se espefifico el RFC.',
                );
                $errorFlag = true;
            } else if ($rfc && strlen($rfc) < 12) {
                $arrErrors[] = array(
                    'param' => 'rfc',
                    'error' => 'No cuenta con la longitud minima para ser un RFC.',
                );
                $errorFlag = true;
            } else if ($rfc && strlen($rfc) == 12) {
                $anexo24 = new OAQ_Anexo24($aduana);
                if (!$anexo24->validateCustomer($rfc, $aduana)) {
                    $arrErrors[] = array(
                        'param' => 'rfc',
                        'error' => 'No se encuentra el RFC en la base de datos.',
                    );
                    $errorFlag = true;
                }
            }

            if (!$aduana) {
                $arrErrors[] = array(
                    'param' => 'aduana',
                    'error' => 'Debe especificar una aduana.',
                );
                $errorFlag = true;
            } else {
                if (!isset($anexo24))
                    $anexo24 = new OAQ_Anexo24($aduana);
                if (!$anexo24->valid) {
                    $arrErrors[] = array(
                        'param' => 'aduana',
                        'error' => "Aduana {$aduana} no disponible por el momento.",
                    );
                    $errorFlag = true;
                }
            }
            if (!$token) {
                $arrErrors[] = array(
                    'param' => 'token',
                    'error' => 'No se especifico el token.',
                );
                $errorFlag = true;
            }
            if (!$fechaIni) {
                $arrErrors[] = array(
                    'param' => 'fecha_ini',
                    'error' => 'No se especifico fecha de inicio o el formato no es el correcto (Ej. yyyy-mm-dd).',
                );
                $errorFlag = true;
            } else if ($fechaIni) {
                if (!$this->isValidMysqlDate($fechaIni)) {
                    $arrErrors[] = array(
                        'param' => 'fecha_ini',
                        'error' => 'Fecha de inicio no es válida.',
                    );
                    $errorFlag = true;
                }
            }
            if (!$fechaFin) {
                $arrErrors[] = array(
                    'param' => 'fecha_fin',
                    'error' => 'No se especifico fecha de fin o el formato no es el correcto (Ej. yyyy-mm-dd).',
                );
                $errorFlag = true;
            } else if ($fechaFin) {
                if (!$this->isValidMysqlDate($fechaFin)) {
                    $arrErrors[] = array(
                        'param' => 'fecha_fin',
                        'error' => 'Fecha de fin no es válida.',
                    );
                    $errorFlag = true;
                }
            }
            if ($this->isValidMysqlDate($fechaFin) && $this->isValidMysqlDate($fechaIni)) {
                if (strtotime($fechaIni) > strtotime($fechaFin)) {
                    $arrErrors[] = array(
                        'param' => 'fechas',
                        'error' => 'La fecha de inicio del reporte no puede ser mayor que la fecha fin.',
                    );
                    $errorFlag = true;
                }
                $datediff = strtotime($fechaFin) - strtotime($fechaIni);
                if (floor($datediff / (60 * 60 * 24)) > 31) {
                    $arrErrors[] = array(
                        'param' => 'fechas',
                        'error' => 'El periodo de consulta no puede ser mayor a 31 días.',
                    );
                    $errorFlag = true;
                }
                if (preg_match('/2012/', $fechaFin) || preg_match('/2012/', $fechaIni)) {
                    $arrErrors[] = array(
                        'param' => 'fechas',
                        'error' => 'El periodo de consulta solo debe ser del año 2013 en adelante.',
                    );
                    $errorFlag = true;
                }
            }
            if (!$tipo) {
                $arrErrors[] = array(
                    'param' => 'tipo',
                    'error' => 'Tipo de operación no especificado, dede ser I o E para importación o exportación respectivamente.',
                );
                $errorFlag = true;
            } else if ($tipo && strlen($tipo) != 1) {
                $arrErrors[] = array(
                    'param' => 'tipo',
                    'error' => 'El tipo de operación no es válida.',
                );
                $errorFlag = true;
            } else if ($tipo && strlen($tipo) == 1) {
                if (!preg_match('/E|I/i', $tipo)) {
                    $arrErrors[] = array(
                        'param' => 'tipo',
                        'error' => 'El tipo de operación no es válida.',
                    );
                    $errorFlag = true;
                }
            }
        } else {
            $arrErrors[] = array(
                'param' => 'token',
                'error' => 'Token no válido',
            );
            $errorFlag = true;
        }


        if (isset($errorFlag)) {
            $errors = $domtree->createElement("errores");
            $addErrorsChild = $xmlRoot->appendChild($errors);
            foreach ($arrErrors as $item) {
                $addErrorsChild->appendChild($domtree->createElement($item['param'], $item['error']));
            }
        } else {
            $pedimentos = $anexo24->getListByPeriod($rfc, $fechaIni, $aduana);
            if (count($pedimentos) != 0) {
                unset($xmlRoot);
                unset($domtree);
                $domtree = new DOMDocument('1.0', 'UTF-8');
                $root = $domtree->createElement("pedimentos");
                $root->setAttribute('Cantidad', count($pedimentos));
                $xmlRoot = $domtree->appendChild($root);
                unset($item);
                foreach ($pedimentos as $k => $item) {
                    $pedimento = $domtree->createElement("pedimento");
                    $addData = $xmlRoot->appendChild($pedimento);
                    $addData->appendChild($domtree->createElement("Operacion", $item["Operacion"]));
                    $addData->appendChild($domtree->createElement("FechaPago", $item["FechaPago"]));
                    $addData->appendChild($domtree->createElement("Aduana", $item["Aduana"]));
                    $addData->appendChild($domtree->createElement("Patente", $item["Patente"]));
                    $addData->appendChild($domtree->createElement("Pedimento", $item["Pedimento"]));
                    $addData->appendChild($domtree->createElement("TipoOpe", $item["TipoOpe"]));
                    $addData->appendChild($domtree->createElement("FirmaValidacion", $item["FirmaValidacion"]));
                    $addData->appendChild($domtree->createElement("FirmaBanco", $item["FirmaBanco"]));
                }
            } else {
                $errors = $domtree->createElement("errores");
                $addErrorsChild = $xmlRoot->appendChild($errors);
                $addErrorsChild->appendChild($domtree->ceateElement('nulo', 'No existen pedimentos del tipo o periodo especificado.'));
            }
        }
        $output = $domtree->saveXML();
        Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer')->setNoRender(true);
        Zend_Layout::getMvcInstance()->disableLayout();
        $this->_response->setHeader('Content-Type', 'text/xml; charset=utf-8')
            ->setBody($output);
    }

    public function prasadAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $domtree = new DOMDocument('1.0', 'UTF-8');
        $xmlRoot = $domtree->appendChild($domtree->createElement("pedimentos"));

        $regex = "/^\d{4}\-\d{2}\-\d{2}$/";
        $rfc = filter_var($this->_getParam('rfc', null), FILTER_SANITIZE_STRING);
        $token = filter_var($this->_getParam('token', null), FILTER_SANITIZE_STRING);
        $fechaIni = filter_var($this->_getParam('fecha_ini', null), FILTER_VALIDATE_REGEXP, array("options" => array("regexp" => $regex)));
        $fechaFin = filter_var($this->_getParam('fecha_fin', null), FILTER_VALIDATE_REGEXP, array("options" => array("regexp" => $regex)));
        $aduana = filter_var($this->_getParam('aduana', null), FILTER_SANITIZE_NUMBER_INT);

        if ($token && $token == sha1($this->_salt . $rfc . $this->_pepper)) {
            $arrErrors = array();
            if (!$rfc) {
                $arrErrors[] = array(
                    'param' => 'rfc',
                    'error' => 'No se espefifico el RFC.',
                );
                $errorFlag = true;
            } else if ($rfc && strlen($rfc) < 12) {
                $arrErrors[] = array(
                    'param' => 'rfc',
                    'error' => 'No cuenta con la longitud minima para ser un RFC.',
                );
                $errorFlag = true;
            } else if ($rfc && strlen($rfc) == 12) {
                $anexo24 = new OAQ_Anexo24($aduana);
                if (!$anexo24->validateCustomer($rfc, $aduana)) {
                    $arrErrors[] = array(
                        'param' => 'rfc',
                        'error' => 'No se encuentra el RFC en la base de datos.',
                    );
                    $errorFlag = true;
                }
            }

            if (!$aduana) {
                $arrErrors[] = array(
                    'param' => 'aduana',
                    'error' => 'Debe especificar una aduana.',
                );
                $errorFlag = true;
            } else {
                if (!isset($anexo24))
                    $anexo24 = new OAQ_Anexo24($aduana);
                if (!$anexo24->valid) {
                    $arrErrors[] = array(
                        'param' => 'aduana',
                        'error' => "Aduana {$aduana} no disponible por el momento.",
                    );
                    $errorFlag = true;
                }
            }
            if (!$token) {
                $arrErrors[] = array(
                    'param' => 'token',
                    'error' => 'No se especifico el token.',
                );
                $errorFlag = true;
            }
            if (!$fechaIni) {
                $arrErrors[] = array(
                    'param' => 'fecha_ini',
                    'error' => 'No se especifico fecha de inicio o el formato no es el correcto (Ej. yyyy-mm-dd).',
                );
                $errorFlag = true;
            } else if ($fechaIni) {
                if (!$this->isValidMysqlDate($fechaIni)) {
                    $arrErrors[] = array(
                        'param' => 'fecha_ini',
                        'error' => 'Fecha de inicio no es válida.',
                    );
                    $errorFlag = true;
                }
            }
            if (!$fechaFin) {
                $arrErrors[] = array(
                    'param' => 'fecha_fin',
                    'error' => 'No se especifico fecha de fin o el formato no es el correcto (Ej. yyyy-mm-dd).',
                );
                $errorFlag = true;
            } else if ($fechaFin) {
                if (!$this->isValidMysqlDate($fechaFin)) {
                    $arrErrors[] = array(
                        'param' => 'fecha_fin',
                        'error' => 'Fecha de fin no es válida.',
                    );
                    $errorFlag = true;
                }
            }
            if ($this->isValidMysqlDate($fechaFin) && $this->isValidMysqlDate($fechaIni)) {
                if (strtotime($fechaIni) > strtotime($fechaFin)) {
                    $arrErrors[] = array(
                        'param' => 'fechas',
                        'error' => 'La fecha de inicio del reporte no puede ser mayor que la fecha fin.',
                    );
                    $errorFlag = true;
                }
                $datediff = strtotime($fechaFin) - strtotime($fechaIni);
                if (floor($datediff / (60 * 60 * 24)) > 31) {
                    $arrErrors[] = array(
                        'param' => 'fechas',
                        'error' => 'El periodo de consulta no puede ser mayor a 31 días.',
                    );
                    $errorFlag = true;
                }
                if (preg_match('/2012/', $fechaFin) || preg_match('/2012/', $fechaIni)) {
                    $arrErrors[] = array(
                        'param' => 'fechas',
                        'error' => 'El periodo de consulta solo debe ser del año 2013 en adelante.',
                    );
                    $errorFlag = true;
                }
            }
        } else {
            $arrErrors[] = array(
                'param' => 'token',
                'error' => 'Token no válido',
            );
            $errorFlag = true;
        }


        if (isset($errorFlag)) {
            $errors = $domtree->createElement("errores");
            $addErrorsChild = $xmlRoot->appendChild($errors);
            foreach ($arrErrors as $item) {
                $addErrorsChild->appendChild($domtree->createElement($item['param'], $item['error']));
            }
        } else {
            $pedimentos = $anexo24->getListByPeriod($rfc, $fechaIni, $aduana);
            if (count($pedimentos) != 0) {
                unset($xmlRoot);
                unset($domtree);
                $domtree = new DOMDocument('1.0', 'UTF-8');
                $root = $domtree->createElement("operaciones");
                $root->setAttribute('cantidad', count($pedimentos));
                $xmlRoot = $domtree->appendChild($root);
                unset($item);
                foreach ($pedimentos as $k => $item) {
                    $pedimento = $domtree->createElement("pedimento");
                    $pedimento->setAttribute("Patente", $item["Patente"]);
                    $pedimento->setAttribute("Pedimento", $item["Pedimento"]);
                    $pedimento->setAttribute("Cliente", $rfc);
                    $pedimento->setAttribute("Aduana", $item["Patente"]);
                    $pedimento->setAttribute("TipoOpe", $item["TipoOpe"]);
                    $pedimento->setAttribute("Pagado", "S");
                    $pedimento->setAttribute("Year", substr($item["FechaPago"], 0, 4));
                    $pedimento->setAttribute("Url", $this->_config->app->url . "/webservice/index/consulta-pedimento?rfc={$rfc}&aduana={$item["Aduana"]}&patente={$item["Patente"]}&pedimento={$item["Pedimento"]}&token=" . sha1($this->_salt . $item["Pedimento"] . $this->_pepper));
                    $addData = $xmlRoot->appendChild($pedimento);
                }
            } else {
                $errors = $domtree->createElement("errores");
                $addErrorsChild = $xmlRoot->appendChild($errors);
                $addErrorsChild->appendChild($domtree->ceateElement('nulo', 'No existen pedimentos del tipo o periodo especificado.'));
            }
        }
        $output = $domtree->saveXML();
        Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer')->setNoRender(true);
        Zend_Layout::getMvcInstance()->disableLayout();
        $this->_response->setHeader('Content-Type', 'text/xml; charset=utf-8')
            ->setBody($output);
    }

    protected function isValidMysqlDate($value)
    {
        if (preg_match("/^(\d{4})-(\d{2})-(\d{2})/", $value, $matches)) {
            if (checkdate($matches[2], $matches[3], $matches[1])) {
                return true;
            }
        }
        return false;
    }

    /**
     * /webservice/index/pedimentos-pagados/token/6ea6fd81e7e0cccfd7559831acc412c0be7b6994/rfc/JMM931208JY9/fechaIni/2014-02-02/fechaFin/2014-03-03/aduana/640/patente/3589
     * /webservice/index/pedimentos-pagados/token/6ea6fd81e7e0cccfd7559831acc412c0be7b6994/rfc/JMM931208JY9/fechaIni/2014-02-02/fechaFin/2014-03-03/aduana/640/patente/3589
     * /webservice/index/pedimentos-pagados/token/b0ed08d9fb69f9cd23067f51f9d1a70ea0b571c2/rfc/VEN940203EU6/fechaIni/2015-02-02/fechaFin/2015-03-03/aduana/646/patente/3589
     *
     */
    public function pedimentosPagadosAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $domtree = new DOMDocument("1.0", "UTF-8");
        $domtree->preserveWhiteSpace = false;
        $domtree->formatOutput = true;
        try {
            $f = array(
                "patente" => array("StringTrim", "StripTags", "Digits"),
                "aduana" => array("Digits"),
                "rfc" => array("StringToUpper"),
                "token" => array("StringToLower"),
            );
            $vld = array(
                "patente" => new Zend_Validate_Int(),
                "aduana" => new Zend_Validate_Int(),
                "rfc" => array(new Zend_Validate_Regex("/^[A-Z]{3,4}([0-9]{2})([0-9]{2})([0-9]{2})?[A-Z0-9]{3,4}/")),
                "token" => array(new Zend_Validate_Regex("/^[a-z0-9]+$/")),
                "fechaIni" => array(new Zend_Validate_Regex("/^\d{4}\-\d{2}\-\d{2}$/")),
                "fechaFin" => array(new Zend_Validate_Regex("/^\d{4}\-\d{2}\-\d{2}$/")),
            );
            $input = new Zend_Filter_Input($f, $vld, $this->_request->getParams());
            if ($input->isValid("patente") && $input->isValid("aduana") && $input->isValid("rfc") && $input->isValid("token") && $input->isValid("fechaIni") && $input->isValid("fechaFin")) {
                $errorFlag = $this->validateData($input->token, $input->rfc, null, null, $input->fechaIni, $input->fechaFin, null);
                if (!$errorFlag) {
                    $ped = new OAQ_ArchivosM3();
                    $pedimentos = $ped->pedimentosPagadosFiltro($input->rfc, null, null, $input->fechaIni, $input->fechaFin);
                    if (count($pedimentos) != 0) {
                        $root = $domtree->createElement("operaciones");
                        $root->setAttribute("cantidad", count($pedimentos));
                        $xmlRoot = $domtree->appendChild($root);
                        foreach ($pedimentos as $item) {
                            $pedimento = $domtree->createElement("pedimento");
                            $pedimento->setAttribute("Patente", $item["patente"]);
                            $pedimento->setAttribute("Pedimento", $item["pedimento"]);
                            $pedimento->setAttribute("Cliente", $item["rfcImportador"]);
                            $pedimento->setAttribute("Aduana", $item["aduana"]);
                            $pedimento->setAttribute("TipoOpe", $item["tipoMovimiento"]);
                            $pedimento->setAttribute("Pagado", "S");
                            $pedimento->setAttribute("Year", substr($item["fechaPago"], 0, 4));
                            if (APPLICATION_ENV == "production") {
                                $pedimento->setAttribute("Url", "https://oaq.dnsalias.net/webservice/index/detalle-pedimento/rfc/{$input->rfc}/aduana/{$item["aduana"]}/patente/{$item["patente"]}/pedimento/{$item["pedimento"]}/token/" . sha1($this->_salt . $item["pedimento"] . $this->_pepper));
                            } else {
                                $pedimento->setAttribute("Url", $this->_config->app->url . "/webservice/index/detalle-pedimento/rfc/{$input->rfc}/aduana/{$item["aduana"]}/patente/{$item["patente"]}/pedimento/{$item["pedimento"]}/token/" . sha1($this->_salt . $item["pedimento"] . $this->_pepper));
                            }
                            $xmlRoot->appendChild($pedimento);
                        }
                    } else {
                        $domtree = new DOMDocument('1.0', 'UTF-8');
                        $root = $domtree->createElement("errores");
                        $xmlRoot = $domtree->appendChild($root);
                        $xmlRoot->appendChild($domtree->createElement("error", 'No existen pedimentos en el periodo.'));
                    }
                } else {
                    $errors = $domtree->createElement("errores");
                    $addErrorsChild = $domtree->appendChild($errors);
                    foreach ($errorFlag as $item) {
                        $addErrorsChild->appendChild($domtree->createElement($item["param"], $item["error"]));
                    }
                }
            } else {
                $root = $domtree->createElement("errores");
                $xmlRoot = $domtree->appendChild($root);
                $xmlRoot->appendChild($domtree->createElement("error", "Invalid Input!"));
            }
            Zend_Controller_Action_HelperBroker::getStaticHelper("viewRenderer")->setNoRender(true);
            Zend_Layout::getMvcInstance()->disableLayout();
            $this->_response->setHeader("Content-Type", "text/xml; charset=utf-8")
                ->setBody($domtree->saveXML());
        } catch (Zend_Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    protected function estatusPedimento($estatus)
    {
        switch ($estatus) {
            case 1:
                return "POR DESPACHAR";
            case 2:
                return "PAGADO";
            case 3:
                return "EN ADUANA";
            case 4:
                return "LIBERADO";
            default:
                return "N/D";
        }
    }

    protected function tipoFecha($fecha)
    {
        switch ($fecha) {
            case 1:
                return "ENTRADA";
            case 2:
                return "PAGO";
            case 8:
                return "LIBERACION";
            case 9:
                return "NOTIFICACION";
            case 10:
                return "ARRIBO";
            case 11:
                return "RECEPCION DE DOCUMENTOS";
            case 12:
                return "INICIO DESPACHO";
            case 13:
                return "SALIDA DESPACHO";
            default:
                return "N/D";
        }
    }

    /**
     *
     * @param string $token
     * @param string $rfc
     * @param int $patente
     * @param int $aduana
     * @param string $fechaIni
     * @param string $fechaFin
     * @param int $pedimento
     * @return type
     */
    protected function validateData($token, $rfc, $patente, $aduana, $fechaIni, $fechaFin, $pedimento = null)
    {
        try {
            $arrErrors = array();
            if (!$rfc) {
                $arrErrors[] = array(
                    "param" => "rfc",
                    "error" => "No se espefifico el RFC.",
                );
                $errorFlag = true;
            } else if ($rfc && strlen($rfc) < 12) {
                $arrErrors[] = array(
                    "param" => "rfc",
                    "error" => "No cuenta con la longitud minima para ser un RFC.",
                );
                $errorFlag = true;
            } else if ($rfc && strlen($rfc) == 12) {
                $row = new Webservice_Model_Table_WsTokens(array("rfc" => $rfc));
                $table = new Webservice_Model_WsTokens();
                $table->find($row);
                if (null === ($row->getId())) {
                    $arrErrors[] = array(
                        "param" => "rfc",
                        "error" => "No se encuentra el RFC en la base de datos.",
                    );
                    $errorFlag = true;
                } else {
                    if ($row->getToken() !== $token && !isset($pedimento)) {
                        $arrErrors[] = array(
                            "param" => "token",
                            "error" => "Token no válido --.",
                        );
                        $errorFlag = true;
                    }
                }
            }
            if (!$fechaIni) {
                $arrErrors[] = array(
                    "param" => "fechaIni",
                    "error" => "No se especifico fecha de inicio o el formato no es el correcto (Ej. yyyy-mm-dd).",
                );
                $errorFlag = true;
            } else if ($fechaIni) {
                if (!$this->isValidMysqlDate($fechaIni)) {
                    $arrErrors[] = array(
                        "param" => "fechaIni",
                        "error" => "Fecha de inicio no es válida.",
                    );
                    $errorFlag = true;
                }
            }
            if (!$fechaFin) {
                $arrErrors[] = array(
                    "param" => "fechaFin",
                    "error" => "No se especifico fecha de fin o el formato no es el correcto (Ej. yyyy-mm-dd).",
                );
                $errorFlag = true;
            } else if ($fechaFin) {
                if (!$this->isValidMysqlDate($fechaFin)) {
                    $arrErrors[] = array(
                        "param" => "fecha_fin",
                        "error" => "Fecha de fin no es válida.",
                    );
                    $errorFlag = true;
                }
            }
            if ($this->isValidMysqlDate($fechaFin) && $this->isValidMysqlDate($fechaIni)) {
                if (strtotime($fechaIni) > strtotime($fechaFin)) {
                    $arrErrors[] = array(
                        "param" => "fechas",
                        "error" => "La fecha de inicio del reporte no puede ser mayor que la fecha fin.",
                    );
                    $errorFlag = true;
                }
                $datediff = strtotime($fechaFin) - strtotime($fechaIni);
                if (floor($datediff / (60 * 60 * 24)) > 31) {
                    $arrErrors[] = array(
                        "param" => "fechas",
                        "error" => "El periodo de consulta no puede ser mayor a 31 días.",
                    );
                    $errorFlag = true;
                }
                if (preg_match("/2012/", $fechaFin) || preg_match("/2012/", $fechaIni)) {
                    $arrErrors[] = array(
                        "param" => "fechas",
                        "error" => "El periodo de consulta solo debe ser del año 2013 en adelante.",
                    );
                    $errorFlag = true;
                }
            }
            if (isset($pedimento)) {
                if ($token !== sha1($this->_salt . $pedimento . $this->_pepper)) {
                    $arrErrors[] = array(
                        "param" => "token",
                        "error" => "Token no válido.",
                    );
                    $errorFlag = true;
                }
            }
            if (!isset($token)) {
                $arrErrors[] = array(
                    "param" => "token",
                    "error" => "Token no se encuentra en la base de datos.",
                );
                $errorFlag = true;
            }
            return isset($errorFlag) ? $arrErrors : null;
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    protected function get_client_ip()
    {
        $ipaddress = '';
        if (getenv('HTTP_CLIENT_IP')) {
            $ipaddress = getenv('HTTP_CLIENT_IP');
        } else if (getenv('HTTP_X_FORWARDED_FOR')) {
            $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
        } else if (getenv('HTTP_X_FORWARDED')) {
            $ipaddress = getenv('HTTP_X_FORWARDED');
        } else if (getenv('HTTP_FORWARDED_FOR')) {
            $ipaddress = getenv('HTTP_FORWARDED_FOR');
        } else if (getenv('HTTP_FORWARDED')) {
            $ipaddress = getenv('HTTP_FORWARDED');
        } else if (getenv('REMOTE_ADDR')) {
            $ipaddress = getenv('REMOTE_ADDR');
        } else {
            $ipaddress = 'UNKNOWN';
        }
        return $ipaddress;
    }

    public function pruebaAction()
    {
    }

    /*
     * /webservice/index/historico?rfc=FMM1203305X1&fechaIni=2015-05-02&fechaFin=2015-05-30&aduana=240&patente=3589
     *
     */

    public function historicoAction()
    {
        $regex = "/^\d{4}\-\d{2}\-\d{2}$/";
        $rfc = filter_var($this->_getParam('rfc', null), FILTER_SANITIZE_STRING);
        $fechaIni = filter_var($this->_getParam('fechaIni', null), FILTER_VALIDATE_REGEXP, array("options" => array("regexp" => $regex)));
        $fechaFin = filter_var($this->_getParam('fechaFin', null), FILTER_VALIDATE_REGEXP, array("options" => array("regexp" => $regex)));
        $patente = filter_var($this->_getParam('patente', null), FILTER_SANITIZE_STRING);
        $aduana = filter_var($this->_getParam('aduana', null), FILTER_SANITIZE_STRING);
        if (isset($patente) && $patente == 3589) {
            $sitawin = new OAQ_AduanetM3(true, 'localhost', 'root', 'mysql11!', 'SAAIWEB', 3306);
            echo '<!doctype html>'
                . '<html lang="en">'
                . '<head>'
                . '<meta charset="utf-8">'
                . '<title>Historico</title>'
                . '</head>'
                . "<style>body {margin:0;padding:0; font-family:sans-serif;}"
                . "table {border-collapse:collapse; width: 100%;}"
                . "table.invoices {padding: 0; margin:0;}"
                . "table th, table td {font-size: 12px; border: 1px #aaa solid; padding: 2px 5px;}"
                . "table th {background: #f1f1f1; font-weight: bold;}"
                . "table td.tdinvoice, td.tdparts {padding: 0; border: 0; margin: 0;}"
                . "table.parts th {background-color: #f3f3f3; border: 1px #aaa solid;}"
                . "table.parts td {border: 0; border: 1px #aaa solid;}"
                . "h3 {margin: 0; padding:0;}"
                . "</style>"
                . "</head>"
                . "<body>";
            $html = '<table>';
            $array = array("Patente", "Aduana", "Pedimento", "Trafico", "TipoOperacion", "TransporteEntrada", "TransporteArribo", "TransporteSalida", "FechaEntrada", "FechaPago", "FirmaValidacion", "FirmaBanco", "TipoCambio", "CvePed", "Regimen", "AduanaEntrada", "ValorDolares", "ValorAduana", "Fletes", "Seguros", "Embalajes", "OtrosIncrementales", "DTA", "IVA", "IGI", "PREV", "CNT", "TotalEfectivo", "PesoBruto", "Bultos", "Guias", "BL", "NumFactura", "Cove", "FechaFactura", "Incoterm", "ValorFacturaUsd", "ValorFacturaMonExt", "NomProveedor", "PaisFactura", "TaxId", "Divisa", "FactorMonExt", "NumParte", "Descripcion", "Fraccion", "OrdenFraccion", "ValorMonExt", "CantUMC", "UMC", "CantUMT", "UMT", "PaisOrigen", "PaisVendedor", "TasaAdvalorem", "TLC", "PROSEC", "PatenteOrig", "AduanaOrig", "PedimentoOrig", "CantidadOriginal", "UnidadOriginal", "FechaOriginal");
            $html .= "<tr>";
            foreach ($array as $item) {
                $html .= $this->_thValue($item);
            }
            $html .= "</tr>";
            echo $html;

            $pedimentos = $sitawin->wsPedimentoPagados($rfc, $fechaIni, $fechaFin);
            if (isset($pedimentos) && !empty($pedimentos)) {
                foreach ($pedimentos as $item) {
                    $data = $sitawin->wsDetallePedimento($item["pedimento"]);
                    $html = "<tr>";
                    if (isset($data["facturas"])) {
                        foreach ($data['facturas'] as $invoice) {
                            if (isset($invoice["partes"]) && !empty($invoice["partes"])) {
                                foreach ($invoice["partes"] as $part) {
                                    $html .= $this->_tdValue($data['patente']);
                                    $html .= $this->_tdValue($data['aduana']);
                                    $html .= $this->_tdValue($data['pedimento']);
                                    $html .= $this->_tdValue($data['referencia']);
                                    $html .= $this->_tdValue($data['tipoOperacion']);
                                    $html .= $this->_tdValue($data['transporteEntrada']);
                                    $html .= $this->_tdValue($data['transporteArribo']);
                                    $html .= $this->_tdValue($data['transporteSalida']);
                                    $html .= $this->_tdValue($data['fechaEntrada']);
                                    $html .= $this->_tdValue($data['fechaPago']);
                                    $html .= $this->_tdValue($data['firmaValidacion']);
                                    $html .= $this->_tdValue($data['firmaBanco']);
                                    $html .= $this->_tdValue($data['tipoCambio']);
                                    $html .= $this->_tdValue($data['cvePed']);
                                    $html .= $this->_tdValue($data['regimen']);
                                    $html .= $this->_tdValue($data['aduanaEntrada']);
                                    $html .= $this->_tdValue($data['valorDolares']);
                                    $html .= $this->_tdValue($data['valorAduana']);
                                    $html .= $this->_tdValue($data['fletes']);
                                    $html .= $this->_tdValue($data['seguros']);
                                    $html .= $this->_tdValue($data['embalajes']);
                                    $html .= $this->_tdValue($data['otrosIncrementales']);
                                    $html .= $this->_tdValue($data['dta']);
                                    $html .= $this->_tdValue($data['iva']);
                                    $html .= $this->_tdValue($data['igi']);
                                    $html .= $this->_tdValue($data['prev']);
                                    $html .= $this->_tdValue($data['cnt']);
                                    $html .= $this->_tdValue($data['totalEfectivo']);
                                    $html .= $this->_tdValue($data['pesoBruto']);
                                    $html .= $this->_tdValue($data['bultos']);
                                    $html .= $this->_tdValue($invoice["numFactura"]);
                                    $html .= $this->_tdValue(htmlentities(utf8_decode(trim($invoice["cove"])), ENT_QUOTES, 'UTF-8'));
                                    $html .= $this->_tdValue(htmlentities(utf8_decode(trim($invoice["fechaFactura"])), ENT_QUOTES, 'UTF-8'));
                                    $html .= $this->_tdValue(htmlentities(utf8_decode(trim($invoice["incoterm"])), ENT_QUOTES, 'UTF-8'));
                                    $html .= $this->_tdValue($invoice["valorFacturaUsd"]);
                                    $html .= $this->_tdValue($invoice["valorFacturaMonExt"]);
                                    $html .= $this->_tdValue(htmlentities(utf8_decode(trim($invoice["nomProveedor"])), ENT_QUOTES, 'UTF-8'));
                                    $html .= $this->_tdValue($invoice["paisFactura"]);
                                    $html .= $this->_tdValue($invoice["taxId"]);
                                    $html .= $this->_tdValue($invoice["divisa"]);
                                    $html .= $this->_tdValue($invoice["factorMonExt"]);
                                    $html .= $this->_tdValue($part["numParte"]);
                                    $html .= $this->_tdValue(htmlentities($part["descripcion"]));
                                    $html .= $this->_tdValue($part["fraccion"]);
                                    $html .= $this->_tdValue($part["ordenFraccion"]);
                                    $html .= $this->_tdValue($part["valorMonExt"]);
                                    $html .= $this->_tdValue($part["precioUnitario"]);
                                    $html .= $this->_tdValue($part["cantUmc"]);
                                    $html .= $this->_tdValue($part["umc"]);
                                    $html .= $this->_tdValue($part["cantUmt"]);
                                    $html .= $this->_tdValue($part["umt"]);
                                    $html .= $this->_tdValue($part["paisOrigen"]);
                                    $html .= $this->_tdValue($part["paisVendedor"]);
                                    $html .= $this->_tdValue($part["tasaAdvalorem"]);
                                    $html .= $this->_tdValue($part["tlc"]);
                                    $html .= $this->_tdValue($part["prosec"]);
                                    if (isset($part["patenteOriginal"])) {
                                        $html .= $this->_tdValue($part["patenteOriginal"]);
                                        $html .= $this->_tdValue($part["aduanaOriginal"]);
                                        $html .= $this->_tdValue($part["pedimentoOriginal"]);
                                        $html .= $this->_tdValue($part["regimenOriginal"]);
                                        $html .= $this->_tdValue($part["cantidadOriginal"]);
                                        $html .= $this->_tdValue($part["unidadOriginal"]);
                                        $html .= $this->_tdValue($part["fechaOriginal"]);
                                    } else {
                                        $html .= $this->_tdValue("");
                                        $html .= $this->_tdValue("");
                                        $html .= $this->_tdValue("");
                                        $html .= $this->_tdValue("");
                                        $html .= $this->_tdValue("");
                                        $html .= $this->_tdValue("");
                                        $html .= $this->_tdValue("");
                                    }
                                    $html .= '</tr>';
                                }
                            }
                        }
                    }
                    echo $html;
                }
            }
            $html = '</table>';
            $html .= '</body>';
            $html .= '</html>';
            echo $html;
        }
    }

    /**
     * /webservice/index/campa-pedimentos-pagados/token/1dd2b7b8a01dbdf34b41e26863429609c0d49c38/rfc/FMM1203305X1/fechaIni/2015-06-02/fechaFin/2015-06-30/aduana/240/patente/3589
     * /webservice/index/campa-pedimentos-pagados/token/be53128d5a81786197166c1742f9a3ec7f8f82f9/rfc/NPM940304MC7/fechaIni/2015-06-02/fechaFin/2015-06-30/aduana/640/patente/3589
     * /webservice/index/campa-pedimentos-pagados?token=be53128d5a81786197166c1742f9a3ec7f8f82f9&rfc=NPM940304MC7&fechaIni=2015-06-02&fechaFin=2015-06-30&aduana=640&patente=3589
     **/
    public function campaPedimentosPagadosAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "patente" => "Digits",
                "aduana" => "Digits",
                "rfc" => "StringToUpper",
                "token" => "StringToLower",
            );
            $v = array(
                "patente" => array("NotEmpty", new Zend_Validate_Int()),
                "aduana" => array("NotEmpty", new Zend_Validate_Int()),
                "rfc" => array("NotEmpty", new Zend_Validate_Regex("/^[A-Z]{3,4}([0-9]{2})([0-9]{2})([0-9]{2})?[A-Z0-9]{3,4}/")),
                "fechaIni" => array("NotEmpty", new Zend_Validate_Regex("/^\d{4}\-\d{2}\-\d{2}$/")),
                "fechaFin" => array("NotEmpty", new Zend_Validate_Regex("/^\d{4}\-\d{2}\-\d{2}$/")),
                "token" => array("NotEmpty", new Zend_Validate_Regex("/^[a-z0-9]+$/")),
            );
            $i = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($i->isValid("fechaIni") && $i->isValid("fechaFin")) {

                $di = new DateTime($i->fechaIni);
                $df = new DateTime("2018-05-01");

                if ($di >= $df) {

                    $domtree = new DOMDocument("1.0", "UTF-8");
                    $xmlRoot = $domtree->appendChild($domtree->createElement("pedimentos"));
                    $errorFlag = $this->validateData($i->token, $i->rfc, null, null, $i->fechaIni, $i->fechaFin);

                    $mppr = new Automatizacion_Model_RptPedimentos();
                    $arr = $mppr->wsObtener($i->patente, $i->aduana, $i->rfc, $i->fechaIni, $i->fechaFin);

                    if (!empty($arr) && count($arr) != 0) {
                        unset($xmlRoot);
                        unset($domtree);
                        $domtree = new DOMDocument("1.0", "UTF-8");
                        $root = $domtree->createElement("operaciones");
                        $root->setAttribute("cantidad", ($arr !== false) ? count($arr) : 0);
                        $xmlRoot = $domtree->appendChild($root);

                        foreach ($arr as $item) {
                            $pedimento = $domtree->createElement("pedimento");
                            $pedimento->setAttribute("operacion", $item["aduana"] . '-' . $item["patente"] . '-' . $item["pedimento"]);
                            $pedimento->setAttribute("tipoOpe", ($item["tipoOperacion"] == 1) ? "IMP" : "EXP");
                            if (APPLICATION_ENV == "production") {
                                $pedimento->setAttribute("url", "https://oaq.dnsalias.net/webservice/index/campa-detalle-pedimento/aduana/{$item["aduana"]}/patente/{$item["patente"]}/pedimento/{$item["pedimento"]}");
                            } else {
                                $pedimento->setAttribute("url", $this->_config->app->url . "/webservice/index/campa-detalle-pedimento/aduana/{$item["aduana"]}/patente/{$item["patente"]}/pedimento/{$item["pedimento"]}");
                            }
                            $xmlRoot->appendChild($pedimento);
                        }
                    } else {
                        $domtree = new DOMDocument('1.0', 'UTF-8');
                        $root = $domtree->createElement("errores");
                        $xmlRoot = $domtree->appendChild($root);
                        $xmlRoot->appendChild($domtree->createElement("error", 'No existen pedimentos en el periodo.'));
                    }
                    $output = $domtree->saveXML();
                    Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer')->setNoRender(true);
                    Zend_Layout::getMvcInstance()->disableLayout();
                    $this->_response->setHeader('Content-Type', 'text/xml; charset=utf-8')
                        ->setBody($output);
                    return;
                }
            }
            if ($i->isValid("patente") && $i->isValid("aduana") && $i->isValid("rfc") && $i->isValid("fechaIni") && $i->isValid("fechaFin") && $i->isValid("token")) {
                $domtree = new DOMDocument("1.0", "UTF-8");
                $xmlRoot = $domtree->appendChild($domtree->createElement("pedimentos"));
                $errorFlag = $this->validateData($i->token, $i->rfc, null, null, $i->fechaIni, $i->fechaFin);
                if (isset($errorFlag)) {
                    $errors = $domtree->createElement("errores");
                    $addErrorsChild = $xmlRoot->appendChild($errors);
                    foreach ($errorFlag as $item) {
                        $addErrorsChild->appendChild($domtree->createElement($item["param"], $item["error"]));
                    }
                } else {
                    if ($i->patente == 3589) {
                        $mapper = new Application_Model_SisPedimentos();
                        if (($s = $mapper->sisPedimentos($i->patente, $i->aduana))) {
                            $sitawin = new OAQ_Sitawin(true, $s["direccion_ip"], $s["usuario"], $s["pwd"], $s["dbname"], $s["puerto"], $s["tipo"]);
                        }
                        if (!$sitawin) {
                            die("No DB connected.");
                        }
                        $data = $sitawin->wsPedimentoPagados($i->rfc, $i->fechaIni, $i->fechaFin);
                        if ($i->patente == 3589 && $i->aduana = 240) {
                            $sitawin = new OAQ_Sitawin(true, '192.168.200.5', 'sa', 'adminOAQ123', 'SITA43589240', 1433, 'Pdo_Mssql');
                            $new = $sitawin->wsPedimentoPagados($i->rfc, $i->fechaIni, $i->fechaFin);
                            if (!empty($data) && !empty($new)) {
                                $data = array_merge($data, $new);
                            } else {
                                $data = $new;
                            }
                        }
                        if (!empty($data) && count($data) != 0) {
                            unset($xmlRoot);
                            unset($domtree);
                            $domtree = new DOMDocument("1.0", "UTF-8");
                            $root = $domtree->createElement("operaciones");
                            $root->setAttribute("cantidad", ($data !== false) ? count($data) : 0);
                            $xmlRoot = $domtree->appendChild($root);
                            unset($item);
                            foreach ($data as $item) {
                                $item["aduana"] = $i->aduana;
                                $pedimento = $domtree->createElement("pedimento");
                                $pedimento->setAttribute("operacion", $item["aduana"] . '-' . $item["patente"] . '-' . $item["pedimento"]);
                                $pedimento->setAttribute("tipoOpe", ($item["tipoOperacion"] == "TOCE.IMP") ? "IMP" : "EXP");
                                if (APPLICATION_ENV == "production") {
                                    $pedimento->setAttribute("url", "https://oaq.dnsalias.net/webservice/index/campa-detalle-pedimento/aduana/{$item["aduana"]}/patente/{$item["patente"]}/pedimento/{$item["pedimento"]}");
                                } else if (APPLICATION_ENV == "staging") {
                                    $pedimento->setAttribute("url", "http://192.168.0.191/webservice/index/campa-detalle-pedimento/aduana/{$item["aduana"]}/patente/{$item["patente"]}/pedimento/{$item["pedimento"]}");
                                } else {
                                    $pedimento->setAttribute("url", $this->_config->app->url . "/webservice/index/campa-detalle-pedimento/aduana/{$item["aduana"]}/patente/{$item["patente"]}/pedimento/{$item["pedimento"]}");
                                }
                                $xmlRoot->appendChild($pedimento);
                            }
                        } else {
                            $domtree = new DOMDocument('1.0', 'UTF-8');
                            $root = $domtree->createElement("errores");
                            $xmlRoot = $domtree->appendChild($root);
                            $xmlRoot->appendChild($domtree->createElement("error", 'No existen pedimentos en el periodo.'));
                        }
                    }
                }
                $output = $domtree->saveXML();
                Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer')->setNoRender(true);
                Zend_Layout::getMvcInstance()->disableLayout();
                $this->_response->setHeader('Content-Type', 'text/xml; charset=utf-8')
                    ->setBody($output);
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Zend_Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    protected function _xmlPedimento(DOMDocument $domtree, $data)
    {
        $root = $domtree->createElement("pedimento");
        $xmlRoot = $domtree->appendChild($root);
        $xmlRoot->appendChild($domtree->createElement("patente", $data['patente']));
        $xmlRoot->appendChild($domtree->createElement("aduana", $data['aduana']));
        $xmlRoot->appendChild($domtree->createElement("pedimento", $data['pedimento']));
        $xmlRoot->appendChild($domtree->createElement("referencia", $data['referencia']));
        $xmlRoot->appendChild($domtree->createElement("tipoOperacion", ($data['tipoOperacion'] == 1) ? 'IMP' : 'EXP'));
        $xmlRoot->appendChild($domtree->createElement("transporteEntrada", $data['transporteEntrada']));
        $xmlRoot->appendChild($domtree->createElement("transporteArribo", $data['transporteArribo']));
        $xmlRoot->appendChild($domtree->createElement("transporteSalida", $data['transporteSalida']));
        $xmlRoot->appendChild($domtree->createElement("fechaEntrada", $data['fechaEntrada']));
        $xmlRoot->appendChild($domtree->createElement("fechaPago", $data['fechaPago']));
        $xmlRoot->appendChild($domtree->createElement("firmaValidacion", $data['firmaValidacion']));
        $xmlRoot->appendChild($domtree->createElement("firmaBanco", $data['firmaBanco']));
        $xmlRoot->appendChild($domtree->createElement("tipoCambio", $data['tipoCambio']));
        $xmlRoot->appendChild($domtree->createElement("cvePed", $data['cvePed']));
        $xmlRoot->appendChild($domtree->createElement("regimen", $data['regimen']));
        $xmlRoot->appendChild($domtree->createElement("aduanaEntrada", $data['aduanaEntrada']));

        $xmlRoot->appendChild($domtree->createElement("valorDolares", $data['valorDolares']));
        $xmlRoot->appendChild($domtree->createElement("valorAduana", ($data['tipoOperacion'] == 1) ? $data['valorAduana'] : '0'));
        $xmlRoot->appendChild($domtree->createElement("valorComercial", $data['valorComercial']));

        $xmlRoot->appendChild($domtree->createElement("fletes", round($data['fletes'])));
        $xmlRoot->appendChild($domtree->createElement("seguros", round($data['seguros'])));
        $xmlRoot->appendChild($domtree->createElement("embalajes", round($data['embalajes'])));
        $xmlRoot->appendChild($domtree->createElement("otrosIncrementales", round($data['otrosIncrementales'])));

        $xmlRoot->appendChild($domtree->createElement("dta", $data['dta']));
        $xmlRoot->appendChild($domtree->createElement("iva", $data['iva']));
        $xmlRoot->appendChild($domtree->createElement("igi", $data['igi']));
        $xmlRoot->appendChild($domtree->createElement("prev", $data['prev']));
        $xmlRoot->appendChild($domtree->createElement("cnt", $data['cnt']));
        $xmlRoot->appendChild($domtree->createElement("totalEfectivo", $data['totalEfectivo']));
        $xmlRoot->appendChild($domtree->createElement("PesoBruto", $data['pesoBruto']));
        $xmlRoot->appendChild($domtree->createElement("bultos", $data['bultos']));

        $mppr = new Automatizacion_Model_RptPedimentoDesglose();
        $facturas = $mppr->wsObtenerFacturas($data['idPedimento']);

        if (isset($facturas) && !empty($facturas)) {
            $invoices = $xmlRoot->appendChild($domtree->createElement("facturas"));
            foreach ($facturas as $invoice) {

                $factura = $invoices->appendChild($domtree->createElement("factura"));
                $factura->appendChild($domtree->createElement("taxId", $invoice["taxId"]));
                $factura->appendChild($domtree->createElement("numFactura", $invoice["numFactura"]));
                $factura->appendChild($domtree->createElement("taxId", htmlentities($invoice["taxId"])));
                $factura->appendChild($domtree->createElement("nomProveedor", htmlentities($invoice["nomProveedor"])));
                $factura->appendChild($domtree->createElement("incoterm", htmlentities(utf8_decode(trim($invoice["incoterm"])), ENT_QUOTES, 'UTF-8')));
                $factura->appendChild($domtree->createElement("cove", htmlentities(utf8_decode(trim($invoice["cove"])), ENT_QUOTES, 'UTF-8')));
                $factura->appendChild($domtree->createElement("fechaFactura", htmlentities(utf8_decode(trim($invoice["fechaFactura"])), ENT_QUOTES, 'UTF-8')));
                $factura->appendChild($domtree->createElement("valorFacturaUsd", $invoice["valorFacturaUsd"]));
                $factura->appendChild($domtree->createElement("valorFacturaMonExt", $invoice["valorFacturaMonExt"]));
                $factura->appendChild($domtree->createElement("paisFactura", $invoice["paisFactura"]));
                $factura->appendChild($domtree->createElement("divisa", $invoice["divisa"]));
                $factura->appendChild($domtree->createElement("factorMonExt", $invoice["factorMonExt"]));
                $partes = $mppr->wsObtenerPartes($data['idPedimento'], $invoice["numFactura"]);

                if (isset($partes) && !empty($partes)) {

                    $parts = $factura->appendChild($domtree->createElement("partes"));

                    foreach ($partes as $part) {
                        $parte = $parts->appendChild($domtree->createElement("parte"));
                        $parte->appendChild($domtree->createElement("numParte", strtoupper($part["numParte"])));
                        $parte->appendChild($domtree->createElement("descripcion", htmlentities($part["descripcion"])));
                        $parte->appendChild($domtree->createElement("fraccion", $part["fraccion"]));
                        $parte->appendChild($domtree->createElement("ordenFraccion", $part["ordenFraccion"]));
                        $parte->appendChild($domtree->createElement("valorMonExt", $part["precioUnitario"] * $part["cantUmc"]));
                        $parte->appendChild($domtree->createElement("precioUnitario", $part["precioUnitario"]));
                        $parte->appendChild($domtree->createElement("cantUmc", $part["cantUmc"]));
                        $parte->appendChild($domtree->createElement("umc", $part["umc"]));
                        $parte->appendChild($domtree->createElement("cantUmt", $part["cantUmt"]));
                        $parte->appendChild($domtree->createElement("umt", $part["umt"]));
                        $parte->appendChild($domtree->createElement("paisOrigen", $part["paisOrigen"]));
                        $parte->appendChild($domtree->createElement("paisVendedor", $part["paisVendedor"]));
                        $parte->appendChild($domtree->createElement("tasaAdvalorem", $part["tasaAdvalorem"]));
                        if (isset($part["tlc"])) {
                            if ($part["tlc"] == 'S') {
                                $tlc = 'S';
                            } else {
                                $tlc = '';
                            }
                        }
                        if (isset($part["prosec"])) {
                            if ($part["prosec"] == 'S') {
                                $prosec = 'S';
                            } else {
                                $prosec = '';
                            }
                        }
                        $parte->appendChild($domtree->createElement("tlc", $tlc));
                        $parte->appendChild($domtree->createElement("prosec", $prosec));
                        if (isset($part["patenteOriginal"])) {
                            $parte->appendChild($domtree->createElement("patenteOriginal", $part["patenteOriginal"]));
                            $parte->appendChild($domtree->createElement("aduanaOriginal", $part["aduanaOriginal"]));
                            $parte->appendChild($domtree->createElement("pedimentoOriginal", $part["pedimentoOriginal"]));
                            $parte->appendChild($domtree->createElement("regimenOriginal", $part["regimenOriginal"]));
                            $parte->appendChild($domtree->createElement("cantidadOriginal", $part["cantidadOriginal"]));
                            $parte->appendChild($domtree->createElement("unidadOriginal", $part["unidadOriginal"]));
                            $parte->appendChild($domtree->createElement("fechaOriginal", $part["fechaOriginal"]));
                        }
                    }
                }
            }
        }
    }

    /**
     * /webservice/index/campa-detalle-pedimento?patente=3589&aduana=646&pedimento=6000019
     * /webservice/index/campa-detalle-pedimento?patente=3589&aduana=640&pedimento=6000019
     *
     * @return boolean
     */
    public function campaDetallePedimentoAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $domtree = new DOMDocument("1.0", "UTF-8");
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags", "Digits"),
            );
            $v = array(
                "patente" => new Zend_Validate_Int(),
                "aduana" => new Zend_Validate_Int(),
                "pedimento" => new Zend_Validate_Int(),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("patente") && $input->isValid("aduana") && $input->isValid("pedimento")) {

                $misc = new OAQ_Misc();
                $sitawin = $misc->sitawin($input->patente, $input->aduana);
                if (!$sitawin) {
                    $root = $domtree->createElement("errores");
                    $xmlRoot = $domtree->appendChild($root);
                    $xmlRoot->appendChild($domtree->createElement("error", 'No existen pedimentos en el periodo.'));
                } else {

                    $mppr = new Automatizacion_Model_RptPedimentos();
                    $data = $mppr->wsObtenerPedimento($input->patente, $input->aduana, $input->pedimento);
                    if (!empty($data)) {

                        $this->_xmlPedimento($domtree, $data);
                        $output = $domtree->saveXML();
                        Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer')->setNoRender(true);
                        Zend_Layout::getMvcInstance()->disableLayout();
                        $this->_response->setHeader("Content-Type", "text/xml; charset=utf-8")
                            ->setBody($output);
                        return;
                    }
                    if (empty($data)) {
                        $data = $sitawin->wsDetallePedimento($input->pedimento);
                    }
                    if (empty($data) && $input->patente == 3589 && $input->aduana = 240) {
                        $sitawin = new OAQ_Sitawin(true, '192.168.200.5', 'sa', 'adminOAQ123', 'SITAW3589240', 1433, 'Pdo_Mssql');
                        $data = $sitawin->wsDetallePedimento($input->pedimento);
                    }
                    if (empty($data) && $input->patente == 3589 && $input->aduana = 640) {
                        $sitawin = new OAQ_Sitawin(true, '192.168.200.5', 'sa', 'adminOAQ123', 'SITAW3589640R', 1433, 'Pdo_Mssql');
                        $data = $sitawin->wsDetallePedimento($input->pedimento);
                    }
                    if (!empty($data) && count($data) != 0 && !empty($data)) {
                        $root = $domtree->createElement("pedimento");
                        $xmlRoot = $domtree->appendChild($root);
                        $xmlRoot->appendChild($domtree->createElement("patente", $data['patente']));
                        $xmlRoot->appendChild($domtree->createElement("aduana", $data['aduana'] . $data["seccAduana"]));
                        $xmlRoot->appendChild($domtree->createElement("pedimento", $data['pedimento']));
                        $xmlRoot->appendChild($domtree->createElement("referencia", $data['referencia']));
                        $xmlRoot->appendChild($domtree->createElement("tipoOperacion", ($data['tipoOperacion'] == 'TOCE.IMP') ? 'IMP' : 'EXP'));
                        $xmlRoot->appendChild($domtree->createElement("transporteEntrada", $data['transporteEntrada']));
                        $xmlRoot->appendChild($domtree->createElement("transporteArribo", $data['transporteArribo']));
                        $xmlRoot->appendChild($domtree->createElement("transporteSalida", $data['transporteSalida']));
                        $xmlRoot->appendChild($domtree->createElement("fechaEntrada", $data['fechaEntrada']));
                        $xmlRoot->appendChild($domtree->createElement("fechaPago", $data['fechaPago']));
                        $xmlRoot->appendChild($domtree->createElement("firmaValidacion", $data['firmaValidacion']));
                        $xmlRoot->appendChild($domtree->createElement("firmaBanco", $data['firmaBanco']));
                        $xmlRoot->appendChild($domtree->createElement("tipoCambio", $data['tipoCambio']));
                        $xmlRoot->appendChild($domtree->createElement("cvePed", $data['cvePed']));
                        $xmlRoot->appendChild($domtree->createElement("regimen", $data['regimen']));
                        $xmlRoot->appendChild($domtree->createElement("aduanaEntrada", $data['aduanaEntrada']));
                        if (!isset($historic)) {
                            $xmlRoot->appendChild($domtree->createElement("valorDolares", $data['valorDolares']));
                            $xmlRoot->appendChild($domtree->createElement("valorAduana", ($data['tipoOperacion'] == 'TOCE.IMP') ? $data['valorAduana'] : '0'));
                            $xmlRoot->appendChild($domtree->createElement("valorComercial", $data['valorComercial']));
                        } else {
                            $xmlRoot->appendChild($domtree->createElement("valorDolares", $data['valorDolares']));
                            $xmlRoot->appendChild($domtree->createElement("valorAduana", ($data['tipoOperacion'] == 'TOCE.IMP') ? $data['valorAduana'] : '0'));
                            $xmlRoot->appendChild($domtree->createElement("valorComercial", $data['valorComercial']));
                        }
                        if (!isset($historic)) {
                            $xmlRoot->appendChild($domtree->createElement("fletes", round($data['fletes'] * $data['tipoCambio'])));
                            $xmlRoot->appendChild($domtree->createElement("seguros", round($data['seguros'] * $data['tipoCambio'])));
                            $xmlRoot->appendChild($domtree->createElement("embalajes", round($data['embalajes'] * $data['tipoCambio'])));
                            $xmlRoot->appendChild($domtree->createElement("otrosIncrementales", round($data['otrosIncrementales'] * $data['tipoCambio'])));
                        } else {
                            $xmlRoot->appendChild($domtree->createElement("fletes", round($data['fletes'])));
                            $xmlRoot->appendChild($domtree->createElement("seguros", round($data['seguros'])));
                            $xmlRoot->appendChild($domtree->createElement("embalajes", round($data['embalajes'])));
                            $xmlRoot->appendChild($domtree->createElement("otrosIncrementales", round($data['otrosIncrementales'])));
                        }
                        $xmlRoot->appendChild($domtree->createElement("dta", $data['dta']));
                        $xmlRoot->appendChild($domtree->createElement("iva", $data['iva']));
                        $xmlRoot->appendChild($domtree->createElement("igi", $data['igi']));
                        $xmlRoot->appendChild($domtree->createElement("prev", $data['prev']));
                        $xmlRoot->appendChild($domtree->createElement("cnt", $data['cnt']));
                        $xmlRoot->appendChild($domtree->createElement("totalEfectivo", $data['totalEfectivo']));
                        $xmlRoot->appendChild($domtree->createElement("PesoBruto", $data['pesoBruto']));
                        $xmlRoot->appendChild($domtree->createElement("bultos", $data['bultos']));
                        if (isset($data["facturas"]) && !empty($data["facturas"])) {
                            $invoices = $xmlRoot->appendChild($domtree->createElement("facturas"));
                            foreach ($data["facturas"] as $invoice) {
                                $factura = $invoices->appendChild($domtree->createElement("factura"));
                                $factura->appendChild($domtree->createElement("taxId", $invoice["taxId"]));
                                $factura->appendChild($domtree->createElement("nomProveedor", htmlentities(utf8_decode(trim($invoice["nomProveedor"])), ENT_QUOTES, 'UTF-8')));
                                $factura->appendChild($domtree->createElement("numFactura", $invoice["numFactura"]));
                                $factura->appendChild($domtree->createElement("incoterm", htmlentities(utf8_decode(trim($invoice["incoterm"])), ENT_QUOTES, 'UTF-8')));
                                $factura->appendChild($domtree->createElement("cove", htmlentities(utf8_decode(trim($invoice["cove"])), ENT_QUOTES, 'UTF-8')));
                                $factura->appendChild($domtree->createElement("fechaFactura", htmlentities(utf8_decode(trim($invoice["fechaFactura"])), ENT_QUOTES, 'UTF-8')));
                                $factura->appendChild($domtree->createElement("valorFacturaUsd", $invoice["valorFacturaUsd"]));
                                $factura->appendChild($domtree->createElement("valorFacturaMonExt", $invoice["valorFacturaMonExt"]));
                                $factura->appendChild($domtree->createElement("paisFactura", $invoice["paisFactura"]));
                                $factura->appendChild($domtree->createElement("divisa", $invoice["divisa"]));
                                $factura->appendChild($domtree->createElement("factorMonExt", $invoice["factorMonExt"]));
                                if (isset($invoice["partes"]) && !empty($invoice["partes"])) {
                                    $parts = $factura->appendChild($domtree->createElement("partes"));
                                    foreach ($invoice["partes"] as $part) {
                                        $parte = $parts->appendChild($domtree->createElement("parte"));
                                        $parte->appendChild($domtree->createElement("numParte", strtoupper($part["numParte"])));
                                        $parte->appendChild($domtree->createElement("descripcion", htmlentities($part["descripcion"])));
                                        $parte->appendChild($domtree->createElement("fraccion", $part["fraccion"]));
                                        $parte->appendChild($domtree->createElement("ordenFraccion", $part["ordenFraccion"]));
                                        $parte->appendChild($domtree->createElement("valorMonExt", $part["valorMonExt"]));
                                        if (!isset($historic)) {
                                            $parte->appendChild($domtree->createElement("precioUnitario", $part["precioUnitario"]));
                                        } else {
                                            $parte->appendChild($domtree->createElement("precioUnitario", $part["valorMonExt"] / $part['cantUmc']));
                                        }
                                        $parte->appendChild($domtree->createElement("cantUmc", $part["cantUmc"]));
                                        $parte->appendChild($domtree->createElement("umc", $part["umc"]));
                                        $parte->appendChild($domtree->createElement("cantUmt", $part["cantUmt"]));
                                        $parte->appendChild($domtree->createElement("umt", $part["umt"]));
                                        $parte->appendChild($domtree->createElement("paisOrigen", $part["paisOrigen"]));
                                        $parte->appendChild($domtree->createElement("paisVendedor", $part["paisVendedor"]));
                                        $parte->appendChild($domtree->createElement("tasaAdvalorem", $part["tasaAdvalorem"]));
                                        if (isset($part["tlc"])) {
                                            if ($part["tlc"] == 'S') {
                                                $tlc = 'S';
                                            } else {
                                                $tlc = '';
                                            }
                                        }
                                        if (isset($part["prosec"])) {
                                            if ($part["prosec"] == 'S') {
                                                $prosec = 'S';
                                            } else {
                                                $prosec = '';
                                            }
                                        }
                                        $parte->appendChild($domtree->createElement("tlc", $tlc));
                                        $parte->appendChild($domtree->createElement("prosec", $prosec));
                                        if (isset($part["patenteOriginal"])) {
                                            $parte->appendChild($domtree->createElement("patenteOriginal", $part["patenteOriginal"]));
                                            $parte->appendChild($domtree->createElement("aduanaOriginal", $part["aduanaOriginal"]));
                                            $parte->appendChild($domtree->createElement("pedimentoOriginal", $part["pedimentoOriginal"]));
                                            $parte->appendChild($domtree->createElement("regimenOriginal", $part["regimenOriginal"]));
                                            $parte->appendChild($domtree->createElement("cantidadOriginal", $part["cantidadOriginal"]));
                                            $parte->appendChild($domtree->createElement("unidadOriginal", $part["unidadOriginal"]));
                                            $parte->appendChild($domtree->createElement("fechaOriginal", $part["fechaOriginal"]));
                                        }
                                    }
                                }
                            }
                        }
                    } else {
                        $root = $domtree->createElement("errores");
                        $xmlRoot = $domtree->appendChild($root);
                        $xmlRoot->appendChild($domtree->createElement("error", "No se encontro información para el pedimento {$input->patente}-{$input->aduana}-{$input->pedimento}."));
                    }
                }
                $output = $domtree->saveXML();
                Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer')->setNoRender(true);
                Zend_Layout::getMvcInstance()->disableLayout();
                $this->_response->setHeader("Content-Type", "text/xml; charset=utf-8")
                    ->setBody($output);
            } else {
                throw new Exception("Invalid input");
            }
        } catch (Zend_Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    /**
     * https://192.168.0.246/webservice/index/campa-detalle-pedimento-html?patente=3589&aduana=640&pedimento=5000713
     * https://187.188.159.44/webservice/index/campa-detalle-pedimento-html?patente=3589&aduana=640&pedimento=5000713
     *
     * @return boolean
     */
    public function campaDetallePedimentoHtmlAction()
    {
        $gets = $this->_request->getParams();
        if (isset($gets['patente']) && isset($gets["aduana"]) && isset($gets["pedimento"])) {
            try {
                if ($gets['aduana'] == '640') {
                    $sitawin = new OAQ_Sitawin(true, '192.168.200.5', 'sa', 'adminOAQ123', 'SITAW3010640', 1433, 'Pdo_Mssql');
                } elseif ($gets['aduana'] == '646') {
                    $sitawin = new OAQ_Sitawin(true, '192.168.0.253', 'sa', 'sqlcointer', 'SITAW3589640', 1433, 'Pdo_Mssql');
                } elseif ($gets['aduana'] == '240') {
                    $sitawin = new OAQ_Sitawin(true, '192.168.200.5', 'sa', 'adminOAQ123', 'SITAW3589240', 1433, 'Pdo_Mssql');
                }
                $data = $sitawin->wsDetallePedimento($gets["pedimento"]);
                if (empty($data)) {
                    if ($gets['aduana'] == '240') {
                        $sitawin = new OAQ_AduanetM3(true, 'localhost', 'root', 'mysql11!', 'SAAIWEB', 3306);
                        $data = $sitawin->wsDetallePedimento($gets["pedimento"]);
                    }
                }
            } catch (Exception $e) {
                Zend_Debug::dump($e->getMessage());
                return false;
            }
            if (isset($data) && !empty($data)) {
                echo '<!doctype html>'
                    . '<html lang="en">'
                    . '<head>'
                    . '<meta charset="utf-8">'
                    . '<title>Detalle pedimento</title>'
                    . '</head>'
                    . "<style>body {margin:0;padding:0; font-family:sans-serif;}"
                    . "table {border-collapse:collapse; }"
                    . "table th, table td {font-size: 12px; border: 1px #555 solid; padding: 2px 5px;}"
                    . "table th {background: #f1f1f1; font-weight: bold;}"
                    . "h3 {"
                    . "margin: 0; padding:0;"
                    . "}"
                    . "</style>"
                    . "</head>"
                    . "<body>";
                $html = '<table>';
                $array = array("Patente", "Aduana", "Pedimento", "Trafico", "TipoOperacion", "TransporteEntrada", "TransporteArribo", "TransporteSalida", "FechaEntrada", "FechaPago", "FirmaValidacion", "FirmaBanco", "TipoCambio", "CvePed", "Regimen", "AduanaEntrada", "ValorDolares", "ValorAduana", "Fletes", "Seguros", "Embalajes", "OtrosIncrementales", "DTA", "IVA", "IGI", "PREV", "CNT", "TotalEfectivo", "PesoBruto", "Bultos", "Guias", "BL", "NumFactura", "Cove", "FechaFactura", "Incoterm", "ValorFacturaUsd", "ValorFacturaMonExt", "NomProveedor", "PaisFactura", "TaxId", "Divisa", "FactorMonExt", "NumParte", "Descripcion", "Fraccion", "OrdenFraccion", "ValorMonExt", "PrecioUnitario", "CantUMC", "UMC", "CantUMT", "UMT", "PaisOrigen", "PaisVendedor", "TasaAdvalorem", "TLC", "PROSEC", "PatenteOrig", "AduanaOrig", "PedimentoOrig", "CantidadOriginal", "UnidadOriginal", "FechaOriginal");
                $html .= "<tr>";
                foreach ($array as $item) {
                    $html .= $this->_thValue($item);
                }
                $html .= "</tr>";
                $html .= "<tr>";
                if (isset($data["facturas"])) {
                    foreach ($data['facturas'] as $invoice) {
                        if (isset($invoice["partes"]) && !empty($invoice["partes"])) {
                            foreach ($invoice["partes"] as $part) {
                                $html .= $this->_tdValue($data['patente']);
                                $html .= $this->_tdValue($data['aduana']);
                                $html .= $this->_tdValue($data['pedimento']);
                                $html .= $this->_tdValue($data['referencia']);
                                $html .= $this->_tdValue($data['tipoOperacion']);
                                $html .= $this->_tdValue($data['transporteEntrada']);
                                $html .= $this->_tdValue($data['transporteArribo']);
                                $html .= $this->_tdValue($data['transporteSalida']);
                                $html .= $this->_tdValue($data['fechaEntrada']);
                                $html .= $this->_tdValue($data['fechaPago']);
                                $html .= $this->_tdValue($data['firmaValidacion']);
                                $html .= $this->_tdValue($data['firmaBanco']);
                                $html .= $this->_tdValue($data['tipoCambio']);
                                $html .= $this->_tdValue($data['cvePed']);
                                $html .= $this->_tdValue($data['regimen']);
                                $html .= $this->_tdValue($data['aduanaEntrada']);
                                $html .= $this->_tdValue($data['valorDolares']);
                                $html .= $this->_tdValue($data['valorAduana']);
                                $html .= $this->_tdValue($data['fletes']);
                                $html .= $this->_tdValue($data['seguros']);
                                $html .= $this->_tdValue($data['embalajes']);
                                $html .= $this->_tdValue($data['otrosIncrementales']);
                                $html .= $this->_tdValue($data['dta']);
                                $html .= $this->_tdValue($data['iva']);
                                $html .= $this->_tdValue($data['igi']);
                                $html .= $this->_tdValue($data['prev']);
                                $html .= $this->_tdValue($data['cnt']);
                                $html .= $this->_tdValue($data['totalEfectivo']);
                                $html .= $this->_tdValue($data['pesoBruto']);
                                $html .= $this->_tdValue($data['bultos']);
                                $html .= $this->_tdValue("");
                                $html .= $this->_tdValue("");
                                $html .= $this->_tdValue($invoice["numFactura"]);
                                $html .= $this->_tdValue(htmlentities(utf8_decode(trim($invoice["cove"])), ENT_QUOTES, 'UTF-8'));
                                $html .= $this->_tdValue(htmlentities(utf8_decode(trim($invoice["fechaFactura"])), ENT_QUOTES, 'UTF-8'));
                                $html .= $this->_tdValue(htmlentities(utf8_decode(trim($invoice["incoterm"])), ENT_QUOTES, 'UTF-8'));
                                $html .= $this->_tdValue($invoice["valorFacturaUsd"]);
                                $html .= $this->_tdValue($invoice["valorFacturaMonExt"]);
                                $html .= $this->_tdValue(htmlentities(utf8_decode(trim($invoice["nomProveedor"])), ENT_QUOTES, 'UTF-8'));
                                $html .= $this->_tdValue($invoice["paisFactura"]);
                                $html .= $this->_tdValue($invoice["taxId"]);
                                $html .= $this->_tdValue($invoice["divisa"]);
                                $html .= $this->_tdValue($invoice["factorMonExt"]);
                                $html .= $this->_tdValue($part["numParte"]);
                                $html .= $this->_tdValue(htmlentities($part["descripcion"]));
                                $html .= $this->_tdValue($part["fraccion"]);
                                $html .= $this->_tdValue($part["ordenFraccion"]);
                                $html .= $this->_tdValue($part["valorMonExt"]);
                                $html .= $this->_tdValue($part["precioUnitario"]);
                                $html .= $this->_tdValue($part["cantUmc"]);
                                $html .= $this->_tdValue($part["umc"]);
                                $html .= $this->_tdValue($part["cantUmt"]);
                                $html .= $this->_tdValue($part["umt"]);
                                $html .= $this->_tdValue($part["paisOrigen"]);
                                $html .= $this->_tdValue($part["paisVendedor"]);
                                $html .= $this->_tdValue($part["tasaAdvalorem"]);
                                $html .= $this->_tdValue($part["tlc"]);
                                $html .= $this->_tdValue($part["prosec"]);
                                if (isset($part["patenteOriginal"])) {
                                    $html .= $this->_tdValue($part["patenteOriginal"]);
                                    $html .= $this->_tdValue($part["aduanaOriginal"]);
                                    $html .= $this->_tdValue($part["pedimentoOriginal"]);
                                    $html .= $this->_tdValue($part["regimenOriginal"]);
                                    $html .= $this->_tdValue($part["cantidadOriginal"]);
                                    $html .= $this->_tdValue($part["unidadOriginal"]);
                                    $html .= $this->_tdValue($part["fechaOriginal"]);
                                } else {
                                    $html .= $this->_tdValue("");
                                    $html .= $this->_tdValue("");
                                    $html .= $this->_tdValue("");
                                    $html .= $this->_tdValue("");
                                    $html .= $this->_tdValue("");
                                    $html .= $this->_tdValue("");
                                    $html .= $this->_tdValue("");
                                }
                                $html .= '</tr>';
                            }
                        }
                    }
                }
                echo $html . '</table></body></html>';
            }
        }
    }

    public function campaDetallePedimentoHtmlPrintAction()
    {
        // https://187.188.159.44/webservice/index/campa-detalle-pedimento-html-print/aduana/240/patente/3589/pedimento/5002950
        $gets = $this->_request->getParams();
        if (isset($gets['patente']) && isset($gets["aduana"]) && isset($gets["pedimento"])) {
            try {
                if ($gets['aduana'] == '640') {
                    $sitawin = new OAQ_Sitawin(true, '192.168.200.5', 'sa', 'adminOAQ123', 'SITAW3010640', 1433, 'Pdo_Mssql');
                } elseif ($gets['aduana'] == '646') {
                    $sitawin = new OAQ_Sitawin(true, '192.168.0.253', 'sa', 'sqlcointer', 'SITAW3589640', 1433, 'Pdo_Mssql');
                } elseif ($gets['aduana'] == '240') {
                    $sitawin = new OAQ_Sitawin(true, '192.168.200.5', 'sa', 'adminOAQ123', 'SITAW3589240', 1433, 'Pdo_Mssql');
                }
                $data = $sitawin->wsDetallePedimento($gets["pedimento"]);
                if (empty($data) || $data == false) {
                    if ($gets['aduana'] == '240') {
                        $sitawin = new OAQ_AduanetM3(true, 'localhost', 'root', 'mysql11!', 'SAAIWEB', 3306);
                        $data = $sitawin->wsDetallePedimento($gets["pedimento"]);
                        $historic = true;
                    }
                }
            } catch (Exception $e) {
                Zend_Debug::dump($e->getMessage());
                return false;
            }
            if (isset($data) && !empty($data)) {
                echo '<!doctype html>'
                    . '<html lang="en">'
                    . '<head>'
                    . '<meta charset="utf-8">'
                    . '<title>Detalle pedimento</title>'
                    . '</head>'
                    . "<style>body {margin:0;padding:0; font-family:sans-serif;}"
                    . "table {border-collapse:collapse; width: 100%;}"
                    . "table.invoices {padding: 0; margin:0;}"
                    . "table th, table td {font-size: 12px; border: 1px #aaa solid; padding: 2px 5px;}"
                    . "table th {background: #f1f1f1; font-weight: bold;}"
                    . "table td.tdinvoice, td.tdparts {padding: 0; border: 0; margin: 0;}"
                    . "table.parts th {background-color: #f3f3f3; border: 1px #aaa solid;}"
                    . "table.parts td {border: 0; border: 1px #aaa solid;}"
                    . "h3 {margin: 0; padding:0;}"
                    . "</style>"
                    . "</head>"
                    . "<body>";
                $html = '<table>';
                $html .= "<tr><th colspan=\"6\">ENCABEZADO</th></tr>";
                $html .= "<tr><th>Patente</th><th>Aduana</th><th>Pedimento</th><th>Referencia</th><th>Tipo Operación</th><th>Aduana Entrada</th></tr>";
                $html .= "<tr>{$this->_tdValue($data['patente'])}{$this->_tdValue($data['aduana'])}{$this->_tdValue($data['pedimento'])}{$this->_tdValue($data['referencia'])}{$this->_tdValue($data['tipoOperacion'])}{$this->_tdValue($data['aduanaEntrada'])}</tr>";
                $html .= "<tr><th>Transporte Entrada</th><th>Transporte Entrada</th><th>Transporte Salida</th><th>Fecha Entrada</th><th>Fecha Pago</th><th>Valor Dolares</th></tr>";
                $html .= "<tr>{$this->_tdValue($data['transporteEntrada'])}{$this->_tdValue($data['transporteEntrada'])}{$this->_tdValue($data['transporteSalida'])}{$this->_tdValue($data['fechaEntrada'])}{$this->_tdValue($data['fechaPago'])}{$this->_tdValue($data['valorDolares'])}</tr>";
                $html .= "<tr><th>Firma Validación</th><th>Firma Banco</th><th>Tipo Cambio</th><th>Cve. Pedimento</th><th>Regimen</th><th>Valor Aduana</th></tr>";
                $html .= "<tr>{$this->_tdValue($data['firmaValidacion'])}{$this->_tdValue($data['firmaBanco'])}{$this->_tdValue($data['tipoCambio'])}{$this->_tdValue($data['cvePed'])}{$this->_tdValue($data['regimen'])}{$this->_tdValue($data['valorAduana'])}</tr>";
                $html .= "<tr><th>Fletes</th><th>Seguros</th><th>Embalajes</th><th>Otros Inc.</th><th>DTA</th><th>IVA</th></tr>";
                $html .= "<tr>{$this->_tdValue($data['fletes'])}{$this->_tdValue($data['seguros'])}{$this->_tdValue($data['embalajes'])}{$this->_tdValue($data['otrosIncrementales'])}{$this->_tdValue($data['dta'])}{$this->_tdValue($data['iva'])}</tr>";
                $html .= "<tr><th>IGI</th><th>PREV</th><th>CNT</th><th>Total Efectivo</th><th>Peso Bruto</th><th>Bultos</th></tr>";
                $html .= "<tr>{$this->_tdValue($data['igi'])}{$this->_tdValue($data['prev'])}{$this->_tdValue($data['cnt'])}{$this->_tdValue($data['totalEfectivo'])}{$this->_tdValue($data['pesoBruto'])}{$this->_tdValue($data['bultos'])}</tr>";
                $html .= "<tr><th colspan=\"6\" style=\"border-bottom: 0\">FACTURAS</th></tr>";
                if (isset($data["facturas"])) {
                    foreach ($data['facturas'] as $invoice) {
                        $html .= "<tr><td colspan=\"6\" class=\"tdinvoice\">";
                        $html .= '<table class=\"invoices\">';
                        $html .= "<tr><th>Num. Factura</th><th>Cove</th><th>Fecha Factura</th><th>Incoterm</th><th>Valor USD</th><th>Valor Mon. Ext.</th></tr>";
                        $html .= "<tr>{$this->_tdValue($invoice["numFactura"])}{$this->_tdValue($invoice["cove"])}{$this->_tdValue($invoice["fechaFactura"])}{$this->_tdValue($invoice["incoterm"])}{$this->_tdValue($invoice["valorFacturaUsd"])}{$this->_tdValue($invoice["valorFacturaMonExt"])}</tr>";
                        $html .= "<tr><th>Nom Prov.</th><th>Pais Fact.</th><th>Tax Id</th><th>Divisa</th><th colspan=\"2\">Factor Mon.Ext.</th></tr>";
                        $html .= "<tr>{$this->_tdValue(htmlentities(utf8_decode(trim($invoice["nomProveedor"])), ENT_QUOTES, 'UTF-8'), 2)}{$this->_tdValue($invoice["paisFactura"])}{$this->_tdValue($invoice["taxId"])}{$this->_tdValue($invoice["divisa"])}{$this->_tdValue($invoice["factorMonExt"])}</tr>";
                        if (isset($invoice["partes"]) && !empty($invoice["partes"])) {
                            $html .= "<tr><th>Orden</th><th>Num. Parte</th><th>Fracción</th><th>Descripción</th><th>Precio Unitario</th><th>Val. Mon Ext.</th></tr>";
                            foreach ($invoice["partes"] as $part) {
                                if (!isset($historic)) {
                                    $html .= "<tr>{$this->_tdValue($part["ordenFraccion"], null, 2)}{$this->_tdValue(strtoupper($part["numParte"]))}{$this->_tdValue(htmlentities($part["fraccion"]))}{$this->_tdValue(utf8_encode(strtoupper($part["descripcion"])))}{$this->_tdValue($part["precioUnitario"])}{$this->_tdValue($part["valorMonExt"])}</tr>";
                                } else {
                                    $html .= "<tr>{$this->_tdValue($part["ordenFraccion"], null, 2)}{$this->_tdValue(strtoupper($part["numParte"]))}{$this->_tdValue(htmlentities($part["fraccion"]))}{$this->_tdValue(utf8_encode(strtoupper($part["descripcion"])))}" . $this->_tdValue($data['tipoCambio'] * $part["precioUnitario"]) . "{$this->_tdValue($part["valorMonExt"])}</tr>";
                                }
                                $html .= "<tr><td colspan=\"5\" class=\"tdparts\">";
                                $html .= '<table class="parts">';
                                $html .= "<tr><th>Cant. Umc</th>{$this->_tdValue($part["cantUmc"])}<th>UMC</th>{$this->_tdValue($part["umc"])}<th>Cant. Umt</th>{$this->_tdValue($part["cantUmt"])}<th>UMT</th>{$this->_tdValue($part["umt"])}<th>Pais Origen</th>{$this->_tdValue($part["paisOrigen"])}<th>Pais Vendedor</th>{$this->_tdValue($part["paisVendedor"])}<th>Tasa Adv.</th>{$this->_tdValue($part["tasaAdvalorem"])}<th>TLC</th>{$this->_tdValue($part["tlc"])}<th>PROSEC</th>{$this->_tdValue($part["prosec"])}</tr>";
                                if (isset($part["patenteOriginal"])) {
                                    $html .= "<tr><th colspan=\"3\">Patente Original</th>{$this->_tdValue($part["patenteOriginal"])}<th colspan=\"2\">Aduana Original</th>{$this->_tdValue($part["aduanaOriginal"])}<th colspan=\"3\">Pedimento. Original</th>{$this->_tdValue($part["pedimentoOriginal"])}<th colspan=\"2\">Regimen Original</th>{$this->_tdValue($part["regimenOriginal"])}<th colspan=\"3\">Fecha Original</th>{$this->_tdValue($part["fechaOriginal"])}</tr>";
                                }
                                $html .= '</table>';
                                $html .= "</td></tr>";
                            }
                        }
                        $html .= '</table>';
                        $html .= "</td></tr>";
                    }
                }
                if (isset($html)) {
                    echo $html . '</table></body></html>';
                    return false;
                } else {
                    return false;
                }
            }
        }
    }

    public function descargaCoveAction()
    {
        try {
            $cove = $this->_request->getParam('cove', null);
            $token = $this->_request->getParam('token', null);
            if (isset($cove) && isset($token)) {
                if ($this->_validateToken($token, $cove) !== true) {
                    return false;
                }
                if ($token === sha1($this->_salt . $cove . $this->_pepper)) {
                    $vucemSol = new Clientes_Model_CovesMapper();
                    $xml = $vucemSol->obtenerSolicitudPorCove($cove);
                    header("Content-Type:text/xml");
                    echo $this->_cleanXml($xml["xml"]);
                } else {
                    $domtree = new DOMDocument('1.0', 'UTF-8');
                    $root = $domtree->createElement("errors");
                    $xmlRoot = $domtree->appendChild($root);
                    $xmlRoot->appendChild($domtree->createElement("error", "Invalid token."));
                    $output = $domtree->saveXML();
                    Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer')->setNoRender(true);
                    Zend_Layout::getMvcInstance()->disableLayout();
                    $this->_response->setHeader('Content-Type', 'text/xml; charset=utf-8')
                        ->setBody($output);
                }
            } else {
                $domtree = new DOMDocument('1.0', 'UTF-8');
                $root = $domtree->createElement("errors");
                $xmlRoot = $domtree->appendChild($root);
                $xmlRoot->appendChild($domtree->createElement("error", "Insuficient data."));
                $output = $domtree->saveXML();
                Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer')->setNoRender(true);
                Zend_Layout::getMvcInstance()->disableLayout();
                $this->_response->setHeader('Content-Type', 'text/xml; charset=utf-8')
                    ->setBody($output);
            }
        } catch (Exception $ex) {
            echo $ex->getMessage();
        }
    }

    protected function _cleanXml($xml)
    {
        return preg_replace('#<soapenv:Header(.*?)>(.*?)</soapenv:Header>#is', '', $xml);
    }

    public function campaDetallePedimentoCsvAction()
    {
        // /webservice/index/campa-detalle-pedimento-html?patente=3589&aduana=640&pedimento=5000713
        // /webservice/index/campa-detalle-pedimento-html?patente=3589&aduana=640&pedimento=5000713
        $gets = $this->_request->getParams();
        if (isset($gets['patente']) && isset($gets["aduana"]) && isset($gets["pedimento"])) {
            try {
                if ($gets['aduana'] == '640') {
                    $sitawin = new OAQ_Sitawin(true, '192.168.200.5', 'sa', 'adminOAQ123', 'SITAW3010640', 1433, 'Pdo_Mssql');
                } elseif ($gets['aduana'] == '646') {
                    $sitawin = new OAQ_Sitawin(true, '192.168.0.253', 'sa', 'sqlcointer', 'SITAW3589640', 1433, 'Pdo_Mssql');
                } elseif ($gets['aduana'] == '240') {
                    $sitawin = new OAQ_Sitawin(true, '192.168.200.5', 'sa', 'adminOAQ123', 'SITAW3589240', 1433, 'Pdo_Mssql');
                }
                $data = $sitawin->wsDetallePedimento($gets["pedimento"]);
                if (empty($data)) {
                    if ($gets['aduana'] == '240') {
                        $sitawin = new OAQ_AduanetM3(true, 'localhost', 'root', 'mysql11!', 'SAAIWEB', 3306);
                        $data = $sitawin->wsDetallePedimento($gets["pedimento"]);
                        $historic = true;
                    }
                }
            } catch (Exception $e) {
                Zend_Debug::dump($e->getMessage());
                return false;
            }
            if (isset($data) && !empty($data)) {
                $array = array("Patente", "Aduana", "Pedimento", "Trafico", "TipoOperacion", "TransporteEntrada", "TransporteArribo", "TransporteSalida", "FechaEntrada", "FechaPago", "FirmaValidacion", "FirmaBanco", "TipoCambio", "CvePed", "Regimen", "AduanaEntrada", "ValorDolares", "ValorAduana", "Fletes", "Seguros", "Embalajes", "OtrosIncrementales", "DTA", "IVA", "IGI", "PREV", "CNT", "TotalEfectivo", "PesoBruto", "Bultos", "Guias", "BL", "NumFactura", "Cove", "FechaFactura", "Incoterm", "ValorFacturaUsd", "ValorFacturaMonExt", "NomProveedor", "PaisFactura", "TaxId", "Divisa", "FactorMonExt", "NumParte", "Descripcion", "Fraccion", "OrdenFraccion", "ValorMonExt", "CantUMC", "UMC", "CantUMT", "UMT", "PaisOrigen", "PaisVendedor", "TasaAdvalorem", "TLC", "PROSEC", "PatenteOrig", "AduanaOrig", "PedimentoOrig", "CantidadOriginal", "UnidadOriginal", "FechaOriginal");
                $html .= "";
                foreach ($array as $item) {
                    $html .= $this->_stringValue($item);
                }
                $html .= "\n";
                if (isset($data["facturas"])) {
                    foreach ($data['facturas'] as $invoice) {
                        if (isset($invoice["partes"]) && !empty($invoice["partes"])) {
                            foreach ($invoice["partes"] as $part) {
                                $html .= $this->_stringValue($data['patente']);
                                $html .= $this->_stringValue($data['aduana']);
                                $html .= $this->_stringValue($data['pedimento']);
                                $html .= $this->_stringValue($data['referencia']);
                                $html .= $this->_stringValue($data['tipoOperacion']);
                                $html .= $this->_stringValue($data['transporteEntrada']);
                                $html .= $this->_stringValue($data['transporteArribo']);
                                $html .= $this->_stringValue($data['transporteSalida']);
                                $html .= $this->_stringValue($data['fechaEntrada']);
                                $html .= $this->_stringValue($data['fechaPago']);
                                $html .= $this->_stringValue($data['firmaValidacion']);
                                $html .= $this->_stringValue($data['firmaBanco']);
                                $html .= $this->_stringValue($data['tipoCambio']);
                                $html .= $this->_stringValue($data['cvePed']);
                                $html .= $this->_stringValue($data['regimen']);
                                $html .= $this->_stringValue($data['aduanaEntrada']);
                                $html .= $this->_stringValue($data['valorDolares']);
                                $html .= $this->_stringValue($data['valorAduana']);
                                $html .= $this->_stringValue($data['fletes']);
                                $html .= $this->_stringValue($data['seguros']);
                                $html .= $this->_stringValue($data['embalajes']);
                                $html .= $this->_stringValue($data['otrosIncrementales']);
                                $html .= $this->_stringValue($data['dta']);
                                $html .= $this->_stringValue($data['iva']);
                                $html .= $this->_stringValue($data['igi']);
                                $html .= $this->_stringValue($data['prev']);
                                $html .= $this->_stringValue($data['cnt']);
                                $html .= $this->_stringValue($data['totalEfectivo']);
                                $html .= $this->_stringValue($data['pesoBruto']);
                                $html .= $this->_stringValue($data['bultos']);
                                $html .= $this->_stringValue($invoice["numFactura"]);
                                $html .= $this->_stringValue(htmlentities(utf8_decode(trim($invoice["cove"])), ENT_QUOTES, 'UTF-8'));
                                $html .= $this->_stringValue(htmlentities(utf8_decode(trim($invoice["fechaFactura"])), ENT_QUOTES, 'UTF-8'));
                                $html .= $this->_stringValue(htmlentities(utf8_decode(trim($invoice["incoterm"])), ENT_QUOTES, 'UTF-8'));
                                $html .= $this->_stringValue($invoice["valorFacturaUsd"]);
                                $html .= $this->_stringValue($invoice["valorFacturaMonExt"]);
                                $html .= $this->_stringValue(htmlentities(utf8_decode(trim($invoice["nomProveedor"])), ENT_QUOTES, 'UTF-8'));
                                $html .= $this->_stringValue($invoice["paisFactura"]);
                                $html .= $this->_stringValue($invoice["taxId"]);
                                $html .= $this->_stringValue($invoice["divisa"]);
                                $html .= $this->_stringValue($invoice["factorMonExt"]);
                                $html .= $this->_stringValue(strtoupper($part["numParte"]));
                                $html .= $this->_stringValue(htmlentities(strtoupper($part["descripcion"])));
                                $html .= $this->_stringValue($part["fraccion"]);
                                $html .= $this->_stringValue($part["ordenFraccion"]);
                                $html .= $this->_stringValue($part["valorMonExt"]);
                                if (!isset($historic)) {
                                    $html .= $this->_stringValue($part["precioUnitario"]);
                                } else {
                                    $html .= $this->_stringValue($part["precioUnitario"] * $data['tipoCambio']);
                                }
                                $html .= $this->_stringValue($part["cantUmc"]);
                                $html .= $this->_stringValue($part["umc"]);
                                $html .= $this->_stringValue($part["cantUmt"]);
                                $html .= $this->_stringValue($part["umt"]);
                                $html .= $this->_stringValue($part["paisOrigen"]);
                                $html .= $this->_stringValue($part["paisVendedor"]);
                                $html .= $this->_stringValue($part["tasaAdvalorem"]);
                                $html .= $this->_stringValue($part["tlc"]);
                                $html .= $this->_stringValue($part["prosec"]);
                                if (isset($part["patenteOriginal"])) {
                                    $html .= $this->_stringValue($part["patenteOriginal"]);
                                    $html .= $this->_stringValue($part["aduanaOriginal"]);
                                    $html .= $this->_stringValue($part["pedimentoOriginal"]);
                                    $html .= $this->_stringValue($part["regimenOriginal"]);
                                    $html .= $this->_stringValue($part["cantidadOriginal"]);
                                    $html .= $this->_stringValue($part["unidadOriginal"]);
                                    $html .= $this->_stringValue($part["fechaOriginal"]);
                                } else {
                                    $html .= $this->_stringValue("");
                                    $html .= $this->_stringValue("");
                                    $html .= $this->_stringValue("");
                                    $html .= $this->_stringValue("");
                                    $html .= $this->_stringValue("");
                                    $html .= $this->_stringValue("");
                                    $html .= $this->_stringValue("");
                                }
                                $html .= "\n";
                            }
                        }
                    }
                }
                if (isset($html)) {
                    echo $html;
                } else {
                    return false;
                }
            }
        }
    }

    protected function _tdValue($value, $colspan = null, $rowspan = null)
    {
        if (isset($colspan)) {
            return '<td colspan="' . $colspan . '">' . (string) $value . '</td>';
        }
        if (isset($rowspan)) {
            return '<td rowspan="' . $rowspan . '">' . (string) $value . '</td>';
        }
        return '<td>' . (string) $value . '</td>';
    }

    protected function _thValue($value, $colspan = null, $rowspan = null)
    {
        if (isset($colspan)) {
            return '<th colspan="' . $colspan . '">' . (string) $value . '</th>';
        }
        if (isset($rowspan)) {
            return '<th rowspan="' . $rowspan . '">' . (string) $value . '</th>';
        }
        return '<th>' . (string) $value . '</th>';
    }

    protected function _stringValue($value)
    {
        return '"' . (string) $value . '",';
    }

    /**
     * /webservice/index/consulta-trafico?token=a1e3fff3f24e476932cc4739c6d127cdeb33513d&rfc=PEM930903SH4&fechaIni=2015-08-02&fechaFin=2015-08-30
     * /webservice/index/campa-pedimentos-pagados?token=a1e3fff3f24e476932cc4739c6d127cdeb33513d&rfc=PEM930903SH4&fechaIni=2015-08-02&fechaFin=2015-08-30&aduana=646&patente=3589
     *
     * @return boolean
     */
    public function consultaTraficoAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "rfc" => "StringToUpper",
                "token" => "StringToLower",
            );
            $v = array(
                "rfc" => array("NotEmpty", new Zend_Validate_Regex("/^[A-Z]{3,4}([0-9]{2})([0-9]{2})([0-9]{2})?[A-Z0-9]{3,4}/")),
                "fechaIni" => array("NotEmpty", new Zend_Validate_Regex("/^\d{4}\-\d{2}\-\d{2}$/")),
                "fechaFin" => array("NotEmpty", new Zend_Validate_Regex("/^\d{4}\-\d{2}\-\d{2}$/")),
                "token" => array("NotEmpty", new Zend_Validate_Regex("/^[a-z0-9]+$/")),
            );
            $i = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($i->isValid("rfc") && $i->isValid("fechaIni") && $i->isValid("fechaFin") && $i->isValid("token")) {
                $row = new Webservice_Model_Table_WsTokens(array("rfc" => $i->rfc));
                $table = new Webservice_Model_WsTokens();
                $table->find($row);
                if (null === ($row->getId())) {
                    $errorFlag[] = array(
                        "param" => "customer",
                        "error" => "Customer not found!",
                    );
                } else {
                    if ($row->getToken() !== $i->token) {
                        $errorFlag[] = array(
                            "param" => "token",
                            "error" => "Invalid token!",
                        );
                    }
                }
                $tbl = new Trafico_Model_TraficosMapper();
                $mdl = new Trafico_Model_TraficoFechasMapper();
                $inv = new Trafico_Model_TraficoFacturasMapper();
                $data = $tbl->consultaPorRfc($i->rfc, $i->fechaIni, $i->fechaFin);
                if (isset($data) && is_array($data) && !isset($errorFlag)) {
                    $domtree = new DOMDocument("1.0", "UTF-8");
                    $root = $domtree->createElement("operaciones");
                    $root->setAttribute('cantidad', count($data));
                    $xmlRoot = $domtree->appendChild($root);
                    foreach ($data as $item) {
                        if (APPLICATION_ENV == "production") {
                            $url = "https://oaq.dnsalias.net";
                        } else if (APPLICATION_ENV == "staging") {
                            $url = "http://192.168.0.191";
                        } else {
                            $url = $this->_config->app->url;
                        }
                        $pedimento = $domtree->createElement("referencia");
                        $pedimento->setAttribute("patente", $item["patente"]);
                        $pedimento->setAttribute("pedimento", $item["pedimento"]);
                        $pedimento->setAttribute("referencia", $item["referencia"]);
                        $pedimento->setAttribute("aduana", $item["aduana"]);
                        $pedimento->setAttribute("tipoOpe", $item["ie"]);
                        $fechas = $mdl->obtenerFechas($item['id']);
                        if (isset($fechas) && is_array($fechas)) {
                            $trafico = $pedimento->appendChild($domtree->createElement("trafico"));
                            foreach ($fechas as $k => $v) {
                                if ($k == 1) {
                                    $trafico->appendChild($domtree->createElement("fechaEntrada", $v));
                                } elseif ($k == 2) {
                                    $trafico->appendChild($domtree->createElement("fechaPago", $v));
                                    $trafico->setAttribute("detalle", $url . "/webservice/index/detalle-pedimento-enh?rfc={$i->rfc}&patente={$item["patente"]}&aduana={$item["aduana"]}&pedimento={$item["pedimento"]}&token=" . sha1($this->_salt . $item["pedimento"] . $this->_pepper));
                                    $trafico->setAttribute("anexo", $url . "/webservice/index/campa-detalle-pedimento?patente={$item["patente"]}&aduana={$item["aduana"]}&pedimento={$item["pedimento"]}");
                                } elseif ($k == 8) {
                                    $trafico->appendChild($domtree->createElement("fechaLiberacion", $v));
                                } elseif ($k == 9) {
                                    $trafico->appendChild($domtree->createElement("fechaNotificacion", $v));
                                } elseif ($k == 10) {
                                    $trafico->appendChild($domtree->createElement("fechaArribo", $v));
                                } elseif ($k == 11) {
                                    $trafico->appendChild($domtree->createElement("fechaRecepcionDocumentos", $v));
                                }
                            }
                        }
                        $facturas = $inv->obtenerFacturasWs($item['id']);
                        if (isset($facturas) && is_array($facturas)) {
                            $facts = $pedimento->appendChild($domtree->createElement("facturas"));
                            foreach ($facturas as $fact) {
                                $invoice = $facts->appendChild($domtree->createElement("factura"));
                                $invoice->setAttribute("url", $url . "webservice/index/desglose-facturas?patente={$item["patente"]}&aduana={$item["aduana"]}&pedimento={$item["pedimento"]}&referencia={$item["referencia"]}&numFactura=" . urlencode($fact["numFactura"]) . "&token=" . sha1($this->_salt . urlencode($fact["numFactura"]) . $this->_pepper));
                                $invoice->appendChild($domtree->createElement("numFactura", $fact["numFactura"]));
                                $invoice->appendChild($domtree->createElement("proveedor", $fact["proveedor"]));
                                $invoice->appendChild($domtree->createElement("moneda", $fact["moneda"]));
                                $invoice->appendChild($domtree->createElement("valorMonetaExtranjera", $fact["valorMonExt"]));
                            }
                        }
                        $xmlRoot->appendChild($pedimento);
                    }
                    $output = $domtree->saveXML();
                    Zend_Controller_Action_HelperBroker::getStaticHelper("viewRenderer")->setNoRender(true);
                    Zend_Layout::getMvcInstance()->disableLayout();
                    $this->_response->setHeader("Content-Type", "text/xml; charset=utf-8")
                        ->setBody($output);
                    return;
                } else {
                    $errorFlag[] = array(
                        "param" => "data",
                        "error" => "No data found on system!",
                    );
                }
            } else {
                $errorFlag[] = array(
                    "param" => "input",
                    "error" => "Invalid input!",
                );
            }
            if (isset($errorFlag)) {
                $domtree = new DOMDocument("1.0", "UTF-8");
                $xmlRoot = $domtree->appendChild($domtree->createElement("pedimentos"));
                $errors = $domtree->createElement("errores");
                $addErrorsChild = $xmlRoot->appendChild($errors);
                foreach ($errorFlag as $item) {
                    $addErrorsChild->appendChild($domtree->createElement($item["param"], $item["error"]));
                }
                $output = $domtree->saveXML();
                Zend_Controller_Action_HelperBroker::getStaticHelper("viewRenderer")->setNoRender(true);
                Zend_Layout::getMvcInstance()->disableLayout();
                $this->_response->setHeader("Content-Type", "text/xml; charset=utf-8")
                    ->setBody($output);
            }
        } catch (Exception $ex) {
            echo $ex->getMessage();
        }
    }

    /**
     * /webservice/index/consulta-trafico?token=a1e3fff3f24e476932cc4739c6d127cdeb33513d&rfc=PEM930903SH4&fechaIni=2015-08-02&fechaFin=2015-08-30
     * /webservice/index/consulta-trafico?token=f980bec24d5aedbb011708b7541f898fab7b29c0&rfc=DME960701JX1&fechaIni=2015-08-02&fechaFin=2015-08-30
     *
     * @return boolean
     */
    public function desgloseFacturasAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $domtree = new DOMDocument('1.0', 'UTF-8');
        $xmlRoot = $domtree->appendChild($domtree->createElement("factura"));
        $regex = "/^\d{4}\-\d{2}\-\d{2}$/";
        $token = filter_var($this->_getParam('token', null), FILTER_SANITIZE_STRING);
        $numFactura = filter_var($this->_getParam('numFactura', null), FILTER_SANITIZE_STRING);
        $referencia = filter_var($this->_getParam('referencia', null), FILTER_SANITIZE_STRING);
        $aduana = filter_var($this->_getParam('aduana', null), FILTER_SANITIZE_NUMBER_INT);
        $patente = filter_var($this->_getParam('patente', null), FILTER_SANITIZE_NUMBER_INT);
        $pedimento = filter_var($this->_getParam('pedimento', null), FILTER_SANITIZE_NUMBER_INT);
        try {
            if (isset($patente) && isset($aduana) && isset($pedimento)) {
                $db = $this->_sistemaPedimentos($patente, $aduana);
                if (!isset($db)) {
                    throw new Exception("No DB!");
                }
                $data = $db->wsDesgloseFactura($referencia, $numFactura);
                if (isset($data) && !empty($data)) {
                    $xmlRoot->appendChild($domtree->createElement("taxId", $data["taxId"]));
                    $xmlRoot->appendChild($domtree->createElement("nomProveedor", htmlentities(utf8_decode(trim($data["nomProveedor"])), ENT_QUOTES, 'UTF-8')));
                    $xmlRoot->appendChild($domtree->createElement("numFactura", $data["numFactura"]));
                    $xmlRoot->appendChild($domtree->createElement("incoterm", htmlentities(utf8_decode(trim($data["incoterm"])), ENT_QUOTES, 'UTF-8')));
                    $xmlRoot->appendChild($domtree->createElement("cove", htmlentities(utf8_decode(trim($data["cove"])), ENT_QUOTES, 'UTF-8')));
                    $xmlRoot->appendChild($domtree->createElement("fechaFactura", htmlentities(utf8_decode(trim($data["fechaFactura"])), ENT_QUOTES, 'UTF-8')));
                    $xmlRoot->appendChild($domtree->createElement("valorFacturaUsd", $data["valorFacturaUsd"]));
                    $xmlRoot->appendChild($domtree->createElement("valorFacturaMonExt", $data["valorFacturaMonExt"]));
                    $xmlRoot->appendChild($domtree->createElement("paisFactura", $data["paisFactura"]));
                    $xmlRoot->appendChild($domtree->createElement("divisa", $data["divisa"]));
                    $xmlRoot->appendChild($domtree->createElement("factorMonExt", $data["factorMonExt"]));
                    if (isset($data["partes"]) && !empty($data["partes"])) {
                        $parts = $xmlRoot->appendChild($domtree->createElement("partes"));
                        foreach ($data["partes"] as $part) {
                            $parte = $parts->appendChild($domtree->createElement("parte"));
                            $parte->appendChild($domtree->createElement("numParte", strtoupper($part["numParte"])));
                            $parte->appendChild($domtree->createElement("descripcion", htmlentities($part["descripcion"])));
                            $parte->appendChild($domtree->createElement("fraccion", $part["fraccion"]));
                            $parte->appendChild($domtree->createElement("ordenFraccion", $part["ordenFraccion"]));
                            $parte->appendChild($domtree->createElement("valorMonExt", $part["valorMonExt"]));
                            $parte->appendChild($domtree->createElement("precioUnitario", $part["precioUnitario"]));
                            $parte->appendChild($domtree->createElement("cantUmc", $part["cantUmc"]));
                            $parte->appendChild($domtree->createElement("umc", $part["umc"]));
                            $parte->appendChild($domtree->createElement("cantUmt", $part["cantUmt"]));
                            $parte->appendChild($domtree->createElement("umt", $part["umt"]));
                            $parte->appendChild($domtree->createElement("paisOrigen", $part["paisOrigen"]));
                            $parte->appendChild($domtree->createElement("paisVendedor", $part["paisVendedor"]));
                            $parte->appendChild($domtree->createElement("tasaAdvalorem", $part["tasaAdvalorem"]));
                            if (isset($part["tlc"])) {
                                if ($part["tlc"] == 'S') {
                                    $tlc = 'S';
                                } else {
                                    $tlc = '';
                                }
                            }
                            if (isset($part["prosec"])) {
                                if ($part["prosec"] == 'S') {
                                    $prosec = 'S';
                                } else {
                                    $prosec = '';
                                }
                            }
                            $parte->appendChild($domtree->createElement("tlc", $tlc));
                            $parte->appendChild($domtree->createElement("prosec", $prosec));
                        }
                    }
                    $output = $domtree->saveXML();
                    Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer')->setNoRender(true);
                    Zend_Layout::getMvcInstance()->disableLayout();
                    $this->_response->setHeader('Content-Type', 'text/xml; charset=utf-8')
                        ->setBody($output);
                } else {
                    echo "No data found";
                }
            }
        } catch (Exception $ex) {
            echo $ex->getMessage();
            return false;
        }
    }

    /**
     * /webservice/index/pedimentos-pagados-enh?token=e8e49b5f6cd85d34d31d92e8978a90dabc061787&rfc=GTO910508AM7&fechaIni=2016-01-01&fechaFin=2016-01-30&aduana=640&patente=3589
     *
     * @throws Exception
     */
    public function pedimentosPagadosEnhAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        try {
            $f = array(
                'patente' => array('StringTrim', 'StripTags', 'Digits'),
                'aduana' => array('StringTrim', 'StripTags', 'Digits'),
                'rfc' => array('StringTrim', 'StripTags', 'StringToUpper'),
                'token' => array('StringTrim', 'StripTags', 'StringToLower'),
                'fechaIni' => array('StringTrim', 'StripTags'),
                'fechaFin' => array('StringTrim', 'StripTags'),
            );
            $v = array(
                'patente' => new Zend_Validate_Int(),
                'aduana' => new Zend_Validate_Int(),
                'rfc' => array(new Zend_Validate_Regex('/^[A-Z0-9]+$/')),
                'token' => array(new Zend_Validate_Regex('/^[a-z0-9]+$/')),
                'fechaIni' => array(new Zend_Validate_Regex("/^\d{4}\-\d{2}\-\d{2}$/")),
                'fechaFin' => array(new Zend_Validate_Regex("/^\d{4}\-\d{2}\-\d{2}$/")),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid()) {

                $row = new Webservice_Model_Table_WsTokens(array("rfc" => $input->rfc));
                $table = new Webservice_Model_WsTokens();
                $table->find($row);

                if (null === ($row->getId())) {
                    throw new Exception("Token not found. Please contact system administrator.");
                } else {
                    if ($row->getToken() !== $input->token) {
                        throw new Exception("Invalid token. Please contact system administrator.");
                    }
                }

                $mppr = new Automatizacion_Model_RptPedimentos();
                $arr = $mppr->wsObtener($input->patente, $input->aduana, $input->rfc, $input->fechaIni, $input->fechaFin);

                $domtree = new DOMDocument("1.0", "UTF-8");
                $domtree->preserveWhiteSpace = false;
                $domtree->formatOutput = true;
                if (isset($arr) && !empty($arr)) {
                    $operaciones = $domtree->createElement("operaciones");
                    $operaciones->setAttribute('cantidad', count($arr));
                    $domtree->appendChild($operaciones);
                    foreach ($arr as $item) {
                        $operacion = $domtree->createElement("pedimento");
                        $operacion->setAttribute('Patente', $item["patente"]);
                        $operacion->setAttribute('Pedimento', $item["pedimento"]);
                        $operacion->setAttribute('Referencia', $item["referencia"]);
                        $operacion->setAttribute('Aduana', $item["aduana"] . $item["seccAduana"]);
                        $operacion->setAttribute('Cliente', $input->rfc);
                        $operacion->setAttribute('TipoOpe', ($item["tipoMovimiento"] == 'TOCE.IMP') ? 1 : 2);
                        $operacion->setAttribute('Year', date('Y', strtotime($item["fechaPago"])));
                        $operacion->setAttribute('FechaPago', date('Y-m-d', strtotime($item["fechaPago"])));
                        $operacion->setAttribute('RfcSociedad', $item["rfcSociedad"]);
                        if (APPLICATION_ENV == "production") {
                            $url = $this->_baseUrl . "/webservice/index/detalle-pedimento-enh/rfc/{$input->rfc}/aduana/" . $item["aduana"] . $item["seccAduana"] . "/patente/{$input->patente}/pedimento/{$item["pedimento"]}/token/" . sha1($this->_salt . $item["pedimento"] . $this->_pepper);
                        } else if (APPLICATION_ENV == "staging") {
                            $url = $this->_baseUrl . "/webservice/index/detalle-pedimento-enh/rfc/{$input->rfc}/aduana/" . $item["aduana"] . $item["seccAduana"] . "/patente/{$input->patente}/pedimento/{$item["pedimento"]}/token/" . sha1($this->_salt . $item["pedimento"] . $this->_pepper);
                        } else {
                            $url = "http://localhost:8090/webservice/index/detalle-pedimento-enh/rfc/{$input->rfc}/aduana/" . $item["aduana"] . $item["seccAduana"] . "/patente/{$input->patente}/pedimento/{$item["pedimento"]}/token/" . sha1($this->_salt . $item["pedimento"] . $this->_pepper);
                        }
                        $operacion->setAttribute('Url', $url);
                        $operaciones->appendChild($operacion);
                    }
                } else {
                    throw new Exception('No data found on selected dates.');
                }

                if (isset($domtree)) {
                    Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer')->setNoRender(true);
                    Zend_Layout::getMvcInstance()->disableLayout();
                    $this->_response->setHeader('Content-Type', 'text/xml; charset=utf-8')
                        ->setBody($domtree->saveXML());
                }
            } else {
                throw new Exception('Invalid input.');
            }
        } catch (Exception $ex) {
            $domtree = new DOMDocument("1.0", "UTF-8");
            $domtree->preserveWhiteSpace = false;
            $domtree->formatOutput = true;
            $root = $domtree->createElement("errores");
            $xmlRoot = $domtree->appendChild($root);
            $xmlRoot->appendChild($domtree->createElement("error", $ex->getMessage()));
            $this->_response->setHeader('Content-Type', 'text/xml; charset=utf-8')
                ->setBody($domtree->saveXML());
        }
    }

    /**
     * /webservice/index/pedimentos-pagados-sociedad-enh?token=e8e49b5f6cd85d34d31d92e8978a90dabc061787&rfc=GTO910508AM7&fechaIni=2016-01-01&fechaFin=2016-01-30&aduana=640&patente=3589
     *
     */
    public function pedimentosPagadosSociedadEnhAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $domtree = new DOMDocument("1.0", "ISO-8859-1");
        $domtree->preserveWhiteSpace = false;
        $domtree->formatOutput = true;
        try {
            $f = array(
                "patente" => array("StringTrim", "StripTags", "Digits"),
                "aduana" => array("StringTrim", "StripTags", "Digits"),
                "rfc" => array("StringTrim", "StripTags", "StringToUpper"),
                "token" => array("StringTrim", "StripTags", "StringToLower"),
                "fechaIni" => array("StringTrim", "StripTags"),
                "fechaFin" => array("StringTrim", "StripTags"),
            );
            $v = array(
                "patente" => new Zend_Validate_Int(),
                "aduana" => new Zend_Validate_Int(),
                "rfc" => array(new Zend_Validate_Regex("/^[A-Z]{3,4}([0-9]{2})([0-9]{2})([0-9]{2})?[A-Z0-9]{3,4}/")),
                "token" => array(new Zend_Validate_Regex("/^[a-z0-9]+$/")),
                "fechaIni" => array(new Zend_Validate_Regex("/^\d{4}\-\d{2}\-\d{2}$/")),
                "fechaFin" => array(new Zend_Validate_Regex("/^\d{4}\-\d{2}\-\d{2}$/")),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("patente") && $input->isValid("token") && $input->isValid("rfc")) {
                $row = new Webservice_Model_Table_WsTokens(array("rfc" => $input->rfc));
                $table = new Webservice_Model_WsTokens();
                $table->find($row);
                if (null === ($row->getId())) {
                    $root = $domtree->createElement("errores");
                    $xmlRoot = $domtree->appendChild($root);
                    $xmlRoot->appendChild($domtree->createElement("error", "RFC no en base de datos."));
                } else {
                    if ($row->getToken() !== $input->token) {
                        $root = $domtree->createElement("errores");
                        $xmlRoot = $domtree->appendChild($root);
                        $xmlRoot->appendChild($domtree->createElement("error", "Token no válido."));
                    } else {
                        $data = array();
                        foreach (array(640, 240, 800) as $k => $v) {
                            $db = $this->_sistemaPedimentos($input->patente, $v);
                            if (isset($db)) {
                                $pedimentos = $db->wsPedimentoPagadosSociedad($input->rfc, $input->fechaIni, $input->fechaFin);
                                if (isset($pedimentos) && $pedimentos != false) {
                                    foreach ($pedimentos as $ped) {
                                        $data[] = $ped;
                                    }
                                }
                            }
                        }
                        if (isset($db) && !empty($data)) {
                            if (isset($data) && !empty($data)) {
                                $operaciones = $domtree->createElement("operaciones");
                                $operaciones->setAttribute("cantidad", count($data));
                                $domtree->appendChild($operaciones);
                                foreach ($data as $item) {
                                    $operacion = $domtree->createElement("pedimento");
                                    $operacion->setAttribute("Patente", $item["patente"]);
                                    $operacion->setAttribute("Pedimento", $item["pedimento"]);
                                    $operacion->setAttribute("Referencia", $item["referencia"]);
                                    $operacion->setAttribute("Aduana", $item["aduana"] . $item["seccAduana"]);
                                    $operacion->setAttribute("Cliente", $item["rfcCliente"]);
                                    $operacion->setAttribute("TipoOpe", ($item["tipoMovimiento"] == "TOCE.IMP") ? 1 : 2);
                                    $operacion->setAttribute("Year", date("Y", strtotime($item["fechaPago"])));
                                    $operacion->setAttribute("FechaPago", date("Y-m-d", strtotime($item["fechaPago"])));
                                    $operacion->setAttribute("RfcSociedad", $item["rfcSociedad"]);
                                    if (APPLICATION_ENV == "production") {
                                        $url = $this->_baseUrl . "/webservice/index/detalle-pedimento-enh/rfc/{$input->rfc}/aduana/" . $item["aduana"] . $item["seccAduana"] . "/patente/{$input->patente}/pedimento/{$item["pedimento"]}/token/" . sha1($this->_salt . $item["pedimento"] . $this->_pepper);
                                    } else if (APPLICATION_ENV == "staging") {
                                        $url = $this->_baseUrl . "/webservice/index/detalle-pedimento-enh/rfc/{$input->rfc}/aduana/" . $item["aduana"] . $item["seccAduana"] . "/patente/{$input->patente}/pedimento/{$item["pedimento"]}/token/" . sha1($this->_salt . $item["pedimento"] . $this->_pepper);
                                    } else {
                                        $url = "http://localhost:8090/webservice/index/detalle-pedimento-enh/rfc/{$input->rfc}/aduana/" . $item["aduana"] . $item["seccAduana"] . "/patente/{$input->patente}/pedimento/{$item["pedimento"]}/token/" . sha1($this->_salt . $item["pedimento"] . $this->_pepper);
                                    }
                                    $operacion->setAttribute("Url", $url);
                                    $operaciones->appendChild($operacion);
                                }
                            } else {
                                $root = $domtree->createElement("errores");
                                $xmlRoot = $domtree->appendChild($root);
                                $xmlRoot->appendChild($domtree->createElement("error", "No existen pedimentos en el periodo."));
                            }
                        } else {
                            $root = $domtree->createElement("errores");
                            $xmlRoot = $domtree->appendChild($root);
                            $xmlRoot->appendChild($domtree->createElement("error", "No data found!"));
                        }
                    }
                }
            } else {
                $root = $domtree->createElement("errores");
                $xmlRoot = $domtree->appendChild($root);
                $xmlRoot->appendChild($domtree->createElement("error", "Invalid Input!"));
            }
            if (isset($domtree)) {
                Zend_Controller_Action_HelperBroker::getStaticHelper("viewRenderer")->setNoRender(true);
                Zend_Layout::getMvcInstance()->disableLayout();
                $this->_response->setHeader("Content-Type", "text/xml; charset=ISO-8859-1")
                    ->setBody($domtree->saveXML());
            }
        } catch (Exception $ex) {
            echo $ex->getMessage();
        }
    }

    protected function _value($value)
    {
        $text = htmlentities(mb_convert_encoding(utf8_encode($value), "UTF-8", "UTF-8"), ENT_COMPAT, "UTF-8");
        return htmlspecialchars($text, ENT_XHTML, "ISO-8859-1");
    }

    public function encodingAction()
    {
        $domtree = new DOMDocument("1.0", "UTF-8");
        $domtree->preserveWhiteSpace = false;
        $domtree->formatOutput = true;
        $operacion = $domtree->createElement("testing");
        $operacion->setAttribute("example", $this->_value("505|7009602|21062017|COVE17215KUO2|EXW|EUR|666.00|592.40|DEU||DE118859311|KTB  IMPORT-EXPORT HANDELSGESELLSCHAFT MBH & CO. KG|GROSSMOORRING||9|21079|HAMBURG|"));
        $domtree->appendChild($operacion);
        Zend_Controller_Action_HelperBroker::getStaticHelper("viewRenderer")->setNoRender(true);
        Zend_Layout::getMvcInstance()->disableLayout();
        $this->_response->setHeader("Content-Type", "text/xml; charset=ISO-8859-1")
            ->setBody($domtree->saveXML());
    }

    /**
     * /webservice/index/detalle-pedimento-enh/rfc/MAT0903126W0/aduana/640/patente/3589/pedimento/6001130/token/bac24173a5163a3b8354858d34fd72331c1d6064
     *
     * @return boolean
     * @throws Exception
     */
    public function detallePedimentoEnhAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $domtree = new DOMDocument("1.0", "UTF-8");
        $domtree->preserveWhiteSpace = false;
        $domtree->formatOutput = true;
        try {
            $map = new Automatizacion_Model_ArchivosValidacionPedimentosMapper();
            $val = new Automatizacion_Model_ArchivosValidacionMapper();
            $f = array(
                "patente" => array("StringTrim", "StripTags", "Digits"),
                "aduana" => array("StringTrim", "StripTags", "Digits"),
                "pedimento" => array("StringTrim", "StripTags", "Digits"),
                "rfc" => array("StringTrim", "StripTags", "StringToUpper"),
                "token" => array("StringTrim", "StripTags", "StringToLower"),
            );
            $vld = array(
                "patente" => new Zend_Validate_Int(),
                "aduana" => new Zend_Validate_Int(),
                "pedimento" => new Zend_Validate_Int(),
                "rfc" => array(new Zend_Validate_Regex("/^[A-Z]{3,4}([0-9]{2})([0-9]{2})([0-9]{2})?[A-Z0-9]{3,4}/")),
                "token" => array(new Zend_Validate_Regex("/^[a-z0-9]+$/")),
            );
            $input = new Zend_Filter_Input($f, $vld, $this->_request->getParams());
            if ($input->isValid("patente") && $input->isValid("aduana") && $input->isValid("rfc") && $input->isValid("pedimento")) {
                if ($this->_validateToken($input->token, $input->pedimento) === true) {

                    $row = new Webservice_Model_Table_WsTokens(array("rfc" => $input->rfc));
                    $table = new Webservice_Model_WsTokens();
                    $f = new OAQ_ArchivosM3();
                    $table->find($row);

                    if (null === ($row->getId())) {

                        $root = $domtree->createElement("errores");
                        $xmlRoot = $domtree->appendChild($root);
                        $xmlRoot->appendChild($domtree->createElement("error", "RFC no en base de datos."));
                    } else {

                        $mppr = new Automatizacion_Model_RptPedimentoDetalle();
                        $arr = $mppr->wsObtener($input->patente, $input->aduana, $input->pedimento);

                        $mpprf = new Automatizacion_Model_RptPedimentoDesglose();
                        $arrf = $mpprf->wsObtenerFacturas($arr['idPedimento']);

                        $mpprp = new Automatizacion_Model_RptPedimentoDesglose();

                        $mpprv = new Trafico_Model_TraficoVucem();

                        if (isset($arr) && $arr !== false) {

                            $operaciones = $domtree->createElement("operaciones");
                            $operacion = $domtree->createElement("pedimento");
                            $operacion->appendChild($domtree->createElement("aduana", $arr["aduana"]));
                            $operacion->appendChild($domtree->createElement("patente", $arr["patente"]));
                            $operacion->appendChild($domtree->createElement("numero", $arr["pedimento"]));
                            $operacion->appendChild($domtree->createElement("referencia", $arr["referencia"]));
                            if (isset($arr["fechaPago"])) {
                                $operacion->appendChild($domtree->createElement("fechaPago", $arr["fechaPago"]));
                            } else {
                                $operacion->appendChild($domtree->createElement("fechaPago", $arr["pago"]));
                            }
                            $operacion->appendChild($domtree->createElement("fechaEntrada", $arr["fechaEntrada"]));
                            $operacion->appendChild($domtree->createElement("regimen", $arr["regimen"]));
                            $operacion->appendChild($domtree->createElement("cvePedimento", $arr["clavePedimento"]));
                            $operacion->appendChild($domtree->createElement("rfcCliente", $arr["rfcCliente"]));
                            $operacion->appendChild($domtree->createElement("nomCliente", $arr["nomCliente"]));
                            $operacion->appendChild($domtree->createElement("fechaEntrada", $arr["fechaEntrada"]));
                            $operacion->appendChild($domtree->createElement("aduanaEntrada", $arr["aduanaEntrada"]));
                            $operacion->appendChild($domtree->createElement("firmaValidacion", trim($arr["firmaValidacion"])));
                            $operacion->appendChild($domtree->createElement("firmaBanco", trim($arr["firmaBanco"])));
                            $operacion->appendChild($domtree->createElement("tipoCambio", strtoupper($arr["tipoCambio"])));
                            $operacion->appendChild($domtree->createElement("transporteEntrada", strtoupper($arr["transporteEntrada"])));
                            $operacion->appendChild($domtree->createElement("transporteArribo", strtoupper($arr["transporteArribo"])));
                            $operacion->appendChild($domtree->createElement("transporteSalida", strtoupper($arr["transporteSalida"])));
                            $operacion->appendChild($domtree->createElement("caja", strtoupper($arr["contendores"])));
                            $operacion->appendChild($domtree->createElement("guias", strtoupper($arr["guias"])));
                            $operacion->appendChild($domtree->createElement("pesoBruto", strtoupper($arr["pesoBruto"])));
                            $operacion->appendChild($domtree->createElement("totalEfectivo", strtoupper($arr["totalEfectivo"])));

                            $map = new Automatizacion_Model_ArchivosValidacionPedimentosMapper();
                            $reg = $map->pedimento($input->patente, $input->pedimento, $input->aduana);

                            if (isset($reg["archivoNombre"])) {
                                $arch = $val->findFile($reg["archivoNombre"], $input->aduana, date("Y", strtotime($arr["fechaPago"])));
                                if (isset($arch["contenido"])) {
                                    $arraym3 = $f->fileToArray(base64_decode($arch["contenido"]), null, $input->pedimento);
                                }
                            }

                            if (isset($arraym3) && !empty($arraym3)) {
                                $registros = array('500', '501', '502', '503', '504', '505', '506', '507', '508', '509', '510', '511', '512', '513', '514', '516', '520', '551', '552', '553', '554', '555', '556', '557', '558', '560', '601', '701', '702', '301', '302', '351', '352', '353', '355', '358', '800', '801');
                                foreach ($registros as $regkey) {
                                    if (isset($arraym3[$regkey]) && is_array($arraym3[$regkey])) {
                                        $registro = $operacion->appendChild($domtree->createElement("registro"));
                                        $registro->appendChild($domtree->createElement("noRegistro", $regkey));
                                        if (count($arraym3[$regkey]) == 1) {
                                            $registro->appendChild($domtree->createElement("valor", $this->_value($arraym3[$regkey][0])));
                                        } elseif (count($arraym3[$regkey]) > 1) {
                                            $valores = $registro->appendChild($domtree->createElement("valores"));
                                            foreach ($arraym3[$regkey] as $reg) {
                                                $valores->appendChild($domtree->createElement("valor", $this->_value($reg)));
                                            }
                                        }
                                    }
                                }
                            }

                            if (isset($arrf) && !empty($arrf)) {
                                $detFacturas = $domtree->createElement("detalleFactura");
                                foreach ($arrf as $invoice) {

                                    $item = $domtree->createElement("item");
                                    $item->appendChild($domtree->createElement("numFactura", strtoupper($invoice["numFactura"])));
                                    if (isset($invoice["cove"]) && trim($invoice["cove"]) != '' && $invoice["cove"] != null && $invoice["cove"] !== false) {
                                        $cove = $domtree->createElement("cove", $invoice["cove"]);
                                        $edoc = $mpprv->buscarEdocument($invoice["cove"]);
                                        /* if ($edoc) {
                                          if (APPLICATION_ENV == "production") {
                                          $cove->setAttribute("url", $this->_baseUrl . "/webservice/index/descarga-cove/cove/{$invoice["cove"]}/token/" . sha1($this->_salt . $invoice["cove"] . $this->_pepper));
                                          } else {
                                          $cove->setAttribute("url", "http://localhost:8090/webservice/index/descarga-cove/cove/{$invoice["cove"]}/token/" . sha1($this->_salt . $invoice["cove"] . $this->_pepper));
                                          }
                                          } */
                                        $item->appendChild($cove);
                                    }
                                    $item->appendChild($domtree->createElement("taxId", $invoice["taxId"]));
                                    $item->appendChild($domtree->createElement("nomProveedor", $this->_value(htmlspecialchars($invoice["nomProveedor"]))));
                                    $item->appendChild($domtree->createElement("incoterm", $this->_value(trim($invoice["incoterm"]))));
                                    $item->appendChild($domtree->createElement("fechaFactura", $this->_value(trim($invoice["fechaFactura"]))));
                                    $item->appendChild($domtree->createElement("valorFacturaUsd", $invoice["valorFacturaUsd"]));
                                    $item->appendChild($domtree->createElement("valorFacturaMonExt", $invoice["valorFacturaMonExt"]));
                                    $item->appendChild($domtree->createElement("paisFactura", $invoice["paisFactura"]));
                                    $item->appendChild($domtree->createElement("divisa", $invoice["divisa"]));
                                    $item->appendChild($domtree->createElement("factorMonExt", $invoice["factorMonExt"]));

                                    $arrp = $mpprp->wsObtenerPartes($arr['idPedimento'], $invoice["numFactura"]);

                                    if (isset($arrp) && !empty($arrp)) {

                                        $parts = $item->appendChild($domtree->createElement("partes"));

                                        foreach ($arrp as $part) {
                                            $parte = $parts->appendChild($domtree->createElement("parte"));
                                            $parte->appendChild($domtree->createElement("ordenFraccion", $part["ordenFraccion"]));
                                            $parte->appendChild($domtree->createElement("fraccion", $part["fraccion"]));
                                            $parte->appendChild($domtree->createElement("numParte", strtoupper($part["numParte"])));
                                            $parte->appendChild($domtree->createElement("descripcion", $this->_value($part["descripcion"])));
                                            $parte->appendChild($domtree->createElement("valorMonExt", $part["valorMonExt"]));
                                            $parte->appendChild($domtree->createElement("precioUnitario", $part["precioUnitario"]));
                                            $parte->appendChild($domtree->createElement("cantUmc", $part["cantUmc"]));
                                            $parte->appendChild($domtree->createElement("umc", $part["umc"]));
                                            $parte->appendChild($domtree->createElement("cantUmt", $part["cantUmt"]));
                                            $parte->appendChild($domtree->createElement("umt", $part["umt"]));
                                            $parte->appendChild($domtree->createElement("paisOrigen", $part["paisOrigen"]));
                                            $parte->appendChild($domtree->createElement("paisVendedor", $part["paisVendedor"]));
                                            $parte->appendChild($domtree->createElement("tasaAdvalorem", $part["tasaAdvalorem"]));

                                            $parte->appendChild($domtree->createElement("tasaAdvalorem", $part["tasaAdvalorem"]));
                                            if (isset($part["tlc"])) {
                                                if ($part["tlc"] == "S") {
                                                    $tlc = "S";
                                                } else {
                                                    $tlc = "";
                                                }
                                            }
                                            if (isset($part["prosec"])) {
                                                if ($part["prosec"] == "S") {
                                                    $prosec = "S";
                                                } else {
                                                    $prosec = "";
                                                }
                                            }

                                            $parte->appendChild($domtree->createElement("tlc", $tlc));
                                            $parte->appendChild($domtree->createElement("prosec", $prosec));
                                            $parte->appendChild($domtree->createElement("patenteOriginal", $part["patenteOrig"]));
                                            $parte->appendChild($domtree->createElement("aduanaOriginal", $part["aduanaOrig"]));
                                            $parte->appendChild($domtree->createElement("pedimentoOriginal", $part["pedimentoOrig"]));
                                        }
                                    }

                                    $detFacturas->appendChild($item);
                                }
                                $operacion->appendChild($detFacturas);
                            }
                            $operaciones->appendChild($operacion);
                            $domtree->appendChild($operaciones);
                        } else {
                            $root = $domtree->createElement("errores");
                            $xmlRoot = $domtree->appendChild($root);
                            $xmlRoot->appendChild($domtree->createElement("error", "No data found!"));
                        }
                    }
                } else {
                    $root = $domtree->createElement("errores");
                    $xmlRoot = $domtree->appendChild($root);
                    $xmlRoot->appendChild($domtree->createElement("error", "Token de pedimento no valido!"));
                }
            } else {
                $root = $domtree->createElement("errores");
                $xmlRoot = $domtree->appendChild($root);
                $xmlRoot->appendChild($domtree->createElement("error", "Invalid Input!"));
            }
            $output = $domtree->saveXML();
            Zend_Controller_Action_HelperBroker::getStaticHelper("viewRenderer")->setNoRender(true);
            Zend_Layout::getMvcInstance()->disableLayout();
            $this->_response->setHeader("Content-Type", "text/xml; charset=utf-8")
                ->setBody($output);
        } catch (Zend_Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    protected function _analizar($contenido, $registro = null, $pedimento = null)
    {
        try {
            $array = preg_split('/\r\n|\r|\n/', $contenido);
            $content = array();
            foreach ($array as $line) {
                $key = substr($line, 0, 3);
                if ($key != '') {
                    if (key_exists($key, $content)) {
                        if (isset($pedimento) && strlen($pedimento) == 7) {
                            if (strpos($line, "|" . $pedimento . "|") > 0) {
                                $content[$key][] = trim($line);
                            }
                        } else {
                            $content[$key][] = trim($line);
                        }
                    } else {
                        if (isset($pedimento) && strlen($pedimento) == 7) {
                            if (strpos($line, "|" . $pedimento . "|") > 0) {
                                $content[$key][] = trim($line);
                            }
                        } else {
                            $content[$key][] = trim($line);
                        }
                    }
                    if ($key == "801") {
                        $content[801][] = trim($line);
                    }
                }
            }
            if (isset($registro)) {
                if (isset($content[$registro])) {
                    return $content[$registro];
                }
                return;
            }
            return $content;
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    protected function _sistemaPedimentos($patente, $aduana)
    {
        $misc = new OAQ_Misc();
        $db = $misc->sitawinTrafico($patente, $aduana);
        return $db;
    }

    protected function _validateToken($token, $value)
    {
        if (sha1($this->_salt . $value . $this->_pepper) === $token) {
            return true;
        }
        return false;
    }

    public function tokenPedimentoAction()
    {
        $f = array(
            "*" => array("StringTrim", "StripTags"),
            "pedimento" => array("Digits"),
        );
        $v = array(
            "pedimento" => array(new Zend_Validate_Int(), "NotEmpty"),
        );
        $i = new Zend_Filter_Input($f, $v, $this->_request->getParams());
        if ($i->isValid("pedimento")) {
            echo sha1($this->_salt . $i->pedimento . $this->_pepper);
        }
    }
}
