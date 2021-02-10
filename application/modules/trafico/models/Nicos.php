<?php

class Trafico_Model_Nicos
{

    protected $_db_table;

    public function __construct()
    {
        $this->_db_table = new Trafico_Model_DbTable_Nicos();
    }

    public function buscar($fraccion)
    {
        try {
            $sql = $this->_db_table->select()
                ->from($this->_db_table, array('tigie_2020', 'nico'))
                ->where("tigie_2012 = ?", $fraccion);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . " > " . $e->getMessage());
        }
    }

    public function todos($busqueda = null)
    {
        try {
            $sql = $this->_db_table->select()
                ->order("tigie_2012 ASC");
            if (isset($busqueda)) {
                $sql->where("(tigie_2012 LIKE '%{$busqueda}%' OR tigie_2020 LIKE '%{$busqueda}%')");
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
