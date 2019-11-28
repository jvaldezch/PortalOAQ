<?php

class Automatizacion_Model_WsAnexoPedimentosMapper {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Automatizacion_Model_DbTable_WsAnexoPedimentos();
    }

    /**
     * 
     * @param String $patente
     * @param String $aduana
     * @param String $pedimento
     * @param String $referencia
     * @return boolean
     */
    public function verificar($patente, $aduana, $pedimento, $referencia) {
        $select = $this->_db_table->select()
                ->where('patente = ?', $patente)
                ->where('aduana = ?', $aduana)
                ->where('pedimento = ?', $pedimento)
                ->where('referencia = ?', $referencia);
        $result = $this->_db_table->fetchRow($select, array());
        if ($result) {
            return true;
        }
        return false;
    }

    /**
     * 
     * @param string $operacion
     * @param string $tipoOperacion
     * @param int $patente
     * @param int $aduana
     * @param int $pedimento
     * @param string $referencia
     * @param array $data
     * @return boolean|null
     */
    public function agregar($operacion, $tipoOperacion, $patente, $aduana, $pedimento, $referencia, $data) {
        $data = array(
            'operacion' => $operacion,
            'tipoOperacion' => $tipoOperacion,
            'patente' => $patente,
            'aduana' => $aduana,
            'pedimento' => $pedimento,
            'referencia' => $referencia,
            'numFactura' => $this->value(array('numFactura', 'NumFactura'), $data),
            'cove' => $this->value(array('cove', 'Cove'), $data),
            'ordenFactura' => $this->value(array('ordenFactura', 'OrdenFactura'), $data),
            'fechaFactura' => $this->value(array('fechaFactura', 'FechaFactura'), $data, true),
            'incoterm' => $this->value(array('incoterm', 'Incoterm'), $data),
            'valorFacturaUsd' => $this->value(array('valorFacturaUsd', 'ValorFacturaUsd'), $data),
            'valorFacturaMonExt' => $this->value(array('valorFacturaMonExt', 'ValorFacturaMonExt'), $data),
            'cveProveedor' => $this->value(array('cveProveedor', 'CveProveedor'), $data),
            'nomProveedor' => $this->value(array('nomProveedor', 'NomProveedor'), $data),
            'paisFactura' => $this->value(array('paisFactura', 'PaisFactura'), $data),
            'factorMonExt' => $this->value(array('factorMonExt', 'FactorMonExt'), $data),
            'divisa' => $this->value(array('divisa', 'Divisa'), $data),
            'numParte' => $this->value(array('taxId', 'TaxId'), $data),
            'descripcion' => $this->value(array('descripcion', 'Descripcion'), $data),
            'fraccion' => $this->value(array('fraccion', 'Fraccion'), $data),
            'ordenFraccion' => $this->value(array('ordenFraccion', 'OrdenFraccion'), $data),
            'ordenAgrupacion' => $this->value(array('ordenAgrupacion', 'OrdenAgrupacion'), $data),
            'valorMonExt' => $this->value(array('valorMonExt', 'ValorMonExt'), $data),
            'valorAduanaMXN' => $this->value(array('valorAduanaMXN'), $data),
            'cantUMC' => $this->value(array('cantUMC', 'CantUMC'), $data),
            'abrevUMC' => $this->value(array('abrevUMC'), $data),
            'cantUMT' => $this->value(array('cantUMT', 'CantUMT'), $data),
            'umc' => $this->value(array('umc', 'UMC'), $data),
            'umt' => $this->value(array('umt', 'UMT'), $data),
            'abrevUMT' => $this->value(array('abrevUMT'), $data),
            'cantOMA' => $this->value(array('cantOMA'), $data),
            'oma' => $this->value(array('oma'), $data),
            'umc' => $this->value(array('umc', 'UMC'), $data),
            'paisOrigen' => $this->value(array('paisOrigen', 'PaisOrigen'), $data),
            'paisVendedor' => $this->value(array('paisVendedor', 'PaisVendedor'), $data),
            'tasaAdvalorem' => $this->value(array('tasaAdvalorem', 'TasaAdvalorem'), $data),
            'formaPagoAdvalorem' => $this->value(array('formaPagoAdvalorem'), $data),
            'umc' => $this->value(array('umc', 'UMC'), $data),
            'iva' => $this->value(array('iva', 'IVA'), $data),
            'ieps' => $this->value(array('ieps', 'IEPS'), $data),
            'isan' => $this->value(array('isan', 'ISAN'), $data),
            'tlc' => $this->value(array('tlc', 'TLC'), $data),
            'tlcan' => $this->value(array('tlcan', 'TLCAN'), $data),
            'tlcue' => $this->value(array('tlcue', 'TLCUE'), $data),
            'prosec' => $this->value(array('prosec', 'PROSEC'), $data),
            'observacion' => $this->value(array('observacion', 'Observacion'), $data),
            'patenteOrig' => $this->value(array('patenteOrig', 'PatenteOriginal'), $data),
            'aduanaOrig' => $this->value(array('aduanaOrig', 'AduanaOriginal'), $data),
            'pedimentoOrig' => $this->value(array('pedimentoOrig', 'PedimentoOriginal'), $data),
//            'ordenFactura' => isset($data["ordenFactura"]) ? $data["ordenFactura"] : null,
//            'fechaFactura' => isset($data["fechaFactura"]) ? date('Y-m-d H:i:s',  strtotime($data["fechaFactura"])) : null,
//            'incoterm' => isset($data["incoterm"]) ? $data["incoterm"] : null,
//            'valorFacturaUsd' => isset($data["valorFacturaUsd"]) ? $data["valorFacturaUsd"] : null,
//            'valorFacturaMonExt' => isset($data["valorFacturaMonExt"]) ? $data["valorFacturaMonExt"] : null,
//            'cveProveedor' => isset($data["cveProveedor"]) ? $data["cveProveedor"] : null,
//            'nomProveedor' => isset($data["nomProveedor"]) ? $data["nomProveedor"] : null,
//            'paisFactura' => isset($data["paisFactura"]) ? $data["paisFactura"] : null,
//            'taxId' => isset($data["taxId"]) ? $data["taxId"] : null,
//            'divisa' => isset($data["divisa"]) ? $data["divisa"] : null,
//            'factorMonExt' => isset($data["factorMonExt"]) ? $data["factorMonExt"] : null,
//            'numParte' => isset($data["numParte"]) ? $data["numParte"] : null,
//            'descripcion' => isset($data["descripcion"]) ? $data["descripcion"] : null,
//            'fraccion' => isset($data["fraccion"]) ? $data["fraccion"] : null,
//            'ordenFraccion' => isset($data["ordenFraccion"]) ? $data["ordenFraccion"] : null,
//            'ordenAgrupacion' => isset($data["ordenAgrupacion"]) ? $data["ordenAgrupacion"] : null,
//            'valorMonExt' => isset($data["valorMonExt"]) ? $data["valorMonExt"] : null,
//            'valorAduanaMXN' => isset($data["valorAduanaMXN"]) ? $data["valorAduanaMXN"] : null,
//            'cantUMC' => isset($data["cantUMC"]) ? $data["cantUMC"] : null,
//            'umc' => isset($data["umc"]) ? $data["umc"] : null,
//            'abrevUMC' => isset($data["abrevUMC"]) ? $data["abrevUMC"] : null,
//            'cantUMT' => isset($data["cantUMT"]) ? $data["cantUMT"] : null,
//            'umt' => isset($data["umt"]) ? $data["umt"] : null,
//            'abrevUMT' => isset($data["abrevUMT"]) ? $data["abrevUMT"] : null,            
//            'cantOMA' => isset($data["cantOMA"]) ? $data["cantOMA"] : null,
//            'oma' => isset($data["oma"]) ? $data["oma"] : null,
//            'paisOrigen' => isset($data["paisOrigen"]) ? $data["paisOrigen"] : null,
//            'paisVendedor' => isset($data["paisVendedor"]) ? $data["paisVendedor"] : null,
//            'tasaAdvalorem' => isset($data["tasaAdvalorem"]) ? $data["tasaAdvalorem"] : null,
//            'formaPagoAdvalorem' => isset($data["formaPagoAdvalorem"]) ? $data["formaPagoAdvalorem"] : null,
//            'iva' => isset($data["iva"]) ? $data["iva"] : null,
//            'ieps' => isset($data["ieps"]) ? $data["ieps"] : null,
//            'isan' => isset($data["isan"]) ? $data["isan"] : null,
//            'tlc' => isset($data["tlc"]) ? $data["tlc"] : null,
//            'tlcan' => isset($data["tlcan"]) ? $data["tlcan"] : null,
//            'tlcue' => isset($data["tlcue"]) ? $data["tlcue"] : null,
//            'prosec' => isset($data["prosec"]) ? $data["prosec"] : null,
//            'observacion' => isset($data["observacion"]) ? $data["observacion"] : null,
//            'patenteOrig' => isset($data["patenteOrig"]) ? $data["patenteOrig"] : null,
//            'aduanaOrig' => isset($data["aduanaOrig"]) ? $data["aduanaOrig"] : null,
//            'pedimentoOrig' => isset($data["pedimentoOrig"]) ? $data["pedimentoOrig"] : null,
            'creado' => date('Y-m-d H:i:s'),
        );
        $added = $this->_db_table->insert($data);
        if ($added) {
            return true;
        }
        return false;
    }

    /**
     * 
     * @param String $patente
     * @param String $aduana
     * @param String $pedimento
     * @param String $referencia
     * @return boolean
     */
    public function contar($patente, $aduana, $pedimento, $referencia) {
        $select = $this->_db_table->select()
                ->from(array('a' => 'ws_anexo_pedimentos'), array('count(numFactura) as conteo'))
                ->where('patente = ?', $patente)
                ->where('aduana = ?', $aduana)
                ->where('pedimento = ?', $pedimento)
                ->where('referencia = ?', $referencia);
        $result = $this->_db_table->fetchRow($select, array());
        if ($result) {
            return true;
        }
        return false;
    }

    protected function value($values, $array, $date = null) {
        if (is_array($values)) {
            foreach ($values as $value) {
                if (isset($array[$value])) {
                    if (isset($date) && $date === true) {
                        return date('Y-m-d H:i:s', strtotime($array[$value]));
                    }
                    return $array[$value];
                }
            }
            return null;
        }
    }

    public function obtenerTodo() {
        $select = $this->_db_table->select()
                ->where('cove IS NOT NULL');
        $result = $this->_db_table->fetchAll($select, array());
        if ($result) {
            return $result->toArray();
        }
        return null;
    }

    public function obtenerAnexo($patente, $aduana, $pedimento) {
        $select = $this->_db_table->select()
                ->from(array('a' => 'ws_anexo_pedimentos'))
                ->where('patente = ?', $patente)
                ->where('aduana = ?', $aduana)
                ->where('pedimento = ?', $pedimento);
        $result = $this->_db_table->fetchAll($select, array());
        if ($result) {
            return $result->toArray();
        }
        return null;
    }

}
