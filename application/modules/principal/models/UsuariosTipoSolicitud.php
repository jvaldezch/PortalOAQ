<?php

class Principal_Model_UsuariosTipoSolicitud {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Principal_Model_DbTable_UsuariosTipoSolicitud();
    }

    public function obtener() {
        try {
            $sql = $this->_db_table->select()
                    ->where("activo = 1")
                    ->order("tipoSolicitud ASC");
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

}
