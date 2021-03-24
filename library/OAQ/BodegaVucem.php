<?php

/**
 * Description of EmailNotifications
 *
 * @author Jaime E. Valdez <jvaldezch@gmail.com>
 */
class OAQ_BodegaVucem {

    protected $idTrafico;
    protected $idBodega;
    protected $idCliente;
    protected $idFactura;
    protected $referencia;
    protected $numFactura;
    protected $tipoOperacion;
    protected $factura;
    protected $trafico;
    protected $sello;
    protected $consolidado;
    protected $username;
    protected $db;
    protected $data;
    protected $xml;
    protected $coveArray;
    protected $appConfig;
    protected $filename;

    function setIdTrafico($idTrafico) {
        $this->idTrafico = $idTrafico;
    }
    
    function setIdBodega($idBodega) {
        $this->idBodega = $idBodega;
    }

    function setUsername($username) {
        $this->username = $username;
    }

    function setIdCliente($idCliente) {
        $this->idCliente = $idCliente;
    }

    function setConsolidado($consolidado) {
        $this->consolidado = $consolidado;
    }

    function setReferencia($referencia) {
        $this->referencia = $referencia;
    }

    function setNumFactura($numFactura) {
        $this->numFactura = $numFactura;
    }

    function setTipoOperacion($tipoOperacion) {
        $this->tipoOperacion = $tipoOperacion;
    }

    function getFactura() {
        return $this->factura;
    }

    function getIdTrafico() {
        return $this->idTrafico;
    }
    
    function getIdBodega() {
        return $this->idBodega;
    }
    
    function setIdFactura($idFactura) {
        $this->idFactura = $idFactura;
    }

    function getXml() {
        return $this->xml;
    }
    
    function getFilename() {
        return $this->filename;
    }

    public function __construct(array $options = null) {
        $this->appConfig = new Application_Model_ConfigMapper();
        if (is_array($options)) {
            $this->setOptions($options);
        }
    }

    public function __set($name, $value) {
        $method = "set" . $name;
        if (("mapper" == $name) || !method_exists($this, $method)) {
            throw new Exception("Invalid property" . __METHOD__);
        }
        $this->$method($value);
    }

    public function __get($name) {
        $method = "get" . $name;
        if (("mapper" == $name) || !method_exists($this, $method)) {
            throw new Exception("Invalid property " . __METHOD__);
        }
        return $this->$method();
    }

    public function setOptions(array $options) {
        $methods = get_class_methods($this);
        foreach ($options as $key => $value) {
            $method = "set" . ucfirst($key);
            if (in_array($method, $methods)) {
                $this->$method($value);
            }
        }
        return $this;
    }

    /**
     * 
     * @return boolean
     */
    public function importarFactura() {
        if (isset($this->patente) && isset($this->aduana)) {
            $misc = new OAQ_Misc();
            $this->db = $misc->sitawinTrafico($this->patente, $this->aduana);
            if (isset($this->db)) {
                if (!isset($this->consolidado)) {
                    $this->factura = $this->db->factura($this->referencia, $this->numFactura, $this->tipoOperacion);
                } else {
                    $this->factura = $this->db->facturaConsolidado($this->referencia, $this->pedimento, $this->numFactura, $this->tipoOperacion);
                }
                if ($this->_agregarDetalle()) {
                    $this->_agregarProductos();
                }
                $this->_agregarProveedor();
            }
            return true;
        }
        return;
    }

    /**
     * 
     * @param array $arr
     * @param int $idFactura
     * @return array|boolean
     */
    protected function _registroDetalle($arr, $idFactura = null) {
        $fields = array("numFactura", "cove", "fechaFactura", "incoterm", "ordenFactura", "valorFacturaUsd", "valorFacturaMonExt", "paisFactura", "divisa", "factorMonExt");
        $array = [];
        if (isset($arr) && !empty($arr)) {
            foreach ($fields as $item) {
                if (isset($arr[$item])) {
                    $array[$item] = $arr[$item];
                }
            }
            if (isset($idFactura)) {
                $array["idFactura"] = $idFactura;
            }
            if (!isset($array["valorFacturaMonExt"]) && (isset($array["divisa"]) && $array["divisa"] == "USD")) {
                $array["valorFacturaMonExt"] = $array["valorFacturaUsd"];
            }
            return $array;
        }
        return;
    }

    /**
     * 
     * @param array $arr
     * @return type
     */
    protected function _registroProducto($arr) {
        $fields = array("numParte", "orden", "fraccion", "subFraccion", "descripcion", "precioUnitario", "valorComercial", "valorUsd", "cantidadFactura", "cantidadTarifa", "prosec", "paisOrigen", "paisVendedor", "oma", "tlc", "umc", "umt");
        $array = [];
        if (isset($arr) && !empty($arr)) {
            foreach ($fields as $item) {
                if (isset($arr[$item])) {
                    $array[$item] = $arr[$item];
                }
            }
            if (!isset($array["precioUnitario"]) && (isset($array["cantidadFactura"]) && isset($array["umc"]))) {
                $array["precioUnitario"] = $array["valorComercial"] / $array["cantidadFactura"];
            }
            if (!isset($array["cantidadOma"]) && isset($array["cantidadFactura"])) {
                $array["cantidadOma"] = $array["cantidadFactura"];
            }
            if (!isset($array["oma"]) && isset($array["umc"])) {
                $tbl = new Vucem_Model_VucemUnidadesMapper();
                $array["oma"] = $tbl->getOma($array["umc"]);
            }
            return $array;
        }
        return;
    }

    /**
     * 
     * @param array $arr
     * @return array|boolean
     */
    protected function _proveedor($arr) {
        if (isset($arr) && $arr !== false && !empty($arr)) {
            $array = array(
                "clave" => $this->_tipoIdentificador($arr["taxId"], $arr["domicilio"]["pais"]),
                "identificador" => isset($arr["taxId"]) ? trim($arr["taxId"]) : null,
                "nombre" => isset($arr["nomProveedor"]) ? trim($arr["nomProveedor"]) : null,
                "calle" => isset($arr["domicilio"]["calle"]) ? trim($arr["domicilio"]["calle"]) : null,
                "numExt" => isset($arr["domicilio"]["numExterior"]) ? $arr["domicilio"]["numExterior"] : null,
                "numInt" => isset($arr["domicilio"]["numInterior"]) ? $arr["domicilio"]["numInterior"] : null,
                "localidad" => isset($arr["domicilio"]["localidad"]) ? trim($arr["domicilio"]["localidad"]) : null,
                "municipio" => isset($arr["domicilio"]["municipio"]) ? trim($arr["domicilio"]["municipio"]) : null,
                "ciudad" => isset($arr["domicilio"]["ciudad"]) ? $arr["domicilio"]["ciudad"] : null,
                "codigoPostal" => isset($arr["domicilio"]["codigoPostal"]) ? $arr["domicilio"]["codigoPostal"] : null,
                "pais" => isset($arr["domicilio"]["pais"]) ? $arr["domicilio"]["pais"] : null,
            );
            $array["idCliente"] = $this->idCliente;
            return $array;
        }
        return;
    }

