<?php

class Bodega_FacturasController extends Zend_Controller_Action {

    protected $_session;
    protected $_config;
    protected $_appconfig;

    public function init() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_appconfig = new Application_Model_ConfigMapper();
        $this->_redirector = $this->_helper->getHelper("Redirector");
        $this->_config = new Zend_Config_Ini(APPLICATION_PATH . "/configs/application.ini", APPLICATION_ENV);
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

    public function editarFacturaAction() {
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
                $row = $mppr->informacionFactura($input->idFactura);
                $this->view->invoice = $row;
                $this->view->idTrafico = $row["idTrafico"]; 
                $this->view->idCliente = $row["idCliente"]; 
                $this->view->idProv = $row["idPro"]; 
                $this->view->idFactura = $input->idFactura;
                
                $mppr = new Trafico_Model_VucemMapper();
                $arr = $mppr->obtenerPorFactura($input->idFactura);
                if (isset($arr["edocument"])) {
                    $this->view->closed = true;
                    
                }
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }
    
    public function detalleAction() {
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "idFactura" => array("Digits"),
            );
            $v = array(
                "idFactura" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("idFactura")) {
                $mppr = new Trafico_Model_FactDetalle();
                if (($row = $mppr->obtener($input->idFactura))) {
                    $this->_helper->json(array("success" => true, "result" => $row));
                } else {
                    throw new Exception("No data!");
                }
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }
    
    public function editarProveedorAction() {
        $this->_helper->viewRenderer->setNoRender(false);
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "idTrafico" => array("Digits"),
                "idFactura" => array("Digits"),
                "idProv" => array("Digits"),
            );
            $v = array(
                "idTrafico" => array("NotEmpty", new Zend_Validate_Int()),
                "idFactura" => array("NotEmpty", new Zend_Validate_Int()),
                "idProv" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("idFactura")) {
                $view = new Zend_View();
                $mppr = new Vucem_Model_VucemPaisesMapper();
                $view->paisSelect = $mppr->getAllCountries();
                $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/facturas/");
                $invoices = new Trafico_Model_TraficoFacturasMapper();                    
                $arr = $invoices->informacionFactura($input->idFactura);
                $view->idTrafico = $input->idTrafico;
                $view->idCliente = $arr["idCliente"];
                if ($input->isValid("idProv")) {
                    $view->idProv = $input->idProv;
                    $trafico = new OAQ_Trafico(array("idTrafico" => $arr["idTrafico"], "usuario" => $this->_session->username, "idUsuario" => $this->_session->id));
                    if ($trafico->getTipoOperacion() == "TOCE.IMP") {
                        $providers = new Trafico_Model_FactPro();
                        $row = $providers->obtener($input->idProv);
                    } else if ($trafico->getTipoOperacion() == "TOCE.EXP") {
                        $providers = new Trafico_Model_FactDest();
                        $row = $providers->obtener($input->idProv);
                    }
                    if (!empty($row)) {
                        $view->nombre = $row["nombre"];                        
                        $view->tipoIdentificador = $row["tipoIdentificador"];                        
                        $view->identificador = $row["identificador"];                        
                        $view->calle = $row["calle"];                        
                        $view->numInt = $row["numInt"];                        
                        $view->numExt = $row["numExt"];                        
                        $view->colonia = $row["colonia"];                        
                        $view->localidad = $row["localidad"];                        
                        $view->municipio = $row["municipio"];                        
                        $view->estado = $row["estado"];                        
                        $view->codigoPostal = $row["codigoPostal"];                        
                        $view->pais = $row["pais"];
                    }
                }
                $this->_helper->json(array("success" => true, "html" => $view->render("editar-proveedor.phtml")));
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }
    
