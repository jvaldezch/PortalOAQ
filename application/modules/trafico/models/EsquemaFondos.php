<?php

class Trafico_Model_EsquemaFondos {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Trafico_Model_DbTable_EsquemaFondos();
    }

    /**
     * 
     * @param type $visual
     * @return type
     * @throws Exception
     */
    public function obtenerTodos($visual = null) {
        try {
            $sql = $this->_db_table->select()
                    ->order("descripcion ASC");
            if (isset($visual)) {
                $sql->where("id NOT IN (50, 99)");
            }
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    /**
     * 
     * @return type
     * @throws Exception
     */
    public function multiOptions() {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("s" => "trafico_esquemafondos", array("*")));
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                $arr = array("" => "---");
                foreach ($stmt->toArray() as $item) {
                    $arr[$item["id"]] = $item["descripcion"];
                }
                return $arr;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

}
