<?php

class Trafico_Model_TraficoPrefijosMapper {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Trafico_Model_DbTable_TraficoPrefijos();
    }

    protected function _throwException($message, $method, Exception $ex) {
        return $message . " at " . $method . " >> " . $ex->getMessage() . " line: " . $ex->getLine() . " info: " . $ex->getCode() . " trace: " . $ex->getTrace();
    }

    public function prefijoAduana($patente, $aduana, $impExp) {
        try {
            $sql = $this->_db_table->select()
                    ->where('patente = ?', $patente)
                    ->where('aduana = ?', $aduana)
                    ->where('impExp = ?', $impExp);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return false;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception($this->_throwException("Db Exception found on", __METHOD__, $ex));
        }
    }

}
