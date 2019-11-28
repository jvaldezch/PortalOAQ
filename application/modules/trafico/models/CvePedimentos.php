<?php

class Trafico_Model_CvePedimentos {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Trafico_Model_DbTable_CvePedimentos();
    }

    /**
     * 
     * @return boolean
     * @throws Exception
     */
    public function obtener() {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("clave"))
                    ->order("clave ASC");
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    /**
     * 
     * @param string $busqueda
     * @return boolean
     * @throws Exception
     */
    public function obtenerClaves($busqueda = null) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("clave", "descripcion", "REGIMENI as regimenImportacion", "REGIMENE as regimenExportacion"))
                    ->order("clave ASC");
            if (isset($busqueda)) {
                $sql->where("(clave LIKE '%{$busqueda}%' OR descripcion LIKE '%{$busqueda}%')");
            }
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

}
