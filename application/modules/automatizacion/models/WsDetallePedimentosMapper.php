<?php

class Automatizacion_Model_WsDetallePedimentosMapper {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Automatizacion_Model_DbTable_WsDetallePedimentos();
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
            'rfcCliente' => $data["RFCCliente"],            
            'nomCliente' => $data["NomCliente"],
            'transporteEntrada' => $data["TransporteEntrada"],
            'transporteArribo' => $data["TransporteArribo"],
            'transporteSalida' => $data["TransporteSalida"],
            'fechaEntrada' => date('Y-m-d H:i:s', strtotime($data["FechaEntrada"])),
            'fechaPago' => date('Y-m-d H:i:s', strtotime($data["FechaPago"])),
            'firmaValidacion' => $data["FirmaValidacion"],
            'firmaBanco' => $data["FirmaBanco"],
            'tipoCambio' => $data["TipoCambio"],
            'cvePed' => $data["CvePed"],
            'regimen' => $data["Regimen"],
            'consolidado' => $data["Consolidado"],
            'aduanaEntrada' => $data["AduanaEntrada"] . $data["SeccionEntrada"],
            'rectificacion' => $data["Rectificacion"],
            'valorDolares' => $data["ValorDolares"],
            'valorAduana' => $data["ValorAduana"],
            'fletes' => $data["Fletes"],
            'seguros' => $data["Seguros"],
            'embalajes' => $data["Embalajes"],
            'otrosIncrementales' => $data["OtrosIncrementales"],
            'dta' => $data["DTA"],
            'iva' => $data["IVA"],
            'igi' => $data["IGI"],
            'prev' => $data["PREV"],
            'cnt' => $data["CNT"],
            'totalEfectivo' => $data["TotalEfectivo"],
            'pesoBruto' => $data["PesoBruto"],
            'bultos' => $data["Bultos"],
            'usuarioAlta' => isset($data["UsuarioAlta"]) ? $data["UsuarioAlta"] : null,
            'usuarioModif' => isset($data["UsuarioModif"]) ? $data["UsuarioModif"] : null,
            'guias' => isset($data["Guias"]) ? $data["Guias"] : null,
            'bl' => isset($data["Bl"]) ? $data["Bl"] : null,
            'talon' => isset($data["Talon"]) ? $data["Talon"] : null,
            'candados' => isset($data["Candados"]) ? $data["Candados"] : null,
            'contenedores' => isset($data["Contenedores"]) ? $data["Contenedores"] : null,
            'observaciones' => isset($data["Observaciones"]) ? $data["Observaciones"] : null,
            'creado' => date('Y-m-d H:i:s'),
        );
        $added = $this->_db_table->insert($data);
        if ($added) {
            return true;
        }
        return false;
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
    
    public function obtenerDetalle($patente, $aduana, $pedimento) {
        $select = $this->_db_table->select()
                ->where('patente = ?',$patente)
                ->where('aduana = ?',$aduana)
                ->where('pedimento = ?',$pedimento);
        $result = $this->_db_table->fetchRow($select, array());
        if ($result) {
            return $result->toArray();
        }
        return null;
    }
    
    public function wsVerificarDetallePedimento($idPedimento) {
        try {
            $sql = $this->_db_table->select()
                    ->where('idPedimento = ?', $idPedimento);
            $stmt = $this->_db_table->fetchRow($sql, array());
            if ($stmt) {
                return true;
            }
            return;            
        } catch (Exception $ex) {
            throw new Exception("Zend DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }
    
    public function wsAgregarDetallePedimento($idPedimento, $row) {
        try {
            $arr = array(
                'idPedimento' => $idPedimento,
                'operacion' => $row["operacion"],
                'tipoOperacion' => $row["tipoOperacion"],
                'patente' => $row["patente"],
                'aduana' => $row["aduana"],
                'pedimento' => $row["pedimento"],
                'referencia' => $row["referencia"],
                'rfcCliente' => $row["rfcCliente"],            
                'nomCliente' => $row["nomCliente"],
                'transporteEntrada' => $row["transporteEntrada"],
                'transporteArribo' => $row["transporteArribo"],
                'transporteSalida' => $row["transporteSalida"],
                'fechaEntrada' => date('Y-m-d H:i:s', strtotime($row["fechaEntrada"])),
                'fechaPago' => date('Y-m-d H:i:s', strtotime($row["fechaPago"])),
                'firmaValidacion' => $row["firmaValidacion"],
                'firmaBanco' => $row["firmaBanco"],
                'tipoCambio' => $row["tipoCambio"],
                'cvePed' => $row["cvePed"],
                'regimen' => $row["regimen"],
                'consolidado' => isset($row["consolidado"]) ? $row["consolidado"] : null,
                'aduanaEntrada' => $row["aduanaEntrada"],
                'rectificacion' => isset($row["rectificacion"]) ? $row["rectificacion"] : null,
                'valorDolares' => $row["valorDolares"],
                'valorAduana' => $row["valorAduana"],
                'fletes' => $row["fletes"],
                'seguros' => $row["seguros"],
                'embalajes' => $row["embalajes"],
                'otrosIncrementales' => $row["otrosIncrementales"],
                'dta' => $row["dta"],
                'iva' => $row["iva"],
                'igi' => $row["igi"],
                'prev' => $row["prev"],
                'cnt' => $row["cnt"],
                'totalEfectivo' => $row["totalEfectivo"],
                'pesoBruto' => $row["pesoBruto"],
                'bultos' => isset($row["bultos"]) ? $row["bultos"] : null,
                'guias' => isset($row["guias"]) ? $row["guias"] : null,
                'bl' => isset($row["bl"]) ? $row["bl"] : null,
                'talon' => isset($row["talon"]) ? $row["talon"] : null,
                'candados' => isset($row["candados"]) ? $row["candados"] : null,
                'contenedores' => isset($row["contenedores"]) ? $row["contenedores"] : null,
                'observaciones' => isset($row["observaciones"]) ? $row["observaciones"] : null,
                'creado' => date('Y-m-d H:i:s'),
//                'usuarioAlta' => isset($row["UsuarioAlta"]) ? $row["UsuarioAlta"] : null,
//                'usuarioModif' => isset($row["UsuarioModif"]) ? $row["UsuarioModif"] : null,
            );
            $added = $this->_db_table->insert($arr);
            if ($added) {
                return true;
            }
            return false;           
        } catch (Exception $ex) {
            throw new Exception("Zend DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

}
