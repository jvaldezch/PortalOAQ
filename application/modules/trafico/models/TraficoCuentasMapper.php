<?php

class Trafico_Model_TraficoCuentasMapper {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Trafico_Model_DbTable_TraficoCuentas();
    }

    public function obtener($tipoConcepto) {
        try {
            $sql = $this->_db_table->select()
                    ->from(array('c' => 'trafico_cuentas'), array('*'))
                    ->where('c.idTipoConcepto = ?', $tipoConcepto)
                    ->order("c.concepto ASC");
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                foreach ($stmt as $item) {
                    $data[$item["cuenta"]] = $item["concepto"];
                }
                return $data;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("Zend DB Exception found on <strong>" . __METHOD__ . "</strong> >> " . $e->getMessage());
        }
    }

}
