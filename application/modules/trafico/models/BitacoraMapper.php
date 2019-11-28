<?php

class Trafico_Model_BitacoraMapper {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Trafico_Model_DbTable_Bitacora();
    }

    public function agregar($data) {
        try {
            $stmt = $this->_db_table->insert($data);
            if ($stmt) {
                return true;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function obtenerPorRango($fechaInicio, $fechaFin, $patente = null, $aduana = null) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("*"))
                    ->where("creado >= ?", date("Y-m-d H:i:s", strtotime($fechaInicio)))
                    ->where("creado <= ?", date("Y-m-d H:i:s", strtotime($fechaFin)));
            if(isset($patente)) {
                $sql->where("patente = ?", $patente)
                        ->where("aduana LIKE ?", substr($aduana, 0, 2) . '%');
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
    
    public function obtener($patente, $aduana, $pedimento, $referencia) {
        try {
            $sql = $this->_db_table->select()
                    ->where("patente = ?", $patente)
                    ->where("aduana LIKE ?", substr($aduana, 0, 2) . '%')
                    ->where("pedimento = ?", $pedimento)
                    ->where("referencia = ?", $referencia)
                    ->where("tipo IS NULL");
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function obtenerPorTipo($patente, $aduana, $pedimento, $referencia, $tipo) {
        try {
            $sql = $this->_db_table->select()
                    ->where("patente = ?", $patente)
                    ->where("aduana LIKE ?", substr($aduana, 0, 2) . '%')
                    ->where("pedimento = ?", $pedimento)
                    ->where("referencia = ?", $referencia)
                    ->where("tipo = ?", $tipo);
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

}
