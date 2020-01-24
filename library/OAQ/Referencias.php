<?php

class OAQ_Referencias {

    protected $idAduana;
    protected $patente;
    protected $aduana;
    protected $pedimento;
    protected $referencia;
    protected $usuario;
    protected $rfcCliente;
    protected $idTrafico;
    protected $idUsuario;
    protected $traficos;
    protected $rs;
    protected $directorio;

    public function __construct(array $options = null) {
        if (is_array($options)) {
            $this->setOptions($options);
        }
    }

    public function __set($name, $value) {
        $method = "set" . $name;
        if (("mapper" == $name) || !method_exists($this, $method)) {
            throw new Exception("Invalid property");
        }
        $this->$method($value);
    }

    public function __get($name) {
        $method = "get" . $name;
        if (("mapper" == $name) || !method_exists($this, $method)) {
            throw new Exception("Invalid property");
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

    function getPatente() {
        return $this->patente;
    }

    function getAduana() {
        return $this->aduana;
    }

    function getPedimento() {
        return $this->pedimento;
    }

    function getReferencia() {
        return $this->referencia;
    }

    function getUsuario() {
        return $this->usuario;
    }

    function setPatente($patente) {
        $this->patente = $patente;
    }

    function setAduana($aduana) {
        $this->aduana = $aduana;
    }

    function setPedimento($pedimento) {
        $this->pedimento = $pedimento;
    }

    function setReferencia($referencia) {
        $this->referencia = $referencia;
    }

    function setUsuario($usuario) {
        $this->usuario = $usuario;
    }
    
    function setIdUsuario($idUsuario) {
        $this->idUsuario = $idUsuario;
    }

    function setIdTrafico($idTrafico) {
        $this->idTrafico = $idTrafico;
    }
    
    function setIdAduana($idAduana) {
        $this->idAduana = $idAduana;
    }
    
    function setRfcCliente($rfcCliente) {
        $this->rfcCliente = $rfcCliente;
    }
    
    function getDirectorio() {
        return $this->directorio;
    }

    function setDirectorio($directorio) {
        $this->directorio = $directorio;
    }
    
    function getIdTrafico() {
        return $this->idTrafico;
    }
        
    protected function _consecutivoPedimento($patente, $aduana) {
        $mppr = new Bitacora_Model_BitacoraPedimentos();
        $pedimento = $mppr->ultimoPedimento($patente, $aduana);
        if ($pedimento == 0) {
            if ((int) date("Y") == 2017) {
                return 7000000;
            }
            if ((int) date("Y") == 2018) {
                return 8000000;
            }
            if ((int) date("Y") == 2019) {
                return 9000000;
            }
            if ((int) date("Y") == 2020) {
                return 0200000;
            }
        }
        return str_pad($pedimento + 1, 7, '0', STR_PAD_LEFT);
    }

    public function consecutivo() {
        if ($this->idAduana == 1) {
            $this->pedimento = $this->_consecutivoPedimento(3589, 640);
            $this->referencia = "Q" . date("y") . substr($this->pedimento, 2, 5);
            return true;
        } else if ($this->idAduana == 2) {
            $this->pedimento = $this->_consecutivoPedimento(3589, 640);
            $this->referencia = "Q" . date("y") . substr($this->pedimento, 2, 5);
            return true;
        } else {
            return;
        }
    }

    public function removerSufijos() {
        if (isset($this->referencia)) {
            if (preg_match("/C$|H$|R$|G$/", $this->referencia) && !preg_match("/-C$|-H$|-R$|-G$/", $this->referencia)) {
                return substr($this->referencia, 0, -1);
            } else if (preg_match("/-C$|-H$|-R$|-G$/", $this->referencia)) {
                return substr($this->referencia, 0, -2);
            } else {
                return $this->referencia;
            }
        } else {
            throw new Exception("Referencia is not been set!");
        }
    }
    
    public function agregarBitacora($mensaje) {
        try {
            $mppr = new Trafico_Model_BitacoraMapper();
            $mppr->agregar(array(
                "patente" => $this->patente,
                "aduana" => $this->aduana,
                "pedimento" => $this->pedimento,
                "referencia" => $this->referencia,
                "bitacora" => $mensaje,
                "usuario" => $this->usuario,
                "creado" => date("Y-m-d H:i:s"),
            ));
            return true;
        } catch (Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    public function crearTrafico($idAduana, $idCliente, $patente, $aduana, $pedimento, $referencia, $rfcCliente, $tipoOperacion, $fechaEta, $bl, $numFactura, $cvePedimento, $idUsuario, $planta = null) {
        $mapper = new Trafico_Model_TraficosMapper();
        if (($arr = $mapper->buscarTrafico($patente, $aduana, $pedimento, $referencia))) {
            return true;
        } else {
            $arr = array(
                "idCliente" => $idCliente,
                "idAduana" => $idAduana,
                "idPlanta" => isset($planta) ? $planta : null,
                "idUsuario" => $idUsuario,
                "patente" => $patente,
                "aduana" => $aduana,
                "pedimento" => $pedimento,
                "referencia" => $referencia,
                "tipoCambio" => null,
                "cvePedimento" => $cvePedimento,
                "ie" => $tipoOperacion,
                "rfcCliente" => $rfcCliente,
                "fechaEta" => date("Y-m-d H:i:s", strtotime($fechaEta)),
                "blGuia" => $bl,
                "facturas" => $numFactura,
                "estatus" => 1,
                "creado" => date("Y-m-d H:i:s")
            );
            if (($id = $mapper->nuevoTrafico($arr))) {
                $this->idTrafico = $id;
                return true;
            }
            return;
        }
        return;
    }
    
    protected function _crearDirectorio($patente, $aduana, $referencia) {
        $appConfig = new Application_Model_ConfigMapper();
        $expDigital = $appConfig->getParam("expdest");
        if (file_exists($expDigital . DIRECTORY_SEPARATOR . $patente . DIRECTORY_SEPARATOR . $aduana . DIRECTORY_SEPARATOR . $referencia)) {
            $this->directorio = $expDigital . DIRECTORY_SEPARATOR . $patente . DIRECTORY_SEPARATOR . $aduana . DIRECTORY_SEPARATOR . $referencia;
            return true;
        } else {
            if (!file_exists($expDigital . DIRECTORY_SEPARATOR . $patente)) {
                mkdir($expDigital . DIRECTORY_SEPARATOR . $patente);
            }
            if (!file_exists($expDigital . DIRECTORY_SEPARATOR . $patente . DIRECTORY_SEPARATOR . $aduana)) {
                mkdir($expDigital . DIRECTORY_SEPARATOR . $patente . DIRECTORY_SEPARATOR . $aduana);
            }
            if (!file_exists($expDigital . DIRECTORY_SEPARATOR . $patente . DIRECTORY_SEPARATOR . $aduana . DIRECTORY_SEPARATOR . $referencia)) {
                mkdir($expDigital . DIRECTORY_SEPARATOR . $patente . DIRECTORY_SEPARATOR . $aduana . DIRECTORY_SEPARATOR . $referencia);
            }
        }
        if (file_exists($expDigital . DIRECTORY_SEPARATOR . $patente . DIRECTORY_SEPARATOR . $aduana . DIRECTORY_SEPARATOR . $referencia)) {
            $this->directorio = $expDigital . DIRECTORY_SEPARATOR . $patente . DIRECTORY_SEPARATOR . $aduana . DIRECTORY_SEPARATOR . $referencia;
            return true;
        }
        return;
    }

    public function crearDirectorio($patente, $aduana, $referencia) {
        $misc = new OAQ_Misc();
        if ($this->_crearDirectorio($patente, $aduana, $misc->replace($referencia))) {
            return true;
        }
        return;
    }

    public function copiarArchivo($origen) {
        if (isset($this->directorio) && $this->directorio != "") {
            copy($origen, $this->directorio . DIRECTORY_SEPARATOR . basename($origen));
            if (file_exists($this->directorio . DIRECTORY_SEPARATOR . basename($origen))) {
                return true;
            }
            return;
        }
        return;
    }

    public function crearRepositorio($patente, $aduana, $referencia, $usuario, $rfcCliente, $pedimento, $idTrafico = null) {
        $rs = new Archivo_Model_RepositorioMapper();
        $this->patente = $patente;
        $this->aduana = $aduana;
        $this->pedimento = $pedimento;
        $this->referencia = $referencia;
        $this->rfcCliente = $rfcCliente;
        $this->usuario = $usuario;
        if (isset($idTrafico)) {
            $this->idTrafico = $idTrafico;
        }
        $this->nuevoRepositorioIndex();
        if($rs->buscarRepositorio($patente, $aduana, $referencia, $idTrafico) !== true) {
            $rs->nuevoRepositorio($patente, $aduana, $pedimento, $referencia, $rfcCliente, $usuario, $idTrafico);
        }
    }
    
    public function agregarArchivo($tipoArchivo, $archivo) {
        $rs = new Archivo_Model_RepositorioMapper();
        if(!($rs->buscarArchivo($tipoArchivo, $this->patente, $this->aduana, $this->referencia, basename($archivo)))) {
            $this->copiarArchivo(realpath($archivo));
            if (file_exists($this->directorio . DIRECTORY_SEPARATOR . basename($archivo))) {
                $arr = array(
                    "rfc_cliente" => $this->rfcCliente,
                    "tipo_archivo" => $tipoArchivo,
                    "patente" => $this->patente,
                    "aduana" => $this->aduana,
                    "pedimento" => $this->pedimento,
                    "referencia" => $this->referencia,
                    "ubicacion" => realpath($this->directorio . DIRECTORY_SEPARATOR . basename($archivo)),
                    "nom_archivo" => basename($archivo),
                    "creado" => date("Y-m-d H:i:s"),
                    "usuario" => "AutoEmail",
                );
                $rs->agregar($arr);
                return true;
            }
        }
        return;
    }
    
    protected function _buscarEnRepositorio() {
        if (($arr = $this->rs->buscarEnRepositorio($this->patente, $this->aduana, $this->referencia))) {
            return $arr;
        }
        return;
    }

    protected function _buscarEnTrafico() {
        $traffic = new Trafico_Model_TraficosMapper();
        if (($arr = $traffic->buscar($this->patente, $this->aduana, $this->referencia))) {
            return $arr;
        }
        return;
    }

    protected function _buscarEnSolicitudes() {
        $traffic = new Trafico_Model_TraficoSolicitudesMapper();
        if (($arr = $traffic->buscar($this->patente, $this->aduana, $this->referencia))) {
            return $arr;
        }
    }

    protected function _agregarRepositorio($pedimento, $rfcCliente) {
        if ($this->rs->buscarRepositorio($this->patente, $this->aduana, $this->referencia) !== true) {
            $this->rs->nuevoRepositorio($this->patente, $this->aduana, $pedimento, $this->referencia, $rfcCliente, $this->usuario);
            return true;
        }
        return;
    }

    public function crearRepositorioRest($patente, $aduana, $referencia) {
        $trafico = new OAQ_Trafico();
        $arr = $trafico->datosDeSistema($patente, $aduana, $referencia);
        if (!empty($arr)) {
            return $arr;
        }
        return;
    }
    
    public function crearRepositorioSitawin() {
        $misc = new OAQ_Misc();
        $db = $misc->sitawinTrafico($this->patente, $this->aduana);
        $this->rs = new Archivo_Model_RepositorioMapper();
        if (isset($db) && !empty($db)) {
            $arr = $db->infoPedimentoBasicaReferencia($this->referencia);
            if (count($arr) > 0) {
                $this->_agregarRepositorio($arr["pedimento"], $arr["rfcCliente"], $this->usuario);
            }
            $arr["sistema"] = "Sitawin";
        }
        if (!isset($arr)) {
            if (($arr = $this->_buscarEnTrafico())) {
                $this->_agregarRepositorio($arr["pedimento"], $arr["rfcCliente"], $this->usuario);
                $arr["sistema"] = "Trafico";
            } elseif (($arr = $this->_buscarEnSolicitudes())) {
                $this->_agregarRepositorio($arr["pedimento"], $arr["rfcCliente"], $this->usuario);
                $arr["sistema"] = "Solicitud";
            }
        }
        if (isset($arr) && !empty($arr)) {
            if ((isset($arr["pedimento"]) && $arr["pedimento"] != "") && (isset($arr["rfcCliente"]) && $arr["rfcCliente"] != "")) {
                $this->pedimento = $arr["pedimento"];
                $this->rfcCliente = $arr["rfcCliente"];
                $this->nuevoRepositorioIndex();
                return array("pedimento" => $arr["pedimento"], "rfcCliente" => $arr["rfcCliente"], "sistema" => $arr["sistema"]);
            }
        } else {
            return;
        }
    }
    
    public function buscarTrafico() {
        $trafico = new OAQ_Trafico(array("patente" => $this->patente, "aduana" => $this->aduana, "referencia" => $this->referencia));
        if(($id = $trafico->buscarTrafico())) {
            return $id;
        }
        return;
    }
    
    public function actualizarFechaFacturacion($fecha, $folio = null) {
        $trafico = new OAQ_Trafico(array("idTrafico" => $this->idTrafico, "idUsuario" => $this->idUsuario));
        $trafico->setFolio($folio);
        $trafico->actualizarFecha(30, $fecha);
        $trafico->actualizarFechaFacturacion($fecha);
        
    }

    public function buscarInfo() {
        $misc = new OAQ_Misc();
        $db = $misc->sitawin($this->patente, $this->aduana);
        $this->rs = new Archivo_Model_RepositorioMapper();
        if (isset($db) && !empty($db)) {
            $arr = $db->datosPedimento($this->referencia);
        }
        if (isset($arr) && !empty($arr)) {
            return ["pedimento" => $arr["pedimento"], "rfcCliente" => $arr["rfcCliente"]];
        } else {
            return;
        }
    }
    
    public function nuevoRepositorioIndex() {
        $model = new Archivo_Model_RepositorioIndex();
        if (!($id = $model->buscar($this->patente, $this->aduana, $this->referencia))) {
            $customers = new Trafico_Model_ClientesMapper();
            if (isset($this->rfcCliente) && $this->rfcCliente != "") {
                $customer = $customers->buscar($this->rfcCliente);

                $adu = new Trafico_Model_TraficoAduanasMapper();
                $idAduana = null;
                if (isset($this->patente) && isset($this->aduana)) {
                    $idAduana = $adu->idAduana($this->patente, $this->aduana);
                }
                $id = $model->agregar($idAduana, $this->rfcCliente, $this->patente, $this->aduana, $this->pedimento, $this->referencia, $this->usuario, $customer["nombre"], isset($this->idTrafico) ? $this->idTrafico : null);

            return $id;
            }
        } else {
            return $id;
        }
        return;
    }

    public function restriccionesAduanas($idUsuario, $role) {
        $mapper = new Trafico_Model_TraficoUsuAduanasMapper();
        $arr = array(
            "aduanas" => null,
            "rfcs" => null,
        );
        if (in_array($role, array("trafico", "super", "super_admon"))) {
            $arr["idsAduana"] = $mapper->todasAduanas();
        } else if (in_array($role, array("inhouse", "proveedor"))) {
            $inh = new Usuarios_Model_UsuarioInhouse();
            $mppr = new Usuarios_Model_UsuariosDocumentos();
            $arr["rfcs"] = $inh->obtenerRfcClientes($idUsuario);
            $dd = $mppr->obtener($idUsuario);
            $arr["documentos"] = $dd["documentos"];
        } else {
            $arr["aduanas"] = $mapper->obtenerAduanasUsuario($idUsuario);
        }
        return $arr;
    }
    
    public function obtenerClientes($rfcs = null, $idClientes = null) {
        try {
            $mppr = new Trafico_Model_ClientesMapper();
            if (isset($idClientes)) {
                $rows = $mppr->obtenerClientes(null, $idClientes);
                if (!(empty($rows))) {
                    $arr = array();
                    foreach ($rows as $item) {
                        $arr[] = array(
                            "id" => $item["id"],
                            "razonSocial" => $item["nombre"],
                        );
                    }
                }
                return $arr;
            }
            if (isset($rfcs)) {
                $rows = $mppr->obtenerClientes($rfcs);
                if (!(empty($rows))) {
                    $arr = array();
                    foreach ($rows as $item) {
                        $arr[] = array(
                            "id" => $item["id"],
                            "razonSocial" => $item["nombre"],
                        );
                    }
                }
                return $arr;
            }
            return;            
        } catch (Exception $ex) {

        }
    }
    
    public function restricciones($idUsuario, $role) {
        $inh = new Usuarios_Model_UsuarioInhouse();
        $mapper = new Trafico_Model_TraficoUsuAduanasMapper();
        $arr = array(
            "idsAduana" => null,
            "rfcs" => null,
            "idsClientes" => null,
        );
        if (in_array($role, array("trafico", "super", "super_admon"))) {
            
            $arr["idsAduana"] = $mapper->todasAduanas();
            $arr["idsClientes"] = $inh->obtenerTodosClientes();
            
        } else if (in_array($role, array("inhouse", "proveedor"))) {
            
            $mppr = new Usuarios_Model_UsuariosDocumentos();
            $arr["rfcs"] = $inh->obtenerRfcClientes($idUsuario);
            
            $arr["idsClientes"] = $inh->obtenerIdClientes($idUsuario);
            $arr["idsAduana"] = $mapper->misAduanas($idUsuario);
            
            $dd = $mppr->obtener($idUsuario);
            $arr["documentos"] = $dd["documentos"];
            
        } else {
            $arr["idsAduana"] = $mapper->misAduanas($idUsuario);
        }
        return $arr;
    }
    
    protected function _buscarExpediente($patente, $aduana, $pedimento, $referencia) {
        $in = new Archivo_Model_Repositorio();
        $indx = new Archivo_Model_RepositorioIndex();
        if (( $id = $indx->buscar($patente, $aduana, $referencia, $pedimento))) {
            $array = [];
            $array["idRepositorio"] = (int) $id;
            $array["archivos"] = $in->archivos($referencia, $patente);
            return $array;
        }
        return;
    }
    
    protected function _actualizarClienteRepositorioIndex($idRepositorio, $idTrafico, $nombreCliente, $rfcCliente, $usuario) {
        $mppr = new Archivo_Model_RepositorioIndex();
        $arr = array(
            "idTrafico" => $idTrafico,
            "nombreCliente" => $nombreCliente,
            "rfcCliente" => $rfcCliente,
            "modificado" => date("Y-m-d H:i:s"),
            "modificadoPor" => $usuario,
        );
        if ($mppr->update($idRepositorio, $arr)) {
            return true;
        }
        return;
    }

    protected function _actualizarRepositorioIndex($idRepositorio, $idTrafico, $idAduana, $nombreCliente, $rfcCliente, $patente, $aduana, $pedimento, $referencia, $usuario) {
        $mppr = new Archivo_Model_RepositorioIndex();
        $arr = array(
            "idTrafico" => $idTrafico,
            "idAduana" => $idAduana,
            "nombreCliente" => $nombreCliente,
            "rfcCliente" => $rfcCliente,
            "patente" => $patente,
            "aduana" => $aduana,
            "pedimento" => $pedimento,
            "referencia" => $referencia,
            "modificado" => date("Y-m-d H:i:s"),
            "modificadoPor" => $usuario,
        );
        if ($mppr->update($idRepositorio, $arr)) {
            return true;
        }
        return;
    }
    
    protected function _actualizarClienteRepositorioArchivos($rfcCliente, $usuario, $arr) {
        $mppr = new Archivo_Model_RepositorioIndex();
        $array = array(
            "rfc_cliente" => $rfcCliente,
            "modificado" => date("Y-m-d H:i:s"),
            "modificadoPor" => $usuario,
        );
        foreach ($arr as $item) {
            $mppr->actualizarRegistroArchivo((int) $item["id"], $array);
        }
        return true;
    }

    protected function _actualizarRepositorioArchivos($patente, $aduana, $pedimento, $referencia, $rfcCliente, $usuario, $arr) {
        $mppr = new Archivo_Model_RepositorioIndex();
        $array = array(
            "rfc_cliente" => $rfcCliente,
            "patente" => $patente,
            "aduana" => $aduana,
            "pedimento" => $pedimento,
            "referencia" => $referencia,
            "modificado" => date("Y-m-d H:i:s"),
            "modificadoPor" => $usuario,
        );
        foreach ($arr as $item) {
            $mppr->actualizarRegistroArchivo((int) $item["id"], $array);
        }
        return true;
    }

    protected function _actualizarClienteTrafico($idTrafico, $idCliente, $rfcCliente) {
        if ($this->traficos->actualizarClienteTrafico($idTrafico, $idCliente, $rfcCliente)) {
            return true;
        }
        return;
    }
    
    protected function _actualizarTrafico($idTrafico, $idCliente, $idAduana, $patente, $aduana, $pedimento, $referencia, $rfcCliente, $tipoOperacion, $cvePedimento, $contenedorCaja = null, $nombreBuque = null) {
        $arr = array(
            "idCliente" => $idCliente,
            "idAduana" => $idAduana,
            "patente" => $patente,
            "aduana" => $aduana,
            "pedimento" => $pedimento,
            "referencia" => $referencia,
            "rfcCliente" => $rfcCliente,
            "ie" => $tipoOperacion,
            "cvePedimento" => $cvePedimento,
            "contenedorCaja" => isset($contenedorCaja) ? $contenedorCaja : null,
            "nombreBuque" => isset($nombreBuque) ? $nombreBuque : null,
            "actualizado" => date("Y-m-d H:i:s"),
        );
        if ($this->traficos->actualizarTrafico($idTrafico, $arr)) {
            return true;
        }
        return;
    }

    public function modificarTraficoReferencia($idTrafico, $idAduana, $idCliente, $pedimento, $referencia, $tipoOperacion, $cvePedimento, $contenedorCaja, $nombreBuque, $usuario) {
        $this->traficos = new Trafico_Model_TraficosMapper();
        $arr = $this->traficos->obtenerPorId($idTrafico);        
        if (!empty($arr)) {
            if ($pedimento == $arr["pedimento"] && $referencia == $arr["referencia"] && $idAduana == $arr["idAduana"] && $idCliente == $arr["idCliente"] && $cvePedimento == $arr["cvePedimento"] && $tipoOperacion == $arr["ie"]) {
                throw new Exception("Los datos son los mismos.");
            }
            $aduanas = new Trafico_Model_TraficoAduanasMapper();
            $clientes = new Trafico_Model_ClientesMapper();
            $adu = $aduanas->obtenerAduana($idAduana);
            $busq = $this->traficos->buscarTrafico($adu["patente"], $adu["aduana"], $pedimento, $referencia, $tipoOperacion, $cvePedimento);
            if (empty($busq)) {
                $idxo = $this->_buscarExpediente($arr["patente"], $arr["aduana"], $arr["pedimento"], $arr["referencia"]);
                $cli = $clientes->datosCliente($idCliente);
                if ($this->_actualizarTrafico($idTrafico, $idCliente, $idAduana, $adu["patente"], $adu["aduana"], $pedimento, $referencia, $cli["rfc"], $tipoOperacion, $cvePedimento, $contenedorCaja, $nombreBuque)) {
                    if (isset($idxo) && !empty($idxo)) {
                        if (isset($idxo["idRepositorio"])) {
                            $this->_actualizarRepositorioIndex($idxo["idRepositorio"], $idTrafico, $idAduana, $cli["nombre"], $cli["rfc"], $adu["patente"], $adu["aduana"], $pedimento, $referencia, $usuario);
                        }
                        if (isset($idxo["archivos"]) && !empty($idxo["archivos"])) {
                            $this->_actualizarRepositorioArchivos($adu["patente"], $adu["aduana"], $pedimento, $referencia, $cli["rfc"], $usuario, $idxo["archivos"]);
                        }
                    }
                    $this->patente = $arr["patente"];
                    $this->aduana = $arr["aduana"];
                    $this->pedimento = $pedimento;
                    $this->referencia = $referencia;
                    $this->usuario = $usuario;
                    $mensaje = "SE MODIFICO LA REFERENCIA " . $arr["referencia"] . " (RFC " . $arr["rfcCliente"] . ") A LA REFERENCIA " . strtoupper(trim($referencia)) . " (" . $cli["rfc"] . ").";
                    $this->agregarBitacora($mensaje);
                    return true;
                }
            } else if (!empty($busq) && $busq["idCliente"] !== $idCliente) {
                $idxo = $this->_buscarExpediente($arr["patente"], $arr["aduana"], $arr["pedimento"], $arr["referencia"]);
                $cli = $clientes->datosCliente($idCliente);
                if ($this->_actualizarClienteTrafico($idTrafico, $idCliente, $cli["rfc"])) {
                    if (isset($idxo) && !empty($idxo)) {
                        if (isset($idxo["idRepositorio"])) {
                            $this->_actualizarClienteRepositorioIndex($idxo["idRepositorio"], $idTrafico, $cli["nombre"], $cli["rfc"], $usuario);
                        }
                        if (isset($idxo["archivos"]) && !empty($idxo["archivos"])) {
                            $this->_actualizarClienteRepositorioArchivos($cli["rfc"], $usuario, $idxo["archivos"]);
                        }
                    }
                    return true;
                }
                throw new Exception("No se pudo actualizar cliente en referencia.");
            } else {
                throw new Exception("La referencia ya existe.");
            }
        } else {
            throw new Exception("No se encontraron datos.");
        }
    }

    public function modificarEntrada($idTrafico, $idBodega, $idCliente, $referencia, $usuario) {

        $mppr = new Bodega_Model_Entradas();

        $arr = $mppr->obtenerPorId($idTrafico);

        if (!empty($arr)) {
            if ($referencia == $arr["referencia"] && $idCliente == $arr["idCliente"] && $idBodega == $arr["idBodega"]) {
                throw new Exception("Los datos son los mismos.");
            }

            $clientes = new Trafico_Model_ClientesMapper();
            $repo = new Archivo_Model_RepositorioMapper();

            $busq = $mppr->buscarEntrada($idBodega, $referencia);

            if (empty($busq)) {
                $cli = $clientes->datosCliente($idCliente);
                $arr_n = array(
                    "idCliente" => $idCliente,
                    "idBodega" => $idBodega,
                    "referencia" => $referencia,
                    "rfcCliente" => $cli["rfc"],
                    "actualizado" => date("Y-m-d H:i:s"),
                );
                if ($mppr->actualizarEntrada($idTrafico, $arr_n)) {
                    $repo->actualizarIdTrafico($idTrafico, array("referencia" => $referencia));
                    return true;
                }

            } else {
                throw new Exception("La referencia ya existe.");
            }
        } else {
            throw new Exception("No se encontraron datos.");
        }
    }

}
