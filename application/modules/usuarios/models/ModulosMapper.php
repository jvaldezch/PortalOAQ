<?php

class Usuarios_Model_ModulosMapper {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Usuarios_Model_DbTable_Modulos();
    }

    public function getModules() {
        try {
            $sql = $this->_db_table->select()
                    ->order("id ASC");
            $stmt = $this->_db_table->fetchAll($sql, array());
            if ($stmt) {
                $data = array();
                foreach ($stmt as $mod) {
                    $data[] = array(
                        "id" => $mod["id"],
                        "modulo" => $mod["modulo"],
                        "nombre" => $mod["nombre"],
                    );
                }
                return $data;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function getModuleName($id) {
        try {
            $sql = $this->_db_table->select()
                    ->where("id = ?", $id);
            $stmt = $this->_db_table->fetchRow($sql, array());
            if ($stmt) {
                return $stmt["modulo"];
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

}
