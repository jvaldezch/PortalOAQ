<?php

class Usuarios_Model_DepartamentosMapper {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Usuarios_Model_DbTable_Departamentos();
    }

    public function getDeptos() {
        try {
            $sql = $this->_db_table->select()
                    ->order("nombre ASC");
            $stmt = $this->_db_table->fetchAll($sql, array());
            if ($stmt) {
                return $stmt->toArray();
            }
            return NULL;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

}
