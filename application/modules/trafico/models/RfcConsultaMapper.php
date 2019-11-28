<?php

class Trafico_Model_RfcConsultaMapper {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Trafico_Model_DbTable_RfcConsulta();
    }

    public function obtener($idCliente) {
        try {
            $sql = $this->_db_table->select()
                    ->where('idCliente = ?', $idCliente);
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                $data = array();
                foreach ($stmt->toArray() as $item) {
                    if ($item["tipo"] == 'cove') {
                        $data["cove"][$item["id"]] = $item["rfc"];
                    } elseif ($item["tipo"] == 'edoc') {
                        $data["edoc"][$item["id"]] = $item["rfc"];
                    }
                }
                return $data;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("Zend DB Exception found on " . __METHOD__ . " >> " . $e->getMessage());
        }
    }

    public function rfcCove($idCliente) {
        try {
            
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("Zend DB Exception found on " . __METHOD__ . " >> " . $e->getMessage());
        }
    }

    public function rfcEdocument($idCliente) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("rfc"))
                    ->where("idCliente = ?", $idCliente)
                    ->where("tipo = 'edoc'");
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                $data = $stmt->toArray();
                return $data["rfc"];
            }
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("Zend DB Exception found on " . __METHOD__ . " >> " . $e->getMessage());
        }
    }

}
