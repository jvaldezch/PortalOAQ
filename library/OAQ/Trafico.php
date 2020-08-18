<?php

/**
 * Description of EmailNotifications
 *
 * @author Jaime E. Valdez <jvaldezch@gmail.com>
 */
class OAQ_Trafico
{

    protected $idTrafico;
    protected $idCliente;
    protected $idRepositorio;
    protected $idAduana;
    protected $idFactura;
    protected $idProv;
    protected $tipoOperacion;
    protected $idPlanta;
    protected $clavePedimento;
    protected $rfcCliente;
    protected $traficos;
    protected $trafico;
    protected $clientes;
    protected $aduanas;
    protected $claves;
    protected $fecha;
    protected $bitacora;
    protected $usuario;
    protected $patente;
    protected $aduana;
    protected $pedimento;
    protected $referencia;
    protected $ie;
    protected $folio;
    protected $sistema;
    protected $idUsuario;
    protected $appconfig;
    protected $directorio;
    protected $misc;
    protected $_firephp;

    function setIdTrafico($idTrafico)
    {
        $this->idTrafico = $idTrafico;
    }

    function setFecha($fecha)
    {
        $this->fecha = $fecha;
    }

    function setUsuario($usuario)
    {
        $this->usuario = $usuario;
    }

    function setIdUsuario($idUsuario)
    {
        $this->idUsuario = $idUsuario;
    }

    function setIdBodega($idBodega)
    {
        $this->idBodega = $idBodega;
    }

    function setPatente($patente)
    {
        $this->patente = $patente;
    }

    function setAduana($aduana)
    {
        $this->aduana = $aduana;
    }

    function setPedimento($pedimento)
    {
        $this->pedimento = $pedimento;
    }

    function setReferencia($referencia)
    {
        $this->referencia = $referencia;
    }

    function setFolio($folio)
    {
        $this->folio = $folio;
    }

    function setIdFactura($idFactura)
    {
        $this->idFactura = $idFactura;
    }

    function getPatente()
    {
        return $this->patente;
    }

    function getAduana()
    {
        return $this->aduana;
    }

    function getPedimento()
    {
        return $this->pedimento;
    }

    function getClavePedimento()
    {
        return $this->clavePedimento;
    }

    function getIdBodega()
    {
        return $this->idBodega;
    }

    function getIdCliente()
    {
        return $this->idCliente;
    }

    function getIdAduana()
    {
        return $this->idAduana;
    }

    function getReferencia()
    {
        return $this->referencia;
    }

    function getTipoOperacion()
    {
        return $this->ie;
    }

    function setIdCliente($idCliente)
    {
        $this->idCliente = $idCliente;
    }

    function setIdAduana($idAduana)
    {
        $this->idAduana = $idAduana;
    }

    function setIe($ie)
    {
        $this->ie = $ie;
    }

    function setIdPlanta($idPlanta)
    {
        $this->idPlanta = $idPlanta;
    }

    function setClavePedimento($clavePedimento)
    {
        $this->clavePedimento = $clavePedimento;
    }

    function setRfcCliente($rfcCliente)
    {
        $this->rfcCliente = $rfcCliente;
    }

    function getRfcCliente()
    {
        return $this->rfcCliente;
    }

    public function __construct(array $options = null)
    {

        $this->_firephp = Zend_Registry::get("firephp");

        $this->appconfig = new Application_Model_ConfigMapper();
        $this->traficos = new Trafico_Model_TraficosMapper();
        $this->trafico = new Trafico_Model_Table_Traficos();
        $this->aduanas = new Trafico_Model_TraficoAduanasMapper();
        $this->clientes = new Trafico_Model_ClientesMapper();
        $this->claves = new Trafico_Model_TraficoCvePedMapper();
        $this->misc = new OAQ_Misc();
        if (is_array($options)) {
            $this->setOptions($options);
        }
        if (isset($this->idTrafico)) {
            $this->trafico->setId($this->idTrafico);
            $this->traficos->find($this->trafico);
            if (null === ($this->trafico->getId())) {
                throw new Exception("Record not found!");
            }
            $this->patente = (int) $this->trafico->getPatente();
            $this->aduana = (int) $this->trafico->getAduana();
            $this->pedimento = $this->trafico->getPedimento();
            $this->referencia = $this->trafico->getReferencia();
            $this->idAduana = $this->trafico->getIdAduana();
            $this->idCliente = $this->trafico->getIdCliente();
            $this->idBodega = $this->trafico->getIdBodega();
            $this->idRepositorio = $this->trafico->getIdRepositorio();
            $this->rfcCliente = $this->trafico->getRfcCliente();
            $this->clavePedimento = $this->trafico->getCvePedimento();
            $this->ie = $this->trafico->getIe();
            if (APPLICATION_ENV == "production") {
                $this->directorio = $this->appconfig->getParam("expdest");
            } else {
                $this->directorio = "D:\\xampp\\tmp\\expedientes";
            }
            $this->bitacora = new OAQ_Referencias(array("patente" => $this->trafico->getPatente(), "aduana" => $this->trafico->getAduana(), "pedimento" => $this->trafico->getPedimento(), "referencia" => $this->trafico->getReferencia(), "usuario" => $this->usuario));
        }
    }

    public function __set($name, $value)
    {
        $method = "set" . $name;
        if (("mapper" == $name) || !method_exists($this, $method)) {
            throw new Exception("Invalid property " . __METHOD__ . " " . $name);
        }
        $this->$method($value);
    }

    public function __get($name)
    {
        $method = "get" . $name;
        if (("mapper" == $name) || !method_exists($this, $method)) {
            throw new Exception("Invalid property " . __METHOD__);
        }
        return $this->$method();
    }

    public function setOptions(array $options)
    {
        $methods = get_class_methods($this);
        foreach ($options as $key => $value) {
            $method = "set" . ucfirst($key);
            if (in_array($method, $methods)) {
                $this->$method($value);
            }
        }
        return $this;
    }

    protected function _agregarBitacora($idTrafico, $idAduana, $patente, $aduana, $pedimento, $referencia, $tipoOperacion, $cvePedimento, $usuario)
    {
        $mppr = new Bitacora_Model_BitacoraPedimentos();
        $arr = array(
            "idTrafico" => $idTrafico,
            "idAduana" => $idAduana,
            "patente" => $patente,
            "aduana" => $aduana,
            "pedimento" => $pedimento,
            "referencia" => $referencia,
            "tipoOperacion" => $tipoOperacion,
            "clavePedimento" => $cvePedimento,
            "creado" => date("Y-m-d H:i:s"),
            "creadoPor" => $usuario,
        );
        $mppr->agregar($arr);
    }

    protected function _obtenerEstatus($value)
    {
        switch ($value) {
            case 1:
                $msg = "y esta en proceso de captura.";
                break;
            case 2:
                $msg = "y tiene estatus de pagado.";
                break;
            case 3:
                $msg = "y ha sido liberada.";
                break;
            case 4:
                $msg = " pero ha sido marcada como borrada.";
                break;
            default:
                break;
        }
        return $msg;
    }

    public function recuperarTrafico($idTrafico)
    {
        if (($arr = $this->traficos->obtenerPorId($idTrafico))) {
            $this->traficos->actualizarDatosTrafico($idTrafico, array(
                "idAduana" => $this->idAduana,
                "idCliente" => $this->idCliente,
                "estatus" => 1,
                "pedimento" => $this->pedimento,
                "referencia" => $this->referencia,
                "idUsuario" => $this->idUsuario,
            ));
            return true;
        }
        return;
    }

    public function cancelarTrafico($idTrafico)
    {
        if (($arr = $this->traficos->obtenerPorId($idTrafico))) {
            $this->traficos->actualizarDatosTrafico($idTrafico, array(
                "estatus" => 4
            ));
            return true;
        }
        return;
    }

    public function comprobarTrafico()
    {
        $row = $this->traficos->busquedaReferencia($this->idAduana, $this->referencia);
        if (!empty($row)) {
            if ($row["pedimento"] != $this->pedimento) {
                return array(
                    "error" => true,
                    "message" => "Un tráfico ya existe en el sistema con la misma referencia pero distinto pedimento.",
                );
            }
            if ($row["idCliente"] != $this->idCliente) {
                return array(
                    "error" => true,
                    "message" => "Un tráfico ya existe en el sistema con la misma referencia pero distinto cliente.",
                );
            }
            if ($row["ie"] != $this->ie) {
                return array(
                    "error" => true,
                    "message" => "Un tráfico ya existe en el sistema con la misma referencia pero distinto tipo de operación (IMPO/EXPO).",
                );
            }
            if ($row["estatus"] == 4) {
                return array(
                    "error" => true,
                    "idTrafico" => $row["id"],
                    "message" => "Un tráfico ya existe en el sistema con la misma referencia pero distinto pedimento.",
                );
            }
            throw new Exception("La referencia existe " . $this->_obtenerEstatus($row['estatus']));
        }
        return;
    }

    protected function _obtenerRegimen($tipoOperacion, $cvePedimento)
    {
        $regimen = null;
        if (null !== ($reg = $this->claves->buscarRegimen($cvePedimento))) {
            if ($tipoOperacion == "TOCE.IMP") {
                $regimen = $reg["regimenImportacion"];
            } else {
                $regimen = $reg["regimenExportacion"];
            }
        }
        return $regimen;
    }

    public function nuevoTrafico($array)
    {
        $arr = $this->aduanas->obtenerAduana($this->idAduana);
        if (isset($arr) && !empty($arr)) {
            $regimen = $this->_obtenerRegimen($array["ie"], $array["cvePedimento"]);
            $cliente = $this->clientes->datosCliente($array["idCliente"]);

            $array["patente"] = $arr["patente"];
            $array["aduana"] = $arr["aduana"];
            $array["regimen"] = $regimen;
            $array["rfcCliente"] = $cliente["rfc"];
            $array["estatus"] = 1;

            if (($idTrafico = $this->traficos->nuevoTrafico($array))) {

                $referencias = new OAQ_Referencias();
                $referencias->crearRepositorio($array["patente"], $array["aduana"], $array["referencia"], "Traffic", $cliente["rfc"], $array["pedimento"], $idTrafico);

                $this->idTrafico = $idTrafico;
                self::__construct();
                $this->actualizarFecha(10, $array["fechaEta"]);
                $this->_agregarBitacora($idTrafico, $array["idAduana"], $array["patente"], $array["aduana"], $array["pedimento"], $array["referencia"], $array["ie"], $array["cvePedimento"], $this->usuario);
                return array("success" => true, "id" => $idTrafico);
            }
        } else {
            throw new Exception("La aduana seleccionada no existe.");
        }
    }

