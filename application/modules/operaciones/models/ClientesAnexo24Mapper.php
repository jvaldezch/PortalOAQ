<?php

class Operaciones_Model_ClientesAnexo24Mapper {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Operaciones_Model_DbTable_ClientesAnexo24();
    }

    /**
     * 
     * @param array $rfcs
     * @return type
     * @throws Exception
     */
    public function todos($rfcs = null) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("rfc", "razonSocial"))
                    ->where("habilitado = 1")
                    ->order("razonSocial ASC");
            if(isset($rfcs) && is_array($rfcs)) {
                $sql->where("rfc IN (?)", $rfcs);
            }
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                $data = array(
                    "" => "-- Seleccionar --"
                );
                foreach ($stmt as $item) {
                    $data[$item["rfc"]] = $item["razonSocial"];
                }
                return $data;
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function todosAnexo($rfc = null) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("rfc"))
                    ->where("habilitado = 1")
                    ->order("rfc ASC");
            if (isset($rfc)) {
               $sql->where("rfc = ?", $rfc); 
            }
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                $data = array();
                foreach ($stmt as $item) {
                    $data[] = $item["rfc"];
                }
                return $data;
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

}
