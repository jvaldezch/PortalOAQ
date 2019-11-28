<?php

class Trafico_Model_Table_TraficoCvePed {

    protected $id;
    protected $clave;
    protected $descripcion;
    protected $IMP;
    protected $ADVAI;
    protected $DTAAI;
    protected $IVAAI;
    protected $ISANAI;
    protected $IEPSAI;
    protected $CCAI;
    protected $P_DTAI;
    protected $DTAI;
    protected $IVAI;
    protected $CONSI;
    protected $EXP;
    protected $ADVAE;
    protected $DTAAE;
    protected $IVAAE;
    protected $ISANAE;
    protected $IEPSAE;
    protected $CCAE;
    protected $P_DTAE;
    protected $DTAE;
    protected $IVAE;
    protected $CONSE;
    protected $CANDADOS;
    protected $TRANSITO;
    protected $PREVIOS;
    protected $EXTRACCION;
    protected $SECTORIAL;
    protected $REGIMENI;
    protected $REGIMENE;
    protected $ACTUADTA;
    protected $ACTUAIVA;
    protected $ACTUACCMT;
    protected $ACTUAISIE;
    protected $IVA0IMP;

    public function __construct(array $options = null) {
        if (is_array($options)) {
            $this->setOptions($options);
        }
    }

    public function __set($name, $value) {
        $method = 'set' . $name;
        if (('mapper' == $name) || !method_exists($this, $method)) {
            throw new Exception('Invalid invoice property');
        }
        $this->$method($value);
    }

    public function __get($name) {
        $method = 'get' . $name;
        if (('mapper' == $name) || !method_exists($this, $method)) {
            throw new Exception('Invalid invoice property');
        }
        return $this->$method();
    }

    public function setOptions(array $options) {
        $methods = get_class_methods($this);
        foreach ($options as $key => $value) {
            $method = 'set' . ucfirst($key);
            if (in_array($method, $methods)) {
                $this->$method($value);
            }
        }
        return $this;
    }

    function getId() {
        return $this->id;
    }

    function getClave() {
        return $this->clave;
    }

    function getDescripcion() {
        return $this->descripcion;
    }

    function getIMP() {
        return $this->IMP;
    }

    function getADVAI() {
        return $this->ADVAI;
    }

    function getDTAAI() {
        return $this->DTAAI;
    }

    function getIVAAI() {
        return $this->IVAAI;
    }

    function getISANAI() {
        return $this->ISANAI;
    }

    function getIEPSAI() {
        return $this->IEPSAI;
    }

    function getCCAI() {
        return $this->CCAI;
    }

    function getP_DTAI() {
        return $this->P_DTAI;
    }

    function getDTAI() {
        return $this->DTAI;
    }

    function getIVAI() {
        return $this->IVAI;
    }

    function getCONSI() {
        return $this->CONSI;
    }

    function getEXP() {
        return $this->EXP;
    }

    function getADVAE() {
        return $this->ADVAE;
    }

    function getDTAAE() {
        return $this->DTAAE;
    }

    function getIVAAE() {
        return $this->IVAAE;
    }

    function getISANAE() {
        return $this->ISANAE;
    }

    function getIEPSAE() {
        return $this->IEPSAE;
    }

    function getCCAE() {
        return $this->CCAE;
    }

    function getP_DTAE() {
        return $this->P_DTAE;
    }

    function getDTAE() {
        return $this->DTAE;
    }

    function getIVAE() {
        return $this->IVAE;
    }

    function getCONSE() {
        return $this->CONSE;
    }

    function getCANDADOS() {
        return $this->CANDADOS;
    }

    function getTRANSITO() {
        return $this->TRANSITO;
    }

    function getPREVIOS() {
        return $this->PREVIOS;
    }

    function getEXTRACCION() {
        return $this->EXTRACCION;
    }

    function getSECTORIAL() {
        return $this->SECTORIAL;
    }

    function getREGIMENI() {
        return $this->REGIMENI;
    }