    public function identificadoresAction() {
        try {            
            $this->_helper->json(array(
                array("id" => "TAX_ID", "text" => "TAX_ID"),
                array("id" => "RFC", "text" => "RFC"),
                array("id" => "CURP", "text" => "CURP"),
                array("id" => "SIN_TAXID", "text" => "SIN_TAXID"),
            ));
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function umcAction() {
        try {
            $mapper = new Vucem_Model_VucemUmcMapper();
            $rows = $mapper->getAllUnits();            
            $this->_helper->json($rows);
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }
    
    public function omaAction() {
        try {
            $mapper = new Vucem_Model_VucemUnidadesMapper();
            $rows = $mapper->getAllUnits();            
            $this->_helper->json($rows);
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }
    
    public function monedasAction() {
        try {
            $mppr = new Vucem_Model_VucemMonedasMapper();
            $arr = $mppr->obtenerMonedas();
            $this->_helper->json(array("success" => true, "result" => $arr));
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }
    
    public function estadosAction() {
        try {
            $mppr = new Application_Model_InegiEstados();
            $arr = $mppr->obtenerTodos();
            $this->_helper->json(array("success" => true, "result" => $arr));
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }
    
    public function paisesAction() {
        try {
            $mppr = new Vucem_Model_VucemPaisesMapper();
            $arr = $mppr->getAllCountries();
            $this->_helper->json(array("success" => true, "result" => $arr));
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }
    
    public function incotermsAction() {
        try {
            $mppr = new Trafico_Model_FactIncoterms();
            $arr = $mppr->obtenerTodos();
            $this->_helper->json(array("success" => true, "result" => $arr));
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }
    
    public function proveedorAction() {
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "idFactura" => array("Digits"),
            );
            $v = array(
                "idFactura" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("idFactura")) {
                $mppr = new Trafico_Model_FactDetalle();
                $arr = $mppr->obtener($input->idFactura);
                if (isset($arr["idPro"])) {
                    $mdl = new Bodega_Model_Proveedores();
                    $row = $mdl->obtener($arr["idPro"]);
                    $this->_helper->json(array("success" => true, "result" => $row));
                } else {
                    $this->_helper->json(array("success" => false));
                }
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }
    
    public function editarParteAction() {
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "idProducto" => array("Digits"),
            );
            $v = array(
                "idProducto" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("idProducto")) {
                
                $view = new Zend_View();
                $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/facturas/");
                $view->idProducto = $input->idProducto;
                $mdl = new Trafico_Model_ClientesPartes();
                
                $row = $mdl->obtener($input->idProducto);
                
                $umc = new Vucem_Model_VucemUmcMapper();
                $view->umcSelect = $umc->getAllUnits();
                $oma = new Vucem_Model_VucemUnidadesMapper();
                $view->omaSelect = $oma->getAllUnits();
                
                $view->numParte = $row["numParte"];
                $view->fraccion = $row["fraccion"];
                $view->descripcion = $row["descripcion"];
                $view->umc = $row["umc"];
                $view->umt = $row["umt"];
                $view->oma = $row["oma"];
                $view->marca = $row["marca"];
                $view->modelo = $row["modelo"];
                $view->subModelo = $row["subModelo"];
                $view->numSerie = $row["numSerie"];
                $view->iva = $row["iva"];
                $view->tlc = $row["tlc"];
                $view->tlcue = $row["tlcue"];
                $view->prosec = $row["prosec"];
                $view->advalorem = $row["advalorem"];
                $view->paisOrigen = $row["paisOrigen"];
                $view->paisVendedor = $row["paisVendedor"];
                $view->observaciones = $row["observaciones"];
                $this->_helper->json(array("success" => true, "html" => $view->render("editar-parte.phtml")));
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function editarProductoAction() {
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "idTrafico" => "Digits",
                "idCliente" => "Digits",
                "idProveedor" => "Digits",
                "idFactura" => array("Digits"),
                "idProveedor" => "Digits",
                "idProducto" => array("Digits"),
            );
            $v = array(
                "idTrafico" => array("NotEmpty", new Zend_Validate_Int()),
                "idCliente" => array("NotEmpty", new Zend_Validate_Int()),
                "idProveedor" => array("NotEmpty", new Zend_Validate_Int()),
                "idFactura" => array("NotEmpty", new Zend_Validate_Int()),
                "idProveedor" => array("NotEmpty", new Zend_Validate_Int()),
                "idProducto" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("idFactura")) {
                $view = new Zend_View();
                $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/facturas/");
                
                $view->idTrafico = $input->idTrafico;
                $view->idCliente = $input->idCliente;
                $view->idProveedor = $input->idProveedor;
                $view->idFactura = $input->idFactura;
                
                $umc = new Vucem_Model_VucemUmcMapper();
                $view->umcSelect = $umc->getAllUnits();
                $oma = new Vucem_Model_VucemUnidadesMapper();
                $view->omaSelect = $oma->getAllUnits();
                if ($input->isValid("idProducto")) {
                    $view->idProducto = $input->idProducto;
                    $mppr = new Trafico_Model_FactProd();
                    if (($row = $mppr->obtenerProducto($input->idProducto))) {
                        $view->orden = $row["orden"];
                        $view->numParte = $row["numParte"];
                        $view->fraccion = $row["fraccion"];
                        $view->descripcion = $row["descripcion"];
                        $view->precioUnitario = $row["precioUnitario"];
                        $view->cantidadFactura = $row["cantidadFactura"];
                        $view->valorComercial = $row["valorComercial"];
                        $view->umc = $row["umc"];
                        $view->cantidadOma = $row["cantidadOma"];
                        $view->umt = $row["umt"];
                        $view->cantidadTarifa = $row["cantidadTarifa"];
                        $view->oma = $row["oma"];
                        $view->marca = $row["marca"];
                        $view->modelo = $row["modelo"];
                        $view->subModelo = $row["subModelo"];
                        $view->numSerie = $row["numSerie"];
                        $view->iva = $row["iva"];
                        $view->tlc = $row["tlc"];
                        $view->tlcue = $row["tlcue"];
                        $view->prosec = $row["prosec"];
                        $view->advalorem = $row["advalorem"];
                        $view->paisOrigen = $row["paisOrigen"];
                        $view->paisVendedor = $row["paisVendedor"];
                        $view->observaciones = $row["observaciones"];
                    }
                } else {
                    $view->orden = 1;
                }
                
                $mpp = new Trafico_Model_TraficoFacturasMapper();
                $info = $mpp->detalleFactura($input->idFactura);
                $facturas = new OAQ_Archivos_Facturas(array("idTrafico" => $info["idTrafico"], "idFactura" => $input->idFactura));
                $facturas->log($info["idTrafico"], $input->idFactura, "Edito producto numFactura: " . $info["numeroFactura"] . " idProducto: " . $input->idProducto, $this->_session->username);
                
                $this->_helper->json(array("success" => true, "html" => $view->render("editar-producto.phtml")));
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }
    
    public function productoAction() {
        $this->_helper->viewRenderer->setNoRender(true);
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
                $mppr = new Trafico_Model_FactProd();
                if (($row = $mppr->obtenerProducto($input->id))) {
                  $this->_helper->json(array("success" => true, "result" => $row));
                } else {
                    throw new Exception("No data!");
                }
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }
    
    public function productosAction() {
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "idFactura" => array("Digits"),
            );
            $v = array(
                "idFactura" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("idFactura")) {
                $mppr = new Trafico_Model_FactProd();
                if (($row = $mppr->obtener($input->idFactura))) {
                  $this->_helper->json(array("success" => true, "result" => $row));
                } else {
                    throw new Exception("No data!");
                }
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }
    
    public function proveedoresAction() {
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "idFactura" => array("Digits"),
            );
            $v = array(
                "idFactura" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("idFactura")) {
                $invoices = new Trafico_Model_TraficoFacturasMapper();
                $arr = $invoices->informacionFactura($input->idFactura);

                $trafico = new OAQ_Trafico(array("idTrafico" => $arr["idTrafico"], "usuario" => $this->_session->username, "idUsuario" => $this->_session->id));

                $row = $trafico->obtenerDatos();
                
                $mppr = new Bodega_Model_Proveedores();
                $rows = $mppr->obtenerProveedores($arr["idCliente"], $row['idBodega']);
                
                if (!empty($rows)) {
                    $this->_helper->json(array("success" => true, "result" => $rows));
                } else {
                    $this->_helper->json(array("success" => false));
                }
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }
    
    public function destinatarioAction() {
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "idFactura" => array("Digits"),
            );
            $v = array(
                "idFactura" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("idFactura")) {
                
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function agregarProveedorAction() {
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "idProv" => array("Digits"),
                "idCliente" => array("Digits"),
                "idFactura" => array("Digits"),
                "cp" => array("Digits"),
            );
            $v = array(
                "idCliente" => array(new Zend_Validate_Int(), "NotEmpty"),
                "idFactura" => array(new Zend_Validate_Int(), "NotEmpty"),
                "clave" => "NotEmpty",
                "razonSocial" => "NotEmpty",
                "numExt" => "NotEmpty",
                "calle" => "NotEmpty",
                "cp" => array(new Zend_Validate_Int(), "NotEmpty"),
                "pais" => "NotEmpty",
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("razonSocial") && $input->isValid("numExt") && $input->isValid("calle") && $input->isValid("cp") && $input->isValid("pais")) {
                $mppr = new Trafico_Model_FactPro();

                $this->_helper->json(array("success" => true, "idProv" => 1));
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function importarFacturaAction() {
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "idFactura" => array("Digits"),
            );
            $v = array(
                "idFactura" => array(new Zend_Validate_Int(), "NotEmpty"),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("idFactura")) {
                $mppr = new Trafico_Model_TraficoFacturasMapper();
                $row = $mppr->informacionFactura($input->idFactura);
                if ($row["idTrafico"]) {
                    
                    $facturas = new OAQ_Archivos_Facturas(array("idTrafico" => $row["idTrafico"], "idFactura" => $input->idFactura));
                    $facturas->log($row["idTrafico"], $input->idFactura, "Importo factura " . $row["numFactura"], $this->_session->username);
                    
                    $trafico = new OAQ_Trafico(array("idTrafico" => $row["idTrafico"], "idFactura" => $input->idFactura, "idUsuario" => $this->_session->id));
                    $arr = $trafico->importarFacturaDesdeSistema($row["numFactura"]);
                    
                    if (!empty($arr)) {
                        $this->_helper->json(array("success" => true, "result" => $arr));
                    } else {
                        $this->_helper->json(array("success" => true, "result" => array()));
                    }
                    
                } else {
                    throw new Exception("Id not set!");
                }
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function guardarProveedorAction() {
        try {
            $request = $this->getRequest();
            if ($request->isPost()) {
                $f = array(
                    "idTrafico" => array("StringTrim", "StripTags", "Digits"),
                    "idCliente" => array("StringTrim", "StripTags", "Digits"),
                    "idProv" => array("StringTrim", "StripTags", "Digits"),
                    "nombre" => "StringToUpper",
                    "identificador" => "StringToUpper",
                    "tipoIdentificador" => "Digits",
                    "calle" => "StringToUpper",
                    "numExt" => "StringToUpper",
                    "numInt" => "StringToUpper",
                    "colonia" => "StringToUpper",
                    "localidad" => "StringToUpper",
                    "municipio" => "StringToUpper",
                    "estado" => "StringToUpper",
                    "codigoPostal" => "StringToUpper",
                    "pais" => "StringToUpper",
                );
                $v = array(
                    "idTrafico" => array("NotEmpty", new Zend_Validate_Int()),
                    "idCliente" => array("NotEmpty", new Zend_Validate_Int()),
                    "idProv" => array("NotEmpty", new Zend_Validate_Int()),
                    "nombre" => "NotEmpty",
                    "identificador" => "NotEmpty",
                    "tipoIdentificador" => "NotEmpty",
                    "calle" => "NotEmpty",
                    "numExt" => "NotEmpty",
                    "numInt" => "NotEmpty",
                    "colonia" => "NotEmpty",
                    "localidad" => "NotEmpty",
                    "municipio" => "NotEmpty",
                    "estado" => "NotEmpty",
                    "codigoPostal" => "NotEmpty",
                    "pais" => "NotEmpty",
                );
                $input = new Zend_Filter_Input($f, $v, $request->getPost());
                if ($input->isValid("idTrafico") && $input->isValid("idCliente")) {

                    $mppr = new Bodega_Model_Proveedores();

                    $mdl = new Trafico_Model_TraficosMapper();
                    $row = $mdl->obtenerPorId($input->idTrafico);

                    $arr = array(
                        "idBodega" => $row["idBodega"],
                        "nombre" => $input->nombre,
                        "identificador" => $input->identificador,
                        "tipoIdentificador" => $input->tipoIdentificador,
                        "calle" => $input->calle,
                        "numExt" => $input->numExt,
                        "numInt" => ($input->isValid("numInt")) ? $input->numInt : null,
                        "colonia" => ($input->isValid("colonia")) ? $input->colonia : null,
                        "localidad" => ($input->isValid("localidad")) ? $input->localidad : null,
                        "municipio" => ($input->isValid("municipio")) ? $input->municipio : null,
                        "estado" => ($input->isValid("estado")) ? $input->estado : null,
                        "codigoPostal" => $input->codigoPostal,
                        "pais" => $input->pais,
                    );

                    if ($input->isValid("idCliente") && $input->isValid("idProv")) {
                        $arr["modificado"] = date("Y-m-d H:i:s");
                        if (($mppr->actualizar($input->idProv, $arr))) {
                            $this->_helper->json(array("success" => true, "message" => "Actualizado"));
                        } else {
                            throw new Exception("No se pudo actualizar");
                        }
                    } else if ($input->isValid("idCliente") && !$input->isValid("idProv")) {
                        $arr["creado"] = date("Y-m-d H:i:s");
                        $arr["idCliente"] = $input->idCliente;
                        if (($mppr->agregar($arr))) {
                            $this->_helper->json(array("success" => true, "message" => "Agregado"));
                        } else {
                            throw new Exception("No se pudo agregar");
                        }
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
    
    public function guardarProductoAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $f = array(
                    "idTrafico" => array("StringTrim", "StripTags", "Digits"),
                    "idCliente" => array("StringTrim", "StripTags", "Digits"),
                    "idProveedor" => array("StringTrim", "StripTags", "Digits"),
                    "idFactura" => array("StringTrim", "StripTags", "Digits"),
                    "idProducto" => array("StringTrim", "StripTags", "Digits"),
                );
                $v = array(
                    "idTrafico" => array("NotEmpty", new Zend_Validate_Int()),
                    "idCliente" => array("NotEmpty", new Zend_Validate_Int()),
                    "idProveedor" => array("NotEmpty", new Zend_Validate_Int()),
                    "idFactura" => array("NotEmpty", new Zend_Validate_Int()),
                    "idProducto" => array("NotEmpty", new Zend_Validate_Int()),
                );
                $input = new Zend_Filter_Input($f, $v, $request->getPost());
                if ($input->isValid("idFactura") && $input->isValid("idCliente") && $input->isValid("idTrafico")) {
                    $post = $request->getPost();
                    $mppr = new Trafico_Model_FactProd();
                    $mdl = new Trafico_Model_ClientesPartes();
                    $arr = array(
                        "numParte" => $post["numParte"],
                        "fraccion" => $post["fraccion"],
                        "descripcion" => $post["descripcion"],
                        "precioUnitario" => $post["precioUnitario"],
                        "cantidadFactura" => $post["cantidadFactura"],
                        "valorComercial" => $post["valorComercial"],
                        "umc" => $post["umc"],
                        "cantidadOma" => $post["cantidadOma"],
                        "oma" => $post["oma"],
                        "cantidadTarifa" => $post["cantidadTarifa"],
                        "umt" => $post["umt"],
                        "marca" => $post["marca"],
                        "modelo" => $post["modelo"],
                        "subModelo" => $post["subModelo"],
                        "numSerie" => $post["numSerie"],
                        "iva" => $post["iva"],
                        "advalorem" => $post["advalorem"],
                        "tlc" => (isset($post["tlc"]) && $post["tlc"] == 'on') ? 'S' : null,
                        "tlcue" => (isset($post["tlcue"]) && $post["tlcue"] == 'on') ? 'S' : null,
                        "prosec" => (isset($post["prosec"]) && $post["prosec"] == 'on') ? 'S' : null,
                        "paisOrigen" => isset($post["paisOrigen"]) ? $post["paisOrigen"] : null,
                        "paisVendedor" => isset($post["paisVendedor"]) ? $post["paisVendedor"] : null,
                        "observaciones" => isset($post["observaciones"]) ? $post["observaciones"] : null,
                    );
                    if ($input->isValid("idProducto")) {
                        $arr["modificado"] = date("Y-m-d H:i:s");
                        $mppr->actualizar($input->idProducto, $arr);
                    } else {
                        $arr["creado"] = date("Y-m-d H:i:s");
                        $arr["idFactura"] = $input->idFactura;
                        $mppr->agregar($arr);
                    }
                    $trafico = new OAQ_Trafico(array("idTrafico" => $input->idTrafico, "idUsuario" => $this->_session->id));
                    if ($trafico->getTipoOperacion() == 'TOCE.IMP') {
                        $tipoOperacion = 1;
                    } else {
                        $tipoOperacion = 2;                        
                    }
                    if (!($id = $mdl->buscar($input->idProveedor, $tipoOperacion, $post["fraccion"], $post["numParte"], $post["paisOrigen"], $post["paisVendedor"]))) {
                        $array = $mdl->prepareDataFromRest($trafico->getIdCliente(), $tipoOperacion, $input->idProveedor, $arr);
                        $mdl->agregar($array);
                    } else {
                        $array = $mdl->prepareDataFromRest($trafico->getIdCliente(), $tipoOperacion, $input->idProveedor, $arr);
                        $mdl->actualizar($id, $array);
                    }
                    
                    $inv = new OAQ_Archivos_Facturas(array("idFactura" => $input->idFactura));
                    $inv->actualizarValorFactura();
                    
                    $this->_helper->json(array("success" => true));
                } else {
                    throw new Exception("Invalid input!");
                }
                $this->_helper->json(array("success" => true));
            } else {
                throw new Exception("Invalid request type!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }
    
    public function guardarParteAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $f = array(
                    "idProducto" => array("StringTrim", "StripTags", "Digits"),
                );
                $v = array(
                    "idProducto" => array("NotEmpty", new Zend_Validate_Int()),
                );
                $input = new Zend_Filter_Input($f, $v, $request->getPost());
                if ($input->isValid("idProducto")) {
                    $post = $request->getPost();
                    $mdl = new Trafico_Model_ClientesPartes();
                    $trafico = new OAQ_Trafico(array("idTrafico" => $input->idTrafico, "idUsuario" => $this->_session->id));
                    if ($trafico->getTipoOperacion() == 'TOCE.IMP') {
                        $tipoOperacion = 1;
                    } else {
                        $tipoOperacion = 2;                        
                    }
                    if (!($id = $mdl->buscar($input->idProveedor, $tipoOperacion, $post["fraccion"], $post["numParte"], $post["paisOrigen"], $post["paisVendedor"]))) {
                        $array = $mdl->prepareDataFromRest($trafico->getIdCliente(), $tipoOperacion, $input->idProveedor, $post);
                        $mdl->agregar($array);
                    } else {
                        $array = $mdl->prepareDataFromRest($trafico->getIdCliente(), $tipoOperacion, $input->idProveedor, $arr);
                        $mdl->actualizar($id, $array);
                    }
                    $this->_helper->json(array("success" => true));
                } else {
                    throw new Exception("Invalid input!");
                }
                $this->_helper->json(array("success" => true));
            } else {
                throw new Exception("Invalid request type!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }
    
    public function borrarProductoAction() {
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
                if ($input->isValid("id")) {
                    $mppr = new Trafico_Model_FactProd();
                    
                    $arr = $mppr->obtenerProducto($input->id);
                    
                    $mpp = new Trafico_Model_TraficoFacturasMapper();
                    $info = $mpp->detalleFactura($arr["idFactura"]);
                    $facturas = new OAQ_Archivos_Facturas(array("idTrafico" => $info["idTrafico"], "idFactura" => $arr["idFactura"]));
                    $facturas->log($info["idTrafico"], $arr["idFactura"], "Se borro producto numFactura: " . $info["numeroFactura"] . " idProducto: " . $input->id, $this->_session->username);
                    
                    $stmt = $mppr->borrar($input->id);
                    if ($stmt == true) {
                        $this->_helper->json(array("success" => true));
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

    public function cambiarProveedorAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $f = array(
                    "idFactura" => array("StringTrim", "StripTags", "Digits"),
                    "idProv" => array("StringTrim", "StripTags", "Digits"),
                );
                $v = array(
                    "idFactura" => array("NotEmpty", new Zend_Validate_Int()),
                    "idProv" => array("NotEmpty", new Zend_Validate_Int()),
                );
                $input = new Zend_Filter_Input($f, $v, $request->getPost());
                if ($input->isValid("idFactura") && $input->isValid("idProv")) {
                    $mppr = new Trafico_Model_FactDetalle();
                    $stmt = $mppr->actualizarProveedor($input->idFactura, $input->idProv);
                    if ($stmt == true) {
                        $this->_helper->json(array("success" => true));
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

    public function guardarAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $f = array(
                    "idFactura" => array("StringTrim", "StripTags", "Digits"),
                );
                $v = array(
                    "idFactura" => array("NotEmpty", new Zend_Validate_Int()),
                    "fechaFactura" => array(new Zend_Validate_Regex("/^\d{4}\-\d{2}\-\d{2}$/"), "NotEmpty"),
                );
                $input = new Zend_Filter_Input($f, $v, $request->getPost());
                if ($input->isValid("idFactura")) {                    
                    $post = $request->getPost();
                    $mppr = new Trafico_Model_FactDetalle();
                    $arr = array(
                        "numFactura" => $post["numFactura"],
                        "divisa" => $post["divisa"],
                        "factorMonExt" => $post["factorMonExt"],
                        "fechaFactura" => $input->fechaFactura,
                        "incoterm" => $post["incoterm"],
                        "observaciones" => $post["observaciones"],
                        "paisFactura" => isset($post["paisFactura"]) ? $post["paisFactura"] : null,
                        "subdivision" => $post["subdivision"],
                        "valorFacturaMonExt" => (double) filter_var($post["valorFacturaMonExt"], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION),
                        "valorFacturaUsd" => (double) filter_var($post["valorFacturaUsd"], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION),
                        "certificadoOrigen" => $post["certificadoOrigen"],
                        "numExportador" => $post["numExportador"],
                        "fletes" => (double) filter_var($post["fletes"], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION),
                        "seguros" => (double) filter_var($post["seguros"], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION),
                        "embalajes" => (double) filter_var($post["embalajes"], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION),
                        "otros" => (double) filter_var($post["otros"], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION),
                        "actualizado" => date("Y-m-d H:i:s")
                    );
                    $mapper = new Trafico_Model_TraficoFacturasMapper();
                    $array = array(
                        "numFactura" => $post["numFactura"],
                        "divisa" => $post["divisa"],
                        "factorMonExt" => $post["factorMonExt"],
                        "fechaFactura" => $input->fechaFactura,
                        "incoterm" => $post["incoterm"],
                        "paisFactura" => isset($post["paisFactura"]) ? $post["paisFactura"] : null,
                        "valorMonExt" => (double) filter_var($post["valorFacturaMonExt"], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION),
                        "valorDolares" => (double) filter_var($post["valorFacturaUsd"], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION),
                        "actualizado" => date("Y-m-d H:i:s")
                    );
                    $mapper->actualizar($input->idFactura, $array);
                    
                    if ($mppr->update($input->idFactura, $arr)) {
                        $mpp = new Trafico_Model_TraficoFacturasMapper();
                        $info = $mpp->detalleFactura($input->idFactura);
                        $facturas = new OAQ_Archivos_Facturas(array("idTrafico" => $info["idTrafico"], "idFactura" => $input->idFactura));
                        $facturas->log($info["idTrafico"], $input->idFactura, "Actualizo factura " . $info["numeroFactura"], $this->_session->username);
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
    
    public function vucemPreviewAction() {
        $this->_helper->viewRenderer->setNoRender(false);
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
                $vucem = new OAQ_BodegaVucem();
                $trafico = new OAQ_Bodega(array("idTrafico" => $input->idTrafico, "usuario" => $this->_session->username, "idUsuario" => $this->_session->id));
                if (isset($arr["idFactura"])) {
                    $vucem->setReferencia($trafico->getReferencia());
                    $xml = $vucem->enviarCove($input->id, $input->idTrafico, $arr["idFactura"], false);
                    if ($xml) {
                        $this->view->contenido = $xml;
                    }
                }
                if (isset($arr["idArchivo"])) {
                    $vucem->setReferencia($trafico->getReferencia());
                    $xml = $vucem->enviarEdocument($input->id, $input->idTrafico, $arr["idArchivo"], $arr["tipoDocumento"], false);
                    if ($xml) {
                        $this->view->contenido = $xml;
                    }
                }
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }
    
    public function partesAction() {
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
                $mppr = new Trafico_Model_ClientesPartes();
                $arr = $mppr->obtenerPorCliente($input->idCliente);
                if (!empty($arr)) {
                    $this->_helper->json(array("success" => true, "results" => $arr));
                } else {
                    $this->_helper->json(array("success" => true, "results" => array()));
                }               
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }
    
    public function crearAdendaAction() {
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
                $facturas  = new OAQ_Archivos_Facturas(array("idFactura" => $input->idFactura));
                $facturas->copiar();
                $this->_helper->json(array("success" => true));
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

}
