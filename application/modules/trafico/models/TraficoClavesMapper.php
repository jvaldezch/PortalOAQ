<?php

class Trafico_Model_TraficoClavesMapper {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Trafico_Model_DbTable_TraficoClaves();
    }
    
    /**
     * 
     * @param int $patente
     * @param int $aduana
     * @return type
     * @throws Exception
     */
    public function obtenerClaves($patente, $aduana) {
        try {
            $sql = $this->_db_table->select()
                    ->from(array('c' => 'trafico_claves'), array('*'))
                    ->where('patente = ?', $patente)
                    ->where('aduana = ?', $aduana);
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                foreach ($stmt as $item) {
                    $data[$item["cvePedimento"]] = $item["cvePedimento"];
                }
                return $data;
            }
            return null;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("Zend DB Exception found on " . __METHOD__ . " >> " . $e->getMessage());
        }
    }

}
