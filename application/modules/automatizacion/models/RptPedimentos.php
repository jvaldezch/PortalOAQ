<?php

class Automatizacion_Model_RptPedimentos {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Automatizacion_Model_DbTable_RptPedimentos();
    }

    public function verificarPedimento($idAduana, $patente, $aduana, $pedimento, $referencia) {
        try {
            $sql = $this->_db_table->select()
                    ->where('idAduana = ?', $idAduana)
                    ->where('patente = ?', $patente)
                    ->where('aduana = ?', $aduana)
                    ->where('pedimento = ?', $pedimento)
                    ->where('referencia = ?', $referencia);
            $stmt = $this->_db_table->fetchRow($sql, array());
            if ($stmt) {
                return true;
            }
            return;
        } catch (Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function agregarPedimento($idAduana, $row) {
        try {
            $arr = array(
                'idAduana' => $idAduana,
                'operacion' => $row["operacion"],
                'tipoOperacion' => $row["tipoOperacion"],
                'patente' => $row["patente"],
                'aduana' => $row["aduana"],
                'pedimento' => $row["pedimento"],
                'referencia' => $row["referencia"],
                'clavePedimento' => $row["clavePedimento"],
                'fechaPago' => date('Y-m-d H:i:s', strtotime($row["fechaPago"])),
                'rfcCliente' => $row["rfcCliente"],
                'rfcSociedad' => isset($row["rfcSociedad"]) ? $row["rfcSociedad"] : null,
                'creado' => date('Y-m-d H:i:s'),
            );
            $stmt = $this->_db_table->insert($arr);
            if ($stmt) {
                return $stmt;
            }
            return;
        } catch (Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function wsSinDetalle($idAduana = null, $limit = null) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array('*'))
                    ->where('detalle IS NULL')
                    ->where('error IS NULL');
            if (isset($limit)) {
                $sql->limit($limit);
            }
            if (isset($idAduana)) {
                $sql->where("idAduana = ?", $idAduana);
            }
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function wsSinAnexo($idAduana = null, $idPedimento = null, $limit = null) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array('*'))
                    ->where('anexo IS NULL AND error IS NULL')
                    ->order('fechaPago ASC');
            if (isset($idAduana)) {
                $sql->where("idAduana = ?", $idAduana);
            }
            if (isset($idPedimento)) {
                $sql->where("id = ?", $idPedimento);
            }
            if (isset($limit)) {
                $sql->limit($limit);
            }
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function actualizar($id, $arr) {
        try {
            $stmt = $this->_db_table->update($arr, array("id = ?" => $id));
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }
    
    public function reporteEncabezado($patente, $aduana, $rfcCliente, $fechaIni, $fechaFin) {
        try {            
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array('p' => 'rpt_pedimentos'), array('operacion', 'tipoOperacion', 'patente', 'aduana', 'pedimento', 'referencia AS trafico', 'fechaPago', 'clavePedimento AS cvePed'))
                    ->joinLeft(array('d' => 'rpt_pedimento_detalle'), 'p.id = d.idPedimento', array('*'))
                    ->joinLeft(array('t' => 'traficos'), 'p.patente = t.patente AND p.aduana = t.aduana AND p.pedimento = t.pedimento ', array(''))
                    ->joinLeft(array("s" => "trafico_clientes_plantas"), "s.id = t.idPlanta", array("descripcion AS planta"))
                    ->where('p.fechaPago >= ?', date('Y-m-d H:i:s', strtotime($fechaIni)))
                    ->where('p.fechaPago <= ?', date('Y-m-d', strtotime($fechaFin)) . ' 23:59:59')
                    ->where('p.patente = ?', $patente)
                    ->where('p.aduana = ?', $aduana)
                    ->where('p.rfcCliente = ?', $rfcCliente);
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }
    
    public function reporteAnexo($patente, $aduana, $rfcCliente, $fechaIni, $fechaFin) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array('p' => 'rpt_pedimentos'), array('operacion', 'tipoOperacion', 'patente', 'aduana', 'pedimento', 'referencia AS trafico', 'fechaPago', 'clavePedimento AS cvePed'))
                    ->joinLeft(array('d' => 'rpt_pedimento_detalle'), 'p.id = d.idPedimento', array('*', 'iva AS ivaPedimento'))
                    ->joinLeft(array('a' => 'rpt_pedimento_desglose'), 'p.id = a.idPedimento', array('*', 'iva AS ivaParte'))
                    ->joinLeft(array('t' => 'traficos'), 'p.patente = t.patente AND p.aduana = t.aduana AND p.pedimento = t.pedimento ', array(''))
                    ->joinLeft(array("s" => "trafico_clientes_plantas"), "s.id = t.idPlanta", array("descripcion AS planta"))
                    ->where('p.fechaPago >= ?', date('Y-m-d H:i:s', strtotime($fechaIni)))
                    ->where('p.fechaPago <= ?', date('Y-m-d', strtotime($fechaFin)) . ' 23:59:59')
                    ->where('p.patente = ?', $patente)
                    ->where('p.aduana = ?', $aduana)
                    ->where('p.rfcCliente = ?', $rfcCliente);
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }
    
    public function wsObtener($patente, $aduana, $rfcCliente, $fechaIni, $fechaFin) {
        try {            
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array('p' => 'rpt_pedimentos'), array('operacion', 'tipoOperacion', 'patente', 'aduana', 'pedimento', 'referencia AS trafico', 'fechaPago', 'clavePedimento AS cvePed'))
                    ->where('p.fechaPago >= ?', date('Y-m-d H:i:s', strtotime($fechaIni)))
                    ->where('p.fechaPago <= ?', date('Y-m-d', strtotime($fechaFin)) . ' 23:59:59')
                    ->where('p.rfcCliente = ?', $rfcCliente);
            if (isset($patente)) {
                $sql->where('p.patente = ?', $patente);
            }
            if (isset($aduana)) {
                $sql->where('p.aduana = ?', $aduana);
            }
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }
    
    public function wsObtenerPedimento($patente, $aduana, $pedimento) {
        try {            
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array('p' => 'rpt_pedimentos'), array('id AS idPedimento', 'operacion', 'tipoOperacion', 'patente', 'aduana', 'pedimento', 'referencia', 'fechaPago', 'clavePedimento AS cvePed'))
                    ->joinLeft(array('d' => 'rpt_pedimento_detalle'), 'p.id = d.idPedimento', array('*'))
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