    /**
     * Regresa el tipo de identificador de VUCEM [0-TAX_ID, 1-RFC, 2-CURP,3-SIN_TAX_ID]
     * 
     * @param string $identificador
     * @param string $pais
     * @return int
     */
    protected function _tipoIdentificador($identificador, $pais) {
        $regRfc = "/^[A-Z]{3,4}([0-9]{2})(1[0-2]|0[1-9])([0-3][0-9])([A-Z0-9]{3,4})$/";
        if (($pais == "MEX" || $pais == "MEXICO") && preg_match($regRfc, str_replace(" ", "", trim($identificador)))) {
            if ($identificador != "EXTR920901TS4") {
                if (strlen($identificador) > 12) {
                    return 2;
                }
                return 1;
            } else {
                return 0;
            }
        }
        if (($pais == "MEX" || $pais == "MEXICO") && !preg_match($regRfc, str_replace(" ", "", trim($identificador)))) {
            return 0;
        }
        if ($pais != "MEX" && trim($identificador) != "") {
            return 0;
        }
        if ($pais != "MEX" && trim($identificador) == "") {
            return 0;
        }
    }

    /**
     * 
     * @return boolean
     */
    protected function _agregarDetalle() {
        $mapper = new Trafico_Model_FactDetalle();
        if (!($mapper->verificar($this->idFactura, $this->numFactura))) {
            $arr = $this->_registroDetalle($this->factura, $this->idFactura);
            $success = $mapper->agregar($arr);
            return true;
        } else {
            $arr = $this->_registroDetalle($this->factura);
            $success = $mapper->actualizar($this->idFactura, $this->numFactura, $arr);
            return true;
        }
        return;
    }

    /**
     * 
     * @return boolean
     */
    protected function _agregarProductos() {
        $mapper = new Trafico_Model_FactProd();
        $mapper->borrar($this->idFactura);
        if (isset($this->factura["productos"])) {
            foreach ($this->factura["productos"] as $item) {
                $arr = $mapper->prepareData($item);
                if (count($arr)) {
                    $arr["idFactura"] = $this->idFactura;
                    $mapper->agregar($arr);
                }
            }
            return true;
        }
        return;
    }

    protected function _agregarProveedor() {
        $details = new Trafico_Model_FactDetalle();
        if ($this->tipoOperacion == "TOCE.IMP" && isset($this->factura["proveedor"])) {
            $mapper = new Trafico_Model_FactPro();
            $arr = $this->_proveedor($this->factura["proveedor"]);
            if (!($id = $mapper->verificar($this->idCliente, $arr["identificador"]))) {
                $id = $mapper->agregar($arr);
            }
            $details->actualizarProveedor($this->idFactura, $id);
        } elseif ($this->tipoOperacion == "TOCE.EXP" && isset($this->factura["destinatario"])) {
            $mapper = new Trafico_Model_FactDest();
            $arr = $this->_proveedor($this->factura["destinatario"]);
            if (!($id = $mapper->verificar($this->idCliente, $arr["identificador"]))) {
                $id = $mapper->agregar($arr);
            }
            $details->actualizarProveedor($this->idFactura, $id);
        }
    }

    public function verFactura() {
        $invoices = new Trafico_Model_TraficoFacturasMapper();
        $header = $invoices->informacionFactura($this->idFactura);
        $customers = new Trafico_Model_ClientesDom();
        $customer = $customers->obtener($header["idCliente"]);
        $details = new Trafico_Model_FactDetalle();
        $detail = $details->obtener($this->idFactura);
        if ($detail["numFactura"] && $detail["idPro"]) {
            $products = new Trafico_Model_FactProd();
            if ($header["ie"] == "TOCE.IMP") {
                $pro = new Trafico_Model_FactPro();
                $provider = $pro->obtener($detail["idPro"]);
            } elseif ($header["ie"] == "TOCE.EXP") {
                $des = new Trafico_Model_FactDest();
                $provider = $des->obtener($detail["idPro"]);
            }
            $array = array(
                "encabezado" => $header,
                "cliente" => $customer,
                "proveedor" => $provider,
                "detalles" => $details->obtener($this->idFactura),
                "productos" => $products->obtener($this->idFactura),
            );
            return $array;
        } else {
            return array(
                "encabezado" => $header,
                "cliente" => $customer
            );
        }
    }

    /**
     * 
     * @param array $array
     * @return type
     */
    public function actualizarDireccionCliente($array) {
        if (isset($this->idCliente)) {
            $fields = array("id", "idCliente", "rfcCliente", "razon_soc", "calle", "numext", "numint", "colonia", "localidad", "municipio", "estado", "cp", "pais");
            foreach ($fields as $item) {
                isset($array[$item]) ? $arr[$item] = $array[$item] : $arr[$item] = null;
            }
            $cp = str_pad($arr["cp"], 5, "0", STR_PAD_LEFT);
            $addresses = new Trafico_Model_ClientesDom();
            $address = new Trafico_Model_Table_TraficoCliDom();
            $address->setIdentificador($arr["rfcCliente"]);
            $address->setClave(1);
            $address->setNombre(html_entity_decode($arr["razon_soc"]));
            $address->setCalle(html_entity_decode($arr["calle"]));
            $address->setNumExt($arr["numext"]);
            $address->setNumInt($arr["numint"]);
            $address->setColonia(html_entity_decode($arr["colonia"]));
            $address->setLocalidad(html_entity_decode($arr["localidad"]));
            $address->setMunicipio(html_entity_decode($arr["municipio"]));
            $address->setEstado(html_entity_decode($arr["estado"]));
            $address->setCodigoPostal($cp);
            $address->setPais($arr["pais"]);
            $address->setModificado(date("Y-m-d H:i:s"));
            if (null !== ($addresses->verificar($this->idCliente))) {
                $addresses->updateByRfc($address);
            } else {
                $addresses->agregarPersonalizado($this->idCliente, 1, $arr["rfcCliente"], html_entity_decode($arr["razon_soc"]), html_entity_decode($arr["calle"]), $arr["numext"], $arr["numint"], $arr["colonia"], $arr["localidad"], $arr["municipio"], null, $cp, $arr["pais"]);
            }
            $customers = new Vucem_Model_VucemClientesMapper();
            $customer = new Vucem_Model_Table_VucemClientes();
            if (isset($arr["id"])) {
                $customer->setId($arr["id"]);
                $customer->setModificado(date("Y-m-d H:i:s"));
                $customer->setModificadopor($this->username);
            } else {
                $customer->setCreado(date("Y-m-d H:i:s"));
                $customer->setCreadopor($this->username);
            }
            $customer->setRfc($arr["rfcCliente"]);
            $customer->setIdentificador(1);
            $customer->setRazon_soc(html_entity_decode($arr["razon_soc"]));
            $customer->setCalle(html_entity_decode($arr["calle"]));
            $customer->setNumext($arr["numext"]);
            $customer->setNumint($arr["numint"]);
            $customer->setLocalidad(html_entity_decode($arr["localidad"]));
            $customer->setColonia(html_entity_decode($arr["colonia"]));
            $customer->setMunicipio(html_entity_decode($arr["municipio"]));
            $customer->setEstado(html_entity_decode($arr["estado"]));
            $customer->setCp($arr["cp"]);
            $customer->setPais(html_entity_decode($arr["pais"]));
            $customers->save($customer);
            return true;
        }
        return;
    }

    public function generarCove() {
        if (isset($this->idTrafico) && isset($this->idFactura)) {
            $traffics = new Trafico_Model_TraficosMapper();
            $xml = new OAQ_Xml(true);
            $invoices = new Trafico_Model_TraficoFacturasMapper();
            $invoice = $invoices->factura($this->idFactura);
            $this->factura = $invoice;
            $traffic = $traffics->obtenerPorId($this->idTrafico);
            $this->trafico = $traffic;
            $this->obtenerSello($traffic["idCliente"]);
            $this->_prepareData();
            $xml->xmlCove($this->data);
            $this->xml = $xml->getXml();
        } else {
            throw new Exception("Invalid data!");
        }
    }

