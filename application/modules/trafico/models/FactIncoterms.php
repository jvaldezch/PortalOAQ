<?php

class Trafico_Model_FactIncoterms {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Trafico_Model_DbTable_FactIncoterms();
    }

    /**
     * 
     * @return type
     * @throws Exception
     */
    public function obtenerTodos() {
        try {
            $sql = $this->_db_table->select()
                    ->order('clave ASC');
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("Db Exception found on" . __METHOD__, $ex);
        }
    }

}
