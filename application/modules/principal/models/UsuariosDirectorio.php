<?php

class Principal_Model_UsuariosDirectorio {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Principal_Model_DbTable_UsuariosDirectorio();
    }

    public function actualizar($id, $arr) {
        try {
            $stmt = $this->_db_table->update($arr, array("idUsuario = ?" => $id));
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

}
