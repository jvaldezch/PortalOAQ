<?php

class Principal_Model_MenuIcono {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Principal_Model_DbTable_MenuIcono();
    }

    public function icono($link, $titulo) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("icono"))
                    ->where("menu = ?", $link);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return '<div class="btn-menu" data-url="' . $link . '"><img src="' . $stmt->icono . '" /><p>' . $titulo . '</p></div>';
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

}