    protected function _prepareData() {
        if (isset($this->factura) && isset($this->trafico)) {
            $this->data["factura"]["rfcConsulta"][] = "OAQ030623UL8";
            if ($this->trafico["patente"] == 3920) {
                $this->data["factura"]["rfcConsulta"][] = "NOGI660213BI0";
            }
            if ($this->trafico["patente"] == 3574) {
                $this->data["factura"]["rfcConsulta"][] = "PEPJ561122765";
            }
            $this->data["factura"]["patenteAduanal"] = $this->trafico["patente"];
            $this->data["factura"]["numeroFacturaOriginal"] = $this->factura["numFactura"];
            $this->data["factura"]["tipoOperacion"] = $this->trafico["ie"];
            $this->data["factura"]["fechaExpedicion"] = $this->factura["fechaFactura"];
            $this->data["factura"]["subdivision"] = $this->factura["subdivision"];
            $this->data["factura"]["certificadoOrigen"] = $this->factura["certificadoOrigen"];
            $this->data["factura"]["numExportador"] = $this->factura["numExportador"];
            $this->data["factura"]["observaciones"] = $this->factura["observaciones"];
            $this->data["factura"]["correoElectronico"] = $this->appConfig->getParam('vucem-email');
            if (isset($this->sello)) {
                $this->data["usuario"]["username"] = $this->sello["rfc"];
                $this->data["usuario"]["password"] = $this->sello["ws_pswd"];
                $this->data["usuario"]["certificado"] = $this->sello["cer"];
                $this->data["usuario"]["key"] = openssl_get_privatekey(base64_decode($this->sello["spem"]), $this->sello["spem_pswd"]);
                $this->data["usuario"]["new"] = isset($this->sello["sha"]) ? true : false;
            }
            if (isset($this->factura["productos"])) {
                foreach ($this->factura["productos"] as $item) {
                    $valTotal = number_format(($item["precioUnitario"] * $item["cantidadFactura"]), 4, ".", false);
                    $valUsd = number_format(($this->factura["factorMonExt"] * ($item["precioUnitario"] * $item["cantidadFactura"])), 4, ".", false);
                    $this->data["mercancias"][] = array(
                        "numParte" => $item["numParte"],
                        "descripcionGenerica" => $item["descripcion"],
                        "claveUnidadMedida" => $item["oma"],
                        "tipoMoneda" => $this->factura["divisa"],
                        "cantidad" => $item["cantidadFactura"],
                        "valorUnitario" => $item["precioUnitario"],
                        "valorTotal" => $valTotal,
                        "valorDolares" => $valUsd,
                    );
                }
            }
        }
    }

    public function obtenerSello($idCliente) {
        $mapper = new Vucem_Model_VucemFirmanteMapper();
        if (isset($idCliente)) {
            $f = new Trafico_Model_CliSello();
            $d = $f->obtenerDefault($idCliente);
            if (is_int($d)) {
                $sello = $mapper->obtenerDetalleFirmanteId($d);
            } else {
                $sello = $mapper->obtenerDetalleFirmanteId(2);
            }
            $this->sello = $sello;
        }
    }

    public function actualizarCoveEnFactura($idVucem, $edocument, $adenda = null) {
        $mppr = new Trafico_Model_VucemMapper();
        if (($arr = $mppr->obtenerVucem($idVucem))) {
            if ($arr["idFactura"]) {
                $inv = new Trafico_Model_TraficoFacturasMapper();
                $inv->actualizar($arr["idFactura"], array("cove" => $edocument, "coveAdenda" => $adenda));
            }
        }
    }

    public function consultaRespuesta($idVucem, $numOperacion) {
        $sello = $this->_obtenerSello($idVucem);
        $mppr = new Trafico_Model_VucemMapper();
        $uti = new Utilerias_Vucem(true);
        if (($arr = $mppr->obtenerVucem($idVucem))) {
            if ($arr["idFactura"]) {
                $data["consulta"] = array(
                    "operacion" => $numOperacion,
                );
                $data["usuario"] = array(
                    "username" => $sello["rfc"],
                    "password" => $sello["ws_pswd"],
                    "certificado" => $sello["cer"],
                    "key" => openssl_get_privatekey(base64_decode($sello["spem"]), $sello["spem_pswd"]),
                    "new" => isset($sello["sha"]) ? true : false,
                );
                $xml = $uti->consultaEstatusOperacionCove($data);
                $this->xml = $xml;
                $resp = $this->_enviarXmlCoveRespuesta();
                if (isset($resp["edocument"])) {
                    $mppr->actualizar($idVucem, array(
                        "edocument" => $resp["edocument"],
                        "error" => 0,
                        "respuesta" => date('Y-m-d H:i:s'),
                        "actualizado" => date('Y-m-d H:i:s'),
                    ));
                    return $resp;
                } else {
                    // manejar error
                    $mppr->actualizar($idVucem, array(
                        "edocument" => null,
                        "error" => 1,
                        "respuesta" => date('Y-m-d H:i:s'),
                        "actualizado" => date('Y-m-d H:i:s'),
                    ));
                    return $resp;
                }
            }
        }
    }
    
    protected function _enviarXmlCoveRespuesta() {
        $serv = new OAQ_Servicios();
        $serv->setXml($this->xml);
        $serv->consultaEstatusCove();

        $response = $serv->getResponse();
        
        $resp = new OAQ_Respuestas();
        $arr = $resp->analizarRespuesta($response);
        return $arr;
    }
    
    protected function _obtenerCliente($idCliente) {
        $mppr = new Trafico_Model_ClientesMapper();
        return $mppr->datosClienteDomicilio($idCliente);
    }
    
    protected function _obtenerTrafico($idTrafico) {
        $mppr = new Trafico_Model_TraficosMapper();
        return $mppr->obtenerPorId($idTrafico);
    }
    
    protected function _obtenerFactura($idFactura) {
        $mppr = new Trafico_Model_TraficoFacturasMapper();
        return $mppr->detalleFactura($idFactura);
    }
    
    protected function _obtenerDestinatario($idProv) {
        $mppr = new Trafico_Model_FactDest();
        return $mppr->obtener($idProv);
    }
    
    protected function _obtenerProveedor($idProv) {
        $mppr = new Trafico_Model_FactPro();
        return $mppr->obtener($idProv);
    }
    
    protected function _obtenerProductos($idFactura) {
        $mppr = new Trafico_Model_FactProd();
        return $mppr->obtener($idFactura);
    }
    
    protected function _actualizarArchivo($idFactura, $filename) {
        $mppr = new Trafico_Model_FactDetalle();
        $mppr->update($idFactura, array(
            "archivoCove" => $filename
        ));
    }
    
