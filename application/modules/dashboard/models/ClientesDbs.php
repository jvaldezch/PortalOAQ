<?php

class Dashboard_Model_ClientesDbs {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Trafico_Model_DbTable_ClientesDbs();
    }

    public function buscarIdentificador($identificador) {
        try {
            $select = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("s" => "trafico_cliente_dbs"), array("idCliente"))
                    ->joinLeft(array("c" => "trafico_clientes"), "s.idCliente = c.id", array("rfc", "nombre"))
                    ->where("s.sistema = 'dashboard'")
                    ->where("s.identificador = ?", $identificador);
            $stmt = $this->_db_table->fetchRow($select);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

}
