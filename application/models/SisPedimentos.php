<?php

class Application_Model_SisPedimentos {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Application_Model_DbTable_SisPedimentos();
    }

    /**
     * 
     * @param int $patente
     * @param int $aduana
     * @return type
     * @throws Exception
     */
    public function sisPedimentos($patente, $aduana) {
        try {
            $sql = $this->_db_table->select()
                    ->where("env = 'prod'")
                    ->where("patente = ?", $patente)
                    ->where("aduana = ?", $aduana);
            $stmt = $this->_db_table->fetchRow($sql, array());
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("Zend DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

}
