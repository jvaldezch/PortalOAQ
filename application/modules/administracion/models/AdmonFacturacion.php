<?php

class Administracion_Model_AdmonFacturacion {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Administracion_Model_DbTable_AdmonFacturacion();
    }

    public function verificar($arr) {
        try {
            $stmt = $this->_db_table->fetchRow(
                    $this->_db_table->select()
                            ->where("patente = ?", $arr["patente"])
                            ->where("aduana = ?", $arr["aduana"])
                            ->where("pedimento = ?", $arr["pedimento"])
                            ->where("referencia = ?", $arr["referencia"])
                            ->where("folio = ?", $arr["folio"])
            );
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function agregar($arr) {
        try {
            $stmt = $this->_db_table->insert($arr);
            if ($stmt) {
                return $stmt;
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

}
