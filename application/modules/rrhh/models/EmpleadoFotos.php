<?php

class Rrhh_Model_EmpleadoFotos {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Rrhh_Model_DbTable_EmpleadoFotos();
    }

    /**
     * 
     * @param array $arr
     * @throws Exception
     */
    public function agregar($arr) {
        try {
            $stmt = $this->_db_table->insert($arr);
            if ($stmt) {
                return $stmt;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    /**
     * 
     * @param int $idEmpleado
     * @return type
     * @throws Exception
     */
    public function obtener($idEmpleado) {
        try {
            $sql = $this->_db_table->select()
                    ->where("idEmpleado = ?", $idEmpleado)
                    ->order("creado DESC")
                    ->limit(1);
            $stmt = $this->_db_table->fetchRow($sql);
            if (count($stmt)) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

}