    /**
     * COVE XML 21
     * COVE PDF 22
     * EDOC PDF 27
     * EDOC XML 56
     * 
     * @param type $idVucem
     */
    public function guardarFacturaEnExpediente($idVucem, $usuario, $idUsuario, $edocument = null) {
        require_once "tcpdf/acusevu.php";        
        $mppr = new Trafico_Model_VucemMapper();
        
        if (($arr = $mppr->obtenerVucem($idVucem))) {
            if ($arr["idFactura"]) {
                
                $inv = new Trafico_Model_TraficoFacturasMapper();
                $arr = $inv->detalleFactura($arr["idFactura"]);
                $trafico = new OAQ_Trafico(array("idTrafico" => $arr["idTrafico"], "usuario" => $usuario, "idUsuario" => $idUsuario));
                
                if (!file_exists($arr["archivoCove"])) {

                    $misc = new OAQ_Misc();
                    if (APPLICATION_ENV == "production") {
                        $misc->set_baseDir($this->_appconfig->getParam("expdest"));
                    } else {
                        $misc->set_baseDir("D:\\xampp\\tmp\\expedientes");
                    }

                    $vucem = new OAQ_TraficoVucem();
                    $vucem->setPatente($trafico->getPatente());
                    $vucem->setAduana($trafico->getAduana());
                    $vucem->setPedimento($trafico->getPedimento());
                    $vucem->setReferencia($trafico->getReferencia());
                    $xml = $vucem->enviarCove($idVucem, $arr["idTrafico"], $arr["idFactura"], false);

                    $directory = $misc->nuevoDirectorioExpediente($trafico->getPatente(), $trafico->getAduana(), $misc->trimUpper($trafico->getReferencia()));

                    $xml_filename = $directory . DIRECTORY_SEPARATOR . $vucem->getFilename();
                    if (isset($edocument)) {
                        $xml_filename = $directory . DIRECTORY_SEPARATOR . preg_replace('/^COVE_/', $edocument . '_', $vucem->getFilename());
                    }
                    $pdf_filename = preg_replace('/\..+$/', '.' . 'pdf', $xml_filename);
                    if (!file_exists($xml_filename)) {
                        if (file_put_contents($xml_filename, $xml)) {
                            $trafico->agregarArchivoExpediente(21, $xml_filename, $edocument);
                        }
                    }
                    if (!file_exists($pdf_filename)) {

                        $data = array(
                            'xml' => $xml,
                            'patente' => $trafico->getPatente(),
                            'aduana' => $trafico->getAduana(),
                            'pedimento' => $trafico->getPedimento(),
                            'referencia' => $trafico->getReferencia(),
                            'cove' => $edocument,
                            'rfcConsulta' => null,
                            'actualizado' => date('Y-m-d H:i:s'),
                            'filename' => $pdf_filename,
                            'creado' => date('Y-m-d H:i:s'),
                        );

                        $print = new OAQ_Imprimir_CoveDetalle2019($data, "P", "pt", "LETTER");
                        $print->set_filename($pdf_filename);
                        $print->Create();
                        $print->Output($pdf_filename, "F");

                        $trafico->agregarArchivoExpediente(22, $pdf_filename, $edocument);
                    }
                    return true;
                }
                if (file_exists($arr["archivoCove"])) {
                    
                    $trafico->agregarArchivoExpediente(21, $arr["archivoCove"], $arr["cove"]);
                    $pdffilename = dirname($arr["archivoCove"]) . DIRECTORY_SEPARATOR . preg_replace('/\..+$/', '.' . 'pdf', basename($arr["archivoCove"]));
                    if (file_exists($pdffilename)) {
                        unlink($pdffilename);
                    }
                    $data = array();
                    $data["cove"] = $arr["cove"];
                    $data["xml"] = file_get_contents($arr["archivoCove"]);
                    if (($trafico->guardarDetalleCove($data, $pdffilename))) {
                        $trafico->agregarArchivoExpediente(22, $pdffilename, $arr["cove"]);
                    }

                }
            }
        }
        
    }
    
    public function guardarEdocumentEnExpediente($idVucem, $edocument, $numTramite, $tipoDocumento, $usuario) {
        $misc = new OAQ_Misc();
        $mppr = new Trafico_Model_VucemMapper();
        if (($arr = $mppr->obtenerVucem($idVucem))) {
            if ($arr["idArchivo"]) {
                $data = $this->_armarEdocument($idVucem, $arr["idTrafico"], $arr["idArchivo"], $tipoDocumento);
                $data["edoc"] = $edocument;
                $data["patente"] = $this->patente;
                $data["aduana"] = $this->aduana;
                $data["referencia"] = $this->referencia;
                $data["pedimento"] = $this->pedimento;
                $data["numTramite"] = $numTramite;
                $data["actualizado"] = date("Y-m-d H:i:s");
                if (APPLICATION_ENV == 'production') {
                    $directory = $this->appConfig->getParam("expdest");
                } else {
                    $directory = "D:\\xampp\\tmp\\expedientes";
                }
                $sello = $this->_obtenerSello($idVucem);
                $vucemFiles = new OAQ_VucemArchivos(array(
                    "id" => $arr["idArchivo"],
                    "tipoDocumento" => $tipoDocumento,
                    "solicitud" => $arr["numeroOperacion"],
                    "dir" => $misc->nuevoDirectorio($directory, $this->patente, $this->aduana, $this->referencia),
                    "data" => $data,
                    "sello" => $sello,
                    "username" => $usuario,
                    "sourceFilename" => $arr["nombreArchivo"],
                ));
                $vucemFiles->guardarEdoc(false);
            }
        }
    }

    protected function _cleanString($string) {
        return trim(str_replace(array("\r", "\n", "\r\n"), '', $string));
    }

