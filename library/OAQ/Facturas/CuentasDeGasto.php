<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class OAQ_Facturas_CuentasDeGasto {

    protected $_backup;
    protected $_basedir;
    protected $_baseday;
    protected $_client;
    protected $_fechaFactura;
    protected $_folio;
    protected $_referencias;
    protected $_misc;
    protected $_sat;
    protected $_cfd;
    protected $_pdf;
    protected $_xmlPath;
    protected $_pdfPath;
    protected $_dir;
    protected $_repo;
    protected $_db;
    protected $_uuid;
    protected $_emisorRfc;
    protected $_emisorNombre;
    protected $_receptorRfc;
    protected $_receptorNombre;
    protected $_pedimento;
    protected $_rfcCliente;

    function __construct() {
        $this->_sat = new OAQ_SATValidar();
        if (APPLICATION_ENV == "production") {
            $this->_backup = "/home/samba-share/expedientes/FacturacionElectronica";
            $this->_basedir = "/home/samba-share/expedientes";
        } else if (APPLICATION_ENV == "staging") {
            $this->_backup = "/home/samba-share/expedientes/FacturacionElectronica";
            $this->_basedir = "/home/samba-share/expedientes";
        } else {
            $this->_backup = "D:\\wamp64\\tmp\\facturacion";
            $this->_basedir = "D:\\wamp64\\tmp\\expedientes";
        }
        $context = stream_context_create(array(
            "ssl" => array(
                "verify_peer" => false,
                "verify_peer_name" => false,
                "allow_self_signed" => true
            )
        ));
        $this->_client = new Zend_Soap_Client("https://192.168.200.5/casaws/zfsoapsica?wsdl", array("stream_context" => $context));
        $this->_misc = new OAQ_Misc();
        $this->_repo = new Archivo_Model_RepositorioMapper();
        $this->_db = Zend_Registry::get("oaqintranet");
    }

    public function facturasRfc($fecha, $patente, $aduana, $rfc) {
        return $this->_client->facturasRfc($fecha, $patente, $aduana, $rfc);
    }

    public function facturas($fecha, $patente, $aduana) {
        return $this->_client->facturas($fecha, $patente, $aduana);
    }

    public function facturaFolio($folio) {
        return $this->_client->facturaFolio($folio);
    }

    public function facturasMes($year, $mes, $rfc) {
        return $this->_client->facturasMes($year, $mes, $rfc);
    }

    public function facturasMensual($year, $mes) {
        return $this->_client->facturasMensual($year, $mes);
    }

    protected function _convertirXml($xml) {
        $arr = $this->_sat->satToArray(html_entity_decode($xml));
        
        if (isset($arr["@attributes"]["Fecha"])) { // v 3.3
            
            $this->_fechaFactura = date("Y-m-d H:i:s", strtotime($arr["@attributes"]["Fecha"]));
            $this->_emisorRfc = $arr["Emisor"]["@attributes"]["Rfc"];
            $this->_emisorNombre = $arr["Emisor"]["@attributes"]["Nombre"];
            $this->_receptorRfc = $arr["Receptor"]["@attributes"]["Rfc"];
            $this->_receptorNombre = $arr["Receptor"]["@attributes"]["Nombre"];
            
        } else if (isset($arr["@attributes"]["fecha"])) { // v 2.4
            
            $this->_fechaFactura = date("Y-m-d H:i:s", strtotime($arr["@attributes"]["fecha"]));
            $this->_emisorRfc = $arr["Emisor"]["@attributes"]["rfc"];
            $this->_emisorNombre = $arr["Emisor"]["@attributes"]["nombre"];
            $this->_receptorRfc = $arr["Receptor"]["@attributes"]["rfc"];
            $this->_receptorNombre = $arr["Receptor"]["@attributes"]["nombre"];
            
            if (isset($arr["Complemento"]["TimbreFiscalDigital"]["@attributes"]["UUID"])) {
                $this->_uuid = $arr["Complemento"]["TimbreFiscalDigital"]["@attributes"]["UUID"];
            } elseif (isset($arr["Complemento"][0]["@attributes"]["UUID"])) {
                $this->_uuid = $arr["Complemento"][0]["@attributes"]["UUID"];
            }
            
        } else {
            throw new Exception("No se pudieron detectar los valores del archivo XML.");
        }
        if (isset($this->_fechaFactura)) {
            if (file_exists($this->_backup)) {
                $this->_baseday = $this->_backup . DIRECTORY_SEPARATOR . substr($this->_fechaFactura, 0, 4) . DIRECTORY_SEPARATOR . substr($this->_fechaFactura, 5, 2) . DIRECTORY_SEPARATOR . substr($this->_fechaFactura, 8, 2);
                if (!file_exists($this->_baseday)) {
                    mkdir($this->_baseday, 0777, true);
                }
            } else {
                throw new Exception("{$this->_backup} no existe.");
            }
        }
        return $arr;
    }

    protected function _removerSufijos($referencia) {
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

    protected function _guardarXml($dir) {
        if (!file_exists($dir . DIRECTORY_SEPARATOR . $this->_cfd["filename"])) {
            file_put_contents($dir . DIRECTORY_SEPARATOR . $this->_cfd["filename"], html_entity_decode($this->_cfd["xml"]));
        }
        if (file_exists($dir . DIRECTORY_SEPARATOR . $this->_cfd["filename"])) {
            $this->_xmlPath = $dir . DIRECTORY_SEPARATOR . $this->_cfd["filename"];
            return true;
        }
        return;
    }

    protected function _guardarPdf($dir) {
        if (isset($this->_pdf) && $this->_pdf !== false) {
            if (!file_exists($dir . DIRECTORY_SEPARATOR . pathinfo($this->_cfd["filename"], PATHINFO_FILENAME) . ".pdf")) {
                file_put_contents($dir . DIRECTORY_SEPARATOR . pathinfo($this->_cfd["filename"], PATHINFO_FILENAME) . ".pdf", base64_decode($this->_pdf["content"]));
            }
            if (file_exists($dir . DIRECTORY_SEPARATOR . pathinfo($this->_cfd["filename"], PATHINFO_FILENAME) . ".pdf")) {
                $this->_pdfPath = $dir . DIRECTORY_SEPARATOR . pathinfo($this->_cfd["filename"], PATHINFO_FILENAME) . ".pdf";
                return true;
            }
            return;        
        }
        return;
    }

    protected function _crearIndice($patente, $aduana, $referencia) {
        try {
            $mapper = new Trafico_Model_TraficoAduanasMapper();
            $arr = array(
                "idAduana" => $mapper->idAduana($patente, $aduana),
                "rfcCliente" => null,
                "patente" => $patente,
                "aduana" => $aduana,
                "pedimento" => null,
                "referencia" => $referencia,
                "creado" => date("Y-m-d H:i:s"),
                "creadoPor" => "Auto",
            );
            $stmt = $this->_db->insert("repositorio_index", $arr);
            if ($stmt) {
                return true;
            }
            return;
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    protected function _verificar($patente, $aduana, $referencia, $folio, $nombreArchivo) {
        try {
            $sql = $this->_db->select()
                    ->from("repositorio", array("id"))
                    ->where("patente = ?", $patente)
                    ->where("aduana = ?", $aduana)
                    ->where("referencia = ?", $referencia)
                    ->where("folio = ?", $folio)
                    ->where("nom_archivo = ?", basename($nombreArchivo));
            $stmt = $this->_db->fetchRow($sql);
            if ($stmt) {
                return true;
            }
            return;
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    protected function _agregarARepositorio($patente, $aduana, $referencia, $folio, $nombreArchivo) {
        try {
            $arr = array(
                "tipo_archivo" => 2,
                "pedimento" => isset($this->_pedimento) ? $this->_pedimento : null,
                "rfc_cliente" => isset($this->_rfcCliente) ? $this->_rfcCliente : null,
                "referencia" => $referencia,
                "patente" => $patente,
                "aduana" => $aduana,
                "folio" => $folio,
                "uuid" => isset($this->_uuid) ? strtoupper($this->_uuid) : null,
                "fecha" => $this->_fechaFactura,
                "emisor_rfc" => $this->_emisorRfc,
                "emisor_nombre" => $this->_emisorNombre,
                "receptor_rfc" => $this->_receptorRfc,
                "receptor_nombre" => $this->_receptorNombre,
                "nom_archivo" => basename($nombreArchivo),
                "ubicacion" => $nombreArchivo,
                "creado" => date("Y-m-d H:i:s"),
                "usuario" => "Auto",
            );
            $stmt = $this->_db->insert("repositorio", $arr);
            if ($stmt) {
                return true;
            }
            return;
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public function obtenerCuenta($folio, $patente, $aduana, $referencia) {
        $this->_cfd = $this->_client->folioCdfi($folio);
        $this->_pdf = $this->_client->searchPdf($folio);
        $this->_referencias = new OAQ_Referencias();
        $this->_folio = $folio;
        if (!isset($this->_cfd) && !isset($this->_cfd["xml"])) {
            return;
        }
        $arr = $this->_convertirXml($this->_cfd["xml"]);
        if ($arr && !empty($arr) && isset($this->_baseday)) {
            $this->_guardarXml($this->_baseday);
            $this->_guardarPdf($this->_baseday);
            $referencia = $this->_removerSufijos($referencia);
            $this->_dir = $this->_misc->createReferenceDir($this->_basedir, $patente, $aduana, $referencia);
            if (file_exists($this->_dir)) {
                $mapper = new Trafico_Model_TraficoAduanasMapper();
                if ($mapper->idAduana($patente, $aduana) == null) {
                    return;
                }
                $this->_referencias = new OAQ_Referencias(array("patente" => $patente, "aduana" => $aduana, "referencia" => $referencia, "usuario" => "AutoFacturacion"));
                if (($idTrafico = $this->_referencias->buscarTrafico())) {
                    $this->_referencias->setIdTrafico($idTrafico);
                    $this->_referencias->setIdUsuario(0);
                    $this->_referencias->actualizarFechaFacturacion($this->_fechaFactura, $this->_folio);
                }
                $arrs = $this->_referencias->crearRepositorioRest($patente, $aduana, $referencia);
                if ($arrs === null) {
                    $this->_crearIndice($patente, $aduana, $referencia);
                } else {
                    $this->_pedimento = $arrs["pedimento"];
                    $this->_rfcCliente = $arrs["rfcCliente"];
                }
                if ($this->_guardarXml($this->_dir)) {
                    if (!($this->_verificar($patente, $aduana, $referencia, $folio, $this->_xmlPath))) {
                        $this->_agregarARepositorio($patente, $aduana, $referencia, $folio, $this->_xmlPath);
                    }
                }
                if ($this->_guardarPdf($this->_dir)) {
                    if (!($this->_verificar($patente, $aduana, $referencia, $folio, $this->_pdfPath))) {
                        $this->_agregarARepositorio($patente, $aduana, $referencia, $folio, $this->_pdfPath);
                    }
                }
                return array(
                    "idTrafico" => isset($idTrafico) ? $idTrafico : null,
                    "patente" => $patente,
                    "aduana" => $aduana,
                    "referencia" => $this->_pedimento,
                    "pedimento" => $referencia,
                    "rfcCliente" => $this->_rfcCliente,
                    "nombreReceptor" => $this->_receptorNombre,
                    "folio" => $folio,
                );
            }
        } else {
            return;
        }
    }

}
