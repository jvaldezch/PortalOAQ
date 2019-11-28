<?php

class Application_Model_WsWsdl {

    protected $_dbTable;

    public function __construct() {
        $this->_dbTable = new Application_Model_DbTable_WsWsdl();
    }

    public function getWsdl($patente, $aduana, $sistema) {
        try {
            $sql = $this->_dbTable->select()
                    ->from("ws_wsdl", array("wsdl"))
                    ->where("patente = ?", $patente)
                    ->where("aduana = ?", $aduana)
                    ->where("habilitado = 1")
                    ->where("sistema = ?", $sistema);
            $stmt = $this->_dbTable->fetchRow($sql);
            if ($stmt) {
                return $stmt["wsdl"];
            }
            return;
        } catch (Zend_Db_Exception $e) {
            throw new Exception("DB Exception at " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function getWsdlPedimentos($patente, $aduana) {
        try {
            $sql = $this->_dbTable->select()
                    ->from("ws_wsdl", array("wsdl"))
                    ->where("patente = ?", $patente)
                    ->where("aduana = ?", $aduana)
                    ->where("habilitado = 1")
                    ->where("tipo = 'pedimentos'");
            $stmt = $this->_dbTable->fetchRow($sql);
            if ($stmt) {
                return $stmt["wsdl"];
            }
            return;
        } catch (Zend_Db_Exception $e) {
            throw new Exception("DB Exception at " . __METHOD__ . ": " . $e->getMessage());
        }
    }

}