    public function enviarCove($idVucem, $idTrafico, $idFactura, $send = true, $save = false) {
        $sello = $this->_obtenerSello($idVucem);
        
        $uti = new Utilerias_BodegaVucem(true);
        $uti->set_idBodega($this->idBodega);
        $uti->set_referencia($this->referencia);
        
        $rfc = new Trafico_Model_RfcConsultaMapper();
        $trafico = $this->_obtenerTrafico($idTrafico);
        $factura = $this->_obtenerFactura($idFactura);
        $cliente = $this->_obtenerCliente($trafico["idCliente"]);
        if (empty($sello)) {
            throw new Exception("No se encontro sello para cliente con RFC {$trafico["rfc"]}.");
        }
        if (isset($trafico["id"]) && isset($factura["id"])) {            
            $this->coveArray["usuario"] = array(
                "username" => $sello["rfc"],
                "password" => $sello["ws_pswd"],
                "certificado" => $sello["cer"],
                "key" => openssl_get_privatekey(base64_decode($sello["spem"]), $sello["spem_pswd"]),
                "new" => isset($sello["sha"]) ? true : false,
            );
            $rfcConsulta = $rfc->rfcEdocument($trafico["idCliente"]);
            if (!isset($rfcConsulta)) {
                $rfcConsulta = array("OAQ030623UL8");
            }
            if ($sello["rfc"] !== $trafico["rfc"] && ($trafico["rfc"] != 'PRUE09329833')) {
                if (!in_array($trafico["rfc"], $rfcConsulta)) {
                    array_push($rfcConsulta, $trafico["rfc"]);
                }
            }
//            if ($this->patente == 3574) {
//                array_push($rfcConsulta, 'PEPJ561122765');
//            }
//            if ($this->patente == 3920) {
//                array_push($rfcConsulta, 'NOGI660213BI0');
//            }
//            if ($this->patente == 3878) {
//                array_push($rfcConsulta, 'JABM5408097G2');
//            }
            $coveAdenda = null;
            if ((int) $factura["adenda"] == 1) {
                if (isset($factura["coveAdenda"]) && $factura["coveAdenda"] != null) {
                    $coveAdenda = $factura["coveAdenda"];
                }
            }
            $this->coveArray["trafico"] = array(
                "tipoOperacion" => $trafico["ie"],
                "numeroFacturaOriginal" => $factura["numFactura"],
                "fechaExpedicion" => date("Y-m-d", strtotime($factura["fechaFactura"])),
                "certificadoOrigen" => $factura["certificadoOrigen"],
                "numExportador" => $factura["numExportador"],
                "subdivision" => $factura["subdivision"],
                "divisa" => $factura["divisa"],
                "observaciones" => ($factura["observaciones"] !== "") ? $this->_cleanString($factura["observaciones"]) : null,
                "factorMonExt" => $factura["factorMonExt"],
                "paisFactura" => $factura["paisFactura"],
                "tipoFigura" => $sello["figura"],
                "correoElectronico" => "soporte@oaq.com.mx",
                "rfcConsulta" => $rfcConsulta,
                "coveAdenda" => $coveAdenda,
            );
            $productos = $this->_obtenerProductos($idFactura);
            $mercancia = array();
            foreach ($productos as $prod) {
                if (isset($prod["cantidadFactura"]) && isset($prod["precioUnitario"])) {
                    $valorTotal = $prod["cantidadFactura"] * $prod["precioUnitario"];                    
                }
                if (isset($factura["factorMonExt"]) && isset($valorTotal)) {
                    $valorDolares = $factura["factorMonExt"] * $valorTotal;
                }
                $mercancia[] = array(
                    "descripcionGenerica" => isset($prod["descripcion"]) ? $this->_cleanString($prod["descripcion"]) : null,
                    "numParte" => isset($prod["numParte"]) ? $this->_cleanString($prod["numParte"]) : null,
                    "secuencial" => isset($prod["orden"]) ? $prod["orden"] : null,
                    "claveUnidadMedida" => isset($prod["oma"]) ? $prod["oma"] : null,
                    "tipoMoneda" => $this->coveArray["trafico"]["divisa"],
                    "cantidad" => isset($prod["cantidadFactura"]) ? number_format($prod["cantidadFactura"], 3, ".", "") : null,
                    "valorUnitario" => isset($prod["precioUnitario"]) ? number_format($prod["precioUnitario"], 6, ".", "") : null,
                    "valorTotal" => isset($valorTotal) ? number_format($valorTotal, 6, ".", "") : null,
                    "valorDolares" => isset($valorDolares) ? number_format($valorDolares, 4, ".", "") : null,
                    "marca" => (isset($prod["marca"]) && $prod["marca"] !== "") ? $this->_cleanString($prod["marca"]) : null,
                    "modelo" => (isset($prod["modelo"]) && $prod["modelo"] !== "") ? $this->_cleanString($prod["modelo"]) : null,
                    "subModelo" => (isset($prod["subModelo"]) && $prod["subModelo"] !== "") ? $this->_cleanString($prod["subModelo"]) : null,
                    "numeroSerie" => (isset($prod["numSerie"]) && $prod["numSerie"] !== "") ? $this->_cleanString($prod["numSerie"]) : null,
                );
            }
            $this->coveArray["mercancias"] = $mercancia;
            $this->_proveedorDestinatario($uti, $trafico["ie"], $cliente, $factura["idPro"]);
            if ($send == true) {
                $this->xml = $uti->xmlCove($this->coveArray, false, $save);
                if (file_exists($uti->get_filename())) {
                    $this->_actualizarArchivo($idFactura, $uti->get_filename());
                    $this->filename = $uti->getXmlFilename();
                }
                return $this->_enviarXmlCove();
            } else {
                $xml = $uti->xmlCove($this->coveArray, false, $save);
                $this->filename = $uti->getXmlFilename();
                var_dump($this->filename);
                return $xml;
            }
        } else {

        }
    }

    protected function _proveedorDestinatario(Utilerias_BodegaVucem $uti, $tipoOperacion, $cliente, $idProv) {
        if ($tipoOperacion === "TOCE.IMP") {
            $this->coveArray["destinatario"] = array(
                "tipoIdentificador" => 1,
                "identificacion" => $cliente["rfc"],
                "nombre" => $this->_cleanString($cliente["razonSocial"]),
                "calle" => $this->_cleanString($cliente["calle"]),
                "numeroExterior" => isset($cliente["numExt"]) ? $cliente["numExt"] : null,
                "numeroInterior" => isset($cliente["numInt"]) ? $cliente["numInt"] : null,
                "colonia" => isset($cliente["colonia"]) ? $this->_cleanString($cliente["colonia"]) : null,
                "localidad" => isset($cliente["localidad"]) ? $this->_cleanString($cliente["localidad"]) : null,
                "municipio" => isset($cliente["municipio"]) ? $this->_cleanString($cliente["municipio"]) : null,
                "entidadFederativa" => isset($cliente["estado"]) ? $cliente["estado"] : null,
                "codigoPostal" => isset($cliente["codigoPostal"]) ? $cliente["codigoPostal"] : null,
                "pais" => isset($cliente["pais"]) ? $cliente["pais"] : null,
            );
            $prov = $this->_obtenerProveedor($idProv);
            $this->coveArray["emisor"] = array(
                "tipoIdentificador" => (isset($prov["tipoIdentificador"]) && $prov["tipoIdentificador"] !== null) ? $prov["tipoIdentificador"] : $uti->tipoIdentificador($prov["identificador"], $prov["pais"]),
                "identificacion" => $prov["identificador"],
                "nombre" => $this->_cleanString($prov["nombre"]),
                "calle" => $this->_cleanString($prov["calle"]),
                "numeroExterior" => isset($prov["numExt"]) ? $prov["numExt"] : null,
                "numeroInterior" => isset($prov["numInt"]) ? $prov["numInt"] : null,
                "colonia" => isset($prov["colonia"]) ? $this->_cleanString($prov["colonia"]) : null,
                "localidad" => isset($prov["localidad"]) ? $this->_cleanString($prov["localidad"]) : null,
                "municipio" => isset($prov["municipio"]) ? $this->_cleanString($prov["municipio"]) : null,
                "entidadFederativa" => isset($prov["estado"]) ? $prov["estado"] : null,
                "codigoPostal" => isset($prov["codigoPostal"]) ? $prov["codigoPostal"] : null,
                "pais" => isset($prov["pais"]) ? $prov["pais"] : null,
            );
        } else if ($tipoOperacion === "TOCE.EXP") {
            $this->coveArray["emisor"] = array(
                "tipoIdentificador" => 1,
                "identificacion" => $cliente["rfc"],
                "nombre" => $this->_cleanString($cliente["razonSocial"]),
                "calle" => $this->_cleanString($cliente["calle"]),
                "numeroExterior" => isset($cliente["numExt"]) ? $cliente["numExt"] : null,
                "numeroInterior" => isset($cliente["numInt"]) ? $cliente["numInt"] : null,
                "colonia" => isset($cliente["colonia"]) ? $this->_cleanString($cliente["colonia"]) : null,
                "localidad" => isset($cliente["localidad"]) ? $this->_cleanString($cliente["localidad"]) : null,
                "municipio" => isset($cliente["municipio"]) ? $this->_cleanString($cliente["municipio"]) : null,
                "entidadFederativa" => isset($cliente["estado"]) ? $cliente["estado"] : null,
                "codigoPostal" => isset($cliente["codigoPostal"]) ? $cliente["codigoPostal"] : null,
                "pais" => isset($cliente["pais"]) ? $cliente["pais"] : null,
            );
            $prov = $this->_obtenerDestinatario($idProv);
            $this->coveArray["destinatario"] = array(
                "tipoIdentificador" => (isset($prov["tipoIdentificador"]) && $prov["tipoIdentificador"] !== null) ? $prov["tipoIdentificador"] : $uti->tipoIdentificador($prov["identificador"], $prov["pais"]),
                "identificacion" => $prov["identificador"],
                "nombre" => $this->_cleanString($prov["nombre"]),
                "calle" => $this->_cleanString($prov["calle"]),
                "numeroExterior" => isset($prov["numExt"]) ? $prov["numExt"] : null,
                "numeroInterior" => isset($prov["numInt"]) ? $prov["numInt"] : null,
                "colonia" => isset($prov["colonia"]) ? $this->_cleanString($prov["colonia"]) : null,
                "localidad" => isset($prov["localidad"]) ? $this->_cleanString($prov["localidad"]) : null,
                "municipio" => isset($prov["municipio"]) ? $this->_cleanString($prov["municipio"]) : null,
                "entidadFederativa" => isset($prov["estado"]) ? $prov["estado"] : null,
                "codigoPostal" => isset($prov["codigoPostal"]) ? $prov["codigoPostal"] : null,
                "pais" => isset($prov["pais"]) ? $prov["pais"] : null,
            );
        }
    }

