<?php

class Manifestacion_Model_ManifestacionFormasPago
{
    protected $_db_table;

    public function __construct()
    {
        $this->_db_table = new Manifestacion_Model_DbTable_ManifestacionFormasPago();
    }

    public function todos()
    {
        try {
            $sql = $this->_db_table->select()
                ->from($this->_db_table, array("*"))
                ->order("descripcion ASC");
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Exception $e) {
            throw new Exception("Db Exception on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
}
