<?php

class Application_Model_ConfigMapper {

    protected $_db;

    public function __construct() {
        $this->_db = Zend_Registry::get("oaqintranet");
    }

    public function getParam($param) {
        try {
            $sql = $this->_db->select()
                    ->from("config", array("value"))
                    ->where("param = ?", $param);
            $stmt = $this->_db->fetchRow($sql, array());
            if ($stmt) {
                return $stmt["value"];
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

}
