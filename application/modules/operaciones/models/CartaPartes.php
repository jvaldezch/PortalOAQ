<?php

class Operaciones_Model_CartaPartes {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Operaciones_Model_DbTable_CartaPartes();
    }
    
    public function cartaPartesSelect($id) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("p" => "carta_partes"), array("*"))
                    ->joinLeft(array("i" => "carta_instrucciones_partes"), "p.idDelivery = i.id", array("*"))
                    ->where("p.idCarta = ?", $id);
            return $sql;
        } catch (Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function verificar($idCarta, $idDelivery) {
        try {
            $sql = $this->_db_table->select()
                    ->where("idCarta = ?", $idCarta)
                    ->where("idDelivery = ?", $idDelivery);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function agregar($arr) {
        try {
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
