<?php

class Automatizacion_Model_RptPedimentoDetalle {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Automatizacion_Model_DbTable_RptPedimentoDetalle();
    }

    public function verificarDetallePedimento($idPedimento) {
        try {
            $sql = $this->_db_table->select()
                    ->where('idPedimento = ?', $idPedimento);
            $stmt = $this->_db_table->fetchRow($sql, array());
            if ($stmt) {
                return true;
            }
            return;
        } catch (Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function agregarDetallePedimento($idPedimento, $row) {
        try {
            $arr["idPedimento"] = $idPedimento;
            $arr["fechaEntrada"] = date('Y-m-d H:i:s', strtotime($row["fechaEntrada"]));
            $values = array('rfcCliente', 'nomCliente', 'transporteEntrada', 'transporteArribo', 'transporteSalida', 'firmaValidacion',
                'firmaBanco', 'tipoCambio', 'regimen', 'consolidado', 'aduanaEntrada', 'rectificacion', 'valorDolares', 'valorAduana', 'valorComercial', 'fletes', 'seguros',
                'embalajes', 'otrosIncrementales', 'dta', 'iva', 'igi', 'prev', 'cnt', 'efectivo', 'otrosEfectivo', 'totalEfectivo', 'pesoBruto', 'bultos', 'guias', 'bl', 'talon', 'candados',
                'contenedores', 'observaciones');
            foreach ($values as $value) {
                $arr[$value] = $this->_check($row, $value);
            }
            $arr["creado"] = date('Y-m-d H:i:s');
            $stmt = $this->_db_table->insert($arr);
            if ($stmt) {
                return true;
            }
            return;
        } catch (Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }
    
    protected function _check(array $arr, $value) {
        if (isset($arr[$value]) && !is_array(isset($arr[$value]))) {
            return $arr[$value];
        }
        return NULL;
    }
    
    public function wsObtener($patente, $aduana, $pedimento) {
        try {            
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array('d' => 'rpt_pedimento_detalle'), array('*'))
                    ->joinLeft(array('p' => 'rpt_pedimentos'), 'p.id = d.idPedimento', array('*'))
                    ->where('p.patente = ?', $patente)
                    ->where('p.aduana = ?', $aduana)
                    ->where('p.pedimento = ?', $pedimento);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

}