    public function nuevaEntradaBodega($array)
    {

        if (isset($array["idCliente"])) {

            $cliente = $this->clientes->datosCliente($array["idCliente"]);

            $array["rfcCliente"] = $cliente["rfc"];
            $array["estatus"] = 1;

            if (($idTrafico = $this->traficos->nuevoTrafico($array))) {

                $referencias = new OAQ_Referencias();
                $referencias->crearRepositorio(null, null, $array["referencia"], "Traffic", $cliente["rfc"], null, $idTrafico);

                $this->idTrafico = $idTrafico;
                self::__construct();
                $this->actualizarFecha(10, $array["fechaEta"]);

                return array("success" => true, "id" => $idTrafico);
            }
        }
    }

    public function agregarTrafico($array)
    {
        $aduana = $this->aduanas->obtenerAduana($array["idAduana"]);
        if (isset($aduana) && !empty($aduana)) {
            $regimen = null;
            if (null !== ($reg = $this->claves->buscarRegimen($array["cvePedimento"]))) {
                if ($array["ie"] == "TOCE.IMP") {
                    $regimen = $reg["regimenImportacion"];
                } else {
                    $regimen = $reg["regimenExportacion"];
                }
            }
            $cliente = $this->clientes->datosCliente($array["idCliente"]);
            $array["patente"] = $aduana["patente"];
            $array["aduana"] = $aduana["aduana"];
            $array["regimen"] = $regimen;
            $array["rfcCliente"] = $cliente["rfc"];
            $array["estatus"] = 1;
            $usuario = $array["usuario"];
            unset($array["usuario"]);
            if (!($traffic = $this->traficos->buscarTrafico($array["patente"], $array["aduana"], $array["pedimento"], $array["referencia"], null, null, $cliente["rfc"]))) {
                $referencias = new OAQ_Referencias();
                $referencias->crearRepositorio($array["patente"], $array["aduana"], $array["referencia"], "Traffic", $cliente["rfc"], $array["pedimento"]);
                $id = $this->traficos->nuevoTrafico($array);
                $this->idTrafico = $id;
                self::__construct();
                $this->actualizarFecha(10, $array["fechaEta"]);
                $this->_agregarBitacora($id, $array["idAduana"], $array["patente"], $array["aduana"], $array["pedimento"], $array["referencia"], $array["ie"], $array["cvePedimento"], $usuario);
                return array("success" => true, "id" => $id);
            } else {
                if ($traffic["estatus"] == 4) {
                    $id = $this->traficos->nuevoTrafico($array);
                    $this->idTrafico = $id;
                    self::__construct();
                    $this->actualizarFecha(10, $array["fechaEta"]);
                    $this->_agregarBitacora($id, $array["idAduana"], $array["patente"], $array["aduana"], $array["pedimento"], $array["referencia"], $array["ie"], $array["cvePedimento"], $usuario);
                    return array("success" => true, "id" => $id);
                }
                $link = "<br><br>Consulta aquí: <a href=\"/trafico/index/editar-trafico?id={$traffic["id"]}\">{$array["aduana"]}-{$array["patente"]}-{$array["pedimento"]}</a>";
                return array("success" => false, "message" => "La referencia ya existe en el sistema y no puede duplicarse.", "link" => $link);
            }
        } else {
            return array("success" => false, "message" => "No se pudo encontrar aduana.");
        }
    }

    public function crearTrafico($idAduana, $idCliente, $pedimento, $referencia, $tipoOperacion, $cvePedimento, $pedimentoRectificar, $fechaEta, $tipoCambio, $consolidado, $rectificacion, $planta = null)
    {
        $aduana = $this->aduanas->obtenerAduana($idAduana);
        $cliente = $this->clientes->datosCliente($idCliente);
        if (isset($aduana) && !empty($aduana)) {
            $regimen = null;
            if (null !== ($reg = $this->claves->buscarRegimen($cvePedimento))) {
                if ($tipoOperacion == "TOCE.IMP") {
                    $regimen = $reg["regimenImportacion"];
                } else {
                    $regimen = $reg["regimenExportacion"];
                }
            }
            $arr = array(
                "idAduana" => $idAduana,
                "idUsuario" => $this->idUsuario,
                "IdCliente" => $idCliente,
                "idPlanta" => isset($planta) ? $planta : null,
                "patente" => $aduana["patente"],
                "aduana" => $aduana["aduana"],
                "pedimento" => $pedimento,
                "referencia" => $referencia,
                "pedimentoRectificar" => isset($pedimentoRectificar) ? $pedimentoRectificar : null,
                "ie" => $tipoOperacion,
                "cvePedimento" => isset($cvePedimento) ? $cvePedimento : "A1",
                "regimen" => $regimen,
                "tipoCambio" => $tipoCambio,
                "consolidado" => $consolidado,
                "rectificacion" => $rectificacion,
                "rfcCliente" => $cliente["rfc"],
                "estatus" => 1,
                "fechaEta" => date("Y-m-d H:i:s", strtotime($fechaEta)),
                "creado" => date("Y-m-d H:i:s"),
            );
            if (!($traffic = $this->traficos->buscarTrafico($aduana["patente"], $aduana["aduana"], $pedimento, $referencia))) {
                $referencias = new OAQ_Referencias();
                $referencias->crearRepositorio($aduana["patente"], $aduana["aduana"], $referencia, "Traffic", $cliente["rfc"], $pedimento);
                $id = $this->traficos->nuevoTrafico($arr);
                $this->idTrafico = $id;
                self::__construct();
                $this->actualizarFecha(10, $fechaEta);
                return array("success" => true, "id" => $id);
            } else {
                if ($traffic["estatus"] == 4) {
                    $id = $this->traficos->nuevoTrafico($arr);
                    $this->idTrafico = $id;
                    self::__construct();
                    $this->actualizarFecha(10, $fechaEta);
                    return array("success" => true, "id" => $id);
                }
                $link = "<br><br>Consulta aquí: <a href=\"/trafico/index/editar-trafico?id={$traffic["id"]}\">{$aduana["aduana"]}-{$aduana["patente"]}-{$pedimento}</a>";
                return array("success" => false, "message" => "La referencia ya existe en el sistema y no puede duplicarse.", "link" => $link);
            }
        } else {
            return array("success" => false, "message" => "No se pudo encontrar aduana.");
        }
    }

    public function obtenerFiltrosFechas()
    {
        $fechas = array(
            1 => array("label" => "fechaEntrada", "regx" => "/^\d{4}-\d{2}-\d{2} (\d{2}):(\d{2}):(\d{2})$/"),
            2 => array("label" => "fechaPago", "regx" => "/^\d{4}-\d{2}-\d{2} (\d{2}):(\d{2}):(\d{2})$/"),
            5 => array("label" => "fechaPresentacion", "regx" => "/^\d{4}-\d{2}-\d{2} (\d{2}):(\d{2}):(\d{2})$/"),
            8 => array("label" => "fechaLiberacion", "regx" => "/^\d{4}-\d{2}-\d{2} (\d{2}):(\d{2}):(\d{2})$/"),
            9 => array("label" => "fechaNotificacion", "regx" => "/^\d{4}-\d{2}-\d{2} (\d{2}):(\d{2}):(\d{2})$/"),
            10 => array("label" => "fechaEta", "regx" => "/^\d{4}-\d{2}-\d{2} (\d{2}):(\d{2}):(\d{2})$/"),
            12 => array("label" => "fechaEtd", "regx" => "/^\d{4}-\d{2}-\d{2} (\d{2}):(\d{2}):(\d{2})$/"),
            20 => array("label" => "fechaRevalidacion", "regx" => "/^\d{4}-\d{2}-\d{2} (\d{2}):(\d{2}):(\d{2})$/"),
            21 => array("label" => "fechaPrevio", "regx" => "/^\d{4}-\d{2}-\d{2} (\d{2}):(\d{2}):(\d{2})$/"),
            22 => array("label" => "fechaDeposito", "regx" => "/^\d{4}-\d{2}-\d{2} (\d{2}):(\d{2}):(\d{2})$/"),
            25 => array("label" => "fechaCitaDespacho", "regx" => "/^\d{4}-\d{2}-\d{2} (\d{2}):(\d{2}):(\d{2})$/"),
            26 => array("label" => "fechaEnvioProforma", "regx" => "/^\d{4}-\d{2}-\d{2} (\d{2}):(\d{2}):(\d{2})$/"),
            27 => array("label" => "fechaEnvioDocumentos", "regx" => "/^\d{4}-\d{2}-\d{2} (\d{2}):(\d{2}):(\d{2})$/"),
            28 => array("label" => "fechaEtaAlmacen", "regx" => "/^\d{4}-\d{2}-\d{2} (\d{2}):(\d{2}):(\d{2})$/"),
            29 => array("label" => "fechaVistoBueno", "regx" => "/^\d{4}-\d{2}-\d{2} (\d{2}):(\d{2}):(\d{2})$/"),
            30 => array("label" => "fechaFacturacion", "regx" => "/^\d{4}-\d{2}-\d{2} (\d{2}):(\d{2}):(\d{2})$/"),
            31 => array("label" => "fechaComprobacion", "regx" => "/^\d{4}-\d{2}-\d{2} (\d{2}):(\d{2}):(\d{2})$/"),
            50 => array("label" => "fechaProformaTercero", "regx" => "/^\d{4}-\d{2}-\d{2} (\d{2}):(\d{2}):(\d{2})$/"),
            51 => array("label" => "fechaSolicitudTransfer", "regx" => "/^\d{4}-\d{2}-\d{2} (\d{2}):(\d{2}):(\d{2})$/"),
            52 => array("label" => "fechaArriboTransfer", "regx" => "/^\d{4}-\d{2}-\d{2} (\d{2}):(\d{2}):(\d{2})$/"),
            53 => array("label" => "fechaVistoBuenoTercero", "regx" => "/^\d{4}-\d{2}-\d{2} (\d{2}):(\d{2}):(\d{2})$/"),
            54 => array("label" => "fechaInstruccionEspecial", "regx" => "/^\d{4}-\d{2}-\d{2} (\d{2}):(\d{2}):(\d{2})$/"),
        );
        return $fechas;
    }

    public function actualizarGuias($guias)
    {
        if (is_array($guias) && !empty($guias)) {
            $g = "";
            foreach ($guias as $item) {
                $g .= $item["guia"] . ", ";
            }
            $this->traficos->actualizarGuia($this->idTrafico, preg_replace("/,\s$/", "", $g));
        }
    }

    public function agregarGuia($idTrafico, $idUsuario, $guias)
    {
        $mppr = new Trafico_Model_TraficoGuiasMapper();
        $arr_guias = explode(",", preg_replace('/\s+/', '', $guias));
        if (is_array($arr_guias)) {
            foreach ($arr_guias as $item) {
                if (!($id = $mppr->verificarGuia($idTrafico, "H", $item))) {
                    $mppr->agregarGuia($idTrafico, "H", $item, $idUsuario);
                }
            }
        }
    }

