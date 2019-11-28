<?php

class Rrhh_Model_EmpleadosBancos {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Rrhh_Model_DbTable_EmpleadosBancos();
    }

    public function obtenerOpciones() {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("*"))
                    ->order("nombre ASC");
            $stmt = $this->_db_table->fetchAll($sql, array());
            if ($stmt) {
                $arr = [];
                foreach ($stmt->toArray() as $item) {
                    $arr[$item["id"]] = $item["nombre"];
                }
                return $arr;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

}