    protected function _enviarXmlCove() {
        $serv = new OAQ_Servicios();
        $serv->setXml($this->xml);
        $serv->consumirServicioCove();
        $response = $serv->getResponse();
        $resp = new OAQ_Respuestas();
        $arr = $resp->analizarRespuesta($response);
        return $arr;
    }

    public function _obtenerSello($idVucem) {
        $mppr = new Trafico_Model_VucemMapper();
        if (($arr = $mppr->obtenerVucem($idVucem))) {
            if ($arr["idSelloAgente"] == null && $arr["idSelloCliente"] == null) {
                throw new Exception("No se ha establecido sello para enviar COVE.");
            }
            if ($arr["idSelloCliente"] !== null) {
                $ss = new Trafico_Model_SellosClientes();
                $sello = $ss->obtenerPorId($arr["idSelloCliente"]);
            }
            if ($arr["idSelloAgente"] !== null) {
                $ss = new Trafico_Model_SellosAgentes();
                $sello = $ss->obtenerPorId($arr["idSelloAgente"]);
            }
        }
        if (isset($sello)) {
            return $sello;
        }
        throw new Exception("No se encontro sello.");
    }
    
    public function _armarEdocument($idVucem, $idTrafico, $idArchivo, $tipoDocumento) {
        $sello = $this->_obtenerSello($idVucem);
        $uti = new Utilerias_Vucem(false, true);
        $mdl = new Trafico_Model_TraficosMapper();
        $mdd = new Archivo_Model_RepositorioMapper();
        $rfc = new Trafico_Model_RfcConsultaMapper();
        $trafico = $mdl->obtenerPorId($idTrafico);
        $archivo = $mdd->getFileById($idArchivo);
        if (empty($sello)) {
            throw new Exception("No se encontro sello para cliente con RFC {$trafico["rfc"]}.");
        }
        if (isset($trafico["id"]) && isset($archivo["id"])) {
            $rfcConsulta = $rfc->rfcEdocument($trafico["idCliente"]);
            $nombreDocumento = substr($archivo["nom_archivo"], 0, -4);
            $data = array(
                "tipoDoc" => $tipoDocumento,
                "idTipoDocumento" => $tipoDocumento,
                "nomArchivo" => $nombreDocumento,
                "nombreDocumento" => $nombreDocumento,
                "archivo" => base64_encode(file_get_contents($archivo["ubicacion"])),
                "hash" => sha1_file($archivo["ubicacion"]),
                "email" => "soporte@oaq.com.mx",
                "correoElectronico" => "soporte@oaq.com.mx",
                "rfcConsulta" => isset($rfcConsulta["rfc"]) ? $rfcConsulta["rfc"] : "OAQ030623UL8",
                "rfc" => $sello["rfc"],
                "razonSocial" => $sello["razon"],
            );
            $data["cadena"] = "|{$data["rfc"]}|{$data["correoElectronico"]}|{$data["idTipoDocumento"]}|{$data["nombreDocumento"]}|{$data["rfcConsulta"]}|{$data["hash"]}|";
            $data["firma"] = $uti->firmar($sello, $data["cadena"]);
            
            return $data;
        }
    }

    public function enviarEdocument($idVucem, $idTrafico, $idArchivo, $tipoDocumento, $send = true) {
        
        $sello = $this->_obtenerSello($idVucem);
        
        $uti = new Utilerias_Vucem(false, true);
        $mdl = new Trafico_Model_TraficosMapper();
        $mdd = new Archivo_Model_RepositorioMapper();
        $rfc = new Trafico_Model_RfcConsultaMapper();
        $trafico = $mdl->obtenerPorId($idTrafico);
        
        $archivo = $mdd->getFileById($idArchivo);
        if (empty($sello)) {
            throw new Exception("No se encontro sello para cliente con RFC {$trafico["rfc"]}.");
        }
        if (isset($trafico["id"]) && isset($archivo["id"])) {
            if ($sello["figura"] == 1) {
                $rfcConsulta["rfc"] = $trafico["rfcCliente"];    
            } else {
                $rfcConsulta["rfc"] = $rfc->rfcEdocument($trafico["idCliente"]);
            }
            $nombreDocumento = substr($archivo["nom_archivo"], 0, -4);
            
            $tbl = new Trafico_Model_VucemMapper();
            $arrv = $tbl->obtenerVucem($idVucem);
            
            if (isset($arrv["ubicacion"]) && file_exists($arrv["ubicacion"])) {
                $archivo_base64 = base64_encode(file_get_contents($arrv["ubicacion"]));
                $archivo_hash = sha1_file($arrv["ubicacion"]);
            } else {
                $archivo_base64 = base64_encode(file_get_contents($archivo["ubicacion"]));
                $archivo_hash = sha1_file($archivo["ubicacion"]);
            }
            
            $data["archivo"] = array(
                "idTipoDocumento" => $tipoDocumento,
                "nombreDocumento" => $nombreDocumento,
                "archivo" => $archivo_base64,
                "hash" => $archivo_hash,
                "correoElectronico" => "soporte@oaq.com.mx",
                "rfcConsulta" => isset($rfcConsulta["rfc"]) ? $rfcConsulta["rfc"] : "OAQ030623UL8",
            );
            
            unset($archivo_base64);
            unset($archivo_hash);
            
            $data["usuario"] = array(
                "username" => $sello["rfc"],
                "password" => $sello["ws_pswd"],
                "certificado" => $sello["cer"],
                "key" => openssl_get_privatekey(base64_decode($sello["spem"]), $sello["spem_pswd"]),
                "new" => isset($sello["sha"]) ? true : false,
            );
            
            if ($send == true) {
                $this->xml = $uti->xmlEdocument($data);
                unset($data);
                return $this->_enviarXmlEdocument();
            } else {
                $xml = $uti->xmlEdocument($data);
                unset($data);
                return $xml;
            }
        }
    }

    protected function _enviarXmlEdocument() {
        $serv = new OAQ_Servicios();
        $serv->setXml($this->xml);
        $serv->consumirServicioEdocument();
        $response = $serv->getResponse();
        $resp = new OAQ_Respuestas();
        $arr = $resp->analizarRespuesta($response);
        return $arr;
    }

