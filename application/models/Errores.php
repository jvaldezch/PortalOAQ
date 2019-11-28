<?php

class Application_Model_Errores {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Application_Model_DbTable_Errores();
    }

    public function get($id) {
        try {
            $select = $this->_db_table->select()
                    ->where("id = ?", $id);
            $stmt = $this->_db_table->fetchRow($select);
            if ($stmt) {
                return $stmt->error;
            }
            return false;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

}