    public function actualizarFecha($tipo, $fecha, $usuario = null)
    {
        if (isset($this->idTrafico) && isset($this->idUsuario)) {
            $dates = new Trafico_Model_TraficoFechasMapper();
            if (($res = $dates->buscar($this->idTrafico, $tipo)) === null) {
                $new = $dates->agregar(array("idTrafico" => $this->idTrafico, "fecha" => date("Y-m-d H:i:s", strtotime($fecha)), "tipo" => $tipo, "creado" => date("Y-m-d H:i:s"), "creadoPor" => $this->idUsuario));
            } else {
                if ((int) strtotime($res["fecha"]) !== (int) strtotime($fecha)) {
                    $updated = $dates->actualizar($res["id"], array("fecha" => date("Y-m-d H:i:s", strtotime($fecha)), "actualizado" => date("Y-m-d H:i:s"), "actualizadoPor" => $this->idUsuario));
                }
            }
            if ($tipo == 1) { // entrada
                $this->traficos->actualizarTipoFecha($this->idTrafico, "fechaEntrada", $fecha);
            } else if ($tipo == 2) { // pago
                $this->trafico->setPagado(1);
                $this->trafico->setEstatus(2);
                $this->trafico->setIdUsuarioModif($this->usuario);
                $this->traficos->save($this->trafico);
                $this->traficos->actualizarFechaPago($this->idTrafico, $fecha);
            } else if ($tipo == 5) { // presentacion
                $this->traficos->actualizarTipoFecha($this->idTrafico, "fechaPresentacion", $fecha);
            } else if ($tipo == 8) { // liberacion
                $this->traficos->actualizarEstatus($this->idTrafico, 3);
                $this->traficos->actualizarFechaLiberacion($this->idTrafico, $fecha);
            } else if ($tipo == 9) { // notificacion
                $this->traficos->actualizarTipoFecha($this->idTrafico, "fechaNotificacion", $fecha);
            } else if ($tipo == 10) { // arribo
                $this->traficos->actualizarTipoFecha($this->idTrafico, "fechaEta", $fecha);
            } else if ($tipo == 12) { // 
                $this->traficos->actualizarTipoFecha($this->idTrafico, "fechaEtd", $fecha);
            } else if ($tipo == 20) { // revalidado
                $this->traficos->actualizarTipoFecha($this->idTrafico, "fechaRevalidacion", $fecha);
            } else if ($tipo == 21) { // previo
                $this->traficos->actualizarTipoFecha($this->idTrafico, "fechaPrevio", $fecha);
            } else if ($tipo == 22) { // deposito
                $this->traficos->actualizarTipoFecha($this->idTrafico, "fechaDeposito", $fecha);
            } else if ($tipo == 25) { // cita
                $this->traficos->actualizarTipoFecha($this->idTrafico, "fechaCitaDespacho", $fecha);
            } else if ($tipo == 26) { // envio de proforma
                $this->traficos->actualizarTipoFecha($this->idTrafico, "fechaEnvioProforma", $fecha);
            } else if ($tipo == 27) { // envio de documentos
                $this->traficos->actualizarTipoFecha($this->idTrafico, "fechaEnvioDocumentos", $fecha);
            } else if ($tipo == 28) { // eta de almacen
                $this->traficos->actualizarTipoFecha($this->idTrafico, "fechaEtaAlmacen", $fecha);
            } else if ($tipo == 29) { // visto bueno
                $this->traficos->actualizarTipoFecha($this->idTrafico, "fechaVistoBueno", $fecha);
            } else if ($tipo == 50) { // proforma de tercero TDQ
                $this->traficos->actualizarTipoFecha($this->idTrafico, "fechaProformaTercero", $fecha);
            } else if ($tipo == 51) { // 
                $this->traficos->actualizarTipoFecha($this->idTrafico, "fechaSolicitudTransfer", $fecha);
            } else if ($tipo == 52) { // 
                $this->traficos->actualizarTipoFecha($this->idTrafico, "fechaArriboTransfer", $fecha);
            } else if ($tipo == 53) { // 
                $this->traficos->actualizarTipoFecha($this->idTrafico, "fechaVistoBuenoTercero", $fecha);
            } else if ($tipo == 54) { // 
                $this->traficos->actualizarTipoFecha($this->idTrafico, "fechaInstruccionEspecial", $fecha);
            } else if ($tipo == 55) { // 
                $this->traficos->actualizarTipoFecha($this->idTrafico, "fechaEir", $fecha);
            } else if ($tipo == 56) { // 
                $this->traficos->actualizarTipoFecha($this->idTrafico, "fechaRevision", $fecha);
            } else if ($tipo == 57) { // 
                $this->traficos->actualizarTipoFecha($this->idTrafico, "fechaDescarga", $fecha);
            } else if ($tipo == 58) { // 
                $this->traficos->actualizarTipoFecha($this->idTrafico, "fechaCarga", $fecha);
            } else if ($tipo == 59) { // 
                $this->traficos->actualizarTipoFecha($this->idTrafico, "fechaSalida", $fecha);
            } else if ($tipo == 30) { // facturacion
                $this->traficos->actualizarTipoFecha($this->idTrafico, "fechaFacturacion", $fecha);
            }
            if (isset($updated) && $updated == true) {
                $this->_bitacora($tipo, $fecha);
            }
            if (isset($new)) {
                $this->_bitacora($tipo, $fecha);
            }
            return true;
        } else {
            throw new Exception("IdTrafico or idUsuario is not set!");
        }
    }

