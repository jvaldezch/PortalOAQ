<?php

class Vucem_Model_Table_TmpFacturas {

    protected $id;
    protected $enviar;
    protected $firmante;
    protected $figura;
    protected $adenda;
    protected $IdFact;
    protected $Patente;
    protected $Aduana;
    protected $Pedimento;
    protected $Referencia;
    protected $TipoOperacion;
    protected $NumFactura;
    protected $NumParte;
    protected $FechaFactura;
    protected $Observaciones;
    protected $Subdivision;
    protected $RelFact;
    protected $Consolidado;
    protected $OrdenFact;
    protected $OrdenFactCon;
    protected $ValDls;
    protected $ValExt;
    protected $CertificadoOrigen;
    protected $NumExportador;
    protected $CveImp;
    protected $CteIden;
    protected $CteRfc;
    protected $CteNombre;
    protected $CteCalle;
    protected $CteNumExt;
    protected $CteNumInt;
    protected $CteColonia;
    protected $CteLocalidad;
    protected $CteCP;
    protected $CteMun;
    protected $CteEdo;
    protected $CtePais;
    protected $CvePro;
    protected $ProIden;
    protected $ProTaxID;
    protected $ProNombre;
    protected $ProCalle;
    protected $ProNumExt;
    protected $ProNumInt;
    protected $ProColonia;
    protected $ProLocalidad;
    protected $ProCP;
    protected $ProMun;
    protected $ProEdo;
    protected $ProPais;
    protected $Creado;
    protected $Modificado;
    protected $Usuario;
    protected $Active;
    protected $Manual;
    protected $Reenvio;

    public function __construct(array $options = null) {
        if (is_array($options)) {
            $this->setOptions($options);
        }
    }

    public function __set($name, $value) {
        $method = "set" . $name;
        if (("mapper" == $name) || !method_exists($this, $method)) {
            throw new Exception("Invalid invoice property");
        }
        $this->$method($value);
    }