    public function analizarEnvioCove(Trafico_Model_VucemMapper $mppr, $idTrafico, $idVucem, $idUsuario, $resp) {
        $row = array(
            "idTrafico" => $idTrafico,
            "idVucem" => $idVucem,
            "idUsuario" => $idUsuario,
            "creado" => date("Y-m-d H:i:s"),
        );
        if (isset($resp["numeroOperacion"]) && $resp["error"] == false) {
            $mppr->actualizar($idVucem, array(
                "numeroOperacion" => $resp["numeroOperacion"],
                "enviado" => date("Y-m-d H:i:s"),
            ));
            $row["enviado"] = 1;
            $row["numeroOperacion"] = $resp["numeroOperacion"];
            if (is_array($resp["messages"])) {
                $msgs = '';
                foreach ($resp["messages"] as $item) {
                    $msgs .= utf8_decode($item);
                }
                $row["mensaje"] = $msgs;
            }
        } else {
            $mppr->actualizar($idVucem, array(
                "numeroOperacion" => null,
                "respuesta" => null,
                "enviado" => null,
                "error" => 1,
            ));
            $row["enviado"] = 0;
            $row["error"] = 1;
            if (is_array($resp["messages"])) {
                $msgs = '';
                foreach ($resp["messages"] as $item) {
                    $msgs .= utf8_decode($item);
                }
                $row["mensajeError"] = $msgs;
            }
        }
        if (isset($row) && !empty($row)) {
            $log = new Trafico_Model_TraficoVucemLog();
            $log->agregar($row);
            return true;
        } else {
            throw new Exception("No se recibio respuesta de VUCEM.");
        }
        return true;
    }

    public function analizarEnvioEdocument(Trafico_Model_VucemMapper $mppr, $idTrafico, $idVucem, $idUsuario, $resp) {
        $row = array(
            "idTrafico" => $idTrafico,
            "idVucem" => $idVucem,
            "idUsuario" => $idUsuario,
            "creado" => date("Y-m-d H:i:s"),
        );
        if (isset($resp["numeroOperacion"]) && $resp["error"] == false) {
            $mppr->actualizar($idVucem, array(
                "numeroOperacion" => $resp["numeroOperacion"],
                "enviado" => date("Y-m-d H:i:s"),
            ));
            $row["enviado"] = 1;
            $row["numeroOperacion"] = $resp["numeroOperacion"];
            if (is_array($resp["messages"])) {
                $msgs = '';
                foreach ($resp["messages"] as $item) {
                    $msgs .= utf8_decode($item);
                }
                $row["mensaje"] = $msgs;
            }
        } else {
            $mppr->actualizar($idVucem, array(
                "numeroOperacion" => null,
                "respuesta" => null,
                "enviado" => null,
                "error" => 1,
            ));
            $row["enviado"] = 0;
            $row["error"] = 1;
            if (is_array($resp["messages"])) {
                $msgs = '';
                foreach ($resp["messages"] as $item) {
                    $msgs .= utf8_decode($item);
                }
                $row["mensajeError"] = $msgs;
            }
        }
        if (isset($row) && !empty($row)) {
            $log = new Trafico_Model_TraficoVucemLog();
            $log->agregar($row);
            return true;
        } else {
            throw new Exception("No se recibio respuesta de VUCEM.");
        }
        return true;
    }
    
    public function consultaRespestaCove(Trafico_Model_TraficoVucemLog $mppr, $idLog, $idVucem, $numeroOperacion, $username, $idUsuario) {
        $vu = new Trafico_Model_TraficoVucem();
        $resp = $this->consultaRespuesta($idVucem, $numeroOperacion);
        if (!empty($resp)) {
            if (isset($resp["edocument"]) && $resp["error"] == false) {
                $mppr->actualizar($idLog, array(
                    "edocument" => $resp["edocument"],
                    "adenda" => isset($resp["numeroAdenda"]) ? $resp["numeroAdenda"] : null,
                    "error" => 0,
                    "procesado" => 1,
                    "actualizado" => date('Y-m-d H:i:s'),
                ));
                $this->actualizarCoveEnFactura($idVucem, $resp["edocument"], isset($resp["numeroAdenda"]) ? $resp["numeroAdenda"] : null);
                $vu->actualizar($idVucem, array(
                    "edocument" => $resp["edocument"],
                    "adenda" => isset($resp["numeroAdenda"]) ? $resp["numeroAdenda"] : null,
                ));
                //$this->guardarFacturaEnExpediente($idVucem, $username, $idUsuario, $resp["edocument"]);
                $this->guardarDetalleCoveXmlPdf($idVucem, $username, $idUsuario);
                return true;
            } else if (!isset($resp["edocument"]) && $resp["error"] == true) {
                if (isset($resp["messages"]) && is_array($resp["messages"])) {
                    $msgs = '';
                    foreach ($resp["messages"] as $item) {
                        $msgs .= utf8_decode($item);
                    }
                } 
                if (isset($resp["message"])) {
                    $msgs = $resp["message"];
                }
                $mppr->actualizar($idLog, array(
                    "edocument" => null,
                    "error" => 1,
                    "procesado" => 1,
                    "mensajeError" => $msgs,
                    "actualizado" => date('Y-m-d H:i:s'),
                ));
                return true;
            }
        }
        return;
    }
    
    public function actualizarEdocumentEnRepositorio($idVucem, $edocument) {
        $mppr = new Trafico_Model_VucemMapper();
        $arr = $mppr->obtenerVucem($idVucem);
        if (isset($arr["idArchivo"])) {
            $mdd = new Archivo_Model_RepositorioMapper();
            $mdd->update($arr["idArchivo"], array(
                "edocument" => $edocument,
            ));
        }    
    }
    
    public function consultaRespuestaEdocument(Trafico_Model_TraficoVucemLog $mppr, $idLog, $idVucem, $numeroOperacion, $username) {
        $resp = $this->consultaRespuestaEd($idVucem, $numeroOperacion);
        if (!empty($resp)) {
            if (isset($resp["edocument"]) && $resp["error"] == false) {
                $mppr->actualizar($idLog, array(
                    "edocument" => $resp["edocument"],
                    "error" => 0,
                    "procesado" => 1,
                    "actualizado" => date('Y-m-d H:i:s'),
                ));
                $this->actualizarEdocumentEnRepositorio($idVucem, $resp["edocument"]);                            
                //$this->guardarEdocumentEnExpediente($idVucem, $resp["edocument"], $resp["numeroDeTramite"], $tipoDocumento, $username);
                $this->guardarEdocumentXmlPdf($idVucem, $username, null);
                return true;
            } else if (!isset($resp["edocument"]) && $resp["error"] == true) {
                if (isset($resp["messages"]) && is_array($resp["messages"])) {
                    $msgs = '';
                    foreach ($resp["messages"] as $item) {
                        $msgs .= utf8_decode($item);
                    }
                } 
                if (isset($resp["message"])) {
                    $msgs = $resp["message"];
                }
                $mppr->actualizar($idLog, array(
                    "edocument" => null,
                    "error" => 1,
                    "procesado" => 1,
                    "mensajeError" => $msgs,
                    "actualizado" => date('Y-m-d H:i:s'),
                ));
                return true;
            }
        }
        return;
    }
    
    protected function _enviarXmlEdocumentRespuesta() {
        $serv = new OAQ_Servicios();
        $serv->setXml($this->xml);
        $serv->consultaEstatusEdocument();
        $response = $serv->getResponse();
        $resp = new OAQ_Respuestas();
        $arr = $resp->analizarRespuesta($response);
        return $arr;
    }