    protected function _bitacora($tipo, $fecha)
    {
        try {
            $tipoFecha = "";
            switch ($tipo) {
                case 1:
                    $tipoFecha = "FECHA DE ENTRADA {$fecha}";
                    break;
                case 2:
                    $tipoFecha = "FECHA DE PAGO {$fecha}";
                    break;
                case 5:
                    $tipoFecha = "FECHA DE PRESENTACIÓN {$fecha}";
                    break;
                case 8:
                    $tipoFecha = "SE LIBERÓ EMBARQUE DE ADUANA {$fecha}";
                    break;
                case 9:
                    $tipoFecha = "NOTIFICACIÓN DE OPERACIÓN POR PARTE DEL CLIENTE {$fecha}";
                    break;
                case 10:
                    $tipoFecha = "FECHA DE ARRIBO {$fecha}";
                    break;
                case 11:
                    $tipoFecha = "RECEPCIÓN DE DOCUMENTOS COMPLETOS {$fecha}";
                    break;
                case 20:
                    $tipoFecha = "SE REVALIDADO BL O GUÍA {$fecha}";
                    break;
                case 21:
                    $tipoFecha = "SE PROGRAMO PREVIO {$fecha}";
                    break;
                case 22:
                    $tipoFecha = "FECHA DE DEPÓSITO {$fecha}";
                    break;
                case 28:
                    $tipoFecha = "FECHA ETA ALMACEN {$fecha}";
                    break;
                case 25:
                    $tipoFecha = "FECHA DE CITA DE DESPACHO {$fecha}";
                    break;
                case 30:
                    $tipoFecha = "FECHA DE FACTURACION {$fecha}";
                    break;
                default:
                    break;
            }
            if ($tipoFecha !== "") {
                $this->bitacora->agregarBitacora($tipoFecha);
            }
        } catch (Zend_Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public function actualizarFechaPago($fecha)
    {
        $this->traficos->actualizarFechaPago($this->idTrafico, $fecha);
    }

    public function actualizarSemaforo($estatus, $observacion = null)
    {
        $this->traficos->actualizarSemaforo($this->idTrafico, $estatus, $observacion);
        $mapper = new Vucem_Model_VucemPedimentosEstado();
        if (!($id = $mapper->verificarDesdeTrafico($this->idTrafico))) {
            if ((int) $estatus == 1) {
                $mapper->agregarDesdeTrafico($this->idTrafico, null, date("Y-m-d H:i:s"), 3, "PRIMERA SELECCIÓN AUTOMATIZADA", 320, "VERDE EN PRIMERA SELECCIÓN");
            } else if ((int) $estatus == 2) {
                $mapper->agregarDesdeTrafico($this->idTrafico, null, date("Y-m-d H:i:s"), 3, "PRIMERA SELECCIÓN AUTOMATIZADA", 310, "ROJO EN PRIMERA SELECCIÓN", $observacion);
            }
        } else {
            if ((int) $estatus == 1) {
                $mapper->actualizarDesdeTrafico($id, null, date("Y-m-d H:i:s"), 3, "PRIMERA SELECCIÓN AUTOMATIZADA", 320, "VERDE EN PRIMERA SELECCIÓN");
            } else if ((int) $estatus == 2) {
                $mapper->actualizarDesdeTrafico($id, null, date("Y-m-d H:i:s"), 3, "PRIMERA SELECCIÓN AUTOMATIZADA", 310, "ROJO EN PRIMERA SELECCIÓN", $observacion);
            }
        }
    }

    public function actualizarFechaFacturacion($fecha)
    {
        $this->traficos->actualizarFechaFacturacion($this->idTrafico, $fecha);
    }

    public function actualizarFechaLiberacion($fecha)
    {
        $this->traficos->actualizarFechaLiberacion($this->idTrafico, $fecha);
    }

    public function asignarmeOperacion()
    {
        if ($this->trafico->getEstatus() !== 3) {
            $this->traficos->asignarAUsuario($this->idTrafico, $this->idUsuario);
            $this->bitacora->agregarBitacora("EL USUARIO " . strtoupper($this->usuario) . " RECLAMO LA OPERACIÓN");
            return true;
        }
        return;
    }

    public function subirArchivoTemporal($idMensaje = null, $idComentario = null)
    {
        $upload = new Zend_File_Transfer_Adapter_Http();
        $upload->addValidator("Count", false, array("min" => 1, "max" => 15))
            ->addValidator("Size", false, array("min" => "1kB", "max" => "6MB"))
            ->addValidator("Extension", false, array("extension" => "jpg,pdf,jpeg,png", "case" => false));
        if (APPLICATION_ENV === "production") {
            $dir = "/home/samba-share/expedientes/temporal";
        } else {
            $dir = "C:\\wamp64\\tmp";
        }
        $upload->setDestination($dir);
        $files = $upload->getFileInfo();
        foreach ($files as $fieldname => $fileinfo) {
            if (($upload->isUploaded($fieldname)) && ($upload->isValid($fieldname))) {
                $ext = strtolower(pathinfo($fileinfo["name"], PATHINFO_EXTENSION));
                $filename = sha1(time() . $fileinfo["name"]) . "." . $ext;
                $upload->addFilter("Rename", $filename, $fieldname);
                $filesa = new Archivo_Model_RepositorioTemporalMapper();
                $table = new Archivo_Model_RepositorioTemporal(array(
                    "idTrafico" => $this->idTrafico,
                    "idMensaje" => isset($idMensaje) ? $idMensaje : null,
                    "idComentario" => isset($idComentario) ? $idComentario : null,
                    "nombreArchivo" => $fileinfo["name"],
                    "archivo" => $filename,
                    "ubicacion" => $dir . DIRECTORY_SEPARATOR . $filename,
                    "creado" => date("Y-m-d H:i:s"),
                    "usuario" => $this->usuario,
                ));
                $filesa->save($table);
                $upload->receive($fieldname);
            }
        }
    }

    public function buscarGuia($numGuia)
    {
        $sis = new Application_Model_ServiciosRestAduana();
        $row = $sis->obtenerSistema(2);
        if (!empty($row) && isset($row["idServicio"])) {
            $mppr = new Application_Model_ServiciosRest();
            if (($arr = $mppr->obtener($row["idServicio"]))) {
                $client = new Zend_Rest_Client($arr['url'], array('timeout' => $arr['timeout']));
                $this->sistema = $arr['sistema'];
            }
            if (isset($client)) {
                $client->setConfig(array('timeout' => 120));
                $response = $client->restPost("/{$this->sistema}/buscar-guia", array(
                    'guia' => $numGuia,
                ));
                if ($response->getBody()) {
                    $row = json_decode($response->getBody(), true);
                    if (!empty($row["response"][0])) {
                        return $row["response"][0];
                    }
                } else {
                    return;
                }
            }
        }
    }

    public function buscarTrafico()
    {
        $mapper = new Trafico_Model_TraficosMapper();
        if (($id = $mapper->buscarReferencia($this->patente, $this->aduana, $this->referencia))) {
            return $id;
        }
        return;
    }

    public function datosDeSistema($patente, $aduana, $referencia)
    {

        $adu = new Trafico_Model_TraficoAduanasMapper();
        if (($idAduana = $adu->idAduana($patente, $aduana))) {

            $sis = new Application_Model_ServiciosRestAduana();
            $row = $sis->obtenerSistema($idAduana);
            if (!empty($row) && isset($row["idServicio"])) {
                $mppr = new Application_Model_ServiciosRest();
                if (($arr = $mppr->obtener($row["idServicio"]))) {
                    $client = new Zend_Rest_Client($arr['url'], array('timeout' => $arr['timeout']));
                    $this->sistema = $arr['sistema'];
                }
            }
            if (isset($client)) {
                $arr = $this->_buscarReferencia($client, $patente, $aduana, $referencia);
                if (!empty($arr)) {
                    return $arr;
                }
                return;
            }
            return;
        }
        return;
    }

    protected function _buscarServicio($sistema)
    {
        $sis = new Application_Model_ServiciosRestAduana();
        $row = $sis->obtenerServicio($this->idAduana, $sistema);
        if (!empty($row) && isset($row["idServicio"])) {
            $mppr = new Application_Model_ServiciosRest();
            if (($arr = $mppr->obtener($row["idServicio"]))) {
                $client = new Zend_Rest_Client($arr['url']);
                $httpClient = $client->getHttpClient();
                $httpClient->setConfig(array('timeout' => 60));
                $client->setHttpClient($httpClient);
                $this->sistema = $arr['sistema'];
            }
        }
        if (isset($client)) {
            return $client;
        }
        return;
    }

    protected function _buscarSistema()
    {
        $sis = new Application_Model_ServiciosRestAduana();
        $row = $sis->obtenerSistema($this->idAduana);
        if (!empty($row) && isset($row["idServicio"])) {
            $mppr = new Application_Model_ServiciosRest();
            if (($arr = $mppr->obtener($row["idServicio"]))) {
                $client = new Zend_Rest_Client($arr['url']);
                $httpClient = $client->getHttpClient();
                $httpClient->setConfig(array('timeout' => 60));
                $client->setHttpClient($httpClient);
                $this->sistema = $arr['sistema'];
            }
        }
        if (isset($client)) {
            return $client;
        }
        return;
    }

    protected function _buscarReferencia(Zend_Rest_Client $client, $patente, $aduana, $referencia)
    {
        try {
            $response = $client->restPost("/{$this->sistema}/buscar-referencia", array(
                'patente' => $patente,
                'aduana' => $aduana,
                'referencia' => $referencia,
            ));
            if ($response->getBody()) {
                $row = json_decode($response->getBody(), true);
                if (!empty($row["response"][0])) {
                    return $row["response"][0];
                }
            } else {
                return false;
            }
        } catch (Exception $ex) {
            return false;
        }
    }

    protected function _encabezadoPedimento(Zend_Rest_Client $client)
    {
        if (($this->patente == 3107 && $this->aduana = 240) || ($this->patente == 3574 && $this->aduana = 800)) {
            $response = $client->restPost("/{$this->sistema}/referencia", array(
                'patente' => $this->patente,
                'aduana' => $this->aduana,
                'pedimento' => $this->pedimento,
                'referencia' => $this->referencia,
            ));
        } else {
            $response = $client->restPost("/{$this->sistema}/buscar-pedimento", array(
                'patente' => $this->patente,
                'aduana' => $this->aduana,
                'pedimento' => $this->pedimento,
                'referencia' => $this->referencia,
            ));
        }
        if ($response->getBody()) {
            $row = json_decode($response->getBody(), true);
            if (!empty($row["response"][0])) {
                return $row["response"][0];
            }
        } else {
            return false;
        }
    }

    protected function _facturasPedimento(Zend_Rest_Client $client)
    {
        try {
            $response = $client->restPost("/{$this->sistema}/encabezado-facturas", array(
                'patente' => $this->patente,
                'aduana' => $this->aduana,
                'pedimento' => $this->pedimento,
                'referencia' => $this->referencia,
            ));
            if ($response->getBody()) {
                $row = json_decode($response->getBody(), true);
                if (!empty($row["response"])) {
                    return $row["response"];
                }
            } else {
                return false;
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    protected function _detalleFactura(Zend_Rest_Client $client, $numFatura)
    {
        $response = $client->restPost("/{$this->sistema}/detalle-factura", array(
            'patente' => $this->patente,
            'aduana' => $this->aduana,
            'pedimento' => $this->pedimento,
            'referencia' => $this->referencia,
            'numFactura' => $numFatura,
        ));
        if ($response->getBody()) {
            $row = json_decode($response->getBody(), true);
            if (!empty($row["response"])) {
                return $row["response"];
            }
        } else {
            return false;
        }
    }

    protected function _proveedorFactura(Zend_Rest_Client $client, $cvePro = null, $numFactura = null)
    {
        if ($this->sistema == 'casa') {
            $response = $client->restPost("/{$this->sistema}/proveedor-factura", array(
                'cvePro' => $cvePro,
            ));
        } else if ($this->sistema == 'slam') {
            $response = $client->restPost("/{$this->sistema}/proveedor-factura", array(
                'patente' => $this->patente,
                'aduana' => $this->aduana,
                'pedimento' => $this->pedimento,
                'referencia' => $this->referencia,
                'numFactura' => $numFactura,
            ));
        } else if ($this->sistema == 'aduanet') {
            $response = $client->restPost("/{$this->sistema}/proveedor-factura", array(
                'patente' => $this->patente,
                'aduana' => $this->aduana,
                'pedimento' => $this->pedimento,
                'referencia' => $this->referencia,
                'numFactura' => $numFactura,
            ));
        }
        if ($response) {
            if ($response->getBody()) {
                $row = json_decode($response->getBody(), true);
                if (!empty($row["response"])) {
                    return $row["response"];
                }
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    protected function _destinatarioFactura(Zend_Rest_Client $client, $cvePro)
    {
        $response = $client->restPost("/{$this->sistema}/destinatario-factura", array(
            'cvePro' => $cvePro,
        ));
        if ($response->getBody()) {
            $row = json_decode($response->getBody(), true);
            if (!empty($row["response"])) {
                return $row["response"];
            }
        } else {
            return false;
        }
    }

    protected function _productosFactura(Zend_Rest_Client $client, $numFactura)
    {
        if ($this->sistema == 'casa') {
            $response = $client->restPost("/{$this->sistema}/productos-factura", array(
                'patente' => $this->patente,
                'aduana' => $this->aduana,
                'pedimento' => $this->pedimento,
                'numFactura' => $numFactura,
            ));
        } else if ($this->sistema == 'slam') {
            $response = $client->restPost("/{$this->sistema}/productos-factura", array(
                'referencia' => $this->referencia,
                'numFactura' => $numFactura,
            ));
        } else if ($this->sistema == 'aduanet') {
            $response = $client->restPost("/{$this->sistema}/proveedor-factura", array(
                'patente' => $this->patente,
                'aduana' => $this->aduana,
                'pedimento' => $this->pedimento,
                'referencia' => $this->referencia,
                'numFactura' => $numFactura,
            ));
        }
        if ($response->getBody()) {
            $row = json_decode($response->getBody(), true);
            if (!empty($row["response"])) {
                return $row["response"];
            }
        } else {
            return false;
        }
    }

    protected function _actualizarEnTrafico($arr)
    {
        $tipoOperacion = null;
        if ((int) $arr["tipoOperacion"] === 1) {
            $tipoOperacion = "TOCE.IMP";
        } else if ((int) $arr["tipoOperacion"] === 2) {
            $tipoOperacion = "TOCE.EXP";
        }
        $update = array(
            "ie" => $tipoOperacion,
            "cvePedimento" => $arr["cvePedimento"],
            "tipoCambio" => $arr["tipoCambio"],
            "regimen" => $arr["regimen"],
            "consolidado" => ($arr["consolidado"] == 0) ? 0 : 1,
            "rectificacion" => (isset($arr["rectificacion"]) && $arr["rectificacion"] == "N") ? 0 : 1,
            "firmaValidacion" => (isset($arr["firmaValidacion"]) && $arr["firmaValidacion"] != "") ? $arr["firmaValidacion"] : null,
            "firmaBanco" => (isset($arr["firmaBanco"]) && $arr["firmaBanco"] != "") ? $arr["firmaBanco"] : null,
            "blGuia" => (isset($arr["blGuia"]) && $arr["blGuia"] != "") ? $arr["blGuia"] : null,
            "contenedorCaja" => (isset($arr["contenedorCaja"]) && $arr["contenedorCaja"] != "") ? $arr["contenedorCaja"] : null,
        );
        $this->traficos->actualizarDatosTrafico($this->idTrafico, $update);
    }

    protected function _actualizarEncabezadoPedimento($arr)
    {
        try {
            $mppr = new Trafico_Model_TraficoPedimento();
            if (!empty($arr)) {
                if (!($id = $mppr->verificar($this->idTrafico, $this->pedimento))) {
                    $mppr->agregar($this->idTrafico, $this->patente, $this->aduana, $this->pedimento, $this->referencia, $arr);
                } else {
                    $mppr->actualizar($id, $arr);
                }
                return;
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    protected function _actualizarFacturasTrafico($arr, $sistema = null)
    {
        $mppr = new Trafico_Model_TraficoFacturasMapper();
        if (!empty($arr)) {
            foreach ($arr as $item) {
                if (!($mppr->verificar($this->idTrafico, $item["numFactura"]))) {
                    if (isset($sistema)) {
                        $item['sistema'] = $sistema;
                    }
                    $mppr->agregarFactura($this->idTrafico, $item, $this->idUsuario);
                }
            }
        }
    }

    protected function _actualizarFechas()
    {
    }

    protected function _obtenerTrafico()
    {
        $arr = $this->traficos->obtenerPorId($this->idTrafico);
        $this->patente = $arr["patente"];
        $this->aduana = $arr["aduana"];
        $this->pedimento = $arr["pedimento"];
        $this->referencia = $arr["referencia"];
        $this->idCliente = $arr["idCliente"];
    }

    public function importarFacturaDesdeSistema($numFactura, $sistema = null)
    {
        $this->_obtenerTrafico();
        $client = null;
        if (!isset($sistema)) {
            $client = $this->_buscarSistema();
        } else {
            $client = $this->_buscarServicio($sistema);
        }
        if ($client) {
            $arr = $this->_encabezadoPedimento($client);
            if ($arr !== false) {
                $arr_inv = $this->_detalleFactura($client, $numFactura);
                if ($arr_inv !== false) {
                    if ($arr_inv[0]["tipoOperacion"] == 1) {
                        $arr_prov = $this->_proveedorFactura($client, $arr_inv[0]["cvePro"], $arr_inv[0]["numFactura"]);
                        if ($arr_prov !== false) {
                            $arr_inv[0]["proveedor"] = $arr_prov[0];
                        }
                    } else {
                        $arr_prov = $this->_destinatarioFactura($client, $arr_inv[0]["cvePro"], $arr_inv[0]["numFactura"]);
                        if ($arr_prov !== false) {
                            $arr_inv[0]["destinatario"] = $arr_prov[0];
                        }
                    }
                    $arr_prod = $this->_productosFactura($client, $numFactura);

                    if ($arr_prod !== false) {
                        $arr_inv[0]["productos"] = $arr_prod;
                    }
                    $this->_transferirDatosTrafico($arr_inv);
                    return $arr_inv[0];
                }
            }
        } else {
            throw new Exception("No hay sistema para la aduana seleccionada.");
        }
    }

    protected function _verificarDetalleFactura($arr, $id_prov)
    {
        $mppr = new Trafico_Model_FactDetalle();
        if (!empty($arr) && isset($id_prov)) {
            if (!($id = $mppr->verificar($this->idFactura, $arr[0]["numFactura"]))) {
                $row = $mppr->prepareDataFromRest($arr[0], $this->idFactura, $id_prov);
                $mppr->agregar($row);
            } else {
                $row = $mppr->prepareDataFromRest($arr[0]);
                $mppr->update($this->idFactura, $row);
            }
        }
    }

    protected function _verificarDestinatario($arr)
    {
        $mppr = new Trafico_Model_FactDest();
        if (!isset($arr["identificador"])) {
            throw new Exception("Factura no tiene identificador del destinatario (TaxId)");
        }
        if (!($id = $mppr->verificarDestinatario($this->idCliente, $arr["cvePro"], $arr["identificador"]))) {
            $row = array(
                "idCliente" => $this->idCliente,
                "clave" => $arr["cvePro"],
                "identificador" => $arr["identificador"],
                "nombre" => $arr["nombreProveedor"],
                "calle" => $arr["calle"],
                "numExt" => $arr["numExterior"],
                "numInt" => $arr["numInterior"],
                "municipio" => $arr["localidad"],
                "estado" => $arr["entidad"],
                "codigoPostal" => $arr["codigoPostal"],
                "pais" => $arr["pais"],
                "creado" => date("Y-m-d H:i:s"),
            );
            return $mppr->agregar($row);
        }
        return $id;
    }

    protected function _verificarProveedor($arr)
    {
        $mppr = new Trafico_Model_FactPro();
        if (!isset($arr["identificador"])) {
            throw new Exception("Factura no tiene identificador del proveedor (TaxId)");
        }
        if (!($id = $mppr->verificarProveedor($this->idCliente, $arr["cvePro"], $arr["identificador"]))) {
            $row = array(
                "idCliente" => $this->idCliente,
                "clave" => $arr["cvePro"],
                "identificador" => $arr["identificador"],
                "nombre" => $arr["nombreProveedor"],
                "calle" => $arr["calle"],
                "numExt" => $arr["numExterior"],
                "numInt" => $arr["numInterior"],
                "municipio" => $arr["localidad"],
                "estado" => $arr["entidad"],
                "codigoPostal" => $arr["codigoPostal"],
                "pais" => $arr["pais"],
                "creado" => date("Y-m-d H:i:s"),
            );
            return $mppr->agregar($row);
        }
        return $id;
    }

    protected function _productosTrafico($arr)
    {
        $mppr = new Trafico_Model_FactProd();
        $mdl = new Trafico_Model_ClientesPartes();
        if (isset($this->idFactura)) {
            $mppr->borrarIdFactura($this->idFactura);
        }
        foreach ($arr as $item) {
            if (isset($item["fraccion"]) && isset($item["numParte"])) {
                $row = $mppr->prepareDataFromRest($item, $this->idFactura);
                $mppr->agregar($row);
                if (!($mdl->buscar($this->idProv, $this->tipoOperacion, $row["fraccion"], $row["numParte"], $row["paisOrigen"], $row["paisVendedor"]))) {
                    $array = $mdl->prepareDataFromRest($this->idCliente, $this->tipoOperacion, $this->idProv, $row);
                    $mdl->agregar($array);
                }
            }
        }
    }

    protected function _transferirDatosTrafico($arr)
    {
        if (!empty($arr)) {
            if (isset($arr[0]["proveedor"]) && (int) $arr[0]["tipoOperacion"] == 1) {
                $id_prov = $this->_verificarProveedor($arr[0]["proveedor"]);
                $this->tipoOperacion = 1;
            }
            if (isset($arr[0]["destinatario"]) && (int) $arr[0]["tipoOperacion"] == 2) {
                $id_prov = $this->_verificarDestinatario($arr[0]["destinatario"]);
                $this->tipoOperacion = 2;
            }
            if (isset($id_prov)) {
                $this->idProv = $id_prov;
            }
            if (isset($arr[0]["productos"]) && !empty($arr[0]["productos"])) {
                $this->_productosTrafico($arr[0]["productos"]);
            }
            if (isset($id_prov)) {
                $this->_verificarDetalleFactura($arr, $id_prov);
            } else {
                throw new Exception("No se pudo encontrar el proveedor.");
            }
        }
    }

    public function actualizarDesdeServicio($servicio)
    {
        $this->_obtenerTrafico();
        if (($client = $this->_buscarServicio($servicio))) {
            $arr = $this->_encabezadoPedimento($client);
            if ($arr) {
                $this->_actualizarEncabezadoPedimento($arr);
                $this->_actualizarEnTrafico($arr);
                if ($arr !== false) {
                    $arr_inv = $this->_facturasPedimento($client);
                    if ($arr_inv !== false) {
                        $this->_actualizarFacturasTrafico($arr_inv, $servicio);
                    }
                }
            }
        } else {
            throw new Exception("No hay sistema para la aduana seleccionada.");
        }
    }

    public function actualizarDesdeSistema()
    {
        $this->_obtenerTrafico();
        if (($client = $this->_buscarSistema())) {
            $arr = $this->_encabezadoPedimento($client);
            if ($arr) {
                $this->_actualizarEncabezadoPedimento($arr);
                $this->_actualizarEnTrafico($arr);
                if ($arr !== false) {
                    $arr_inv = $this->_facturasPedimento($client);
                    if ($arr_inv !== false) {
                        $this->_actualizarFacturasTrafico($arr_inv);
                    }
                }
            }
        } else {
            throw new Exception("No hay sistema para la aduana seleccionada.");
        }
    }

    protected function _obtenerServicioRest($idServicio)
    {
        $mppr = new Application_Model_ServiciosRest();
        if (($arr = $mppr->obtener($idServicio))) {
            $client = new Zend_Rest_Client($arr['url']);
            $httpClient = $client->getHttpClient();
            $httpClient->setConfig(array('timeout' => 60));
            $client->setHttpClient($httpClient);
            $this->sistema = $arr['sistema'];
        }
        if (isset($client)) {
            return $client;
        }
        return;
    }

    protected function _detalleOperacion(Zend_Rest_Client $client)
    {
        $response = $client->restPost("/{$this->sistema}/detalle-operacion", array(
            'patente' => $this->patente,
            'aduana' => $this->aduana,
            'pedimento' => $this->pedimento,
            'referencia' => $this->referencia,
        ));
        if ($response->getBody()) {
            $row = json_decode($response->getBody(), true);
            if (!empty($row["response"]) && $row["response"]["error"] == false) {
                return $row["response"]["results"];
            }
        } else {
            return;
        }
        return;
    }

    public function obtenerDatosDesdeSistema()
    {
        $this->_obtenerTrafico();
        if (($client = $this->_buscarSistema())) {
            $arr = $this->_detalleOperacion($client);
            if ($arr) {
                return $arr;
            }
            // Aduanet forzado
            $serv = $this->_obtenerServicioRest(3);
            $arr = $this->_detalleOperacion($serv);
            if ($arr) {
                return $arr;
            }
            return;
        } else {
            throw new Exception("No hay sistema para la aduana seleccionada.");
        }
    }

    public function actualizar($arr)
    {
        if (($this->traficos->actualizarTrafico($this->idTrafico, $arr))) {
            return true;
        }
        return;
    }

    public function actualizarDesdeSitawin()
    {
        $arro = $this->traficos->obtenerPorId($this->idTrafico);
        $this->patente = $arro["patente"];
        $this->aduana = $arro["aduana"];
        $this->pedimento = $arro["pedimento"];
        $this->referencia = $arro["referencia"];
        $this->idCliente = $arro["idCliente"];
        if (($arr = $this->_sitawin())) {
            if (isset($arr) && !empty($arr)) {
                if (isset($arr["pago"]["fechaEntrada"]) && $arr["pago"]["fechaEntrada"] != "") {
                    if ((int) $arr["basico"]["tipoOperacion"] == 2) {
                        $this->actualizarFecha(5, $arr["pago"]["fechaEntrada"]);
                    } else {
                        $this->actualizarFecha(1, $arr["pago"]["fechaEntrada"]);
                    }
                }
                if (isset($arr["pago"]["fechaPago"]) && $arr["pago"]["fechaPago"] != "" && ($arr["pago"]["firmaValidacion"] != "" && $arr["pago"]["firmaBanco"] != "")) {
                    if ((int) $this->patente == 3589) {
                        $vucemPed = new Vucem_Model_VucemPedimentosMapper();
                        $vucemPed->agregarVacio($this->idTrafico, $this->patente, $this->aduana, $this->pedimento);
                    }
                    $arr["basico"]["firmaValidacion"] = $arr["pago"]["firmaValidacion"];
                    $arr["basico"]["firmaBanco"] = $arr["pago"]["firmaBanco"];
                    $this->actualizarFecha(2, $arr["pago"]["fechaPago"]);
                    $this->traficos->actualizarEstatus($this->idTrafico, 2);
                }
                if (!empty($arr["basico"]["candados"])) {
                    $cand = new Trafico_Model_Candados();
                    foreach ($arr["basico"]["candados"] as $item) {
                        if ($item["numero"] == "PENDIENTE") {
                            continue;
                        }
                        if (!($cand->verificar($this->idTrafico, $item["numero"]))) {
                            $cand->agregar($this->idTrafico, $item["numero"], $item["color"]);
                        }
                    }
                }
                if (!empty($arr["basico"]["facturas"])) {
                    $model = new Trafico_Model_TraficoFacturasMapper();
                    foreach ($arr["basico"]["facturas"] as $item) {
                        if (!($model->verificar($this->idTrafico, $item["numFactura"]))) {
                            $inv = $model->agregarFactura($this->idTrafico, $item, $this->idUsuario);
                            if (isset($item["cove"])) {
                                $vucem = new Trafico_Model_TraficoVucem();
                                $v = new Trafico_Model_Table_TraficoVucem(array("idTrafico" => $this->idTrafico, "idFactura" => $inv, "numFactura" => $item["numFactura"]));
                                $vucem->find($v);
                                if (null === ($v->getId())) {
                                    $v->setEdoc($item["cove"]);
                                    $v->setCreado(date("Y-m-d H:i:s"));
                                    $vucem->save($v);
                                }
                            }
                        }
                    }
                }
                if (!empty($arr["basico"]["transportes"])) {
                    $tran = new Trafico_Model_Trans();
                    if (!($tran->verificar($this->idTrafico, $arr["basico"]["transportes"]["placas"]))) {
                        $tran->agregar($this->idTrafico, $arr["basico"]["transportes"]["placas"], $arr["basico"]["transportes"]["transportista"], $arr["basico"]["transportes"]["pais"], $arr["basico"]["transportes"]["domicilio"]);
                    }
                    $arr["basico"]["contenedorCaja"] = $arr["basico"]["transportes"]["placas"];
                }
                if (!empty($arr["basico"]["guias"])) {
                    $tn = new Trafico_Model_TraficoGuiasMapper();
                    $arr["basico"]["blGuia"] = "";
                    foreach ($arr["basico"]["guias"] as $item) {
                        $arr["basico"]["blGuia"] .= $item["guia"] . ", ";
                        if (!($tn->verificarGuia($this->idTrafico, $item["tipoGuia"], preg_replace('/\s+/', '', $item["guia"])))) {
                            $tn->agregarGuia($this->idTrafico, $item["tipoGuia"], preg_replace('/\s+/', '', $item["guia"]), $this->idUsuario);
                        }
                    }
                    $arr["basico"]["blGuia"] = preg_replace("/,\s$/", "", $arr["basico"]["blGuia"]);
                }
                $update = $this->_encabezadoParaActualizar($arr["basico"]);
                if ($update["cvePedimento"] == "" || $update["cvePedimento"] == null) {
                    $update["cvePedimento"] = $arro["cvePedimento"];
                }
                if ($update["firmaValidacion"] == "" || $update["firmaValidacion"] == null) {
                    $update["firmaValidacion"] = $arro["firmaValidacion"];
                }
                if ($update["firmaBanco"] == "" || $update["firmaBanco"] == null) {
                    $update["firmaBanco"] = $arro["firmaBanco"];
                }
                if ($update["ie"] == "" || $update["ie"] == null) {
                    $update["ie"] = $arro["ie"];
                }
                if ($update["rectificacion"] == "" || $update["rectificacion"] == null) {
                    $update["rectificacion"] = $arro["rectificacion"];
                }
                if ($update["consolidado"] == "" || $update["consolidado"] == null) {
                    $update["consolidado"] = $arro["consolidado"];
                }
                if ($update["regimen"] == "" || $update["regimen"] == null) {
                    $update["regimen"] = $arro["regimen"];
                }
                if (isset($arr["proveedorFacturas"])) {
                    $update["cantidadFacturas"] = $arr["proveedorFacturas"]["cantidadFacturas"];
                    $update["cantidadPartes"] = $arr["proveedorFacturas"]["cantidadPartes"];
                    $update["facturas"] = $arr["proveedorFacturas"]["facturas"];
                    $update["proveedores"] = $arr["proveedorFacturas"]["proveedores"];
                }
                $this->traficos->actualizarDatosTrafico($this->idTrafico, $update);
            }
        } else {
            return;
        }
    }

    protected function _encabezadoParaActualizar($row)
    {
        $tipoOperacion = null;
        if ((int) $row["tipoOperacion"] === 1) {
            $tipoOperacion = "TOCE.IMP";
        } else if ((int) $row["tipoOperacion"] === 2) {
            $tipoOperacion = "TOCE.EXP";
        }
        return array(
            "ie" => $tipoOperacion,
            "cvePedimento" => $row["cvePedimento"],
            "tipoCambio" => $row["tipoCambio"],
            "regimen" => $row["regimen"],
            "consolidado" => ($row["consolidado"] == "N") ? 0 : 1,
            "rectificacion" => ($row["rectificacion"] == "N") ? 0 : 1,
            "firmaValidacion" => (isset($row["firmaValidacion"]) && $row["firmaValidacion"] != "") ? $row["firmaValidacion"] : null,
            "firmaBanco" => (isset($row["firmaBanco"]) && $row["firmaBanco"] != "") ? $row["firmaBanco"] : null,
            "blGuia" => (isset($row["blGuia"]) && $row["blGuia"] != "") ? $row["blGuia"] : null,
            "contenedorCaja" => (isset($row["contenedorCaja"]) && $row["contenedorCaja"] != "") ? $row["contenedorCaja"] : null,
        );
    }

    protected function _sitawin()
    {
        $misc = new OAQ_Misc();
        $db = $misc->sitawinTrafico($this->patente, $this->aduana);
        if (isset($db)) {
            $arr = array();
            $arr["basico"] = $db->infoPedimentoBasicaReferencia($this->referencia);
            $arr["pago"] = $db->informacionDePago($this->referencia);
            $arr["proveedorFacturas"] = $db->proveedoresFacturas($this->referencia);
            return $arr;
        }
        return;
    }

    public function obtenerBitacora()
    {
        $mppr = new Trafico_Model_BitacoraMapper();
        $arr = $mppr->obtener($this->patente, $this->aduana, $this->pedimento, $this->referencia);
        if (!empty($arr)) {
            return $arr;
        }
        return;
    }

    public function obtenerBitacoraBodega()
    {
        $mppr = new Trafico_Model_BitacoraMapper();
        $arr = $mppr->obtenerBitacoraBodega($this->referencia);
        if (!empty($arr)) {
            return $arr;
        }
        return;
    }

    public function crearZip($idUsuario, $role)
    {

        $exp = new OAQ_Expediente_Descarga();
        $misc = new OAQ_Misc();

        $mppr = new Archivo_Model_RepositorioMapper();

        $referencias = new OAQ_Referencias();
        $res = $referencias->restricciones($idUsuario, $role);

        if (!in_array($role, array("inhouse", "cliente", "proveedor"))) {
            $files = $mppr->getFilesByReferenceUsers($this->referencia, $this->patente, $this->aduana);
        } else if (in_array($role, array("proveedor"))) {
            $files = $mppr->obtener($this->referencia, $this->patente, $this->aduana, json_decode($res["documentos"]));
        } else if (in_array($role, array("inhouse"))) {
            $files = $mppr->obtener($this->referencia, $this->patente, $this->aduana, json_decode($res["documentos"]));
        } else if (in_array($role, array("cliente"))) {
            $files = $mppr->getFilesByReferenceCustomers($this->referencia, $this->patente, $this->aduana);
        }
        if (count($files)) {
            $zipName = $exp->zipFilename($this->patente, $this->aduana, $this->pedimento, $misc->limpiarNombreReferencia($this->referencia), $this->rfcCliente);
            if (APPLICATION_ENV === "production" || APPLICATION_ENV === "staging") {
                $zipDir = "/tmp/zips";
            } else {
                $zipDir = "D:\\xampp\\tmp\\zips";
            }
            if (!file_exists($zipDir)) {
                mkdir($zipDir, 0777, true);
            }
            $zipFilename = $zipDir . DIRECTORY_SEPARATOR . $zipName;
            if (file_exists($zipFilename)) {
                unlink($zipFilename);
            }
            $zip = new ZipArchive();
            if ($zip->open($zipFilename, ZIPARCHIVE::CREATE) !== TRUE) {
                return null;
            }
            foreach ($files as $file) {
                if (file_exists($file["ubicacion"])) {
                    $tmpfile = $zipDir . DIRECTORY_SEPARATOR . sha1($file["ubicacion"]);
                    copy($file["ubicacion"], $tmpfile);

                    if ($this->rfcCliente == 'GCO980828GY0') {
                        if (($zip->addFile($tmpfile, $exp->filename($this->patente, $this->aduana, $this->pedimento, basename($file["ubicacion"]), $file["tipo_archivo"], $this->rfcCliente, $file))) === true) {
                            $added[] = $tmpfile;
                        }
                    } else {
                        if (($zip->addFile($tmpfile, $exp->filename($this->patente, $this->aduana, $this->pedimento, basename($file["ubicacion"]), $file["tipo_archivo"]))) === true) {
                            $added[] = $tmpfile;
                        }
                    }
                    unset($tmpfile);
                }
            }

            $val = new OAQ_ArchivosValidacion();
            if (isset($this->pedimento)) {
                $arch_val = $val->archivosDePedimento($this->patente, $this->aduana, $this->pedimento);
                if (!empty($arch_val)) {
                    $mppr_val = new Automatizacion_Model_ArchivosValidacionMapper();
                    foreach ($arch_val as $a_val) {
                        if ($a_val['idArchivoValidacion']) {
                            $file_val = $mppr_val->fileContent($a_val['idArchivoValidacion']);
                            if ($file_val) {
                                $zip->addFromString($a_val['archivoNombre'], base64_decode($file_val["contenido"]));
                            }
                        }
                    }
                }
            }

            if (($zip->close()) === TRUE) {
                $closed = true;
            }
            if ($closed === true) {
                foreach ($added as $tmp) {
                    unlink($tmp);
                }
            }
            if (file_exists($zipFilename)) {
                return $zipFilename;
            }
            return;
        }
    }

    public function obtenerComentarios()
    {
        $mppr = new Trafico_Model_ComentariosMapper();
        $arr = $mppr->obtenerTodos($this->idTrafico);
        if (!empty($arr)) {
            return $arr;
        }
        return;
    }

    public function agregarComentario($mensaje)
    {
        $mppr = new Trafico_Model_ComentariosMapper();
        $stmt = $mppr->agregar($this->idTrafico, $this->idUsuario, $mensaje);
        if ($stmt) {
            return true;
        }
        return;
    }

    public function obtenerArchivosComentarios()
    {
        $mppr = new Archivo_Model_RepositorioTemporalMapper();
        $arr = $mppr->archivosTrafico($this->idTrafico);
        if (!empty($arr)) {
            return $arr;
        }
        return;
    }

    protected function _buscarIndexRepositorio()
    {
        $mppr = new Archivo_Model_RepositorioIndex();
        if (($arr = $mppr->buscarIndex($this->patente, $this->aduana, $this->pedimento, $this->referencia))) {
            if ($arr["idTrafico"] == null) {
                $mppr->update($arr["id"], array("idTrafico" => $this->idTrafico));
            }
            return $arr["id"];
        } else {
            return $mppr->agregarDesdeTrafico($this->idTrafico, $this->idAduana, $this->rfcCliente, $this->patente, $this->aduana, $this->pedimento, $this->referencia, $this->usuario);
        }
        return;
    }

    public function verificarIndexRepositorios()
    {
        if (($id = $this->_buscarIndexRepositorio())) {
            $rs = new Archivo_Model_RepositorioMapper();
            if (!($rs->buscarRepositorio($this->patente, $this->aduana, $this->referencia))) {
                $rs->nuevoRepositorio($this->patente, $this->aduana, $this->pedimento, $this->referencia, $this->rfcCliente, $this->usuario);
            }
            return $id;
        }
        return;
    }

    protected function _buscarArchivoEnRepositorio($nombreArchivo)
    {
        $mppr = new Archivo_Model_Repositorio();
        if (($mppr->buscarArchivo($this->patente, $this->aduana, $this->pedimento, $this->referencia, $nombreArchivo))) {
            return true;
        }
        return;
    }

    protected function _agregarArchivoEnRepositorio($tipoArchivo, $nombreArchivo, $edocument = null)
    {
        $mppr = new Archivo_Model_Repositorio();

        $exists = $this->_buscarArchivoEnRepositorio(basename($nombreArchivo));

        if ($exists) {
            return true;
        }

        $added = $mppr->agregar(array(
            "id_trafico" => $this->idTrafico,
            "rfc_cliente" => $this->rfcCliente,
            "tipo_archivo" => $tipoArchivo,
            "referencia" => $this->referencia,
            "patente" => $this->patente,
            "aduana" => $this->aduana,
            "pedimento" => $this->pedimento,
            "nom_archivo" => basename($nombreArchivo),
            "ubicacion" => $nombreArchivo,
            "creado" => date("Y-m-d H:i:s"),
            "edocument" => $edocument,
            "usuario" => $this->usuario,
        ));
        if ($added) {
            return true;
        }
        return;
    }

    public function agregarArchivoExpediente($tipoArchivo, $nombreArchivo, $edocument = null)
    {
        $misc = new OAQ_Misc();
        $misc->set_baseDir($this->appconfig->getParam("expdest"));
        $directory = $misc->nuevoDirectorioExpediente($this->patente, $this->aduana, $misc->trimUpper($this->referencia));
        if (file_exists($directory)) {
            if (($id = $this->_buscarIndexRepositorio())) {
                if (!($this->_buscarArchivoEnRepositorio(basename($nombreArchivo)))) {
                    $this->_agregarArchivoEnRepositorio($tipoArchivo, $nombreArchivo, $edocument);
                }
            }
        }
    }

    public function guardarDetalleCove($arr, $filename)
    {
        $arr['filename'] = basename($filename);
        $pdf = new OAQ_Imprimir_CoveDetalle2019($arr, "P", "pt", "LETTER");
        $pdf->set_filename($filename);
        $pdf->Create();
        $pdf->Output($filename, "F");
        if (file_exists($filename)) {
            return true;
        }
        return;
    }

    public function borrar()
    {
        $db = Zend_Registry::get("oaqintranet");

        /* $sql = $db->select()
          ->from("repositorio", array("id", "ubicacion"))
          ->where("patente = ?", $this->patente)
          ->where("aduana = ?", $this->aduana)
          ->where("referencia = ?", $this->referencia);
          $stmt_repo = $db->fetchAll($sql);
          if (!empty($stmt_repo)) {
          foreach ($stmt_repo as $item) {
          if (file_exists($item["ubicacion"])) {
          unlink($item["ubicacion"]);
          }
          $db->delete("repositorio", array("id = ?" => $item["id"]));
          }
          } */

        $sql = $db->select()
            ->from("repositorio_index", array("id"))
            ->where("patente = ?", $this->patente)
            ->where("aduana = ?", $this->aduana)
            ->where("referencia = ?", $this->referencia);
        $stmt_repoi = $db->fetchAll($sql);
        if (!empty($stmt_repoi)) {
            foreach ($stmt_repoi as $item) {
                $db->delete("repositorio_index", array("id = ?" => $item["id"]));
            }
        }

        $sql = $db->select()
            ->from("trafico_facturas", array("id"))
            ->where("idTrafico = ?", $this->idTrafico);
        $stmt_facts = $db->fetchAll($sql);
        if (!empty($stmt_facts)) {
            foreach ($stmt_facts as $item) {
                $db->delete("trafico_factprod", array("idFactura = ?" => $item["id"]));
                $db->delete("trafico_factdetalle", array("idFactura = ?" => $item["id"]));
            }
        }

        /* $sql = $db->select()
          ->from("trafico_imagenes", array("id", "carpeta", "imagen", "miniatura"))
          ->where("idTrafico = ?", $this->idTrafico);
          $stmt_imgs = $db->fetchAll($sql);
          if (!empty($stmt_imgs)) {
          foreach ($stmt_imgs as $item) {
          if (file_exists($item["carpeta"] . DIRECTORY_SEPARATOR . $item["imagen"])) {
          unlink($item["carpeta"] . DIRECTORY_SEPARATOR . $item["imagen"]);
          }
          if (file_exists($item["carpeta"] . DIRECTORY_SEPARATOR . $item["miniatura"])) {
          unlink($item["carpeta"] . DIRECTORY_SEPARATOR . $item["miniatura"]);
          }
          $db->delete("trafico_imagenes", array("id = ?" => $item["id"]));
          }
          } */

        $db->delete("trafico_bitacora_pedimentos", array("idTrafico = ?" => $this->idTrafico));
        $db->delete("trafico_candados", array("idTrafico = ?" => $this->idTrafico));
        $db->delete("trafico_comentarios", array("idTrafico = ?" => $this->idTrafico));
        $db->delete("trafico_facturas_log", array("idTrafico = ?" => $this->idTrafico));
        $db->delete("trafico_fechas", array("idTrafico = ?" => $this->idTrafico));
        $db->delete("trafico_guias", array("idTrafico = ?" => $this->idTrafico));
        $db->delete("trafico_notificaciones", array("idTrafico = ?" => $this->idTrafico));
        $db->delete("trafico_ordenremision", array("idTrafico = ?" => $this->idTrafico));
        $db->delete("trafico_pedimento", array("idTrafico = ?" => $this->idTrafico));
        $db->delete("trafico_trans", array("idTrafico = ?" => $this->idTrafico));
        $db->delete("trafico_vucem", array("idTrafico = ?" => $this->idTrafico));
        $db->delete("traficos", array("id = ?" => $this->idTrafico));
        return true;
    }

    public function descargaCove($idCliente, $cove, $out_dir, $nom_archivo)
    {
        if (isset($idCliente)) {
            $mpp = new Trafico_Model_SellosClientes();
            $sello = $mpp->obtenerPorIdCliente($idCliente);
        }

        if (!isset($sello)) {
            return "No se encuentra sello.";
        }

        $cadena = "|{$sello["rfc"]}|{$cove}|";

        $pkeyid = openssl_get_privatekey(base64_decode($sello['spem']), $sello['spem_pswd']);

        openssl_sign($cadena, $signature, $pkeyid, OPENSSL_ALGO_SHA256);

        $data = array(
            "username" => $sello["rfc"],
            "password" => $sello["ws_pswd"],
            "certificado" => $sello['cer'],
            "firma" => base64_encode($signature),
        );

        $res = array();

        $con = new OAQ_XmlCoves($data, $cove, $cadena);

        $f_xml_con = $out_dir . DIRECTORY_SEPARATOR . preg_replace('/\\.[^.\\s]{3,4}$/', '', $nom_archivo) . '_CON.xml';
        $f_xml_res = $out_dir . DIRECTORY_SEPARATOR . preg_replace('/\\.[^.\\s]{3,4}$/', '', $nom_archivo) . '.xml';

        $f_xml_con_acuse = $out_dir . DIRECTORY_SEPARATOR . preg_replace('/\\.[^.\\s]{3,4}$/', '', $nom_archivo) . '_CON_ACUSE.xml';
        $f_xml_res_acuse = $out_dir . DIRECTORY_SEPARATOR . preg_replace('/\\.[^.\\s]{3,4}$/', '', $nom_archivo) . '_ACUSE.xml';
        $f_xml_acuse_pdf = $out_dir . DIRECTORY_SEPARATOR . preg_replace('/\\.[^.\\s]{3,4}$/', '', $nom_archivo) . '_ACUSE.pdf';


        if (!file_exists($f_xml_con)) {
            $con->xmlConsultaEdocument();
            $xml_con = $con->getXml();
            file_put_contents($f_xml_con, $xml_con);
        } else {
            $xml_con = file_get_contents($f_xml_con);
        }

        if ($xml_con) {
            if (!file_exists($f_xml_res)) {
                $con->descargaEdocument($xml_con);
                $xml_res = $con->getResponse();
                if ($xml_res) {
                    file_put_contents($f_xml_res, $xml_res);
                }
            }
            if (file_exists($f_xml_res)) {
                $res['xml'] = $f_xml_res;
            }
        }

        if (!file_exists($f_xml_con_acuse)) {
            $con->xmlConsultaAcuseEdocument();
            $xml_con_acuse = $con->getXml();
            file_put_contents($f_xml_con_acuse, $xml_con_acuse);
        } else {
            $xml_con_acuse = file_get_contents($f_xml_con_acuse);
        }

        if ($xml_con_acuse) {
            if (!file_exists($f_xml_res_acuse)) {
                $con->descargaAcuseEdocument($xml_con_acuse);
                $xml_res_acuse = $con->getResponse();
                file_put_contents($f_xml_res_acuse, "<S:Envelope>" . $con->stringInsideTags($xml_res_acuse, "S:Envelope") . "</S:Envelope>");
            } else {
                $xml_res_acuse = file_get_contents($f_xml_res_acuse);
            }
            if ($xml_res_acuse) {

                if (!file_exists($f_xml_acuse_pdf)) {
                    if (($con->analizarRespuesaAcuse($xml_res_acuse, $f_xml_acuse_pdf))) {
                        $res['pdf'] = $f_xml_acuse_pdf;
                    }
                } else{
                    $res['pdf'] = $f_xml_acuse_pdf;
                }
            }
        }

        return $res;
    }

    public function descargaPedimento($idCliente = null, $patente = null)
    {

        if (isset($idCliente)) {
            $mpp = new Trafico_Model_SellosClientes();
            $sello = $mpp->obtenerPorId($idCliente);
        }

        if (isset($patente)) {
            if ($patente == 3589) {
                $firmantes = new Vucem_Model_VucemFirmanteMapper();
                $sello = $firmantes->obtenerDetalleFirmante("MALL640523749");
            }
        }

        if (!isset($sello)) {
            return "No se encuentra sello.";
        }

        $arr["usuario"] = array(
            "username" => $sello["rfc"],
            "password" => $sello["ws_pswd"],
            "certificado" => null,
            "key" => null,
            "new" => null,
        );

        $xml = new OAQ_XmlPedimentos();
        $xml->set_aduana($this->aduana);
        $xml->set_patente($this->patente);
        $xml->set_pedimento($this->pedimento);

        $xml->set_array($arr);

        $xml->consultaPedimentoCompleto();

        $serv = new OAQ_Servicios();
        $serv->setXml($xml->getXml());

        $con_filename = $this->_nombreArchivo('PEDCONSULTA');
        if (!file_exists($con_filename)) {
            $this->_guardarArchivoXml($con_filename, $xml->getXml());
        }

        $ped_filename = $this->_nombreArchivo('PEDIMENTO');
        if (!file_exists($ped_filename)) {

            $serv->consumirPedimento();

            $res = new OAQ_Respuestas();
            $resp = $res->analizarRespuestaPedimento($serv->getResponse());

            if ($resp["error"] == false && isset($resp["numeroOperacion"])) {
                $this->_guardarArchivoXml($ped_filename, $this->misc->formatXmlString($serv->getResponse()));
                if (file_exists($ped_filename)) {
                    $this->_analizar($ped_filename);
                }
                return array(
                    "success" => true,
                    "message" => null,
                );
            } else {
                return $resp;
            }
        } else {
            $this->_analizar($ped_filename);
            return array(
                "success" => true,
                "message" => "El pedimento ya existe en el repositorio.",
            );
        }
    }

    protected function _analizar($xmlPedimento)
    {
        $array = $this->_datosXmlPedimento($xmlPedimento);
        if (!empty($array)) {
            $this->_agregarDb($array);
            $this->_agregarArchivoEnRepositorio(91, $xmlPedimento);
        }
    }

    protected function _datosXmlPedimento($xmlPedimento)
    {

        $vu = new OAQ_VucemEnh();
        $array = $vu->xmlStrToArray(file_get_contents($xmlPedimento));

        if (isset($array["Body"]["consultarPedimentoCompletoRespuesta"])) {
            $body = $array["Body"]["consultarPedimentoCompletoRespuesta"];

            $arr = [];

            if (isset($body["numeroOperacion"])) {
                $arr["numeroOperacion"] = (int) $body["numeroOperacion"];
            }

            if (isset($body["pedimento"]["encabezado"])) {
                $h = $body["pedimento"]["encabezado"];

                if (isset($h["tipoOperacion"]["clave"])) {
                    $arr["tipoOperacion"] = (int) $h["tipoOperacion"]["clave"];
                }

                if (isset($h["claveDocumento"]["clave"])) {
                    $arr["clavePedimento"] = $h["claveDocumento"]["clave"];
                }

                if (isset($h["rfcAgenteAduanalSocFactura"])) {
                    $arr["rfcAgenteAduanalSocFactura"] = $h["rfcAgenteAduanalSocFactura"];
                }

                if (isset($h["curpApoderadomandatario"])) {
                    $arr["curpApoderadomandatario"] = $h["curpApoderadomandatario"];
                }
            }

            if (isset($body["pedimento"]["partidas"])) {
                $arr["partidas"] = count($body["pedimento"]["partidas"]);
            }

            if (isset($body["pedimento"]["importadorExportador"])) {
                $arr["rfcCliente"] = $body["pedimento"]["importadorExportador"]["rfc"];
            }

            return $arr;
        }
        return;
    }

    protected function _agregarDb($arr)
    {
        $mppr = new Vucem_Model_VucemPedimentosMapper();
        if (!($mppr->verificar($this->patente, $this->aduana, $this->pedimento))) {
            $arr["idTrafico"] = $this->idTrafico;
            $arr["patente"] = $this->patente;
            $arr["aduana"] = $this->aduana;
            $arr["pedimento"] = $this->pedimento;
            $mppr->add($arr);
        }
    }

    protected function _nombreArchivo($prefix, $ext = '.xml')
    {
        return $this->_directorio() . DIRECTORY_SEPARATOR . $prefix . '_' . $this->aduana . "-" . $this->patente . "-" . $this->pedimento . $ext;
    }

    protected function _directorio()
    {
        $this->misc->set_baseDir($this->directorio);
        $path = $this->misc->nuevoDirectorioExpediente($this->patente, $this->aduana, $this->referencia);
        if (file_exists($path)) {
            return $path;
        }
        throw new Exception("Unable to find directory");
    }

    public function directorioExpediente()
    {
        $this->misc->set_baseDir($this->directorio);
        $path = $this->misc->nuevoDirectorioExpediente($this->patente, $this->aduana, $this->referencia);
        if (file_exists($path)) {
            return $path;
        }
        throw new Exception("Unable to find directory");
    }

    protected function _guardarArchivoXml($filename, $xml)
    {
        try {
            file_put_contents($filename, $xml);
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public function obtenerFacturas()
    {
        $mppr = new Trafico_Model_TraficoFacturasMapper();
        $arr = $mppr->obtenerDetalleFacturas($this->idTrafico);
        if (!empty($arr)) {
            return $arr;
        }
        return;
    }

    public function obtenerFacturasPedimento()
    {
        $mppr = new Trafico_Model_TraficoFacturasMapper();
        $arr = $mppr->obtenerFacturasPedimento($this->idTrafico);
        if (!empty($arr)) {
            return $arr;
        }
        return;
    }

    public function obtenerProductosPartidas($agrupado = null)
    {
        $mppr = new Trafico_Model_TraficoFacturasMapper();
        $mppr_p = new Trafico_Model_FactProd();
        $arr = $mppr->obtenerDetalleFacturas($this->idTrafico);

        $ids = array();

        foreach ($arr as $item) {
            $ids[] = (int) $item["idFactura"];
        }

        if (!empty($ids)) {
            if (!$agrupado) {
                $partidas = $mppr_p->obtenerPartidas($ids);
            } else {
                $partidas = $mppr_p->obtenerPartidasAgrupadas($ids);
            }
            if (!empty($partidas)) {
                return $partidas;
            }
        }

        return;
    }

    public function obtenerDatos()
    {
        $tn = new Trafico_Model_TraficoGuiasMapper();
        $arr = $this->traficos->encabezado($this->idTrafico);
        if (!empty($arr)) {
            $guias = "";
            $gs = $tn->obtenerGuias($this->idTrafico);
            if (isset($gs) && !empty($gs)) {
                foreach ($gs as $g) {
                    $guias .= $g["guia"] . ", ";
                }
                $arr['blGuia'] = preg_replace("/,\s$/", "", $guias);
            }
            return $arr;
        }
        return;
    }

    public function obtenerProductosFactura($idFactura)
    {
        $mppr = new Trafico_Model_FactProd();
        $arr = $mppr->obtener($idFactura);
        if (!empty($arr)) {
            return $arr;
        }
        return;
    }

    public function obtenerCliente()
    {
        $arr = $this->clientes->datosCliente($this->idCliente);
        if (!empty($arr)) {
            return $arr;
        }
        return;
    }

    public function seleccionConsolidarTraficos(array $arr)
    {
        if (is_array($arr)) {
            $array = [];
            foreach ($arr as $item) {
                $row = $this->traficos->encabezado($item);
                if (!empty($row)) {
                    $array[] = array(
                        "idTrafico" => $row["id"],
                        "referencia" => $row["referencia"]
                    );
                }
            }
            return $array;
        } else {
            throw new Exception("No array provided");
        }
    }

    public function consolidarTraficos($idMaster, $id)
    {
        if (($this->traficos->traficoMaster($idMaster, $id))) {
            return true;
        }
        return;
    }

    public function traficosConsolidados()
    {
        if (($arr = $this->traficos->traficosConsolidados($this->idTrafico))) {
            return $arr;
        }
        return;
    }

    public function asignarOrdenCarga(array $arr, $ordenCarga)
    {
        if (is_array($arr)) {
            foreach ($arr as $item) {
                $this->traficos->actualizarOrdenCarga($item, $ordenCarga);
            }
            return true;
        } else {
            throw new Exception("No array provided");
        }
    }

    public function removerSufijos($referencia)
    {
        if (isset($referencia)) {
            if (preg_match("/C$|H$|R$|G$/", $referencia) && !preg_match("/-C$|-H$|-R$|-G$/", $referencia)) {
                return substr($referencia, 0, -1);
            } else if (preg_match("/-C$|-H$|-R$|-G$|-E$/", $referencia)) {
                return substr($referencia, 0, -2);
            } else {
                return $referencia;
            }
        } else {
            throw new Exception("Referencia is not been set!");
        }
    }

    public function contarCovesEdocuments()
    {
        $vucem = new Trafico_Model_TraficoVucem();
        $arr = array(
            "coves" => $vucem->contarCoves($this->idTrafico),
            "edocuments" => $vucem->contarEdocuments($this->idTrafico),
        );
        $this->actualizar($arr);
        return $arr;
    }

    public function archivosDeExpediente($customer = null)
    {
        try {

            $mppr = new Archivo_Model_RepositorioMapper();

            if ($customer) {
                $files = $mppr->getFilesByReferenceCustomers($this->referencia, $this->patente, $this->aduana);
                return $files;
            }
        } catch (Exception $ex) {
        }
    }

    public function cambiarEstatus($estatus)
    {
        if (($this->traficos->actualizarEstatus($this->idTrafico, $estatus))) {
            return true;
        }
        return;
    }

    public function covesDeTrafico()
    {
        // 21 xml
        // 22 pdf
        // COVE203QF8G96 caracteres 
        $mppr = new Archivo_Model_Repositorio();
        $rows = $mppr->buscarCoves($this->patente, $this->aduana, $this->referencia);
        return $rows;
    }

    public function edocumentsDeTrafico()
    {
        // 27 xml
        // 56 pdf 
        // 0438200HTNYG6 13 caracteres
        $mppr = new Archivo_Model_Repositorio();
        $rows = $mppr->buscarEdocuments($this->patente, $this->aduana, $this->referencia);
        return $rows;
    }
}