    public function __get($name) {
        $method = "get" . $name;
        if (("mapper" == $name) || !method_exists($this, $method)) {
            throw new Exception("Invalid invoice property");
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
    
    function getId() {
        return $this->id;
    }

    function getEnviar() {
        return $this->enviar;
    }

    function getFirmante() {
        return $this->firmante;
    }

    function getFigura() {
        return $this->figura;
    }

    function getAdenda() {
        return $this->adenda;
    }

    function getIdFact() {
        return $this->IdFact;
    }

    function getPatente() {
        return $this->Patente;
    }

    function getAduana() {
        return $this->Aduana;
    }

    function getPedimento() {
        return $this->Pedimento;
    }

    function getReferencia() {
        return $this->Referencia;
    }

    function getTipoOperacion() {
        return $this->TipoOperacion;
    }

    function getNumFactura() {
        return $this->NumFactura;
    }

    function getNumParte() {
        return $this->NumParte;
    }

    function getFechaFactura() {
        return $this->FechaFactura;
    }

    function getObservaciones() {
        return $this->Observaciones;
    }

    function getSubdivision() {
        return $this->Subdivision;
    }

    function getRelFact() {
        return $this->RelFact;
    }

    function getConsolidado() {
        return $this->Consolidado;
    }

    function getOrdenFact() {
        return $this->OrdenFact;
    }

    function getOrdenFactCon() {
        return $this->OrdenFactCon;
    }

    function getValDls() {
        return $this->ValDls;
    }

    function getValExt() {
        return $this->ValExt;
    }

    function getCertificadoOrigen() {
        return $this->CertificadoOrigen;
    }

    function getNumExportador() {
        return $this->NumExportador;
    }

    function getCveImp() {
        return $this->CveImp;
    }

    function getCteIden() {
        return $this->CteIden;
    }

    function getCteRfc() {
        return $this->CteRfc;
    }

    function getCteNombre() {
        return $this->CteNombre;
    }

    function getCteCalle() {
        return $this->CteCalle;
    }

    function getCteNumExt() {
        return $this->CteNumExt;
    }

    function getCteNumInt() {
        return $this->CteNumInt;
    }

    function getCteColonia() {
        return $this->CteColonia;
    }

    function getCteLocalidad() {
        return $this->CteLocalidad;
    }

    function getCteCP() {
        return $this->CteCP;
    }

    function getCteMun() {
        return $this->CteMun;
    }

    function getCteEdo() {
        return $this->CteEdo;
    }

    function getCtePais() {
        return $this->CtePais;
    }

    function getCvePro() {
        return $this->CvePro;
    }

    function getProIden() {
        return $this->ProIden;
    }

    function getProTaxID() {
        return $this->ProTaxID;
    }

    function getProNombre() {
        return $this->ProNombre;
    }

    function getProCalle() {
        return $this->ProCalle;
    }

    function getProNumExt() {
        return $this->ProNumExt;
    }

    function getProNumInt() {
        return $this->ProNumInt;
    }

    function getProColonia() {
        return $this->ProColonia;
    }

    function getProLocalidad() {
        return $this->ProLocalidad;
    }

    function getProCP() {
        return $this->ProCP;
    }

    function getProMun() {
        return $this->ProMun;
    }

    function getProEdo() {
        return $this->ProEdo;
    }

    function getProPais() {
        return $this->ProPais;
    }

    function getCreado() {
        return $this->Creado;
    }

    function getModificado() {
        return $this->Modificado;
    }

    function getUsuario() {
        return $this->Usuario;
    }

    function getActive() {
        return $this->Active;
    }

    function getManual() {
        return $this->Manual;
    }

    function getReenvio() {
        return $this->Reenvio;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setEnviar($enviar) {
        $this->enviar = $enviar;
    }

    function setFirmante($firmante) {
        $this->firmante = $firmante;
    }

    function setFigura($figura) {
        $this->figura = $figura;
    }

    function setAdenda($adenda) {
        $this->adenda = $adenda;
    }

    function setIdFact($IdFact) {
        $this->IdFact = $IdFact;
    }

    function setPatente($Patente) {
        $this->Patente = $Patente;
    }

    function setAduana($Aduana) {
        $this->Aduana = $Aduana;
    }

    function setPedimento($Pedimento) {
        $this->Pedimento = $Pedimento;
    }

    function setReferencia($Referencia) {
        $this->Referencia = $Referencia;
    }

    function setTipoOperacion($TipoOperacion) {
        $this->TipoOperacion = $TipoOperacion;
    }

    function setNumFactura($NumFactura) {
        $this->NumFactura = $NumFactura;
    }

    function setNumParte($NumParte) {
        $this->NumParte = $NumParte;
    }

    function setFechaFactura($FechaFactura) {
        $this->FechaFactura = $FechaFactura;
    }

    function setObservaciones($Observaciones) {
        $this->Observaciones = $Observaciones;
    }

    function setSubdivision($Subdivision) {
        $this->Subdivision = $Subdivision;
    }

    function setRelFact($RelFact) {
        $this->RelFact = $RelFact;
    }

    function setConsolidado($Consolidado) {
        $this->Consolidado = $Consolidado;
    }

    function setOrdenFact($OrdenFact) {
        $this->OrdenFact = $OrdenFact;
    }

    function setOrdenFactCon($OrdenFactCon) {
        $this->OrdenFactCon = $OrdenFactCon;
    }

    function setValDls($ValDls) {
        $this->ValDls = $ValDls;
    }

    function setValExt($ValExt) {
        $this->ValExt = $ValExt;
    }

    function setCertificadoOrigen($CertificadoOrigen) {
        $this->CertificadoOrigen = $CertificadoOrigen;
    }

    function setNumExportador($NumExportador) {
        $this->NumExportador = $NumExportador;
    }

    function setCveImp($CveImp) {
        $this->CveImp = $CveImp;
    }

    function setCteIden($CteIden) {
        $this->CteIden = $CteIden;
    }

    function setCteRfc($CteRfc) {
        $this->CteRfc = $CteRfc;
    }

    function setCteNombre($CteNombre) {
        $this->CteNombre = $CteNombre;
    }

    function setCteCalle($CteCalle) {
        $this->CteCalle = $CteCalle;
    }

    function setCteNumExt($CteNumExt) {
        $this->CteNumExt = $CteNumExt;
    }

    function setCteNumInt($CteNumInt) {
        $this->CteNumInt = $CteNumInt;
    }

    function setCteColonia($CteColonia) {
        $this->CteColonia = $CteColonia;
    }

    function setCteLocalidad($CteLocalidad) {
        $this->CteLocalidad = $CteLocalidad;
    }

    function setCteCP($CteCP) {
        $this->CteCP = $CteCP;
    }

    function setCteMun($CteMun) {
        $this->CteMun = $CteMun;
    }

    function setCteEdo($CteEdo) {
        $this->CteEdo = $CteEdo;
    }

    function setCtePais($CtePais) {
        $this->CtePais = $CtePais;
    }

    function setCvePro($CvePro) {
        $this->CvePro = $CvePro;
    }

    function setProIden($ProIden) {
        $this->ProIden = $ProIden;
    }

    function setProTaxID($ProTaxID) {
        $this->ProTaxID = $ProTaxID;
    }

    function setProNombre($ProNombre) {
        $this->ProNombre = $ProNombre;
    }

    function setProCalle($ProCalle) {
        $this->ProCalle = $ProCalle;
    }

    function setProNumExt($ProNumExt) {
        $this->ProNumExt = $ProNumExt;
    }

    function setProNumInt($ProNumInt) {
        $this->ProNumInt = $ProNumInt;
    }

    function setProColonia($ProColonia) {
        $this->ProColonia = $ProColonia;
    }

    function setProLocalidad($ProLocalidad) {
        $this->ProLocalidad = $ProLocalidad;
    }

    function setProCP($ProCP) {
        $this->ProCP = $ProCP;
    }

    function setProMun($ProMun) {
        $this->ProMun = $ProMun;
    }

    function setProEdo($ProEdo) {
        $this->ProEdo = $ProEdo;
    }

    function setProPais($ProPais) {
        $this->ProPais = $ProPais;
    }

    function setCreado($Creado) {
        $this->Creado = $Creado;
    }

    function setModificado($Modificado) {
        $this->Modificado = $Modificado;
    }

    function setUsuario($Usuario) {
        $this->Usuario = $Usuario;
    }

    function setActive($Active) {
        $this->Active = $Active;
    }

    function setManual($Manual) {
        $this->Manual = $Manual;
    }

    function setReenvio($Reenvio) {
        $this->Reenvio = $Reenvio;
    }

}
