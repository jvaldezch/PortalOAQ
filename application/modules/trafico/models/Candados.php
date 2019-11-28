<?php

class Trafico_Model_Candados {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Trafico_Model_DbTable_Candados();
    }

    public function obtener($idTrafico) {
        try {
            $sql = $this->_db_table->select()
                    ->where("idTrafico = ?", $idTrafico);
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function verificar($idTrafico, $numero) {
        try {
            $sql = $this->_db_table->select()
                    ->where("idTrafico = ?", $idTrafico)
                    ->where("numero = ?", $numero);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function agregar($idTrafico, $numero, $color) {
        try {
            $arr = array(
                "idTrafico" => $idTrafico,
                "numero" => $numero,
                "color" => $color,
                "creado" => date("Y-m-d H:i:s"),
            );
            $stmt = $this->_db_table->insert($arr);
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

}