    function getREGIMENE() {
        return $this->REGIMENE;
    }

    function getACTUADTA() {
        return $this->ACTUADTA;
    }

    function getACTUAIVA() {
        return $this->ACTUAIVA;
    }

    function getACTUACCMT() {
        return $this->ACTUACCMT;
    }

    function getACTUAISIE() {
        return $this->ACTUAISIE;
    }

    function getIVA0IMP() {
        return $this->IVA0IMP;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setClave($clave) {
        $this->clave = $clave;
    }

    function setDescripcion($descripcion) {
        $this->descripcion = $descripcion;
    }

    function setIMP($IMP) {
        $this->IMP = $IMP;
    }

    function setADVAI($ADVAI) {
        $this->ADVAI = $ADVAI;
    }

    function setDTAAI($DTAAI) {
        $this->DTAAI = $DTAAI;
    }

    function setIVAAI($IVAAI) {
        $this->IVAAI = $IVAAI;
    }

    function setISANAI($ISANAI) {
        $this->ISANAI = $ISANAI;
    }

    function setIEPSAI($IEPSAI) {
        $this->IEPSAI = $IEPSAI;
    }

    function setCCAI($CCAI) {
        $this->CCAI = $CCAI;
    }

    function setP_DTAI($P_DTAI) {
        $this->P_DTAI = $P_DTAI;
    }

    function setDTAI($DTAI) {
        $this->DTAI = $DTAI;
    }

    function setIVAI($IVAI) {
        $this->IVAI = $IVAI;
    }

    function setCONSI($CONSI) {
        $this->CONSI = $CONSI;
    }

    function setEXP($EXP) {
        $this->EXP = $EXP;
    }

    function setADVAE($ADVAE) {
        $this->ADVAE = $ADVAE;
    }

    function setDTAAE($DTAAE) {
        $this->DTAAE = $DTAAE;
    }

    function setIVAAE($IVAAE) {
        $this->IVAAE = $IVAAE;
    }

    function setISANAE($ISANAE) {
        $this->ISANAE = $ISANAE;
    }

    function setIEPSAE($IEPSAE) {
        $this->IEPSAE = $IEPSAE;
    }

    function setCCAE($CCAE) {
        $this->CCAE = $CCAE;
    }

    function setP_DTAE($P_DTAE) {
        $this->P_DTAE = $P_DTAE;
    }

    function setDTAE($DTAE) {
        $this->DTAE = $DTAE;
    }

    function setIVAE($IVAE) {
        $this->IVAE = $IVAE;
    }

    function setCONSE($CONSE) {
        $this->CONSE = $CONSE;
    }

    function setCANDADOS($CANDADOS) {
        $this->CANDADOS = $CANDADOS;
    }

    function setTRANSITO($TRANSITO) {
        $this->TRANSITO = $TRANSITO;
    }

    function setPREVIOS($PREVIOS) {
        $this->PREVIOS = $PREVIOS;
    }

    function setEXTRACCION($EXTRACCION) {
        $this->EXTRACCION = $EXTRACCION;
    }

    function setSECTORIAL($SECTORIAL) {
        $this->SECTORIAL = $SECTORIAL;
    }

    function setREGIMENI($REGIMENI) {
        $this->REGIMENI = $REGIMENI;
    }

    function setREGIMENE($REGIMENE) {
        $this->REGIMENE = $REGIMENE;
    }

    function setACTUADTA($ACTUADTA) {
        $this->ACTUADTA = $ACTUADTA;
    }

    function setACTUAIVA($ACTUAIVA) {
        $this->ACTUAIVA = $ACTUAIVA;
    }

    function setACTUACCMT($ACTUACCMT) {
        $this->ACTUACCMT = $ACTUACCMT;
    }

    function setACTUAISIE($ACTUAISIE) {
        $this->ACTUAISIE = $ACTUAISIE;
    }

    function setIVA0IMP($IVA0IMP) {
        $this->IVA0IMP = $IVA0IMP;
    }

}
