<?php

class Clientes_Model_Traficos {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Clientes_Model_DbTable_Traficos();
    }
    
    public function obtenerTraficoCliente($rfcCliente, $fechaInicio = null, $fechaFin = null) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("t" => "traficos"), array("*"))
                    ->joinLeft(array("u" => "usuarios"), "t.idUsuario = u.id", array("nombre"))
                    ->where("t.estatus NOT IN (4)")
                    ->where("t.rfcCliente = ?", $rfcCliente);
            if (isset($fechaInicio) && isset($fechaFin)) {
                $sql->where("t.fechaLiberacion >= ?", date("Y-m-d H:i:s", strtotime($fechaInicio)))
                        ->where("t.fechaLiberacion <= ?", date("Y-m-d H:i:s", strtotime($fechaFin)));
            }
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    public function totalTraficoCliente($rfcCliente, $fechaInicio = null, $fechaFin = null) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("t" => "traficos"), array("count(*) AS total"))
                    ->joinLeft(array("u" => "usuarios"), "t.idUsuario = u.id", array(""))
                    ->where("t.estatus NOT IN (4)")
                    ->where("t.rfcCliente = ?", $rfcCliente);
            if (isset($fechaInicio) && isset($fechaFin)) {
                $sql->where("t.fechaLiberacion >= ?", date("Y-m-d H:i:s", strtotime($fechaInicio)))
                        ->where("t.fechaLiberacion <= ?", date("Y-m-d H:i:s", strtotime($fechaFin)));
            }
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->total;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
}
