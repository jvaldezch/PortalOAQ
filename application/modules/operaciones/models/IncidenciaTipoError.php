<?php

class Operaciones_Model_IncidenciaTipoError
{

    protected $_db_table;

    public function __construct()
    {
        $this->_db_table = new Operaciones_Model_DbTable_IncidenciaTipoError();
    }

    public function obtener()
    {
        try {
            $sql = $this->_db_table->select()
                ->from($this->_db_table, array('id', 'tipoError'))
                ->order("tipoError");
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
