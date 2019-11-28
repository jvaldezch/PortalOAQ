<?php

class Trafico_Model_TraficoTmpMapper {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Trafico_Model_DbTable_TraficoTmp();
    }
    
    public function obtener($usuario) {
        try {
            $sql = $this->_db_table->select()
                    ->where("usuario = ?", $usuario);
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return false;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception($this->_throwException("Db Exception found on", __METHOD__, $ex));
        }
    }
    
    public function verificar($patente, $aduana, $pedimento, $referencia) {
        try {
            $sql = $this->_db_table->select()
                    ->from("trafico_tmp", array("id"))
                    ->where("patente = ?", $patente)
                    ->where("aduana = ?", $aduana)
                    ->where("pedimento = ?", $pedimento)
                    ->where("referencia = ?", $referencia);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return true;
            }
            return false;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception($this->_throwException("Db Exception found on", __METHOD__, $ex));
        }
    }
    
    public function agregar($array, $username) {
        try {
            $data = array(
                "patente" => $array["patente"],
                "aduana" => $array["aduana"],
                "pedimento" => $array["pedimento"],
                "referencia" => $array["referencia"],
                "rfcCliente" => $array["rfcCliente"],
                "cveImportador" => $array["cveImportador"],
                "tipoOperacion" => $array["tipoOperacion"],
                "tipoCambio" => $array["tipoCambio"],
                "cvePed" => $array["cvePed"],
                "tipoCambio" => $array["tipoCambio"],
                "regimen" => $array["regimen"],
                "usuario" => $username,
                "creado" => date("Y-m-d H:i:s"),
            );
            $stmt = $this->_db_table->insert($data);
            if ($stmt) {
                return true;
            }
            return false;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception($this->_throwException("Db Exception found on", __METHOD__, $ex));
        }
    }

    protected function _throwException($message, $method, Exception $ex) {
        return $message . " at " . $method . " >> " . $ex->getMessage() . " line: " . $ex->getLine() . " info: " . $ex->getCode() . " trace: " . $ex->getTrace();
    }

}
