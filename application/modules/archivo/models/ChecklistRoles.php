<?php

class Archivo_Model_ChecklistRoles {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Archivo_Model_DbTable_ChecklistRoles();
    }

    public function rolChecklist($role) {
        try {
            $this->__construct();
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("tipo"))
                    ->where("rol = ?", $role);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->toArray()["tipo"];
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("Zend DB Exception found on " . __METHOD__ . " > " . $e->getMessage());
        }
    }
    
}
