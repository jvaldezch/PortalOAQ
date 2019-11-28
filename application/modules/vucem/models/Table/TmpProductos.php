<?php

class Vucem_Model_Table_TmpProductos {

    protected $id;
    protected $IDFACTURA;
    protected $ID_FACT;
    protected $ID_PROD;
    protected $PATENTE;
    protected $ADUANA;
    protected $PEDIMENTO;
    protected $REFERENCIA;
    protected $SUB;
    protected $ORDEN;
    protected $CODIGO;
    protected $SUBFRA;
    protected $DESC1;
    protected $PREUNI;
    protected $VALCOM;
    protected $MONVAL;
    protected $VALCEQ;
    protected $VALMN;
    protected $VALDLS;
    protected $CANTFAC;
    protected $CANTTAR;
    protected $UMC;
    protected $UMT;
    protected $PAIORI;
    protected $PAICOM;
    protected $FACTAJU;
    protected $CERTLC;
    protected $PARTE;
    protected $CAN_OMA;
    protected $UMC_OMA;
    protected $DESC_COVE;
    protected $OBS;
    protected $MARCA;
    protected $MODELO;
    protected $SUBMODELO;
    protected $NUMSERIE;
    protected $CREADO;
    protected $MODIFICADO;
    protected $USUARIO;
    protected $ACTIVE;

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

    function getIDFACTURA() {
        return $this->IDFACTURA;
    }

    function getID_FACT() {
        return $this->ID_FACT;
    }

    function getID_PROD() {
        return $this->ID_PROD;
    }

    function getPATENTE() {
        return $this->PATENTE;
    }

    function getADUANA() {
        return $this->ADUANA;
    }

    function getPEDIMENTO() {
        return $this->PEDIMENTO;
    }

    function getREFERENCIA() {
        return $this->REFERENCIA;
    }

    function getSUB() {
        return $this->SUB;
    }

    function getORDEN() {
        return $this->ORDEN;
    }

    function getCODIGO() {
        return $this->CODIGO;
    }

    function getSUBFRA() {
        return $this->SUBFRA;
    }

    function getDESC1() {
        return $this->DESC1;
    }

    function getPREUNI() {
        return $this->PREUNI;
    }

    function getVALCOM() {
        return $this->VALCOM;
    }

    function getMONVAL() {
        return $this->MONVAL;
    }

    function getVALCEQ() {
        return $this->VALCEQ;
    }

    function getVALMN() {
        return $this->VALMN;
    }

    function getVALDLS() {
        return $this->VALDLS;
    }

    function getCANTFAC() {
        return $this->CANTFAC;
    }

    function getCANTTAR() {
        return $this->CANTTAR;
    }

    function getUMC() {
        return $this->UMC;
    }

    function getUMT() {
        return $this->UMT;
    }

    function getPAIORI() {
        return $this->PAIORI;
    }

    function getPAICOM() {
        return $this->PAICOM;
    }

    function getFACTAJU() {
        return $this->FACTAJU;
    }

    function getCERTLC() {
        return $this->CERTLC;
    }

    function getPARTE() {
        return $this->PARTE;
    }

    function getCAN_OMA() {
        return $this->CAN_OMA;
    }

    function getUMC_OMA() {
        return $this->UMC_OMA;
    }

    function getDESC_COVE() {
        return $this->DESC_COVE;
    }

    function getOBS() {
        return $this->OBS;
    }

    function getMARCA() {
        return $this->MARCA;
    }

    function getMODELO() {
        return $this->MODELO;
    }

    function getSUBMODELO() {
        return $this->SUBMODELO;
    }

    function getNUMSERIE() {
        return $this->NUMSERIE;
    }

    function getCREADO() {
        return $this->CREADO;
    }

    function getMODIFICADO() {
        return $this->MODIFICADO;
    }

    function getUSUARIO() {
        return $this->USUARIO;
    }

