<?php

class Application_Model_MenuAccesos {

    protected $_db;

    public function __construct() {
        $this->_db = Zend_Registry::get("oaqintranet");
    }

    /**
     * 
     * @param int $id
     * @return boolean
     */
    public function obtenerPorRol($id) {
        try {
            $sql = $this->_db->select()
                    ->from("menu_accesos", array("*"))
                    ->where("idRol = ?", $id)
                    ->order(array("orden"));
            $stmt = $this->_db->fetchAll($sql, array());
            if ($stmt) {
                return $stmt;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $ex) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

}
