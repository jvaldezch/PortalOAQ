<?php

class Bodega_Model_Entradas {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Bodega_Model_DbTable_Entradas();
    }
    public function buscarEntrada($idBodega, $referencia) {
        try {
            $sql = $this->_db_table->select()
                    ->from("traficos", array("id", "estatus", "idCliente"))
                    ->where("idBodega = ?", $idBodega)
                    ->where("referencia = ?", $referencia)
                    ->where("estatus <> 4");
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

}
