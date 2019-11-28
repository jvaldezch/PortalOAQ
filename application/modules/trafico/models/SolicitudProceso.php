<?php

class Trafico_Model_SolicitudProceso {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Trafico_Model_DbTable_SolicitudProceso();
    }

    /**
     * 
     * @return type
     * @throws Exception
     */
    public function obtenerTodos() {
        try {
            $sql = $this->_db_table->select()
                    ->order("descripcion ASC");
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
    public function multiOptions($admin = null) {
        try {
            $sql = $this->_db_table->select();
            if(isset($admin)) {
                $sql->where("id NOT IN (4, 10)");
            } else {
                $sql->where("id NOT IN (5, 6)");                
            }
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
