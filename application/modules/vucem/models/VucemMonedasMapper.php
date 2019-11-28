<?php

class Vucem_Model_VucemMonedasMapper {

    protected $_db_table;

    function __construct() {
        $this->_db_table = new Vucem_Model_DbTable_VucemMonedas();
    }

    /**
     * 
     * @return type
     * @throws Exception
     */
    public function getAllCurrencies() {
        try {
            $sql = $this->_db_table->select()
                    ->order('codigo ASC');
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt;
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("Db Exception found on" . __METHOD__, $ex);
        }
    }

    /**
     * 
     * @param string $busqueda
     * @return type
     * @throws Exception
     */
    public function obtenerMonedas($busqueda = null) {
        try {
            $sql = $this->_db_table->select()
                    ->order("codigo ASC");
            if (isset($busqueda)) {
                $sql->where("(codigo LIKE '%{$busqueda}%' OR moneda LIKE '%{$busqueda}%')");
            }
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