    function getACTIVE() {
        return $this->ACTIVE;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setIDFACTURA($IDFACTURA) {
        $this->IDFACTURA = $IDFACTURA;
    }

    function setID_FACT($ID_FACT) {
        $this->ID_FACT = $ID_FACT;
    }

    function setID_PROD($ID_PROD) {
        $this->ID_PROD = $ID_PROD;
    }

    function setPATENTE($PATENTE) {
        $this->PATENTE = $PATENTE;
    }

    function setADUANA($ADUANA) {
        $this->ADUANA = $ADUANA;
    }

    function setPEDIMENTO($PEDIMENTO) {
        $this->PEDIMENTO = $PEDIMENTO;
    }

    function setREFERENCIA($REFERENCIA) {
        $this->REFERENCIA = $REFERENCIA;
    }

    function setSUB($SUB) {
        $this->SUB = $SUB;
    }

    function setORDEN($ORDEN) {
        $this->ORDEN = $ORDEN;
    }

    function setCODIGO($CODIGO) {
        $this->CODIGO = $CODIGO;
    }

    function setSUBFRA($SUBFRA) {
        $this->SUBFRA = $SUBFRA;
    }

    function setDESC1($DESC1) {
        $this->DESC1 = $DESC1;
    }

    function setPREUNI($PREUNI) {
        $this->PREUNI = $PREUNI;
    }

    function setVALCOM($VALCOM) {
        $this->VALCOM = $VALCOM;
    }

    function setMONVAL($MONVAL) {
        $this->MONVAL = $MONVAL;
    }

    function setVALCEQ($VALCEQ) {
        $this->VALCEQ = $VALCEQ;
    }

    function setVALMN($VALMN) {
        $this->VALMN = $VALMN;
    }

    function setVALDLS($VALDLS) {
        $this->VALDLS = $VALDLS;
    }

    function setCANTFAC($CANTFAC) {
        $this->CANTFAC = $CANTFAC;
    }

    function setCANTTAR($CANTTAR) {
        $this->CANTTAR = $CANTTAR;
    }

    function setUMC($UMC) {
        $this->UMC = $UMC;
    }

    function setUMT($UMT) {
        $this->UMT = $UMT;
    }

    function setPAIORI($PAIORI) {
        $this->PAIORI = $PAIORI;
    }

    function setPAICOM($PAICOM) {
        $this->PAICOM = $PAICOM;
    }

    function setFACTAJU($FACTAJU) {
        $this->FACTAJU = $FACTAJU;
    }

    function setCERTLC($CERTLC) {
        $this->CERTLC = $CERTLC;
    }

    function setPARTE($PARTE) {
        $this->PARTE = $PARTE;
    }

    function setCAN_OMA($CAN_OMA) {
        $this->CAN_OMA = $CAN_OMA;
    }

    function setUMC_OMA($UMC_OMA) {
        $this->UMC_OMA = $UMC_OMA;
    }

    function setDESC_COVE($DESC_COVE) {
        $this->DESC_COVE = $DESC_COVE;
    }

    function setOBS($OBS) {
        $this->OBS = $OBS;
    }

    function setMARCA($MARCA) {
        $this->MARCA = $MARCA;
    }

    function setMODELO($MODELO) {
        $this->MODELO = $MODELO;
    }

    function setSUBMODELO($SUBMODELO) {
        $this->SUBMODELO = $SUBMODELO;
    }

    function setNUMSERIE($NUMSERIE) {
        $this->NUMSERIE = $NUMSERIE;
    }

    function setCREADO($CREADO) {
        $this->CREADO = $CREADO;
    }

    function setMODIFICADO($MODIFICADO) {
        $this->MODIFICADO = $MODIFICADO;
    }

    function setUSUARIO($USUARIO) {
        $this->USUARIO = $USUARIO;
    }

    function setACTIVE($ACTIVE) {
        $this->ACTIVE = $ACTIVE;
    }

}