    public function consultaRespuestaEd($idVucem, $numOperacion) {
        $sello = $this->_obtenerSello($idVucem);
        $mppr = new Trafico_Model_VucemMapper();
        $uti = new Utilerias_Vucem(false, true);
        if (($arr = $mppr->obtenerVucem($idVucem))) {
            if ($arr["idArchivo"]) {
                $data["consulta"] = array(
                    "operacion" => $numOperacion,
                );
                $data["usuario"] = array(
                    "username" => $sello["rfc"],
                    "password" => $sello["ws_pswd"],
                    "certificado" => $sello["cer"],
                    "key" => openssl_get_privatekey(base64_decode($sello["spem"]), $sello["spem_pswd"]),
                    "new" => isset($sello["sha"]) ? true : false,
                );
                $xml = $uti->consultaEstatusOperacionEdocument($data);
                $this->xml = $xml;
                $resp = $this->_enviarXmlEdocumentRespuesta();
                if (isset($resp["edocument"])) {
                    $mppr->actualizar($idVucem, array(
                        "edocument" => $resp["edocument"],
                        "error" => 0,
                        "respuesta" => date('Y-m-d H:i:s'),
                        "actualizado" => date('Y-m-d H:i:s'),
                    ));
                    return $resp;
                } else {
                    $mppr->actualizar($idVucem, array(
                        "edocument" => null,
                        "error" => 1,
                        "respuesta" => date('Y-m-d H:i:s'),
                        "actualizado" => date('Y-m-d H:i:s'),
                    ));
                    return $resp;
                }
            }
        }
    }

    public function guardarDetalleCoveXmlPdf($id, $usuario, $idUsuario) {
        $mppr = new Trafico_Model_VucemMapper();
        $arr = $mppr->obtenerVucem($id);
        if (!empty($arr)) {
            $trafico = new OAQ_Trafico(array("idTrafico" => $arr["idTrafico"], "usuario" => $usuario, "idUsuario" => $idUsuario));
            if (isset($arr["idFactura"])) {
                
                if (!isset($arr["archivoCove"]) || !file_exists($arr["archivoCove"])) {

                    $misc = new OAQ_Misc();
                    if (APPLICATION_ENV == "production") {
                        $misc->set_baseDir($this->appConfig->getParam("expdest"));
                    } else {
                        $misc->set_baseDir("D:\\xampp\\tmp\\expedientes");
                    }

                    $this->setPatente($trafico->getPatente());
                    $this->setAduana($trafico->getAduana());
                    $this->setPedimento($trafico->getPedimento());
                    $this->setReferencia($trafico->getReferencia());
                    $xml = $this->enviarCove($id, $arr["idTrafico"], $arr["idFactura"], false);

                    $directory = $misc->nuevoDirectorioExpediente($trafico->getPatente(), $trafico->getAduana(), $misc->trimUpper($trafico->getReferencia()));

                    $xml_filename = $directory . DIRECTORY_SEPARATOR . $this->getFilename();
                    if (isset($arr["edocument"])) {
                        if (isset($arr["adenda"])) {
                            $xml_filename = $directory . DIRECTORY_SEPARATOR . preg_replace('/^COVE_/', $arr["adenda"] . '_', $this->getFilename());
                        } else {
                            $xml_filename = $directory . DIRECTORY_SEPARATOR . preg_replace('/^COVE_/', $arr["edocument"] . '_', $this->getFilename());
                        }
                    }
                    $pdf_filename = preg_replace('/\..+$/', '.' . 'pdf', $xml_filename);
                    if (!file_exists($xml_filename)) {
                        if (file_put_contents($xml_filename, $xml)) {
                            $trafico->agregarArchivoExpediente(21, $xml_filename, $arr["edocument"]);
                        }
                    }

                    if (!file_exists($pdf_filename)) {

                        $data = array(
                            'xml' => $xml,
                            'patente' => $trafico->getPatente(),
                            'aduana' => $trafico->getAduana(),
                            'pedimento' => $trafico->getPedimento(),
                            'referencia' => $trafico->getReferencia(),
                            'cove' => $arr["edocument"],
                            'rfcConsulta' => null,
                            'actualizado' => date('Y-m-d H:i:s'),
                            'filename' => $pdf_filename,
                            'creado' => date('Y-m-d H:i:s'),
                        );

                        $print = new OAQ_Imprimir_CoveDetalle2019($data, "P", "pt", "LETTER");
                        $print->set_filename($pdf_filename);
                        $print->Create();
                        $print->Output($pdf_filename, "F");

                        $trafico->agregarArchivoExpediente(22, $pdf_filename, $arr["edocument"]);
                    }
                    return true;
                    
                }
            }
        }
        return;
    }

    public function guardarEdocumentXmlPdf($id, $usuario, $idUsuario) {
        $mppr = new Trafico_Model_VucemMapper();
        $arr = $mppr->obtenerVucem($id);
        if (!empty($arr)) {
            $trafico = new OAQ_Trafico(array("idTrafico" => $arr["idTrafico"], "usuario" => $usuario, "idUsuario" => $idUsuario));
            $misc = new OAQ_Misc();
            
            $data = $this->_armarEdocument($id, $arr["idTrafico"], $arr["idArchivo"], $arr["tipoDocumento"]);
            $data["edoc"] = $arr["edocument"];
            $data["patente"] = $trafico->getPatente();
            $data["aduana"] = $trafico->getAduana();
            $data["referencia"] = $trafico->getReferencia();
            $data["pedimento"] = $trafico->getPedimento();
            $data["numTramite"] = $arr["numeroOperacion"];
            $data["actualizado"] = date("Y-m-d H:i:s");
            
            if (APPLICATION_ENV == 'production') {
                $directory = $this->appConfig->getParam("expdest");
            } else {
                $directory = "D:\\xampp\\tmp\\expedientes";
            }
            $sello = $this->_obtenerSello($id);
            
            $directory = $misc->nuevoDirectorio($directory, $trafico->getPatente(), $trafico->getAduana(), $trafico->getReferencia());
            
            $xml_filename = "ED" . $data["edoc"] . "_" . $trafico->getAduana() . '-' . $trafico->getPatente() . '-' . $trafico->getPedimento() . "_" . preg_replace('/\..+$/', '.xml', $arr["nombreArchivo"]);
            $pdf_filename = "ED" . $data["edoc"] . "_" . $trafico->getAduana() . '-' . $trafico->getPatente() . '-' . $trafico->getPedimento() . "_" . preg_replace('/\..+$/', '', $arr["nombreArchivo"]);
            
            $ed["archivo"] = array(
                "idTipoDocumento" => $arr["tipoDocumento"],
                "nombreDocumento" => $arr["nombreArchivo"],
                "archivo" => base64_encode(file_get_contents($directory . DIRECTORY_SEPARATOR . $arr["nombreArchivo"])),
                "hash" => sha1_file($directory . DIRECTORY_SEPARATOR . $arr["nombreArchivo"]),
                "correoElectronico" => "soporte@oaq.com.mx",
                "rfcConsulta" => "OAQ030623UL8"
            );
            
            $ed["usuario"] = array(
                "username" => $sello["rfc"],
                "password" => $sello["ws_pswd"],
                "certificado" => $sello["cer"],
                "key" => openssl_get_privatekey(base64_decode($sello["spem"]), $sello["spem_pswd"]),
                "new" => null,
            );
            
            $xml = new OAQ_Xml(false, true);
            $xml->set_dir($directory);
            $xml->xmlEdocument($data, true);
            $xml->saveToDisk(null, $xml_filename);
            if (file_exists($directory . DIRECTORY_SEPARATOR . $xml_filename)) {
                $trafico->agregarArchivoExpediente(27, $directory . DIRECTORY_SEPARATOR . $xml_filename, $arr["edocument"]);
            }
            
            $print = new OAQ_PrintEdocuments();
            $print->set_data($data);
            $print->set_dir($directory);
            $print->saveEdocument($pdf_filename);
            
            if (file_exists($directory . DIRECTORY_SEPARATOR . $pdf_filename . '.pdf')) {
                $trafico->agregarArchivoExpediente(56, $directory . DIRECTORY_SEPARATOR . $pdf_filename . '.pdf', $arr["edocument"]);
            }
            
        }
        return;
    }
    
}
