<?php

class Clientes_Model_Clientes {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Clientes_Model_DbTable_Clientes();
    }

    public function obtenerId($rfcCliente) {
        try {
            $sql = $this->_db_table->select()
                    ->from(array("c" => "trafico_clientes"), array("id"))
                    ->where("c.rfc = ?", $rfcCliente);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                $data = $stmt->toArray();
                return $data["id"];
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

}
