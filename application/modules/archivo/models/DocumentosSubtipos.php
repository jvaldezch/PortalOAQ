<?php

class Archivo_Model_DocumentosSubtipos {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Archivo_Model_DbTable_DocumentosSubtipos();
    }

    public function verificar($idDocumento) {
        try {
            $stmt = $this->_db_table->fetchAll(
                    $this->_db_table->select()
                            ->from($this->_db_table, array("subTipo AS value", "nombre AS text"))
                            ->where('idDocumento = ?', $idDocumento)
                            ->order("nombre ASC")
            );
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

}
