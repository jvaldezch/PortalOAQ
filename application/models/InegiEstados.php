<?php

/**
 * Description of UserEmails
 *
 * @author Jaime E. Valdez <jvaldezch@gmail.com>
 */
class Application_Model_InegiEstados {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Application_Model_DbTable_InegiEstados();
    }
    
    public function obtenerTodos() {
        try {
            $select = $this->_db_table->select()
                    ->order("nombre ASC");
            if (($result = $this->_db_table->fetchAll($select))) {
                return $result->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

}
